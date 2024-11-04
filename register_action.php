<?php
require 'AuthController.php';
$auth = new AuthController();
$result = $auth->register($_POST['username'], $_POST['password'], $_POST['email']);
echo $result;
?>
