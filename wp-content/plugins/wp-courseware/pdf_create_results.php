<?php

// Check that plugin is active (so that this cannot be accessed if plugin isn't).
require(dirname(__FILE__) . '/../../../wp-config.php' );


// Can't find active WP Courseware init function, so cannot be active.
if (!function_exists('WPCW_plugin_init')) {
	WPCW_export_results_notFound();
}

// Get unit and quiz ID
$unitID = intval(WPCW_arrays_getValue($_GET, 'unitid'));
$quizID = intval(WPCW_arrays_getValue($_GET, 'quizid'));

// Get the post object for this quiz item.
$post = get_post($unitID);
if (!$post) {		
	WPCW_export_results_notFound(__('Could not find training unit.', 'wp_courseware'));
}

// Initalise the unit details
$fe = new WPCW_UnitFrontend($post);
	
// #### Get associated data for this unit. No course/module data, then it's not a unit 
if (!$fe->check_unit_doesUnitHaveParentData()) {
	WPCW_export_results_notFound(__('Could not find course details for unit.', 'wp_courseware'));
}

// #### User not allowed access to content
if (!$fe->check_user_canUserAccessCourse()) {
	WPCW_export_results_notFound($fe->fetch_message_user_cannotAccessCourse());
}


include_once 'pdf/pdf_quizresults.inc.php';
$qrpdf = new WPCW_QuizResults();

$parentData = $fe->fetch_getUnitParentData();
$quizDetails = $fe->fetch_getUnitQuizDetails();

// Set values for use in the results
$qrpdf->setTraineeName(WPCW_users_getUsersName($current_user));
$qrpdf->setCourseName($parentData->course_title);
$qrpdf->setQuizName($quizDetails->quiz_title);

// Render status messages
$qrpdf->setQuizMessages($fe->check_quizzes_workoutQuizPassStatusDetails());

// Render feedback messages
$qrpdf->setQuizFeedback($fe->fetch_customFeedbackMessage_calculateMessages());

// Render the results
$qrpdf->setQuizResults($fe->render_quizzes_showAllCorrectAnswers(true));

$qrpdf->generatePDF('browser');		
die();





/**
 * Show a generic error, details not found.
 */
function WPCW_export_results_notFound($extraMessage = false)
{
	printf(__('Could not export your results. %s', 'wp_courseware'), $extraMessage);
	die();
}

?>