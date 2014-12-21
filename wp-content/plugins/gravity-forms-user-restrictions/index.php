<?php
/*
Plugin Name: Gravity Forms User Restrictions
Description: Restrict the number of user submissions on Gravity Forms
Author: Cyril Batillat
Version: 1.0.3
Author URI: http://bazalt.fr/
License: GPL2
Text Domain: gravity-forms-user-restrictions
Domain Path: /languages
*/

/*  Copyright 2013 Cyril Batillat (email : contact@bazalt.fr)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( ! class_exists('Gravityforms_user_restrictions' ) ) :

    class Gravityforms_user_restrictions {

        private static $_instance;
        private $_limit_user_entries_by = array();
        private $_limit_user_entries_durations = array();

        /**
         * Singlton pattern
         * @return Gravityforms_user_restrictions
         */
        public static function getInstance () {
            if ( self::$_instance instanceof self) return self::$_instance;
            self::$_instance = new self();
            return self::$_instance;
        }

        /**
         * Avoid creation of an instance from outside
         */
        private function __clone () {}

        /**
         * Private constructor (part of singleton pattern)
         * Declare WordPress Hooks
         */
        private function __construct() {
            // Load a custom text domain
            add_action( 'plugins_loaded', array($this, 'plugins_loaded') );

            // JS
            add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );

            // Add new fields on settings UI
            add_filter( 'gform_form_settings', array( $this, 'form_settings' ), 10, 2 );

            // Save news fields
            add_filter('gform_pre_form_settings_save', array( $this, 'form_settings_save' ));

            // GF Hook to modify form HTML
            add_filter('gform_get_form_filter', array( $this, 'form_display'), 10, 2 );
            add_filter('gform_pre_validation', array( $this, 'form_pre_validate') );

            // Custom Hooks
            add_filter('gf_user_restrictions_limitby', array( $this, 'limitby_to_sql'), 10, 3 );
            add_filter('gf_user_restrictions_duration', array( $this, 'limitduration_to_sql'), 10, 3 );
        }

        /**
         * Action hook plugins_loaded
         * Load plugin text domain
         */
        public function plugins_loaded() {
            load_plugin_textdomain( 'gravity-forms-user-restrictions', false, basename( dirname(__FILE__) ) . '/languages/' );

            // Define some ways to limit user entries
            $this->_limit_user_entries_by = array(
                'ip' => __('IP', 'gravity-forms-user-restrictions'),
                'user_id' => __('User ID', 'gravity-forms-user-restrictions'),
                'embed_url' => __('Embed URL', 'gravity-forms-user-restrictions')
            );
            $this->_limit_user_entries_by = apply_filters('gravityforms_limit_user_entries_by', $this->_limit_user_entries_by);

            // Define some durations
            $this->_limit_user_entries_durations = array(
                '' =>   __('total entries', 'gravity-forms-user-restrictions'),
                'day' =>   __('per day', 'gravity-forms-user-restrictions'),
                'week' =>   __('per week', 'gravity-forms-user-restrictions'),
                'month' =>   __('per month', 'gravity-forms-user-restrictions'),
                'year' =>   __('per year', 'gravity-forms-user-restrictions'),
            );
            $this->_limit_user_entries_durations = apply_filters('gravityforms_limit_user_durations', $this->_limit_user_entries_durations);
        }

        /**
         * Add scripts
         * @param $hook_suffix
         */
        public function scripts( $hook_suffix ) {
            if($hook_suffix !== 'toplevel_page_gf_edit_forms') return;
            wp_enqueue_script(
                'gravity-forms-user-restrictions',
                plugin_dir_url(__FILE__) . 'js/gravity-forms-user-restrictions.js',
                array('jquery'), '1.0'
            );
        }

        /**
         * Gravity Forms hook to modify form HTML before displaying it
         * @param $form_string
         * @param $form
         * @return string
         */
        public function form_display( $form_string, $form ) {
            if( !$this->is_limit_reached( $form['id'] ) ) return $form_string;

            $libxml_use_internal_errors = libxml_use_internal_errors();
            libxml_use_internal_errors(true);
            $dom = new DOMDocument();
            $dom->loadHTML($form_string);

            $errors = libxml_get_errors();
            foreach ($errors as $error) {
                error_log( $this->__libxml_error_tostring($error, $form_string) );
                if($error->level > LIBXML_ERR_WARNING ) {
                    libxml_clear_errors();
                    libxml_use_internal_errors($libxml_use_internal_errors);
                    return $form_string;
                }
            }

            // Search and remove content of form wrapper (div.form_wrapper)
            $xpath = new DOMXPath($dom);
            $entries = $xpath->query('//div[contains(@class,"gform_wrapper")]');
            foreach ($entries as $entry) {
                while ($entry->childNodes->length > 0) {
                    $entry->removeChild($entry->childNodes->item(0));
                }

                // Append the error message in the form wrapper
                $f = $dom->createDocumentFragment();
                $f->appendXML('<div class="validation_error user_restriction">' . $form['limitUserEntriesReachedMessage'] . '</div>');
                $entry->appendChild($f);
            }

            $form_string = $dom->saveHTML();

            libxml_clear_errors();
            libxml_use_internal_errors($libxml_use_internal_errors);
            return $form_string;
        }

        /**
         * Form validation : check user restrictions
         * @param $validation_result
         * @return mixed
         */
        public function form_pre_validate( $validation_result ) {
            $form = RGFormsModel::get_form_meta($validation_result['id']);
            if ($this->is_limit_reached($validation_result['id'])) {
                $validation_result['is_valid'] = false;
                // Seems ike $validation_result['is_valid'] is not enough
                foreach($validation_result['fields'] as &$field){
                    $field["failed_validation"] = true;
                    $field["validation_message"] = $form['limitUserEntriesReachedMessage'];
                }
            }
            return $validation_result;
        }

        /**
         * Add some fields in the form settings UI
         * @param $form_settings
         * @param $form
         * @return mixed
         */
        public function form_settings($form_settings, $form) {
            ob_start();
            ?>
            <tr>
                <th>
                    <?php _e('Limit number of entries per user', 'gravity-forms-user-restrictions'); ?>
                </th>
                <td>
                    <input type="checkbox"
                           id="gform_limit_user_entries"
                           name="form_limit_user_entries"
                           value="1"
                        <?php if(!empty($form['limitUserEntries'])) echo 'checked="checked"'; ?>
                        />
                    <label for="gform_limit_user_entries"><?php _e('Enable user entry limit', 'gravity-forms-user-restrictions'); ?></label>
                </td>
            </tr>
            <?php
            $html = ob_get_contents();
            ob_end_clean();
            $form_settings['Restrictions']['user_restrictions'] = $html;

            ob_start();
            ?>
            <tr id="limit_user_entries_settings" class="child_setting_row">
                <td colspan="2" class="gf_sub_settings_cell">
                    <div class="gf_animate_sub_settings">
                        <table>
                            <tr>
                                <th>
                                    <label for="form_limit_user_entries_by"><?php _e('Limit by', 'gravity-forms-user-restrictions'); ?></label>
                                </th>
                                <td>
                                    <select name="form_limit_user_entries_by[]" id="form_limit_user_entries_by" multiple="multiple">
                                        <?php
                                        foreach((array) $this->_limit_user_entries_by as $value => $label) {
                                            $selected = (!empty($form['limitUserEntriesBy']) && in_array($value, (array) $form['limitUserEntriesBy'])) ? 'selected="selected"' : '';
                                            ?>
                                            <option value="<?php echo esc_attr($value); ?>" <?php echo $selected; ?>>
                                                <?php echo $label;?>
                                            </option>
                                        <?php
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="form_limit_user_entries_number"><?php _e('Number of Entries', 'gravity-forms-user-restrictions'); ?></label>
                                </th>
                                <td>
                                    <input type="text" name="form_limit_user_entries_number"
                                           id="form_limit_user_entries_number"
                                           value="<?php if(!empty($form['limitUserEntriesNumber'])) echo esc_attr($form['limitUserEntriesNumber']); ?>" />
                                    <select name="form_limit_user_entries_duration">
                                        <?php
                                        foreach((array) $this->_limit_user_entries_durations as $value => $label) {
                                            $selected = (!empty($form['limitUserEntriesDuration']) && in_array($value, (array) $form['limitUserEntriesDuration'])) ? 'selected="selected"' : '';
                                            ?>
                                            <option value="<?php echo esc_attr($value); ?>" <?php echo $selected; ?>>
                                                <?php echo $label; ?>
                                            </option>
                                        <?php
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="form_user_entries_reached_message"><?php _e('Number of entries reached Message', 'gravity-forms-user-restrictions'); ?></label>
                                </th>
                                <td>
                                    <textarea name="form_user_entries_reached_message"
                                              class="fieldwidth-3"
                                              id="form_user_entries_reached_message"><?php if(!empty($form['limitUserEntriesReachedMessage'])) echo esc_attr($form['limitUserEntriesReachedMessage']); ?></textarea>
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
            <?php
            $html = ob_get_contents();
            ob_end_clean();
            $form_settings['Restrictions']['user_restrictions_by'] = $html;
            $this->is_limit_reached($form['id']);
            return $form_settings;
        }

        /**
         * Save custom fields
         * @param $form
         * @return mixed
         */
        public function form_settings_save( $form ) {
            $form['limitUserEntries'] = rgpost('form_limit_user_entries');
            $form['limitUserEntriesBy'] = (array) $_POST['form_limit_user_entries_by'];
            $form['limitUserEntriesNumber'] = intval(rgpost('form_limit_user_entries_number'));
            $form['limitUserEntriesDuration'] = rgpost('form_limit_user_entries_duration');
            $form['limitUserEntriesReachedMessage'] = rgpost('form_user_entries_reached_message');
            return $form;
        }

        /**
         * Is submission limit reached ?
         * @param $form_id
         * @return bool
         */
        public function is_limit_reached($form_id) {
            /** @var WPdb $wp_db */
            global $wpdb;

            $form = RGFormsModel::get_form_meta($form_id);

            if( empty($form['limitUserEntries']) ) return false;
            if( empty($form['limitUserEntriesBy']) ) return false;
            if( empty($form['limitUserEntriesNumber']) ) return false;

            $sql_params = array(
                'select' => array('count(rg_lead.id) AS total_rows'),
                'from' => array(
                    'rg_lead' => RGFormsModel::get_lead_table_name()
                ),
                'where' => array(
                    $wpdb->prepare('rg_lead.form_id = %d', $form_id)
                )
            );

            foreach ((array) $form['limitUserEntriesBy'] as $limiter) {
                $sql_params = apply_filters('gf_user_restrictions_limitby', $sql_params, $limiter, $form);
            }

            if( !empty($form['limitUserEntriesDuration']) ) {
                $sql_params = apply_filters('gf_user_restrictions_duration', $sql_params, $form['limitUserEntriesDuration'], $form);
            }
            $sql = $this->__build_SQL_Select($sql_params);
            $entry_count = $wpdb->get_var($sql);

            $entry_count = apply_filters('gf_user_restrictions_count', $entry_count, $form);

            if($entry_count < intval($form['limitUserEntriesNumber'])) return false;
            return true;
        }

        /**
         * Build a simple SQL Select query based on an array of params
         * WordPress is missing a real query builder... :(
         * @param array $sql_params
         * @return bool|string
         */
        private function __build_SQL_Select($sql_params = array()) {
            if(empty($sql_params['select'])) return false;
            $sql = 'SELECT ';
            $sql.= implode(', ', $sql_params['select']);
            if(!empty( $sql_params['from'] )) {
                $sql.= ' FROM ';
                foreach((array) $sql_params['from'] as $alias => $table) {
                    $sql.= $table;
                    if( is_string( $alias ) ) $sql.= ' AS ' . $alias;
                }
            }
            if(!empty( $sql_params['where'] )) {
                $sql.= ' WHERE ';
                $sql.= implode(' AND ', $sql_params['where']);
            }
            return $sql;
        }

        /**
         * Hook gf_user_restrictions_limitby
         * @param $sql_params
         * @param $limiter
         * @param $form
         * @return mixed
         */
        public function limitby_to_sql($sql_params, $limiter, $form) {
            global $wpdb;
            switch ($limiter) {
                case 'user_id':
                    $sql_params['where'][] = $wpdb->prepare('rg_lead.created_by = %s', get_current_user_id());
                    break;
                case 'embed_url':
                    $sql_params['where'][] = $wpdb->prepare('rg_lead.source_url = %s', RGFormsModel::get_current_page_url());
                    break;
                default:
                    $sql_params['where'][] = $wpdb->prepare('rg_lead.ip = %s', RGFormsModel::get_ip());
            }
            return $sql_params;
        }

        /**
         * Hook gf_user_restrictions_duration
         * @param $sql_params
         * @param $duration
         * @param $form
         * @return mixed
         */
        public function limitduration_to_sql($sql_params, $duration, $form) {
            switch ($duration) {
                case 'day':
                    $sql_params['where'][] = 'rg_lead.date_created >= DATE_SUB( NOW(), INTERVAL 1 DAY)';
                    break;
                case 'week':
                    $sql_params['where'][] = 'rg_lead.date_created >= DATE_SUB( NOW(), INTERVAL 1 WEEK)';
                    break;
                case 'month':
                    $sql_params['where'][] = 'rg_lead.date_created >= DATE_SUB( NOW(), INTERVAL 1 MONTH)';
                    break;
                case 'year':
                    $sql_params['where'][] = 'rg_lead.date_created >= DATE_SUB( NOW(), INTERVAL 1 YEAR)';
                    break;
            }
            return $sql_params;
        }

        /**
         * Libxml error handler
         * @see http://www.php.net/manual/en/function.libxml-get-errors.php
         * @param $error
         * @param $xml
         * @return string
         */
        private function __libxml_error_tostring($error, $xml) {
            $return  = $xml[$error->line - 1] . "\n";
            $return .= str_repeat('-', $error->column) . "^\n";

            switch ($error->level) {
                case LIBXML_ERR_WARNING:
                    $return .= "Warning $error->code: ";
                    break;
                case LIBXML_ERR_ERROR:
                    $return .= "Error $error->code: ";
                    break;
                case LIBXML_ERR_FATAL:
                    $return .= "Fatal Error $error->code: ";
                    break;
            }

            $return .= trim($error->message) .
                "\n  Line: $error->line" .
                "\n  Column: $error->column";

            if ($error->file) {
                $return .= "\n  File: $error->file";
            }

            return "$return\n\n--------------------------------------------\n\n";
        }
    }
    Gravityforms_user_restrictions::getInstance();
endif;