<?php
// Only logged-in administrators can use Event Management.
require_once __DIR__ . '/../includes/auth.php';
require_role('Admin', '../');
