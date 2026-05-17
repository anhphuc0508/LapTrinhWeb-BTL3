<?php
require_once __DIR__ . '/../CONFIG/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../BLL/ProductBLL.php';

$bll = new ProductBLL($pdo);
$id = $_GET['product_id'] ?? null;
$product = [
    'product_name' => '', 'category_id' => '', 'supplier_id' => '', 
    'description' => '', 'unit_price' => 0, 'stock_quantity' => 0
];
$variants = [];

$categories = $bll->getCategories();
$suppliers = $bll->getSuppliers();

if ($id) {
    $product = $bll->getProductById($id);
    $variants = $bll->getProductVariants($id);
    if (!$product) {
        $_SESSION['error'] = "Sản phẩm không tồn tại!";
        header('Location: index.php');
        exit;
    }
}
$fullname = !empty($_SESSION['full_name']) ? $_SESSION['full_name'] : ($_SESSION['username'] ?? 'Admin');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $id ? 'Sửa' : 'Thêm' ?> Sản phẩm</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <style>
        .form-container { background: var(--bg-card); padding: 30px; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); border: 1px solid var(--border-color); max-width: 800px; }
    </style>
</head>
<body>
    <div class="app-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2><i class="fa-solid fa-cube"></i> Trang Quản Lý</h2>
            </div>
            <ul class="nav-links">
                <li><a href="index.php"><i class="fa-solid fa-box"></i> Sản phẩm</a></li>

            </ul>
            <ul class="nav-links" style="margin-top: auto; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1);">
                <li><a href="login.php"><i class="fa-solid fa-right-from-bracket"></i> Đăng xuất</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <header class="topbar">
                <div class="search-bar"></div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($fullname) ?>&background=0D8ABC&color=fff" alt="User">
                    <div>
                        <span><?= htmlspecialchars($fullname) ?></span><br>
                        <small style="color: <?= ($_SESSION['role'] ?? '') === 'admin' ? '#f59e0b' : '#10b981' ?>; font-weight: 600; font-size: 11px; text-transform: uppercase;">
                            <?= ($_SESSION['role'] ?? '') === 'admin' ? 'Quản trị viên' : 'Nhân viên' ?>
                        </small>
                    </div>
                </div>
            </header>

            <div class="content-header">
                <div class="title">
                    <h1><?= $id ? 'Chỉnh Sửa' : 'Thêm Mới' ?> Sản Phẩm</h1>
                    <p>Nhập thông tin sản phẩm dưới đây.</p>
                </div>
                <a href="index.php" class="btn btn-secondary" style="text-decoration: none;">
                    <i class="fa-solid fa-arrow-left"></i> Quay lại
                </a>
            </div>

            <div class="form-container">
                <form id="mainProductForm" onsubmit="submitAjaxForm(event, 'mainProductForm')">
                    <input type="hidden" name="action" value="<?= $id ? 'update' : 'add' ?>">
                    <?php if ($id): ?>
                        <input type="hidden" name="product_id" value="<?= $id ?>">
                    <?php endif; ?>

                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">Tên Sản phẩm *</label>
                        <input type="text" name="product_name" class="form-control" value="<?= htmlspecialchars($product['product_name']) ?>" required>
                    </div>

                    <div class="form-row mb-3 d-flex gap-3">
                        <div class="form-group w-50">
                            <label class="form-label fw-bold">Danh mục</label>
                            <select name="category_id" class="form-select">
                                <option value="">-- Chọn danh mục --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['category_id'] ?>" <?= $product['category_id'] == $cat['category_id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['category_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group w-50">
                            <label class="form-label fw-bold">Nhà cung cấp</label>
                            <select name="supplier_id" class="form-select">
                                <option value="">-- Chọn nhà cung cấp --</option>
                                <?php foreach ($suppliers as $sup): ?>
                                    <option value="<?= $sup['supplier_id'] ?>" <?= $product['supplier_id'] == $sup['supplier_id'] ? 'selected' : '' ?>><?= htmlspecialchars($sup['supplier_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-row mb-3 d-flex gap-3">
                        <div class="form-group w-50">
                            <label class="form-label fw-bold">Giá bán (VNĐ) *</label>
                            <input type="number" name="unit_price" class="form-control" value="<?= $product['unit_price'] ?>" min="0" required>
                        </div>
                        <div class="form-group w-50">
                            <label class="form-label fw-bold">Số lượng tồn kho *</label>
                            <input type="number" name="stock_quantity" class="form-control" value="<?= $product['stock_quantity'] ?>" min="0" required>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label class="form-label fw-bold">Mô tả chi tiết</label>
                        <textarea name="description" class="form-control" rows="5"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Lưu thông tin</button>
                    </div>
                </form>
            </div>

          
            <?php if ($id): ?>
            <div class="form-container mt-4">
                <h3 class="mb-4"><i class="fa-solid fa-shapes"></i> Quản Lý Biến Thể Sản Phẩm</h3>
                <div class="mb-4">
                    <h5>Danh sách biến thể hiện tại:</h5>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>SKU</th>
                                    <th>Tên biến thể</th>
                                    <th>Giá bán</th>
                                    <th>Tồn kho</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($variants) > 0): ?>
                                    <?php foreach ($variants as $v): ?>
                                    <tr>
                                        <td>#<?= $v['variant_id'] ?></td>
                                        <td><?= htmlspecialchars($v['sku'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($v['variant_name'] ?? '-') ?></td>
                                        <td><?= number_format($v['price'] ?? 0, 0, ',', '.') ?> đ</td>
                                        <td><?= $v['stock_quantity'] ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editVariantModal" onclick='editVariant(<?= json_encode($v) ?>)'>
                                                <i class="fa-solid fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteVariant(<?= $v['variant_id'] ?>, <?= $id ?>)">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="6" class="text-center text-muted">Chưa có biến thể nào. Thêm biến thể mới bằng nút dưới đây.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="border-top pt-4">
                    <h5 class="mb-3">Thêm biến thể mới:</h5>
                    <form id="addVariantForm" onsubmit="submitAjaxForm(event, 'addVariantForm')">
                        <input type="hidden" name="action" value="addVariant">
                        <input type="hidden" name="product_id" value="<?= $id ?>">
                        
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label class="form-label fw-bold">SKU</label>
                                <input type="text" name="sku" class="form-control" placeholder="VD: SKU001...">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Tên biến thể</label>
                                <input type="text" name="variant_name" class="form-control" placeholder="VD: Size M...">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Giá bán (VNĐ)</label>
                                <input type="number" name="price" class="form-control" min="0" value="0" step="0.01">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Tồn kho</label>
                                <input type="number" name="stock_quantity" class="form-control" min="0" value="0">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success"><i class="fa-solid fa-plus"></i> Thêm biến thể</button>
                    </form>
                </div>
            </div>
            <?php else: ?>
            <div class="alert alert-info mt-4" role="alert">
                <i class="fa-solid fa-info-circle"></i> Hãy lưu sản phẩm trước khi thêm biến thể.
            </div>
            <?php endif; ?>
        </main>
    </div>

    <div class="modal fade" id="editVariantModal" tabindex="-1" aria-labelledby="editVariantModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editVariantModalLabel">Chỉnh sửa biến thể</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editVariantForm" onsubmit="submitAjaxForm(event, 'editVariantForm')">
                    <input type="hidden" name="action" value="updateVariant">
                    <input type="hidden" name="product_id" value="<?= $id ?>">
                    <input type="hidden" name="variant_id" id="editVariantId">
                    
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">SKU</label>
                            <input type="text" name="sku" id="editVariantSku" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tên biến thể</label>
                            <input type="text" name="variant_name" id="editVariantName" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Giá bán (VNĐ)</label>
                            <input type="number" name="price" id="editVariantPrice" class="form-control" min="0" step="0.01">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tồn kho</label>
                            <input type="number" name="stock_quantity" id="editVariantStock" class="form-control" min="0">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Lưu thay đổi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editVariant(variant) {
            document.getElementById('editVariantId').value = variant.variant_id;
            document.getElementById('editVariantSku').value = variant.sku || '';
            document.getElementById('editVariantName').value = variant.variant_name || '';
            document.getElementById('editVariantPrice').value = variant.price || 0;
            document.getElementById('editVariantStock').value = variant.stock_quantity || 0;
        }

        async function submitAjaxForm(event, formId) {
            event.preventDefault(); 
            
            const form = document.getElementById(formId);
            const formData = new FormData(form);

            try {
                const response = await fetch('../API/ProductAPI.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (data.status === 'success') {
                    alert(data.message || 'Thành công!'); 
                    if (data.new_product_id) {
                        window.location.href = 'product_form.php?product_id=' + data.new_product_id;
                    } else {
                        location.reload();
                    }
                } else {
                    alert('LỖI: ' + (data.message || 'Xử lý thất bại!'));
                }
            } catch (error) {
                console.error('Lỗi API:', error);
                alert('Không thể kết nối đến máy chủ!');
            }
        }

        async function deleteVariant(variantId, productId) {
            if (!confirm('Bạn có chắc chắn muốn xóa biến thể này?')) return;
            
            const fd = new FormData();
            fd.append('action', 'deleteVariant');
            fd.append('variant_id', variantId);
            fd.append('product_id', productId);

            try {
                const response = await fetch('../API/ProductAPI.php', {
                    method: 'POST',
                    body: fd 
                });
                const data = await response.json();

                if (data.status === 'success') {
                    location.reload();
                } else {
                    alert('LỖI: ' + (data.message || 'Không thể xóa'));
                }
            } catch (error) {
                console.error('Lỗi API:', error);
                alert('Có lỗi xảy ra khi xóa!');
            }
        }
    </script>
</body>
</html>
