<?php
if (!defined('ABSPATH')) exit;

// Load the routing logic
$section = sanitize_text_field($_GET['section'] ?? 'dashboard');

// Allowed sections
$allowed = ['dashboard', 'info', 'active', 'settings'];
if (!in_array($section, $allowed)) {
    $section = 'dashboard';
}

?>

<div class="tm-account-wrapper">

    <!-- MOBILE HEADER -->
    <div class="tm-mobile-header">
        <button id="tm-hamburger" class="tm-hamburger">
            <span></span><span></span><span></span>
        </button>
        <span class="tm-mobile-title">My Account</span>
    </div>

    <!-- SIDEBAR -->
    <div class="tm-account-sidebar">
        <ul>
            <li><a href="?section=dashboard" class="<?php echo $section=='dashboard'?'active':''; ?>"> sdfsds Dashboard</a></li>
            <li><a href="?section=info" class="<?php echo $section=='info'?'active':''; ?>">User Information</a></li>
            <li><a href="?section=active" class="<?php echo $section=='active'?'active':''; ?>">Active Trademarks</a></li>
            <li><a href="?section=settings" class="<?php echo $section=='settings'?'active':''; ?>">Settings</a></li>
        </ul>
    </div>


    <!-- CONTENT AREA -->
    <div class="tm-account-content">
        <?php include TM_ACCOUNT_PATH . "dashboard-router.php"; ?>
    </div>

</div>
