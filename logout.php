<?php
include 'includes/db.php'; // nạp class Auth & session

use App\Auth;

Auth::start();
session_unset();   // xoá toàn bộ biến session
session_destroy(); // huỷ phiên

header('Location: account.php');
exit;
