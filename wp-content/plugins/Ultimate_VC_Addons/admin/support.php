<div class="wrap about-wrap">
  <h1><?php echo __("Brainstorm Force &mdash; Support","ultimate"); ?></h1>
  <div class="about-text">We take pride in providing top notch support to our customers.</div>
  <div class="ult-badge" style="background: url(<?php echo plugins_url('img/brainstorm-logo.png',__FILE__); ?>) no-repeat top center; background-size: 150px;"></div>
  <h2 class="nav-tab-wrapper"> <a href="#support-home" data-tab="support-home" class="nav-tab nav-tab-active"> 
  Home </a>
  <?php 
  $ultimate_keys = get_option('ultimate_keys');
  if(!empty($ultimate_keys) && isset($ultimate_keys['envato_username'])){ ?>
  	<a href="#support-access" data-tab="support-access" class="nav-tab"> Developer Access </a>
  <?php } ?>
  <a href="#support-forum" data-tab="support-forum" class="nav-tab"> 
  Support Forum </a> </h2>
  <div id="support-home" class="ult-tabs active-tab">
    <div class="changelog point-releases">
      <h3>Currently Active Plugins</h3>
      <p>This is a list of plugins by "Brainstorm Force", currently active on this site.</p>
      <table class="wp-list-table widefat plugins">
        <thead>
          <tr>
            <th scope="col" id="name" class="manage-column column-name" style="">Plugin</th>
            <th scope="col" id="version" class="manage-column column-version" style="">Version</th>
            <th scope="col" id="status" class="manage-column column-status" style="">Status</th>
          </tr>
        </thead>
        <tfoot>
          <tr>
            <th scope="col" class="manage-column column-name" style="">Plugin</th>
            <th scope="col" class="manage-column column-version" style="">Version</th>
            <th scope="col" class="manage-column column-status" style="">Status</th>
          </tr>
        </tfoot>
        <tbody>
          <?php
				$plugins = get_plugins();
				foreach ( $plugins as $plugin => $data ) {
					$plugin_name = $data['Name'];
					$plugin_author = $data['Author'];
					$plugin_version = $data['Version'];
					$plugin_file = $plugin;
					$plugin_status = is_plugin_active( $plugin ) ? 'Active' : 'Inactive';
					if($plugin_author == "Brainstorm Force"){
				?>
          <tr class="<?php echo strtolower($plugin_status); ?>">
            <th scope="col" class="manage-column column-name"><?php echo $plugin_name; ?></th>
            <th scope="col" class="manage-column column-version"><?php echo $plugin_version; ?></th>
            <th scope="col" class="manage-column column-status"><?php echo $plugin_status; ?></th>
          </tr>
          <?php }
				}?>
        </tbody>
      </table>
    </div>
  </div>
  <?php if(get_option('ultimate_updater') === 'enabled'){ ?>
  <div id="support-access" class="ult-tabs">
    <div class="changelog point-releases">
      <h3>Grant Access to Developers</h3>
      <p>By clicking on the following button, you will grant access to your site to the developers of this plugin.  <a href="#" class="display_info"> How does this work?</a></p>
    </div>
    <p class="about-description">
      <?php $access = get_option('developer_access'); 
				$access_status = ($access) ? '<span class="active-access">Active</span>' : '<span class="inactive-access">Inactive</span>';
			?>
    <div class="access-msg"> </div>
    <div class="feature-section col one-col">
      <h3 class="access-status">Developer Access : <?php echo $access_status; ?></h3>
      <div class="access-buttons">
        <?php
                    $interval = get_option('access_time');
                    $time = getdate($interval);//date("Y-m-d",$interval);
                    $d = $time['mday'];
                    $m = $time['month'];
                    $y = $time['year'];
					$hr = $time['hours'];
					$min = $time['minutes'];
                    $date = $d.' '.$m.', '.$y.' &mdash; '.$hr.':'.$min .' Hours';
                    if($access){
                    ?>
        <p>You have granted access to developer. The developer access will be automatically revoked on <strong><?php echo $date; ?></strong></p>
        <button class="button button-hero button-primary" id="developer-access-revoke">Revoke Access</button>
        <button class="button button-hero button-primary" id="developer-access-extend">Extend Access</button>
        <?php
                    } else {
                    ?>
        <button class="button button-hero button-primary" id="developer-access">Grant Temporary Access to Developers</button>
        <?php } ?>
      </div>
      <?php 
				if(isset($_COOKIE["DeveloperAccess"]) && $_COOKIE["DeveloperAccess"] == "active"){
				?>
      <p></p>
      <hr>
      <h4>Developer Notes -</h4>
      <div class="developer-notes">
        <table width="100%">
          <tr>
            <td width="40%"> Developer Name: </td>
            <td><input type="text" id="developer" value=""></td>
          </tr>
          <tr>
            <td> Developer Note: </td>
            <td><textarea id="notes" cols="15" rows="4"></textarea></td>
          </tr>
        </table>
        <button class="button button-primary update-developer-notes">Update</button>
      </div>
      <?php	
				}
				?>
      <p></p>
      <hr>
      <h4>Developer Access Log </h4>
      <p>This log will be updated after each time developer access your site.</p>
      <table class="wp-list-table widefat plugins">
        <thead>
          <tr>
            <th scope="col" width="20%" id="name" class="manage-column column-name" style="">Developer</th>
            <th scope="col" width="20%" id="version" class="manage-column column-version" style="">Login Time</th>
            <th scope="col" id="status" class="manage-column column-status" style="">Notes</th>
          </tr>
        </thead>
        <tfoot>
          <tr>
            <th scope="col" class="manage-column column-name" style="">Developer</th>
            <th scope="col" class="manage-column column-version" style="">Login Time</th>
            <th scope="col" class="manage-column column-status" style="">Notes</th>
          </tr>
        </tfoot>
        <tbody>
          <?php
				$developer_logs = get_option('developer_log');
				if(is_array($developer_logs) && !empty($developer_logs)){
					foreach ( $developer_logs as $key => $data ) {
						$dev = $data['dev'];
						$note = $data['note'];
						$time = $data['time'];
						$date = getdate($time); 
						$d = $date['mday'];
						$m = $date['month'];
						$y = $date['year'];
						$hr = $date['hours'];
						$min = $date['minutes'];
						$login_time = $d.' '.$m.', '.$y.' &mdash; '.$hr.':'.$min .' Hours';
					?>
          <tr>
            <th scope="col" class="manage-column column-name"><?php echo $dev; ?></th>
            <th scope="col" class="manage-column column-version"><?php echo $login_time; ?></th>
            <th scope="col" class="manage-column column-status"><?php echo stripslashes($note); ?></th>
          </tr>
          <?php }
				} else {
					echo '<tr><td><p> No log available. </p></td></tr>';
				}
				?>
        </tbody>
      </table>
    </div>
    </p>
  </div>
   <?php } ?>
  <div id="support-forum" class="ult-tabs">
    <div class="changelog point-releases">
      <h3>Access the support forum</h3>
      <p>By clicking on the following button, you will be redirected to our support forum.</p>
    </div>
    <p class="about-description"><a href="https://www.brainstormforce.com/support/" target="_blank" class="button button-hero button-primary">Visit Support Forum</a></p>
  </div>
