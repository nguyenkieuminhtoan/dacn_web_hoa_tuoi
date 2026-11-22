<?php
include_once __DIR__ . '/../../models/dashboard_model.php';
class DashboardController
{
    const LOW_STOCK_THRESHOLD = 5;

    private $model;
    public function __construct()
    {
        $this->model = new DashboardModel();
    }

    private function validDate($value)
    {
        $d = DateTime::createFromFormat('Y-m-d', (string) $value);
        return ($d && $d->format('Y-m-d') === $value) ? $value : null;
    }

    public function index()
    {
        // Mặc định: 30 ngày gần nhất
        $to   = $this->validDate($_GET['to'] ?? '') ?? date('Y-m-d');
        $from = $this->validDate($_GET['from'] ?? '') ?? date('Y-m-d', strtotime($to . ' -29 days'));
        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }

        $summary         = $this->model->getSummary($from, $to);
        $latestOrders    = $this->model->getLatestOrders($from, $to, 10);
        $topProducts     = $this->model->getTopProducts($from, $to, 5);
        $lowStockLimit   = self::LOW_STOCK_THRESHOLD;
        $lowStock        = $this->model->getLowStockProducts($lowStockLimit);

        $viewFile = "../../views/admins/overview.php";
        include __DIR__ . '/../../views/admins/dashboard.php';
    }
}
