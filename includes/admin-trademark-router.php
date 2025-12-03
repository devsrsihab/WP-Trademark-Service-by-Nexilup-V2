<?php
if (!defined('ABSPATH')) exit;

/**
 * Trademark Admin Router
 * Handles different actions for trademark admin page
 */

// Get the action and ID
 $action = $_GET['action'] ?? '';
 $id = intval($_GET['id'] ?? 0);

 $tpl_base = WP_TMS_NEXILUP_PLUGIN_PATH . 'templates/admin/trademarks/';

// Only check capabilities for edit and view actions, not for the default list view
if ($action === 'edit' || $action === 'view') {
    // Check if user has the required capability
    if (!current_user_can('manage_options')) {
        wp_die('Sorry, you are not allowed to access this page.');
    }
}


// Route to the appropriate template
switch ($action) {
    case 'view':
        // View trademark details
        include $tpl_base . 'trademark-view.php';
        break;
        
    case 'edit':
        // Edit trademark
        include $tpl_base . 'trademark-edit.php';
        break;
        
    default:
        // Default: show the list (no capability check needed for search/filter)
        include $tpl_base . 'table.php';
        break;
}