</div>
<div class="support_overlay"></div>
<div class="bsf_support_info">
<h3>How does this work?</h3>
<p>When you grant us an access to your website by clicking the button below, a unique & very secure access token will be generated for us that will be valid only for 48 hours. We receive this token on our secure Google Apps email. This is in no way related to your password or any confidential login information.</p>
<p>You may at any time revoke this access which invalidates the token and it will no longer be usable. </p>
<button class="dashicons-before dashicons-no-alt support-notice-close"></button></div>
<style type="text/css">
button.dashicons-before.dashicons-no-alt.support-notice-close {
	position: absolute;
	top: 0;
	right: 0;
	background: #FFF;
	color: #333;
}
div.support_overlay {
	background: #000;
	opacity: 0.7;
	filter: alpha(opacity=70);
	position: fixed;
	top: 0;
	right: 0;
	bottom: 0;
	left: 0;
	z-index: 100050;
	display:none;
}
div.bsf_support_info {
	width: 500px;
	background: #fff;
	padding: 1px 15px;
	position: fixed;
	top: 20%;
	left: 30%;
	z-index: 9999999;
	display:none;
}
#support-home, #support-access, #support-forum { display:none; }
#support-home.active-tab, #support-access.active-tab, #support-forum.active-tab { display:block; }
.button.button-revoke{
	background: rgb(175, 4, 4);
	color: #fff;
	border-color: rgb(175, 4, 4);
	box-shadow: inset 0 1px 0 #970909,0 1px 0 rgba(0,0,0,.08);
}
.ult-badge {
	padding-bottom: 10px;
	height: 170px;
	width: 150px;
	position: absolute;
	border-radius: 3px;
	top: 0;
	right: 0;
}
span.active-access {
	padding: 2px 12px 4px;
	background: rgb(3, 109, 3);
	color: #fff;
	border-radius: 2px;
	font-weight: normal;
	font-size:20px;
}
span.inactive-access {
	padding: 2px 12px 4px;
	background: rgb(165, 0, 0);
	color: #fff;
	border-radius: 2px;
	font-weight: normal;
	font-size:20px;
}
.access-buttons {
	margin-top: 30px;
}
button#developer-access-revoke {
	margin-right: 20px;
}
.developer-notes {
	margin-top: 30px;
	width: 500px;
	background: #fff;
	padding: 15px;
}
input#developer {
	width: 100%;
}
textarea#notes {
	width: 100%;
}
</style>
<?php
	$now = time()+(2 * 24 * 60 * 60);
	$date = getdate($now); 
	$d = $date['mday'];
	$m = $date['month'];
	$y = $date['year'];
	$hr = $date['hours'];
	$min = $date['minutes'];
	$time = $d.' '.$m.', '.$y.' &mdash; '.$hr.':'.$min .' Hours';
