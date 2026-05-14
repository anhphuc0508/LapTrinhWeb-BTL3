<?php
require_once __DIR__ . '/../CONFIG/db.php'; 
require_once __DIR__ . '/../BLL/UserBLL.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    $userBLL = new UserBLL($pdo);

    if ($action == 'login') {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        $user = $userBLL->checkLogin(['username' => $username, 'password' => $password]);
        
        if ($user) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            header("Location: index.php"); 
            exit;
        } else {
            $error = 'Sai tên đăng nhập hoặc mật khẩu!';
        }
    } elseif ($action == 'register') {
        $username = $_POST['username'] ?? '';
        $fullname = $_POST['fullname'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm'] ?? '';

        if ($password !== $confirm) {
            $error = 'Mật khẩu xác nhận không khớp!';
        } else {
            try {
                $result = $userBLL->addUser([
                    'username' => $username,
                    'fullname' => $fullname,
                    'email' => $email,
                    'password' => $password,
                    'role' => 'employee'
                ]);
                
                if ($result) {
                    $success = 'Đăng ký thành công! Vui lòng chuyển sang tab Đăng nhập.';
                } else {
                    $error = 'Đăng ký thất bại. Vui lòng thử lại sau.';
                }
            } catch (Exception $e) {
                $error = 'Tên đăng nhập hoặc Email có thể đã tồn tại!';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác thực - Hệ thống Quản trị</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary: #4F46E5;
            --primary-hover: #4338CA;
            --bg-color: #F3F4F6;
            --card-bg: rgba(255, 255, 255, 0.95);
            --text-main: #1F2937;
            --text-muted: #6B7280;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #EEF2FF 0%, #C7D2FE 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px 0;
            overflow-x: hidden;
        }

        .auth-wrapper {
            width: 100%;
            max-width: 480px;
            padding: 20px;
            position: relative;
        }

        .auth-card {
            background: var(--card-bg);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            padding: 40px 30px;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            min-height: 550px;
        }

        .auth-card:hover {
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.12);
        }

        .toggle-container {
            display: flex;
            background: #EEF2FF;
            border-radius: 12px;
            padding: 5px;
            margin-bottom: 30px;
            position: relative;
            z-index: 10;
        }

        .toggle-btn {
            flex: 1;
            padding: 10px;
            text-align: center;
            color: var(--text-muted);
            font-weight: 600;
            cursor: pointer;
            z-index: 2;
            transition: color 0.3s ease;
        }

        .toggle-btn.active {
            color: var(--primary);
        }

        .toggle-slider {
            position: absolute;
            top: 5px;
            bottom: 5px;
            left: 5px;
            width: calc(50% - 5px);
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            transition: transform 0.3s cubic-bezier(0.4, 0.0, 0.2, 1);
            z-index: 1;
        }

        .forms-container {
            position: relative;
            width: 100%;
            height: 100%;
        }

        .form-section {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            transition: all 0.4s cubic-bezier(0.4, 0.0, 0.2, 1);
            opacity: 0;
            pointer-events: none;
            transform: translateX(50px);
        }

        .form-section.active {
            opacity: 1;
            pointer-events: auto;
            transform: translateX(0);
            position: relative;
        }

        .form-section.left {
            transform: translateX(-50px);
        }

        .login-header {
            text-align: center;
            margin-bottom: 25px;
        }

        .logo-icon {
            font-size: 40px;
            color: var(--primary);
            margin-bottom: 15px;
            background: #EEF2FF;
            width: 80px;
            height: 80px;
            line-height: 80px;
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 4px 10px rgba(79, 70, 229, 0.2);
        }

        .login-header h2 {
            font-weight: 700;
            color: var(--text-main);
            font-size: 24px;
            margin-bottom: 8px;
        }

        .login-header p {
            color: var(--text-muted);
            font-size: 14px;
            margin: 0;
        }

        .form-floating {
            margin-bottom: 20px;
        }

        .form-control {
            border-radius: 12px;
            border: 1px solid #E5E7EB;
            padding: 12px 15px;
            height: calc(3.5rem + 2px);
            font-size: 15px;
            background-color: #F9FAFB;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
            background-color: #fff;
        }

        .form-floating label {
            padding: 12px 15px;
            color: var(--text-muted);
        }

        .input-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #9CA3AF;
            z-index: 10;
        }

        .btn-auth {
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 14px;
            font-weight: 600;
            font-size: 16px;
            width: 100%;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            margin-top: 10px;
        }

        .btn-auth:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(79, 70, 229, 0.3);
        }

        .btn-auth:active {
            transform: translateY(0);
        }

        .options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
            font-size: 14px;
        }

        .form-check-input:checked {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        .forgot-link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .forgot-link:hover {
            color: var(--primary-hover);
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="auth-wrapper">
        <div class="auth-card">
            
            <div class="toggle-container">
                <div class="toggle-slider" id="toggleSlider"></div>
                <div class="toggle-btn active" id="btnShowLogin" onclick="toggleAuth('login')">Đăng nhập</div>
                <div class="toggle-btn" id="btnShowRegister" onclick="toggleAuth('register')">Đăng ký</div>
            </div>

            <div class="forms-container">
                
                <?php if ($error): ?>
                    <div class="alert alert-danger" style="position: absolute; top: 0; left: 0; width: 100%; z-index: 100; padding: 10px; border-radius: 12px; text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success" style="position: absolute; top: 0; left: 0; width: 100%; z-index: 100; padding: 10px; border-radius: 12px; text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                        <?= htmlspecialchars($success) ?>
                    </div>
                <?php endif; ?>

                <div class="form-section active" id="loginSection">
                    <div class="login-header">
                        <div class="logo-icon"><i class="fa-solid fa-cube"></i></div>
                        <h2>Chào mừng trở lại!</h2>
                        <p>Đăng nhập vào hệ thống quản trị sản phẩm</p>
                    </div>

                    <form action="login.php" method="POST" id="loginForm">
                        <input type="hidden" name="action" value="login">
                        <div class="position-relative form-floating mb-3">
                            <input type="text" name="username" class="form-control" id="login_username" placeholder="Tên đăng nhập" required>
                            <label for="login_username">Tên đăng nhập</label>
                            <i class="fa-solid fa-user input-icon"></i>
                        </div>

                        <div class="position-relative form-floating mb-3">
                            <input type="password" name="password" class="form-control" id="login_password" placeholder="Mật khẩu" required>
                            <label for="login_password">Mật khẩu</label>
                            <i class="fa-solid fa-lock input-icon"></i>
                        </div>

                        <div class="options mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember">
                                <label class="form-check-label text-muted" for="remember">Ghi nhớ tôi</label>
                            </div>
                            <a href="#" class="forgot-link">Quên mật khẩu?</a>
                        </div>

                        <button type="submit" class="btn-auth">Đăng nhập <i class="fa-solid fa-arrow-right ms-2"></i></button>
                    </form>
                </div>

           
                <div class="form-section" id="registerSection">
                    <div class="login-header">
                        <div class="logo-icon"><i class="fa-solid fa-user-plus"></i></div>
                        <h2>Tạo tài khoản mới</h2>
                        <p>Đăng ký tài khoản nhân viên hệ thống</p>
                    </div>

                    <form action="login.php" method="POST" id="registerForm">
                        <input type="hidden" name="action" value="register">
                        <div class="row">
                            <div class="col-6">
                                <div class="position-relative form-floating mb-3">
                                    <input type="text" name="username" class="form-control" id="reg_username" placeholder="Tên đăng nhập" required>
                                    <label for="reg_username">Tên đăng nhập *</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="position-relative form-floating mb-3">
                                    <input type="text" name="fullname" class="form-control" id="reg_fullname" placeholder="Họ tên" required>
                                    <label for="reg_fullname">Họ tên *</label>
                                </div>
                            </div>
                        </div>

                        <div class="position-relative form-floating mb-3">
                            <input type="email" name="email" class="form-control" id="reg_email" placeholder="Email" required>
                            <label for="reg_email">Địa chỉ Email *</label>
                            <i class="fa-solid fa-envelope input-icon"></i>
                        </div>

                        <div class="position-relative form-floating mb-3">
                            <input type="password" name="password" class="form-control" id="reg_password" placeholder="Mật khẩu" required>
                            <label for="reg_password">Mật khẩu *</label>
                            <i class="fa-solid fa-lock input-icon"></i>
                        </div>

                        <div class="position-relative form-floating mb-3">
                            <input type="password" name="confirm" class="form-control" id="reg_confirm" placeholder="Xác nhận MK" required>
                            <label for="reg_confirm">Xác nhận mật khẩu *</label>
                            <i class="fa-solid fa-shield-check input-icon"></i>
                        </div>

                        <button type="submit" class="btn-auth">Đăng ký tài khoản <i class="fa-solid fa-check ms-2"></i></button>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('focus', function() {
                let icon = this.parentElement.querySelector('.input-icon');
                if(icon) icon.style.color = '#4F46E5';
            });
            input.addEventListener('blur', function() {
                let icon = this.parentElement.querySelector('.input-icon');
                if(icon) icon.style.color = '#9CA3AF';
            });
        });

        function toggleAuth(type) {
            const loginSec = document.getElementById('loginSection');
            const regSec = document.getElementById('registerSection');
            const slider = document.getElementById('toggleSlider');
            const btnLogin = document.getElementById('btnShowLogin');
            const btnReg = document.getElementById('btnShowRegister');

            if (type === 'login') {
                slider.style.transform = 'translateX(0)';
                btnLogin.classList.add('active');
                btnReg.classList.remove('active');
                
                loginSec.classList.remove('left');
                loginSec.classList.add('active');
                
                regSec.classList.remove('active');
                regSec.classList.add('left'); 
            } else {
                slider.style.transform = 'translateX(100%)';
                btnReg.classList.add('active');
                btnLogin.classList.remove('active');

                regSec.classList.remove('left');
                regSec.classList.add('active');
                
                loginSec.classList.remove('active');
                loginSec.classList.add('left'); 
            }
        }

      
        document.getElementById('registerForm').addEventListener('submit', e => {
            let p1 = document.getElementById('reg_password').value;
            let p2 = document.getElementById('reg_confirm').value;
            if(p1 !== p2) {
                e.preventDefault(); 
                alert('Mật khẩu xác nhận không khớp!');
            }
        });
    </script>
</body>
</html>
