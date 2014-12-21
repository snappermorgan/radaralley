<?php

/*
Query editor listing.
Written by Dustin Bolton, Ronald Huereca, and Chris Jean for iThemes.com
Version 1.1.0

Version History
	1.0.0 - 2012-09-11 - Chris Jean
		Setting version to 1.0.0
	1.0.1 - 2012-09-11 - Chris Jean
		Removed errant "hello" text from output.
		Removed commented debug code.
	1.1.0 - 2012-12-01 - Chris Jean
		Misc code cleanup.
		Changed the editor format to better match WordPress editors.
		Improved error messages.
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
		$this->alert( __( 'You must provide a Query Name.', 'it-l10n-loopbuddy' ), true );
	}
	else {
		$errors = false;
		foreach ( (array) $this->_options['queries'] as $id => $group ) {
			
			if ( $group['title'] == htmlentities( $_POST['group_name'] ) ) {
				$this->alert( __( 'This Query Name already exists. Please choose another name.', 'it-l10n-loopbuddy' ), true );
				$errors = true;
				break;
			}
		}
		include_once( $this->_pluginPath . '/classes/queryitems.php' );
		$lb_query_items = new loopbuddy_queryitems( $this->_parent );
		if ( $errors === false ) {
			$this_groupoptions = $lb_query_items->get_defaults( array( 'title' => sanitize_text_field( $_POST[ 'group_name' ] ) ) );
			
			
			array_push( $this->_options['queries'], $this_groupoptions );
			$this->_parent->save();
			
			$this->alert( sprintf( __( 'Query &quot;%s&quot; has been added.', 'it-l10n-loopbuddy' ), htmlentities( stripslashes( $_POST['group_name'] ) ) ) );
		}
	}
}
else if ( 'delete' == $action ) {
	if ( ! empty( $_REQUEST['items'] ) && is_array( $_REQUEST['items'] ) ) {
		$deleted_groups = '';
		
		foreach ( (array) $_REQUEST['items'] as $id ) {
			$deleted_groups .= ' "' . stripslashes( $this->_options['queries'][$id]['title'] ) . '", ';
			unset( $this->_options['queries'][$id] );
		}
		
		$this->_parent->save();
		$this->alert( sprintf( _n( 'Deleted query %s.', 'Deleted queries %s', count( $_REQUEST['items'] ), 'it-l10n-loopbuddy' ), trim( $deleted_groups, ', ' ) ) );
	}
	else {
		$this->alert( __( 'No queries were selected for deletion.', 'it-l10n-loopbuddy' ), true );
	}
}
else if ( 'import' == $action ) {
	if ( isset( $_FILES[ 'import_data' ] ) ) {
		$filename = $_FILES[ 'import_data' ][ 'tmp_name' ];
		
		if ( empty( $filename ) ) {
			$this->alert( __( 'You must supply a file to import.', 'it-l10n-loopbuddy' ), true );
		}
		else {
			$result = $this->_parent->import( $filename, 'queries' );
			
			if ( is_wp_error( $result ) )
				$this->alert( $result->get_error_message(), true );
			else
				$this->alert( sprintf( _n( '$1$s query has been imported', '%1$s queries have been imported', $result, 'it-l10n-loopbuddy' ), number_format( $result ) ) );
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
			$duplicate_items[ $id ] = $this->_options[ 'queries' ][ $id ];
		}
		
		$result = $this->_parent->import( $duplicate_items, 'queries' );
		
		$this->alert( sprintf( _n( 'Query duplicated.', '%1$s queries duplicated.', $result, 'it-l10n-loopbuddy' ), number_format( $result ) ) );
	}
	else {
		$this->alert( __( 'No queries were selected for duplication.', 'it-l10n-loopbuddy' ), true );
	}
}

if ( 'edit' == $action ) {
	require( 'view_queries-edit.php' );
}
else {
	?>
	<h2><img src="<?php echo $this->_pluginURL; ?>/images/loopbuddy_rings.png" style="vertical-align: -4px;"> <?php _e( 'Queries', 'it-l10n-loopbuddy' ); ?></h2>
	
	<form id="posts-filter" enctype="multipart/form-data" method="post" action="<?php echo $this->_selfLink; ?>-queries">
		<div class="tablenav">
			<div class="alignleft actions">
				<select name="action">
					<option selected="selected" value=""><?php _e( 'Bulk Actions' ); ?></option>
					<option value="delete"><?php _e( 'Delete', 'it-l10n-loopbuddy' ); ?></option>
					<option value="duplicate"><?php _e( 'Duplicate', 'it-l10n-loopbuddy' ); ?></option>
					<option value="export_queries"><?php _e( 'Export', 'it-l10n-loopbuddy' ); ?></option>
				</select>
				<input type="submit" id="doaction" class="button action" value="<?php _e( 'Apply' ); ?>" />
			</div>
		</div>
		<table class="widefat">
			<thead>
				<tr class="thead">
					<th scope="col" class="manage-column column-cb check-column"><input type="checkbox" class="check-all-entries" /></th>
					<th><?php _e( 'Query Name', 'it-l10n-loopbuddy' ); ?></th>
				</tr>
			</thead>
			<tfoot>
				<tr class="thead">
					<th scope="col" class="manage-column column-cb check-column"><input type="checkbox" class="check-all-entries" /></th>
					<th><?php _e( 'Query Name', 'it-l10n-loopbuddy' ); ?></th>
				</tr>
			</tfoot>
			<tbody>
				<?php
				if ( empty( $this->_options['queries'] ) ) {
					?>
					<tr><td colspan="4" style="text-align: center;"><i><?php printf( __( 'Please add a new %s group below to get started.', 'it-l10n-loopbuddy' ), $this->_name ); ?></i></td></tr>
					<?php
				} else {
					uasort( $this->_options['queries'], 'pb_lb_sort_by_title' );
					
					foreach ( (array) $this->_options['queries'] as $id => $group ) {
						?>
						<tr class="entry-row alternate" id="entry-<?php echo $id; ?>">
							<th scope="row" class="check-column"><input type="checkbox" name="items[]" class="entries" value="<?php echo $id; ?>" /></th>
							<td>
								<strong>
									<a class="row-title" title="Edit &quot;<?php echo $group['title']; ?>&quot;" href="<?php echo $this->_selfLink; ?>-queries&action=edit&id=<?php echo $id; ?>">
										<?php echo stripslashes( $group['title'] ); ?>
									</a>
								</strong>
								
								<div class="row-actions">
									<span class="edit">
										<a href="<?php echo $this->_selfLink; ?>-queries&action=edit&id=<?php echo $id; ?>"><?php _e( 'Edit', 'it-l10n-loopbuddy' ); ?></a> |
									</span>
									<span class="duplicate">
										<a href="<?php echo $this->_selfLink; ?>-queries&action=duplicate&items[]=<?php echo $id; ?>"><?php _e( 'Duplicate', 'it-l10n-loopbuddy' ); ?></a> |
									</span>
									<span class="trash">
										<a class="submitdelete" href="<?php echo $this->_selfLink; ?>-queries&action=delete&items[]=<?php echo $id; ?>"><?php _e( 'Delete', 'it-l10n-loopbuddy' ); ?></a>
									</span>
									<!-- <a href="<?php echo $this->_selfLink; ?>-queries&edit=<?php echo $id; ?>"><?php _e( 'Preview', 'it-l10n-loopbuddy' ); ?></a>-->
								</div>
							</td>
						</tr>
						<?php
					}
				}
				?>
			</tbody>
		</table>
		<div class="tablenav">
			<div class="alignleft actions">
				<select name="action2">
					<option selected="selected" value=""><?php _e( 'Bulk Actions' ); ?></option>
					<option value="delete"><?php _e( 'Delete', 'it-l10n-loopbuddy' ); ?></option>
					<option value="duplicate"><?php _e( 'Duplicate', 'it-l10n-loopbuddy' ); ?></option>
					<option value="export_queries"><?php _e( 'Export', 'it-l10n-loopbuddy' ); ?></option>
				</select>
				<input type="submit" id="doaction" class="button action" value="<?php _e( 'Apply' ); ?>" />
			</div>
		</div>
		
		<?php $this->nonce(); ?>
	</form><br />
	
	<h3><?php printf( __( 'Add New %s Query', 'it-l10n-loopbuddy' ), esc_html( $this->_name ) ); ?></h3>
	<form method="post" action="<?php echo esc_attr( $this->_selfLink ); ?>-queries">
		<table class="form-table">
			<tr>
				<td><label for="group_name"><?php _e( 'Query Name', 'it-l10n-loopbuddy' ); ?><?php $this->tip( __( 'Name of the new group to create. This is for your convenience and is not publicly displayed.', 'it-l10n-loopbuddy' ) ); ?></label></td>
				<td><input type="text" name="group_name" id="group_name" size="45" maxlength="45" /></td>
			</tr>
		</table>
		<p class="submit"><input type="submit" name="create_group" value="<?php _e( '+ Add Query', 'it-l10n-loopbuddy' ); ?>" class="button-primary" /></p>
		<?php $this->nonce(); ?>
	</form>
	<h3><?php printf( __( 'Import Queries', 'it-l10n-loopbuddy' ), esc_html( $this->_name ) ); ?></h3>
	<form method="post" action="<?php echo esc_attr( $this->_selfLink ); ?>-queries" enctype="multipart/form-data">
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
