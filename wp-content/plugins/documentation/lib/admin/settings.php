<?php
/**
 * settings.php
 *
 * Copyright (c) 2013 "kento" Karim Rahimpur www.itthinx.com
 *
 * This code is released under the GNU General Public License.
 * See COPYRIGHT.txt and LICENSE.txt.
 *
 * This code is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This header and all notices must be kept intact.
 *
 * @author Karim Rahimpur
 * @package documentation 1.0.0
 * @since documentation 1.0.0
 */
?>

<?php
if ( !current_user_can( Documentation_Settings::$capability ) ) {
	wp_die( __( 'Access denied.', DOCUMENTATION_PLUGIN_DOMAIN ) );
}

if ( isset( $_POST['action'] ) && ( $_POST['action'] == 'set' ) && wp_verify_nonce( $_POST['documentation-settings'], 'admin' ) ) {

	$options = Documentation::get_options();
	$options['document_comments_open'] = !empty( $_POST['document_comments_open'] );

	$maybe_slug = !empty( $_POST['document_slug'] ) ? $_POST['document_slug'] : '';
	$maybe_slug = preg_replace( '/(\s|[^A-Za-z0-9-_])+/', '', wp_strip_all_tags( $maybe_slug ) );
	$options['document_slug'] = $maybe_slug;

	Documentation::set_options( $options );

	echo
		'<p class="info">' .
		__( 'The settings have been saved.', DOCUMENTATION_PLUGIN_DOMAIN ) .
		'</p>';
}

$options = Documentation::get_options();
$document_comments_open = isset( $options['document_comments_open'] ) ? $options['document_comments_open'] : true;
$document_slug = !empty( $options['document_slug'] ) ? $options['document_slug'] : '';
?>

<div class="settings">
<form name="settings" method="post" action="">
<div>
<?php
// echo '<div class="buttons">';
// printf( '<input class="import button" type="submit" name="submit" value="%s" />', esc_attr( echo __( 'Save', DOCUMENTATION_PLUGIN_DOMAIN ) ) ); 
// echo '</div>'; // .buttons
?>

<label>
<?php
	printf( '<input type="checkbox" name="document_comments_open" %s />', $document_comments_open ? ' checked="checked" ' : '' );
	echo ' ';
	echo __( 'Allow comments on documents', DOCUMENTATION_PLUGIN_DOMAIN );
?>
</label>
<p class="description">
<?php
	echo __( 'Disable this option if you do not want to allow visitors to post comments on documents.', DOCUMENTATION_PLUGIN_DOMAIN );
	echo ' ';
	echo __( 'If this option is enabled, you may choose to allow comments on each document individually.', DOCUMENTATION_PLUGIN_DOMAIN );
	echo ' ';
	echo __( 'If this option is disabled, comments on all documents are disabled.', DOCUMENTATION_PLUGIN_DOMAIN );
?>
</p>
<div class="separator"></div>
<label>
<?php
echo __( 'Document slug', DOCUMENTATION_PLUGIN_DOMAIN );
echo ' ';
printf( '<input type="text" name="document_slug" value="%s" />', esc_attr( $document_slug ) );
echo '<p class="description">';
echo __( 'Depending on your Permalink settings, URLs of documents will contain this in their path before the section that identifies the document.', DOCUMENTATION_PLUGIN_DOMAIN );
echo ' ';
echo __( 'If left empty, the default <em>document</em> applies.', DOCUMENTATION_PLUGIN_DOMAIN );
echo ' ';
echo sprintf( __( 'After changing this, please visit the <a href="%s">Permalinks</a> admin section to make sure that the permalink structure is updated.', DOCUMENTATION_PLUGIN_DOMAIN ), admin_url( 'options-permalink.php' ) );
echo '</p>';
?>
</label>
<div class="separator"></div>

<?php wp_nonce_field( 'admin', 'documentation-settings', true, true ); ?>

<div class="buttons">
<input class="import button" type="submit" name="submit" value="<?php echo __( 'Save', DOCUMENTATION_PLUGIN_DOMAIN ); ?>" />
<input type="hidden" name="action" value="set" />
</div>
</div>
</form>
</div>
