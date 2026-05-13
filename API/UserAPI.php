<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../BLL/UserBLL.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
    $action = $_POST['action'] ?? '';
    $bll = new UserBLL($pdo);

    try{
        if($action == 'add'){
            $bll->addUser($_POST);
            $_SESSION['success'] = "Thêm thành công!";
        }
        elseif($action == 'delete'){
            $bll->deleteUser($_POST['id']);
            $_SESSION['success'] = "Xóa thành công!";
        }
        else if($action == 'update'){
            bll->updateUser($_POST);
            $_SESSION['success'] = "Cập nhật thành công!";
        }
    }
}
?>