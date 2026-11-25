<?php
class ProductModel
{
    private $pdo;
    function __construct()
    {
        $this->pdo = pdo_connect();
    }

    public function getAllProductForCustomer()
    {
        $pdo = pdo_connect();
        $sql = "SELECT p.product_id, p.product_name, p.price, p.image_url, p.stock_quantity, p.status, c.category_name, c.category_id
                FROM products p 
                JOIN categories c ON p.category_id = c.category_id 
                WHERE p.status = 'available'
                ORDER BY p.product_id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->applyPromotions($products);
        return $products;
    }

    // Từ khóa tìm kiếm -> mẫu LIKE (thoát ký tự % và _ do người dùng gõ)
    private function keywordPattern($keyword)
    {
        return '%' . addcslashes(trim((string) $keyword), '%_\\') . '%';
    }

    public function getProductsForCustomerPagination($limit, $offset, $selectedCategories = [], $selectedPriceRange = null, $keyword = '')
    {
        $pdo = pdo_connect();
        $sql = "SELECT p.product_id, p.product_name, p.price, p.image_url, p.stock_quantity, p.status, c.category_name, c.category_id
                FROM products p
                JOIN categories c ON p.category_id = c.category_id
                WHERE p.status = 'available'";

        // Tìm kiếm theo tên sản phẩm / tên danh mục
        if (trim((string) $keyword) !== '') {
            $sql .= " AND (p.product_name LIKE ? OR c.category_name LIKE ?)";
        }

        // Filter by categories
        if (!empty($selectedCategories) && is_array($selectedCategories)) {
            $placeholders = implode(',', array_fill(0, count($selectedCategories), '?'));
            $sql .= " AND c.category_id IN ($placeholders)";
        }

        // Filter by price range
        if ($selectedPriceRange && count($selectedPriceRange) == 2) {
            $sql .= " AND p.price >= ? AND p.price <= ?";
        }

        $sql .= " ORDER BY p.product_id DESC LIMIT ? OFFSET ?";

        $stmt = $pdo->prepare($sql);

        $paramIndex = 1;
        if (trim((string) $keyword) !== '') {
            $stmt->bindValue($paramIndex++, $this->keywordPattern($keyword));
            $stmt->bindValue($paramIndex++, $this->keywordPattern($keyword));
        }

        // Bind category parameters
        if (!empty($selectedCategories) && is_array($selectedCategories)) {
            foreach ($selectedCategories as $categoryId) {
                $stmt->bindValue($paramIndex++, $categoryId, PDO::PARAM_INT);
            }
        }

        // Bind price range parameters
        if ($selectedPriceRange && count($selectedPriceRange) == 2) {
            $stmt->bindValue($paramIndex++, (int)$selectedPriceRange[0], PDO::PARAM_INT);
            $stmt->bindValue($paramIndex++, (int)$selectedPriceRange[1], PDO::PARAM_INT);
        }

        $stmt->bindValue($paramIndex++, $limit, PDO::PARAM_INT);
        $stmt->bindValue($paramIndex++, $offset, PDO::PARAM_INT);
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->applyPromotions($products);

        return $products;
    }

    public function applyPromotions(&$products)
    {
        if (empty($products)) return;

        $productIds = array_column($products, 'product_id');
        require_once __DIR__ . '/promotion_model.php';
        $promotionModel = new PromotionModel();
        $promotions = $promotionModel->getProductPromotions($productIds);

        // Gom khuyến mãi theo sản phẩm
        $promotionMap = [];
        foreach ($promotions as $promo) {
            $promotionMap[$promo['product_id']][] = $promo;
        }

        foreach ($products as &$product) {
            if (empty($promotionMap[$product['product_id']])) continue;

            // Nhiều khuyến mãi cùng lúc -> chọn cái cho giá thấp nhất
            // (trước đây lấy cái cuối danh sách nên trang chủ và trang chi tiết có thể lệch giá)
            $best = null;
            foreach ($promotionMap[$product['product_id']] as $promo) {
                $price = $promo['discount_type'] === 'percentage'
                    ? $product['price'] * (1 - $promo['discount_value'] / 100)
                    : $product['price'] - $promo['discount_value'];
                $price = max(0, $price);
                if ($best === null || $price < $best[1]) $best = [$promo, $price];
            }
            $product['promotion'] = $best[0];
            $product['discounted_price'] = $best[1];
        }
        unset($product);
    }

    // Sản phẩm gợi ý cho trang chi tiết: ưu tiên cùng danh mục, thiếu thì lấy thêm sản phẩm mới nhất
    public function getRelatedProducts($categoryId, $excludeId, $limit = 4)
    {
        $sql = "SELECT p.product_id, p.product_name, p.price, p.image_url, p.stock_quantity, p.status, c.category_name, c.category_id
                FROM products p
                JOIN categories c ON p.category_id = c.category_id
                WHERE p.status = 'available' AND p.product_id <> ?
                ORDER BY (p.category_id = ?) DESC, p.product_id DESC
                LIMIT ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(1, (int) $excludeId, PDO::PARAM_INT);
        $stmt->bindValue(2, (int) $categoryId, PDO::PARAM_INT);
        $stmt->bindValue(3, (int) $limit, PDO::PARAM_INT);
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->applyPromotions($products);
        return $products;
    }

