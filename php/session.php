<?php
// Start session only once
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}

/*
|--------------------------------------------------------------------------
| SAFE SESSION GETTERS
|--------------------------------------------------------------------------
*/
function current_user_id()
{
    return $_SESSION['user_id'] ?? null;
}

function current_user_role()
{
    return $_SESSION['user_role'] ?? null;
}

function current_user_name()
{
    return $_SESSION['user_name'] ?? 'Account';
}