<div class="wrap about-wrap">
	<h1><?php echo __("Ultimate Addon Settings","ultimate"); ?></h1>
    <div class="about-text">Enable or disable the features as per your preference..</div>
    <div class="ult-badge" style="background:url(<?php echo plugins_url('img/brainstorm-logo.png',__FILE__); ?>) no-repeat top center; background-size: 150px;"></div>
    <div id="msg"></div>
    <h2 class="nav-tab-wrapper"> 
    	<a href="#ultimate-modules" data-tab="ultimate-modules" class="nav-tab nav-tab-active"> 
  		Modules </a> <a href="#ultimate-settings" data-tab="ultimate-settings" class="nav-tab"> 
  		Advanced Settings </a> </h2>
    <?php
	$ultimate_modules = get_option('ultimate_modules');
	$ultimate_row = get_option('ultimate_row');
	$ultimate_animation = get_option('ultimate_animation');
	$checked = '';
	if($ultimate_row == "enable"){
		$checked_row = 'checked="checked"';
	} else {
		$checked_row = '';
	}
	
	if($ultimate_animation == "enable"){
		$ultimate_animation = 'checked="checked"';
	} else {
		$ultimate_animation = '';
	}
	$modules = array(
		'Ultimate_Animation' => 'Animation Block',
		'Ultimate_Buttons' => 'Advanced Buttons',
		'Ultimate_CountDown' => 'Count Down Timer',
		'Ultimate_Flip_Box' => 'Flip Boxes',
		'Ultimate_Google_Maps' => 'Google Maps',
		'Ultimate_Google_Trends' => 'Google Trends',
		'Ultimate_Headings' => 'Headings',
		'Ultimate_Icon_Timeline' => 'Timeline',
		'Ultimate_Info_Box' => 'Info Boxes',
		'Ultimate_Info_Circle' => 'Info Circle',
		'Ultimate_Info_List' => 'Info List',
		'Ultimate_Info_Tables' => 'Info Tables',
		'Ultimate_Interactive_Banners' => 'Interactive Banners',
		'Ultimate_Interactive_Banner_2' => 'Interactive Banners - 2',
		'Ultimate_Modals' => 'Modal Popups',
		//'Ultimate_Parallax' => 'Row Backgrounds',
		'Ultimate_Pricing_Tables' => 'Price Box',
		'Ultimate_Spacer' => 'Spacer / Gap',
		'Ultimate_Stats_Counter' => 'Stats Counter',
		'Ultimate_Swatch_Book' => 'Swatch Book',
		'Ultimate_Icons' => 'Icons',
		'Ultimate_List_Icon' => 'List Icons',
		'WooComposer' => 'WooComposer',
	);
	?>
    <div id="ultimate-modules" class="ult-tabs active-tab">
    <form method="post" id="ultimate_modules">
    	<input type="hidden" name="action" value="update_ultimate_modules" />
    	<table class="form-table">
        	<tbody>
            	<?php
					$i = 1; 
					foreach($modules as $module => $label){
						if(is_array($ultimate_modules) && !empty($ultimate_modules)){ 
							if(in_array(strtolower($module),$ultimate_modules)){
								$checked = 'checked="checked"';
							} else {
								$checked = '';
							}
						}
						if(($i % 2) == 1){
						?>
						<tr valign="top" style="border-bottom: 1px solid #ddd;">
						<?php } ?>
							<th scope="row"><?php echo $label; ?></th>
							<td> 
							<div class="onoffswitch">
								<input type="checkbox" <?php echo $checked; ?> class="onoffswitch-checkbox" value="<?php echo strtolower($module); ?>" id="<?php echo strtolower($module); ?>" name="ultimate_modules[]" />
								
								<label class="onoffswitch-label" for="<?php echo strtolower($module); ?>">
									<div class="onoffswitch-inner">
										<div class="onoffswitch-active">
											<div class="onoffswitch-switch">ON</div>
										</div>
										<div class="onoffswitch-inactive">
											<div class="onoffswitch-switch">OFF</div>
										</div>
									</div>
								</label>
							</div>
							</td>
						<?php if(($i % 2) == 1){ ?>
						<!-- </tr> -->
						<?php } ?>
                <?php $i++; } ?>
            </tbody>
        </table>
    </form>
	<p class="submit"><input type="submit" name="submit" id="submit-modules" class="button button-large button-primary" value="<?php echo __("Save Changes","ultimate");?>"></p>
    </div>
    
    <div id="ultimate-settings" class="ult-tabs">
    <form method="post" id="ultimate_settings">
    	<input type="hidden" name="action" value="update_ultimate_options" />
    	<table class="form-table">
        	<tbody>
                <tr valign="top">
                	<th scope="row"><?php echo __("Row backgrounds","ultimate"); ?></th>
                    <td> <div class="onoffswitch">
                    <input type="checkbox" <?php echo $checked_row; ?> id="ultimate_row" value="enable" class="onoffswitch-checkbox" name="ultimate_row" />
                         <label class="onoffswitch-label" for="ultimate_row">
                            <div class="onoffswitch-inner">
                                <div class="onoffswitch-active">
                                    <div class="onoffswitch-switch">ON</div>
                                </div>
                                <div class="onoffswitch-inactive">
                                    <div class="onoffswitch-switch">OFF</div>
                                </div>
                            </div>
                        </label>
                        </div>
					</td>
                </tr>
                <tr valign="top">
                	<th scope="row"><?php echo __("Animation Block on Mobile","ultimate"); ?></th>
                    <td> <div class="onoffswitch">
                    <input type="checkbox" <?php echo $ultimate_animation; ?> id="ultimate_animations" value="enable" class="onoffswitch-checkbox" name="ultimate_animation" />
                         <label class="onoffswitch-label" for="ultimate_animations">
                            <div class="onoffswitch-inner">
                                <div class="onoffswitch-active">
                                    <div class="onoffswitch-switch">ON</div>
                                </div>
                                <div class="onoffswitch-inactive">
                                    <div class="onoffswitch-switch">OFF</div>
                                </div>
                            </div>
                        </label>
                         </div>
					</td>
                </tr>
            </tbody>
        </table>
    </form>
	<p class="submit"><input type="submit" name="submit" id="submit-settings" class="button button-large button-primary" value="<?php echo __("Save Changes","ultimate");?>"></p>
    </div>
