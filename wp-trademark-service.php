<?php
/**
 * Plugin Name:       WP Trademark Service by Nexilup
 * Description:       Trademark country pricing, multi-step order forms, and WooCommerce integration.
 * Version:           1.0.0
 * Author:            Md. Sohanur Rahman Sihab
 * Text Domain:       wp-tms-nexilup
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Define constants
 */
define( 'WP_TMS_NEXILUP_VERSION', '1.0.0' );
define( 'WP_TMS_NEXILUP_PLUGIN_FILE', __FILE__ );
define( 'WP_TMS_NEXILUP_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'WP_TMS_NEXILUP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WP_TMS_NEXILUP_URL', plugin_dir_url( __FILE__ ) );
$tm_product_id = get_option('tm_master_product_id');

if ($tm_product_id) {
    define('TM_MASTER_PRODUCT_ID', intval($tm_product_id));
}

/**
 * Load activation/deactivation deps early
 */
require_once WP_TMS_NEXILUP_PLUGIN_PATH . 'includes/class-tm-activator.php';
require_once WP_TMS_NEXILUP_PLUGIN_PATH . 'includes/class-tm-deactivator.php';
require_once WP_TMS_NEXILUP_PLUGIN_PATH . 'includes/class-tm-database.php';
require_once WP_TMS_NEXILUP_PLUGIN_PATH . 'includes/class-tm-pages.php';
require_once WP_TMS_NEXILUP_PLUGIN_PATH . 'includes/class-tm-rewrite.php';

/**
 * Activation Hook
 */
function wp_tms_nexilup_activate() {

    WP_TMS_Activator::activate();

    // Create required pages once
    TM_Pages::create_required_pages();

    // Add rewrite rules and flush once
    TM_Rewrite::routes();
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'wp_tms_nexilup_activate' );

/**
 * Deactivation Hook
 */
function wp_tms_nexilup_deactivate() {
    WP_TM_Deactivator::deactivate();
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'wp_tms_nexilup_deactivate' );

/**
 * Bootstrap plugin
 */
function wp_tms_nexilup_init() {

    load_plugin_textdomain(
        'wp-tms-nexilup',
        false,
        dirname( plugin_basename( __FILE__ ) ) . '/languages'
    );

    require_once WP_TMS_NEXILUP_PLUGIN_PATH . 'includes/class-tm-admin.php';
    require_once WP_TMS_NEXILUP_PLUGIN_PATH . 'includes/class-tm-frontend.php';
    require_once WP_TMS_NEXILUP_PLUGIN_PATH . 'includes/class-tm-woocommerce.php';
    require_once WP_TMS_NEXILUP_PLUGIN_PATH . 'includes/class-tm-countries.php';
    require_once WP_TMS_NEXILUP_PLUGIN_PATH . 'includes/class-tm-country-prices.php';
    require_once WP_TMS_NEXILUP_PLUGIN_PATH . 'includes/class-tm-service-conditions.php';
    require_once WP_TMS_NEXILUP_PLUGIN_PATH . 'includes/class-tm-trademarks.php';
    require_once WP_TMS_NEXILUP_PLUGIN_PATH . 'includes/class-tm-ajax.php';
    require_once WP_TMS_NEXILUP_PLUGIN_PATH . 'includes/class-tm-service-form.php';
    require_once WP_TMS_NEXILUP_PLUGIN_PATH . 'includes/ajax-step-flow.php';
    require_once WP_TMS_NEXILUP_PLUGIN_PATH . 'includes/class-tm-upload.php';
    require_once WP_TMS_NEXILUP_PLUGIN_PATH . 'includes/class-tm-orders.php';

    TM_Admin::init();
    TM_Frontend::init();

    if ( class_exists( 'WooCommerce' ) ) {
        TM_WooCommerce::init();
    }

    TM_Countries::init();
    TM_Country_Prices::init();
    TM_Service_Conditions::init();
    TM_Trademarks::init();
    TM_Ajax::init();
    TM_Upload::init();


    // IMPORTANT: only one router system
    TM_Rewrite::init();
    TM_Pages::init();
}
add_action( 'plugins_loaded', 'wp_tms_nexilup_init' );

/**
 * Add settings link on plugin list
 */
function wp_tms_nexilup_settings_link( $links ) {
    $settings_url = admin_url( 'admin.php?page=tm-dashboard' );
    $settings_link = '<a href="' . esc_url( $settings_url ) . '">' . __( 'Dashboard', 'wp-tms-nexilup' ) . '</a>';
    array_unshift( $links, $settings_link );
    return $links;
}
add_filter(
    'plugin_action_links_' . plugin_basename( __FILE__ ),
    'wp_tms_nexilup_settings_link'
);

/**
 * Safe function to get order item meta with fallback
 */
function tm_get_meta_safe($item, $key) {
    if (is_callable([$item, 'get_meta'])) {
        return $item->get_meta($key, true);
    }
    return '';
}

// Hide the default WooCommerce custom meta display
add_filter('woocommerce_hidden_order_itemmeta', function($hidden) {
    $hidden[] = 'tm_country_id';
    $hidden[] = 'tm_country_iso';
    $hidden[] = 'tm_type';
    $hidden[] = 'tm_mark_text';
    $hidden[] = 'tm_classes';
    $hidden[] = 'tm_class_list';
    $hidden[] = 'tm_class_details';
    $hidden[] = 'tm_goods_services';
    $hidden[] = 'tm_priority_claim';
    $hidden[] = 'tm_poa_type';
    $hidden[] = 'tm_logo_id';
    $hidden[] = 'tm_logo_url';
    $hidden[] = 'tm_total_price';
    $hidden[] = 'tm_currency';
    $hidden[] = 'tm_step';
    $hidden[] = 'tm_additional_class';
    $hidden[] = 'tm_trademark_id';
    $hidden[] = 'tm_owner';
    $hidden[] = 'tm_raw_step_data';
    $hidden[] = 'tm_tm_additional_class';
    return $hidden;
});

add_action('woocommerce_after_order_itemmeta', 'tm_display_pretty_order_item_meta', 10, 3);

/**
 * Display trademark details in WooCommerce order admin
 */
function tm_display_pretty_order_item_meta($item_id, $item, $order) {

    // Only show for trademark items
    if (!$item->get_meta('tm_trademark_id', true)) {
        return;
    }

    // Get raw step data
    $raw_data = $item->get_meta('tm_raw_step_data', true);
    $data = json_decode($raw_data, true);
    
    // If raw data is not available, try to get individual meta fields
    if (empty($data)) {
        $data = [
            'country_id' => $item->get_meta('tm_country_id', true),
            'country_iso' => $item->get_meta('tm_country_iso', true),
            'tm_type' => $item->get_meta('tm_type', true),
            'mark_text' => $item->get_meta('tm_mark_text', true),
            'tm_from' => $item->get_meta('tm_from', true),
            'tm_goods' => $item->get_meta('tm_goods', true),
            'tm_logo_id' => $item->get_meta('tm_logo_id', true),
            'tm_logo_url' => $item->get_meta('tm_logo_url', true),
            'tm_additional_class' => $item->get_meta('tm_additional_class', true),
            'tm_priority' => $item->get_meta('tm_priority', true),
            'tm_poa' => $item->get_meta('tm_poa', true),
            'tm_class_count' => $item->get_meta('tm_class_count', true),
            'tm_class_list' => $item->get_meta('tm_class_list', true),
            'tm_class_details' => $item->get_meta('tm_class_details', true),
            'tm_total_price' => $item->get_meta('tm_total_price', true),
            'tm_currency' => $item->get_meta('tm_currency', true),
            'step' => $item->get_meta('tm_step', true)
        ];
    }
    
    // Get additional class mode flag
    $is_additional_class = isset($data['tm_additional_class']) && $data['tm_additional_class'] === '1';
        
    // Get class details - handle double encoding
    $class_list = [];
    $class_details = [];
    
    // Helper function to decode potentially double-encoded JSON
    $decode_json = function($value) {
        // First try to decode
        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            // If it's still a string, try decoding again
            if (is_string($decoded)) {
                $decoded_again = json_decode($decoded, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded_again)) {
                    return $decoded_again;
                }
            }
            // If it's already an array, return it
            if (is_array($decoded)) {
                return $decoded;
            }
        }
        return [];
    };
    
    // Handle class list
    if (isset($data['tm_class_list'])) {
        // Check if it's a JSON string or empty array
        if ($data['tm_class_list'] === '[]' || empty($data['tm_class_list'])) {
            // If class count is 1, set class list to [1]
            if (isset($data['tm_class_count']) && $data['tm_class_count'] == 1) {
                $class_list = [1];
            }
        } else {
            // Process JSON string
            $raw = $data['tm_class_list'];
            
            // Remove first-level wrapping quotes
            $step1 = trim($raw, '"');
            
            // Remove escape slashes
            $step2 = stripcslashes($step1);
            
            // Remove second-level wrapping quotes
            $step3 = trim($step2, '"');
            
            // Remove escape slashes again
            $step4 = stripcslashes($step3);
            
            // Decode JSON
            $decoded = json_decode($step4, true);
            
            if (is_array($decoded)) {
                $class_list = $decoded;
            }
        }
    }
    
    // Handle class details
