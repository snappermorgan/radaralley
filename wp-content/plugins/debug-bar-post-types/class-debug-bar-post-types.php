<?php
/**
 * Debug Bar Post Types, a WordPress plugin
 *
 * @package     WordPress\Plugins\Debug Bar Post Types
 * @author      Juliette Reinders Folmer <wpplugins_nospam@adviesenzo.nl>
 * @link        https://github.com/jrfnl/Debug-Bar-Post-Types
 * @since       1.0
 * @version     1.1.1
 *
 * @copyright   2013-2014 Juliette Reinders Folmer
 * @license     http://creativecommons.org/licenses/GPL/2.0/ GNU General Public License, version 2 or higher
 */

// Avoid direct calls to this file
if ( ! function_exists( 'add_action' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}


/**
 * The class in this file extends the functionality provided by the parent plugin "Debug Bar".
 */
if ( ! class_exists( 'Debug_Bar_Post_Types' ) && class_exists( 'Debug_Bar_Panel' ) ) {
	class Debug_Bar_Post_Types extends Debug_Bar_Panel {

		const DBPT_STYLES_VERSION = '1.1';

		const DBPT_NAME = 'debug-bar-post-types';

		public function init() {
			load_plugin_textdomain( self::DBPT_NAME, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

			$this->title( __( 'Post Types', self::DBPT_NAME ) );

			//debug_bar_enqueue_scripts
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		}

		public function enqueue_scripts() {
			$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min' );
			wp_enqueue_style( self::DBPT_NAME, plugins_url( 'css/debug-bar-post-types' . $suffix . '.css', __FILE__ ), array( 'debug-bar' ), self::DBPT_STYLES_VERSION );
		}

		public function prerender() {
			$this->set_visible( true );
		}

		public function render() {

			$wp_post_types = $GLOBALS['wp_post_types'];
			$names         = array_keys( $wp_post_types );

			$properties = array();
			$caps       = array();
			$count      = count( $wp_post_types );
			$double     = ( $count > 4 ? true : false ); // whether to repeat the row labels on the other side of the table
			
			if ( ! class_exists( 'Debug_Bar_Pretty_Output' ) ) {
				require_once plugin_dir_path( __FILE__ ) . 'inc/debug-bar-pretty-output/class-debug-bar-pretty-output.php';
			}


			echo '
		<h2><span>' . esc_html__( 'Total Post Types:', self::DBPT_NAME ) . '</span>' . esc_html( $count ) . '</h2>';

			if ( is_array( $wp_post_types ) && $count > 0 ) {
				/* Put the relevant info in arrays */
				foreach ( $wp_post_types as $name => $post_type_obj ) {
					$props = get_object_vars( $post_type_obj );

					if ( is_array( $props ) && count( $props ) > 0 ) {
						foreach ( $props as $key => $value ) {
							if ( ! is_object( $value ) ) {
								$properties[$key][$name] = $value;
							}
							else if ( $key === 'cap' ) {
								$cap_obj = get_object_vars( $value );

								if ( is_array( $cap_obj ) && count( $cap_obj ) > 0 ) {
									foreach ( $cap_obj as $v ) {
										$caps[$v][$name] = $v;
									}
									unset( $v );
								}
								unset( $cap_obj );
							}
						}
					}
					unset( $props );
				}

				
				/* Create the properties table */
				if ( count( $properties ) > 0 ) {
					/* Create header row */
					$header_row = '
			<tr>
				<th>' . esc_html__( 'Property', self::DBPT_NAME ) . '</th>';
					foreach ( $names as $name ) {
						$header_row .= '
				<th>' . esc_html( $name ) . '</th>';
					}
					unset( $name );
					$header_row .= ( $double === true ? '
				<th class="' . self::DBPT_NAME . '-table-end">' . esc_html__( 'Property', self::DBPT_NAME ) . '</th>' : '' );
					$header_row .= '
			</tr>';


					echo '
		<h3>' . esc_html__( 'Post Type Properties:', self::DBPT_NAME ) . '</h3>
		<table class="debug-bar-table ' . self::DBPT_NAME . '">
			<thead>
			' . $header_row . '
			</thead>
			<tfoot>
			' . $header_row . '
			</tfoot>
			<tbody>';
					unset( $header_row );


					/* Sort */
					uksort( $properties, 'strnatcasecmp' );


					/* Output */
					foreach ( $properties as $key => $value ) {
						echo '
			<tr>
				<th>' . esc_html( $key ) . '</th>';

						foreach ( $names as $name ) {
							echo '
				<td>';

							if ( isset( $value[$name] ) ) {
								if ( defined( 'Debug_Bar_Pretty_Output::VERSION' ) ) {
									echo Debug_Bar_Pretty_Output::get_output( $value[$name], '', true, '', true );
								}
								else {
									// An old version of the pretty output class was loaded
									Debug_Bar_Pretty_Output::output( $value[$name], '', true, '', true );
								}
							}
							else {
								echo '&nbsp;';
							}

							echo '
				</td>';
						}
						unset( $name );

						echo ( $double === true ? '
				<th class="' . self::DBPT_NAME . '-table-end">' . esc_html( $key ) . '</th>' : '' ) . '
			</tr>';
					}
					unset( $key, $value );

					echo '
			</tbody>
		</table>
';
				}


				/* Create the capabilities table */
				if ( count( $caps ) > 0 ) {
					/* Create header row */
					$header_row = '
			<tr>
				<th>' . esc_html__( 'Capability', self::DBPT_NAME ) . '</th>';
					foreach ( $names as $name ) {
						$header_row .= '
				<th>' . esc_html( $name ) . '</th>';
					}
					unset( $name );
					$header_row .= ( $double === true ? '
				<th>' . esc_html__( 'Capability', self::DBPT_NAME ) . '</th>' : '' ) . '
			</tr>';


					echo '
		<h3>' . esc_html__( 'Post Type Capabilities:', self::DBPT_NAME ) . '</h3>
		<table class="debug-bar-table ' . self::DBPT_NAME . ' ' . self::DBPT_NAME . '-caps">
			<thead>
			' . $header_row . '
			</thead>
			<tfoot>
			' . $header_row . '
			</tfoot>
			<tbody>';
					unset( $header_row );


					/* Sort */
					uksort( $caps, 'strnatcasecmp' );


					/* Output */
					foreach ( $caps as $key => $value ) {
						echo '
			<tr>
				<th>' . esc_html( $key ) . '</th>';

						foreach ( $names as $name ) {
							$img = ( isset( $value[$name] ) ? 'check' : 'cross' );
							$alt = ( isset( $value[$name] ) ? __( 'Has capability', self::DBPT_NAME ) : __( 'Does not have capability', self::DBPT_NAME ) );

							echo '
				<td><img src="' . esc_url( plugins_url( 'images/badge-circle-' . $img . '-16.png', __FILE__ ) ) . '" width="16" height="16" alt="' . esc_attr( $alt ) . '" /></td>';
							unset( $img, $alt );
						}
						unset( $name );

						echo ( $double === true ? '
				<th class="' . self::DBPT_NAME . '-table-end">' . esc_html( $key ) . '</th>' : '' ) .'
			</tr>';
					}
					unset( $key, $value );

					echo '
			</tbody>
		</table>
';
				}
			}
			else {
				echo '<p>' . esc_html__( 'No post types found.', self::DBPT_NAME ) . '</p>';
			}

			unset( $names, $properties, $caps );
		}
	} // End of class Debug_Bar_Post_Types
} // End of if class_exists wrapper