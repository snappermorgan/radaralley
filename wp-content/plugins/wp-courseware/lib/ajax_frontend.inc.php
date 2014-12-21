<?php
/**
 * Frontend only AJAX functions.
 */
include_once 'frontend_only.inc.php'; // Ensure we have frontend functions

// Use object to handle the rendering of the unit on the frontend.
include_once WPCW_plugin_getPluginDirPath() . 'classes/class_frontend_unit.inc.php';


/**
 * Function called when user is requesting a retake of a quiz. Lots of checking
 * needs to go on here for security reasons to ensure that they don't manipulate 
 * their own progress (or somebody elses).
 */
function WPCW_AJAX_units_handleQuizRetakeRequest()
{
	// Security check
	if (!wp_verify_nonce(WPCW_arrays_getValue($_POST, 'progress_nonce'), 'wpcw-progress-nonce')) {
        die (__('Security check failed!', 'wp_courseware'));
	}
	
	// Get unit and quiz ID
	$unitID = intval(WPCW_arrays_getValue($_POST, 'unitid'));
	$quizID = intval(WPCW_arrays_getValue($_POST, 'quizid'));
	
	// Get the post object for this quiz item.
	$post = get_post($unitID);
	if (!$post) {		
		echo WPCW_UnitFrontend::message_createMessage_error(__('Error - could not request a retake for the quiz.', 'wp_courseware') . ' ' . __('Could not find training unit.', 'wp_courseware'));
		die();
	}
	
	// Initalise the unit details
	$fe = new WPCW_UnitFrontend($post);
		
	// #### Get associated data for this unit. No course/module data, then it's not a unit 
	if (!$fe->check_unit_doesUnitHaveParentData()) {
		echo WPCW_UnitFrontend::message_createMessage_error(__('Error - could not request a retake for the quiz.', 'wp_courseware') . ' ' . __('Could not find course details for unit.', 'wp_courseware'));
		die();
	}
	
	// #### User not allowed access to content
	if (!$fe->check_user_canUserAccessCourse()) {
		echo $fe->message_user_cannotAccessCourse();
		die();
	}
	
	// #### See if we're in a position to retake this quiz?
	if (!$fe->check_quizzes_canUserRequestRetake())
	{
		echo WPCW_UnitFrontend::message_createMessage_error(__('Error - could not request a retake for the quiz.', 'wp_courseware') . ' ' . __('You are not permitted to retake this quiz.', 'wp_courseware'));
		die();
	}
	
	// Trigger the upgrade to progress so that we're allowed to retake this quiz.
	$fe->update_quizzes_requestQuizRetake();
	
	// Only complete if allowed to continue.
	echo $fe->render_detailsForUnit(false, true);
	die();
}




/**
 * Function called when the user is marking a unit as complete. 
 */
function WPCW_AJAX_units_handleUserProgress() 
{
	// Security check
	if (!wp_verify_nonce(WPCW_arrays_getValue($_POST, 'progress_nonce'), 'wpcw-progress-nonce')) {
        die (__('Security check failed!', 'wp_courseware'));
	}
	
	$unitID = WPCW_arrays_getValue($_POST, 'id');
	
	// Validate the course ID
	if (!preg_match('/unit_complete_(\d+)/', $unitID, $matches)) {
		echo WPCW_UnitFrontend::message_error_getCompletionBox_error();
		die();
	}
	$unitID = $matches[1];
	
	
	// Get the post object for this quiz item.
	$post = get_post($unitID);
	if (!$post) {		
		echo WPCW_UnitFrontend::message_createMessage_error(__('Error - could not save your progress.', 'wp_courseware') . ' ' . __('Could not find training unit.', 'wp_courseware'));
		die();
	}
	
	// Initalise the unit details
	$fe = new WPCW_UnitFrontend($post);
		
	// #### Get associated data for this unit. No course/module data, then it's not a unit 
	if (!$fe->check_unit_doesUnitHaveParentData()) {
		echo WPCW_UnitFrontend::message_createMessage_error(__('Error - could not save your progress.', 'wp_courseware') . ' ' . __('Could not find course details for unit.', 'wp_courseware'));
		die();
	}
	
	// #### User not allowed access to content
	if (!$fe->check_user_canUserAccessCourse()) {
		echo $fe->message_user_cannotAccessCourse();
		die();
	}
	
	WPCW_units_saveUserProgress_Complete($fe->fetch_getUserID(), $fe->fetch_getUnitID(), 'complete');
	
	// Unit complete, check if course/module is complete too.
	do_action('wpcw_user_completed_unit', $fe->fetch_getUserID(), $fe->fetch_getUnitID(), $fe->fetch_getUnitParentData());
		
	// Only complete if allowed to continue.
	echo $fe->render_completionBox_complete();
	die();
}




