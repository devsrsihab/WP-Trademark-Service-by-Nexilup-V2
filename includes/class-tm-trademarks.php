<?php
if (!defined('ABSPATH')) exit;

class TM_Trademarks {

    public static function init() {
        add_action('wp_ajax_tm_admin_get_trademark', [ __CLASS__, 'ajax_admin_get_trademark' ]);
        add_action('wp_ajax_tm_admin_update_status', [ __CLASS__, 'ajax_admin_update_status' ]);
        add_action('wp_ajax_tm_admin_upload_doc', [ __CLASS__, 'ajax_admin_upload_doc' ]);
        add_action('wp_ajax_tm_admin_get_docs', [ __CLASS__, 'ajax_admin_get_docs' ]);
        add_action('wp_ajax_tm_admin_update_trademark', [ __CLASS__, 'ajax_admin_update_trademark' ]);
        add_action('wp_ajax_tm_user_view_trademark', ['TM_Trademarks', 'ajax_user_view_trademark']);
        add_action('wp_ajax_nopriv_tm_user_view_trademark', ['TM_Trademarks', 'ajax_user_view_trademark']);
        add_action('wp_ajax_tm_user_get_docs', ['TM_Trademarks', 'ajax_user_get_docs']);

        add_action('admin_post_tm_admin_update_trademark', [__CLASS__, 'update_trademark_page_submit']);
    }

    public static function table() {
        global $wpdb;
        return $wpdb->prefix . "tm_trademarks";
    }

    public static function ajax_user_get_docs() {
        check_ajax_referer('tm_user_trademark_nonce', 'nonce');

        $id = intval($_POST['id']);
        $user_id = get_current_user_id();

        $t = self::get($id, $user_id);
        if (!$t) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        $docs = TM_Trademarks::get_documents($id);

        ob_start();
        include WP_TMS_NEXILUP_PLUGIN_PATH . 'templates/frontend/user-documents-list.php';
        $html = ob_get_clean();

        wp_send_json_success(['html' => $html]);
    }

    public static function ajax_user_view_trademark() {
        check_ajax_referer('tm_user_trademark_nonce', 'nonce');

        $id = intval($_POST['id']);
        $user_id = get_current_user_id();

        $t = self::get($id, $user_id);
        if (!$t) {
            wp_send_json_error(['message' => 'Unauthorized or not found']);
        }

        ob_start();
        include WP_TMS_NEXILUP_PLUGIN_PATH . 'templates/frontend/trademark-user-details.php';
        $html = ob_get_clean();

        wp_send_json_success(['html' => $html]);
    }

