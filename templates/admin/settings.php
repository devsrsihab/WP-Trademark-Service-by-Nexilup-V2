<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$selected_product = get_option('tm_master_product_id');
$products = wc_get_products(['limit' => -1]);
?>

<div class="wrap">
    <h1>Trademark Settings</h1>

    <form method="post" action="options.php">
        <?php settings_fields('tm_settings_group'); ?>
        <?php do_settings_sections('tm_settings_group'); ?>

        <table class="form-table">
            <tr>
                <th>Select Master WooCommerce Product</th>
                <td>
                    <select name="tm_master_product_id" required>
                        <option value="">-- Select Product --</option>

                        <?php foreach ( $products as $p ) : ?>
                            <option value="<?= $p->get_id(); ?>" 
                                <?= selected($selected_product, $p->get_id()); ?>>
                                <?= $p->get_name(); ?> (ID: <?= $p->get_id(); ?>)
                            </option>
                        <?php endforeach; ?>

                    </select>

                    <p class="description">
                        This product is required for Trademark Service to work.
                        You must select it before continuing.
                    </p>
                </td>
            </tr>
        </table>

        <?php submit_button(); ?>
    </form>
</div>
