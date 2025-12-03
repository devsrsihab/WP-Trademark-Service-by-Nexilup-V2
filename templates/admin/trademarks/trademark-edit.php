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

// Get countries for dropdown
 $countries = TM_Database::get_countries(['active_only' => true]);

// Decode class list and details
 $class_list = json_decode($trademark->class_list, true) ?: [];
 $class_details = json_decode($trademark->class_details, true) ?: [];
?>

<div class="wrap tm-wrap">
    <h1 class="tm-page-title">Edit Trademark #<?php echo $id; ?></h1>
    
<form id="tm-edit-form" method="post" action="<?php echo admin_url('admin-post.php'); ?>">
        <input type="hidden" name="trademark_id" value="<?php echo $id; ?>">
        <input type="hidden" name="action" value="tm_admin_update_trademark">

        <?php wp_nonce_field('tm_admin_trademark_nonce'); ?>
        
        <div class="tm-form-section">
            <h2>Basic Information</h2>
            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th scope="row">
                            <label for="trademark_type">Trademark Type</label>
                        </th>
                        <td>
                            <select name="trademark_type" id="trademark_type" >
                                <option value="word" <?php selected($trademark->trademark_type, 'word'); ?>>Word Mark</option>
                                <option value="figurative" <?php selected($trademark->trademark_type, 'figurative'); ?>>Figurative Mark</option>
                                <option value="combined" <?php selected($trademark->trademark_type, 'combined'); ?>>Combined Mark</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="mark_text">Mark Text</label>
                        </th>
                        <td>
                            <input type="text" name="mark_text" id="mark_text" value="<?php echo esc_attr($trademark->mark_text); ?>" >
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="status">Status</label>
                        </th>
                        <td>
                            <select name="status" id="status" >
                                <option value="pending_payment" <?php selected($trademark->status, 'pending_payment'); ?>>Pending Payment</option>
                                <option value="paid" <?php selected($trademark->status, 'paid'); ?>>Paid</option>
                                <option value="in_process" <?php selected($trademark->status, 'in_process'); ?>>In Process</option>
                                <option value="completed" <?php selected($trademark->status, 'completed'); ?>>Completed</option>
                                <option value="cancelled" <?php selected($trademark->status, 'cancelled'); ?>>Cancelled</option>
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="tm-form-section">
            <h2>Classes and Goods/Services</h2>
            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th scope="row">
                            <label for="class_count">Class Count</label>
                        </th>
                        <td>
                            <input type="number" name="class_count" id="class_count" value="<?php echo $trademark->class_count; ?>" min="1" >
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="class_list">Class Numbers (comma-separated)</label>
                        </th>
                        <td>
                            <input type="text" name="class_list" id="class_list" value="<?php echo esc_attr(implode(', ', $class_list)); ?>" >
                        </td>
                    </tr>
                    <?php if (!empty($class_details)): ?>
                    <tr>
                        <th scope="row">
                            <label>Class Details</label>
                        </th>
                        <td>
                            <div id="class-details-container">
                                <?php foreach ($class_details as $index => $detail): ?>
                                    <div class="tm-class-detail-row" style="margin-bottom: 15px; padding: 15px; background-color: #f9f9f9; border: 1px solid #ddd; border-radius: 4px;">
                                        <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                                            <div style="flex: 1;">
                                                <label>Class Number</label>
                                                <input type="text" name="class_details[<?php echo $index; ?>][class]" value="<?php echo esc_attr($detail['class']); ?>" >
                                            </div>
                                            <div style="flex: 3;">
                                                <label>Goods/Services</label>
                                                <textarea name="class_details[<?php echo $index; ?>][goods]" rows="3" ><?php echo esc_textarea($detail['goods']); ?></textarea>
                                            </div>
                                            <div style="flex: 0;">
                                                <label>&nbsp;</label>
                                                <button type="button" class="button button-secondary tm-remove-class-detail">Remove</button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" id="tm-add-class-detail" class="button">Add Class Detail</button>
                        </td>
                    </tr>
                    <?php else: ?>
                    <tr>
                        <th scope="row">
                            <label for="goods_services">Goods/Services</label>
                        </th>
                        <td>
                            <textarea name="goods_services" id="goods_services" rows="5"><?php echo esc_textarea($trademark->goods_services); ?></textarea>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="tm-form-section">
            <h2>Additional Options</h2>
            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th scope="row">
                            <label for="priority_claim">Priority Claim</label>
                        </th>
                        <td>
                            <select name="priority_claim" id="priority_claim" >
                                <option value="0" <?php selected($trademark->priority_claim, 0); ?>>No</option>
                                <option value="1" <?php selected($trademark->priority_claim, 1); ?>>Yes</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="poa_type">POA Type</label>
                        </th>
                        <td>
                            <select name="poa_type" id="poa_type" >
                                <option value="normal" <?php selected($trademark->poa_type, 'normal'); ?>>Normal</option>
                                <option value="late" <?php selected($trademark->poa_type, 'late'); ?>>Late</option>
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="tm-form-section">
            <h2>Pricing</h2>
            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th scope="row">
                            <label for="final_price">Final Price</label>
                        </th>
                        <td>
                            <input type="number" name="final_price" id="final_price" value="<?php echo $trademark->final_price; ?>" step="0.01" min="0" >
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
            <a href="?page=tm-trademarks&action=view&id=<?php echo $id; ?>" class="button">Cancel</a>
        </p>
    </form>