/**
 * Function called when a user is submitting quiz answers via
 * the frontend form. 
 */
function WPCW_AJAX_units_handleQuizResponse() 
{
	// Security check
	if (!wp_verify_nonce(WPCW_arrays_getValue($_POST, 'progress_nonce'), 'wpcw-progress-nonce')) {
        die (__('Security check failed!', 'wp_courseware'));
	}
	
	// Quiz ID and Unit ID are combined in the single CSS ID for validation.
	// So validate both are correct and that user is allowed to access quiz.
	$quizAndUnitID = WPCW_arrays_getValue($_POST, 'id');
	
	// e.g. quiz_complete_69_1 or quiz_complete_17_2 (first ID is unit, 2nd ID is quiz)
	if (!preg_match('/quiz_complete_(\d+)_(\d+)/', $quizAndUnitID, $matches)) {
		echo WPCW_UnitFrontend::message_error_getCompletionBox_error();
		die();
	}

	// Use the extracted data for further validation
	$unitID = $matches[1];
	$quizID = $matches[2];
	
	// Get the post object for this quiz item.
	$post = get_post($unitID);
	if (!$post) {		
		echo WPCW_UnitFrontend::message_createMessage_error(__('Error - could not save your quiz results.', 'wp_courseware') . ' ' . __('Could not find training unit.', 'wp_courseware'));
		die();
	}
	
	// Initalise the unit details
	$fe = new WPCW_UnitFrontend($post);
	
	
	// #### Get associated data for this unit. No course/module data, then it's not a unit 
	if (!$fe->check_unit_doesUnitHaveParentData()) {
		echo WPCW_UnitFrontend::message_createMessage_error(__('Error - could not save your quiz results.', 'wp_courseware') . ' ' . __('Could not find course details for unit.', 'wp_courseware'));
		die();
	}
	
	// #### User not allowed access to content
	if (!$fe->check_user_canUserAccessCourse()) {
		echo $fe->message_user_cannotAccessCourse();
		die();
	}
	
	// #### Check that the quiz is valid and belongs to this unit
	if (!$fe->check_quizzes_isQuizValidForUnit($quizID)) {
		echo WPCW_UnitFrontend::message_createMessage_error(__('Error - could not save your quiz results.', 'wp_courseware') . ' ' . __('Quiz data does not match quiz for this unit.', 'wp_courseware'));
		die();
	}
	
	$canContinue = false;
	
	
	// #### Do we have all the answers that we need so that we can grade the quiz?	
	// #### Answer Check Variation A - Paging
	if ($fe->check_paging_areWePagingQuestions())
	{
		// If this is false, then we keep checking for more answers. 
		$readyForMarking = $fe->check_quizzes_canWeContinue_checkAnswersFromPaging($_POST);		
	}
	
	// #### Answer Check Variation B - All at once (no paging)
	else 
	{
		// If this is false, then the form is represented asking for fixes.
		$readyForMarking = $fe->check_quizzes_canWeContinue_checkAnswersFromOnePageQuiz($_POST);
	}
	
	
	
	// Now checks are done, $this->unitQuizProgress contains the latest questions so that we can mark them.
	if ($readyForMarking) {
		$canContinue = $fe->check_quizzes_gradeQuestionsForQuiz();
	}
	
	
	// #### Validate the answers that we have, which determines if we can carry on to the next 
	//      unit, or if the user needs to do something else.
	if ($canContinue) 
	{
		WPCW_units_saveUserProgress_Complete($fe->fetch_getUserID(), $fe->fetch_getUnitID(), 'complete');
		
		// Unit complete, check if course/module is complete too.
		do_action('wpcw_user_completed_unit', $fe->fetch_getUserID(), $fe->fetch_getUnitID(), $fe->fetch_getUnitParentData());
	}
	
	
	// Show the appropriate messages/forms for the user to look at. This is common for all execution
	// paths.
	echo $fe->render_detailsForUnit(false, true);
	die();
	
}


/**
 * Handle a user wanting to go to the previous question or jump a question without saving the question details.
 */
