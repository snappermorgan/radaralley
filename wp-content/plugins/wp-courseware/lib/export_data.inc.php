<?php


/**
 * Sends the HTTP headers for CSV content that forces a download.
 * @param String $filenameToUse The filename to use.
 */
function WPCW_data_export_sendHeaders_CSV($filenameToUse)
{
	$debugMode = false;
	
	if (!$debugMode)
	{
		// Force the file to download
		header('Content-Type: application/csv');  
		header('Content-Disposition: attachment; filename="'. $filenameToUse . '"');
		header("Cache-Control: no-store, no-cache");
	} 
	else {
		// Enable below and disable header() calls above for debugging purposes.
		header('Content-Type: text/plain');
	}
}


/**
 * Function that checks to see if a data export has been triggered.
 */
function WPCW_data_handleDataExport()
{
	// Check for a generic trigger for an export.
	if (isset($_GET['wpcw_export']) && $exportType = $_GET['wpcw_export'])
	{
		// If user is not allowed to edit options, then redirect to home page
		if (!current_user_can('manage_options') ) { 
			wp_redirect(get_bloginfo('url'), 301);
			return; 
		}
		
		// Contains the data type => the function that generates it.
		$exportTypeList = array(
			'csv_import_user_sample'	=> 'WPCW_data_export_userImportSample',
			'csv_export_survey_data'	=> 'WPCW_data_export_quizSurveyData',
			'gradebook_csv'				=> 'WPCW_data_export_gradebookData'
		);
		
		// Check the export type matches the only types of export that we handle.
		if (!in_array($exportType, array_keys($exportTypeList))) {
			return;
		}
		
		// Trigger the function that will export this type of file.
		call_user_func($exportTypeList[$exportType]);
		
		// All done.
		// die(); // Let functions call die if they want to call it.
	}
}


/**
 * Function that handles the export of the survey responses for a specified survey.
 */
