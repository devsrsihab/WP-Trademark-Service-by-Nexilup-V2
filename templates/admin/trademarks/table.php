<?php
if (!defined('ABSPATH')) exit;

// Get pagination parameters
 $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
 $per_page = 20;
 $offset = ($paged - 1) * $per_page;

// Get search and filter parameters
 $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
 $country_filter = isset($_GET['country']) ? sanitize_text_field($_GET['country']) : '';
 $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';

// Get countries for filter dropdown
 $countries = TM_Database::get_countries(['active_only' => true]);

// Get trademarks with pagination, search, and filters
 $trademarks = TM_Trademarks::admin_get_all($per_page, $offset, $search, $country_filter, $status_filter);
 $total_trademarks = TM_Trademarks::admin_get_all_count($search, $country_filter, $status_filter);
 $total_pages = ceil($total_trademarks / $per_page);

 $nonce = wp_create_nonce('tm_admin_trademark_nonce');
?>

<link rel="stylesheet" href="<?php echo WP_TMS_NEXILUP_URL . 'assets/css/admin-trademarks.css'; ?>">

<div class="wrap tm-wrap">
    <h1 class="tm-page-title">Trademark Orders</h1>
    
    <!-- Search and Filters -->
    <div class="tm-admin-filters" style="margin-bottom: 20px;">
        <form method="get" action="">
            <input type="hidden" name="page" value="tm-trademarks">
            
            <div class="tm-filter-row" style="display: flex; gap: 15px; margin-bottom: 15px; align-items: center;">
                <div class="tm-filter-field">
                    <label for="tm-search" style="display: block; margin-bottom: 5px; font-weight: 600;">Search:</label>
                    <input type="text" id="tm-search" name="s" value="<?php echo esc_attr($search); ?>" placeholder="Search by mark text, ID, or user email" style="padding: 8px; width: 250px;">
                </div>
                
                <div class="tm-filter-field">
                    <label for="tm-country" style="display: block; margin-bottom: 5px; font-weight: 600;">Country:</label>
                    <select id="tm-country" name="country" style="padding: 8px; width: 150px;">
                        <option value="">All Countries</option>
                        <?php foreach ($countries as $country): ?>
                            <option value="<?php echo $country->iso_code; ?>" <?php selected($country_filter, $country->iso_code); ?>>
                                <?php echo esc_html($country->country_name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="tm-filter-field">
                    <label for="tm-status" style="display: block; margin-bottom: 5px; font-weight: 600;">Status:</label>
                    <select id="tm-status" name="status" style="padding: 8px; width: 150px;">
                        <option value="">All Statuses</option>
                        <option value="pending_payment" <?php selected($status_filter, 'pending_payment'); ?>>Pending Payment</option>
                        <option value="paid" <?php selected($status_filter, 'paid'); ?>>Paid</option>
                        <option value="in_process" <?php selected($status_filter, 'in_process'); ?>>In Process</option>
                        <option value="completed" <?php selected($status_filter, 'completed'); ?>>Completed</option>
                        <option value="cancelled" <?php selected($status_filter, 'cancelled'); ?>>Cancelled</option>
                    </select>
                </div>
                
                <div class="tm-filter-field">
                    <button type="submit" class="button">Filter</button>
                    <a href="?page=tm-trademarks" class="button">Reset</a>
                </div>
            </div>
        </form>
    </div>

    <table class="wp-list-table widefat fixed striped mt-20" id="tm-admin-trademark-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Country</th>
                <th>Mark</th>
                <th>Classes</th>
                <th>Status</th>
                <th>User</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
        <?php foreach ($trademarks as $t): ?>
            <tr data-id="<?php echo $t->id; ?>">
                <td><?php echo $t->id; ?></td>
                <td><?php echo esc_html($t->country_name); ?></td>
                <td><?php echo esc_html($t->mark_text); ?></td>
                <td>
                    <?php 
                    // Decode class list for display
                    $class_list = json_decode($t->class_list, true);
                    if (is_array($class_list)) {
                        echo implode(', ', $class_list);
                    } else {
                        echo $t->class_count;
                    }
                    ?>
                </td>

                <td>
                    <span class="tm-status-badge tm-status-<?php echo $t->status; ?>">
                        <?php echo ucfirst(str_replace('_', ' ', $t->status)); ?>
                    </span>
                </td>

                <td><?php echo esc_html(get_user_by('id', $t->user_id)->user_email); ?></td>

                <td><?php echo date('M d, Y', strtotime($t->created_at)); ?></td>

                <td>
                    <a href="?page=tm-trademarks&action=view&id=<?php echo $t->id; ?>" class="button">View</a>
                    <a href="?page=tm-trademarks&action=edit&id=<?php echo $t->id; ?>" class="button">Edit</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    
    <!-- Pagination -->
    <div class="tablenav bottom" style="margin-top: 20px;">
        <div class="tablenav-pages">
            <?php
            $current_url = remove_query_arg('paged', $_SERVER['REQUEST_URI']);
            
            if ($total_pages > 1) {
                echo '<span class="displaying-num">' . sprintf(
                    'Showing %d-%d of %d',
                    ($paged - 1) * $per_page + 1,
                    min($paged * $per_page, $total_trademarks),
                    $total_trademarks
                ) . '</span>';
                
                echo '<span class="pagination-links">';
                
                // First page
                if ($paged > 1) {
                    echo '<a class="first-page button" href="' . esc_url(add_query_arg('paged', 1, $current_url)) . '">&laquo;</a>';
                }
                
                // Previous page
                if ($paged > 1) {
                    echo '<a class="prev-page button" href="' . esc_url(add_query_arg('paged', $paged - 1, $current_url)) . '">&lsaquo;</a>';
                }
                
                // Page numbers
                $start = max(1, $paged - 2);
                $end = min($total_pages, $paged + 2);
                
                for ($i = $start; $i <= $end; $i++) {
                    $class = $i == $paged ? 'current' : '';
                    echo '<a class="' . $class . ' button" href="' . esc_url(add_query_arg('paged', $i, $current_url)) . '">' . $i . '</a>';
                }
                
                // Next page
                if ($paged < $total_pages) {
                    echo '<a class="next-page button" href="' . esc_url(add_query_arg('paged', $paged + 1, $current_url)) . '">&rsaquo;</a>';
                }
                
                // Last page
                if ($paged < $total_pages) {
                    echo '<a class="last-page button" href="' . esc_url(add_query_arg('paged', $total_pages, $current_url)) . '">&raquo;</a>';
                }
                
                echo '</span>';
            }
            ?>
        </div>
    </div>
</div>

<script>
    const TM_ADMIN_TRADEMARK_AJAX = "<?php echo admin_url('admin-ajax.php'); ?>";
    const TM_ADMIN_TRADEMARK_NONCE = "<?php echo $nonce; ?>";
</script>

<script src="<?php echo WP_TMS_NEXILUP_URL . 'assets/js/admin-trademarks.js'; ?>"></script>