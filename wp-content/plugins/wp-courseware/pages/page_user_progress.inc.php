<?php
/**
 * WP Courseware
 * 
 * Functions relating to showing the process for a specific user.
 */



/**
 * Shows a detailed summary of the user progress.
 */
function WPCW_showPage_UserProgess_load()
{
	global $wpcwdb, $wpdb;
	$wpdb->show_errors();
	
	$page = new PageBuilder(false);
	$page->showPageHeader(__('Detailed User Progress Report', 'wp_courseware'), '75%', WPCW_icon_getPageIconURL());
	
	
	// Check passed user ID is valid
	$userID = WPCW_arrays_getValue($_GET, 'user_id');
	$userDetails = get_userdata($userID); 
	if (!$userDetails) 
	{
		$page->showMessage(__('Sorry, but that user could not be found.', 'wp_courseware'), true);
		$page->showPageFooter();
		return false;		
	}

	printf(__('<p>Here you can see how well <b>%s</b> (Username: <b>%s</b>) is doing with your training courses.</p>', 'wp_courseware'), $userDetails->data->display_name, $userDetails->data->user_login);
		

	// #### 1 - Show a list of all training courses, and then list the units associated with that course.	
	$SQL = "SELECT * 
			FROM $wpcwdb->courses
			ORDER BY course_title ASC 
			";
	
	$courseCount = 0;
	
	$courses = $wpdb->get_results($SQL);
	if ($courses)  
	{
		foreach ($courses as $course)
		{
			$up = new UserProgress($course->course_id, $userID);
			
			// Skip if user is not allowed to access the training course.
			if (!WPCW_courses_canUserAccessCourse($course->course_id, $userID)) {
				continue;
			}
			
			printf('<h3 class="wpcw_tbl_progress_course">%s</h3>', $course->course_title);
			printf('<table class="widefat wpcw_tbl wpcw_tbl_progress">');
			
			printf('<thead>');
				printf('<th>%s</th>', 															__('Unit', 'wp_courseware'));
				printf('<th class="wpcw_center">%s</th>', 								__('Completed', 'wp_courseware'));
				printf('<th class="wpcw_center wpcw_tbl_progress_quiz_name">%s</th>', 	__('Quiz Name', 'wp_courseware'));
				printf('<th class="wpcw_center">%s</th>', 								__('Quiz Status', 'wp_courseware'));
				printf('<th class="wpcw_center">%s</th>', 								__('Actions', 'wp_courseware'));
			printf('</thead><tbody>');			
			
			
			// #### 2 - Fetch all associated modules 
			$modules = WPCW_courses_getModuleDetailsList($course->course_id);
			if ($modules)
			{
				foreach ($modules as $module)
				{
					// #### 3 - Render Modules as a heading.
					printf('<tr class="wpcw_tbl_progress_module">');
						printf('<td colspan="3">%s %d - %s</td>',
							__('Module', 'wp_courseware'),
							$module->module_number,
							$module->module_title
						);
						 
						// Blanks for Quiz Name and Actions.
						printf('<td>&nbsp;</td>');
						printf('<td>&nbsp;</td>');
					printf('</tr>');
					
					// #### 4. - Render the units for this module
					$units = WPCW_units_getListOfUnits($module->module_id);
					if ($units) 
					{						
						foreach ($units as $unit)
						{
							$showDetailLink = false;
							
							printf('<tr class="wpcw_tbl_progress_unit">');
							
							printf('<td class="wpcw_tbl_progress_unit_name">%s %d - %s</td>',
								__('Unit', 'wp_courseware'),
								$unit->unit_meta->unit_number,
								$unit->post_title
							);
							
							// Has the unit been completed yet?
							printf('<td class="wpcw_tbl_progress_completed">%s</td>', $up->isUnitCompleted($unit->ID) ? __('Completed', 'wp_courseware') : '');
							
							// See if there's a quiz for this unit?
							$quizDetails = WPCW_quizzes_getAssociatedQuizForUnit($unit->ID, false, $userID);
							
							// Render the quiz details.
							if ($quizDetails) 
							{
								// Title of quiz
								printf('<td class="wpcw_tbl_progress_quiz_name">%s</td>', $quizDetails->quiz_title);								
								
								// No correct answers, so mark as complete.
								if ('survey' == $quizDetails->quiz_type) 
								{
									$quizResults = WPCW_quizzes_getUserResultsForQuiz($userID, $unit->ID, $quizDetails->quiz_id);
									
									if ($quizResults)
									{
										printf('<td class="wpcw_tbl_progress_completed">%s</td>', __('Completed', 'wp_courseware'));
																			
										// Showing a link to view details
										$showDetailLink = true;
										printf('<td><a href="%s&user_id=%d&quiz_id=%d&unit_id=%d" class="button-secondary">%s</a></td>',	
											admin_url('users.php?page=WPCW_showPage_UserProgess_quizAnswers'),
											$userID, $quizDetails->quiz_id, $unit->ID,
											__('View Survey Details', 'wp_courseware')
										);
									}
									
									// Survey not taken yet
									else {
										printf('<td class="wpcw_center">%s</td>', __('Pending', 'wp_courseware'));
									}
								}
								
								// Quiz - show correct answers.
								else 
								{
									$quizResults = WPCW_quizzes_getUserResultsForQuiz($userID, $unit->ID, $quizDetails->quiz_id);
									
									// Show the admin how many questions were right.
									if ($quizResults) 
									{
										// -1% means that the quiz is needing grading.
										if ($quizResults->quiz_grade < 0) {
											printf('<td class="wpcw_center">%s</td>', __('Awaiting Final Grading', 'wp_courseware'));
										}
										else {
											printf('<td class="wpcw_tbl_progress_completed">%d%%</td>', number_format($quizResults->quiz_grade, 1));
										}
										
										
										// Showing a link to view details
										$showDetailLink = true;			
										
										printf('<td><a href="%s&user_id=%d&quiz_id=%d&unit_id=%d" class="button-secondary">%s</a></td>',	
											admin_url('users.php?page=WPCW_showPage_UserProgess_quizAnswers'),
											$userID, $quizDetails->quiz_id, $unit->ID,
											__('View Quiz Details', 'wp_courseware')
										);
										
									} // end of if  printf('<td class="wpcw_tbl_progress_completed">%s</td>'
									
									
									// Quiz not taken yet
									else {
										printf('<td class="wpcw_center">%s</td>', __('Pending', 'wp_courseware'));
									}
									
								} // end of if survey
							} // end of if $quizDetails
							
							
							// No quiz for this unit
							else {					
								printf('<td class="wpcw_center">-</td>');
								printf('<td class="wpcw_center">-</td>');
							}
							
							// Quiz detail link
							if (!$showDetailLink) {
								printf('<td>&nbsp;</td>');
							}
							
							printf('</tr>');
						}
						
					}
					
				}
			}
			
			printf('</tbody></table>');
			
			// Track number of courses user can actually access
			$courseCount++;
		}
		
		// Course is not allowed to access any courses. So show a meaningful message.
		if ($courseCount == 0) {
			$page->showMessage(sprintf(__('User <b>%s</b> is not currently allowed to access any training courses.', 'wp_courseware'), $userDetails->data->display_name), true);
		}
		
	}
	
	else {
		printf('<p>%s</p>', __('There are currently no courses to show. Why not create one?', 'wp_courseware'));
	}
		
	$page->showPageFooter();
}



