<?php if (isset($_GET["settings-updated"]) && $_GET["settings-updated"] == "true") { ?>
<div class="updated settings-error" id="setting-error-settings_updated"> 
    <p><strong><?php _e("Settings saved.", "gd-bbpress-tools"); ?></strong></p>
</div>
<?php } ?>

<form action="" method="post">
    <?php wp_nonce_field("gd-bbpress-tools"); ?>
    <div class="d4p-settings">
        <h3><?php _e("Topics with most replies", "gd-bbpress-tools"); ?></h3>
        <p><?php _e("This view will show list of topics with most replies.", "gd-bbpress-tools"); ?></p>
        <table class="form-table">
            <tbody>
                <tr valign="top">
                    <th scope="row"><label for="view_mostreplies_active"><?php _e("Active", "gd-bbpress-tools"); ?></label></th>
                    <td>
                        <input type="checkbox" <?php if ($options["view_mostreplies_active"] == 1) echo " checked"; ?> name="view_mostreplies_active" />
                    </td>
                </tr>
            </tbody>
        </table>

        <h3><?php _e("Topics Search", "gd-bbpress-tools"); ?></h3>
        <p><?php _e("This view is used to perform search through topics.", "gd-bbpress-tools"); ?></p>
        <table class="form-table">
            <tbody>
                <tr valign="top">
                    <th scope="row"><label for="view_searchresults_active"><?php _e("Active", "gd-bbpress-tools"); ?></label></th>
                    <td>
                        <input type="checkbox" <?php if ($options["view_searchresults_active"] == 1) echo " checked"; ?> name="view_searchresults_active" />
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="d4p-settings-second">
        <h3><?php _e("Latest Topics", "gd-bbpress-tools"); ?></h3>
        <p><?php _e("This view will show list of topics starting with latest ones.", "gd-bbpress-tools"); ?></p>
        <table class="form-table">
            <tbody>
                <tr valign="top">
                    <th scope="row"><label for="view_latesttopics_active"><?php _e("Active", "gd-bbpress-tools"); ?></label></th>
                    <td>
                        <input type="checkbox" <?php if ($options["view_latesttopics_active"] == 1) echo " checked"; ?> name="view_latesttopics_active" />
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="d4p-clear"></div>
    <p class="submit"><input type="submit" value="<?php _e("Save Changes", "gd-bbpress-tools"); ?>" class="button-primary" id="gdbb-views-submit" name="gdbb-views-submit" /></p>
</form>
