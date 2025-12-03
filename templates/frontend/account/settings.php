<?php
if (!defined('ABSPATH')) exit;

$user = wp_get_current_user();
?>

<div class="tm-settings-form">


        <?php 
        $updated = isset($_GET['tm_updated']) ? sanitize_text_field($_GET['tm_updated']) : null;
        $error   = isset($_GET['tm_error'])   ? sanitize_text_field($_GET['tm_error'])   : null;

        ?>

        <?php if ($updated === 'profile'): ?>
            <div class="tm-alert-success">Profile updated successfully.</div>
        <?php elseif ($updated === 'password'): ?>
            <div class="tm-alert-success">Password updated successfully.</div>
        <?php endif; ?>

        <?php if ($error === 'wrong_password'): ?>
            <div class="tm-alert-error">Current password is incorrect.</div>

        <?php elseif ($error === 'password_mismatch'): ?>
            <div class="tm-alert-error">Passwords do not match.</div>

        <?php elseif ($error === 'invalid_nonce'): ?>
            <div class="tm-alert-error">Security validation failed. Please try again.</div>
        <?php endif; ?>





    <h2 class="tm-section-title">Update Profile</h2>

    <form method="post" class="tm-settings-form-wrapper">
        <?php wp_nonce_field('tm_update_profile', 'tm_profile_nonce'); ?>

        <div class="tm-form-grid">
            <div>
                <label>Full Name</label>
                <input type="text" name="full_name" value="<?php echo esc_attr($user->display_name); ?>">
            </div>

            <div>
                <label>Company</label>
                <input type="text" name="company" value="<?php echo esc_attr(get_user_meta($user->ID, 'company', true)); ?>">
            </div>

            <div>
                <label>Phone</label>
                <input type="text" name="phone" value="<?php echo esc_attr(get_user_meta($user->ID, 'phone', true)); ?>">
            </div>

            <div>
                <label>Address</label>
                <input type="text" name="address" value="<?php echo esc_attr(get_user_meta($user->ID, 'address', true)); ?>">
            </div>

            <div>
                <label>City</label>
                <input type="text" name="city" value="<?php echo esc_attr(get_user_meta($user->ID, 'city', true)); ?>">
            </div>

            <div>
                <label>State</label>
                <input type="text" name="state" value="<?php echo esc_attr(get_user_meta($user->ID, 'state', true)); ?>">
            </div>

            <div>
                <label>ZIP / Postal Code</label>
                <input type="text" name="zip" value="<?php echo esc_attr(get_user_meta($user->ID, 'zip', true)); ?>">
            </div>

            <div>
                <label>Country</label>
                <input type="text" name="country" value="<?php echo esc_attr(get_user_meta($user->ID, 'country', true)); ?>">
            </div>
        </div>

        <button class="tm-btn-primary" type="submit" name="tm_update_profile">Save Changes</button>
    </form>



    <hr style="margin:40px 0;">



    <h2 class="tm-section-title">Change Password</h2>

    <form method="post" class="tm-settings-form-wrapper">
        <?php wp_nonce_field('tm_update_password', 'tm_password_nonce'); ?>

        <div class="tm-form-grid">

            <div class="tm-password-field">
                <label>Current Password</label>
                <div class="tm-password-wrapper">
                    <input type="password" name="current_password" class="tm-password-input">
                    <span class="tm-password-toggle">👁️</span>
                </div>
            </div>

            <div class="tm-password-field">
                <label>New Password</label>
                <div class="tm-password-wrapper">
                    <input type="password" name="new_password" class="tm-password-input">
                    <span class="tm-password-toggle">👁️</span>
                </div>
            </div>

            <div class="tm-password-field">
                <label>Confirm New Password</label>
                <div class="tm-password-wrapper">
                    <input type="password" name="confirm_password" class="tm-password-input">
                    <span class="tm-password-toggle">👁️</span>
                </div>
            </div>

        </div>

        <button class="tm-btn-primary" type="submit" name="tm_update_password">Update Password</button>
    </form>


</div>
