<?php
include 'admin_functions.php';
sessionCheck();
if ($_GET['action'] === 'userId') {
    echo $_SESSION['userId'];
} elseif ($_GET['action'] === 'operationMode') {
    echo $_SESSION['operationMode'];
}
