API Manager Plugin Example
==========================

The WooCommerce API Manager has a Software Update API and a License Key API. Each API returns messages that can be displayed by the client. The example plugin is designed to be used within WordPress to display any messages sent by the APIs, but can also be used a template to create libraries in other programming languages, or to roll-your-own libraries, and integrations. There are several  options to integrate the plugin example code into your plugin.

For testing, the plugin example will work on its own if activated in WordPress by changing two lines in the api-manager-example.php file:

* `public $upgrade_url = 'http://localhost/toddlahman/';`, change to the URL of your store
* `$this->ame_software_product_id = 'API Manager Example';`, change to your product's Software Title as it exists on the product edit screen in the API Tab.

Pull Requests and Issues
------------------------

To help improve the plugin example it is best to fork this repository, and submit a pull-request to request changes. Any questions can asked by opening an issue at any time.

Option 1 - Using the classes and libraries as-is
------------------------------------------------

Option 1 provides a form to enter the activation data required to activate the software, it provides the data to get software updates, and it will display all messages the APIs return. Everything that might be needed is included in the plugin example.

The example plugin can be dropped into a plugin product using the following instructions.

* Copy and paste the `am` directory into the root directory of your plugin. For example /wp-content/my-plugin/am
* Copy and paste all the code from `if ( ! defined( 'ABSPATH' ) ) exit;` to the bottom of the api-manager-example.php file into your main plugin file. For example, /wp-content/my-plugin/my-plugin.php is the file the code needs to be copied into `if ( ! defined( 'ABSPATH' ) ) exit;` is a line of code the provides security for the file. If you already have `if ( ! defined( 'ABSPATH' ) ) exit;` in your  main plugin file, you can omit this line. Don't copy the plugin header file, or it will conflict with your plugin's header file, or things won't work.
* Change the class names so they are unique to your plugin. Below is a list of classnames to change.
	* API_Manager_Example found in api-manager-example.php
	* API_Manager_Example_MENU found in /am/admin/class-wc-api-manager-menu.php
	* API_Manager_Example_Password_Management found in /am/classes/class-wc-api-manager-passwords.php
	* Api_Manager_Example_Key found in /am/classes/class-wc-key-api.php
	* API_Manager_Example_Update_API_Check found in /am/classes/class-wc-plugin-update.php
* Change the values in the api-manager-example.php file so they are unique to your plugin. Many of these values will be saved in the options table of the database. For many of the values using find or replace to change `api_manager_example` to your plugin's name will speed up this process. Once the values are changed, the api-manager-example.php does not need to be moved to your plugin directory, only the class in api-manager-example.php needs to be copied to your plugin's main file. Since the code from api-manager-example.php is contained in a class, there will be no conflict with your plugin. Change the following values.
 	* `api_manager_example_activated` - string
 	* `API_Manager_Example` - class name must match the main example plugin class name.
 	*  `$upgrade_url` - class variable, change the value.
 	* `$version`  - class variable, change the value each time your plugin version changes.
 	* `$api_manager_example_version_name` - class variable, change the value.
 	* `$text_domain` - class variable, change the value. It is better to hardcode the text domain into your plugin.
 	* `$ame_update_check` - class variable, change the value.
 	* `$this->text_domain` - can be changed to a 'string' that matches the plugin's text domain.
 	* `$this->ame_software_product_id` - class variable, change the value.
 	* `$this->ame_data_key` - class variable, change the value.
 	* `$this->ame_api_key` - class variable, does not need to be changed.
 	* `$this->ame_activation_email` - class variable, does not need to be changed.
 	* `$this->ame_product_id_key` - class variable, change the value.
 	* `$this->ame_instance_key` - class variable, change the value.
 	* `$this->ame_deactivate_checkbox_key` - class variable, change the value.
 	* `$this->ame_activated_key` - class variable, change the value.
 	* `$this->ame_deactivate_checkbox` - class variable, change the value.
 	* `$this->ame_activation_tab_key` - class variable, change the value.
 	* `$this->ame_deactivation_tab_key` - class variable, change the value.
 	* `$this->ame_settings_menu_title` - class variable, change the value.
 	* `$this->ame_settings_title` - class variable, change the value.
 	* `$this->ame_menu_tab_activation_title` - class variable, change the value.
 	* `$this->ame_menu_tab_deactivation_title` - class variable, change the value.
 	* `$this->ame_renew_license_url` - class variable, change the value.
 	* `Api_Manager_Example_Key` - class name, must match the class name in `am/classes/class-wc-key-api.php`
 	* `API_Manager_Example_Update_API_Check` - class name, must match the class name in `am/classes/class-wc-plugin-update.php`
 	* `API_Manager_Example_Password_Management` - class name, must match the class name in `am/classes/class-wc-api-manager-passwords.php`
 	* `am_example_inactive_notice()` - class method,  must match the method name in `add_action( 'admin_notices', 'API_Manager_Example::am_example_inactive_notice' );` located just above the `API_Manager_Example` class name
 	* `api_manager_example_dashboard` - string, found inside the `am_example_inactive_notice()` static method. Must match the `$this->ame_activation_tab_key` class variable.
 	* In the line `<p><?php printf( __( 'The API Manager Example API License Key has not been activated, so the plugin is inactive! %sClick here%s to activate the license key and the plugin.', 'api-manager-example' ), '<a href="' . esc_url( admin_url( 'options-general.php?page=api_manager_example_dashboard' ) ) . '">', '</a>' ); ?></p>`, change `The API Manager Example` to match your plugin's name, and `api_manager_example_dashboard` to match the `$this->ame_activation_tab_key` class variable.
 	* `API_Manager_Example` - class name found inside the `AME()` function, must match the class name in the api-manager-example.php file.
 	* `AME()` - function. Do a find and replace throughout all the plugin example files. The functions makes the singular design pattern possible in the plugin example.
* It is assumed that your plugin is using a languages directory in the root of the plugin directory. There is no need to copy the lanugages directory, or any of its contents. The following line serves as a functional example to load translations. `load_plugin_textdomain( $this->text_domain, false, dirname( untrailingslashit( plugin_basename( __FILE__ ) ) ) . '/languages' );`

The example code contains a method to cleanup activation data when the plugin is deactivated. When the plugin is deactivated, then reactivated, a new instance ID is generated that is unique to each installation, and helps the API Manager identify different installations of software.

To prevent the use of the software before an API Key is activated, test to see if the API Key is active. The value found in this line, `$this->ame_activated_key 			= 'api_manager_example_activated';`, in this case `api_manager_example_activated`, can be used to query the options table using `get_option()`, to see if the value is `Activated'. An example of this can be found in the api-manager-example.php file, just above the start of the `API_Manager_Example` class, that reads `if ( get_option( 'api_manager_example_activated' ) != 'Activated' ) {`. Simly change `!=` to `==`. Use this anywhere to disable the software until it has been successfully activated.

Option 2 - Not adding the API_Manager_Example in the root/main plugin file
--------------------------------------------------------------------------

Option 2 provides all the benefits of Option 1, except it doesn't require the API_Manager_Example class to be copied and pasted into your plugin's root/main plugin file. Instead we'll create functions and constants to provide values to API_Manager_Example. This will require far less code in the plugin's root/main plugin file, making it look cleaner.




