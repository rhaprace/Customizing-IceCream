<?php

session_start();
require "config.php";

if (isset($_POST['register'])) {
    $name = $_POST['text'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $stmt->close();
        $_SESSION['register_error'] = "Email is already taken.";
        $_SESSION['active_form'] = "register";
        header("Location: index.php");
        exit();
    }
    $stmt->close();

    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $hashedPassword, $role);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Registration successful! You may now log in.";
        $_SESSION['active_form'] = "login";
    } else {
        $_SESSION['register_error'] = "Registration failed. Please try again.";
        $_SESSION['active_form'] = "register";
    }
    $stmt->close();

    header("Location: index.php");
    exit();
}

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $stmt->close();

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
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
    } else {
        $stmt->close();
    }

    $_SESSION['login_error'] = "Incorrect email or password.";
    $_SESSION['active_form'] = "login";
    header("Location: index.php");
    exit();
}
