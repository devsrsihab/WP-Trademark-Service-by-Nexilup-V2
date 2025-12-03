<?php
if (!defined('ABSPATH')) exit;

$user_id = get_current_user_id();
$section = sanitize_text_field($_GET['section'] ?? 'dashboard');

// Allowed sections
$allowed = ['dashboard', 'info', 'active', 'settings'];

if (!in_array($section, $allowed)) {
    $section = 'dashboard';
}
?>

<div class="tm-account-wrapper">

    <!-- Sidebar -->
    <?php include __DIR__ . '/sidebar.php'; ?>

    <!-- Page Content -->
    <div class="tm-account-content">
        <?php
        switch ($section) {

            case 'info':
                include __DIR__ . '/user-information.php';
                break;

            case 'active':
                include __DIR__ . '/active-trademarks.php';
                break;

            case 'settings':
                include __DIR__ . '/settings.php';
                break;

            default:
                include __DIR__ . '/dashboard.php';
        }
        ?>
    </div>

</div>
