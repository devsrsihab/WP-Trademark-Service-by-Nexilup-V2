<?php
if (!defined('ABSPATH')) exit;

class TM_Frontend {

    public static function init() {
        add_shortcode('tm_country_table', [__CLASS__, 'shortcode_country_table']);
        add_shortcode('tm_country_single', [__CLASS__, 'shortcode_country_single']);
        add_shortcode('tm_service_form', [__CLASS__, 'render_service_form']);
        add_shortcode('tm_my_trademarks', [__CLASS__, 'shortcode_my_trademarks']);

        add_action('wp_ajax_tm_load_service_conditions', 'tm_load_service_conditions');
        add_action('wp_ajax_nopriv_tm_load_service_conditions', 'tm_load_service_conditions');

        add_action('wp_enqueue_scripts', [__CLASS__, 'register_scripts']);
        add_action("wp_ajax_tm_filter_country_table", [__CLASS__, "ajax_filter_table"]);
        add_action("wp_ajax_nopriv_tm_filter_country_table", [__CLASS__, "ajax_filter_table"]);

        add_action('wp_ajax_tm_get_country_price', [__CLASS__,'tm_get_country_price']);
        add_action('wp_ajax_nopriv_tm_get_country_price', [__CLASS__,'tm_get_country_price']);

        add_action('wp_ajax_tm_get_country_price_step1', [TM_Frontend::class, 'tm_get_country_price_step1']);
        add_action('wp_ajax_nopriv_tm_get_country_price_step1', [TM_Frontend::class, 'tm_get_country_price_step1']);

        // user dashboard
        if (!defined('TM_ACCOUNT_PATH')) {
            define('TM_ACCOUNT_PATH', WP_TMS_NEXILUP_PLUGIN_PATH . 'templates/frontend/account/');
        }
        add_shortcode('tm_account', [__CLASS__, 'shortcode_account_dashboard']);

        add_action('init', [__CLASS__, 'handle_profile_update']);

    }

    // register script
    public static function register_scripts() {

        wp_enqueue_script('tm-country-filter-js');

        wp_localize_script('tm-country-filter-js', 'tm_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('tm_nonce')
        ]);



        wp_register_style(
            'tm-step3-css',
            WP_TMS_NEXILUP_URL . 'assets/css/frontend-step3.css',
            ['tm-frontend-css'],
            WP_TMS_NEXILUP_VERSION
        );

        wp_register_script(
            'tm-step3-js',
            WP_TMS_NEXILUP_URL . 'assets/js/frontend-step3.js',
            ['jquery'],
            WP_TMS_NEXILUP_VERSION,
            true
        );

        wp_enqueue_style(
            'tm-global-css',
            WP_TMS_NEXILUP_PLUGIN_URL . 'assets/css/global.css',
            [],
            WP_TMS_NEXILUP_VERSION
        );


        wp_register_style(
            'tm-frontend-css',
            WP_TMS_NEXILUP_URL . 'assets/css/frontend.css',
            [],
            WP_TMS_NEXILUP_VERSION
        );

        wp_register_style(
            'tm-frontend-flag',
            WP_TMS_NEXILUP_URL . 'assets/css/country-flag.css',
            [],
            WP_TMS_NEXILUP_VERSION
        );

        wp_register_style(
            'tm-country-single-css',
            WP_TMS_NEXILUP_URL . 'assets/css/country-single.css',
            ['tm-frontend-css'],
            WP_TMS_NEXILUP_VERSION
        );

        wp_register_style(
            'tm-step1-css',
            WP_TMS_NEXILUP_URL . 'assets/css/frontend-step1.css',
            ['tm-frontend-css'],
            WP_TMS_NEXILUP_VERSION
        );

