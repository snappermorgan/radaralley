<?php
/**
 * Admin only AJAX functions.
 */


/**
 * Function called when adding a question to a quiz from the thickbox.
 */
function WPCW_AJAX_handleThickboxAction_QuestionPool_addQuestion()
{
	$questionID = WPCW_arrays_getValue($_POST, 'questionnum');
	$questionDetails = WPCW_questions_getQuestionDetails($questionID);
	
	// Convert the question to HTML based on type.
	if ($questionDetails)
	{
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
				
			case 'random_selection':
				$quizObj = new WPCW_quiz_RandomSelection($questionDetails);
				break;
				
			default:
				die(__('Unknown quiz type: ', 'wp_courseware') . $questionDetails->question_type);
				break;
		}
		
		$quizObj->showErrors = true;
		$quizObj->needCorrectAnswers = true;

		echo $quizObj->editForm_toString();
	}	
	die();
}


/**
 * Function called when any filtering occurs within the thickbox window
 * for the Question Pool.
 */
function WPCW_AJAX_handleThickboxAction_QuestionPool()
{
	$args = wp_parse_args($_POST, array(
		'pagenum' 	=> 1
	));
	
	// Create URL from parameters to use for building the question pool table
	echo WPCW_questionPool_showPoolTable(20, $args, 'ajax');
	
	die();
}


/**
 * Function called when a question tag needs to be removed.
 */
function WPCW_AJAX_handleQuestionRemoveTag()
{
	$ajaxResults = array(
		'success' 	=> true,
	);
	
	$tagID 		= intval(WPCW_arrays_getValue($_POST, 'tagid'));
	$questionID = intval(WPCW_arrays_getValue($_POST, 'questionid'));
	
	WPCW_questions_tags_removeTag($questionID, $tagID);
	
	header('Content-Type: application/json');
	echo json_encode($ajaxResults);
	die();
}


/**
 * Function called when a new question tag is added.
 */
function WPCW_AJAX_handleQuestionNewTag()
{
	$ajaxResults = array(
		'success' 	=> true,
		'errormsg' 	=> __('Unfortunately there was a problem adding the tag.', 'wp_courseware'),
		'html'		=> false
	);
	
	// Assume that we may have multiple tags, separated by commas.
	$potentialTagList = explode(',', WPCW_arrays_getValue($_POST, 'tagtext'));
	$cleanTagList = array();
	
	// Check if question is expected to have been saved.
	$hasQuestionBeenSaved = 'yes' == WPCW_arrays_getValue($_POST, 'isquestionsaved'); 
	
	// Got potential tags
	if (!empty($potentialTagList))
	{
		// Clean up each tag, and add to a list.
		foreach ($potentialTagList as $potentialTag)
		{
			$cleanTagList[] = sanitize_text_field(stripslashes($potentialTag));
		}
		
		// Check that cleaned tags are ok too
		if (!empty($cleanTagList)) 
		{
			// Do this if the question exists and we're adding tags.
			if ($hasQuestionBeenSaved)
			{
				// Get the ID of the question we're adding this tag to.
				$questionID = intval(WPCW_arrays_getValue($_POST, 'questionid'));
				
				
				// Validate that the question exists before we tag it.
				$questionDetails = WPCW_questions_getQuestionDetails($questionID);
				if (!$questionDetails) {
					$ajaxResults['errormsg'] = __('Unfortunately that question could not be found, so the tag could not be added.', 'wp_courseware');
					$ajaxResults['success'] = false;
				}
				
				// Question Found - carry on
				else
				{
					// Add the tag to the database, get a list of the tag details now that they have been added.
					$tagDetailList = WPCW_questions_tags_addTags($questionID, $cleanTagList);		
					foreach ($tagDetailList as $tagAddedID => $tagAddedText)
					{
						// Create the HTML to show the new tag.
						$ajaxResults['html'] .= sprintf('<span><a data-questionid="%d" data-tagid="%d" class="ntdelbutton">X</a>&nbsp;%s</span>', 
								$questionID, $tagAddedID, $tagAddedText
							);
					}
					
				} // else question found
			} 
			
			// We expect the question not to exist, hence we don't try to add to a question.
			else 
			{
				$tagDetailList = WPCW_questions_tags_addTags_withoutQuestion($cleanTagList);
				
				// For a new question, the ID is a string, not a value.
				$questionIDStr = WPCW_arrays_getValue($_POST, 'questionid');
				
				// Create a hidden form entry plus the little tag, so that we can add the tag to the question when we save.
				foreach ($tagDetailList as $tagAddedID => $tagAddedText)
				{
					// Create the HTML to show the new tag. We'll add the full string to the hidden field so that we can
					// add the tags later.
					$ajaxResults['html'] .= sprintf('
								<span>
									<a data-questionid="%d" data-tagid="%d" class="ntdelbutton">X</a>&nbsp;%s
									<input type="hidden" name="tags_to_add%s[]" value="%s" />
								</span>
								', 
							0, $tagAddedID, $tagAddedText,
							$questionIDStr, addslashes($tagAddedText)
						);
				} // end foreach
			}
			
		}
	}
	
	header('Content-Type: application/json');
	echo json_encode($ajaxResults);
	die();
}



