<?php
require_once __DIR__ . '/../config/session.php';

$_SESSION = [];
session_destroy();

header("Location: /WebTech/index.php");
exit;
