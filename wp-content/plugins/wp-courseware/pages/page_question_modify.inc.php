<?php
/**
 * WP Courseware
 * 
 * Functions relating to modifying a question.
 */


/**
 * Function that allows a question to be edited.
 */
function WPCW_showPage_ModifyQuestion_load() 
{
	$page = new PageBuilder(true); 
	$page->showPageHeader(__('Edit Single Question', 'wp_courseware'), '70%', WPCW_icon_getPageIconURL());
	
	$questionID = false;
	
	// Check POST and GET
	if (isset($_GET['question_id'])) {
		$questionID = $_GET['question_id'] + 0;
	} 
	else if (isset($_POST['question_id'])) {
		$questionID = $_POST['question_id'] + 0;
	}

	// Trying to edit a question	
	$questionDetails = WPCW_questions_getQuestionDetails($questionID, true);	
	
	// Abort if question not found.
	if (!$questionDetails)
	{
		$page->showMessage(__('Sorry, but that question could not be found.', 'wp_courseware'), true);
		$page->showPageFooter();
		return;
	}
	
	
	// See if the question has been submitted for saving.
	if ('true' == WPCW_arrays_getValue($_POST, 'question_save_mode'))
	{
		WPCW_handler_questions_processSave(false, true);
		
		$page->showMessage(__('Question successfully updated.', 'wp_courseware'));
		
		// Assume save has happened, so reload the settings.
		$questionDetails = WPCW_questions_getQuestionDetails($questionID, true);
	}
	
	
	// Manually set the order to zero, as not needed for ordering in this context.
	$questionDetails->question_order = 0;
	
	switch ($questionDetails->question_type)
	{
		case 'multi':
			$quizObj = new WPCW_quiz_MultipleChoice($questionDetails);
			break;
			
		case 'truefalse':
			$quizObj = new WPCW_quiz_TrueFalse($questionDetails);					
			break;
			
		case 'open':
			$quizObj = new WPCW_quiz_OpenEntry($questionDetails);
			break;
			
		case 'upload':
			$quizObj = new WPCW_quiz_FileUpload($questionDetails);
			break;
			
		default:
			die(__('Unknown quiz type: ', 'wp_courseware') . $questionDetails->question_type);
			break;
	}
	
	$quizObj->showErrors = true;
	$quizObj->needCorrectAnswers = true;
	$quizObj->hideDragActions = true;

	
	// #wpcw_quiz_details_questions = needed for media uploader
	// .wpcw_question_holder_static = needed for wrapping the question using existing HTML.
	printf('<div id="wpcw_quiz_details_questions"><ul class="wpcw_question_holder_static">');
	
		// Create form wrapper, so that we can save this question.
		printf('<form method="POST" action="%s?page=WPCW_showPage_ModifyQuestion&question_id=%d" />', 
			admin_url('admin.php'), $questionDetails->question_id
		);	
	
			// Question hidden fields
			printf('<input name="question_id" type="hidden" value="%d" />', $questionDetails->question_id);
			printf('<input name="question_save_mode" type="hidden" value="true" />');
	
			// Show the quiz so that it can be edited. We're re-using the code we have for editing questions, 
			// to save creating any special form edit code.
			echo $quizObj->editForm_toString();
		
			// Save and return buttons.
			printf('<div class="wpcw_button_group"><br/>');						
				printf('<a href="%s?page=WPCW_showPage_QuestionPool" class="button-secondary">%s</a>&nbsp;&nbsp;', admin_url('admin.php'), __('&laquo; Return to Question Pool', 'wp_courseware'));
				printf('<input type="submit" class="button-primary" value="%s" />', __('Save Question Details', 'wp_courseware'));
			printf('</div>');
		
		printf('</form>');
	printf('</ul></div>');

	$page->showPageFooter();
}