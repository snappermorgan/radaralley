<?php
/**
 * WP Courseware
 * 
 * Functions relating to showing the question pool page.
 */




/**
 * Shows the main Question Pool table.
 */
function WPCW_showPage_QuestionPool_load()
{	
	global $wpcwdb, $wpdb;
	$wpdb->show_errors();
	
	// Get the requested page number
	$paging_pageWanted = WPCW_arrays_getValue($_GET, 'pagenum') + 0;
	if ($paging_pageWanted == 0) {
		$paging_pageWanted = 1;
	}
	
	// Title for page with page number
	$titlePage = false;
	if ($paging_pageWanted > 1) {
		$titlePage = sprintf(' - %s %s', __('Page', 'wp_courseware'), $paging_pageWanted);
	}
	
	$page = new PageBuilder(false);
	$page->showPageHeader(__('Question Pool', 'wp_courseware').$titlePage, '75%', WPCW_icon_getPageIconURL());
	
	
	// Handle the question deletion before showing remaining questions
	WPCW_quizzes_handleQuestionDeletion($page);	
	
	// Show the main pool table
	echo WPCW_questionPool_showPoolTable(50, $_GET, 'std', $page);
	
	$page->showPageFooter();
}




/**
 * Handle the question deletion from the question pool page.
 * @param PageBuilder $page The page rendering object.
 */
