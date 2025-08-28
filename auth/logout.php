<?php
require_once '../config/auth.php';

// Logout user
logoutUser();

// Redirect to login page with success message
header('Location: login.php?message=You have been logged out successfully');
exit;
?>