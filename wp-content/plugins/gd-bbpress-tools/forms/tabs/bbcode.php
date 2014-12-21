<?php

function d4p_bbcode_selected($bbcode, $deactivated) {
    echo '<input name="bbcode_activated[]" value="'.$bbcode.'" type="checkbox" ';

    if (!in_array($bbcode, $deactivated)) {
        echo ' checked';
    }

    echo ' />';
}

if (isset($_GET["settings-updated"]) && $_GET["settings-updated"] == "true") { ?>
<div class="updated settings-error" id="setting-error-settings_updated"> 
    <p><strong><?php _e("Settings saved.", "gd-bbpress-tools"); ?></strong></p>
</div>
<?php } ?>

<form action="" method="post">
    <?php wp_nonce_field("gd-bbpress-tools"); ?>
    <div class="d4p-settings">
        <h3><?php _e("BBCodes Support", "gd-bbpress-tools"); ?></h3>
        <p><?php _e("Implements shortcodes for standard BBCodes based on the phpBB 3.0 forums implementation with many additional codes.", "gd-bbpress-tools"); ?></p>
        <table class="form-table">
            <tbody>
                <tr valign="top">
                    <th scope="row"><label for="bbcodes_active"><?php _e("Active", "gd-bbpress-tools"); ?></label></th>
                    <td>
                        <input type="checkbox" <?php if ($options["bbcodes_active"] == 1) echo " checked"; ?> name="bbcodes_active" />
                    </td>
                </tr>
            </tbody>
        </table>
        <h3><?php _e("BBCodes New Topic/Reply Notice", "gd-bbpress-tools"); ?></h3>
        <p><?php _e("If the BBCodes support is active, you can display notice in the new topic/reply form.", "gd-bbpress-tools"); ?></p>
        <table class="form-table">
            <tbody>
                <tr valign="top">
                    <th scope="row"><label for="bbcodes_notice"><?php _e("Active", "gd-bbpress-tools"); ?></label></th>
                    <td>
                        <input type="checkbox" <?php if ($options["bbcodes_notice"] == 1) echo " checked"; ?> name="bbcodes_notice" />
                    </td>
                </tr>
            </tbody>
        </table>
        <h3><?php _e("Limit to bbPress only", "gd-bbpress-tools"); ?></h3>
        <p><?php _e("Processing of the bbcodes can be limited only to bbPress implemented forums, topics and replies.", "gd-bbpress-tools"); ?></p>
        <table class="form-table">
            <tbody>
                <tr valign="top">
                    <th scope="row"><label for="bbcodes_bbpress_only"><?php _e("Active", "gd-bbpress-tools"); ?></label></th>
                    <td>
                        <input type="checkbox" <?php if ($options["bbcodes_bbpress_only"] == 1) echo " checked"; ?> name="bbcodes_bbpress_only" />
                    </td>
                </tr>
            </tbody>
        </table>
        <h3><?php _e("Advanced BBCodes", "gd-bbpress-tools"); ?></h3>
        <p><?php echo sprintf(__("Some BBCodes can be available only to selected user roles. This include: %s.", "gd-bbpress-tools"), 'URL, YOUTUBE, VIMEO, GOOGLE, IMG, NOTE'); ?></p>
        <table class="form-table">
            <tbody>
                <tr valign="top">
                    <th scope="row"><?php _e("Allowed to", "gd-bbpress-tools") ?></th>
                    <td>
                        <fieldset>
                            <legend class="screen-reader-text"><span><?php _e("Allowed to", "gd-bbpress-tools"); ?></span></legend>
                            <label for="bbcodes_special_super_admin">
                                <input type="checkbox" <?php if ($options["bbcodes_special_super_admin"] == 1) echo " checked"; ?> name="bbcodes_special_super_admin" />
                                <?php _e("Super Admin", "gd-bbpress-tools"); ?>
                            </label><br/>
                            <?php foreach ($_user_roles as $role => $title) { ?>
                            <label for="bbcodes_special_roles_<?php echo $role; ?>">
                                <input type="checkbox" <?php if (!isset($options["bbcodes_special_roles"]) || is_null($options["bbcodes_special_roles"]) || in_array($role, $options["bbcodes_special_roles"])) echo " checked"; ?> value="<?php echo $role; ?>" id="bbcodes_special_roles_<?php echo $role; ?>" name="bbcodes_special_roles[]" />
                                <?php echo $title; ?>
                            </label><br/>
                            <?php } ?>
                        </fieldset>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e("Capability", "gd-bbpress-tools") ?></th>
                    <td>
                        <strong>d4p_bbpt_bbcodes_special</strong>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="bbcodes_special_action"><?php _e("Restrict action", "gd-bbpress-tools"); ?></label></th>
                    <td>
                        <select name="bbcodes_special_action" class="regular-text">
                            <option value="info"<?php if ($options["bbcodes_special_action"] == "info") echo ' selected="selected"'; ?>><?php _e("Replace with notice", "gd-bbpress-tools"); ?></option>
                            <option value="delete"<?php if ($options["bbcodes_special_action"] == "delete") echo ' selected="selected"'; ?>><?php _e("Remove from content", "gd-bbpress-tools"); ?></option>
                        </select>
                    </td>
                </tr>
            </tbody>
        </table>
        <h3><?php _e("Active BBCodes", "gd-bbpress-tools"); ?></h3>
        <p><?php _e("By default all BBCodes are active. If you want, you can disable registration of any of the BBCodes. To do that, just uncheck the BBCodes you want to deactivate from the list on the right side.", "gd-bbpress-tools"); ?></p>
        <p class="submit"><input type="submit" value="<?php _e("Save Changes", "gd-bbpress-tools"); ?>" class="button-primary" id="gdbb-bbcode-submit" name="gdbb-bbcode-submit" /></p>
    </div>
    <div class="d4p-settings-second">
        <h3><?php _e("List of Standard BBCodes", "gd-bbpress-tools"); ?></h3>
        <table class="form-table d4p-bbcodes-table">
            <tbody>
                <tr valign="top">
                    <th scope="row"><?php d4p_bbcode_selected('b', $options['bbcodes_deactivated']); ?></th><td><?php _e("Bold", "gd-bbpress-tools"); ?></td>
                    <td>[b]{content}[/b]</td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php d4p_bbcode_selected('i', $options['bbcodes_deactivated']); ?></th><td><?php _e("Italic", "gd-bbpress-tools"); ?></td>
                    <td>[i]{content}[/i]</td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php d4p_bbcode_selected('u', $options['bbcodes_deactivated']); ?></th><td><?php _e("Underline", "gd-bbpress-tools"); ?></td>
                    <td>[u]{content}[/u]</td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php d4p_bbcode_selected('s', $options['bbcodes_deactivated']); ?></th><td><?php _e("Strikethrough", "gd-bbpress-tools"); ?></td>
                    <td>[s]{content}[/s]</td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php d4p_bbcode_selected('center', $options['bbcodes_deactivated']); ?></th><td><?php _e("Align: Center", "gd-bbpress-tools"); ?></td>
                    <td>[center]{content}[/center]</td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php d4p_bbcode_selected('right', $options['bbcodes_deactivated']); ?></th><td><?php _e("Align: Right", "gd-bbpress-tools"); ?></td>
                    <td>[right]{content}[/right]</td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php d4p_bbcode_selected('left', $options['bbcodes_deactivated']); ?></th><td><?php _e("Align: Left", "gd-bbpress-tools"); ?></td>
                    <td>[left]{content}[/left]</td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php d4p_bbcode_selected('justify', $options['bbcodes_deactivated']); ?></th><td><?php _e("Align: Justify", "gd-bbpress-tools"); ?></td>
                    <td>[justify]{content}[/justify]</td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php d4p_bbcode_selected('hr', $options['bbcodes_deactivated']); ?></th><td><?php _e("Horizontal Line", "gd-bbpress-tools"); ?></td>
                    <td>[hr]</td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php d4p_bbcode_selected('sub', $options['bbcodes_deactivated']); ?></th><td><?php _e("Subscript", "gd-bbpress-tools"); ?></td>
                    <td>[sub]{content}[/sub]</td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php d4p_bbcode_selected('sup', $options['bbcodes_deactivated']); ?></th><td><?php _e("Superscript", "gd-bbpress-tools"); ?></td>
                    <td>[sup]{content}[/sup]</td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php d4p_bbcode_selected('reverse', $options['bbcodes_deactivated']); ?></th><td><?php _e("Reverse", "gd-bbpress-tools"); ?></td>
                    <td>[reverse]{content}[/reverse]</td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php d4p_bbcode_selected('size', $options['bbcodes_deactivated']); ?></th><td><?php _e("Font Size", "gd-bbpress-tools"); ?></td>
                    <td>[size={size}]{content}[/size]</td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php d4p_bbcode_selected('color', $options['bbcodes_deactivated']); ?></th><td><?php _e("Font Color", "gd-bbpress-tools"); ?></td>
                    <td>[color={color}]{content}[/color]</td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php d4p_bbcode_selected('pre', $options['bbcodes_deactivated']); ?></th><td><?php _e("Preformatted", "gd-bbpress-tools"); ?></td>
                    <td>[pre]{content}[/pre]</td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php d4p_bbcode_selected('blockquote', $options['bbcodes_deactivated']); ?></th><td><?php _e("Blockquote", "gd-bbpress-tools"); ?></td>
                    <td>[blockquote]{content}[/blockquote]</td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php d4p_bbcode_selected('border', $options['bbcodes_deactivated']); ?></th><td><?php _e("Border", "gd-bbpress-tools"); ?></td>
                    <td>[border]{content}[/border]</td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php d4p_bbcode_selected('area', $options['bbcodes_deactivated']); ?></th><td><?php _e("Area", "gd-bbpress-tools"); ?></td>
                    <td>[area]{content}[/area]<br/>[area={title}]{content}[/area]</td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php d4p_bbcode_selected('div', $options['bbcodes_deactivated']); ?></th><td><?php _e("Block", "gd-bbpress-tools"); ?></td>
                    <td>[div]{content}[/div]</td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php d4p_bbcode_selected('list', $options['bbcodes_deactivated']); ?></th><td><?php _e("List: Ordered", "gd-bbpress-tools"); ?></td>
                    <td>[list]{content}[/list]</td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php d4p_bbcode_selected('ol', $options['bbcodes_deactivated']); ?></th><td><?php _e("List: Ordered", "gd-bbpress-tools"); ?></td>
                    <td>[ol]{content}[/ol]</td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php d4p_bbcode_selected('ul', $options['bbcodes_deactivated']); ?></th><td><?php _e("List: Unordered", "gd-bbpress-tools"); ?></td>
                    <td>[ul]{content}[/ul]</td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php d4p_bbcode_selected('li', $options['bbcodes_deactivated']); ?></th><td><?php _e("List: Item", "gd-bbpress-tools"); ?></td>
                    <td>[li]{content}[/li]</td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php d4p_bbcode_selected('quote', $options['bbcodes_deactivated']); ?></th><td><?php _e("Quote", "gd-bbpress-tools"); ?></td>
                    <td>[quote]{content}[/quote]<br/>[quote={id}]{content}[/quote]</td>
                </tr>
            </tbody>
        </table>
        <h3><?php _e("List of Advanced BBCodes", "gd-bbpress-tools"); ?></h3>
        <table class="form-table d4p-bbcodes-table">
            <tbody>
                <tr valign="top">
                    <th scope="row"><?php d4p_bbcode_selected('url', $options['bbcodes_deactivated']); ?></th><td><?php _e("URL", "gd-bbpress-tools"); ?></td>
                    <td>[url]{link}[/url]<br/>[url=link]{text}[/url]<br/>[url='link']{text}[/url]</td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php d4p_bbcode_selected('img', $options['bbcodes_deactivated']); ?></th><td><?php _e("Image", "gd-bbpress-tools"); ?></td>
                    <td>[img]{image_url}[/img]<br/>[img={width}x{height}]{image_url}[/img]<br/>[img width={x} height={y}]{image_url}[/img]</td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php d4p_bbcode_selected('youtube', $options['bbcodes_deactivated']); ?></th><td><?php _e("YouTube Video", "gd-bbpress-tools"); ?></td>
                    <td>[youtube]{id}[/youtube]<br/>[youtube width={x} height={y}]{id}[/youtube]<br/>
                        [youtube]{url}[/youtube]<br/>[youtube width={x} height={y}]{url}[/youtube]</td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php d4p_bbcode_selected('vimeo', $options['bbcodes_deactivated']); ?></th><td><?php _e("Vimeo Video", "gd-bbpress-tools"); ?></td>
                    <td>[vimeo]{id}[/vimeo]<br/>[vimeo width={x} height={y}]{id}[/vimeo]<br/>
                        [vimeo]{url}[/vimeo]<br/>[vimeo width={x} height={y}]{url}[/vimeo]</td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php d4p_bbcode_selected('google', $options['bbcodes_deactivated']); ?></th><td><?php _e("Google Search URL", "gd-bbpress-tools"); ?></td>
                    <td>[google]{search}[/google]</td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php d4p_bbcode_selected('note', $options['bbcodes_deactivated']); ?></th><td><?php _e("Note", "gd-bbpress-tools"); ?></td>
                    <td>[note]{content}[/note]</td>
                </tr>
            </tbody>
        </table>
    </div>
</form>
