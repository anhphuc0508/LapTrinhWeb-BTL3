<?php
class CategoryDAL
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }
    public function getCategories($limit = 5, $offset = 0)
    {
        $sql=  "SELECT category_id, category_name, description
                FROM categories 
                ORDER BY category_name ASC
                LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', (int) $limit , PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    public function addCategories($category_name, $description)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO categories (category_name, description) 
            VALUES (?, ?)
        ");
        return $stmt->execute([$category_name, $description]);
    }
    public function deleteCategory($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM categories WHERE category_id = ?");
        return $stmt->execute([$id]);
    }
    public function updateCategory($id, $name, $description) {
        $stmt = $this->pdo->prepare("
            UPDATE categories 
            SET category_name = ?, description = ? 
            WHERE category_id = ?
        ");
        return $stmt->execute([$name, $description, $id]);
    }
    public function getCategoryTotal(){
        $sql ="SELECT COUNT(*) FROM categories";
        $stmt= $this->pdo->query($sql);
        return $stmt->fetchColumn();
    }
}