function WPCW_data_export_quizSurveyData()
{
	$quizID = trim(WPCW_arrays_getValue($_GET, 'quiz_id')) + 0;
	$quizDetails = WPCW_quizzes_getQuizDetails($quizID, true, false, false);
	
	// Check that we can find the survey.
	if (!$quizDetails) {
		printf('<div class="error updated"><p>%s</p></div>', __('Sorry, could not find that survey to export the response data.', 'wp_courseware'));
		return;
	}
	
	// Ensure it's a survey
	if ('survey' != $quizDetails->quiz_type) {
		printf('<div class="error updated"><p>%s</p></div>', __('Sorry, but the selected item is not a survey, it\'s a quiz.', 'wp_courseware'));
		return;
	}
	
	// Does this survey contain random questions? If so, then we need to get the full question data
	// of all possible questions
	if (WPCW_quizzes_doesQuizContainRandomQuestions($quizDetails))
	{
		$quizDetails->questions = WPCW_quizzes_randomQuestions_fullyExpand($quizDetails);
	}
	
	global $wpcwdb, $wpdb;
	$wpdb->show_errors();
	
	// Create a URL-safe version of the filename.
	$csvFileName = WPCW_urls_createCleanURL('survey-' . $quizDetails->quiz_id . '-' . $quizDetails->quiz_title) . '.csv';
	WPCW_data_export_sendHeaders_CSV($csvFileName);
	
	// The headings
	$headings = array(
		__('Trainee WP ID', 'wp_courseware'), 
		__('Trainee Name', 'wp_courseware'),
		__('Trainee Email Address', 'wp_courseware')
	);
	
	// Extract the questions to use as headings.
	$questionListForColumns = array();
	
	// See if we have any questions in the list.
	if (!empty($quizDetails->questions)) 
	{
		foreach ($quizDetails->questions as $questionID => $questionDetails) 
		{
			$questionListForColumns[$questionID] = $questionDetails->question_question;
			
			// Add this question to the headings.
			$headings[] = $questionDetails->question_question;
		}
	}
	
	// Start CSV
	$out = fopen('php://output', 'w');
	
	// Push out the question headings.
	fputcsv($out, $headings);
	
	// The responses to the questions
	$answers = $wpdb->get_results($wpdb->prepare("
		SELECT * 
		FROM $wpcwdb->user_progress_quiz
		WHERE quiz_id = %d
	", $quizDetails->quiz_id));
	
	// Process eacy response from the user, extracting their details too.
	if (!empty($answers))
	{
		foreach ($answers as $answerDetails)
		{
			$resultData = array();
			
			// We've definitely got the ID
			$resultData[] = $answerDetails->user_id;
			
			// See if we can get the name and email address.
			$userDetails = get_userdata($answerDetails->user_id);
			if ($userDetails) {
				$resultData[] = $userDetails->display_name;
				$resultData[] = $userDetails->user_email;
			} 
			// User has been deleted.
			else {
				$resultData[] = __('User no longer on system.', 'wp_courseware');
				$resultData[] = __('n/a', 'wp_courseware');
			}
			
			// Extract their responses into an array
			$theirResponses = maybe_unserialize($answerDetails->quiz_data);
						
			// Go through answers logically now
			if (!empty($questionListForColumns))
			{
				foreach ($questionListForColumns as $questionID => $questionTitle)
				{
					if (isset($theirResponses[$questionID]) && isset($theirResponses[$questionID]['their_answer'])) {
						$resultData[] = $theirResponses[$questionID]['their_answer'];
					}
					
					// Put something in the column, even if there is no answer.
					else {
						$resultData[] = __('No answer for this question.', 'wp_courseware');
					}
				}
			} // end of !empty check
			
			
			fputcsv($out, $resultData);
		} // end foreach
	} // end of if (!empty($answers))
	
	// All done
	fclose($out);
	
	die();
}


/**
 * Takes a string and makes it safe for a URL
 * @param String $urlString The string to make safe.
 * @return String A string safe enough to use as a URL.
 */
function WPCW_urls_createCleanURL($urlString)
{
	$urlString = trim(strtolower($urlString));
	
	// Remove brackets completely
	$urlString = preg_replace('%[\(\[\]\)]%', '', $urlString);
	
	// Remove non-alpha characters
	$urlString = preg_replace('%[^0-9a-z\-]%', '-', $urlString);
	
	// Replace long sequences of hypens with a single hyphen
	$urlString = preg_replace('%[\-]+%', '-', $urlString);  
	
	// Remove the last hypen (if there is one)
	$urlString = rtrim($urlString, '-');
	
	return $urlString;
}


/**
 * Function that generates a sample CSV file from the database using the relevant course IDs.
 */
function WPCW_data_export_userImportSample()
{
	WPCW_data_export_sendHeaders_CSV('wpcw-import-users-sample.csv');
	
	// Start CSV
	$out = fopen('php://output', 'w');
	
	// The headings
	$headings = array('first_name', 'last_name', 'email_address', 'courses_to_add_to');
	fputcsv($out, $headings);

	// Use existing course IDs to make it more useful. If there are no courses
	// Create some dummy courses to add. 
	$courseList = array();
	$courseList[1] = __('Test Course', 'wp_courseware') . ' A'; 
	$courseList[2] = __('Test Course', 'wp_courseware') . ' B';
	
	$courseListOfIDs = 0;
	foreach ($courseList as $courseID => $courseName)
	{
		$data = array();
		$data[] = 'John';
		$data[] = 'Smith';
		$data[] = get_bloginfo('admin_email');
		
		// Sequentially add each ID to the list
		if ($courseListOfIDs) { 
			$courseListOfIDs .= ',' . $courseID;
		} else {
			$courseListOfIDs = $courseID;
		}
		$data[] = $courseListOfIDs;
		
		// Not removing any courses
		$data[] = false;
		
		fputcsv($out, $data);
	}
		
	// All done
	fclose($out);
	
	die();
}


/**
 * Generates a verbose output of the gradebook data for a specific course.
 */
function WPCW_data_export_gradebookData()
{
	global $wpcwdb, $wpdb;
	$wpdb->show_errors();
		
	// #### 1 - See if the course exists first.
	$courseDetails = false;
	if (isset($_GET['course_id']) && $courseID = $_GET['course_id']) {
		$courseDetails = WPCW_courses_getCourseDetails($courseID);
	}
	
	// Course does not exist, simply output an error using plain text.
	if (!$courseDetails) 
	{
		header('Content-Type: text/plain');
		_e('Sorry, but that course could not be found.', 'wp_courseware');
		return;	
	}
	
	// #### 2 - Need a list of all quizzes for this course, excluding surveys.
	$quizzesForCourse = WPCW_quizzes_getAllQuizzesForCourse($courseDetails->course_id);	
	
	// Handle situation when there are no quizzes.
	if (!$quizzesForCourse) {
		header('Content-Type: text/plain');
		_e('There are no quizzes for this course, therefore no grade information to show.', 'wp_courseware');
		return;	
	}
	
	// Do we want certificates?
	$usingCertificates = ('use_certs' == $courseDetails->course_opt_use_certificate);
	
	// Create a simple list of IDs to use in SQL queries
	$quizIDList = array();
	foreach ($quizzesForCourse as $singleQuiz)  {
		$quizIDList[] = $singleQuiz->quiz_id;
	}
	
	// Convert list of IDs into an SQL list
	$quizIDListForSQL = '(' . implode(',', $quizIDList) . ')';
	
	// Course does exist, so now we really output the data
	$csvFilename = sanitize_title($courseDetails->course_title) . "-gradebook-" . date("Y-m-d") . ".csv";
	WPCW_data_export_sendHeaders_CSV($csvFilename);
	
	// Start CSV
	$out = fopen('php://output', 'w');
	
	// #### 3 - The headings for the CSV data
	$headings = array(
		__('Name', 							'wp_courseware'), 
		__('Username', 						'wp_courseware'),
		__('Email Address', 				'wp_courseware'),
		__('Course Progress', 				'wp_courseware'),
		__('Cumulative Grade', 				'wp_courseware'),
		__('Has Grade Been Sent?', 			'wp_courseware'),
		
	);	
	
	// Check if we're using certificates or not.
	if ($usingCertificates) {
		$headings[] = __('Is Certificate Available?', 	'wp_courseware');
	}

	// #### 4 - Add the headings for the quiz titles.
	foreach ($quizzesForCourse as $singleQuiz)
	{
		$headings[] = sprintf('%s (quiz_%d)', $singleQuiz->quiz_title, $singleQuiz->quiz_id);
	}	
	
	// #### 6 - Render the headings
	fputcsv($out, $headings);
	
	// #### 7 - Select all users that exist for this course
	$SQL = $wpdb->prepare("
		SELECT * 
		FROM $wpcwdb->user_courses uc									
		LEFT JOIN $wpdb->users u ON u.ID = uc.user_id
		WHERE uc.course_id = %d
		  AND u.ID IS NOT NULL			
		", $courseDetails->course_id);
	
	$userData = $wpdb->get_results($SQL);
	if (!$userData)
	{
		// All done
		fclose($out);
		return;
	}
	
	// #### 8 - Render the specific user details.
	foreach ($userData as $userObj)
	{
		$quizResults = WPCW_quizzes_getQuizResultsForUser($userObj->ID, $quizIDListForSQL);
				
		// Track cumulative data 
		$quizScoresSoFar = 0;
		$quizScoresSoFar_count = 0;
		
		// Track the quiz scores in order
		$thisUsersQuizData = array();
		
		// ### Now render results for each quiz
		foreach ($quizIDList as $aQuizID)
		{
			// Got progress data, process the result
			if (isset($quizResults[$aQuizID])) 
			{
				// Extract results and unserialise the data array.
				$theResults = $quizResults[$aQuizID];
				$theResults->quiz_data = maybe_unserialize($theResults->quiz_data);
				
				// We've got something that needs grading.
				if ($theResults->quiz_needs_marking > 0) {
					$thisUsersQuizData['quiz_' . $aQuizID] = __('Manual Grade Required', 'wp_courseware');
				}
				
				// User is blocked - they've failed and are blocked
				else if ('quiz_fail_no_retakes' == $theResults->quiz_next_step_type)
				{
					$thisUsersQuizData['quiz_' . $aQuizID] = __('Quiz Retakes Exhausted', 'wp_courseware');
				}
				
				// Quiz not yet complete...
				else if ('incomplete' == $theResults->quiz_paging_status)
				{
					$thisUsersQuizData['quiz_' . $aQuizID] = __('In Progress', 'wp_courseware');
				}
				
				// No quizzes need marking, so show the scores as usual.
				else 
				{
					// Calculate score, and use for cumulative.
					$score = number_format($theResults->quiz_grade);
					$quizScoresSoFar += $score;

					$thisUsersQuizData['quiz_' . $aQuizID] = $score . '%';
						
					$quizScoresSoFar_count++;
				}
			} // end of quiz result check. 
			
			// No progress data - quiz not completed yet
			else {
				$thisUsersQuizData['quiz_' . $aQuizID] = __('Not Taken', 'wp_courseware');
			}
		}	
				
		$dataToOutput = array();
		
		// These must be in the order of the columns specified above for it all to match up.		
		$dataToOutput['name'] 				= $userObj->display_name;
		$dataToOutput['username'] 			= $userObj->user_login;		
		$dataToOutput['email_address'] 		= $userObj->user_email;
		
		// Progress Details
		$dataToOutput['course_progress']	= $userObj->course_progress . '%';
		$dataToOutput['cumulative_grade']	= ($quizScoresSoFar_count > 0 ? number_format(($quizScoresSoFar / $quizScoresSoFar_count), 1) . '%' : __('n/a', 'wp_courseware'));
		$dataToOutput['has_grade_been_sent'] = ('sent' == $userObj->course_final_grade_sent ? __('Yes', 'wp_courseware') : __('No', 'wp_courseware'));
			
		// Show if there's a certificate that can be downloaded.
		if ($usingCertificates)
		{
			$dataToOutput['is_certificate_available'] 	= __('No', 'wp_courseware');				
			if (WPCW_certificate_getCertificateDetails($userObj->ID, $courseDetails->course_id, false))
			{
				$dataToOutput['is_certificate_available'] = __('Yes', 'wp_courseware');
			} 
		}
		
		// Output the quiz summary here..
		$dataToOutput += $thisUsersQuizData;
		
		fputcsv($out, $dataToOutput);
	}
		
	// All done
	fclose($out);
	die();
}


/**
 * Does this quiz contain any random questions.
 * 
 * @param unknown_type $quizDetails The quiz details to check.
 * @return Boolean True if there are random questions, false otherwise.
 */
function WPCW_quizzes_doesQuizContainRandomQuestions($quizDetails)
{
	if (empty($quizDetails->questions)) { 
		return false;
	}
	
	// Just need the first question to confirm if this is the case.
	foreach ($quizDetails->questions as $singleQuestion)
	{
		if ('random_selection' == $singleQuestion->question_type) {
			return true;
		}
	}
	
	return false;
}


/**
 * Does this quiz contain any random questions.
 * 
 * @param unknown_type $quizDetails The quiz details to check.
 * @return Boolean True if there are random questions, false otherwise.
 */
function WPCW_quizzes_randomQuestions_fullyExpand($quizDetails)
{
	if (empty($quizDetails->questions)) { 
		return $quizDetails->questions;
	}
	
	// Just need the first question to confirm if this is the case.
	$newQuizList = array();
	foreach ($quizDetails->questions as $questionID => $singleQuestion)
	{
		// Got a random selection, so we need to get all question variations.
		if ('random_selection' == $singleQuestion->question_type)
		{
			// Need tags for this question
			$tagDetails = WPCW_quiz_RandomSelection::decodeTagSelection($singleQuestion->question_question);
			
			// Ignore limits, just get all questions
			$expandedQuestions = WPCW_quiz_RandomSelection::questionSelection_getRandomQuestionsFromTags($tagDetails);
			if (!empty($expandedQuestions)) {
				$newQuizList += $expandedQuestions;
			}
		}
		
		// Normal question - just return it.
		else {
			$newQuizList[$questionID] = $singleQuestion;
		}
	}
	
	return $newQuizList;
}

?>