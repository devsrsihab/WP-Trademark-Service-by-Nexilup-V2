<?php
if (!defined('ABSPATH')) exit;

$user_id = get_current_user_id();
$user    = get_userdata($user_id);

// User meta
$first_name  = get_user_meta($user_id, 'first_name', true);
$last_name   = get_user_meta($user_id, 'last_name', true);
$phone       = get_user_meta($user_id, 'billing_phone', true);
$company     = get_user_meta($user_id, 'billing_company', true);
$address1    = get_user_meta($user_id, 'billing_address_1', true);
$address2    = get_user_meta($user_id, 'billing_address_2', true);
$city        = get_user_meta($user_id, 'billing_city', true);
$state       = get_user_meta($user_id, 'billing_state', true);
$postcode    = get_user_meta($user_id, 'billing_postcode', true);
$country     = get_user_meta($user_id, 'billing_country', true);
?>

<!-- PROFILE CARD -->
<div class="tm-card tm-section-block">
    <h2 class="tm-section-heading">Profile Overview</h2>

    <div class="tm-info-grid">

        <div class="tm-info-item">
            <label>Full Name</label>
            <div class="tm-info-value">
                <?php echo esc_html(trim("$first_name $last_name")); ?>
            </div>
        </div>

        <div class="tm-info-item">
            <label>Email</label>
            <div class="tm-info-value">
                <?php echo esc_html($user->user_email); ?>
            </div>
        </div>

        <div class="tm-info-item">
            <label>Phone</label>
            <div class="tm-info-value">
                <?php echo $phone ? esc_html($phone) : '—'; ?>
            </div>
        </div>

        <div class="tm-info-item">
            <label>Username</label>
            <div class="tm-info-value">
                <?php echo esc_html($user->user_login); ?>
            </div>
        </div>

        <div class="tm-info-item full-width">
            <label>Account Created</label>
            <div class="tm-info-value">
                <?php echo date("F j, Y", strtotime($user->user_registered)); ?>
            </div>
        </div>

    </div>
</div>


<!-- BILLING CARD -->
<div class="tm-card tm-section-block">
    <h2 class="tm-section-heading">Billing Information</h2>

    <div class="tm-info-grid">

        <div class="tm-info-item">
            <label>Company</label>
            <div class="tm-info-value">
                <?php echo $company ? esc_html($company) : '—'; ?>
            </div>
        </div>

        <div class="tm-info-item">
            <label>Address</label>
            <div class="tm-info-value">
                <?php echo esc_html($address1 ?: '—'); ?>
                <?php if ($address2) echo "<br>" . esc_html($address2); ?>
            </div>
        </div>

        <div class="tm-info-item">
            <label>City</label>
            <div class="tm-info-value">
                <?php echo esc_html($city ?: '—'); ?>
            </div>
        </div>

        <div class="tm-info-item">
            <label>State</label>
            <div class="tm-info-value">
                <?php echo esc_html($state ?: '—'); ?>
            </div>
        </div>

        <div class="tm-info-item">
            <label>Postal Code</label>
            <div class="tm-info-value">
                <?php echo esc_html($postcode ?: '—'); ?>
            </div>
        </div>

        <div class="tm-info-item">
            <label>Country</label>
            <div class="tm-info-value">
                <?php echo esc_html($country ?: '—'); ?>
            </div>
        </div>

    </div>
</div>