/**
 * Shows a detailed summary of the user's quiz or survey answers.
 */
function WPCW_showPage_UserProgess_quizAnswers_load()
{
	global $wpcwdb, $wpdb;
	$wpdb->show_errors();
	
	$page = new PageBuilder(false);
	$page->showPageHeader(__('Detailed User Quiz/Survey Results', 'wp_courseware'), '75%', WPCW_icon_getPageIconURL());
	
	$userID = WPCW_arrays_getValue($_GET, 'user_id') + 0;
	$unitID = WPCW_arrays_getValue($_GET, 'unit_id') + 0;
	$quizID = WPCW_arrays_getValue($_GET, 'quiz_id') + 0;
	
	
	
	// Create a link back to the detailed user progress, and back to all users.
	printf('<div class="wpcw_button_group">');
	
	// Link back to all user summary
	printf('<a href="%s" class="button-secondary">%s</a>&nbsp;&nbsp;', 
		admin_url('users.php'),
		__('&laquo; Return to User Summary', 'wp_courseware')
	);
		
	if ($userDetails = get_userdata($userID))
	{
		// Link back to user's personal summary
		printf('<a href="%s&user_id=%d" class="button-secondary">%s</a>&nbsp;&nbsp;', 
			admin_url('users.php?page=WPCW_showPage_UserProgess'),
			$userDetails->ID,
			sprintf(__('&laquo; Return to <b>%s\'s</b> Progress Report', 'wp_courseware'), $userDetails->display_name)
		);		
	}
	
			
	
	// Try to get the full detailed results.
	$results = WPCW_quizzes_getUserResultsForQuiz($userID, $unitID, $quizID);
	
	// No results, so abort.
	if (!$results) 	
	{
		// Close the button wrapper for above early
		printf('</div>'); // .wpcw_button_group
		
		$page->showMessage(__('Sorry, but no results could be found.', 'wp_courseware'), true);
		$page->showPageFooter();
		return;
	}
		
	// Could potentially have an issue where the quiz has been deleted
	// but the data exists.. small chance though.
	$quizDetails = WPCW_quizzes_getQuizDetails($quizID, true, true, $userID);
	
	// Extra button - return to gradebook
	printf('<a href="%s&course_id=%d" class="button-secondary">%s</a>&nbsp;&nbsp;', 
		admin_url('admin.php?page=WPCW_showPage_GradeBook'), $quizDetails->parent_course_id,
		__("&laquo; Return to Gradebook", 'wp_courseware')
	);
	
	printf('</div>'); // .wpcw_button_group
	
	
	// #### 1 - Handle grades being updated
	$results = WPCW_showPage_UserProgess_quizAnswers_handingGrading($quizDetails, $results, $page, $userID, $unitID);
		
	// #### 2A - Check if next action for user has been triggered by the admin. 
	$results = WPCW_showPage_UserProgess_quizAnswers_whatsNext_savePreferences($quizDetails, $results, $page, $userID, $unitID);
	
	// #### 2B - Handle telling admin what's next	
	WPCW_showPage_UserProgess_quizAnswers_whatsNext($quizDetails, $results, $page, $userID, $unitID);
	
	//Ê#### 3 - Handle sending emails if something has changed.
	if (isset($results->sendOutEmails) && $results->sendOutEmails)
	{
		$extraDetail = (isset($results->extraEmailDetail) ? $results->extraEmailDetail : '');
		
		// Only called if the quiz was graded.
		if (isset($results->quiz_has_just_been_graded) && $results->quiz_has_just_been_graded)
		{
			// Need to call the action anyway, but any functions hanging off this
			// should check if the admin wants users to have notifications or not.
			do_action('wpcw_quiz_graded', $userID, $quizDetails, number_format($results->quiz_grade, 1), $extraDetail);	
		}
		
		$courseDetails = WPCW_courses_getCourseDetails($quizDetails->parent_course_id);
		if ($courseDetails->email_quiz_grade_option == 'send_email')
		{
			// Message is only if quiz has been graded.
			if (isset($results->quiz_has_just_been_graded) && $results->quiz_has_just_been_graded) 	
			{
				$page->showMessage(__('The user has been sent an email with their grade for this course.', 'wp_courseware'));
			}
		}
	}
	
	
	// #### - Table 1 - Overview
	printf('<h3>%s</h3>', __('Quiz/Survey Overview', 'wp_courseware'));
	
	$tbl = new TableBuilder();
	$tbl->attributes = array(
		'id' 	=> 'wpcw_tbl_progress_quiz_info',
		'class'	=> 'widefat wpcw_tbl'
	);
		
	$tblCol = new TableColumn(false, 'quiz_label');
	$tblCol->cellClass = 'wpcw_tbl_label';		
	$tbl->addColumn($tblCol);
	
	$tblCol = new TableColumn(false, 'quiz_detail');		
	$tbl->addColumn($tblCol);
	
	// These are the base details for the quiz to show.
	$summaryData = array(
		__('Quiz Title', 'wp_courseware')					=> $quizDetails->quiz_title,
		__('Quiz Description', 'wp_courseware')				=> $quizDetails->quiz_desc,
		__('Quiz Type', 'wp_courseware')					=> WPCW_quizzes_getQuizTypeName($quizDetails->quiz_type),
		__('No. of Questions', 'wp_courseware') 			=> $results->quiz_question_total,
		 
		__('Completed Date', 'wp_courseware') 				=>
				__('About', 'wp_courseware') . ' ' . human_time_diff($results->quiz_completed_date_ts) . ' ' . __('ago', 'wp_courseware') . 
				'<br/><small>(' . date('D jS M Y \a\t H:i:s', $results->quiz_completed_date_ts) . ')</small>',
				
		__('Number of Quiz Attempts', 'wp_courseware')			=> $results->attempt_count,
		__('Permitted Quiz Attempts', 'wp_courseware')		=> (-1 == $quizDetails->quiz_attempts_allowed ? __('Unlimited', 'wp_courseware') : $quizDetails->quiz_attempts_allowed)
	);
	
	
	// Quiz details relating to score, etc.
	if ('survey' != $quizDetails->quiz_type)
	{	
		$summaryData[__('Pass Mark', 'wp_courseware')]		= $quizDetails->quiz_pass_mark . '%';				
		
		// Still got items to grade
		if ($results->quiz_needs_marking > 0)
		{
			$summaryData[__('No. of Questions to Grade', 'wp_courseware')] = '<span class="wpcw_status_info wpcw_icon_pending">' .$results->quiz_needs_marking . '</span>';
			$summaryData[__('Overall Grade', 'wp_courseware')]	= '<span class="wpcw_status_info wpcw_icon_pending">' . __('Awaiting Final Grading', 'wp_courseware') . '</span>';
		}
		else
		{
			$summaryData[__('No. of Question to Grade', 'wp_courseware')] = '-';
			
			// Show if PASSED or FAILED with the overall grade.
			$gradeData = false;
			if ($results->quiz_grade >= $quizDetails->quiz_pass_mark) 
			{
				$gradeData = sprintf('<span class="wpcw_tbl_progress_quiz_overall wpcw_question_yesno_status wpcw_question_yes">%s%% %s</span>', number_format($results->quiz_grade, 1), __('Passed', 'wp_courseware'));
			}
			else {
				$gradeData = sprintf('<span class="wpcw_tbl_progress_quiz_overall wpcw_question_yesno_status wpcw_question_no">%s%% %s</span>', number_format($results->quiz_grade, 1), __('Failed', 'wp_courseware'));
			}
			
			$summaryData[__('Overall Grade', 'wp_courseware')]	= $gradeData;
		}
	}
	
	
	foreach ($summaryData as $label => $data)
	{
		$tbl->addRow(array(
			'quiz_label' => $label . ':',
			'quiz_detail' => $data,
		)); 
	}
	
	echo $tbl->toString();
	

	// ### 4 - Form Code - to allow instructor to send data back to 
	printf('<form method="POST" id="wpcw_tbl_progress_quiz_grading_form">');
	printf('<input type="hidden" name="grade_answers_submitted" value="true">');  
	
	// ### 5 - Table 2 - Each Specific Quiz
	$questionNumber = 0;
	if ($results->quiz_data && count($results->quiz_data) > 0)
	{
		foreach ($results->quiz_data as $questionID => $answer)
		{
			$data = $answer;			
			
			// Get the question type
			if (isset($quizDetails->questions[$questionID]))
			{
				// Store as object for easy reference.
				$quObj = $quizDetails->questions[$questionID];
				
				// Render the question as a table.
				printf('<h3>%s #%d - %s</h3>', __('Question', 'wp_courseware'), ++$questionNumber, $quObj->question_question);

				$tbl = new TableBuilder();
				$tbl->attributes = array(
					'id' 	=> 'wpcw_tbl_progress_quiz_info',
					'class'	=> 'widefat wpcw_tbl wpcw_tbl_progress_quiz_answers_'. $quObj->question_type // Add question type to table class, for good measure!
				);
					
				$tblCol = new TableColumn(false, 'quiz_label');
				$tblCol->cellClass = 'wpcw_tbl_label';		
				$tbl->addColumn($tblCol);
				
				$tblCol = new TableColumn(false, 'quiz_detail');		
				$tbl->addColumn($tblCol);
				
				$theirAnswer = false;
				switch ($quObj->question_type)
				{
					case 'truefalse':
					case 'multi':
						$theirAnswer = $answer['their_answer'];
					break;
					
					// File Upload - create a download link
					case 'upload':
						$theirAnswer = sprintf('<a href="%s%s" target="_blank" class="button-primary">%s .%s %s (%s)</a>', 
							WP_CONTENT_URL, $answer['their_answer'],
							__('Open', 'wp_courseware'),
							pathinfo($answer['their_answer'], PATHINFO_EXTENSION),
							__('File', 'wp_courseware'), 
							WPCW_files_getFileSize_human($answer['their_answer'])								
						);
					break;
					
					// Open Ended - Wrap in span tags, to cap the size of the field, and format new lines.
					case 'open': 
						$theirAnswer = '<span class="wpcw_q_answer_open_wrap"><textarea readonly>'. $data['their_answer'] .'</textarea></span>'; 
					break;
				} // end of $theirAnswer check
				
				
				$summaryData = array(
					// Quiz Type - Work out the label for the quiz type
					__('Type', 'wp_courseware')	=> array(
							'data' 		=> WPCW_quizzes_getQuestionTypeName($quObj->question_type), 
							'cssclass' 	=> ''
					),
					
					__('Their Answer', 'wp_courseware')	=> array(
							'data' 		=> $theirAnswer, 
							'cssclass' 	=> ''
					),
				);
				
				
				// Just for quizzes - show answers/grade
				if ('survey' != $quizDetails->quiz_type)
				{
					switch ($quObj->question_type)
					{
						case 'truefalse':
						case 'multi':
							// The right answer...
							$summaryData[__('Correct Answer', 'wp_courseware')] = array(
								'data' 		=> $answer['correct'],
								'cssclass' 	=> ''
							); 
							
							// Did they get it right?
							$getItRight = sprintf('<span class="wpcw_question_yesno_status wpcw_question_%s">%s</span>', $answer['got_right'], 
								('yes' == $answer['got_right'] ? __('Yes', 'wp_courseware') : __('No', 'wp_courseware'))
							);
								
							$summaryData[__('Did they get it right?', 'wp_courseware')] = array(
								'data' 		=> $getItRight,
								'cssclass'	=> ''
							);
						break;
						
						case 'upload':
						case 'open':
								$gradeHTML = false;
								$theirGrade = WPCW_arrays_getValue($answer, 'their_grade');
							
								// Not graded - show select box.
								if ($theirGrade == 0) 
								{
									$cssClass = 'wpcw_grade_needs_grading';
								}
								
								// Graded - Show click-to-edit link
								else 
								{
									$cssClass = 'wpcw_grade_already_graded';									
									$gradeHTML = sprintf('<span class="wpcw_grade_view">%d%% <a href="#">(%s)</a></span>', $theirGrade, __('Click to edit', 'wp_courseware'));
								}
								
								// Not graded yet, allow admin to grade the quiz, or change
								// the grading later if they want to.							
								$gradeHTML .= WPCW_forms_createDropdown(
									'grade_quiz_' . $quObj->question_id, 
									WPCW_quizzes_getPercentageList(__('-- Select a grade --', 'wp_courseware')),
									$theirGrade,
									false,
									'wpcw_tbl_progress_quiz_answers_grade'
								);
								
								
								$summaryData[__('Their Grade', 'wp_courseware')] = array(
									'data' 		=> $gradeHTML,
									'cssclass'	=> $cssClass
								);								
							break;
					}
				} // Check of showing the right answer.		
					
				
				foreach ($summaryData as $label => $data)
				{
					$tbl->addRow(array(
						'quiz_label' => $label . ':',
						'quiz_detail' => $data['data'],
					), $data['cssclass']); 
				}
				
				echo $tbl->toString();

			} // end if (isset($quizDetails->questions[$questionID]))
		} // foreach ($results->quiz_data as $questionID => $answer)
	}
	
	
	printf('</form>');
	
	// Shows a bar that pops up, allowing the user to easily save all grades that have changed.
	?>
	<div id="wpcw_sticky_bar" style="display: none">
		<div id="wpcw_sticky_bar_inner">
			<a href="#" id="wpcw_tbl_progress_quiz_grading_updated" class="button-primary"><?php _e('Save Changes to Grades', 'wp_courseware'); ?></a>
			<span id="wpcw_sticky_bar_status" title="<?php _e('Grades have been changed. Ready to save changes?', 'wp_courseware'); ?>"></span>
		</div>
	</div>
	<br/><br/><br/><br/>
	<?php 
		
	$page->showPageFooter();
}


