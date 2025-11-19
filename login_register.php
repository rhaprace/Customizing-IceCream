<?php
session_start();
require "config.php"; 

if (isset($_POST['register'])) {

    $name = $_POST['text'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];


    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $check = $conn->query("SELECT * FROM users WHERE email='$email'");

    if ($check->num_rows > 0) {
        $_SESSION['register_error'] = "Email is already taken.";
        $_SESSION['active_form'] = "register";
        header("Location: index.php");
        exit();
    }


    $conn->query("INSERT INTO users (name, email, password, role) VALUES ('$name', '$email', '$hashedPassword', '$role')");

    $_SESSION['success_message'] = "Registration successful! You may now log in.";
    $_SESSION['active_form'] = "login";
    header("Location: index.php");
    exit();
}


if (isset($_POST['login'])) {

    $email = $_POST['email'];
    $password = $_POST['password'];

    $result = $conn->query("SELECT * FROM users WHERE email='$email'");

    if ($result->num_rows > 0) {

        $user = $result->fetch_assoc();

   
        if (password_verify($password, $user['password'])) {

            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            
            if ($user['role'] == 'admin') {
                header("Location: admin_page.php");
            } else {
                header("Location: user_page.php");
            }
            exit();
        }
    }

    $_SESSION['login_error'] = "Incorrect email or password.";
    $_SESSION['active_form'] = "login";
    header("Location: index.php");
    exit();
}

?>
