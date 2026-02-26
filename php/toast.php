<?php
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