<?php
/**
 * WP Courseware
 * 
 * Functions relating to showing the gradebook page.
 */


/**
 * Gradebook View - show the grade details for the users of the system. 
 */
function WPCW_showPage_GradeBook_load()
{
	$page = new PageBuilder(false);
	
	$courseDetails = false;
	$courseID = false;
	
	// Trying to view a specific course	
	$courseDetails = false;
	if (isset($_GET['course_id'])) 
	{
		$courseID 		= $_GET['course_id'] + 0;
		$courseDetails 	= WPCW_courses_getCourseDetails($courseID);
	}
	
	// Abort if course not found.
	if (!$courseDetails)
	{		
		$page->showPageHeader(__('GradeBook', 'wp_courseware'), '75%', WPCW_icon_getPageIconURL());
		$page->showMessage(__('Sorry, but that course could not be found.', 'wp_courseware'), true);
		$page->showPageFooter();
		return;
	}
	
	// Show title of this course
	$page->showPageHeader(__('GradeBook', 'wp_courseware') . ': ' . $courseDetails->course_title, '75%', WPCW_icon_getPageIconURL());
	
	global $wpcwdb, $wpdb;
	$wpdb->show_errors();
	
	// Need a list of all quizzes for this course, excluding surveys.
	$quizzesForCourse = WPCW_quizzes_getAllQuizzesForCourse($courseDetails->course_id);
	
	
	// Handle situation when there are no quizzes.
	if (!$quizzesForCourse) {
		$page->showMessage(__('There are no quizzes for this course, therefore no grade information to show.', 'wp_courseware'), true);
		$page->showPageFooter();
		return;
	}
	
	// Create a simple list of IDs to use in SQL queries
	$quizIDList = array();
	foreach ($quizzesForCourse as $singleQuiz)  {
		$quizIDList[] = $singleQuiz->quiz_id;
	}
	
	// Convert list of IDs into an SQL list
	$quizIDListForSQL = '(' . implode(',', $quizIDList) . ')';
	
	// Do we want certificates?
	$usingCertificates = ('use_certs' == $courseDetails->course_opt_use_certificate);
	
	
	// #### Handle checking if we're sending out any emails to users with their final grades
	// Called here so that any changes are reflected in the table using the code below.
	if ('email_grades' == WPCW_arrays_getValue($_GET, 'action')) {
		WPCW_showPage_GradeBook_handleFinalGradesEmail($courseDetails, $page);
	}
	
	// Get the requested page number
	$paging_pageWanted = WPCW_arrays_getValue($_GET, 'pagenum') + 0;
	if ($paging_pageWanted == 0) {
		$paging_pageWanted = 1;
	}
	
	// Need a count of how many there are to mark anyway, hence doing calculation.
	// Using COUNT DISTINCT so that we get a total of the different user IDs.
	// If we use GROUP BY, we end up with several rows of results.
	$userCount_toMark = $wpdb->get_var("
		SELECT COUNT(DISTINCT upq.user_id) AS user_count 
		FROM $wpcwdb->user_progress_quiz upq		
			LEFT JOIN $wpdb->users u ON u.ID = upq.user_id											
		WHERE upq.quiz_id IN $quizIDListForSQL
		  AND upq.quiz_needs_marking > 0
		  AND u.ID IS NOT NULL
		  AND quiz_is_latest = 'latest'
		");
	
	// Count - all users for this course
	$userCount_all = $wpdb->get_var($wpdb->prepare("
		SELECT COUNT(*) AS user_count 
		FROM $wpcwdb->user_courses uc									
		LEFT JOIN $wpdb->users u ON u.ID = uc.user_id
		WHERE uc.course_id = %d
		  AND u.ID IS NOT NULL
		", $courseDetails->course_id));	
	
	// Count - users who have completed the course.	
	$userCount_completed = $wpdb->get_var($wpdb->prepare("
		SELECT COUNT(*) AS user_count 
		FROM $wpcwdb->user_courses uc									
		LEFT JOIN $wpdb->users u ON u.ID = uc.user_id
		WHERE uc.course_id = %d
		  AND u.ID IS NOT NULL
		  AND uc.course_progress = 100
		", $courseDetails->course_id));
	
	// Count - all users that need their final grade.
	$userCount_needGrade = $wpdb->get_var($wpdb->prepare("
		SELECT COUNT(*) AS user_count 
		FROM $wpcwdb->user_courses uc									
		LEFT JOIN $wpdb->users u ON u.ID = uc.user_id
		WHERE uc.course_id = %d
		  AND u.ID IS NOT NULL
		  AND uc.course_progress = 100
		  AND uc.course_final_grade_sent != 'sent'
		", $courseDetails->course_id));

	
	// SQL Code used by filters below
	$coreSQL_allUsers = $wpdb->prepare("
			SELECT * 
			FROM $wpcwdb->user_courses uc									
				LEFT JOIN $wpdb->users u ON u.ID = uc.user_id
			WHERE uc.course_id = %d
			  AND u.ID IS NOT NULL			
			", $courseDetails->course_id);
	
	
	// The currently selected filter to determine what quizzes to show.
	$currentFilter = WPCW_arrays_getValue($_GET, 'filter');
	switch ($currentFilter)
	{
		case 'to_mark':
			// Chooses all progress where there are questions that need grading.
			// Then group by user, so that we don't show the same user twice.
			// Not added join for certificates, since they can't be complete
			// if they've got stuff to be marked.
			$coreSQL = "
				SELECT * 
				FROM $wpcwdb->user_progress_quiz upq									
					LEFT JOIN $wpdb->users u ON u.ID = upq.user_id					
					LEFT JOIN $wpcwdb->user_courses uc ON uc.user_id = upq.user_id
				WHERE upq.quiz_id IN $quizIDListForSQL
				  AND upq.quiz_needs_marking > 0
				  AND u.ID IS NOT NULL
				  AND quiz_is_latest = 'latest'
				GROUP BY u.ID								  
				";  
			
				// No need to re-calculate, just re-use the number.
				$paging_totalCount = $userCount_toMark;
			break;
			
			
		// Completed the course
		case 'completed':
				// Same SQL as all users, but just filtering those with a progress of 100.
				$coreSQL = $coreSQL_allUsers ." 
					AND uc.course_progress = 100
				";
			
				// The total number of results to show - used for paging
				$paging_totalCount = $userCount_completed;
			break;
			
		// Completed the course
		case 'eligible_for_final_grade':
				// Same SQL as all users, but just filtering those with a progress of 100 AND
				// needing a final grade due to flag in course_progress.
				$coreSQL = $coreSQL_allUsers ." 
					AND uc.course_progress = 100 
					AND course_final_grade_sent != 'sent'
				";
			
				// The total number of results to show - used for paging
				$paging_totalCount = $userCount_needGrade;
			break;
			
		// Default to all users, regardless of what progress they've made
		default:
				$currentFilter = 'all';
							
				// Allow the query to be modified by other plugins
				$coreSQL_filteredUsers = apply_filters("wpcw_back_query_filter_gradebook_users", $coreSQL_allUsers, $courseDetails->course_id);

				// Select all users that exist for this course
				$coreSQL = $coreSQL_filteredUsers;

				// The total number of results to show - used for paging
				$paging_totalCount = $userCount_all;
			break;
	}
	
	
	// Generate page URL
	$summaryPageURL = admin_url('admin.php?page=WPCW_showPage_GradeBook&course_id=' . $courseDetails->course_id);
	
	$paging_resultsPerPage  = 50; 	
	$paging_recordStart 	= (($paging_pageWanted-1) * $paging_resultsPerPage) + 1;
	$paging_recordEnd 		= ($paging_pageWanted * $paging_resultsPerPage);
	$paging_pageCount 		= ceil($paging_totalCount/$paging_resultsPerPage);	
	$paging_sqlStart		= $paging_recordStart - 1;
		
	// Use the main SQL from above, but limit it and order by user's name. 
	$SQL = "$coreSQL
			ORDER BY display_name ASC
			LIMIT $paging_sqlStart, $paging_resultsPerPage";
			
	// Generate paging code
	$baseURL = WPCW_urls_getURLWithParams($summaryPageURL, 'pagenum')."&pagenum=";
	$paging = WPCW_tables_showPagination($baseURL, $paging_pageWanted, $paging_pageCount, $paging_totalCount, $paging_recordStart, $paging_recordEnd);

		
	$tbl = new TableBuilder();
	$tbl->attributes = array(
		'id' 	=> 'wpcw_tbl_quiz_gradebook',
		'class'	=> 'widefat wpcw_tbl'
	);
			
	$tblCol = new TableColumn(__('Learner Details', 'wp_courseware'), 'learner_details');
	$tblCol->cellClass = "wpcw_learner_details";
	$tbl->addColumn($tblCol);
			
	// ### Add the quiz data
	if ($quizzesForCourse)
	{
		// Show the overall progress for the course.
		$tblCol = new TableColumn(__('Overall Progress', 'wp_courseware'), 'course_progress');
		//$tblCol->headerClass = "wpcw_center";
		$tblCol->cellClass = "wpcw_grade_course_progress";
		$tbl->addColumn($tblCol);
		
		
		// ### Create heading for cumulative data.
		$tblCol = new TableColumn(__('Cumulative Grade', 'wp_courseware'), 'quiz_cumulative');
		$tblCol->headerClass = "wpcw_center";
		$tblCol->cellClass = "wpcw_grade_summary wpcw_center";
		$tbl->addColumn($tblCol);
		
		// ### Create heading for cumulative data.
		$tblCol = new TableColumn(__('Grade Sent?', 'wp_courseware'), 'grade_sent');
		$tblCol->headerClass = "wpcw_center";
		$tblCol->cellClass = "wpcw_grade_summary wpcw_center";
		$tbl->addColumn($tblCol);
		
		// ### Create heading for cumulative data.
		if ($usingCertificates)
		{
			$tblCol = new TableColumn(__('Certificate Available?', 'wp_courseware'), 'certificate_available');
			$tblCol->headerClass = "wpcw_center";
			$tblCol->cellClass = "wpcw_grade_summary wpcw_center";
			$tbl->addColumn($tblCol);
		}
		
		
		// ### Add main quiz scores
		foreach ($quizzesForCourse as $singleQuiz)
		{
			$tblCol = new TableColumn($singleQuiz->quiz_title, 'quiz_' . $singleQuiz->quiz_id);
			$tblCol->cellClass = "wpcw_center wpcw_quiz_grade";
			$tblCol->headerClass = "wpcw_center wpcw_quiz_grade";
			$tbl->addColumn($tblCol);
		}			
	}
	
	$urlForQuizResultDetails = admin_url('users.php?page=WPCW_showPage_UserProgess_quizAnswers');
			
	
	$userList = $wpdb->get_results($SQL);
	if (!$userList)
	{
		switch ($currentFilter)
		{
			case 'to_mark':
				$msg = __('There are currently no quizzes that need a manual grade.', 'wp_courseware');				
				break;
				
			case 'eligible_for_final_grade':
				$msg = __('There are currently no users that are eligible to receive their final grade.', 'wp_courseware');				
				break;
				
			case 'completed':
				$msg = __('There are currently no users that have completed the course.', 'wp_courseware');				
				break;
				
			default:
				$msg = __('There are currently no learners allocated to this course.', 'wp_courseware');
				break;
		}
		
		// Create spanning item with message - number of quizzes + fixed columns.
		$rowDataObj = new RowDataSimple('wpcw_no_users wpcw_center', $msg, count($quizIDList) + 5);
		$tbl->addRowObj($rowDataObj);
	}
	
	// We've got some users to show.
	else {
		
		// ### Format main row data and show it.
		$odd = false;
		foreach ($userList as $singleUser)
		{
			$data = array();
			
			// Basic Details with avatar
			$data['learner_details'] = sprintf('
				%s
				<span class="wpcw_col_cell_name">%s</span>
				<span class="wpcw_col_cell_username">%s</span>
				<span class="wpcw_col_cell_email"><a href="mailto:%s" target="_blank">%s</a></span></span>
			', 
				get_avatar($singleUser->ID, 48),
				$singleUser->display_name, 
				$singleUser->user_login, 
				$singleUser->user_email, $singleUser->user_email
			);	
	
			// Get the user's progress for the quizzes.
			if ($quizzesForCourse)
			{
				$quizResults = WPCW_quizzes_getQuizResultsForUser($singleUser->ID, $quizIDListForSQL);
				
				// Track cumulative data
				$quizScoresSoFar = 0;
				$quizScoresSoFar_count = 0;
				
				
				// ### Now render results for each quiz
				foreach ($quizIDList as $aQuizID)
				{
					// Got progress data, process the result
					if (isset($quizResults[$aQuizID])) 
					{
						// Extract results and unserialise the data array.
						$theResults = $quizResults[$aQuizID];
						$theResults->quiz_data = maybe_unserialize($theResults->quiz_data);
						
						
						$quizDetailURL = sprintf('%s&user_id=%d&quiz_id=%d&unit_id=%d', $urlForQuizResultDetails, $singleUser->ID, $theResults->quiz_id, $theResults->unit_id);
						
						// We've got something that needs grading. So render link to where the quiz can be graded.
						if ($theResults->quiz_needs_marking > 0)
						{
							$data['quiz_' . $aQuizID] = sprintf('<span class="wpcw_grade_needs_grading"><a href="%s">%s</span>', $quizDetailURL, __('Manual Grade Required', 'wp_courseware'));
						}
						
						// User is blocked - they've failed and are blocked
						else if ('quiz_fail_no_retakes' == $theResults->quiz_next_step_type)
						{
							$data['quiz_' . $aQuizID] = sprintf('<span class="wpcw_grade_needs_grading"><a href="%s">%s</span>', $quizDetailURL, __('Quiz Retakes Exhausted', 'wp_courseware'));
						}
						
						// Quiz not yet complete...
						else if ('incomplete' == $theResults->quiz_paging_status)
						{
							$data['quiz_' . $aQuizID] = '<span class="wpcw_grade_not_taken">' . __('In Progress', 'wp_courseware') . '</span>';
						}
						
						// No quizzes need marking, so show the scores as usual.
						else 
						{
							// Use grade for cumulative grade
							$score = number_format($quizResults[$aQuizID]->quiz_grade, 1);
							$quizScoresSoFar += $score;
							$quizScoresSoFar_count++;
													
							// Render score and link to the full test data.
							$data['quiz_' . $aQuizID] = sprintf('<span class="wpcw_grade_valid"><a href="%s">%s%%</span>', $quizDetailURL, $score);
						}
					} 
					
					// No progress data - quiz not completed yet
					else {
						$data['quiz_' . $aQuizID] = '<span class="wpcw_grade_not_taken">' . __('Not Taken', 'wp_courseware') . '</span>';
					}
				}	
				
				
				// #### Show the cumulative quiz results.
				$data['quiz_cumulative'] = '-';
				if ($quizScoresSoFar_count > 0)
				{
					$data['quiz_cumulative'] = 	'<span class="wpcw_grade_valid">' . number_format(($quizScoresSoFar / $quizScoresSoFar_count), 1) . '%</span>';
				}				
			}
			
			// ####ÊUser Progress
			$data['course_progress'] = WPCW_stats_convertPercentageToBar($singleUser->course_progress);
			
			// #### Grade Sent?
			$data['grade_sent'] = ('sent' == $singleUser->course_final_grade_sent ? __('Yes', 'wp_courseware') : '-');
			
			
			// #### Certificate - Show if there's a certificate that can be downloaded.
			if ($usingCertificates && $certDetails = WPCW_certificate_getCertificateDetails($singleUser->ID, $courseDetails->course_id, false))
			{
				$data['certificate_available'] = sprintf('<a href="%s" title="%s">%s</a>',					 
					WPCW_certificate_generateLink($certDetails->cert_access_key), 
					__('Download the certificate for this user.', 'wp_courseware'),
					__('Yes', 'wp_courseware')
				);
			} 
			else {
				$data['certificate_available'] = '-';
			}
			
			// Odd/Even row colouring.
			$odd = !$odd;
			$tbl->addRow($data, ($odd ? 'alternate' : ''));
		}// single user
	} // Check we have some users.
			
	// Here are the action buttons for Gradebook.
	printf('<div class="wpcw_button_group">');
	
		// Button to generate a CSV of the gradebook. 
		printf('<a href="%s" class="button-primary">%s</a>&nbsp;&nbsp;', 
			admin_url('?wpcw_export=gradebook_csv&course_id=' . $courseDetails->course_id),
			__('Export Gradebook (CSV)', 'wp_courseware')
		);
		
		printf('<a href="%s" class="button-primary">%s</a>&nbsp;&nbsp;', 
			admin_url('admin.php?page=WPCW_showPage_GradeBook&action=email_grades&filter=all&course_id=' . $courseDetails->course_id),
			__('Email Final Grades', 'wp_courseware')
		);
		
		// URL that shows the eligible users who are next to get the email for the final grade.
		$eligibleURL = sprintf(admin_url('admin.php?page=WPCW_showPage_GradeBook&course_id=%d&filter=eligible_for_final_grade'), $courseDetails->course_id);

		// Create information about how people are chosen to send grades to.
		printf('<div id="wpcw_button_group_info_gradebook" class="wpcw_button_group_info">%s</div>',
			sprintf(__('Grades will only be emailed to students who have <b>completed the course</b> and who have <b>not yet received</b> their final grade. 
			   You can see the students who are <a href="%s">eligible to receive the final grade email</a> here.', 'wp_courseware'), $eligibleURL)
		);
	
	printf('</div>');		
	
	
	echo $paging;
	
	// Show the filtering to selectively show different quizzes
	// Filter list can be modified to indicate Group's name instead of 'all'
	$filters_list = array(
		'all' 						=> sprintf(__('All (%d)', 								'wp_courseware'), $userCount_all),
		'completed' 				=> sprintf(__('Completed (%d)', 						'wp_courseware'), $userCount_completed),
		'eligible_for_final_grade' 	=> sprintf(__('Eligible for Final Grade Email (%d)', 	'wp_courseware'), $userCount_needGrade),
		'to_mark' 					=> sprintf(__('Just Quizzes that Need Marking (%d)', 	'wp_courseware'), $userCount_toMark),
	);

	// Allow the filters to be customised
	$filters_list = apply_filters("wpcw_back_filters_gradebook_filters", $filters_list, $courseDetails->course_id);

	echo WPCW_table_showFilters($filters_list, WPCW_urls_getURLWithParams($summaryPageURL, 'filter')."&filter=", $currentFilter);
	
	// Finally show table		
	echo $tbl->toString();		
	echo $paging;		
	
	
	$page->showPageFooter();
}


/**
 * Handle sending out emails to users when they have completed the course and we're sending them their final grade.
 * 
 * @param Object $courseDetails The details of the course that we're sending details out for.
 * @param PageBuilder $page The page that's rendering the page structure.
 */
function WPCW_showPage_GradeBook_handleFinalGradesEmail($courseDetails, $page)
{
	// This could take a long time, hence setting time limit to unlimited.
	set_time_limit(0);
	
	global $wpdb, $wpcwdb;
	$wpdb->show_errors();
	
	// Get users to email final grades to
	$usersNeedGrades_SQL = $wpdb->prepare("
		SELECT * 
		FROM $wpcwdb->user_courses uc									
		LEFT JOIN $wpdb->users u ON u.ID = uc.user_id
		WHERE uc.course_id = %d
		  AND u.ID IS NOT NULL
		  AND uc.course_progress = 100
		  AND uc.course_final_grade_sent != 'sent'
		", $courseDetails->course_id);
	
	// Allow the list of users to email to be customised.
	$usersNeedGrades = $wpdb->get_results(apply_filters("wpcw_back_query_filter_gradebook_users_final_grades_email", $usersNeedGrades_SQL, $courseDetails));
	
	// Abort if there's nothing to do, showing a useful error message to the user.
	if (empty($usersNeedGrades))
	{	
		$page->showMessage(
			__('There are currently no users that are eligible to receive their final grade.', 'wp_courseware') . ' ' . 
			__('No emails have been sent.', 'wp_courseware'), 
			true);
		return;
	}
	
	$totalUserCount = count($usersNeedGrades);
	
	//WPCW_debug_showArray($courseDetails);
	
	
	// ### Email Template - Construct the from part of the email
	$headers = false; 
	if ($courseDetails->course_from_email) {
		$headers = sprintf('From: %s <%s>' . "\r\n", $courseDetails->course_from_name, $courseDetails->course_from_email);
	}
			
	
	// Start the status pane to wrap the updates.
	printf('<div id="wpcw_gradebook_email_progress">');
	
		// Little summary of how many users there are.
		printf('<h3>%s <b>%d %s</b>...</h3>', 
			__('Sending final grade emails to', 'wp_courseware'), $totalUserCount, _n('user', 'users', $totalUserCount, 'wp_courseware')
		);
		
		// Get all the quizzes for this course
		$quizIDList 		= array();
		$quizIDListForSQL 	= false;
		$quizzesForCourse 	= WPCW_quizzes_getAllQuizzesForCourse($courseDetails->course_id);
		
		// Create a simple list of IDs to use in SQL queries
		if ($quizzesForCourse)
		{		
			foreach ($quizzesForCourse as $singleQuiz)  {
				$quizIDList[$singleQuiz->quiz_id] = $singleQuiz;
			}
		
			// Convert list of IDs into an SQL list
			$quizIDListForSQL = '(' . implode(',', array_keys($quizIDList)) . ')'; 
		}
				
		
		// Run through each user, and generate their details.
		$userCount = 1;
		foreach ($usersNeedGrades as $aSingleUser)
		{
			printf('<p>%s (%s) - <b>%d%% %s</b></p>', 
				$aSingleUser->display_name, $aSingleUser->user_email,
				number_format(($userCount / $totalUserCount) * 100, 1),
				__('complete', 'wp_courseware')
			);
						
			// Work out what tags we have to replace in the body and subject and replace
			// the generic ones.
			$messageBody 		= $courseDetails->email_complete_course_grade_summary_body;
			$tagList_Body 		= WPCW_email_getTagList($messageBody);
			$messageBody     	= WPCW_email_replaceTags_generic($courseDetails, $aSingleUser, $tagList_Body, $messageBody);
			
			$messageSubject		= $courseDetails->email_complete_course_grade_summary_subject;
			$tagList_Subject 	= WPCW_email_getTagList($messageSubject);
			$messageSubject 	= WPCW_email_replaceTags_generic($courseDetails, $aSingleUser, $tagList_Subject, $messageSubject);
			
			
			
			// Generate the data for all of the quizzes, and add it to the email.
			$quizGradeMessage = "\n";
			
			// Only add quiz summary if we have one!
			if (!empty($quizIDList))
			{
				// Get quiz results for this user
				$quizResults = WPCW_quizzes_getQuizResultsForUser($aSingleUser->ID, $quizIDListForSQL);
				
				// Track cumulative data 
				$quizScoresSoFar = 0;
				$quizScoresSoFar_count = 0;
				
				// ### Now render results for each quiz
				foreach ($quizIDList as $aQuizID => $singleQuiz)
				{
					// Got progress data, process the result
					if (isset($quizResults[$aQuizID])) 
					{
						// Extract results and unserialise the data array.
						$theResults = $quizResults[$aQuizID];
						$theResults->quiz_data = maybe_unserialize($theResults->quiz_data);
						
						// We've got something that needs grading.
						if ($theResults->quiz_needs_marking == 0)
						{
							// Calculate score, and use for cumulative.
							$score = number_format($theResults->quiz_grade);
							$quizScoresSoFar += $score;		
							$quizScoresSoFar_count++;
														
							// Add to string with the quiz name and each grade.
							$quizGradeMessage .= sprintf("%s #%d - %s\n%s: %s%%\n\n",
								__('Quiz', 'wp_courseware'), $quizScoresSoFar_count,
								$singleQuiz->quiz_title,
								__('Grade', 'wp_courseware'), $score
							);
							
						}
					} // end of quiz result check. 
				}	
			} // end of check for quizzes for course
			
			// Calculate the cumulative grade
			$cumulativeGrade = ($quizScoresSoFar_count > 0 ? number_format(($quizScoresSoFar / $quizScoresSoFar_count), 1) . '%' : __('n/a', 'wp_courseware'));
			
			// Now replace the cumulative grades.
			$messageBody = str_ireplace('{QUIZ_SUMMARY}', trim($quizGradeMessage), $messageBody);
			$messageBody = str_ireplace('{CUMULATIVE_GRADE}', $cumulativeGrade, $messageBody);
						
			
			// Set up the target email address
			$targetEmail = $aSingleUser->user_email;
						
			// Send the actual email
			if (!wp_mail($targetEmail, $messageSubject, $messageBody, $headers)) {
				error_log('WPCW_email_sendEmail() - email did not send.');
			}
			
			
			// Update the user record to mark as being sent
		    $wpdb->query($wpdb->prepare("
		    	UPDATE $wpcwdb->user_courses
		    	   SET course_final_grade_sent = 'sent'
		    	WHERE user_id = %d
		    	  AND course_id = %d
		    ", $aSingleUser->ID, $courseDetails->course_id));
			
			flush();
			$userCount++;
		}	
		
		// Tell the user we're complete.
		printf('<h3>%s</h3>', __('All done.', 'wp_courseware'));
	
	printf('</div>');
}



?>