</div>
<script type="text/javascript">
var submit_btn = jQuery("#submit-modules");
submit_btn.bind('click',function(e){
	e.preventDefault();
	var data = jQuery("#ultimate_modules").serialize();
	jQuery.ajax({
		url: ajaxurl,
		data: data,
		dataType: 'html',
		type: 'post',
		success: function(result){
			console.log(result);
			if(result == "success"){
				jQuery("#msg").html('<div class="updated"><p>Settings updated successfully!</p></div>');
				jQuery("#msg").scrollTop(0);
			} else {
				jQuery("#msg").html('<div class="error"><p>No settings were updated.</p></div>');
				jQuery("#msg").scrollTop(0);
			}
		}
	});
});

var submit_btn = jQuery("#submit-settings");
submit_btn.bind('click',function(e){
	e.preventDefault();
	var data = jQuery("#ultimate_settings").serialize();
	jQuery.ajax({
		url: ajaxurl,
		data: data,
		dataType: 'html',
		type: 'post',
		success: function(result){
			console.log(result);
			if(result == "success"){
				jQuery("#msg").html('<div class="updated"><p>Settings updated successfully!</p></div>');
			} else {
				jQuery("#msg").html('<div class="error"><p>No settings were updated.</p></div>');
			}
		}
	});
});

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
});
</script>
<style type="text/css">
/*On Off Checkbox Switch*/
.onoffswitch {
	position: relative;
	width: 95px;
	display: inline-block;
	float: left;
	margin-right: 15px;
	-webkit-user-select:none;
	-moz-user-select:none;
	-ms-user-select: none;
}
.onoffswitch-checkbox {
	display: none !important;
}
.onoffswitch-label {
	display: block;
	overflow: hidden;
	cursor: pointer;
	border: 0px solid #999999;
	border-radius: 0px;
}
.onoffswitch-inner {
	width: 200%;
	margin-left: -100%;
	-moz-transition: margin 0.3s ease-in 0s;
	-webkit-transition: margin 0.3s ease-in 0s;
	-o-transition: margin 0.3s ease-in 0s;
	transition: margin 0.3s ease-in 0s;
}
.onoffswitch-inner > div {
	float: left;
	position: relative;
	width: 50%;
	height: 24px;
	padding: 0;
	line-height: 24px;
	font-size: 12px;
	color: white;
	font-weight: bold;
	-moz-box-sizing: border-box;
	-webkit-box-sizing: border-box;
	box-sizing: border-box;
}
.onoffswitch-inner .onoffswitch-active {
	padding-left: 15px;
	background-color: #CCCCCC;
	color: #FFFFFF;
}
.onoffswitch-inner .onoffswitch-inactive {
	padding-right: 15px;
	background-color: #CCCCCC;
	color: #FFFFFF;
	text-align: right;
}
.onoffswitch-switch {
	/*width: 50px;*/
	width:35px;
	margin: 0px;
	text-align: center;
	border: 0px solid #999999;
	border-radius: 0px;
	position: absolute;
	top: 0;
	bottom: 0;
}
.onoffswitch-active .onoffswitch-switch {
	background: #3F9CC7;
	left: 0;
}
.onoffswitch-inactive .onoffswitch-switch {
	background: #7D7D7D;
	right: 0;
}
.onoffswitch-active .onoffswitch-switch:before {
	content: " ";
	position: absolute;
	top: 0;
	/*left: 50px;*/
	left:35px;
	border-style: solid;
	border-color: #3F9CC7 transparent transparent #3F9CC7;
	/*border-width: 12px 8px;*/
	border-width: 15px;
}
.onoffswitch-inactive .onoffswitch-switch:before {
	content: " ";
	position: absolute;
	top: 0;
	/*right: 50px;*/
	right:35px;
	border-style: solid;
	border-color: transparent #7D7D7D #7D7D7D transparent;
	/*border-width: 12px 8px;*/
	border-width: 50px;
}
.onoffswitch-checkbox:checked + .onoffswitch-label .onoffswitch-inner {
	margin-left: 0;
}
#ultimate-settings, #ultimate-modules{ display:none; }
#ultimate-settings.active-tab, #ultimate-modules.active-tab{ display:block; }
.ult-badge {
	padding-bottom: 10px;
	height: 170px;
	width: 150px;
	position: absolute;
	border-radius: 3px;
	top: 0;
	right: 0;
}
div#msg > .updated, div#msg > .error { display:block !important;}
div#msg {
	position: absolute;
	left: 0;
	top: 100px;
	max-width: 30%;
}
</style>