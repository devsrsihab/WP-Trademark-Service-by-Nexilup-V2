<?php
if (!defined('ABSPATH')) exit;

$user_id = get_current_user_id();
global $wpdb;

$table = $wpdb->prefix . "tm_trademarks";

$trademarks = $wpdb->get_results(
    $wpdb->prepare("SELECT * FROM $table WHERE user_id = %d ORDER BY id DESC", $user_id)
);
?>

<div class="tm-card tm-section-block">
    <h2 class="tm-section-heading">Active Trademarks</h2>

    <?php if (empty($trademarks)) : ?>

        <div class="tm-empty-state">
            <p>No trademarks found.</p>
        </div>

    <?php else : ?>

        <div class="tm-trademark-list">
            <?php foreach ($trademarks as $tm) : 
                $status = strtolower($tm->status);
                $status_label = ucfirst(str_replace('_', ' ', $status));
                $created = date("F j, Y", strtotime($tm->created_at));
            ?>

                <div class="tm-trademark-item">

                    <div class="tm-trademark-header">
                        <div>
                            <h3 class="tm-trademark-title">
                                <?php echo esc_html($tm->mark_text ?: 'Unnamed Trademark'); ?>
                            </h3>
                            <span class="tm-trademark-id">ID: <?php echo $tm->id; ?></span>
                        </div>
                            <span class="tm-status-badge tm-status-<?php echo $status; ?>">
                                <?php echo esc_html($status_label); ?>
                            </span>

                    </div>

                    <div class="tm-trademark-body">

                        <div class="tm-info-row">
                            <label>Country</label>
                            <div class="value"><?php echo esc_html($tm->country_iso); ?></div>
                        </div>

                        <div class="tm-info-row">
                            <label>Trademark Type</label>
                            <div class="value"><?php echo ucfirst(esc_html($tm->trademark_type)); ?></div>
                        </div>

                        <div class="tm-info-row">
                            <label>Classes</label>
                            <div class="value">
                                <?php 
                                    $list = json_decode($tm->class_list, true);
                                    echo $list ? implode(', ', $list) : '—';
                                ?>
                            </div>
                        </div>

                        <div class="tm-info-row">
                            <label>Created</label>
                            <div class="value"><?php echo $created; ?></div>
                        </div>

                    </div>

                    <!-- <div class="tm-trademark-footer">
                        <a href="/my-account/trademark-view?id=<?php echo $tm->id; ?>" 
                           class="tm-btn-secondary">
                           View Details
                        </a>
                    </div> -->

                </div>

            <?php endforeach; ?>
        </div>

    <?php endif; ?>
</div>
