<?php

// Check that plugin is active (so that this cannot be accessed if plugin isn't).
require(dirname(__FILE__) . '/../../../wp-config.php' );


// Can't find active WP Courseware init function, so cannot be active.
if (!function_exists('WPCW_plugin_init')) {
	WPCW_certificate_notFound();
}

// Certificate class
include_once 'pdf/pdf_certificates.inc.php';

// Grab the certificate from the parameter
$certificateID = WPCW_arrays_getValue($_GET, 'certificate');

// Nothing to see.
if (!$certificateID) {
	WPCW_certificate_notFound();
}


// #### PREVIEW - Has a preview been requested? Is the user logged in and is permitted to preview.
if ('preview' == $certificateID)
{
	// User can change options - allow preview
	if (current_user_can('manage_options')) 
	{
		// See if the provided ID is a valid ID
		$current_user = wp_get_current_user();
		
		// Generate certificate
		$cert = new WPCW_Certificate();
		$cert->generatePDF(WPCW_users_getUsersName($current_user), __('This is an example course...', 'wp_courseware'), false, 'browser');		
		die();
	}
	// User cannot change options, so they should not be able to accesss this.
	else {
		WPCW_certificate_notFound();
	}
}

// No, appears to be a proper certificate
else 
{
	// Check database for the certificate by the ID
	$certificateDetails = WPCW_certificate_getCertificateDetails_byAccessKey($certificateID);
			
	// Not a valid certificate, abort
	if (!$certificateDetails) {
		WPCW_certificate_notFound();
	}
	
	$courseDetails 	= WPCW_courses_getCourseDetails($certificateDetails->cert_course_id);	
	$userInfo 		= get_userdata($certificateDetails->cert_user_id);
	
	// Not a valid course or user data
	if (!$certificateDetails || !$userInfo) {
		WPCW_certificate_notFound();
	}
	
	// Generate certificate to download
	$cert = new WPCW_Certificate();
	$cert->generatePDF(WPCW_users_getUsersName($userInfo), $courseDetails->course_title, $certificateDetails, 'browser'); 		
	die();
}



/**
 * Show a generic error, details not found.
 */
function WPCW_certificate_notFound()
{
	_e('No certificate was found.', 'wp_courseware');
	die();
}

die();

?>