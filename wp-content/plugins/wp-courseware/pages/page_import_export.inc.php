<?php
/**
 * WP Courseware
 * 
 * Functions relating to showing the import and export of WP Courseware training courses.
 */


/**
 * Shows the page to do with importing/exporting training courses.
 */
function WPCW_showPage_ImportExport_load()
{
	switch (WPCW_arrays_getValue($_GET, 'show'))
	{
		case 'import':
			WPCW_showPage_ImportExport_import();
			break;
			
		case 'import_users':
			WPCW_showPage_ImportExport_importUsers();
			break;
			
		default:
			WPCW_showPage_ImportExport_export();
			break;
	}
}


/**
 * Shows the menu where the user can select the import or export page.
 * @param String $currentPage The currently selected page.
 */
function WPCW_showPage_ImportExport_menu($currentPage)
{
	printf('<div id="wpcw_menu_import_export">');
	
	switch ($currentPage)
	{
		case 'import':
			printf('<span><a href="%s">%s</a></span>', admin_url('admin.php?page=WPCW_showPage_ImportExport'), __('Export Course', 'wp_courseware'));
			printf('&nbsp;|&nbsp;');
			printf('<span><b>%s</b></span>', __('Import Course', 'wp_courseware'));
			printf('&nbsp;|&nbsp;');
			printf('<span><a href="%s&show=import_users">%s</a></span>', admin_url('admin.php?page=WPCW_showPage_ImportExport'), __('Import Users', 'wp_courseware'));
			break;
			
		case 'import_users':
			printf('<span><a href="%s">%s</a></span>', admin_url('admin.php?page=WPCW_showPage_ImportExport'), __('Export Course', 'wp_courseware'));
			printf('&nbsp;|&nbsp;');
			printf('<span><a href="%s&show=import">%s</a></span>', admin_url('admin.php?page=WPCW_showPage_ImportExport'), __('Import Course', 'wp_courseware'));
			printf('&nbsp;|&nbsp;');
			printf('<span><b>%s</b></span>', __('Import Users', 'wp_courseware'));
			break;
			
		default:
			printf('<span><b>%s</b></span>', __('Export Course', 'wp_courseware'));
			printf('&nbsp;|&nbsp;');
			printf('<span><a href="%s&show=import">%s</a></span>', admin_url('admin.php?page=WPCW_showPage_ImportExport'), __('Import Course', 'wp_courseware'));
			printf('&nbsp;|&nbsp;');
			printf('<span><a href="%s&show=import_users">%s</a></span>', admin_url('admin.php?page=WPCW_showPage_ImportExport'), __('Import Users', 'wp_courseware'));
			break;
	}	

	
	printf('</div>');
}


/**
 * Show the export course page.
 */
