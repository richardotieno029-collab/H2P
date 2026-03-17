<?php
if (session_status()===PHP_SESSION_NONE){
    session_start();
}

// Ensure the global loader is available whenever we show toast messages.
// This is safe to include multiple times across pages.
if (!defined('H2P_LOADER_INCLUDED')) {
    define('H2P_LOADER_INCLUDED', true);
    include __DIR__ . '/includes/loader.php';
}

if (isset($_SESSION['toast'])):
    $toast = $_SESSION['toast'];
    unset($_SESSION['toast']); // show once
?>
    <div class="toast toast-<?= htmlspecialchars($toast['type']) ?>">
        <?= htmlspecialchars($toast['message']) ?>
    </div>

    <script>
        setTimeout(() => {
            const toast = document.querySelector('.toast');
            if (toast) toast.classList.add('hide');
        }, 3500);
    </script>
<?php endif; ?>