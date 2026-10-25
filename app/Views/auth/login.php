<?php
declare(strict_types=1);
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= e(APP_NAME) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-container {
            max-width: 400px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="card shadow-lg">
                <div class="card-body p-5">
                    <h2 class="text-center mb-4"><?= e(APP_NAME) ?></h2>
                    <p class="text-center text-muted mb-4">Sistem Informasi Bimbingan Tugas Akhir</p>
                    
                    <?php if ($flashError): ?>
                        <div class="alert alert-danger" role="alert">
                            <?= e($flashError) ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- PENTING: action harus ke /login, method POST -->
                    <form method="POST" action="<?= e(BASE_URL) ?>/login">
                        <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= e($_POST['email'] ?? '') ?>" required autofocus>
                        </div>
                        
                        <div class="mb-4">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>