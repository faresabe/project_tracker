<?php
require_once '../authentication/db_connection.php';

destroyUserSession();
header("Location: ../authentication/sign_in.php");
exit();
?>