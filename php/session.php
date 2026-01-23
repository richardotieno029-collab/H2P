<?php
// Start session only once
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
|--------------------------------------------------------------------------
| SAFE SESSION GETTERS (OPTIONAL BUT CLEAN)
|--------------------------------------------------------------------------
*/
function current_user_id()
{
    return $_SESSION['user_id'] ?? null;
}

function current_user_role()
{
    return $_SESSION['role'] ?? null;
}

function current_user_name()
{
    return $_SESSION['user_name'] ?? 'Account';
}