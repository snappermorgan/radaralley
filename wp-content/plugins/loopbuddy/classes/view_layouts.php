<?php


/*
Layout editor listing.
Written by Dustin Bolton, Ronald Huereca, and Chris Jean for iThemes.com
Version 1.3.0

Version History
	1.3.0 - 2012-12-01 - Chris Jean
		Starting at version 1.3.0 to match the plugin release version.
		Misc code cleanup.
		Updated editor interface to better match WordPress standards.
		Improved error message output.
*/


$this->admin_scripts();

if ( ! empty( $_REQUEST['action'] ) )
	$action = $_REQUEST['action'];
if ( ! empty( $_REQUEST['action2'] ) )
	$action = $_REQUEST['action2'];

if ( empty( $action ) ) {
	if ( ! empty( $_POST['create_group'] ) )
		$action = 'create';
	else if ( ! empty( $_POST['import_groups'] ) )
		$action = 'import';
	else
		$action = '';
}


?>

<div class="wrap">
	<?php
		if ( 'create' == $action ) {
			check_admin_referer( $this->_parent->_var . '-nonce' );
			if ( empty( $_POST['group_name'] ) ) {
				$this->alert( __( 'You must provide a Layout Name.', 'it-l10n-loopbuddy' ), true );
			}
			else {
				$errors = false;
				foreach ( (array) $this->_options['layouts'] as $id => $group ) {
					if ( $group['title'] == htmlentities( $_POST['group_name'] ) ) {
						$this->alert( __( 'This Layout Name already exists. Please choose another name.', 'it-l10n-loopbuddy' ), true );
						$errors = true;
						break;
					}
				}
				
				if ( $errors === false ) {
					$this_groupoptions = $this->_parent->_layoutdefaults;
					
					$this_groupoptions['title'] = htmlentities( $_POST['group_name'] );
					array_push( $this->_options['layouts'], $this_groupoptions );
					$this->_parent->save();
					
					$this->alert( sprintf( __( 'Layout &quot;%s&quot; has been added.', 'it-l10n-loopbuddy' ), htmlentities( stripslashes( $_POST['group_name'] ) ) ) );
				}
			}
		}
		else if ( 'delete' == $action ) {
			if ( ! empty( $_REQUEST['items'] ) && is_array( $_REQUEST['items'] ) ) {
				$deleted_groups = '';
				
				foreach ( (array) $_REQUEST['items'] as $id ) {
					$deleted_groups .= ' "' . stripslashes( $this->_options['layouts'][$id]['title'] ) . '",';
					unset( $this->_options['layouts'][$id] );
				}
				
				$this->_parent->save();
				$this->alert( sprintf( _n( 'Deleted layout %s.', 'Deleted layouts %s', count( $_REQUEST['items'] ), 'it-l10n-loopbuddy' ), trim( $deleted_groups, ', ' ) ) );
			}
			else {
				$this->alert( __( 'No layouts were selected for deletion.', 'it-l10n-loopbuddy' ), true );
			}
		}
		else if ( 'import' == $action ) {
			if ( isset( $_FILES['import_data'] ) ) {
				$filename = $_FILES['import_data']['tmp_name'];
				
				if ( empty( $filename ) ) {
					$this->alert( __( 'You must supply a file to import.', 'it-l10n-loopbuddy' ), true );
				}
				else {
					$result = $this->_parent->import( $filename, 'layouts' );
					
					if ( is_wp_error( $result ) )
						$this->alert( $result->get_error_message(), true );
					else
						$this->alert( sprintf( _n( '%1$s layout has been imported', '%1$s layouts have been imported', $result, 'it-l10n-loopbuddy' ), number_format( $result ) ) );
				}
			}
			else {
				$this->alert( __( 'You must supply a file to import.', 'it-l10n-loopbuddy' ), true );
			}
		}
		else if ( 'duplicate' == $action ) {
			if ( ! empty( $_REQUEST['items'] ) && is_array( $_REQUEST['items'] ) ) {
				$duplicate_items = array();
				
				foreach ( (array) $_REQUEST['items'] as $id ) {
					$duplicate_items[ $id ] = $this->_options[ 'layouts' ][ $id ];
				}
				
				$result = $this->_parent->import( $duplicate_items, 'layouts' );
				
				$this->alert( sprintf( _n( 'Layout duplicated.', '%1$s layouts duplicated.', $result, 'it-l10n-loopbuddy' ), number_format( $result ) ) );
			}
			else {
				$this->alert( __( 'No layouts were selected for duplication.', 'it-l10n-loopbuddy' ), true );
			}
		}
		
		if ( 'edit' == $action ) {
			require( 'view_layouts-edit.php' );
		}
		else {
			
?>
	<h2><img src="<?php echo $this->_pluginURL; ?>/images/loopbuddy_rings.png" style="vertical-align: -4px;"> <?php _e( 'Layouts', 'it-l10n-loopbuddy' ); ?></h2>
	
	<form id="posts-filter" enctype="multipart/form-data" method="post" action="<?php echo $this->_selfLink; ?>-layouts">
		<div class="tablenav top">
			<div class="alignleft actions">
				<select name="action">
					<option selected="selected" value=""><?php _e( 'Bulk Actions' ); ?></option>
					<option value="delete"><?php _e( 'Delete', 'it-l10n-loopbuddy' ); ?></option>
					<option value="duplicate"><?php _e( 'Duplicate', 'it-l10n-loopbuddy' ); ?></option>
					<option value="export_layouts"><?php _e( 'Export', 'it-l10n-loopbuddy' ); ?></option>
				</select>
				<input type="submit" id="doaction" class="button action" value="<?php _e( 'Apply' ); ?>" />
			</div>
		</div>
		
		<table class="wp-list-table widefat fixed" cellspacing="0">
			<thead>
				<tr class="thead">
					<th id="cb" scope="col" class="manage-column column-cb check-column"><input type="checkbox" class="check-all-entries" /></th>
					<th><?php _e( 'Layout Name', 'it-l10n-loopbuddy' ); ?></th>
				</tr>
			</thead>
			<tfoot>
				<tr class="thead">
					<th scope="col" class="manage-column column-cb check-column"><input type="checkbox" class="check-all-entries" /></th>
					<th><?php _e( 'Layout Name', 'it-l10n-loopbuddy' ); ?></th>
				</tr>
			</tfoot>
			<tbody>
				<?php if ( empty( $this->_options['layouts'] ) ) : ?>
					<tr><td colspan="4" style="text-align: center;"><i><?php printf( __( 'Please add a new %s group below to get started.', 'it-l10n-loopbuddy' ), esc_html( $this->_name ) ); ?></i></td></tr>
				<?php else: ?>
					<?php uasort( $this->_options['layouts'], 'pb_lb_sort_by_title' ); ?>
					
					<?php foreach ( (array) $this->_options['layouts'] as $id => $group ) : ?>
						<tr class="entry-row alternate" id="entry-<?php echo $id; ?>">
							<th scope="row" class="check-column"><input type="checkbox" name="items[]" class="entries" value="<?php echo $id; ?>" /></th>
							<td>
								<strong>
									<a class="row-title" title="Edit &quot;<?php echo $group['title']; ?>&quot;" href="<?php echo $this->_selfLink; ?>-layouts&action=edit&id=<?php echo $id; ?>">
										<?php echo stripslashes( $group['title'] ); ?>
									</a>
								</strong>
								
								<div class="row-actions">
									<span class="edit">
										<a href="<?php echo $this->_selfLink; ?>-layouts&action=edit&id=<?php echo $id; ?>"><?php _e( 'Edit', 'it-l10n-loopbuddy' ); ?></a> |
									</span>
									<span class="duplicate">
										<a href="<?php echo $this->_selfLink; ?>-layouts&action=duplicate&items[]=<?php echo $id; ?>"><?php _e( 'Duplicate', 'it-l10n-loopbuddy' ); ?></a> |
									</span>
									<span class="trash">
										<a class="submitdelete" href="<?php echo $this->_selfLink; ?>-layouts&action=delete&items[]=<?php echo $id; ?>"><?php _e( 'Delete', 'it-l10n-loopbuddy' ); ?></a>
									</span>
								</div>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
		
		<div class="tablenav bottom">
			<div class="alignleft actions">
				<select name="action2">
					<option selected="selected" value=""><?php _e( 'Bulk Actions' ); ?></option>
					<option value="delete"><?php _e( 'Delete', 'it-l10n-loopbuddy' ); ?></option>
					<option value="duplicate"><?php _e( 'Duplicate', 'it-l10n-loopbuddy' ); ?></option>
					<option value="export_layouts"><?php _e( 'Export', 'it-l10n-loopbuddy' ); ?></option>
				</select>
				<input type="submit" id="doaction" class="button action" value="<?php _e( 'Apply' ); ?>" />
			</div>
		</div>
		
		<?php $this->nonce(); ?>
	</form>
	<br />
	
	<h3><?php printf( __( 'Add New %s Layout', 'it-l10n-loopbuddy' ), esc_html( $this->_name ) ); ?></h3>
	<form method="post" action="<?php echo esc_attr( $this->_selfLink ); ?>-layouts">
		<table class="form-table">
			<tr>
				<td><label for="group_name"><?php _e( 'Layout Name', 'it-l10n-loopbuddy' ); ?><?php $this->tip( __( 'Name of the new group to create. This is for your convenience and not publicly displayed.', 'it-l10n-loopbuddy' ) ); ?></label></td>
				<td><input type="text" name="group_name" id="group_name" size="45" maxlength="45" /></td>
			</tr>
		</table>
		<p class="submit"><input type="submit" name="create_group" value="<?php _e( '+ Add Group', 'it-l10n-loopbuddy' ); ?>" class="button-primary" /></p>
		<?php $this->nonce(); ?>
	</form>
	
	<h3><?php printf( __( 'Import Layouts', 'it-l10n-loopbuddy' ), esc_html( $this->_name ) ); ?></h3>
	<form method="post" action="<?php echo esc_attr( $this->_selfLink ); ?>-layouts" enctype="multipart/form-data">
		<table class="form-table">
			<tr>
				<td><input type="file" name='import_data' /></td>
			</tr>
		</table>
		<p class="submit"><input type="submit" name="import_groups" value="<?php _e( 'Import', 'it-l10n-loopbuddy' ); ?>" class="button-primary" /></p>
		<?php $this->nonce(); ?>
	</form>
<?php
			
		}
	?>
</div>