/**
 * Handles the grading of the quiz questions.
 */
function WPCW_showPage_UserProgess_quizAnswers_handingGrading($quizDetails, $results, $page, $userID, $unitID)
{
	if (isset($_POST['grade_answers_submitted']) && 'true' == $_POST['grade_answers_submitted'])
	{
		$listOfQuestionsToMark = $results->quiz_needs_marking_list;
		
		// Switch array so values become keys.		
		if (!empty($listOfQuestionsToMark)) {
			$listOfQuestionsToMark = array_flip($listOfQuestionsToMark);
		} 
		// Ensure we always have a valid array
		else {
			$listOfQuestionsToMark = array();
		}
		
		
		// Check $_POST keys for the graded results.
		foreach ($_POST as $key => $value)
		{
			// Check that we have a question ID and a matching grade for the quiz. Only want grades that are greater than 0.
			if (preg_match('/^grade_quiz_([0-9]+)$/', $key, $keyMatches) && preg_match('/^[0-9]+$/', $value) && $value > 0)
			{
				$questionID = $keyMatches[1];
				
				// Remove from list to be marked, if found
				unset($listOfQuestionsToMark[$questionID]);

				// Add the grade information to the quiz
				if (isset($results->quiz_data[$questionID])) 
				{
					$results->quiz_data[$questionID]['their_grade'] = $value;
				}				
			}
		}
		
		// Update the database with the list of questions to mark, plus the updated quiz grading information.
		// Return to a simple list again, hence using array flip (ID => index) becomes (index => ID) 
		$results->quiz_needs_marking_list = array_flip($listOfQuestionsToMark); 
		
		// Update the results in the database.
		WPCW_quizzes_updateQuizResults($results);
		
		// Success message
		$page->showMessage(__('Grades have been successfully updated for this user.', 'wp_courseware'));
		
		
		// Refresh the results - now that we've made changes
		$results = WPCW_quizzes_getUserResultsForQuiz($userID, $unitID, $quizDetails->quiz_id);
		
		// All items are marked, so email user, and tell admin that user has been notified.
		if ($results->quiz_needs_marking == 0)
		{
			// Send out email only if not a blocking test, or blocking and passed.
			if ('quiz_block' == $quizDetails->quiz_type && $results->quiz_grade < $quizDetails->quiz_pass_mark) {
				$results->sendOutEmails = false;
			} else {
				$results->sendOutEmails = true;
			}
			
			// Check if the user has passed or not to indicate what to do next.
			if ($results->quiz_grade >= $quizDetails->quiz_pass_mark) 
			{
				// Just a little note to mark as complete
				$results->extraEmailDetail = __('You have passed the quiz.', 'wp_courseware');
				
				printf('<div id="message" class="wpcw_msg wpcw_msg_success">%s</span></div>', 
					__('The user has <b>PASSED</b> this quiz, and the associated unit has been marked as complete.', 'wp_courseware')
				);
				
				WPCW_units_saveUserProgress_Complete($userID, $unitID);
				
				// Unit complete, check if course/module is complete too.
				do_action('wpcw_user_completed_unit', $userID, $unitID, WPCW_units_getAssociatedParentData($unitID));
			} 
		}
		
		// Set flag that the quiz has just literally been graded for use in code around this.
		// Doing this after the results have been updated above.
		$results->quiz_has_just_been_graded = true;
	}

	return $results;
}


