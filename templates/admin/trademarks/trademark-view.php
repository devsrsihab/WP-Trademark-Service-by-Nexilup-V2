<?php
if (!defined('ABSPATH')) exit;

// Get the trademark ID
 $id = intval($_GET['id'] ?? 0);
if (!$id) {
    wp_die('Invalid trademark ID');
}

// Get the trademark details
 $trademark = TM_Trademarks::get($id);
if (!$trademark) {
    wp_die('Trademark not found');
}

// Get user details
 $user = get_user_by('id', $trademark->user_id);

// Get order details
 $order = wc_get_order($trademark->woo_order_id);
 $tm_goods_service = $trademark->goods_services ?? '';
 var_dump($tm_goods_service);

// Decode class list and details
 $class_list = json_decode($trademark->class_list, true) ?: [];
 $class_details = json_decode($trademark->class_details, true) ?: [];
            
            // Remove first-level wrapping quotes
            $step1 = trim($trademark->class_details, '"');
            
            // Remove escape slashes
            $step2 = stripcslashes($step1);
            
            // Remove second-level wrapping quotes
            $step3 = trim($step2, '"');
            
            // Remove escape slashes again
            $step4 = stripcslashes($step3);
            
            // Decode JSON
            $decoded_class_details = json_decode($step4, true);
// Fix for class_details - check if it's already an array
if (is_array($decoded_class_details)) {
    // If it's not an array, try to decode it
    $class_details = $decoded_class_details ?: [];
}


?>

<div class="wrap tm-wrap">
    <h1 class="tm-page-title">Trademark Details #<?php echo $id; ?></h1>
    
    <div class="tm-trademark-details-container" style="display: flex; gap: 20px;">
        <!-- Main Details Column -->
        <div class="tm-main-details" style="flex: 2;">
            <div class="tm-details-section">
                <h2>Basic Information</h2>
                <table class="form-table" role="presentation">
                    <tbody>
                        <tr>
                            <th scope="row">ID</th>
                            <td><?php echo $id; ?></td>
                        </tr>
                        <tr>
                            <th scope="row">Country</th>
                            <td><?php echo esc_html($trademark->country_iso); ?></td>
                        </tr>
                        <tr>
                            <th scope="row">Trademark Type</th>
                            <td><?php echo ucfirst($trademark->trademark_type); ?></td>
                        </tr>
                        <tr>
                            <th scope="row">Mark Text</th>
                            <td><?php echo esc_html($trademark->mark_text); ?></td>
                        </tr>
                        <tr>
                            <th scope="row">Status</th>
                            <td>
                                <span class="tm-status-badge tm-status-<?php echo $trademark->status; ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $trademark->status)); ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Created</th>
                            <td><?php echo date('M d, Y H:i', strtotime($trademark->created_at)); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="tm-details-section">
                <h2>Classes and Goods/Services</h2>
                <table class="form-table" role="presentation">
                    <tbody>
                        <tr>
                            <th scope="row">Class Count</th>
                            <td><?php echo $trademark->class_count; ?></td>
                        </tr>
                        <tr>
                            <th scope="row">Class Numbers</th>
                            <td>
                                <?php 
                                if (!empty($class_list)) {
                                    $strsplaed = stripslashes($class_list);
                                    $decodedString = json_decode($strsplaed, true);
                                    echo implode('-', $decodedString);
                                } else {
                                    echo 'N/A';
                                }
                                ?>
                            </td>
                        </tr>
                        <?php if(!$tm_goods_service): ?>
                        <tr>
                            <th scope="row">Class Details</th>
                            <td>
                                <?php if (!empty($class_details)): ?>
                                    <?php foreach ($class_details as $detail): ?>
                                        <div class="tm-class-detail" style="margin-bottom: 10px; padding: 10px; background-color: #f9f9f9; border: 1px solid #ddd; border-radius: 4px;">
                                            <strong>Class <?php echo esc_html($detail['class']); ?>:</strong> 
                                            <?php echo esc_html($detail['goods']); ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p>No class details available.</p>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php else: ?>
                        <tr>
                            <th scope="row">Goods/Services</th>
                            <td>
                                <?php echo esc_html($tm_goods_service); ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="tm-details-section">
                <h2>Additional Options</h2>
                <table class="form-table" role="presentation">
                    <tbody>
                        <tr>
                            <th scope="row">Priority Claim</th>
                            <td><?php echo $trademark->priority_claim ? 'Yes' : 'No'; ?></td>
                        </tr>
                        <tr>
                            <th scope="row">POA Type</th>
                            <td><?php echo ucfirst($trademark->poa_type); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="tm-details-section">
                <h2>Pricing</h2>
                <table class="form-table" role="presentation">
                    <tbody>
                        <tr>
                            <th scope="row">Final Price</th>
                            <td><?php echo number_format($trademark->final_price, 2) . ' ' . $trademark->currency; ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
