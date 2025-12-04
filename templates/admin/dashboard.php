<?php if (!defined('ABSPATH')) exit; ?>

<link rel="stylesheet" href="<?php echo WP_TMS_NEXILUP_URL . 'assets/css/admin-dashboard.css'; ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<div class="tm-dashboard-container">

    <?php 
    // 🔥 SHOW WARNING IF MASTER PRODUCT IS NOT SET
    if ( TM_Admin::is_master_product_missing() ) : ?>
        <div class="tm-alert tm-alert-error">
            <div class="tm-alert-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="tm-alert-content">
                <h3>⚠ IMPORTANT SETUP REQUIRED</h3>
                <p>You must select the <strong>Master WooCommerce Product</strong> before using any Trademark Service features.</p>
                <a href="<?php echo admin_url('admin.php?page=tm-settings'); ?>" class="tm-btn tm-btn-primary">
                    Go to Settings
                </a>
            </div>
        </div>
    <?php endif; ?>
    
    <div class="tm-dashboard-header">
        <div class="tm-header-content">
            <h1>Trademark Service Dashboard</h1>
            <p class="tm-dashboard-subtitle">Manage all Trademark Service configurations and data.</p>
        </div>
        <div class="tm-header-actions">
            <div class="tm-search-box">
                <input type="text" placeholder="Search...">
                <i class="fas fa-search"></i>
            </div>
            <div class="tm-notification-icon">
                <i class="fas fa-bell"></i>
                <span class="tm-badge">3</span>
            </div>
        </div>
    </div>

    <?php 
    // Get dynamic statistics from the database without modifying TM_Database class
    global $wpdb;
    
    // Get countries count
    $countries_table = $wpdb->prefix . 'tm_countries';
    $countries_count = $wpdb->get_var("SELECT COUNT(*) FROM $countries_table WHERE status = 1");
    
    // Get average pricing
    $prices_table = $wpdb->prefix . 'tm_country_prices';
    $avg_pricing = $wpdb->get_var("SELECT AVG(first_class_fee) FROM $prices_table WHERE first_class_fee > 0");
    
    // Get active orders count
    $trademarks_table = $wpdb->prefix . 'tm_trademarks';
    $active_orders = $wpdb->get_var("SELECT COUNT(*) FROM $trademarks_table WHERE status NOT IN ('cancelled', 'failed')");
    
    // Get unique users count
    $users_count = $wpdb->get_var("SELECT COUNT(DISTINCT user_id) FROM $trademarks_table");
    
    // Get recent trademarks
    $recent_trademarks = $wpdb->get_results("
        SELECT t.id, t.mark_text, t.status, t.created_at, c.country_name, c.iso_code
        FROM $trademarks_table t
        LEFT JOIN $countries_table c ON t.country_id = c.id
        ORDER BY t.created_at DESC
        LIMIT 5
    ");
    
    // Get status distribution
    $status_distribution = $wpdb->get_results("
        SELECT status, COUNT(*) as count
        FROM $trademarks_table
        GROUP BY status
    ");
    ?>

    <div class="tm-stats-container">
        <div class="tm-stat-card">
            <div class="tm-stat-icon" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;">
                <i class="fas fa-globe"></i>
            </div>
            <div class="tm-stat-content">
                <h3><?php echo esc_html($countries_count); ?></h3>
                <p>Countries</p>
            </div>
        </div>
        
        <div class="tm-stat-card">
            <div class="tm-stat-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="tm-stat-content">
                <h3>$<?php echo number_format($avg_pricing, 2); ?></h3>
                <p>Avg. Pricing</p>
            </div>
        </div>
        
        <div class="tm-stat-card">
            <div class="tm-stat-icon" style="background: rgba(251, 146, 60, 0.1); color: #fb923c;">
                <i class="fas fa-file-contract"></i>
            </div>
            <div class="tm-stat-content">
                <h3><?php echo esc_html($active_orders); ?></h3>
                <p>Active Orders</p>
            </div>
        </div>
        
        <div class="tm-stat-card">
            <div class="tm-stat-icon" style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6;">
                <i class="fas fa-users"></i>
            </div>
            <div class="tm-stat-content">
                <h3><?php echo esc_html($users_count); ?></h3>
                <p>Users</p>
            </div>
        </div>
    </div>

    <div class="tm-cards">
        <div class="tm-card">
            <div class="tm-card-icon">
                <i class="fas fa-globe-americas"></i>
            </div>
            <h3>Countries</h3>
            <p>Manage supported countries and ISO codes used in trademark filings.</p>
            <a href="<?php echo admin_url('admin.php?page=tm-countries'); ?>" class="tm-btn tm-btn-primary">Manage Countries</a>
        </div>

        <div class="tm-card">
            <div class="tm-card-icon">
                <i class="fas fa-tags"></i>
            </div>
            <h3>Country Pricing</h3>
            <p>Configure fees, government charges & class-based pricing per country.</p>
            <a href="<?php echo admin_url('admin.php?page=tm-country-prices'); ?>" class="tm-btn tm-btn-primary">Manage Pricing</a>
        </div>

        <div class="tm-card">
            <div class="tm-card-icon">
                <i class="fas fa-file-alt"></i>
            </div>
            <h3>Service Conditions</h3>
            <p>Edit texts & instructions used throughout the trademark filing workflow.</p>
            <a href="<?php echo admin_url('admin.php?page=tm-service-conditions'); ?>" class="tm-btn tm-btn-primary">Edit Conditions</a>
        </div>

        <div class="tm-card">
            <div class="tm-card-icon">
                <i class="fas fa-cog"></i>
            </div>
            <h3>Settings</h3>
            <p>Setup the Master Product if not set yet</p>
            <a href="<?php echo admin_url('admin.php?page=tm-settings'); ?>" class="tm-btn tm-btn-primary">Edit Settings</a>
        </div>

        <div class="tm-card">
            <div class="tm-card-icon">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <h3>Trademark Orders</h3>
            <p>View and manage trademark applications submitted by users.</p>
            <a href="<?php echo admin_url('admin.php?page=tm-trademarks'); ?>" class="tm-btn tm-btn-primary">View Orders</a>
        </div>
    </div>

    <!-- Recent Trademarks Section -->
    <div class="tm-recent-trademarks">
        <div class="tm-section-header">
            <h2>Recent Trademark Applications</h2>
            <a href="<?php echo admin_url('admin.php?page=tm-trademarks'); ?>" class="tm-btn tm-btn-ghost tm-btn-sm">
                View All <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        
        <div class="tm-table-container">
            <table class="tm-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Mark Text</th>
                        <th>Country</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recent_trademarks)) : ?>
                        <?php foreach ($recent_trademarks as $trademark) : ?>
                            <tr>
                                <td>#<?php echo esc_html($trademark->id); ?></td>
                                <td><?php echo esc_html($trademark->mark_text ? substr($trademark->mark_text, 0, 30) . '...' : 'N/A'); ?></td>
                                <td><?php echo esc_html($trademark->country_name ? $trademark->country_name : $trademark->iso_code); ?></td>
                                <td><span class="tm-status-badge tm-status-<?php echo esc_attr($trademark->status); ?>"><?php echo esc_html(ucfirst(str_replace('_', ' ', $trademark->status))); ?></span></td>
                                <td><?php echo date('M j, Y', strtotime($trademark->created_at)); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="5">No trademark applications found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Status Distribution Chart -->
    <div class="tm-status-chart">
        <div class="tm-section-header">
            <h2>Application Status Distribution</h2>
        </div>
        
        <div class="tm-chart-container">
            <?php if (!empty($status_distribution)) : ?>
                <div class="tm-chart-bars">
                    <?php 
                    $max_count = max(array_column($status_distribution, 'count'));
                    foreach ($status_distribution as $status) : 
                    ?>
                        <div class="tm-chart-bar">
                            <div class="tm-bar-label"><?php echo esc_html(ucfirst(str_replace('_', ' ', $status->status))); ?></div>
                            <div class="tm-bar-container">
                                <div class="tm-bar-fill" style="width: <?php echo ($status->count / $max_count) * 100; ?>%;"></div>
                            </div>
                            <div class="tm-bar-value"><?php echo esc_html($status->count); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <p>No status data available.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Shortcodes Panel -->
    <div class="tm-shortcodes-box">
        <div class="tm-section-header">
            <h2>Available Shortcodes</h2>
            <div class="tm-section-actions">
                <button class="tm-btn tm-btn-ghost tm-btn-sm">
                    <i class="fas fa-copy"></i> Copy All
                </button>
            </div>
        </div>

        <div class="tm-shortcodes-grid">
            <div class="tm-shortcode-item">
                <div class="tm-shortcode-header">
                    <h4>Country Table</h4>
                    <button class="tm-copy-btn" data-clipboard-text="[tm_country_table single_page='/trademark-country/']">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
                <code>[tm_country_table single_page="/trademark-country/"]</code>
                <p>Displays the full list of trademark countries with pricing table.</p>
            </div>

            <div class="tm-shortcode-item">
                <div class="tm-shortcode-header">
                    <h4>Single Country</h4>
                    <button class="tm-copy-btn" data-clipboard-text="[tm_country_single]">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
                <code>[tm_country_single]</code>
                <p>Shows pricing details for a single country.</p>
            </div>

            <div class="tm-shortcode-item">
                <div class="tm-shortcode-header">
                    <h4>Service Form</h4>
                    <button class="tm-copy-btn" data-clipboard-text="[tm_service_form]">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
                <code>[tm_service_form]</code>
                <p>The trademark filing multi-step form.</p>
            </div>

            <div class="tm-shortcode-item">
                <div class="tm-shortcode-header">
                    <h4>My Trademarks</h4>
                    <button class="tm-copy-btn" data-clipboard-text="[tm_my_trademarks]">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
                <code>[tm_my_trademarks]</code>
                <p>User's trademark orders list.</p>
            </div>
            
            <div class="tm-shortcode-item">
                <div class="tm-shortcode-header">
                    <h4>Account Management</h4>
                    <button class="tm-copy-btn" data-clipboard-text="[tm_account]">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
                <code>[tm_account]</code>
                <p>User's Account Management.</p>
            </div>
        </div>

        <div class="tm-shortcodes-note">
            <i class="fas fa-info-circle"></i>
            <p>Paste these shortcodes on any page to enable Trademark features.</p>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Copy to clipboard functionality
    const copyButtons = document.querySelectorAll('.tm-copy-btn');
    
    copyButtons.forEach(button => {
        button.addEventListener('click', function() {
            const textToCopy = this.getAttribute('data-clipboard-text');
            navigator.clipboard.writeText(textToCopy).then(() => {
                const originalIcon = this.innerHTML;
                this.innerHTML = '<i class="fas fa-check"></i>';
                
                setTimeout(() => {
                    this.innerHTML = originalIcon;
                }, 2000);
            });
        });
    });
    
    // Copy all functionality
    const copyAllBtn = document.querySelector('.tm-section-actions .tm-btn');
    if (copyAllBtn) {
        copyAllBtn.addEventListener('click', function() {
            const allShortcodes = Array.from(document.querySelectorAll('.tm-shortcode-item code'))
                .map(code => code.textContent)
                .join('\n');
            
            navigator.clipboard.writeText(allShortcodes).then(() => {
                const originalText = this.innerHTML;
                this.innerHTML = '<i class="fas fa-check"></i> Copied!';
                
                setTimeout(() => {
                    this.innerHTML = originalText;
                }, 2000);
            });
        });
    }
});
</script>