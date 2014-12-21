<?php
/**
 * WP Courseware
 * 
 * Functions relating to showing the certificate settings page.
 */




/**
 * Show the page where the user can set up the certificate settings. 
 */
function WPCW_showPage_Certificates_load()
{
	$page = new PageBuilder(true);
	$page->showPageHeader(__('Training Courses - Certificate Settings', 'wp_courseware'), '75%', WPCW_icon_getPageIconURL());
	
	 
	$settingsFields = array(
		'section_certificates_defaults' => array(
				'type'	  	=> 'break',
				'html'	   	=> WPCW_forms_createBreakHTML(__('Certificate Settings', 'wp_courseware')),
			),			
			
		'cert_signature_type' => array(
				'label' 	=> __('Signature Type', 'wp_courseware'),
				'type'  	=> 'radio',
				'cssclass'	=> 'wpcw_cert_signature_type',
				'required'	=> 'true',
				'data'		=> array(
					'text' 		=> sprintf('<b>%s</b> - %s', __('Text', 'wp_courseware'), __('Just use text for the signature.', 'wp_courseware')),
					'image' 	=> sprintf('<b>%s</b> - %s', __('Image File', 'wp_courseware'), __('Use an image for the signature.', 'wp_courseware')),
				),
			),	

		'cert_sig_text' => array(
				'label' 	=> __('Name to use for signature', 'wp_courseware'),
				'type'  	=> 'text',
				'cssclass'	=> 'wpcw_cert_signature_type_text',
				'desc'  	=> __('The name to use for the signature area.', 'wp_courseware'),
				'validate'	 	=> array(
					'type'		=> 'string',
					'maxlen'	=> 150,
					'minlen'	=> 1,
					'regexp'	=> '/^[^<>]+$/',
					'error'		=> __('Please enter the name to use for the signature area.', 'wp_courseware'),
				)	
			),
			
		'cert_sig_image_url' => array(
				'label' 	=> __('Your Signature Image', 'wp_courseware'),
				'cssclass'	=> 'wpcw_image_upload_field wpcw_cert_signature_type_image',
				'type'  	=> 'text',
				'desc'  	=> '&bull;&nbsp;' . __('The URL of your signature image. Using a transparent image is recommended.', 'wp_courseware') .  
							   	'<br/>&bull;&nbsp;' . sprintf(__('The image must be <b>%d pixels wide, and %d pixels high</b> to render correctly. ', 'wp_courseware'), WPCW_CERTIFICATE_SIGNATURE_WIDTH_PX*2, WPCW_CERTIFICATE_SIGNATURE_HEIGHT_PX*2),
				'validate'	 	=> array(
					'type'		=> 'url',
					'maxlen'	=> 300,
					'minlen'	=> 1,
					'error'		=> __('Please enter the URL of your signature image.', 'wp_courseware'),
				),
				'extrahtml'		=> sprintf('<a id="cert_sig_image_url_btn" href="#" class="wpcw_insert_image button-secondary" data-uploader_title="%s" data-uploader_btn_text="%s" data-target="cert_sig_image_url"><span class="wpcw_insert_image_img"></span> %s</a>',
										__('Choose an image to use for the signature image...', 'wp_courseware'),
										__('Select Image', 'wp_courseware'), 
										__('Select Image', 'wp_courseware')
									),
			),			
				
		'cert_logo_enabled' => array(
				'label' 	=> __('Show your logo?', 'wp_courseware'),
				'cssclass'	=> 'wpcw_cert_logo_enabled',
				'type'  	=> 'radio',
				'required'	=> 'true',
				'data'		=> array(
					'cert_logo' 	=> sprintf('<b>%s</b> - %s', __('Yes', 'wp_courseware'), __('Use your logo on the certificate.', 'wp_courseware')),
					'no_cert_logo' 	=> sprintf('<b>%s</b> - %s', __('No', 'wp_courseware'), __('Don\'t show a logo on the certificate.', 'wp_courseware')),
				),
			),	

		'cert_logo_url' => array(
				'label' 	=> __('Your Logo Image', 'wp_courseware'),
				'type'  	=> 'text',
				'cssclass'	=> 'wpcw_cert_logo_url wpcw_image_upload_field',
				'desc'  	=> '&bull;&nbsp;' . __('The URL of your logo image. Using a transparent image is recommended.', 'wp_courseware') .  
							   	'<br/>&bull;&nbsp;' . sprintf(__('The image must be <b>%d pixels wide, and %d pixels</b> high to render correctly. ', 'wp_courseware'), WPCW_CERTIFICATE_LOGO_WIDTH_PX*2, WPCW_CERTIFICATE_LOGO_HEIGHT_PX*2),
				'validate'	 	=> array(
					'type'		=> 'url',
					'maxlen'	=> 300,
					'minlen'	=> 1,
					'error'		=> __('Please enter the URL of your logo image.', 'wp_courseware'),
				),
				'extrahtml'		=> sprintf('<a id="cert_logo_url_btn" href="#" class="wpcw_insert_image button-secondary" data-uploader_title="%s" data-uploader_btn_text="%s" data-target="cert_logo_url"><span class="wpcw_insert_image_img"></span> %s</a>',
										__('Choose an image to use for your logo on the certificate...', 'wp_courseware'),
										__('Select Image', 'wp_courseware'), 
										__('Select Image', 'wp_courseware')
									),	
			),	

		'cert_background_type' => array(
				'label' 	=> __('Certificate Background', 'wp_courseware'),
				'cssclass'	=> 'wpcw_cert_background_type',
				'type'  	=> 'radio',
				'required'	=> 'true',
				'data'		=> array(
					'use_default' 	=> sprintf('<b>%s</b> - %s', __('Built-in', 'wp_courseware'), __('Use the built-in certificate background.', 'wp_courseware')),
					'use_custom' 	=> sprintf('<b>%s</b> - %s', __('Custom', 'wp_courseware'), __('Use your own certificate background.', 'wp_courseware')),
				),
			),	

		'cert_background_custom_url' => array(
				'label' 	=> __('Custom Background Image', 'wp_courseware'),
				'type'  	=> 'text',
				'cssclass'	=> 'wpcw_cert_background_custom_url wpcw_image_upload_field',
				'desc'  	=> '&bull;&nbsp;' . __('The URL of your background image.', 'wp_courseware') .  
							   	'<br/>&bull;&nbsp;' . sprintf(__('The background image must be <b>%d pixels wide, and %d pixels</b> high at <b>72 dpi</b> to render correctly. ', 'wp_courseware'), WPCW_CERTIFICATE_BG_WIDTH_PX, WPCW_CERTIFICATE_BG_HEIGHT_PX),
				'validate'	 	=> array(
					'type'		=> 'url',
					'maxlen'	=> 300,
					'minlen'	=> 1,
					'error'		=> __('Please enter the URL of your certificate background image.', 'wp_courseware'),
				),				
				'extrahtml'		=> sprintf('<a id="cert_background_custom_url_btn" href="#" class="wpcw_insert_image button-secondary" data-uploader_title="%s" data-uploader_btn_text="%s" data-target="cert_background_custom_url"><span class="wpcw_insert_image_img"></span> %s</a>',
										__('Choose an image to use for the certificate background...', 'wp_courseware'),
										__('Select Image', 'wp_courseware'), 
										__('Select Image', 'wp_courseware')
									),	
			),	
			
		// Section that allows you to choose which encoding to use for the certificate.
		'section_encodings' 	=> array(
				'type'	  	=> 'break',
				'html'	   	=> WPCW_forms_createBreakHTML(__('Language and Encoding Settings', 'wp_courseware')),
			),
			
 		'certificate_encoding' => array(
				'label' 	=> __('Certificate Encoding', 'wp_courseware'),
				'type'  	=> 'select',
				'required'	=> true,
				'desc'  	=> __('Choose a codepage encoding that matches your language to ensure certificates render correctly. You may need an encoding other than <code>ISO-8859-1</code> if you are using a non-English language.', 'wp_courseware'), 
				'data'		=> array(
					'ISO-8859-1' 	=> __('ISO-8859-1 - Latin alphabet - North America, Western Europe, Latin America, etc. (Default)', 'wp_courseware'),
					'ISO-8859-2' 	=> __('ISO-8859-2 - Latin alphabet 2 - Eastern Europe.', 'wp_courseware'),
					'ISO-8859-3' 	=> __('ISO-8859-3 - Latin alphabet 3 - SE Europe, Esperanto', 'wp_courseware'),
					'ISO-8859-4' 	=> __('ISO-8859-4 - Latin alphabet 4 - Scandinavia/Baltics', 'wp_courseware'),
					'ISO-8859-5' 	=> __('ISO-8859-5 - Latin/Cyrillic - Bulgarian, Belarusian, Russian and Macedonian', 'wp_courseware'),
					'ISO-8859-6' 	=> __('ISO-8859-6 - Latin/Arabic - Arabic languages', 'wp_courseware'),
					'ISO-8859-7' 	=> __('ISO-8859-7 - Latin/Greek - modern Greek language', 'wp_courseware'),
					'ISO-8859-8' 	=> __('ISO-8859-8 - Latin/Hebrew - Hebrew languages', 'wp_courseware'),
					'ISO-8859-9' 	=> __('ISO-8859-9 - Latin 5 part 9 - Turkish languages', 'wp_courseware'),
					'ISO-8859-10' 	=> __('ISO-8859-10 - Latin 6 Lappish, Nordic, Eskimo - The Nordic languages', 'wp_courseware'),
					'ISO-8859-15' 	=> __('ISO-8859-15 - Latin 9 (aka Latin 0) - Similar to ISO 8859-1', 'wp_courseware'),
			
				// The following do not work with iconv(), hence are commented out.
					//'ISO-8859-JP'	=> __('ISO-8859-JP - Latin/Japanese part 1 - The Japanese language', 'wp_courseware'),
					//'ISO-8859-JP-2'	=> __('ISO-8859-JP-2 - Latin/Japanese part 2 - The Japanese language', 'wp_courseware'),
					//'ISO-8859-KR'	=> __('ISO-8859-KR - Latin/Korean part 1 - The Korean language', 'wp_courseware')
				),
			),			
		);
		
	
	$settings = new SettingsForm($settingsFields, WPCW_DATABASE_SETTINGS_KEY, 'wpcw_form_settings_certificates');
	$settings->setSaveButtonLabel(__('Save ALL Settings', 'wp_courseware'));
	
	$settings->msg_settingsSaved   	= __('Settings successfully saved.', 'wp_courseware');
	$settings->msg_settingsProblem 	= __('There was a problem saving the settings.', 'wp_courseware');
	$settings->setAllTranslationStrings(WPCW_forms_getTranslationStrings());
			
	$settings->show();	

	
	
	// RHS Support Information
	$page->showPageMiddle('23%');	
		
	// Create a box where the admin can preview the certificates to see what they look like.
	$page->openPane('wpcw-certificates-preview', __('Preview Certificate', 'wp_courseware'));
	printf('<p>%s</p>', __('After saving the settings, you can preview the certificate using the button below. The preview opens in a new window.', 'wp_courseware'));
	printf('<div class="wpcw_btn_centre"><a href="%spdf_create_certificate.php?certificate=preview" target="_blank" class="button-primary">%s</a></div>', WPCW_plugin_getPluginPath(), __('Preview Certificate', 'wp_courseware'));	
	
	$page->closePane();
	
	
	
	WPCW_docs_showSupportInfo($page);
	WPCW_docs_showSupportInfo_News($page);	
	WPCW_docs_showSupportInfo_Affiliate($page);
	
	
	$page->showPageFooter();
}
?>