<?php
include_once 'includes/config.php';

$_SESSION['user'] = null;
session_regenerate_id();
session_destroy();

header('location: index.php');
exit;