    public function countProductsForCustomer($selectedCategories = [], $selectedPriceRange = null, $keyword = '')
    {
        $pdo = pdo_connect();
        $sql = "SELECT COUNT(*) FROM products p
                JOIN categories c ON p.category_id = c.category_id
                WHERE p.status = 'available'";

        if (trim((string) $keyword) !== '') {
            $sql .= " AND (p.product_name LIKE ? OR c.category_name LIKE ?)";
        }

        // Filter by categories
        if (!empty($selectedCategories) && is_array($selectedCategories)) {
            $placeholders = implode(',', array_fill(0, count($selectedCategories), '?'));
            $sql .= " AND c.category_id IN ($placeholders)";
        }

        // Filter by price range
        if ($selectedPriceRange && count($selectedPriceRange) == 2) {
            $sql .= " AND p.price >= ? AND p.price <= ?";
        }

        $stmt = $pdo->prepare($sql);

        $paramIndex = 1;
        if (trim((string) $keyword) !== '') {
            $stmt->bindValue($paramIndex++, $this->keywordPattern($keyword));
            $stmt->bindValue($paramIndex++, $this->keywordPattern($keyword));
        }

        // Bind category parameters
        if (!empty($selectedCategories) && is_array($selectedCategories)) {
            foreach ($selectedCategories as $categoryId) {
                $stmt->bindValue($paramIndex++, $categoryId, PDO::PARAM_INT);
            }
        }

        // Bind price range parameters
        if ($selectedPriceRange && count($selectedPriceRange) == 2) {
            $stmt->bindValue($paramIndex++, (int)$selectedPriceRange[0], PDO::PARAM_INT);
            $stmt->bindValue($paramIndex++, (int)$selectedPriceRange[1], PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function getAllProduct()
    {
        $pdo = pdo_connect();
        $sql = "SELECT p.product_id ,p.product_name AS product_name, p.price, p.description,p.image_url,
                        p.stock_quantity, p.status, c.category_name, p.created_at,p.updated_at
        FROM products p JOIN categories c ON p.category_id = c.category_id ORDER BY p.product_id DESC
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->applyPromotions($products);
        return $products;
    }

    public function addProduct($product_name, $price, $description, $image_url, $stock_quantity, $status, $category_id)
    {
        $pdo = pdo_connect();
        $sql = "INSERT INTO products (product_name,price,description,image_url,stock_quantity,status,category_id) VALUES (?,?,?,?,?,?,?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$product_name, $price, $description, $image_url, $stock_quantity, $status, $category_id]);
        return $stmt;
    }

    public function getValueEnumStatus()
    {
        $pdo = pdo_connect();
        $sql = "SHOW COLUMNS FROM products LIKE 'status'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $listEnum = $row['Type'];
        preg_match("/^enum\('(.*)'\)$/", $listEnum, $matches);
        return explode("','", $matches[1]);
    }

    public function deleteProduct($product_id)
    {
        $pdo = pdo_connect();
        $sql_img = "SELECT image_url FROM products WHERE product_id=?";
        $stmt = $pdo->prepare($sql_img);
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($product) {
            deleteImage($product['image_url']);
            $sql = "DELETE FROM products WHERE product_id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$product_id]);
            return $stmt->rowCount() > 0;
        }
        return false;
    }

    public function getByIdProduct($product_id)
    {
        $pdo = pdo_connect();
        $sql = "SELECT *FROM products WHERE product_id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($product) {
            $arr = [$product];
            $this->applyPromotions($arr);
            return $arr[0];
        }
        return false;
    }

    public function updateProduct($product_name, $price, $description, $image_url, $stock_quantity, $status, $category_id, $product_id)
    {
        $pdo = pdo_connect();
        // Trường hợp:
        //1. update nhưng không update ảnh
        if ($image_url === null) {
            $sql = "UPDATE products 
            SET product_name=?,price=?,description=?,stock_quantity=?,status=?, category_id=? 
            WHERE product_id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$product_name, $price, $description, $stock_quantity, $status, $category_id, $product_id]);
            return $stmt;
        }
        //2. update nhưng có update cả ảnh thì xóa ảnh của product hiện tại thay = ảnh mới
        else {
            $current_image_url = $this->getByIdProduct($product_id);
            if (!empty($current_image_url['image_url']) && $current_image_url) {
                deleteImage($current_image_url['image_url']);
            }
            $sql = "UPDATE products 
                SET product_name=?,price=?,description=?,image_url=?,stock_quantity=?,status=?, category_id=? 
                WHERE product_id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$product_name, $price, $description, $image_url, $stock_quantity, $status, $category_id, $product_id]);
            return $stmt;
        }
    }

    // Hàm phân trang 
    public function getProductPagination($limit, $offset)
    {
        $pdo = pdo_connect();
        $limit = (int)$limit;
        $offset = (int)$offset;

        $sql = "SELECT p.product_id ,p.product_name AS product_name, p.price, p.description,p.image_url,
                   p.stock_quantity, p.status, c.category_name, p.created_at,p.updated_at
            FROM products p 
            JOIN categories c ON p.category_id = c.category_id 
            ORDER BY p.product_id DESC
            LIMIT $limit OFFSET $offset";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->applyPromotions($products);
        return $products;
    }

    public function getTotalProduct()
    {
        $pdo = pdo_connect();
        $sql = "SELECT COUNT(*) FROM products";
        $stmt = $pdo->query($sql);
        return $stmt->fetchColumn();
    }
    // END Hàm phân trang 
}
