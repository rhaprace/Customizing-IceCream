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
    <title>Login & Register</title>
    <link rel="stylesheet" href="style.css?v=1">
</head>
<body>

<div class="container">

    
    <form action="login_register.php" method="POST" class="form <?php echo $activeForm=='login'?'active':''; ?>" id="login">
        <h2>Login</h2>

        <?php if(!empty($login_error)) : ?>
            <p class="error"><?php echo $login_error; ?></p>
        <?php endif; ?>

        <div class="input-box">
            <span class="icon">♡</span>
            <input type="email" name="email" placeholder="Email" required>
        </div>

        <div class="input-box">
            <span class="icon">♡</span>
            <input type="password" name="password" placeholder="Password" required>
        </div>

        <button type="submit" name="login" class="btn">♡ Login ♡</button>

        <p class="switch-text">Don't have an account?
            <a href="#" onclick="showForm('register')">Register</a>
        </p>
    </form>


    <form action="login_register.php" method="POST" class="form <?php echo $activeForm=='register'?'active':''; ?>" id="register">
        <h2>Register</h2>

        <?php if(!empty($register_error)) : ?>
            <p class="error"><?php echo $register_error; ?></p>
        <?php endif; ?>

        <div class="input-box">
            <span class="icon">♡</span>
            <input type="text" name="text" placeholder="Full Name" required>
        </div>

        <div class="input-box">
            <span class="icon">♡</span>
            <input type="email" name="email" placeholder="Email" required>
        </div>

        <div class="input-box">
            <span class="icon">♡</span>
            <input type="password" name="password" placeholder="Password" required>
        </div>

        <div class="input-box">
            <span class="icon">♡</span>
            <select name="role" required>
                <option value="user">User</option>
                <option value="admin">Admin</option>
            </select>
        </div>

        <button type="submit" name="register" class="btn">♡ Register ♡</button>

        <p class="switch-text">Already have an account?
            <a href="#" onclick="showForm('login')">Login</a>
        </p>
    </form>

</div>


<script>
function showForm(formName) {
    document.getElementById('login').classList.remove('active');
    document.getElementById('register').classList.remove('active');
    document.getElementById(formName).classList.add('active');
}
</script>

</body>
</html>