</div>

<style>
.tm-form-section {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.tm-form-section h2 {
    margin-top: 0;
    margin-bottom: 15px;
    font-size: 1.2em;
    border-bottom: 1px solid #eee;
    padding-bottom: 10px;
}

.tm-class-detail-row input,
.tm-class-detail-row textarea {
    width: 100%;
}
</style>

<script>
jQuery(document).ready(function($) {

    /**
     * ---------------------------------------------
     * REMOVE AJAX — allow normal form submit
     * ---------------------------------------------
     * The form should submit normally to run
     * PHP update logic via admin_post action.
     */
    $('#tm-edit-form').off('submit'); // Remove any bound AJAX submit
    // No e.preventDefault() here — let WordPress process form normally.


    /**
     * ---------------------------------------------
     * FIX: Safe next index for class details
     * ---------------------------------------------
     */
    <?php 
    $next_index = 0;
    if (!empty($class_details) && is_array($class_details)) {
        $keys = array_keys($class_details);
        if (!empty($keys)) {
            $next_index = max($keys) + 1;
        }
    }
    ?>
    var classDetailIndex = <?php echo intval($next_index); ?>;


    /**
     * ---------------------------------------------
     * Add class detail row
     * ---------------------------------------------
     */
    $('#tm-add-class-detail').on('click', function() {

        var html = `
        <div class="tm-class-detail-row" style="margin-bottom: 15px; padding: 15px; background-color: #f9f9f9; border: 1px solid #ddd; border-radius: 4px;">
            <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                <div style="flex: 1;">
                    <label>Class Number</label>
                    <input type="text" name="class_details[` + classDetailIndex + `][class]" />
                </div>
                <div style="flex: 3;">
                    <label>Goods/Services</label>
                    <textarea name="class_details[` + classDetailIndex + `][goods]" rows="3"></textarea>
                </div>
                <div style="flex: 0;">
                    <label>&nbsp;</label>
                    <button type="button" class="button button-secondary tm-remove-class-detail">Remove</button>
                </div>
            </div>
        </div>`;

        $('#class-details-container').append(html);
        classDetailIndex++;
    });


    /**
     * ---------------------------------------------
     * Remove class detail row
     * ---------------------------------------------
     */
    $(document).on('click', '.tm-remove-class-detail', function() {
        $(this).closest('.tm-class-detail-row').remove();
    });

});
</script>