        wp_register_style(
            'tm-step2-css',
            WP_TMS_NEXILUP_URL . 'assets/css/frontend-step2.css',
            ['tm-frontend-css'],
            WP_TMS_NEXILUP_VERSION
        );
        wp_register_style(
            'tm-country-table-css',
            WP_TMS_NEXILUP_URL . 'assets/css/country-table-pro.css',
            [],
            WP_TMS_NEXILUP_VERSION
        );

        wp_register_style(
            'tm-frontend-modal-css',
            WP_TMS_NEXILUP_URL . 'assets/css/frontend-modal.css',
            [],
            WP_TMS_NEXILUP_VERSION
        );

        wp_register_script(
            'tm-prices-modal-js',
            WP_TMS_NEXILUP_URL . 'assets/js/frontend-prices-modal.js',
            ['jquery'],
            WP_TMS_NEXILUP_VERSION,
            true
        );

        wp_register_script(
            'tm-step1-js',
            WP_TMS_NEXILUP_URL . 'assets/js/frontend-step1.js',
            ['jquery'],
            WP_TMS_NEXILUP_VERSION,
            true
        );

        wp_register_script(
            'tm-step2-js',
            WP_TMS_NEXILUP_URL . 'assets/js/frontend-step2.js',
            ['jquery'],
            WP_TMS_NEXILUP_VERSION,
            true
        );