    public static function create_from_order($order_id, $item, $tm)
    {
        global $wpdb;
        $table = self::table();


        // Get WooCommerce order
        $order = wc_get_order($order_id);

        if (!$order) {
            $order_status = 'pending';
        } else {
            $order_status = $order->get_status();
        }


        // Map Woo → Trademark status
        $map = [
            'pending'    => 'pending_payment',
            'processing' => 'processing',
            'completed'  => 'paid',
            'on-hold'    => 'on_hold',
            'cancelled'  => 'cancelled',
            'refunded'   => 'refunded',
            'failed'     => 'failed',
        ];
        

        $trademark_status = $map[$order_status] ?? 'pending_payment';



        // ORIGINAL LOGIC BELOW
        $country_id  = intval($tm['raw_step_data']['country_id'] ?? 0);
        $country_iso = sanitize_text_field($tm['raw_step_data']['country_iso'] ?? '');

        if (!$country_id && $country_iso) {
            $country_id = TM_Countries::get_id_by_iso($country_iso);
        }

        if (!$country_id) {
            return 0;
        }

        $mark_text   = sanitize_text_field($tm['raw_step_data']['mark_text'] ?? '');
        $type        = sanitize_text_field($tm['raw_step_data']['tm_type'] ?? 'word');
        $classes     = intval($tm['raw_step_data']['tm_class_count'] ?? 1);
        if ($classes < 1) $classes = 1;

        $class_list = $tm['raw_step_data']['tm_class_list'] ?? [];
        if (!is_array($class_list)) {
            $class_list = json_decode($class_list, true) ?: [];
        }

        $class_details = $tm['raw_step_data']['tm_class_details'] ?? [];
        if (!is_array($class_details)) {
            $class_details = json_decode($class_details, true) ?: [];
        }

        $extra_class_count = max(0, $classes - 1);

        $goods          = sanitize_text_field($tm['raw_step_data']['tm_goods'] ?? '');
        $priority       = intval($tm['raw_step_data']['tm_priority'] ?? 0);
        $poa            = sanitize_text_field($tm['poa_type'] ?? 'normal');

        $logo_id        = intval($tm['raw_step_data']['tm_logo_id'] ?? 0);
        $logo_url       = sanitize_text_field($tm['raw_step_data']['tm_logo_url'] ?? '');

        $total_price    = floatval($tm['raw_step_data']['tm_total_price'] ?? 0);
        $currency       = sanitize_text_field($tm['currency'] ?? 'USD');

        $raw_owner     = wp_json_encode($tm['owner'] ?? []);
        $raw_step_data = wp_json_encode($tm['raw_step_data'] ?? $tm ?? []);

        // INSERT TRADEMARK RECORD
        $wpdb->insert($table, [
            'user_id'           => get_current_user_id(),
            'country_id'        => $country_id,
            'country_iso'       => $country_iso,

            'service_step'      => intval($tm['step'] ?? 1),
            'trademark_type'    => $type,
            'mark_text'         => $mark_text,

            'class_count'       => $classes,
            'extra_class_count' => $extra_class_count,

            'class_list'        => wp_json_encode($class_list),
            'class_details'     => wp_json_encode($class_details),

            'goods_services'    => $goods,
            'priority_claim'    => $priority,
            'poa_type'          => $poa,

            'has_logo'          => $logo_id ? 1 : 0,
            'logo_id'           => $logo_id,
            'logo_url'          => $logo_url,

            'final_price'       => $total_price,
            'currency'          => $currency,

            'owner_profile_id'  => 0,
            'woo_order_id'      => $order_id,

            // FINAL MAPPED STATUS
            'status'            => $trademark_status,

            'raw_owner'         => $raw_owner,
            'raw_step_data'     => $raw_step_data,

            'created_at'        => current_time('mysql'),
            'updated_at'        => current_time('mysql'),
        ]);


        return $wpdb->insert_id;
    }

