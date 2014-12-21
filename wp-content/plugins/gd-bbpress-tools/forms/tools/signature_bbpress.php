<h2 class="entry-title"><?php bbp_is_user_home() ? _e("Your Forum Signature", "gd-bbpress-tools") : _e("User Forum Signature", "gd-bbpress-tools"); ?></h2>
<fieldset class="bbp-form">
    <legend><?php bbp_is_user_home() ? _e("Your Forum Signature", "gd-bbpress-tools") : _e("User Forum Signature", "gd-bbpress-tools"); ?></legend>
    <?php do_action('bbp_user_edit_before_signature'); ?>
    <div>
        <label for="signature"><?php _e("Signature", "gd-bbpress-tools"); ?></label>
        <fieldset class="bbp-form password">
            <textarea name="signature" id="signature" rows="5" cols="30" tabindex="<?php bbp_tab_index(); ?>" style="width: 100%;"><?php echo esc_attr(bbp_get_displayed_user_field('signature')); ?></textarea>
            <span class="description">
                <?php echo sprintf(__("Signature length is limited to %s characters.", "gd-bbpress-tools"), $this->max_length); ?><br/>
                <?php do_action('bbp_user_edit_signature_info'); ?>
            </span>
        </fieldset>
    </div>
    <?php do_action('bbp_user_edit_after_signature'); ?>
</fieldset>
