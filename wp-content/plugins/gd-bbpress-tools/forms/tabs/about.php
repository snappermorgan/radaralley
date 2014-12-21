<div class="d4p-information">
    <div class="d4p-plugin">
        <h3>GD bbPress Tools <?php echo $options["version"]; ?></h3>
        <?php

        $status = ucfirst($options["status"]);
        if ($options["revision"] > 0) {
            $status.= " #".$options["revision"];
        }

        _e("Release Date: ", "gd-bbpress-tools");
        echo '<strong>'.$options["date"].'</strong><br/>';
        _e("Status: ", "gd-bbpress-tools");
        echo '<strong>'.$status.'</strong><br/>';
        _e("Build: ", "gd-bbpress-tools");
        echo '<strong>'.$options["build"].'</strong>';

        ?>
    </div>
    <h3><?php _e("System Requirements", "gd-bbpress-tools"); ?></h3>
    <?php

        _e("WordPress: ", "gd-bbpress-tools");
        echo '<strong>3.2 or newer</strong><br/>';
        _e("bbPress: ", "gd-bbpress-tools");
        echo '<strong>2.0 or newer</strong>';

    ?><br/><br/><br/><br/>
</div>
<div class="d4p-information-second">
    <h3><?php _e("Important Plugin Links", "gd-bbpress-tools"); ?></h3>
    <a target="_blank" href="http://www.dev4press.com/plugins/gd-bbpress-tools/">GD bbPress Tools <?php _e("Home Page", "gd-bbpress-tools"); ?></a><br/>
    <a target="_blank" href="http://wordpress.org/extend/plugins/gd-bbpress-tools/">GD bbPress Tools <?php _e("on", "gd-bbpress-tools"); ?> WordPress.org</a>
    <h3><?php _e("Plugin Support", "gd-bbpress-tools"); ?></h3>
    <a target="_blank" href="http://www.dev4press.com/forums/forum/free-plugins/gd-bbpress-tools/"><?php _e("Plugin Support Forum on Dev4Press", "gd-bbpress-tools"); ?></a><br/>
    <h3><?php _e("Dev4Press Important Links", "gd-bbpress-tools"); ?></h3>
    <a target="_blank" href="http://twitter.com/milangd">Dev4Press <?php _e("on", "gd-bbpress-tools"); ?> Twitter</a><br/>
    <a target="_blank" href="http://www.facebook.com/dev4press">Dev4Press Facebook <?php _e("Page", "gd-bbpress-tools"); ?></a><br/>
    <a target="_blank" href="http://d4p.me/d4plus">Dev4Press Google+ <?php _e("Page", "gd-bbpress-tools"); ?></a>
</div>
<div class="d4p-clear"></div>
<div class="d4p-copyright">
    Dev4Press &copy; 2008 - 2012 <a target="_blank" href="http://www.dev4press.com">www.dev4press.com</a> | Golden Dragon WebStudio <a target="_blank" href="http://www.gdragon.info">www.gdragon.info</a>
</div>