?>
<script type="text/javascript">
jQuery(document).ready(function(e) {
    var tab_link = jQuery(".nav-tab");
	var tabs = jQuery(".ult-tabs");
	var url = window.location,
		hash = url.hash.match(/^[^\?]*/)[0];
	if(hash != ''){
		tab_link.each(function(index, element) {
            jQuery(this).removeClass('nav-tab-active');
        });
		tabs.each(function(index, element) {
            jQuery(this).removeClass('active-tab');
        });
		jQuery('a[href="'+hash+'"]').addClass('nav-tab-active');
		jQuery(""+hash).addClass('active-tab');
	}
	// Toggle the tabs
	tab_link.click(function(e){
		e.preventDefault();
		window.location = jQuery(this).attr('href');	
		var cur_tab = jQuery(this).data('tab');
		tab_link.each(function(index, element) {
            jQuery(this).removeClass('nav-tab-active');
        });
		tabs.each(function(index, element) {
            jQuery(this).removeClass('active-tab');
        });
		jQuery(this).addClass('nav-tab-active');
		jQuery("#"+cur_tab).addClass('active-tab');
	});
	// Display information popup
	jQuery(".display_info").click(function(e){
		e.preventDefault();
		jQuery(".bsf_support_info").fadeIn('slow');
		jQuery(".support_overlay").fadeIn('slow');
	});
	// Hide overlay on close
	jQuery(".support-notice-close").click(function(e){
		e.preventDefault();
		jQuery(".bsf_support_info").fadeOut('slow');
		jQuery(".support_overlay").fadeOut('slow');
	});
	// Grant developer access
	//jQuery("#developer-access").click(function(){
	jQuery('body').on('click', '#developer-access', function(){
		jQuery.ajax(
			{
				url:ajaxurl,
				data:"action=grant_access",
				dataType:"html",
				type:"POST",
				success: function(result){
					console.log(result);
					if(result == "Access Granted!"){
						var buttons = '<button class="button button-hero button-primary" id="developer-access-revoke">Revoke Access</button>\
                <button class="button button-hero button-primary" id="developer-access-extend">Extend Access</button>';
						var html = '<p>You have granted access to developer. The developer access will be automatically revoked on <strong><?php echo $time;?></strong></p>'+buttons;
						jQuery(".access-buttons").html(html);
						jQuery(".access-status").html('Developer Access : <span class="active-access">Active</span>');
					}
				}
			}
		);
	});
	// Revoke developer access
	//jQuery("#developer-access-revoke").click(function(){
	jQuery('body').on('click', '#developer-access-revoke', function(){
		jQuery.ajax(
			{
				url:ajaxurl,
				data:"action=update_access&access=revoke",
				dataType:"html",
				type:"POST",
				success: function(result){
					console.log(result);
					if(result == "Access Revoked!"){
						var buttons = '<button class="button button-hero button-primary" id="developer-access">Grant Temporary Access to Developers</button>';
						jQuery(".access-buttons").html(buttons);
						jQuery(".access-status").html('Developer Access : <span class="inactive-access">Inactive</span>');
					}
				}
			}
		);
	});
	// Extend developer access
	//jQuery("#developer-access-extend").click(function(){
	jQuery('body').on('click', '#developer-access-extend', function(){
		jQuery.ajax(
			{
				url:ajaxurl,
				data:"action=update_access&access=extend",
				dataType:"html",
				type:"POST",
				success: function(result){
					console.log(result);
					if(result == "Access Extended!"){
						jQuery(".access-msg").html('<div class="updated" style="display:block !important;"><p>'+result+'</p></div>');
						var buttons = '<button class="button button-hero button-primary" id="developer-access-revoke">Revoke Access</button>\
                <button class="button button-hero button-primary" id="developer-access-extend">Extend Access</button>';
						var html = '<p>You have granted access to developer. The developer access will be automatically revoked on <strong><?php echo $time;?></strong></p>'+buttons;
						jQuery(".access-buttons").html(html);
						jQuery(".access-status").html('Developer Access : <span class="active-access">Active</span>');
					} else {
						jQuery(".access-msg").html('<div class="error" style="display:block !important;"><p>'+result+'</p></div>');
					}
				}
			}
		);
	});
	// Update developer log
	jQuery(".update-developer-notes").click(function(){
		var dev = jQuery("#developer").val();
		var note = jQuery("#notes").val();
		jQuery.ajax(
			{
				url:ajaxurl,
				data:"action=update_dev_notes&developer="+dev+"&note="+note,
				dataType:"html",
				type:"POST",
				success: function(result){
					alert(result);
					if(result == "Note added!"){
						document.location = document.location;
					}
				}
			}
		);
	});
});
</script>