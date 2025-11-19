<?php
session_start();

$login_error = $_SESSION['login_error'] ?? '';
$register_error = $_SESSION['register_error'] ?? '';
$activeForm = $_SESSION['active_form'] ?? 'login';

session_unset();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login & Register - Sweet Treats</title>
    <link rel="stylesheet" href="style.css?v=2">
</head>
<body class="auth-page">

<div class="auth-container">
    <div class="auth-branding">
        <div class="branding-content">
            <div class="ice-cream-illustration">
                <span class="ice-cream-emoji large">🍦</span>
                <span class="ice-cream-emoji medium">🍨</span>
                <span class="ice-cream-emoji small">🍧</span>
            </div>
            <h1 class="branding-title" id="branding-title">Welcome to Sweet Treats!</h1>
            <p class="branding-subtitle" id="branding-subtitle">Login to customize your perfect ice cream</p>
            <div class="decorative-elements">
                <span class="float-element">🍓</span>
                <span class="float-element">🍫</span>
                <span class="float-element">🌰</span>
                <span class="float-element">🍒</span>
            </div>
        </div>
    </div>
    <div class="auth-form-panel">
        <div class="form-container">
            <form action="login_register.php" method="POST" class="form <?php echo $activeForm=='login'?'active':''; ?>" id="login">
                <h2>Login to Your Account</h2>

                <?php if(!empty($login_error)) : ?>
                    <p class="error">❌ <?php echo $login_error; ?></p>
                <?php endif; ?>

                <div class="input-box">
                    <span class="icon">📧</span>
                    <input type="email" name="email" placeholder="Email Address" required>
                </div>

                <div class="input-box">
                    <span class="icon">🔒</span>
                    <input type="password" name="password" placeholder="Password" required>
                </div>

                <button type="submit" name="login" class="btn auth-btn">Login</button>

                <p class="switch-text">Don't have an account?
                    <a href="#" onclick="showForm('register')">Create Account</a>
                </p>
            </form>

            <form action="login_register.php" method="POST" class="form <?php echo $activeForm=='register'?'active':''; ?>" id="register">
                <h2>Create Your Account</h2>

                <?php if(!empty($register_error)) : ?>
                    <p class="error">❌ <?php echo $register_error; ?></p>
                <?php endif; ?>

                <div class="input-box">
                    <span class="icon">👤</span>
                    <input type="text" name="text" placeholder="Full Name" required>
                </div>

                <div class="input-box">
                    <span class="icon">📧</span>
                    <input type="email" name="email" placeholder="Email Address" required>
                </div>

                <div class="input-box">
                    <span class="icon">🔒</span>
                    <input type="password" name="password" placeholder="Password" required>
                </div>

                <div class="input-box">
                    <span class="icon">🎭</span>
                    <select name="role" required>
                        <option value="" disabled selected>Select Role</option>
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

                <button type="submit" name="register" class="btn auth-btn">Create Account</button>

                <p class="switch-text">Already have an account?
                    <a href="#" onclick="showForm('login')">Login Here</a>
                </p>
            </form>
        </div>
    </div>
</div>


<script>
function showForm(formName) {
    document.getElementById('login').classList.remove('active');
    document.getElementById('register').classList.remove('active');
    document.getElementById(formName).classList.add('active');

    const brandingTitle = document.getElementById('branding-title');
    const brandingSubtitle = document.getElementById('branding-subtitle');

    if (formName === 'login') {
        brandingTitle.textContent = 'Welcome Back!';
        brandingSubtitle.textContent = 'Login to customize your perfect ice cream';
    } else {
        brandingTitle.textContent = 'Join Sweet Treats!';
        brandingSubtitle.textContent = 'Create an account and start your sweet journey';
    }
}
</script>

</body>
</html>