// Handle class details
if (isset($data['tm_class_details'])) {
    // Check if it's a JSON string or empty array
    if ($data['tm_class_details'] !== '[]' && !empty($data['tm_class_details'])) {
        // Process JSON string with your specific approach
        $raw = $data['tm_class_details'];
        
        // Step 1: remove first-level wrapping quotes
        $step1 = trim($raw, '"');
        
        // Step 2: remove escape slashes
        $step2 = stripcslashes($step1);
        
        // Step 3: remove second-level wrapping quotes
        $step3 = trim($step2, '"');
        
        // Step 4: remove escape slashes again
        $step4 = stripcslashes($step3);
        
        // Final: decode JSON
        $decoded = json_decode($step4, true);
        if (is_array($decoded)) {
            $class_details = $decoded;
        }
    }
}
    
    // Start building the HTML
    echo '<div class="tm-order-details" style="
        margin: 20px 0;
        padding: 20px;
        background-color: #f9f9f9;
        border: 1px solid #ddd;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    ">
        <h3 style="
            margin: 0 0 15px 0;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
            color: #23282d;
            font-size: 16px;
        ">Trademark Details</h3>';
    
    // Basic Information Section
    echo '<div style="margin-bottom: 20px;">
        <h4 style="
            margin: 0 0 10px 0;
            color: #555;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        ">Basic Information</h4>
        <table class="tm-details-table" style="width: 100%; border-collapse: collapse;">';
    
    // Helper function to display a row
    $display_row = function($label, $value) {
        if (empty($value) && $value !== '0') return;
        echo "<tr style='border-bottom: 1px solid #eee;'>
            <th style='text-align: left; padding: 8px 10px 8px 0; width: 180px; font-weight: 600; color: #23282d; font-size: 13px;'>{$label}:</th>
            <td style='padding: 8px 0; color: #555; font-size: 13px;'>{$value}</td>
        </tr>";
    };
    
    // Display basic information
    $display_row('Country ID', isset($data['country_id']) ? $data['country_id'] : '');
    $display_row('Country ISO', isset($data['country_iso']) ? $data['country_iso'] : '');
    $display_row('Trademark Type', isset($data['tm_type']) ? ucfirst($data['tm_type']) : '');
    $display_row('Trademark Name', isset($data['mark_text']) ? $data['mark_text'] : '');
    $display_row('Form', isset($data['tm_from']) ? $data['tm_from'] : '');
    
    // Display logo if available
    if (!empty($data['tm_logo_url'])) {
        $display_row('Logo', "<img src='{$data['tm_logo_url']}' style='max-width: 120px; max-height: 120px; border: 1px solid #ddd; border-radius: 4px;'>");
    }
    
    echo '</table></div>';
    
    // Classes and Goods Section
    echo '<div style="margin-bottom: 20px;">
        <h4 style="
            margin: 0 0 10px 0;
            color: #555;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        ">Classes and Goods/Services</h4>
        <table class="tm-details-table" style="width: 100%; border-collapse: collapse;">';
    
    // Display class count
    $display_row('Class Count', isset($data['tm_class_count']) ? $data['tm_class_count'] : '');
    
    // Display class list if available
    if (!empty($class_list)) {
        $display_row('Class Numbers', implode(', ', $class_list));
    }
    
    // Handle different modes for goods/services
    if (!empty($class_details)) {
        // Additional class mode - goods are in class_details
        if (!empty($class_details)) {
            echo "<tr style='border-bottom: 1px solid #eee;'>
                <th style='text-align: left; padding: 8px 10px 8px 0; width: 180px; font-weight: 600; color: #23282d; font-size: 13px;'>Class Details:</th>
                <td style='padding: 8px 0; color: #555; font-size: 13px;'>";
            
            echo "<div style='margin: 0; padding: 0;'>";
            foreach ($class_details as $detail) {
                $class_num = isset($detail['class']) ? esc_html($detail['class']) : '';
                $goods = isset($detail['goods']) ? esc_html($detail['goods']) : '';
                
                echo "<div style='margin-bottom: 10px; padding: 10px; background-color: #fff; border: 1px solid #e1e1e1; border-radius: 4px;'>
                    <div style='font-weight: 600; margin-bottom: 5px; color: #32373c;'>Class {$class_num}: {$goods}</div>
                </div>";
            }
            echo "</div></td></tr>";
        }
    } else {
        // Standard mode - goods are in a single field
        $goods = isset($data['tm_goods']) ? $data['tm_goods'] : '';
        
        if (!empty($goods)) {
            // If we have class list, show goods with class numbers
            if (!empty($class_list)) {
                echo "<tr style='border-bottom: 1px solid #eee;'>
                    <th style='text-align: left; padding: 8px 10px 8px 0; width: 180px; font-weight: 600; color: #23282d; font-size: 13px;'>Goods/Services:</th>
                    <td style='padding: 8px 0; color: #555; font-size: 13px;'>";
                
                echo "<div style='margin: 0; padding: 0;'>";
                foreach ($class_list as $class_num) {
                    echo "<div style='margin-bottom: 10px; padding: 10px; background-color: #fff; border: 1px solid #e1e1e1; border-radius: 4px;'>
                        <div style='font-weight: 600; margin-bottom: 5px; color: #32373c;'>Class {$class_num}:</div>
                        <div style='color: #555;'>" . nl2br(esc_html($goods)) . "</div>
                    </div>";
                }
                echo "</div></td></tr>";
            } else {
                // No class list, just show goods
                $display_row('Goods/Services', nl2br(esc_html($goods)));
            }
        }
    }
    
    echo '</table></div>';
    
    // Additional Options Section
    echo '<div style="margin-bottom: 20px;">
        <h4 style="
            margin: 0 0 10px 0;
            color: #555;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        ">Additional Options</h4>
        <table class="tm-details-table" style="width: 100%; border-collapse: collapse;">';
    
    // Display additional options
    $priority_claim = isset($data['tm_priority']) ? $data['tm_priority'] : '0';
    $display_row('Priority Claim', $priority_claim === '1' ? 'Yes' : 'No');
    
    $poa_type = isset($data['tm_poa']) ? $data['tm_poa'] : 'normal';
    $display_row('POA Type', ucfirst($poa_type));
    
    $display_row('Additional Class Mode', $is_additional_class ? 'Yes' : 'No');
    
    echo '</table></div>';
    
    // Pricing Section
    echo '<div style="margin-bottom: 20px;">
        <h4 style="
            margin: 0 0 10px 0;
            color: #555;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        ">Pricing</h4>
        <table class="tm-details-table" style="width: 100%; border-collapse: collapse;">';
    
    // Display pricing information
    $total_price = isset($data['tm_total_price']) ? $data['tm_total_price'] : '';
    $currency = isset($data['tm_currency']) ? $data['tm_currency'] : '';
    $display_row('Total Price', !empty($total_price) ? number_format((float)$total_price, 2) . ' ' . $currency : 'Not calculated');
    
    echo '</table></div>';
    
    // System Information Section
    echo '<div>
        <h4 style="
            margin: 0 0 10px 0;
            color: #555;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        ">System Information</h4>
        <table class="tm-details-table" style="width: 100%; border-collapse: collapse;">';
    
    // Display system information
    $display_row('Step', isset($data['step']) ? $data['step'] : '');
    $display_row('Logo ID', isset($data['tm_logo_id']) ? $data['tm_logo_id'] : '');
    
    echo '</table></div>';
    
    // Close the container
    echo '</div>';
}