/**
 * Function that shows details to the admin telling them what to do next.
 */
function WPCW_showPage_UserProgess_quizAnswers_whatsNext($quizDetails, $results, $page, $userID, $unitID)
{
	// Tell admin still questions that need marking
	if ($results->quiz_needs_marking > 0)
	{
		printf('<div id="message" class="wpcw_msg wpcw_msg_info"><span class="wpcw_icon_pending"><b>%s</b></span></div>', 
			__('This quiz has questions that need grading.', 'wp_courseware')
		);
	}
	else  
	{
		// Show the form only if the quiz is blocking and they've failed. 
		if ('quiz_block' == $quizDetails->quiz_type && $results->quiz_grade < $quizDetails->quiz_pass_mark)
		{		
			$showAdminProgressForm = true;
			$showAdminMessageCustom = false;
			
			// Show admin which method was selected.
			if ($results->quiz_next_step_type)
			{
				switch ($results->quiz_next_step_type)
				{
					case 'progress_anyway':
						printf('<div id="message" class="wpcw_msg wpcw_msg_info">%s</span></div>', 
								__('You have allowed the user to <b>progress anyway</b>, despite failing the quiz.', 'wp_courseware')
							);
						$showAdminProgressForm = false;
						break;
						
					case 'retake_quiz':
						printf('<div id="message" class="wpcw_msg wpcw_msg_info">%s</span></div>', 
								__('You have requested that the user <b>re-takes the quiz</b>.', 'wp_courseware')
							);
						$showAdminProgressForm = false;
						break;
						
					case 'retake_waiting':
						printf('<div id="message" class="wpcw_msg wpcw_msg_info">%s</span></div>', 
								__('The user has requested a retake, but they have not yet completed the quiz.', 'wp_courseware')
							);
						$showAdminProgressForm = false;
						break;
						
					case 'quiz_fail_no_retakes':
						$showAdminMessageCustom = __('The user has <b>exhausted all of their retakes</b>.', 'wp_courseware');
						$showAdminProgressForm = true;
						break;
						
						
				}
			}
			
			// Next step has not been specified, allow the admin to choose one.
			if ($showAdminProgressForm) 
			{
				printf('<div class="wpcw_user_progress_failed"><form method="POST">');
				
				// Show the main message or a custom message from above.
				printf('<div id="message" class="wpcw_msg wpcw_msg_error">%s %s</span></div>', 
					$showAdminMessageCustom,  __('Since this is a <b>blocking quiz</b>, and the user has <b>failed</b>, what would you like to do?', 'wp_courseware')
				);
				
				printf('
					<div class="wpcw_user_progress_failed_next_action">
						<label><input type="radio" name="wpcw_user_continue_action" class="wpcw_next_action_progress_anyway" value="progress_anyway" checked="checked" /> <span><b>%s</b> %s</span></label><br/>
						<label><input type="radio" name="wpcw_user_continue_action" class="wpcw_next_action_retake_quiz" value="retake_quiz" /> <span><b>%s</b> %s</span></label>
					</div>
					
					<div class="wpcw_user_progress_failed_reason" style="display: none;">
						<label><b>%s</b></label><br/>
						<textarea name="wpcw_user_progress_failed_reason"></textarea><br/>
						<small>%s</small>
					</div>
					
					<div class="wpcw_user_progress_failed_btns">
						<input type="submit" name="failed_quiz_next_action" value="%s" class="button-primary" />
					</div>
				', 
					__('Allow the user to continue anyway.', 'wp_courseware'),
					__(' (User is emailed saying they can continue)', 'wp_courseware'),
					__('Require the user to re-take the quiz.', 'wp_courseware'),
					__(' (User is emailed saying they need to re-take the quiz)', 'wp_courseware'),
					__('Custom Message:', 'wp_courseware'),
					__('Custom message for the user that\'s sent to the user when asking them to retake the quiz.', 'wp_courseware'),
					__('Save Preference', 'wp_courseware')
				);
				
				printf('</form></div>'); 
			}
		}
	}
}