<!-- Sidebar Column -->
<div class="tm-sidebar" style="flex: 1;">

    <!-- USER INFORMATION -->
    <div class="tm-details-section">
        <h2>User Information</h2>
        <table class="form-table" role="presentation">
            <tbody>

                <tr>
                    <th scope="row">Name</th>
                    <td>
                        <?php 
                            $full_name = trim($user->first_name . ' ' . $user->last_name);
                            echo esc_html($full_name ?: $user->display_name);
                        ?>
                    </td>
                </tr>

                <tr>
                    <th scope="row">Email</th>
                    <td><?php echo esc_html($user->user_email); ?></td>
                </tr>

                <tr>
                    <th scope="row">Phone</th>
                    <td>
                        <?php 
                            echo esc_html( get_user_meta($user->ID, 'billing_phone', true) ?: 'N/A' );
                        ?>
                    </td>
                </tr>

                <tr>
                    <th scope="row">Billing Address</th>
                    <td>
                        <?php 
                            $billing_address = array_filter([
                                get_user_meta($user->ID, 'billing_address_1', true),
                                get_user_meta($user->ID, 'billing_address_2', true),
                                get_user_meta($user->ID, 'billing_city', true),
                                get_user_meta($user->ID, 'billing_state', true),
                                get_user_meta($user->ID, 'billing_postcode', true),
                                get_user_meta($user->ID, 'billing_country', true)
                            ]);
                            
                            echo !empty($billing_address)
                                ? esc_html(implode(', ', $billing_address))
                                : 'N/A';
                        ?>
                    </td>
                </tr>

                <tr>
                    <th scope="row">Registered On</th>
                    <td><?php echo date('M d, Y', strtotime($user->user_registered)); ?></td>
                </tr>

                <tr>
                    <th scope="row">Total Orders</th>
                    <td>
                        <?php
                            $user_orders = wc_get_orders([
                                'customer_id' => $user->ID,
                                'return'      => 'ids'
                            ]);
                            echo count($user_orders);
                        ?>
                    </td>
                </tr>

                <tr>
                    <th scope="row">Last Order</th>
                    <td>
                        <?php
                            $orders = wc_get_orders([
                                'customer_id' => $user->ID,
                                'orderby'     => 'date',
                                'order'       => 'DESC',
                                'limit'       => 1,
                                'return'      => 'objects'
                            ]);

                            if (!empty($orders)) {
                                $last_order = $orders[0];
                                echo 'Order #' . $last_order->get_id() . ' on ' . $last_order->get_date_created()->date('M d, Y');
                            } else {
                                echo 'No previous orders';
                            }
                        ?>
                    </td>
                </tr>

            </tbody>
        </table>
    </div>

    <!-- ORDER INFORMATION -->
    <?php if ($order): ?>
    <div class="tm-details-section">
        <h2>Order Information</h2>
        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row">Order ID</th>
                    <td>
                        <a href="<?php echo admin_url('post.php?post=' . $order->get_id() . '&action=edit'); ?>">
                            #<?php echo $order->get_id(); ?>
                        </a>
                    </td>
                </tr>

                <tr>
                    <th scope="row">Order Date</th>
                    <td><?php echo $order->get_date_created()->date('M d, Y H:i'); ?></td>
                </tr>

                <tr>
                    <th scope="row">Order Status</th>
                    <td><?php echo esc_html($order->get_status()); ?></td>
                </tr>

                <tr>
                    <th scope="row">Order Total</th>
                    <td><?php echo $order->get_formatted_order_total(); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <!-- ACTION BUTTONS -->
    <div class="tm-details-section">
        <h2>Actions</h2>
        <p>
            <a href="?page=tm-trademarks&action=edit&id=<?php echo $id; ?>" class="button button-primary">
                Edit Trademark
            </a>
            <a href="?page=tm-trademarks" class="button">Back to List</a>
        </p>
    </div>

</div>

    </div>
</div>

<style>
.tm-details-section {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.tm-details-section h2 {
    margin-top: 0;
    margin-bottom: 15px;
    font-size: 1.2em;
    border-bottom: 1px solid #eee;
    padding-bottom: 10px;
}

.tm-status-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.tm-status-pending_payment {
    background-color: #f0f6fc;
    color: #0073aa;
}

.tm-status-paid {
    background-color: #edfaef;
    color: #00a32a;
}

.tm-status-in_process {
    background-color: #fcf9ef;
    color: #dba617;
}

.tm-status-completed {
    background-color: #edfaef;
    color: #00a32a;
}

.tm-status-cancelled {
    background-color: #fcf0f1;
    color: #d63638;
}

.tm-doc-list {
    list-style: none;
    margin: 0;
    padding: 0;
}

.tm-doc-list li {
    padding: 10px 0;
    border-bottom: 1px solid #eee;
}

.tm-doc-list li:last-child {
    border-bottom: none;
}

.tm-doc-date {
    display: block;
    color: #666;
    font-size: 12px;
    margin-top: 5px;
}

.tm-upload-field {
    margin-bottom: 15px;
}

.tm-upload-field label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
}

.tm-upload-field select,
.tm-upload-field input[type="file"] {
    width: 100%;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Handle document upload form
    $('#tm-upload-doc-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        
        $.ajax({
            url: TM_ADMIN_TRADEMARK_AJAX,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    alert('Document uploaded successfully');
                    location.reload();
                } else {
                    alert(response.data.message || 'Upload failed');
                }
            },
            error: function() {
                alert('Upload failed');
            }
        });
    });
});
</script>