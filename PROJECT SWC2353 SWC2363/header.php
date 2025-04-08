<?php
if (!defined('SESSION_ALREADY_STARTED') && session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZeusTech Gadget Store</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <h1>ZeusTech</h1>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="products.php">Shop By Category</a></li>
                    <?php if (isLoggedIn()): ?>
                        <li><a href="cart.php">Cart</a></li>
                        <li><a href="profile.php">Profile</a></li>
                        <li><a href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php">Register</a></li>
                    <?php endif; ?>
                    <?php if (isAdmin()): ?>
                        <li><a href="adminlogin.php">Admin Panel</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    <main class="container">