function WPCW_quizzes_handleQuestionDeletion($page)
{
	global $wpcwdb, $wpdb;
	$wpdb->show_errors();
	
	// Check that the question exists and deletion has been requested
	if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['question_id']))
	{
		$questionID = $_GET['question_id'];
		$questionDetails = WPCW_questions_getQuestionDetails($questionID);
		
		// Only do deletion if question details are valid.
		if ($questionDetails)
		{
			// Delete question from question map
			$wpdb->query($wpdb->prepare("
				DELETE FROM $wpcwdb->quiz_qs_mapping
				WHERE question_id = %d
			", $questionDetails->question_id));
			
			
			// Finally delete question itself
			$wpdb->query($wpdb->prepare("
				DELETE FROM $wpcwdb->quiz_qs 
				WHERE question_id = %d
			", $questionDetails->question_id));
			
			
			$page->showMessage(sprintf(__('The question \'%s\' was successfully deleted.', 'wp_courseware'), $questionDetails->question_question));
			
		} // end of if $questionDetails
		
	} // end of check for deletion action
}


/**
 * Fetch the form for showing actions at the bottom of the page for the QuestionPool.
 */
function WPCW_showPage_QuestionPool_actionForm()
{
	// Start wrapper for bulk actions
	$formWrapper_end = '<div id="wpcw_tbl_question_pool_bulk_actions">'; 
	
		// Error messages - if no questions or tags have been selected.
		$formWrapper_end .= sprintf('<div id="wpcw_bulk_action_message_no_questions" class="wpcw_msg_error">%s</div>', __('Please select <b>at least 1 question</b> before continuing...', 'wp_courseware'));
		$formWrapper_end .= sprintf('<div id="wpcw_bulk_action_message_no_tag_first" class="wpcw_msg_error">%s</div>', __('Please select the <b>first tag</b> before continuing...', 'wp_courseware'));
		$formWrapper_end .= sprintf('<div id="wpcw_bulk_action_message_no_tag_second" class="wpcw_msg_error">%s</div>', __('Please select the <b>second tag</b> before continuing...', 'wp_courseware'));
	
		// Label - saying these are actions
		$formWrapper_end .= sprintf('<label>%s</label>', __('Action for selected questions?', 'wp_courseware'));
		
		// Dropdown of actions
		$formWrapper_end .= sprintf(WPCW_forms_createDropdown('wpcw_bulk_action_actions', array(
			''				=> __('--- Select action ---', 'wp_courseware'),
			'add_tag'		=> __('Add tag to selected questions', 'wp_courseware'),
			'remove_tag'	=> __('Remove tag from selected questions', 'wp_courseware'),
			'replace_tag'	=> __('Replace all instances of tag', 'wp_courseware'),
		), false, 'wpcw_tbl_question_pool_bulk_actions_chooser', false));
		
		// #### The starting labels for all 3 actions.
		$formWrapper_end .= sprintf('<label class="wpcw_bulk_action_label wpcw_bulk_action_add_tag">%s:</label>', __('Add Tag', 'wp_courseware'));
		$formWrapper_end .= sprintf('<label class="wpcw_bulk_action_label wpcw_bulk_action_remove_tag">%s:</label>', __('Remove Tag', 'wp_courseware'));
		$formWrapper_end .= sprintf('<label class="wpcw_bulk_action_label wpcw_bulk_action_replace_tag">%s:</label>', __('Replace Tag', 'wp_courseware'));
		
		// #### All 3 - Selector for Add/Remove/Replace tag - first box
		$formWrapper_end .= WPCW_questions_tags_getTagDropdown(__('Select a tag', 'wp_courseware'), 
			'wpcw_bulk_action_select_tag_a', 	// Name 
			WPCW_arrays_getValue($_POST, 'wpcw_bulk_action_select_tag_a'),
			'wpcw_bulk_action_select_tag_a wpcw_bulk_action_select_tag wpcw_bulk_action_add_tag wpcw_bulk_action_remove_tag wpcw_bulk_action_replace_tag' // CSS Classes
		);
		
		// ### Just 'Replace Tag' - the second label
		$formWrapper_end .= sprintf('<label class="wpcw_bulk_action_label wpcw_bulk_action_replace_tag">%s:</label>', __('With', 'wp_courseware'));
		
		// Just 'Replace Tag' - the second dropdown
		$formWrapper_end .= WPCW_questions_tags_getTagDropdown(__('Select a tag', 'wp_courseware'), 
			'wpcw_bulk_action_select_tag_b', 	// Name 
			WPCW_arrays_getValue($_POST, 'wpcw_bulk_action_select_tag_b'),
			'wpcw_bulk_action_select_tag_b wpcw_bulk_action_select_tag wpcw_bulk_action_replace_tag'
		);
	
		// Button - submit
		$formWrapper_end .= sprintf('<input type="submit" class="button-primary" value="%s">', __('Update Questions', 'wp_courseware'));

	// End wrapper for bulk actions
	$formWrapper_end .= '</div>';
	
	return $formWrapper_end;
}


/**
 * Process the action form to change tags for the selected questions.
 */
function WPCW_showPage_QuestionPool_processActionForm($page)
{
	global $wpdb, $wpcwdb;
	$wpdb->show_errors();
	
	if (!isset($_POST['wpcw_bulk_action_actions'])) {
		return;
	}
	
	// #### #1 - Get a list of the questions to update
	$questionListToUpdate =  array();
	foreach ($_POST as $key => $value)
	{
		// We're looking for these to get the question ID
		// 		[question_162] => on
		//		[question_149] => on
		if (preg_match('/^question_([0-9]+)$/', $key, $matches)) {
			$questionListToUpdate[] = $matches[1];
		}
	}
	
	// Appears there's nothing to do.
	if (empty($questionListToUpdate)) {
		$page->showMessage(__('Error. Please select some questions to update.', 'wp_courseware'), true);
		return;
	}
		
	
	// #### #2 - Validate that the questions do indeed exist
	// Direct SQL is ok here, as IDs have been validated with the regex previously.	
	$questionListStr = implode(',', $questionListToUpdate);
	$validatedQuestions = $wpdb->get_col("
		SELECT * 
		FROM $wpcwdb->quiz_qs
		WHERE question_id IN ($questionListStr) 
	");
	 
	// Appears there's nothing to do, as questions do not validate.
	if (empty($questionListToUpdate)) {
		$page->showMessage(__('Error. Those questions no longer exist. Please select some more questions to update.', 'wp_courseware'), true);
		return;
	}
		
	// #### #3 - Check that the action is what we're expecting. 
	$actionToProcess = WPCW_arrays_getValue($_POST, 'wpcw_bulk_action_actions');
	switch ($actionToProcess)
	{
		case 'add_tag':
		case 'remove_tag':
		case 'replace_tag':
		break;
		
		default: 
			$page->showMessage(__('Error. Did not recognise action to apply to selected questions.', 'wp_courseware'), true);
			return;
		break;
	}
	
	// #### #4 - Check that we have the tags that we're expecting.
	$tagID_first  = WPCW_arrays_getValue($_POST, 'wpcw_bulk_action_select_tag_a', 0);
	$tagID_second = WPCW_arrays_getValue($_POST, 'wpcw_bulk_action_select_tag_b', 0);
	
	$tagDetails_first = false;
	$tagDetails_second = false;
	
	if (!$tagDetails_first = WPCW_questions_tags_getTagDetails($tagID_first))
	{
		$page->showMessage(__('Error. The first tag does not exist. Please select another tag.', 'wp_courseware'), true);
		return;
	}
	
	// Check replace tag requirements
	if ('replace_tag' == $actionToProcess)
	{
		// No 2nd tag
		if (!$tagDetails_second = WPCW_questions_tags_getTagDetails($tagID_second)) {		
			$page->showMessage(__('Error. The second tag does not exist. Please select another tag.', 'wp_courseware'), true);
			return;
		}
	
		// 1st and 2nd tags match
		if ($tagDetails_first->question_tag_id == $tagDetails_second->question_tag_id) {
			$page->showMessage(__('Error. The first and second tag should be different.', 'wp_courseware'), true);
			return;
		}
	}
	
	// #### #5 - By this point, everything is validated, so just execute the SQL.
	foreach ($validatedQuestions as $questionID)
	{	
		switch ($actionToProcess)
		{
			case 'add_tag':
				$wpdb->query($wpdb->prepare("
					INSERT IGNORE $wpcwdb->question_tag_mapping
					(question_id, tag_id) 
					VALUES (%d, %d) 
				", $questionID, $tagDetails_first->question_tag_id));
			break;
		
			case 'remove_tag':
				$wpdb->query($wpdb->prepare("
					DELETE FROM $wpcwdb->question_tag_mapping
					WHERE question_id = %d
					  AND tag_id = %d 
				", $questionID, $tagDetails_first->question_tag_id));
			break;
				
			case 'replace_tag':
				$wpdb->query($wpdb->prepare("
					UPDATE $wpcwdb->question_tag_mapping
					  SET tag_id = %d
					WHERE question_id = %d
					  AND tag_id = %d 
				", 
					$tagDetails_second->question_tag_id, 
					$questionID, $tagDetails_first->question_tag_id
				));
			break;
		}
	}
	
	// Need to update tag counts
	WPCW_questions_tags_updatePopularity($tagDetails_first->question_tag_id);

	// 2nd is optional, so just need to check it exists first before trying update to prevent
	// an error message.
	if ($tagDetails_second) {
		WPCW_questions_tags_updatePopularity($tagDetails_second->question_tag_id);
	}
	
	// #### #6 Finally show message
	$page->showMessage(__('Questions successfully updated.', 'wp_courseware'));
}

?>