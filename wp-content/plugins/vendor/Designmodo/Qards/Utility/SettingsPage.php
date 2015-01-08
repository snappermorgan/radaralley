<?php
/*
 * This file is part of the Designmodo WordPress Plugin.
 *
 * (c) Designmodo Inc. <info@designmodo.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Designmodo\Qards\Utility;

/**
 * SettingsPage implements WP admin's settings page handler.
 */
class SettingsPage
{

    /**
     * Init SettingsPage
     *
     * @return void
     */
    public static function init()
    {
        add_action(
            'admin_menu',
            function () {
                add_theme_page(
                    __('Qards settings'),
                    __('Qards Settings'),
                    'edit_theme_options',
                    'qards_settings',
                    array(
                        'Designmodo\Qards\Utility\SettingsPage',
                        'show'
                    )
                );
            }
        );

        add_action(
            'admin_init',
            function() {
                register_setting(
                    'qards_settings',
                    'qards_settings',
                    function($input) {
                        return $input;
                    }
                );

                add_settings_section(
                    'qards_settings_main',
                    'Main Settings',
                    function () {
                        echo '<p>Main settings.</p>';
                    },
                    'qards_settings'
                );

                add_settings_field(
                    'DM_GLOBAL_CSS',
                    __( 'Global CSS' ),
                    function () {
                        $qardsSettings = get_option('qards_settings');
                        ?><div id="DM_GLOBAL_CSS_edit" style="height: 200px; width: 100%;"></div><textarea id="DM_GLOBAL_CSS" name="qards_settings[DM_GLOBAL_CSS]" style="display:none;"><?php echo $qardsSettings['DM_GLOBAL_CSS']; ?></textarea><?php
                    },
                    'qards_settings',
                    'qards_settings_main'
                );

                // Export subscribers
                if ($_POST['qards_export_subscribers']) {
                    Subscription::export();
                }
            }
        );
    }

    public static function show()
    {
        ?>
<div class="wrap">
<script src="https://cdn.jsdelivr.net/ace/1.1.6/min/ace.js" type="text/javascript" charset="utf-8"></script>
<script>
jQuery(document).ready(function() {
    var editors = [{id:'DM_GLOBAL_CSS', mode:'css'}];
    for (var index in editors) {
        var editor = ace.edit(editors[index].id + "_edit");
        //editor.setTheme("ace/theme/monokai");
        editor.getSession().setMode("ace/mode/" + editors[index].mode);
        ace.edit(editors[index].id + "_edit").setValue(jQuery("#" + editors[index].id).val());
//         document.getElementById(editors[index].id + '_edit').style.fontSize='1.2em';
    };

    jQuery(document).on('submit', '#qards_settings_form', function() {
        jQuery("#DM_GLOBAL_CSS").val(ace.edit("DM_GLOBAL_CSS_edit").getValue());
    })
});
</script>

    <h2><?php echo __('Qards settings'); ?></h2>
    <form method="post" action="options.php" id="qards_settings_form">
        <?php settings_fields('qards_settings'); ?>
        <?php do_settings_sections('qards_settings'); ?>
        <p class="submit">
            <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
        </p>
    </form>
    <form method="post" action="options.php" id="">
        <h3>Export subscribers</h3>
        <p>Export subscribers</p>
        <p class="submit">
            <input type="submit" class="button-primary" name="qards_export_subscribers" value="<?php _e('Export as CSV') ?>" />
        </p>
    </form>
</div>
        <?php
    }
}