function WPCW_showPage_ImportExport_export()
{	
	$page = new PageBuilder(true);
	$page->showPageHeader(__('Export Training Course', 'wp_courseware'), '75%', WPCW_icon_getPageIconURL());
	
	// Show form of courses that can be exported.
	$form = new FormBuilder('wpcw_export');
	$form->setSubmitLabel(__('Export Course', 'wp_courseware'));
	
	// Course selection
	$formElem = new FormElement('export_course_id', __('Course to Export', 'wp_courseware'), true);
	$formElem->setTypeAsComboBox(WPCW_courses_getCourseList(__('--- Select a course to export ---', 'wp_courseware')));
	$form->addFormElement($formElem);
	
	// Options for what to export
	$formElem = new FormElement('what_to_export', __('What to Export', 'wp_courseware'), true);
	$formElem->setTypeAsRadioButtons(array(
		'whole_course'				=> __('<b>All</b> - The whole course - including modules, units and quizzes.', 'wp_courseware'),
		'just_course'				=> __('<b>Just the Course</b> - Just the course title, description and settings (no modules, units or quizzes).', 'wp_courseware'),	
		'course_modules'			=> __('<b>Course and Modules</b> - Just the course settings and module settings (no units or quizzes).', 'wp_courseware'),
		'course_modules_and_units'	=> __('<b>Course, Modules and Units</b> - The course settings and module settings and units (no quizzes).', 'wp_courseware'),
	));
	$form->addFormElement($formElem);
	
	$form->setDefaultValues(array(
		'what_to_export' => 'whole_course'
	));
	
	
	
	
	if ($form->formSubmitted())
	{
		// Do the full export
		if ($form->formValid()) {
			// If data is valid, export will be handled by export class.  
		}
		
		// Show errors
		else  {			
			$page->showListOfErrors($form->getListOfErrors(), __('Sorry, but unfortunately there were some errors. Please fix the errors and try again.', 'wp_courseware'));
		}
	}
	
	
	// Show selection menu for import/export to save pages
	WPCW_showPage_ImportExport_menu('export');	
	
	printf('<p class="wpcw_doc_quick">');
	_e('When you export a course, you\'ll get an <b>XML file</b>, which you can then <b>import into another WordPress website</b> that\'s running <b>WP Courseware</b>.<br/> 
	    When you export the course units with a course, just the <b>HTML to render images and video</b> will be copied, but the <b>actual images and video files will not be exported</b>.', 'wp_courseware');
	printf('</p>');
	
	echo $form->toString();
	
	$page->showPageFooter();
}


/**
 * Show the import course page.
 */
function WPCW_showPage_ImportExport_import()
{
	$page = new PageBuilder(true);
	$page->showPageHeader(__('Import Training Course', 'wp_courseware'), '75%', WPCW_icon_getPageIconURL());	
	
		
	
	// Show selection menu for import/export to save pages	
	WPCW_showPage_ImportExport_menu('import');
	
	
	// Show form to import some XML
	$form = new FormBuilder('wpcw_import');
	$form->setSubmitLabel(__('Import Course', 'wp_courseware'));
	
	// Course upload for XML file
	$formElem = new FormElement('import_course_xml', __('Course Import XML File', 'wp_courseware'), true);
	$formElem->setTypeAsUploadFile();
	$form->addFormElement($formElem);
	
	
	if ($form->formSubmitted())
	{
		// Do the full export
		if ($form->formValid()) 
		{
			// Handle the importing/uploading
			WPCW_courses_importCourseFromFile($page);
		}
		
		// Show errors
		else  {
			$page->showListOfErrors($form->getListOfErrors(), __('Unfortunately, there were some errors trying to import the CSV file.', 'wp_courseware'));
		}
	}
	
	// Workout maximum upload size
	$max_upload = (int)(ini_get('upload_max_filesize'));
	$max_post = (int)(ini_get('post_max_size'));
	$memory_limit = (int)(ini_get('memory_limit'));
	$upload_mb = min($max_upload, $max_post, $memory_limit);
	
	printf('<p class="wpcw_doc_quick">');
	printf(__('You can import any export file created by <b>WP Courseware</b> using the form below.', 'wp_courseware') . ' ' . __('The <b>maximum upload file size</b> for your server is <b>%d MB</b>.', 'wp_courseware'), $upload_mb);
	printf('</p>');
	
	echo $form->toString();
	
	$page->showPageFooter();
}




/**
 * Show the import course page.
 */
function WPCW_showPage_ImportExport_importUsers()
{
	$page = new PageBuilder(true);
	$page->showPageHeader(__('Import Users from CSV File', 'wp_courseware'), '75%', WPCW_icon_getPageIconURL());	
	
		
	// Show selection menu for import/export to save pages	
	WPCW_showPage_ImportExport_menu('import_users');
	
	
	// Show form to import some XML
	$form = new FormBuilder('wpcw_import_users');
	$form->setSubmitLabel(__('Import Users', 'wp_courseware'));
	
	// Course upload for XML file
	$formElem = new FormElement('import_course_csv', __('User Import CSV File', 'wp_courseware'), true);
	$formElem->setTypeAsUploadFile();
	$form->addFormElement($formElem);
	
	
	if ($form->formSubmitted())
	{
		// Do the full export
		if ($form->formValid()) 
		{
			// Handle the importing/uploading
			WPCW_users_importUsersFromFile($page);
		}
		
		// Show errors
		else  {
			$page->showListOfErrors($form->getListOfErrors(), __('Unfortunately, there were some errors trying to import the XML file.', 'wp_courseware'));
		}
	}
	
	// Workout maximum upload size
	$max_upload = (int)(ini_get('upload_max_filesize'));
	$max_post = (int)(ini_get('post_max_size'));
	$memory_limit = (int)(ini_get('memory_limit'));
	$upload_mb = min($max_upload, $max_post, $memory_limit);
	
	printf('<p class="wpcw_doc_quick">');
	printf(__('You can import a CSV file of users using the form below.', 'wp_courseware') . ' ' . __('The <b>maximum upload file size</b> for your server is <b>%d MB</b>.', 'wp_courseware'), $upload_mb);
	printf('</p>');	
	
	echo $form->toString();
	
	
	printf('<br/><br/><div class="wpcw_docs_wrapper">');
		printf('<b>%s</b>', __('Some tips for importing users via a CSV file:', 'wp_courseware'));
		printf('<ul>');
			printf('<li>' . __('If a user email address already exists, then just the courses are updated for that user.', 'wp_courseware'));
			printf('<li>' . __('User names are generated from the first and last name information. If a user name already exists, then a unique username is generated.', 'wp_courseware'));
			printf('<li>' . __('To add a user to many courses, just separate those course IDs with a comma in the <code>courses_to_add_to</code> column.', 'wp_courseware'));
			printf('<li>' . __('If a user is created, any courses set to be automatically assigned will be done first, and then the courses added in the <code>courses_to_add_to</code> column.', 'wp_courseware'));
			printf('<li>' . __('You can download an <a href="%s">example CSV file here</a>.', 'wp_courseware') . '</li>', 								admin_url('?wpcw_export=csv_import_user_sample'));	
			printf('<li>' . __('The IDs for the training courses can be found on the <a href="%s">course summary page</a>.', 'wp_courseware'). '</li>', admin_url('admin.php?page=WPCW_wp_courseware'));
		printf('</ul>');
	printf('</div>');
	
	$page->showPageFooter();
}


/**
 * Handles the upload and import of the course file.
 * @param Object $page The page object to show messages.
 */
function WPCW_courses_importCourseFromFile($page)
{
	if (isset($_FILES['import_course_xml']['name']))
	{
		// See what type of file we're tring to upload
		$type = strtolower($_FILES['import_course_xml']['type']);
		$fileTypes = array(
			'text/xml',
			'application/xml',
		);
		
		if (!in_array($type, $fileTypes)) {
			$page->showMessage(__('Unfortunately, you tried to upload a file that isn\'t XML.', 'wp_courseware'), true);
			return false;
		}		
		
		// Filetype is fine, carry on
		$errornum = $_FILES['import_course_xml']['error'] + 0;
		$tempfile = $_FILES['import_course_xml']['tmp_name'];
		
		
		// File uploaded successfully?				
		if ($errornum == 0)
		{
			// Try the import, return error/success here
			$importResults = WPCW_Import::importTrainingCourseFromXML($tempfile);
			if ($importResults['errors']) 
			{
				$page->showListOfErrors($importResults['errors'], __('Unfortunately, there were some errors trying to import the XML file.', 'wp_courseware'));
			} 
			
			// All worked - so show a link to the newly created course here.
			else 
			{
				$message = __('The course was successfully imported.', 'wp_courseware') . '<br/><br/>'; 
				$message .= sprintf(__('You can now <a href="%s">edit the Course Settings</a> or <a href="%s">edit the Unit &amp; Module Ordering</a>.', 'wp_courseware'), 
					admin_url('admin.php?page=WPCW_showPage_ModifyCourse&course_id='.$importResults['course_id']),
					admin_url('admin.php?page=WPCW_showPage_CourseOrdering&course_id='.$importResults['course_id'])
				);
			
				
				$page->showMessage($message);
			}
		}
		// Error occured, so report a meaningful error
		else
		{
			switch ($errornum)
			{				
				case UPLOAD_ERR_FORM_SIZE:
				case UPLOAD_ERR_INI_SIZE:
					$page->showMessage(__("Unfortunately the file you've uploaded is too large for the system.", 'wp_courseware'), true);
					break;
					
				case UPLOAD_ERR_PARTIAL:
				case UPLOAD_ERR_NO_FILE:
					$page->showMessage(__("For some reason, the file you've uploaded didn't transfer correctly to the server. Please try again.", 'wp_courseware'), true);
					break;
					
				case UPLOAD_ERR_NO_TMP_DIR:
				case UPLOAD_ERR_CANT_WRITE:
					$page->showMessage(__("There appears to be an issue with your server, as the import file could not be stored in the temporary directory.", 'wp_courseware'), true);
					break;
					
				case UPLOAD_ERR_EXTENSION:
					$page->showMessage(__('Unfortunately, you tried to upload a file that isn\'t XML.', 'wp_courseware'), true);
					break;
			}
		}
	} 
}


/**
 * Handles the upload and import of the user CSV file.
 * @param Object $page The page object to show messages.
 */
function WPCW_users_importUsersFromFile($page)
{
	set_time_limit(0);
	$page->showMessage(__('Import started...', 'wp_courseware'));
	flush();
	
	if (isset($_FILES['import_course_csv']['name']))
	{
		// See what type of file we're tring to upload
		$type = strtolower($_FILES['import_course_csv']['type']);
		$fileTypes = array(
			'text/csv', 
			'text/plain', 
			'application/csv', 
			'text/comma-separated-values', 
			'application/excel', 
			'application/vnd.ms-excel', 
			'application/vnd.msexcel', 
			'text/anytext', 
			'application/octet-stream', 
			'application/txt'
		);
		
		if (!in_array($type, $fileTypes)) {
			$page->showMessage(__('Unfortunately, you tried to upload a file that isn\'t a CSV file.', 'wp_courseware'), true);
			return false;
		}		
		
		// Filetype is fine, carry on
		$errornum = $_FILES['import_course_csv']['error'] + 0;
		$tempfile = $_FILES['import_course_csv']['tmp_name'];
		
		
		// File uploaded successfully?				
		if ($errornum == 0)
		{
			// Try the import, return error/success here
			if (($csvHandle = fopen($tempfile, "r")) !== FALSE)
			{
				$assocData = array();
				$rowCounter = 0;
				
				// Extract the user details from the CSV file into an array for importing.
				while (($rowData = fgetcsv($csvHandle, 0, ",")) !== FALSE) 
				{
					if (0 === $rowCounter) {
						$headerRecord = $rowData;
					} else {
						foreach( $rowData as $key => $value) {
							$assocData[$rowCounter - 1][$headerRecord[$key]] = $value;  
						}
						$assocData[$rowCounter - 1]['row_num'] = $rowCounter + 1;
					}
					$rowCounter++;
				}
				
				// Check we have users to process before continuing.
				if (count($assocData) < 1) {
					$page->showMessage(__('No data was found in the CSV file, so there is nothing to do.', 'wp_courseware'), true);
					return;
				}
				
				
				// Get a list of all courses that we can add a user too.
				$courseList = WPCW_courses_getCourseList(false);
				
				// Statistics for update.
				$count_newUser = 0;
				$count_skippedButUpdated = 0;
				$count_aborted = 0;
				
				// By now, $assocData contains a list of user details in an array. 
				// So now we try to insert all of these users into the system, and validate them all.
				$skippedList = array();
				foreach ($assocData as $userRowData)
				{
					// #### 1 - See if we have a username that we can use. If not, abort.
					$firstName = trim($userRowData['first_name']);
					$lastName  = trim($userRowData['last_name']);
					
					$userNameToCreate = $firstName.$lastName;
					if (!$userNameToCreate)
					{
						$skippedList[] = array(
							'id' 		=> $userRowData,
							'row_num'	=> $userRowData['row_num'],
							'aborted'	=> true,
							'reason' 	=> __('Cannot create a user with no name.', 'wp_courseware')
						);
						$count_aborted++;
						continue;
					} // username check		
					

					// // #### 2 - Email address of user already exists.
					if ($userID = email_exists($userRowData['email_address']))
					{
						$skippedList[] = array(
							'id' 		=> $userRowData,
							'row_num'	=> $userRowData['row_num'],
							'aborted'	=> false,
							'reason' 	=> __('Email address already exists.', 'wp_courseware')
						);				

						$count_skippedButUpdated++;
					} 
					
					
					// #### 3 - User does not exist, so creating
					else 
					{
						
						// #### 3A - Try and create a unique Username
						$userlogin = $userNameToCreate;
					    while (username_exists($userlogin)) 
					    {
					    	$userlogin = $userNameToCreate . rand(10, 999);
					    }
						
					    
					    // #### 3B - Create a new password
					    $newPassword = wp_generate_password(15);
						
						// #### 3C - Try to create the new user
					   	$userDetailsToAdd = array(
					   		'user_login'	=> $userlogin,
					    	'user_email'	=> $userRowData['email_address'],
						    'first_name'	=> $firstName,
						    'last_name'		=> $lastName,
						    'display_name'	=> trim($firstName . ' ' . $lastName),
					   		'user_pass'		=> $newPassword,
					    );
						
					    // #### 3D - Check for error when creating
					    $result = wp_insert_user($userDetailsToAdd);
					    if (is_wp_error($result))
					    {				    	
					    	$skippedList[] = array(
								'id' 		=> $userRowData,
								'row_num'	=> $userRowData['row_num'],
					    		'aborted'	=> true,
								'reason' 	=> $result->get_error_message()
							);
							$count_aborted++;
							continue;
					    }
					    								
						// #### 3E - User now exists at this point, copy ID
						// to user ID variable.
						$userID = $result;
						
						// #### 3F - Notify user of their new password.
						wp_new_user_notification($userID, $newPassword);
						flush();
						
						$count_newUser++;						
					}
					
					// #### 4 - Break list of courses into an array, and then add that user to those courses
					$coursesToAdd = explode(',', $userRowData['courses_to_add_to']);
					if ($coursesToAdd && count($coursesToAdd) > 0) {					
						WPCW_courses_syncUserAccess($userID, $coursesToAdd); 
					}
					
				}
				
				// Summary import.
				$page->showMessage(__('Import complete!', 'wp_courseware') . ' ' . sprintf(__('%d users were registered, %d users were updated, and %d user entries could not be processed.', 'wp_courseware'), 
					$count_newUser, $count_skippedButUpdated, $count_aborted)
				);
				
				
				// Show any skipped users
				if (!empty($skippedList)) 
				{
					printf('<div id="wpcw_user_import_skipped">');					
						printf('<b>' . __('The following %d users were not imported:', 'wp_courseware') . '</b>', count($skippedList));
						
						printf('<table class="widefat">');
							printf('<thead>');
								printf('<tr>');
									printf('<th>%s</th>', __('Line #', 'wp_courseware'));
									printf('<th>%s</th>', __('User Email Address', 'wp_courseware'));
									printf('<th>%s</th>', __('Reason why not imported', 'wp_courseware'));
									printf('<th>%s</th>', __('Updated Anyway?', 'wp_courseware'));
								printf('</tr>');
							printf('</thead>');
							
							$odd = false;
							foreach ($skippedList as $skipItem)
							{
								printf('<tr class="%s %s">', ($odd ? 'alternate' : ''), ($skipItem['aborted'] ? 'wpcw_error' : 'wpcw_ok'));
								printf('<td>%s</td>', $skipItem['row_num']);
								printf('<td>%s</td>', $skipItem['id']['email_address']);
								printf('<td>%s</td>', $skipItem['reason']);
								printf('<td>%s</td>', ($skipItem['aborted'] ? __('No, Aborted', 'wp_courseware') : __('Yes', 'wp_courseware')));
								printf('</tr>');
								
								$odd = !$odd;
							}
						
						printf('</table>');
										
					printf('</div>');
				}
				
				
				// All done
				fclose($csvHandle);
			}
			else {
				$page->showMessage(__('Unfortunately, the temporary CSV file could not be opened for processing.', 'wp_courseware'), true);
				return;
			}
			
		}
		// Error occured, so report a meaningful error
		else
		{
			switch ($errornum)
			{				
				case UPLOAD_ERR_FORM_SIZE:
				case UPLOAD_ERR_INI_SIZE:
					$page->showMessage(__("Unfortunately the file you've uploaded is too large for the system.", 'wp_courseware'), true);
					break;
					
				case UPLOAD_ERR_PARTIAL:
				case UPLOAD_ERR_NO_FILE:
					$page->showMessage(__("For some reason, the file you've uploaded didn't transfer correctly to the server. Please try again.", 'wp_courseware'), true);
					break;
					
				case UPLOAD_ERR_NO_TMP_DIR:
				case UPLOAD_ERR_CANT_WRITE:
					$page->showMessage(__("There appears to be an issue with your server, as the import file could not be stored in the temporary directory.", 'wp_courseware'), true);
					break;
					
				case UPLOAD_ERR_EXTENSION:
					$page->showMessage(__('Unfortunately, you tried to upload a file that isn\'t a CSV file.', 'wp_courseware'), true);
					break;
			}
		} 
	} // end of if (isset($_FILES['import_course_csv']['name'])) 
}

?>