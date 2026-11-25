<?php
class CategoryModel
{
    private $pdo;
    function __construct()
    {
        $this->pdo = pdo_connect();
    }

    public function getAllCategory()
    {
        $pdo = pdo_connect();
        $sql = "SELECT * FROM categories 
        ORDER BY category_id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Trang quản trị: danh mục kèm tổng số sản phẩm (mọi trạng thái; danh mục trống = 0)
    public function getAllCategoryWithProductCount()
    {
        $sql = "SELECT c.*, COUNT(p.product_id) AS product_count
                FROM categories c
                LEFT JOIN products p ON p.category_id = c.category_id
                GROUP BY c.category_id
                ORDER BY c.category_id DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addCategory($category_name, $description)
    {
        $pdo = pdo_connect();
        $sql = "INSERT INTO categories (category_name, description) VALUES (?,?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$category_name, $description]);
        return $stmt;
    }

    public function deleteCategory($category_id)
    {
        $pdo = pdo_connect();
        $sql = "DELETE FROM categories WHERE category_id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$category_id]);
        return $stmt->rowCount() > 0;
    }

    public function getByIdCategory($category_id)
    {
        $pdo = pdo_connect();
        $sql = "SELECT * FROM categories WHERE category_id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$category_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateCategory($category_id, $category_name, $description)
    {
        $pdo = pdo_connect();
        $sql = "UPDATE categories SET category_name=?, description=? WHERE category_id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$category_id, $category_name, $description]);
        return $stmt;
    }
}