/**
 * Function called when the unit is asked to be duplicated.
 */
function WPCW_AJAX_handleUnitDuplication()
{
	//error_log(print_r($_POST, true));
	
	// Security check
	if (!wp_verify_nonce(WPCW_arrays_getValue($_POST, 'security_id'), 'wpcw_ajax_unit_change')) {
        die (__('Security check failed!', 'wp_courseware'));
	}

	// See if we can get the post that we've asked to duplicate
	$sourcePostID = WPCW_arrays_getValue($_POST, 'source_id', false);
	$newUnit = get_post($sourcePostID, 'ARRAY_A');

	
	$ajaxResults = array(
		'success' 	=> true,
		'errormsg' 	=> false
	);
	
	// Got the new unit
	if ($newUnit) 
	{
		// Modify the post title to add ' Copy' to the end.
		$newUnit['post_title'] .= ' ' . __('Copy', 'wp_courseware');
		
		// Adjust date to today
		$newUnit['post_date'] = current_time('mysql');
		
		// Remove some of the keys relevant to the other post so that they are generated
		// automatically.
		unset($newUnit['ID']);
		unset($newUnit['guid']);
		unset($newUnit['comment_count']);
 		unset($newUnit['post_name']);
		unset($newUnit['post_date_gmt']);
				
		// Insert the post into the database
		$newUnitID = wp_insert_post($newUnit);
		
		// Duplicate all the taxonomies/terms
		$taxonomies = get_object_taxonomies($newUnit['post_type']);
		if (!empty($taxonomies))
		{
			foreach ($taxonomies as $taxonomy) {
				$terms = wp_get_post_terms($sourcePostID, $taxonomy, array('fields' => 'names'));
				wp_set_object_terms($newUnitID, $terms, $taxonomy);
			}
		}
	
		// Duplicate all the custom fields
		$custom_fields = get_post_custom($sourcePostID);
		if (!empty($custom_fields))
		{
			foreach ($custom_fields as $key => $value) {
				add_post_meta($newUnitID, $key, maybe_unserialize($value[0]));
			}
		}
	}
	
	// Post not found, show relevant error
	else 
	{
		$ajaxResults['success'] = false;
		$ajaxResults['errormsg'] = __('Post could not be found.', 'wp_courseware');
	}
	
	header('Content-Type: application/json');
	echo json_encode($ajaxResults);
	die();
}


/**
 * Function called when the unit ordering is being saved via AJAX.
 * This function will save the order of the modules, units and any unassigned units.
 * 
 */