/**
 * Handles saving what the admin wants to do for the user next.
 */
function WPCW_showPage_UserProgess_quizAnswers_whatsNext_savePreferences($quizDetails, $results, $page, $userID, $unitID)
{
	// Admin wants to save the next action to this progress.
	if (isset($_POST['failed_quiz_next_action']) && $_POST['failed_quiz_next_action'])
	{
		global $wpdb, $wpcwdb;
		$wpdb->show_errors();
		
		$userNextAction = WPCW_arrays_getValue($_POST, 'wpcw_user_continue_action');
		$userRetakeMsg = filter_var(WPCW_arrays_getValue($_POST, 'wpcw_user_progress_failed_reason'), FILTER_SANITIZE_STRING);
		
		// Check action is valid. Abort if not
		if (!in_array($userNextAction, array('retake_quiz', 'progress_anyway'))) {
			return $results;
		}
		
		// Sort out the SQL statement for what to update
		switch ($userNextAction)
		{
			// User needs to retake the course.
			case 'retake_quiz':
				break;
				
			// User is allowed to progress
			case 'progress_anyway':
					$userRetakeMsg = false;
					
					// Mark the unit as completed.
					WPCW_units_saveUserProgress_Complete($userID, $unitID);
					
					// Unit complete, check if course/module is complete too.
					do_action('wpcw_user_completed_unit', $userID, $unitID, WPCW_units_getAssociatedParentData($unitID));
				break;
		}
		
		// Update the progress item
		$SQL = $wpdb->prepare("
		    	UPDATE $wpcwdb->user_progress_quiz
		    	  SET quiz_next_step_type = '%s', 
		    	      quiz_next_step_msg = %s  
		    	WHERE user_id = %d 
		    	  AND unit_id = %d 
		    	  AND quiz_id = %d
		    	ORDER BY quiz_attempt_id DESC
		    	LIMIT 1
	   		", 
				$userNextAction, $userRetakeMsg,
				$userID, $unitID, $quizDetails->quiz_id
			);
			 
		$wpdb->query($SQL);		
		
		// Need to update the results object for use later.
		$results->quiz_next_step_type = $userNextAction;
		$results->quiz_next_step_msg = $userRetakeMsg;
							
		
		switch ($userNextAction)
		{
			// User needs to retake the course.
			case 'retake_quiz':
					$results->extraEmailDetail = __('Since you didn\'t pass the quiz, the instructor has asked that you re-take this quiz.', 'wp_courseware');
					if ($userRetakeMsg) { 
						$results->extraEmailDetail .= "\n\n" . 	$userRetakeMsg;
					}
				break;
				
			// User is allowed to progress
			case 'progress_anyway':
					$results->extraEmailDetail = __('Although you didn\'t pass the quiz, the instructor is allowing you to continue on to the next unit.', 'wp_courseware');
					
					// Mark the unit as completed.
					WPCW_units_saveUserProgress_Complete($userID, $unitID);
					
					// Unit complete, check if course/module is complete too.
					do_action('wpcw_user_completed_unit', $userID, $unitID, WPCW_units_getAssociatedParentData($unitID));
				break;
		}
		
    	// Tell code to send out emails
		$results->sendOutEmails = true;		
	}
	
	return $results;
}



?>