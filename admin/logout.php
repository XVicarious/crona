<?php
require "admin_functions.php";
if (sessionCheck()) {
    session_destroy();
    header('Location: ../');
}
