<style>
#mymail-map li{
	border-bottom: 1px solid #CCC;
}
#mymail-map li label{
	display: inline-block;
	width: 150px;
}
#mymail-map li label select{
	display: inline-block;
	width: 200px;
}
#mymail-map li ul li{
	border-bottom: 0;
	padding-left: 20px;
}

</style>
<?php
		$form_id = isset( $_GET['id'] ) ? intval($_GET['id']) : null;
		$form = RGFormsModel::get_form_meta( $form_id );
		$mymail = isset($form['mymail']) ? $form['mymail'] : array('lists' => array());

?>
<div class="gform_panel gform_panel_mymail_settings" id="mymail_settings">

		<h3><span><?php _e('MyMail Settings', 'mymail-gravitiyforms') ?></span></h3>

		<form action="" method="post" id="gform_form_settings">

		<table class="gforms_form_settings" cellspacing="0" cellpadding="0">
			<tr>
				<th></th>
				<td><label><input type="checkbox" name="mymail[active]" value="1" <?php checked(isset($mymail['active'])) ?>> <?php _e('Enable MyMail for this Form', 'mymail-gravitiyforms') ?></label>
				</td>
			</tr>
			<tr>
				<th><?php _e('Map Fields', 'mymail-gravitiyforms') ?></th>
				<td>
				<p class="description"><?php _e('define which field represents which value from your MyMail settings', 'mymail-gravitiyforms') ?></p>
				<?php
				$fields = array(
					'email' => mymail_text('email'),
					'firstname' => mymail_text('firstname'),
					'lastname' => mymail_text('lastname'),
				);
				if ($customfields = mymail_option('custom_field')) {
					foreach ($customfields as $field => $data) {
						$fields[$field] = $data['name'];
					}
				}
				$optionsdd = '<option value="-1">'.__('choose', 'mymail-gravitiyforms').'</option>';
				foreach($fields as $id => $name){
					$optionsdd .= '<option value="'.$id.'">'.$name.'</option>';
				}
				
				if(is_array($form['fields'])){
					echo '<ul id="mymail-map">';
					foreach($form['fields'] as $field){
						if(isset($field["inputs"]) && is_array($field["inputs"])){
							echo '<li><strong>'.(!empty($field['label']) ? $field['label'] : __('Untitled', 'mymail-gravitiyforms')).':</strong><ul>';
							
							foreach($field["inputs"] as $input){
								echo '<li><label>'.$input['label'].'</label> ➨ <select name="mymail[map]['.$input['id'].']" >';
									echo '<option value="-1">'.__('not mapped', 'mymail-gravitiyforms').'</option>';
									foreach($fields as $id => $name){
										echo '<option value="'.$id.'" '.selected($id, $mymail['map'][$input['id'].''], false).'>'.$name.'</option>';
									}
								echo '</select></li>';
							}
								
							echo '</ul></li>';

						}else{
							echo '<li> <label><strong>'.$field['label'].'</strong></label> ➨ <select name="mymail[map]['.$field['id'].']">';
								echo '<option value="-1">'.__('not mapped', 'mymail-gravitiyforms').'</option>';
								foreach($fields as $id => $name){
									echo '<option value="'.$id.'" '.selected($id, $mymail['map'][$field['id'].''], false).'>'.$name.'</option>';
								}
							echo '</select></li>';
						}
					}
					echo '</ul>';
				}
				
				?>
				
				</td>
			</tr>
			<tr>
				<th><?php _e('Subscribe new users to', 'mymail-gravitiyforms') ?></th>
				<td>
				<?php
			$lists = mymail('lists')->get();
			$selected = isset($mymail['lists']) ? $mymail['lists'] : array();
	
			if(!empty($lists)){
				foreach( $lists as $list){
				?>
				<label><input type="checkbox" name="mymail[lists][]" value="<?php echo $list->ID ?>" <?php checked(in_array($list->ID, $selected)) ?>> <?php echo $list->name?></label><br>
				<?php 
				}
				?>
				<?php 
			}else{
				?>
				<p class="description"><?php _e('No Lists found!' ,'mymail-gravitiyforms'); echo ' <a href="edit.php?post_type=newsletter&page=mymail_lists&new">'.__('Create a List now' ,'mymail-gravitiyforms').'</a>';?></p>	
				<?php 
			}
			?>
				</td>
			</tr>
			<tr>
				<th><?php _e('Double Opt In', 'mymail-gravitiyforms') ?></th>
				<td><label><input type="checkbox" name="mymail[double-opt-in]" value="1" <?php checked(isset($mymail['double-opt-in'])) ?>> <?php _e('Users have to confirm their subscription', 'mymail-gravitiyforms') ?></label><br>

				</td>
			</tr>

			<tr>
				<th><?php _e('Conditional check', 'mymail-gravitiyforms') ?></th>
				<td><label><input type="checkbox" name="mymail[conditional]" value="1" <?php checked(isset($mymail['conditional'])) ?>> <?php _e('Enable Conditional check', 'mymail-gravitiyforms') ?></label>
				<p><?php _e('subscribe user only if', 'mymail-gravitiyforms') ?> 
				<?php
				if(is_array($form['fields'])){
					echo '<select name="mymail[conditional_field]"><option value="-1">-</option>';
					foreach($form['fields'] as $field){
						if(!in_array($field['type'], array('checkbox', 'radio'))) continue;
	
						if(isset($field["inputs"]) && is_array($field["inputs"])){
							echo '<optgroup label="'.($field['label'] ? $field['label'] : __('Checkbox', 'mymail-gravitiyforms')).'">';
							foreach($field["inputs"] as $input)
								echo '<option value="'.$input['id'].'" '.selected($input['id'], $mymail['conditional_field'], false).'>'.$input['label'].'</option>';
							echo '</optgroup>';
								
						}elseif(isset($field["choices"]) && is_array($field["choices"])){
							echo '<optgroup label="'.$field['label'].'">';
							foreach($field["choices"] as $input)
								echo '<option value="'.$field['id'].'|'.$input['value'].'" '.selected($input['value'], $mymail['conditional_field'], false).'>'.$input['text'].'</option>';
							echo '</optgroup>';
								
						}else{
							echo '<option value="'.$field['id'].'" '.selected($input['id'], $mymail['conditional_field'], false).'>sss'.$field['label'].'</option>';
						}
					}
					echo '</select>';
				}
				?>
				<?php _e('is checked', 'mymail-gravitiyforms') ?></p>
				</td>
			</tr>
		</table>
		<?php wp_nonce_field( 'mymail_gf_save_form', 'gform_save_form_settings' ); ?>
		<input type="hidden" id="gform_meta" name="gform_meta">
		<input type="submit" id="gform_save_settings" name="gform_save_settings" value="<?php _e('Update Form Settings', 'mymail-gravitiyforms') ?>" class="button-primary gfbutton">

		</form>
		

</div>