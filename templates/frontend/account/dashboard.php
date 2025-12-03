<?php 
$user = wp_get_current_user();
$user_id = $user->ID;

global $wpdb;
$table = $wpdb->prefix . "tm_trademarks";

// Count user trademarks
$active_count = $wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM $table WHERE user_id = %d AND status IN ('processing','paid','in_process')",
    $user_id
));

$account_number = $user_id + 10000;
$created = date('m/d/Y', strtotime($user->user_registered));
?>

<div class="tm-dashboard-boxes">
    <div class="item">
        <strong><?php echo $active_count; ?></strong>
        <span>Total Active</span>
    </div>
    <div class="item">
        <strong><?php echo esc_html($user->display_name); ?></strong>
        <span>User Name</span>
    </div>
    <div class="item">
        <strong><?php echo $account_number; ?></strong>
        <span>Account Number</span>
    </div>
    <div class="item">
        <strong><?php echo $created; ?></strong>
        <span>Account Created</span>
    </div>
</div>
