<?php
// Practical 14: Role-Based Access Control helpers.
//   requireLogin() - not logged in  -> redirect to ../login.php
//   requireAdmin() - logged in but not Admin -> HTTP 403 page
// (They use the shared functions in includes/auth.php.)
require_once __DIR__ . '/../includes/auth.php';

function requireLogin(): void
{
    require_login('../');
}

function requireAdmin(): void
{
    require_role('Admin', '../');
}
