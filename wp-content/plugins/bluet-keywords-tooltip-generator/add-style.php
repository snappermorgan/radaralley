<?php
defined('ABSPATH') or die("No script kiddies please!");

function bluet_kw_custom_style(){
	$style_options=get_option('bluet_kw_style');
	
	$tooltip_color=$style_options['bt_kw_tt_color'];
	$tooltip_bg_color=$style_options['bt_kw_tt_bg_color'];
	$bt_kw_on_background=$style_options['bt_kw_on_background'];
	
	$desc_color=$style_options['bt_kw_desc_color'];
	$desc_bg_color=$style_options['bt_kw_desc_bg_color'];
	
	$desc_font_size=(empty($style_options['bt_kw_desc_font_size'])? 17 : $style_options['bt_kw_desc_font_size']);
	
	$is_important="";
	
	if(!is_admin()){ 
		$is_important=" !important";
	}
	?>
	<style>

	/*for alt images tooltips*/
	.bluet_tooltip_alt{
		max-width: 250px;
		padding: 1px 5px;
		text-align: center;
		color: <?php echo $desc_color; ?> <?php echo($is_important)?>;
		background-color: <?php echo $desc_bg_color; ?> <?php echo($is_important)?>;
		position: absolute;
		border-radius: 4px;
		z-index:9999;
	}
	
	.bluet_tooltip{
		text-decoration: none; 
		color: <?php echo $tooltip_color; ?> <?php echo($is_important)?>;
		
		<?php
		if(!$bt_kw_on_background){
			echo("background: ".$tooltip_bg_color." ".$is_important.";");
			
			echo("padding: 1px 7px 4px 7px;");
			echo("font-size: 1em;");
			echo("border-radius: 10px;");
		}else{
			echo("border-bottom: ".$tooltip_color." 1px dotted;");
		}
		?>
	}
	
	.bluet_block_to_show{
		display:none;	
		max-width:400px;
		z-index:9999;
		padding-top:10px;
		
		position: absolute;
		width:300px;
		height: auto;
	}
	.bluet_block_container{		  
		color: <?php echo $desc_color; ?> <?php echo($is_important)?>;
		background: <?php echo $desc_bg_color; ?> <?php echo($is_important)?>;
		border-radius: 4px;
		box-shadow: 0px 0px 10px #6A7563;
		padding: 13px;
		font-size:<?php echo $desc_font_size; ?>px <?php echo($is_important)?>;
		line-height: normal;
		font-weight: normal;
		display:inline-block;
		width:inherit;
	}
	
	.bluet_block_container img
	{
		float:left;
		margin-bottom:8px;
		border: none !important;
		border-radius: 4px;
		width:100%;
		height: auto;
	}
	

	.bluet_block_to_show:after {
	  content: '';
	  position: absolute;
	  bottom: 100%;
	  left: 50%;
	  top:4px;
	  margin-left: -8px;
	  width: 0;
	  height: 0;
	  border-bottom: 7px solid <?php echo $desc_bg_color; ?>;
	  border-right: 8px solid transparent;
	  border-left: 8px solid transparent;
	}
	
	.bluet-hide-excluded{
		display:none;
	}
	
	.bluet_title_on_block{
		text-transform: capitalize;
		font-weight: bold;
	}
	
	</style>
	<?php
}