        public static function update_trademark_page_submit() {

            // Verify nonce
            if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'tm_admin_trademark_nonce')) {
                wp_die('Security check failed');
            }

            $id = intval($_POST['trademark_id']);

            // Basic update fields
            $update_data = [
                'trademark_type'  => sanitize_text_field($_POST['trademark_type']),
                'mark_text'       => sanitize_text_field($_POST['mark_text']),
                'class_count'     => intval($_POST['class_count']),
                'priority_claim'  => intval($_POST['priority_claim']),
                'poa_type'        => sanitize_text_field($_POST['poa_type']),
                'final_price'     => floatval($_POST['final_price']),
                'status'          => sanitize_text_field($_POST['status']),
            ];

            // Goods/services textarea (only exists if class_details is EMPTY)
            if (isset($_POST['goods_services'])) {
                $update_data['goods_services'] = sanitize_textarea_field($_POST['goods_services']);
            }

            // CLASS LIST — convert CSV to array
            if (!empty($_POST['class_list'])) {
                $list = array_map('trim', explode(',', $_POST['class_list']));
                $update_data['class_list'] = $list;
            } else {
                $update_data['class_list'] = [];
            }

            // CLASS DETAILS — multi-row structure
            if (!empty($_POST['class_details']) && is_array($_POST['class_details'])) {
                $clean_details = [];

                foreach ($_POST['class_details'] as $index => $row) {
                    if (empty($row['class']) && empty($row['goods'])) continue;

                    $clean_details[$index] = [
                        'class' => sanitize_text_field($row['class']),
                        'goods' => sanitize_textarea_field($row['goods']),
                    ];
                }

                $update_data['class_details'] = $clean_details;
            } else {
                $update_data['class_details'] = [];
            }

            // Update trademark in DB
            TM_Trademarks::update($id, $update_data);

            // Redirect to view page
            wp_redirect(admin_url("admin.php?page=tm-trademarks&action=view&id=$id"));
            exit;
        }








    public static function get_user_trademarks($user_id) {
        global $wpdb;
        $table = self::table();
        $countries = TM_Database::table_name('countries');

        return $wpdb->get_results(
            $wpdb->prepare(
                "
                SELECT t.*, c.country_name 
                FROM $table t
                LEFT JOIN $countries c ON c.id = t.country_id
                WHERE t.user_id = %d
                ORDER BY t.created_at DESC
                ",
                $user_id
            )
        );
    }

    public static function get($id, $user_id = 0) {
        global $wpdb;
        $table = self::table();

        if ($user_id > 0) {
            return $wpdb->get_row(
                $wpdb->prepare("SELECT * FROM $table WHERE id = %d AND user_id = %d", $id, $user_id)
            );
        }

        return $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id)
        );
    }

    public static function update_status($id, $status) {
        global $wpdb;
        $table = self::table();

        return $wpdb->update(
            $table,
            ['status' => $status, 'updated_at' => current_time('mysql')],
            ['id' => $id]
        );
    }

    public static function update($id, $data) {
        global $wpdb;
        $table = self::table();

        // Prepare data for update
        $update_data = [];
        
        if (isset($data['country_id'])) {
            $update_data['country_id'] = intval($data['country_id']);
        }
        
        if (isset($data['trademark_type'])) {
            $update_data['trademark_type'] = sanitize_text_field($data['trademark_type']);
        }
        
        if (isset($data['mark_text'])) {
            $update_data['mark_text'] = sanitize_text_field($data['mark_text']);
        }
        
        if (isset($data['class_count'])) {
            $update_data['class_count'] = intval($data['class_count']);
            $update_data['extra_class_count'] = max(0, intval($data['class_count']) - 1);
        }
        
        if (isset($data['class_list'])) {
            $class_list = is_array($data['class_list']) ? $data['class_list'] : json_decode($data['class_list'], true);
            $update_data['class_list'] = wp_json_encode($class_list);
        }
        
        if (isset($data['class_details'])) {
            $class_details = is_array($data['class_details']) ? $data['class_details'] : json_decode($data['class_details'], true);
            $update_data['class_details'] = wp_json_encode($class_details);
        }
        
        if (isset($data['goods_services'])) {
            $update_data['goods_services'] = sanitize_textarea_field($data['goods_services']);
        }
        
        if (isset($data['priority_claim'])) {
            $update_data['priority_claim'] = intval($data['priority_claim']);
        }
        
        if (isset($data['poa_type'])) {
            $update_data['poa_type'] = sanitize_text_field($data['poa_type']);
        }
        
        if (isset($data['logo_url'])) {
            $update_data['logo_url'] = esc_url_raw($data['logo_url']);
        }
        
        if (isset($data['final_price'])) {
            $update_data['final_price'] = floatval($data['final_price']);
        }
        
        if (isset($data['status'])) {
            $update_data['status'] = sanitize_text_field($data['status']);
        }
        
        $update_data['updated_at'] = current_time('mysql');

        return $wpdb->update(
            $table,
            $update_data,
            ['id' => $id]
        );
    }

    public static function admin_get_all($per_page = 20, $offset = 0, $search = '', $country_filter = '', $status_filter = '') {
        global $wpdb;
        $table = self::table();
        $countries = TM_Database::table_name('countries');
        $users = $wpdb->users;

        $where = "WHERE 1=1";
        $join = "";
        
        // Add search conditions
        if (!empty($search)) {
            $where .= $wpdb->prepare(
                " AND (t.mark_text LIKE %s OR t.id LIKE %s OR u.user_email LIKE %s)",
                '%' . $wpdb->esc_like($search) . '%',
                '%' . $wpdb->esc_like($search) . '%',
                '%' . $wpdb->esc_like($search) . '%'
            );
        }
        
        // Add country filter
        if (!empty($country_filter)) {
            $where .= $wpdb->prepare(" AND c.iso_code = %s", $country_filter);
        }
        
        // Add status filter
        if (!empty($status_filter)) {
            $where .= $wpdb->prepare(" AND t.status = %s", $status_filter);
        }

        return $wpdb->get_results(
            "
                SELECT t.*, c.country_name, u.user_email 
                FROM $table t
                LEFT JOIN $countries c ON c.id = t.country_id
                LEFT JOIN $users u ON u.ID = t.user_id
                $where
                ORDER BY t.created_at DESC
                LIMIT $offset, $per_page
            "
        );
    }
    
    public static function admin_get_all_count($search = '', $country_filter = '', $status_filter = '') {
        global $wpdb;
        $table = self::table();
        $countries = TM_Database::table_name('countries');
        $users = $wpdb->users;

        $where = "WHERE 1=1";
        $join = "";
        
        // Add search conditions
        if (!empty($search)) {
            $where .= $wpdb->prepare(
                " AND (t.mark_text LIKE %s OR t.id LIKE %s OR u.user_email LIKE %s)",
                '%' . $wpdb->esc_like($search) . '%',
                '%' . $wpdb->esc_like($search) . '%',
                '%' . $wpdb->esc_like($search) . '%'
            );
        }
        
        // Add country filter
        if (!empty($country_filter)) {
            $where .= $wpdb->prepare(" AND c.iso_code = %s", $country_filter);
        }
        
        // Add status filter
        if (!empty($status_filter)) {
            $where .= $wpdb->prepare(" AND t.status = %s", $status_filter);
        }

        return $wpdb->get_var(
            "
                SELECT COUNT(t.id)
                FROM $table t
                LEFT JOIN $countries c ON c.id = t.country_id
                LEFT JOIN $users u ON u.ID = t.user_id
                $where
            "
        );
    }

    public static function ajax_admin_get_trademark() {
        check_ajax_referer('tm_admin_trademark_nonce', 'nonce');

        $id = intval($_POST['id']);
        $t  = self::get($id);

        if (!$t) {
            wp_send_json_error(['message' => 'Trademark not found']);
        }

        ob_start();
        include WP_TMS_NEXILUP_PLUGIN_PATH . 'templates/admin/trademark-details.php';
        $html = ob_get_clean();

        wp_send_json_success(['html' => $html]);
    }

    public static function ajax_admin_update_status() {
        check_ajax_referer('tm_admin_trademark_nonce', 'nonce');

        $id     = intval($_POST['id']);
        $status = sanitize_text_field($_POST['status']);

        $valid = ['pending_payment','paid','in_process','completed','cancelled'];

        if (!in_array($status, $valid)) {
            wp_send_json_error(['message' => 'Invalid status']);
        }

        self::update_status($id, $status);

        self::send_status_email($id, $status);

        wp_send_json_success(['message' => 'Status updated']);
    }
    
    public static function ajax_admin_update_trademark() {
        check_ajax_referer('tm_admin_trademark_nonce', 'nonce');

        $id = intval($_POST['id']);
        $t  = self::get($id);

        if (!$t) {
            wp_send_json_error(['message' => 'Trademark not found']);
        }

        // Prepare data for update
        $update_data = [];
        
        if (isset($_POST['trademark_type'])) {
            $update_data['trademark_type'] = sanitize_text_field($_POST['trademark_type']);
        }
        
        if (isset($_POST['mark_text'])) {
            $update_data['mark_text'] = sanitize_text_field($_POST['mark_text']);
        }
        
        if (isset($_POST['class_count'])) {
            $update_data['class_count'] = intval($_POST['class_count']);
        }
        
        if (isset($_POST['class_list'])) {
            $class_list = is_array($_POST['class_list']) ? $_POST['class_list'] : json_decode(stripslashes($_POST['class_list']), true);
            $update_data['class_list'] = $class_list;
        }
        
        if (isset($_POST['class_details'])) {
            $class_details = is_array($_POST['class_details']) ? $_POST['class_details'] : json_decode(stripslashes($_POST['class_details']), true);
            $update_data['class_details'] = $class_details;
        }
        
        if (isset($_POST['goods_services'])) {
            $update_data['goods_services'] = sanitize_textarea_field($_POST['goods_services']);
        }
        
        if (isset($_POST['priority_claim'])) {
            $update_data['priority_claim'] = intval($_POST['priority_claim']);
        }
        
        if (isset($_POST['poa_type'])) {
            $update_data['poa_type'] = sanitize_text_field($_POST['poa_type']);
        }
        
        if (isset($_POST['final_price'])) {
            $update_data['final_price'] = floatval($_POST['final_price']);
        }
        
        if (isset($_POST['status'])) {
            $update_data['status'] = sanitize_text_field($_POST['status']);
        }

        // Update the trademark
        $result = self::update($id, $update_data);

        if ($result === false) {
            wp_send_json_error(['message' => 'Failed to update trademark']);
        }

        wp_send_json_success(['message' => 'Trademark updated successfully']);
    }

    public static function send_status_email($id, $status) {
        global $wpdb;

        $t = self::get($id);
        if (!$t) return;

        $user = get_user_by('id', $t->user_id);
        if (!$user) return;

        $country = TM_Countries::get($t->country_id);

        ob_start();
        include WP_TMS_NEXILUP_PLUGIN_PATH . 'emails/status-email.php';
        $message = ob_get_clean();

        wp_mail(
            $user->user_email,
            "Your Trademark Status Updated: " . ucfirst(str_replace('_', ' ', $status)),
            $message,
            ['Content-Type: text/html; charset=UTF-8']
        );
    }

    public static function ajax_admin_upload_doc() {
        check_ajax_referer('tm_admin_trademark_nonce', 'nonce');

        $id   = intval($_POST['id']);
        $type = sanitize_text_field($_POST['type']);

        if (!isset($_FILES['file'])) {
            wp_send_json_error(['message' => 'No file uploaded']);
        }

        $file = $_FILES['file'];

        $allowed = ['pdf','jpg','jpeg','png','doc','docx'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            wp_send_json_error(['message' => 'Invalid file type']);
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        $upload = wp_handle_upload($file, ['test_form' => false]);

        if (isset($upload['error'])) {
            wp_send_json_error(['message' => $upload['error']]);
        }

        global $wpdb;
        $files = $wpdb->prefix . "tm_trademark_files";

        $wpdb->insert($files, [
            'trademark_id' => $id,
            'file_name'    => $file['name'],
            'file_url'     => $upload['url'],
            'file_type'    => $type,
            'uploaded_by'  => get_current_user_id(),
            'created_at'   => current_time('mysql')
        ]);

        self::send_document_email($id, $file['name']);

        wp_send_json_success(['message' => 'Uploaded']);
    }

    public static function ajax_admin_get_docs() {
        check_ajax_referer('tm_admin_trademark_nonce', 'nonce');

        $id = intval($_POST['id']);

        global $wpdb;
        $files = $wpdb->prefix . "tm_trademark_files";

        $docs = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM $files WHERE trademark_id = %d ORDER BY created_at DESC", $id)
        );

        ob_start();

        if ($docs) {
            echo "<ul class='tm-doc-list'>";
            foreach ($docs as $d) {
                echo "<li>
                    <strong>{$d->file_type}</strong> — {$d->file_name}
                    <a href='{$d->file_url}' target='_blank' class='button'>Download</a>
                </li>";
            }
            echo "</ul>";
        } else {
            echo "<p>No documents uploaded.</p>";
        }

        wp_send_json_success(['html' => ob_get_clean()]);
    }
}