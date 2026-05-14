<?php
require_once __DIR__ . '/../DAL/CategoryDAL.php';

class CategoryBLL
{
    private $dal;

    public function __construct($pdo)
    {
        $this->dal = new CategoryDAL($pdo);
    }

    public function getCategories($limit = 5, $offset = 0)
    {
        return $this->dal->getCategories($limit, $offset);
    }
    public function addCategories($data)
    {
        return $this->dal->addCategories($data['category_name'], $data['description']);
    }
    public function deleteCategory($id)
    {
        return $this->dal->deleteCategory($id);
    }
    public function updateCategory($data)
    {
        if (empty($data['category_name'])) return false;
        return $this->dal->updateCategory(
            $data['category_id'],
            $data['category_name'],
            $data['description']
        );
    }
    public function getCategoryTotal(){
        return $this->dal->getCategoryTotal();
    }
}