        wp_localize_script('tm-step2-js', 'tm_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('tm_nonce')
        ]);

        wp_register_script(
            'tm-my-trademarks-js',
            WP_TMS_NEXILUP_URL . 'assets/js/frontend-my-trademarks.js',
            ['jquery'],
            WP_TMS_NEXILUP_VERSION,
            true
        );

        wp_register_style(
            'tm-account-css',
            WP_TMS_NEXILUP_URL . 'assets/css/account.css',
            [],
            WP_TMS_NEXILUP_VERSION
        );

        wp_register_script(
            'tm-account-js',
            WP_TMS_NEXILUP_URL . 'assets/js/account.js',
            ['jquery'],
            WP_TMS_NEXILUP_VERSION,
            true
        );

    }


    public static function shortcode_account_dashboard() {

        if (!is_user_logged_in()) {
            return "<p class='tm-error'>You must log in to access your trademark dashboard.</p>";
        }

        wp_enqueue_style('tm-account-css');
        wp_enqueue_script('tm-account-js');

        // Load template output
        ob_start();
        include WP_TMS_NEXILUP_PLUGIN_PATH . 'templates/frontend/account/dashboard-router.php';
        return ob_get_clean();
    }



    public static function tm_get_country_price_step1() {

        $country_id = intval($_POST['country_id']);
        $type = sanitize_text_field($_POST['type']);

        $row = TM_Country_Prices::get_step1_price_row($country_id);

        if (!$row) {
            wp_send_json_error(['message' => 'No price row found']);
        }

        wp_send_json_success([
            'first' => floatval($row->first_class_fee),
            'add'   => floatval($row->additional_class_fee),
            'currency' => $row->currency ?: 'USD'
        ]);
    }




    public static function tm_get_country_price() {

        if (!isset($_POST['country_id']) || !isset($_POST['type'])) {
            wp_send_json_error(['message' => 'Missing data']);
        }

        global $wpdb;
        $country_id = intval($_POST['country_id']);
        $type       = sanitize_text_field($_POST['type']);

        // Always fallback to word
        if ($type !== 'word') {
            $type = 'word';
        }

        // Get price row
        $price_row = TM_Country_Prices::get_priority_price_row($country_id, $type);

        if (!$price_row) {
            wp_send_json_error(['message' => 'No price row']);
        }

        $first = floatval($price_row->first_class_fee);
        $add   = floatval($price_row->additional_class_fee);

        wp_send_json_success([
            'step1_one' => $first,
            'step1_add' => $add
        ]);
    }




    function tm_load_service_conditions() {
        check_ajax_referer('tm_nonce', 'nonce');

        global $wpdb;
        $table = TM_Database::table_name('service_conditions');
        $country_id = intval($_POST['country_id']);

        $rows = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM $table WHERE country_id = %d ORDER BY step_number ASC", $country_id)
        );

        ob_start();
        if ($rows) {
            foreach ($rows as $sc) {
                echo "<h3>Step {$sc->step_number}</h3>";
                echo "<div class='sc-block'>" . wp_kses_post($sc->content) . "</div>";
            }
        } else {
            echo "<p>No service conditions available.</p>";
        }

        wp_send_json_success(['html' => ob_get_clean()]);
    }

 

    public static function shortcode_country_table($atts) {
        $atts = shortcode_atts([
            'per_page'    => 10,
            'single_page' => ''
        ], $atts);

        wp_enqueue_style('tm-frontend-flag'); 
        wp_enqueue_style('tm-country-table-css');

        global $wpdb;
        $table = TM_Database::table_name('countries');

        $search = isset($_GET['tm_search']) ? sanitize_text_field($_GET['tm_search']) : '';
        $paged  = isset($_GET['tm_page']) ? max(1, intval($_GET['tm_page'])) : 1;
        $per_page = intval($atts['per_page']);

        $where = "WHERE status = 1";
        $params = [];

        if ($search) {
            $where .= " AND country_name LIKE %s";
            $params[] = '%' . $wpdb->esc_like($search) . '%';
        }

        $total_sql = "SELECT COUNT(*) FROM $table $where";
        $total = $params ? $wpdb->get_var($wpdb->prepare($total_sql, ...$params)) : $wpdb->get_var($total_sql);

        $max_pages = ceil($total / $per_page);
        $offset = ($paged - 1) * $per_page;

        if ($params) {
            $params[] = $per_page;
            $params[] = $offset;
            $sql = $wpdb->prepare(
                "SELECT * FROM $table $where ORDER BY country_name ASC LIMIT %d OFFSET %d",
                ...$params
            );
        } else {
            $sql = $wpdb->prepare(
                "SELECT * FROM $table $where ORDER BY country_name ASC LIMIT %d OFFSET %d",
                $per_page,
                $offset
            );
        }

        $countries = $wpdb->get_results($sql);

        ob_start();
        $single_page = trailingslashit($atts['single_page']);
        include WP_TMS_NEXILUP_PLUGIN_PATH . 'templates/frontend/country-table.php';
        return ob_get_clean();
    }

    public static function shortcode_country_single($atts) {

        wp_enqueue_style('tm-frontend-css');
        wp_enqueue_style('tm-frontend-modal-css');
        wp_enqueue_style('tm-country-single-css');
        wp_enqueue_script('tm-prices-modal-js');

        /* -----------------------------------
        1. Validate country parameter
        ----------------------------------- */
        if (!isset($_GET['country'])) {
            return "<p class='tm-error'>No country selected.</p>";
        }

        $iso = sanitize_text_field($_GET['country']);

        global $wpdb;
        $countries_table = TM_Database::table_name('countries');
        $prices_table    = TM_Database::table_name('country_prices');
        $sc_table        = TM_Database::table_name('service_conditions');

        /* -----------------------------------
        2. Load country
        ----------------------------------- */
        $country = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $countries_table WHERE iso_code = %s", $iso)
        );

        if (!$country) {
            return "<p class='tm-error'>Invalid country.</p>";
        }

        /* -----------------------------------
        3. Load single price row
        ----------------------------------- */
        $price = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $prices_table WHERE country_id = %d LIMIT 1", $country->id)
        );

        if (!$price) {
            return "<p class='tm-error'>No pricing available for this country.</p>";
        }

        /* -----------------------------------
        4. Build pricing model (same logic as table)
        ----------------------------------- */
        $remark  = trim($price->general_remarks);
        $first   = floatval($price->first_class_fee);
        $add     = floatval($price->additional_class_fee);
        $currency = $price->currency ?: 'USD';

        if (strpos($remark, 'filing_') === 0) {
            $fees = [
                's1_one' => $first,
                's1_add' => $add,
                's2_one' => $add,
                's2_add' => $add,
                's3_one' => $add,
                's3_add' => $add,
            ];
        } elseif (strpos($remark, 'registration_') === 0) {
            $fees = [
                's1_one' => $add,
                's1_add' => $add,
                's2_one' => $add,
                's2_add' => $add,
                's3_one' => $add,
                's3_add' => $add,
            ];
        } else {
            $fees = [
                's1_one' => $first,
                's1_add' => $add,
                's2_one' => $add,
                's2_add' => $add,
                's3_one' => $add,
                's3_add' => $add,
            ];
        }

        /* -----------------------------------
        5. Load ONE service condition row
        ----------------------------------- */
        $service_condition = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $sc_table WHERE country_id = %d LIMIT 1", $country->id)
        );

        $has_service_condition = ($service_condition && trim($service_condition->content) !== "");

        /* -----------------------------------
        6. Pass data to template
        ----------------------------------- */
        ob_start();

        $p        = $price;
        $sc_item  = $service_condition;
        $has_sc   = $has_service_condition;
        $fee_data = $fees;

        include WP_TMS_NEXILUP_PLUGIN_PATH . 'templates/frontend/country-single.php';

        return ob_get_clean();
    }

    public static function handle_profile_update() {

        // ================================
        // SAFE REDIRECT URL
        // ================================
        $redirect_url = wp_get_referer() ? wp_get_referer() : site_url('/tm-account/?section=settings');

        // =====================================================
        // 1. UPDATE PROFILE INFORMATION
        // =====================================================
        if (isset($_POST['tm_update_profile'])) {

            if (!isset($_POST['tm_profile_nonce']) ||
                !wp_verify_nonce($_POST['tm_profile_nonce'], 'tm_update_profile')) {

                wp_redirect(add_query_arg('tm_error', 'invalid_nonce', $redirect_url));
                exit;
            }

            $user_id = get_current_user_id();

            wp_update_user([
                'ID'           => $user_id,
                'display_name' => sanitize_text_field($_POST['full_name']),
            ]);

            update_user_meta($user_id, 'company', sanitize_text_field($_POST['company']));
            update_user_meta($user_id, 'phone', sanitize_text_field($_POST['phone']));
            update_user_meta($user_id, 'address', sanitize_text_field($_POST['address']));
            update_user_meta($user_id, 'city', sanitize_text_field($_POST['city']));
            update_user_meta($user_id, 'state', sanitize_text_field($_POST['state']));
            update_user_meta($user_id, 'zip', sanitize_text_field($_POST['zip']));
            update_user_meta($user_id, 'country', sanitize_text_field($_POST['country']));

            wp_redirect(add_query_arg('tm_updated', 'profile', $redirect_url));
            exit;
        }



        // =====================================================
        // 2. UPDATE PASSWORD
        // =====================================================
        if (isset($_POST['tm_update_password'])) {

            // Validate nonce
            if (!isset($_POST['tm_password_nonce']) ||
                !wp_verify_nonce($_POST['tm_password_nonce'], 'tm_update_password')) {

                wp_redirect(add_query_arg('tm_error', 'invalid_nonce', $redirect_url));
                exit;
            }

            $user = wp_get_current_user();

            $current = sanitize_text_field($_POST['current_password']);
            $new     = sanitize_text_field($_POST['new_password']);
            $confirm = sanitize_text_field($_POST['confirm_password']);

            // Validate Current Password
            if (!wp_check_password($current, $user->user_pass, $user->ID)) {
                wp_redirect(add_query_arg('tm_error', 'wrong_password', $redirect_url));
                exit;
            }

            // Validate Matching Passwords
            if ($new !== $confirm) {
                wp_redirect(add_query_arg('tm_error', 'password_mismatch', $redirect_url));
                exit;
            }

            // =====================================================
            // UPDATE PASSWORD WITHOUT LOGGING OUT
            // =====================================================

            wp_set_password($new, $user->ID);

            // Auto-login user again (prevents logout)
            wp_set_current_user($user->ID);
            wp_set_auth_cookie($user->ID, true, false);

            // Fire login hook (important for WooCommerce + sessions)
            do_action('wp_login', $user->user_login, $user);

            // Redirect with success
            wp_redirect(add_query_arg('tm_updated', 'password', $redirect_url));
            exit;
        }

    }






    /**
     * MULTISTEP ORDER FORM
     * Step1 = comprehensive study form
     * Step2 = confirm order + payment
     * Step3 = WC Thankyou page
     */
    public static function render_service_form($atts) {

        wp_enqueue_style('tm-frontend-css');
        wp_enqueue_style('tm-frontend-flag');

        $country_code = isset($_GET['country']) ? sanitize_text_field($_GET['country']) : '';
        $step = TM_Service_Form::detect_initial_step();

        if (!$country_code) {
            return "<p class='tm-error'>No country selected</p>";
        }

        global $wpdb;
        $country = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}tm_countries WHERE iso_code = %s",
                $country_code
            )
        );

        if (!$country) {
            return "<p class='tm-error'>Invalid country.</p>";
        }

        $js_data = [
            'ajax_url'    => admin_url('admin-ajax.php'),
            'nonce'       => wp_create_nonce('tm_nonce'),
            'country_id'  => (int)$country->id,
            'country_iso' => $country->iso_code,
            'step'        => $step,
            'tm_additional_class'=> isset($_GET['tm_additional_class']) ? (int) $_GET['tm_additional_class'] : 0,

        ];

        ob_start();

        if ($step === 1) {
            wp_enqueue_style('tm-step1-css');
            wp_enqueue_script('tm-step1-js');
            wp_localize_script('tm-step1-js', 'tm_ajax', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('tm_nonce')
            ]);
            
    // ⭐ ADD THIS LINE
    $js_data['step2_url'] = site_url('/tm/trademark-registration/order-form?country='.$country->iso_code);


            wp_localize_script('tm-step1-js', 'TM_GLOBAL', $js_data);
            include WP_TMS_NEXILUP_PLUGIN_PATH . 'templates/frontend/step1.php';

        } elseif ($step === 2) {
            wp_enqueue_style('tm-step2-css');
            wp_enqueue_script('tm-step2-js');
            wp_localize_script('tm-step2-js', 'TM_GLOBAL', $js_data);
            include WP_TMS_NEXILUP_PLUGIN_PATH . 'templates/frontend/step2.php';

        
        } elseif ($step === 3) {

            wp_enqueue_style('tm-step3-css');
            wp_enqueue_script('tm-step3-js');

              // Correct GET param usage
            $order_id  = intval($_GET['tm_order_received'] ?? 0);
            $order_key = sanitize_text_field($_GET['key'] ?? '');


            $js_data['order_id'] =  $order_id;
            $js_data['order_key'] =  $order_key;

            wp_localize_script('tm-step3-js', 'TM_GLOBAL', $js_data);

            include WP_TMS_NEXILUP_PLUGIN_PATH . 'templates/frontend/step3.php';

        } 

        
        else {
            echo "<p class='tm-error'>Invalid step.</p>";
        }

        return ob_get_clean();
    }

    public static function shortcode_my_trademarks() {
        if (!is_user_logged_in()) {
            return "<p class='tm-error'>Please login to view your trademark dashboard.</p>";
        }

        wp_enqueue_style('tm-frontend-css');
        wp_enqueue_script('tm-my-trademarks-js');

        ob_start();
        include WP_TMS_NEXILUP_PLUGIN_PATH . 'templates/frontend/my-trademarks.php';
        return ob_get_clean();
    }
}
