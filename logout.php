<?php
require_once __DIR__ . '/includes/auth.php';
destroy_session();
header('Location: login.php?loggedout=1');
exit;
