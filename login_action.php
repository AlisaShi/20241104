<?php
require 'AuthController.php';
$auth = new AuthController();
$result = $auth->login($_POST['username'], $_POST['password']);
echo $result;
?>