function WPCW_AJAX_units_handleQuizJumpQuestion()
{
	// Security check
	if (!wp_verify_nonce(WPCW_arrays_getValue($_POST, 'progress_nonce'), 'wpcw-progress-nonce')) {
        die (__('Security check failed!', 'wp_courseware'));
	}
	
	// Get unit and quiz ID
	$unitID = intval(WPCW_arrays_getValue($_POST, 'unitid'));
	$quizID = intval(WPCW_arrays_getValue($_POST, 'quizid'));
	
	$jumpMode = 'previous';
	$msgPrefix = __('Error - could not load the previous question.', 'wp_courseware'). ' ';
	
	// We're skipping ahead.
	if ('next' == WPCW_arrays_getValue($_POST, 'qu_direction'))
	{
		$jumpMode = 'next';
		$msgPrefix = __('Error - could not load the next question.', 'wp_courseware'). ' ';
	}
	
	
	
	// Get the post object for this quiz item.
	$post = get_post($unitID);
	if (!$post) {		
		echo WPCW_UnitFrontend::message_createMessage_error($msgPrefix . __('Could not find training unit.', 'wp_courseware'));
		die();
	}
	
	// Initalise the unit details
	$fe = new WPCW_UnitFrontend($post);
	
	
	// #### Get associated data for this unit. No course/module data, then it's not a unit 
	if (!$fe->check_unit_doesUnitHaveParentData()) {
		echo WPCW_UnitFrontend::message_createMessage_error($msgPrefix . __('Could not find course details for unit.', 'wp_courseware'));
		die();
	}
	
	// #### User not allowed access to content
	if (!$fe->check_user_canUserAccessCourse()) {
		echo $fe->message_user_cannotAccessCourse();
		die();
	}
	
	// #### Check that the quiz is valid and belongs to this unit
	if (!$fe->check_quizzes_isQuizValidForUnit($quizID)) {
		echo WPCW_UnitFrontend::message_createMessage_error($msgPrefix . __('Quiz data does not match quiz for this unit.', 'wp_courseware'));
		die();
	}
	
	$canContinue = false;
	

	// If we're paging, then do what we need next.
	if ($fe->check_paging_areWePagingQuestions()) {
		$fe->fetch_paging_getQuestion_moveQuestionMarker($jumpMode);
	}
	
	echo $fe->render_detailsForUnit(false, true);
	die();
}


/**
 * Function called when user starting a quiz and needs to kick off the timer.
 */
function WPCW_AJAX_units_handleQuizTimerBegin()
{
	// Security check
	if (!wp_verify_nonce(WPCW_arrays_getValue($_POST, 'progress_nonce'), 'wpcw-progress-nonce')) {
        die (__('Security check failed!', 'wp_courseware'));
	}
	
	// Get unit and quiz ID
	$unitID = intval(WPCW_arrays_getValue($_POST, 'unitid'));
	$quizID = intval(WPCW_arrays_getValue($_POST, 'quizid'));
	
	// Get the post object for this quiz item.
	$post = get_post($unitID);
	if (!$post) {		
		echo WPCW_UnitFrontend::message_createMessage_error(__('Error - could not start the timer for the quiz.', 'wp_courseware') . ' ' . __('Could not find training unit.', 'wp_courseware'));
		die();
	}
	
	// Initalise the unit details
	$fe = new WPCW_UnitFrontend($post);
		
	// #### Get associated data for this unit. No course/module data, then it's not a unit 
	if (!$fe->check_unit_doesUnitHaveParentData()) {
		echo WPCW_UnitFrontend::message_createMessage_error(__('Error - could not start the timer for the quiz.', 'wp_courseware') . ' ' . __('Could not find course details for unit.', 'wp_courseware'));
		die();
	}
	
	// #### User not allowed access to content
	if (!$fe->check_user_canUserAccessCourse()) {
		echo $fe->message_user_cannotAccessCourse();
		die();
	}
	
	// #### See if we're in a position to retake this quiz?
	if (!$fe->check_quizzes_canUserRequestRetake())
	{
		echo WPCW_UnitFrontend::message_createMessage_error(__('Error - could not start the timer for the quiz.', 'wp_courseware') . ' ' . __('You are not permitted to retake this quiz.', 'wp_courseware'));
		die();
	}
	
	// Trigger the upgrade to progress so that we can start the quiz, and trigger the timer.
	$fe->update_quizzes_beginQuiz();
	
	// Only complete if allowed to continue.
	echo $fe->render_detailsForUnit(false, true);
	die();
}

?>