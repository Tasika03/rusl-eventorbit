<?php
session_start();
session_unset();
session_destroy();

setcookie('eo_user', '', time() - 3600, '/');

header('Location: login.php');
exit;