function WPCW_AJAX_handleUnitOrderingSaving() 
{
	// Security check
	if (!wp_verify_nonce(WPCW_arrays_getValue($_POST, 'order_nonce'), 'wpcw-order-nonce')) {
        die (__('Security check failed!', 'wp_courseware'));
	}
	
	// Get list of modules to save, check IDs are what we expect, and abort if nothing to do.
	$moduleList = WPCW_arrays_getValue($_POST, 'moduleList');
	if (!$moduleList || count($moduleList) < 1) {
		die();
	}
	
	global $wpdb, $wpcwdb;
	$wpdb->show_errors();
	
	$parentCourseID = 0;
	
	// Save new module ordering to database
	$moduleOrderCount = 0;
	
	// Ordering of units is absolute to the whole course
	$unitOrderCount = 0; 
	
	//error_log(print_r($_POST, true));
	
	// Need a course ID for resetting the ordering.
	foreach ($moduleList as $moduleID) 
	{
		// Validate we have an actual module
		if (preg_match('/^wpcw_mod_(\d+)$/', $moduleID, $matches))
		{
			// Get course ID from module
			$moduleDetails = WPCW_modules_getModuleDetails($matches[1]);
			if ($moduleDetails) {
				$parentCourseID = $moduleDetails->parent_course_id;
				break;
			}
		}
	}	
	
	// If there's no associated parent course, there's an issue.
	if (!$parentCourseID) {
		error_log('WPCW_AJAX_handleUnitOrderingSaving(). No associated parent course ID, so aborting.');
		die();
	}
	
	
	// 2013-05-01 - Bug with orphan modules being left in the units_meta
	// Fix - Clean out existing units in this course, resetting them. 
	// Then update the ordering using the loops below.
	$SQL = $wpdb->prepare("
		UPDATE $wpcwdb->units_meta
		   SET unit_order = 0, parent_module_id = 0, 
		   	   parent_course_id = 0, unit_number = 0
		WHERE parent_course_id = %d
	", $parentCourseID);
	
	$wpdb->query($SQL);
	
	foreach ($moduleList as $moduleID)
	{		
		// ### Check module name matches expected format.
		if (preg_match('/^wpcw_mod_(\d+)$/', $moduleID, $matches))
		{
			$moduleOrderCount++;
			$moduleIDClean = $matches[1];
			
			// Update module list with new ordering
			$SQL = $wpdb->prepare("
				UPDATE $wpcwdb->modules
				   SET module_order = %d, module_number = %d
				WHERE module_id = %d
			", $moduleOrderCount, $moduleOrderCount, $moduleIDClean);
			
			$wpdb->query($SQL);
			
			
			// ### Check units associated with this module			
			$unitList = WPCW_arrays_getValue($_POST, $moduleID);
			if ($unitList && count($unitList) > 0)
			{
				$unitNumber = 0;
				foreach ($unitList as $unitID)
				{
					$unitNumber++;
					
					// Check unit name matches expected format.
					if (preg_match('/^wpcw_unit_(\d+)$/', $unitID, $matches))
					{
						$unitOrderCount += 10;
						$unitIDClean = $matches[1];

						// Update database with new association and ordering.
						$SQL = $wpdb->prepare("
							UPDATE $wpcwdb->units_meta
							   SET unit_order = %d, parent_module_id = %d, 
							   	   parent_course_id = %d, unit_number = %d
							WHERE unit_id = %d
						", $unitOrderCount, $moduleIDClean,  
						$parentCourseID, $unitNumber,
						$unitIDClean);
						
						$wpdb->query($SQL);
						
						// 2013-05-01 - Updated to use the module ID, rather than the module order.
						update_post_meta($unitIDClean, 'wpcw_associated_module', $moduleIDClean);						
					}
				}// end foreach 
			} // end of $unitList check
		}
	}
	
	
	// #### Check for any units that have associated quizzes
	foreach ($_POST as $key => $value)
	{
		// Check any post value that has a unit in it
		if (preg_match('/^wpcw_unit_(\d+)$/', $key, $matches)) 
		{
			$unitIDClean = $matches[1];
			
			// Try to extract the unit ID
			// [wpcw_unit_71] => Array
        	// (
            //	[0] => wpcw_quiz_2
        	//)			
			$quizIDRaw = false;
			if ($value && is_array($value)) {
				$quizIDRaw = $value[0];
			}
				
			// Got a matching quiz ID
			if (preg_match('/^wpcw_quiz_(\d+)$/', $quizIDRaw, $matches)) 
			{
				$quizIDClean = $matches[1];
				
				// Grab parent course ID from unit. Can't assume all units are in same course.
				$parentData = WPCW_units_getAssociatedParentData($unitIDClean);
				$parentCourseID = $parentData->parent_course_id;

				// Update database with new association and ordering.
				$SQL = $wpdb->prepare("
					UPDATE $wpcwdb->quiz
					   SET parent_unit_id = %d, parent_course_id = %d
					WHERE quiz_id = %d
				", $unitIDClean, $parentCourseID, $quizIDClean);
				
				$wpdb->query($SQL);			

				// Add new associated unit information to the user quiz progress,
				// keeping any existing quiz results.
				$SQL = $wpdb->prepare("
					UPDATE $wpcwdb->user_progress_quiz
					   SET unit_id = %d
					WHERE quiz_id = %d
				", $unitIDClean, $quizIDClean);
				
				$wpdb->query($SQL);
			}
		}
	}
	
	
	// #### Check for any unassigned units, and ensure they're de-associated from modules.
	$unitList = WPCW_arrays_getValue($_POST, 'unassunits');
	if ($unitList && count($unitList) > 0)
	{
		foreach ($unitList as $unitID)
		{
			// Check unit name matches expected format.
			if (preg_match('/^wpcw_unit_(\d+)$/', $unitID, $matches))
			{
				$unitIDClean = $matches[1];

				// Update database with new association and ordering.
				$SQL = $wpdb->prepare("
					UPDATE $wpcwdb->units_meta
					   SET unit_order = 0, parent_module_id = 0, parent_course_id = 0, unit_number = 0
					WHERE unit_id = %d
				", $unitIDClean);
				
				$wpdb->query($SQL);
				
				// Update post meta to remove associated module detail
				update_post_meta($unitIDClean, 'wpcw_associated_module', 0);
				
				// Remove progress for this unit, as likely to be associated with something else.
				$SQL = $wpdb->prepare("
					DELETE FROM $wpcwdb->user_progress
					WHERE unit_id = %d
				", $unitIDClean);
				
				$wpdb->query($SQL);
			}
		} // end foreach ($unitList as $unitID)
	}
	
	// #### Check for any unassigned quizzes, and ensure they're de-associated from units.
	$quizList = WPCW_arrays_getValue($_POST, 'unassquizzes');
	if ($quizList && count($quizList) > 0)
	{
		foreach ($quizList as $quizID)
		{
			// Check unit name matches expected format.
			if (preg_match('/^wpcw_quiz_(\d+)$/', $quizID, $matches))
			{
				$quizIDClean = $matches[1];

				// Update database with new association and ordering.
				$SQL = $wpdb->prepare("
					UPDATE $wpcwdb->quiz
					   SET parent_unit_id = 0, parent_course_id = 0
					WHERE quiz_id = %d
				", $quizIDClean);
				
				$wpdb->query($SQL);
				
				
				// Remove the associated unit information from the user quiz progress.
				// But keep the quiz results for now.
				$SQL = $wpdb->prepare("
					UPDATE $wpcwdb->user_progress_quiz
					   SET unit_id = 0
					WHERE quiz_id = %d
				", $quizIDClean);
				
				$wpdb->query($SQL);
			}
		} // end foreach ($quizList as $quizID)
	}
	
	// Update course details
	$courseDetails = WPCW_courses_getCourseDetails($parentCourseID);
	if ($courseDetails) {
		do_action('wpcw_course_details_updated', $courseDetails);
	}
	
	//error_log(print_r($matches, true));
	die(); 	
}


?>