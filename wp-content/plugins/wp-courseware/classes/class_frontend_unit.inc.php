<?php

/**
 * Class that handles showing unit-related data to a user on the frontend of the website.
 *
 */
class WPCW_UnitFrontend
{
	/**
	 * Stores the associated post object.
	 * @var Object
	 */
	protected $unitPost;
	
	
	/**
	 * The associated parent data for this unit.
	 * @var Object
	 */
	protected $parentData;
	
	
	/**
	 * The ID of the currently logged in user.
	 * @var Integer
	 */
	protected $currentUserID;
	
	
	/**
	 * The unit progress for this user.
	 * @var UserProgress
	 */
	protected $userProgress;
	
	/**
	 * The optional quiz details for this unit.
	 * @var Object
	 */
	protected $unitQuizDetails;
	
	/**
	 * The optional quiz progress for this user.
	 * @var Object
	 */
	protected $unitQuizProgress;
	
	/**
	 * A list of incomplete question IDs (with their positions)
	 * @var Integer
	 */
	protected $unitQuizProgress_incompleteQs;
	
	/**
	 * Stores the quiz grades that need marking.
	 * @var Array
	 */
	protected $unchecked_QuizAnswersToGrade;
		
	/**
	 * Contains extra messages to be shown when sending output via AJAX or browser.
	 * @var Array
	 */
	protected $extraMessagesToShow_preQuizResults;
	
	/**
	 * Stores a cache of the results by tag.
	 * @var Array
	 */
	protected $cached_resultsByTag;
	
	
	
	/**
	 * Creates the object using the unit post.
	 * @param Object $post The post object for this unit.
	 */
	function __construct($post)	
	{
		$this->unitPost = $post;
		
		// Initialise any raw data
		$this->unchecked_QuizAnswersToGrade = $this->fetch_quizzes_loadRawAnswersSoFarForThisQuiz(false);
		
		// Load parent data for this unit
		$this->parentData = WPCW_units_getAssociatedParentData($post->ID);
		
		// Load the user ID for the user who is logged in
		$this->currentUserID = get_current_user_id();
		
		$this->extraMessagesToShow_preQuizResults = array();
		
		// Defaults
		$this->userProgress = false;
		$this->unitQuizDetails = false;
		$this->unitQuizProgress = false;
		$this->unitQuizProgress_incompleteQs = false;
		
		$this->cached_resultsByTag = false;
		
		
		// Try to load the progress data for this user
		if ($this->parentData)
		{
			// Main user progress data
			$this->userProgress = new UserProgress($this->parentData->course_id, $this->currentUserID);
			
			// Quiz details (if applicable)			
			// Get quiz details
			$this->unitQuizDetails = WPCW_quizzes_getAssociatedQuizForUnit($this->unitPost->ID, true, $this->currentUserID);
			
			// Get quiz progress details
			if ($this->check_quizzes_validQuizDetails())
			{
				// Got the user progress, determine if it's pending marking or not. 
				$this->unitQuizProgress = WPCW_quizzes_getUserResultsForQuiz($this->currentUserID, $this->unitPost->ID, $this->unitQuizDetails->quiz_id);

				// Try to work out which questions are still incomplete.
				$this->updateInternalVariable_quizzes_getIncompleteQuestions();
			}
		}

	}
	
	
	/**
	 * Updating the internal $unitQuizProgress_incompleteQs variable, work out the list of
	 * incomplete questions that the user has yet to have completed.
	 */
	function updateInternalVariable_quizzes_getIncompleteQuestions()
	{
		// Always have an empty array
		$this->unitQuizProgress_incompleteQs = array();
		
		// Not got any quiz data?
		if (empty($this->unitQuizDetails)) {
			return;
		}
		
		
		
		// Got any existing progress data?
		if (!empty($this->unitQuizProgress) && !empty($this->unitQuizProgress->quiz_data)) 
		{
			$indexOfQuestion = 0;
			foreach ($this->unitQuizProgress->quiz_data as $thisQuestionID => $thisAnswerDetails)
			{
				// Found a question that's not complete, so add to the list of incomplete questions in
				// the order that it appears. Maps ID => ordering.
				if ($thisAnswerDetails['is_incomplete'] == 1) {
					$this->unitQuizProgress_incompleteQs[$indexOfQuestion] = $thisQuestionID;
				}
				
				$indexOfQuestion++; // Stores 0-based index of the question
			}
		}
		
		// No progress data, so just use the raw list of questions to create a list of ID => ordering.
		else
		{
			if (!empty($this->unitQuizDetails->questions));
			{
				$indexOfQuestion = 0;
				foreach ($this->unitQuizDetails->questions as $thisQuestionID => $questionObj)
				{
					// Stores 0-based index of the question
					$this->unitQuizProgress_incompleteQs[$indexOfQuestion++] = $thisQuestionID;
				}
			}
		}
	}
	
	
	
	/**
	 * Check if we have an active timer.
	 */
	function check_timers_doWeHaveTimerDataForThisQuiz()
	{
		// See if we have quiz details, and we're in timer mode, and that we have a valid quiz start time.
		if (!empty($this->unitQuizDetails) 											&& 
			'use_timer' == $this->unitQuizDetails->quiz_timer_mode 					&& 
		
			// We need progress, and a retake has been started, and the start date is valid
			!empty($this->unitQuizProgress) 										&& 
			'retake_waiting' != $this->unitQuizProgress->quiz_next_step_type 		&&
			'0000-00-00 00:00:00' != $this->unitQuizProgress->quiz_started_date)
		{
			return true;	
		}
		
		return false;
	}
	
	
	
	/**
	 * Check if we have a timer that has expired. Assumes that we've already checked for an active timer.
	 */
	function check_timers_doWeHaveAnActiveTimer_thatHasExpired()
	{
		// We've already sorted this.
		if (!empty($this->unitQuizProgress) && 'complete' == $this->unitQuizProgress->quiz_paging_status) {
			return;
		}
		
		// Total time in seconds for this quiz 
		$timerDetails_secondsLeft = $allowedTime = 60 * $this->unitQuizDetails->quiz_timer_mode_limit;
		
		// What time do we have left?
		$timeSoFar = strtotime($this->unitQuizProgress->quiz_started_date);
		$timeNow = current_time('timestamp');
		
		// We've started the quiz when the time so far is > 0
		if ($timeSoFar > 0) {
			$timerDetails_secondsLeft = $allowedTime - ($timeNow - $timeSoFar);
		}
		
		// Check the number of seconds that are left. If the time left is negative
		// then the quiz has expired, so we need to mark the quiz as expired before carrying on.
		return ($timerDetails_secondsLeft <= 0);
	}
	
	
	/**
	 * Updates the quiz progress as the timer has expired. Assumes that 
	 * calling this function that all prior checks have been executed correctly.
	 */
	function update_quizzes_endQuizTimeExpired()
	{
		// First load any answers that we have already (perhaps through paging).
		$this->fetch_quizzes_loadRawAnswersSoFarForThisQuiz($this->unitQuizProgress->quiz_data);
		
		// Does the grading and notifications if needed.
		$this->check_quizzes_gradeQuestionsForQuiz();
	}
	
	
	/**
	 * Main rendering function for the details for this unit.
	 * 
	 * @param String $content The content currently being shown on the page.
	 * @param Boolean $justGetCompletionAndQuizData If true, then just render the completion box and quiz details.
	 * 
	 * @return String The content to render for the unit.
	 */
	function render_detailsForUnit($content, $justGetCompletionAndQuizData = false)
	{
		$completionBox = false;
		$extraMessages = false;
		$quizProgressData = false;
		
		// ####ÊTimers - Check if the timer has timed out for the quiz.
		if ($this->check_timers_doWeHaveTimerDataForThisQuiz())
		{	
			if ($this->check_timers_doWeHaveAnActiveTimer_thatHasExpired()) {
				$this->update_quizzes_endQuizTimeExpired();
			} 
		}
		
		
		// #### Completion Box - Determine if this unit has been completed or not by rendering the status 
		// 						 box that appears in the footer of the unit.
		if ($this->check_unit_isCompleted()) {
			$completionBox .= $this->render_completionBox_complete();
		}		
		else {
			$completionBox .= $this->render_completionBox_pending();
		}
		
		// If we want to show answers for this quiz/survey, then we show them. 
		if ($this->check_quizzes_hasUserCompletedQuiz() && 
		    $this->check_quizzes_doWeShowResults() && 
		    !$this->check_quizzes_areWeWaitingForUserToRetakeQuiz())
		{
			// Got the user progress, determine if it's pending marking or not. 
			// User has completed this quiz... show the results.
			if ($this->unitQuizProgress)  
			{				
				$quizProgressData = $this->render_quizzes_showAllCorrectAnswers();
			}
		}
		
		// Any extra messages we're showing?
		$extraMessages = implode("\n", $this->extraMessagesToShow_preQuizResults);
		
		
		// Just want the completion box and quiz, don't need the body content or navigation.
		if ($justGetCompletionAndQuizData)
		{
			return $completionBox . $extraMessages . $quizProgressData;
		}
		else 
		{		
			// #### Show the navigation box (disable the next item if not completed yet).
			$navigationBox = $this->render_navigation_getNavigationBox(!$this->check_unit_isCompleted());
		
			// Modify the main content if needed
			$content = apply_filters('wpcw_front_unit_content_normal', $content, $this->parentData, $this->unitPost);
			
			// Create a wrapper around all dynamic content for jQuery/AJAX control
			return $content . '<div id="wpcw_fe_outer_wrap">' . $completionBox  . $extraMessages . $quizProgressData . '</div>' . $navigationBox ;	
		}
	}
	
	
	/**
	 * Determine if we're waiting for the user to retake the quiz or not. This is when they 
	 * have clicked on the button to request a retake of the quiz.
	 */
	function check_quizzes_areWeWaitingForUserToRetakeQuiz()
	{
		// Yes, we're doing a retake if the flag is set on the latest progress, marked as retake waiting.
		return ($this->unitQuizProgress && 'retake_waiting' == $this->unitQuizProgress->quiz_next_step_type);
	}
	
	
	/**
	 * Is this user allowed to retake this quiz? Requires that there are enough chances to retake the quiz
	 * and that quiz is in a state for retakes.
	 */
	function check_quizzes_canUserRequestRetake()
	{
		$remainingAttempts = $this->fetch_quizzes_getRemainingAttempts();
		return ($this->unitQuizProgress && 
		
			// Manual intervention
			('retake_quiz' == $this->unitQuizProgress->quiz_next_step_type && $this->unitQuizProgress->attempt_count > 0) ||

			// Automatic retake permitted
			($remainingAttempts != 0)
			);
	}
	
	
	/**
	 * Check if user has passed quiz, is their grade is more than the pass mark.
	 */
	function check_quizzes_hasUserPassedQuiz()
	{
		return $this->unitQuizProgress && ($this->unitQuizProgress->quiz_grade >= $this->unitQuizDetails->quiz_pass_mark);
	}
	
	
	/**
	 * Check if user has any attempts left.
	 */
	function fetch_quizzes_getRemainingAttempts()
	{
		// Got unlimited attempts remaining.
		if (-1 == $this->unitQuizDetails->quiz_attempts_allowed) {
			return -1;
		}
		
		// Had too many attempts, return 0.
		if ($this->unitQuizProgress->attempt_count >= $this->unitQuizDetails->quiz_attempts_allowed) {
			return 0;
		}
		
		// Return the difference.
		return $this->unitQuizDetails->quiz_attempts_allowed - $this->unitQuizProgress->attempt_count;
	}
	
	
	/**
	 * Update the progress for this quiz to allow a retake to happen.
	 */
	function update_quizzes_requestQuizRetake()
	{
		if ($this->check_quizzes_canUserRequestRetake())
		{			
			global $wpdb, $wpcwdb;
			$wpdb->show_errors();
		
			// Update the quiz progress so that the next step is showing the quiz so that the user can complete it.
			$this->update_quizzes_setNextStepData('retake_waiting', false);
		
			// Remove any locks for this unit for this user, if there are any random questions.
			$wpdb->query($wpdb->prepare("
				DELETE FROM $wpcwdb->question_rand_lock 
				WHERE question_user_id = %d
				  AND parent_unit_id = %d
			", $this->currentUserID, $this->unitPost->ID));
			
			// Reload any questions, as we've just deleted random locks.
			$this->unitQuizDetails = WPCW_quizzes_getAssociatedQuizForUnit($this->unitPost->ID, true, $this->currentUserID);
			
			// Change the status of the user progress without reloading all of the data again.
			$this->unitQuizProgress->quiz_next_step_type = 'retake_waiting';
		}
	}
	
	
	/**
	 * Determine if we want to show the results for this quiz.
	 * @return Boolean True if we return results, false otherwise.
	 */
	function check_quizzes_doWeShowResults()
	{
		// If no details, then don't show results.
		if (!$this->check_quizzes_validQuizDetails()) {			
			return false;
		}
		
		// ### Show Answers - show quiz answers if requested.
		// Do we want to try and show the results?
		// @since V2.90
		$wantToTryToShowResults = false; //('survey' == $this->unitQuizDetails->quiz_type && 'show_responses' = $this->unitQuizDetails->quiz_show_survey_responses);
		switch ($this->unitQuizDetails->quiz_type)
		{
			// Survey - show responses only
			case 'survey':
					$wantToTryToShowResults = ('show_responses' == $this->unitQuizDetails->quiz_show_survey_responses);
				break;
				
			// Quiz - Show Answers mode
			default:
					// Do we want to show the answers after they've completed it?
					$showAnswersSettings = maybe_unserialize($this->unitQuizDetails->show_answers_settings);			
					if (('show_answers' == $this->unitQuizDetails->quiz_show_answers) && 'on' == WPCW_arrays_getValue($showAnswersSettings, 'show_results_later'))
					{
						$wantToTryToShowResults = true;
					}
				break;
		}
		
		return $wantToTryToShowResults;
	}
	
	
	/**
	 * Render all of the correct answers for the user.
	 * 
	 * @param $returnDataInArray Boolean If true, then return the answers as an array of formatted elements.
	 */
	function render_quizzes_showAllCorrectAnswers($returnDataInArray = false)
	{
		// Hopefully not needed, but just in case.
		if (!$this->unitQuizDetails) {
			return false;
		}
		
		// @since V2.90
		// Working on a survey, so this is a little different. We only show a response, not giving
		// any indication if they are right or not.
		if ('survey' == $this->unitQuizDetails->quiz_type)
		{
			$setting_showCorrectAnswer = false;
			$setting_showUserAnswer    = true;
			$setting_showExplanation   = false;
			$setting_showMarkAnswers   = false;
			$setting_showAnswersLater  = $setting_showUserAnswer;
		}
	
		// Working on a quiz
		else 
		{
			// Work out the settings for showing the answers 
			$setting_allRaw = maybe_unserialize($this->unitQuizDetails->show_answers_settings);
			
			// Extract our settings. 
			$setting_showCorrectAnswer 	= ('on' == WPCW_arrays_getValue($setting_allRaw, 'show_correct_answer')); 
			$setting_showUserAnswer    	= ('on' == WPCW_arrays_getValue($setting_allRaw, 'show_user_answer'));
			$setting_showExplanation    = ('on' == WPCW_arrays_getValue($setting_allRaw, 'show_explanation'));
			$setting_showMarkAnswers    = ('on' == WPCW_arrays_getValue($setting_allRaw, 'mark_answers'));
		}
	
		$html = false;
		$arrayToReturn = array();
		
			// Create a simple DIV wrapper for the correct answers.
			if (!$returnDataInArray)
			{
				$html .= '<div class="wpcw_fe_quiz_box_wrap wpcw_fe_quiz_box_full_answers">';		
				$html .= '<div class="wpcw_fe_quiz_box wpcw_fe_quiz_box_pending">';
				
				// #### 1 - Quiz Title - constant for all quizzes
				$html .= sprintf('<div class="wpcw_fe_quiz_title"><b>%s</b> %s</div>', __('Correct Answers for: ', 'wp_courseware'), $this->unitQuizDetails->quiz_title);
				
				// #### 2 - Header before questions
				$html .= '<div class="wpcw_fe_quiz_q_hdr"></div>';
			}				
		
				// #### 3 - Extract the correct answer from the index of questions.
				if ($this->unitQuizDetails->questions && count($this->unitQuizDetails->questions) > 0)
				{
					$questionNum = 1;
					
					foreach ($this->unitQuizDetails->questions as $question) 
					{
						// Clean out the html ready to add each item
						if ($returnDataInArray) {
							$html = false;
						} else {						
							$html .= '<div class="wpcw_fe_quiz_q_single">';
						}
		
						// ### 3a - Question title
						$html .= sprintf('<div class="wpcw_fe_quiz_q_title">%s #%d - %s</div>', 
							__('Question', 'wp_courseware'),
							$questionNum++,
							$question->question_question
						);
						
						// ### 3b - The question image - If there's an image for this quiz, then render it.
						if ($question->question_image) {
							$html .= sprintf('<div class="wpcw_fe_quiz_q_image"><img src="%s" /></div>', $question->question_image);
						}
						
						// ### 3c - Their Mark (correct or incorrect)
						if ($setting_showMarkAnswers)
						{		
							// See if they have an answer for this question first
							if (!empty($this->unitQuizProgress->quiz_data) && isset($this->unitQuizProgress->quiz_data[$question->question_id]))
							{
								$theirAnswerDetails = $this->unitQuizProgress->quiz_data[$question->question_id];
								
								switch ($question->question_type)
								{
									// open-ended questions have grades.
									case 'open':
									case 'upload':
											// Check if question still needs to be marked.
											if (isset($this->unitQuizProgress->quiz_needs_marking_list) && is_array($this->unitQuizProgress->quiz_needs_marking_list) && in_array($question->question_id, $this->unitQuizProgress->quiz_needs_marking_list))
											{
												$html .= sprintf('<div class="wpcw_fe_quiz_q_result wpcw_fe_quiz_q_user_grade"><b>%s:</b>&nbsp;&nbsp;%s</div>', 
													__('Your Grade', 'wp_courseware'),
													__('Pending', 'wp_courseware')
												);
											}
	
											// Nope, it's marked, so show the grade.
											else 
											{
												$gradePercentage = WPCW_arrays_getValue($theirAnswerDetails, 'their_grade');
												
												$html .= sprintf('<div class="wpcw_fe_quiz_q_result wpcw_fe_quiz_q_user_grade"><b>%s:</b>&nbsp;&nbsp;%d%%</div>', 
													__('Your Grade', 'wp_courseware'),
													$gradePercentage
												);
											}
										break;
										
									case 'multi':
									case 'truefalse':

										
											// Got it right...
											if ('yes' == WPCW_arrays_getValue($theirAnswerDetails, 'got_right'))
											{
												$html .= sprintf('<div class="wpcw_fe_quiz_q_result wpcw_fe_quiz_q_result_correct">%s</div>', __('Correct ', 'wp_courseware'));
											}
											
											// Got it wrong...
											else {
												$html .= sprintf('<div class="wpcw_fe_quiz_q_result wpcw_fe_quiz_q_result_incorrect">%s</div>', __('Incorrect ', 'wp_courseware'));
											}
										break;
								}
	
							}
						} // end if ($setting_showMarkAnswers)			
						
						
						// Work out the correct answer
						$correctAnswer = $this->check_quizzes_getCorrectAnswer($question);
												
						// ### 3c - Answer - User's Answer
						if ($setting_showUserAnswer)
						{
							$theirAnswer = NULL;
							
							// See if they have an answer for this question first
							if (!empty($this->unitQuizProgress->quiz_data) && isset($this->unitQuizProgress->quiz_data[$question->question_id]))
							{
								$theirAnswerDetails = $this->unitQuizProgress->quiz_data[$question->question_id];								
								
								// Handle file types and open-ended questions
								switch($question->question_type)
								{
									// File upload, so show link to file.
									case 'upload':
											$theirAnswerRaw = WPCW_arrays_getValue($theirAnswerDetails, 'their_answer');
											$theirAnswer = sprintf('<a href="%s%s" target="_blank">%s .%s %s (%s)</a>', 
												WP_CONTENT_URL, $theirAnswerRaw,
												__('Open', 'wp_courseware'),
												pathinfo($theirAnswerRaw, PATHINFO_EXTENSION),
												__('File', 'wp_courseware'), 
												WPCW_files_getFileSize_human($theirAnswerRaw)								
											);
										break;
										
									// Paragraph of text - show with <p> tags
									case 'open':
											$theirAnswer = wpautop(WPCW_arrays_getValue($theirAnswerDetails, 'their_answer'));
										break;
										
										
									default:
											$theirAnswer = WPCW_arrays_getValue($theirAnswerDetails, 'their_answer');
										break;
								}							
							}
							
							// We've got a valid answer.
							if (!is_null($theirAnswer))
							{
								$html .= sprintf('<div class="wpcw_fe_quiz_q_your_answer"><b>%s:</b>&nbsp;&nbsp;%s</div>', 
									__('Your Answer', 'wp_courseware'),
									$theirAnswer
								);
								
								// Show associated image for their answer if we have one.
								$imageURL = $this->fetch_quizzes_getImageForAnswer($question, WPCW_arrays_getValue($theirAnswerDetails, 'their_answer_raw'));
								if ($imageURL) {
									$html .= sprintf('<div class="wpcw_fe_quiz_a_image"><img src="%s" /></div>', $imageURL);	
								}
							}
							
							// We don't have an answer for this question.
							else 
							{
								$html .= sprintf('<div class="wpcw_fe_quiz_q_your_answer wpcw_fe_quiz_q_your_answer_none_found"><b>%s:</b>&nbsp;&nbsp;(%s)</div>', 
									__('Your Answer', 'wp_courseware'),
									__('We don\'t have your answer for this question', 'wp_courseware')
								);
							}
						} // end if ($setting_showUserAnswer)
	
						
						// ### 3c - Answer - The Correct Answer (for fixed answer questions)
						if ($setting_showCorrectAnswer && in_array($question->question_type, array('truefalse', 'multi')))
						{						
							$html .= sprintf('<div class="wpcw_fe_quiz_q_correct"><b>%s:</b>&nbsp;&nbsp;%s</div>', 
								__('Correct Answer', 'wp_courseware'),
								$correctAnswer
							);
							
							// Show image if there is one for this answer.
							$imageURL = $this->fetch_quizzes_getImageForAnswer($question, $question->question_correct_answer);
							if ($imageURL) {
								$html .= sprintf('<div class="wpcw_fe_quiz_a_image"><img src="%s" /></div>', $imageURL);	
							}
						} // end if ($setting_showCorrectAnswer && $correctAnswer)
						
						
						
						// Quiz Explanation - If there's a quiz explanation, put it here.
						if ($setting_showExplanation && $question->question_answer_explanation) 
						{
							$html .= sprintf('<div class="wpcw_fe_quiz_q_explanation"><b>%s:</b>&nbsp;&nbsp;%s</div>', 
								__('Explanation', 'wp_courseware'),
								$question->question_answer_explanation
							);
						}
						
						// Add this portion of data rather than use the div wrapper.
						if ($returnDataInArray) {
							$arrayToReturn[] = $html;
						} else {						
							$html .= '</div>'; // wpcw_fe_quiz_q_single
						}
					}				 
				} 			
	
		// Non-array mode - return HTML
		if (!$returnDataInArray)
		{
			$html .= '</div>'; // .wpcw_fe_quiz_box 
			$html .= '</div>'; // .wpcw_fe_quiz_box_wrap
		}			
		// Returning array items now.
		else 
		{
			return $arrayToReturn;	
		}
			
		return $html;
	}
	
		
	/**
	 * Creates a box to show that a unit has been completed.
	 * 
	 * @return String The HTML that renders this box.
	 */
	function render_completionBox_complete()
	{			
	    $html = false;
	    // Work out if course completed.
	    
	    // Unit and course is complete, so show course complete message.
		if ($this->userProgress->isCourseCompleted()) 
		{
			$certHTML = false;		
			$certificateDetails = WPCW_certificate_getCertificateDetails($this->currentUserID, $this->parentData->course_id);
			
			// Generate certificate button if enabled and a certificate exists for this user.
			if ('use_certs' == $this->parentData->course_opt_use_certificate && $certificateDetails)
			{		
				$certHTML = sprintf('<div class="wpcw_fe_progress_box_download"><a href="%s" class="fe_btn fe_btn_download">%s</a></div>', 
					WPCW_certificate_generateLink($certificateDetails->cert_access_key), __('Download Certificate', 'wp_courseware')
				);
			}
					
			// Course completion message
			$html .= sprintf('<div class="wpcw_fe_progress_box_wrap">
				<div class="wpcw_fe_progress_box wpcw_fe_progress_box_complete">%s%s</div>
			</div>', $certHTML, $this->parentData->course_message_course_complete);
		
		}
		
		// Unit is complete, but course isn't
		else
		{
			$html = sprintf('<div class="wpcw_fe_progress_box_wrap"><div class="wpcw_fe_progress_box wpcw_fe_progress_box_complete">%s</div></div>', 
				$this->parentData->course_message_unit_complete
			);	
		}
		
		// Got any quiz status data to show?
		$quizMessageData = $this->render_completionBox_quizPassStatus();
		
		// Got any custom feedback data to show?
		$customFeedbackMessages = $this->render_customFeedbackMessage_showResults();
		
		return $html . $quizMessageData . $customFeedbackMessages;
	}
	
	
	
	/**
	 * Renders the custom feedback messages on the front of the website.
	 */
	function render_customFeedbackMessage_showResults()
	{
		$html = false;
		
		$resultsList = $this->fetch_customFeedbackMessage_calculateMessages();
		
		if (!empty($resultsList))
		{
			// Start the wrapper for the results.
			$html .= sprintf('<div class="wpcw_fe_progress_box_wrap"><div class="wpcw_fe_progress_box wpcw_fe_custom_feedback_wrap wpcw_fe_custom_feedback_count_%d">', 
				count($resultsList) // Adding count to allow for special styles based on how many feedback messages there are.
			);
			
			// Render each message with a wrapper around it.
			foreach ($resultsList as $singleMessage)
			{
				$html .= '<div class="wpcw_fe_custom_feedback_item">' . wpautop($singleMessage) . '</div>'; 
			}
			
			// Close wrapper
			$html .= '</div></div>';
		}
		
		return $html;
	}
	
	
	/**
	 * Works out if we're showing any custom feedback messages to the user.
	 * @return $resultsList The list of messages to show (or an empty array). 
	 */
	function fetch_customFeedbackMessage_calculateMessages()
	{
		$resultsList = array();
		
		// #### 1) - Check we have graded questions for a quiz
		if ($this->check_quizzes_validQuizDetails() && 
			$this->unitQuizProgress && 
			'survey' !=  $this->unitQuizDetails->quiz_type && 
			($this->unitQuizProgress->quiz_needs_marking == 0))
		{
			// #### 2) - See if we have any tag grades for this quiz
			$tagBucketList = $this->fetch_quizzes_questionResultsByTag();
			
			// No tags to check data against
			if (empty($tagBucketList)) {
				return $resultsList;
			}
			
			// #### 3) - See if we have any feedback messages for this quiz using
			// a combination of the tags from above, and the quiz ID. This saves
			// how much data we load from the database.
			$tagIDList = array_keys($tagBucketList); 			
			$feedbackList = WPCW_quizzes_feedback_getFeedbackMessagesForQuiz($this->unitQuizDetails->quiz_id, $tagIDList);
			
			
			// #### 4) - Now we process each feedback message to see if it matches the criteria.
			if (!empty($feedbackList))
			{
				// Now include custom feedback class, as we need it.
				include_once WPCW_plugin_getPluginDirPath() . 'classes/class_custom_feedback.inc.php';
				
				foreach ($feedbackList as $feedbackItem)
				{
					$fb = new WPCW_quiz_CustomFeedback($this->unitQuizDetails->quiz_id, $feedbackItem);
					
					// See if the message matches the feedback we've got.
					if ($fb->doesMessageMatchCriteria($tagBucketList))
					{
						$resultsList[] = $fb->getMessage();
					}
					//echo $fb->generate_editForm();
				}
			} // end if $feedbackList.
		} // end if quiz check
		
		return $resultsList;
	}
	
	
	/**
	 * Function called to work out what the pass status details are for this quiz.
	 */
	function check_quizzes_workoutQuizPassStatusDetails()
	{
		$rtnDetails = array(
			'error_mode'	=> false
		);		
		
		// See if we have a quiz for this unit? If so, render it and allow the trainee to complete it. 
		if ($this->check_quizzes_validQuizDetails() && 
			$this->unitQuizProgress && 
			'survey' !=  $this->unitQuizDetails->quiz_type)
		{
			$errorMode = false;
						
			// All questions have been graded.
			if ($this->unitQuizProgress->quiz_needs_marking == 0)
			{				
				// 1) Add button download
				$rtnDetails['button_dl_results_url'] = apply_filters('wpcw_results_generated_url', WPCW_plugin_getPluginPath() . 'pdf_create_results.php?unitid=' . $this->unitPost->ID . '&quizid=' . $this->unitQuizDetails->quiz_id);
				
				// 2) Show how many they have correct
				// Open-ended - just show grade.
				if ($this->unitQuizDetails->has_open_questions)
				{
					$rtnDetails[0]['msg_overall_grade'] = sprintf(__('Your grade for this quiz is <b>%d%%</b>.', 'wp_courseware'), 
						$this->unitQuizProgress->quiz_grade
					);
				}
				
				// Just closed questions, show out of message.
				else 
				{
					$rtnDetails[0]['msg_overall_grade'] = sprintf(__('You got %d out of %d questions <b>(%d%%)</b> correct!', 'wp_courseware'), 
						$this->unitQuizProgress->quiz_correct_questions, 
						$this->unitQuizProgress->quiz_question_total, 
						$this->unitQuizProgress->quiz_grade
					);
				}
	
				// 3) Non-Blocking quiz - show say that the unit is now complete.
				if ('quiz_noblock' == $this->unitQuizDetails->quiz_type)
				{
					$rtnDetails[0]['msg_nonblock_completion_status'] = $this->parentData->course_message_unit_complete;
				}
				
				// 4) Blocking quiz - show if they have passed or failed this quiz.
				else
				{
					// 5) Show the pass mark.
					$rtnDetails[0]['msg_block_pass_grade'] = sprintf(__('The pass grade for this quiz is <b>(%d%%)</b>', 'wp_courseware'), $this->unitQuizDetails->quiz_pass_mark);
					
					// 6) Show if they have passed or failed.						
					if ($this->check_quizzes_hasUserPassedQuiz())
					{						
						$rtnDetails[1]['msg_block_pass_has_passed'] = sprintf(__('Congratulations, you have <b>passed</b> this quiz!', 'wp_courseware'), $this->unitQuizDetails->quiz_pass_mark);
						$rtnDetails[1]['msg_block_pass_has_passed_unit_complete'] = $this->parentData->course_message_unit_complete;
					}
					else 
					{
						$errorMode = true;
						$rtnDetails[1]['msg_block_pass_has_failed'] = sprintf(__('Unfortunately, this means you have <b>failed</b> this quiz.', 'wp_courseware'), $this->unitQuizDetails->quiz_pass_mark);
						
						// 6) Show message that they need to pass to progress (as long as admin says that they can't progress anyway).
						if ('progress_anyway' == $this->unitQuizProgress->quiz_next_step_type)
						{
							$rtnDetails[1]['msg_block_pass_next_action'] = sprintf(__('However, the instructor is allowing you to continue anyway.', 'wp_courseware'), $this->unitQuizDetails->quiz_pass_mark);
						}
						else {
							$rtnDetails[1]['msg_block_pass_next_action'] = sprintf(__('To progress to the next unit, you need to pass this quiz.', 'wp_courseware'), $this->unitQuizDetails->quiz_pass_mark);
						}
						
						// 7) How many attempts have they now had?
						$attemptCount = $this->unitQuizProgress->attempt_count;
						$rtnDetails[2]['msg_attempts'] = ($attemptCount == 1 
								? __('You have had <b>1 previous attempt</b> at this quiz.', 'wp_courseware')  
								: sprintf(__('You have had <b>%d previous attempts</b> at this quiz.', 'wp_courseware'), $attemptCount)
							);
						
					}
				} // end of blocking quiz check
				
				
				// 8) - Show the quiz breakdown by tag (if wanted)
				if ('on' == $this->unitQuizDetails->quiz_results_by_tag) {
					$rtnDetails['msg_results_by_tag'] = $this->render_quizzes_questionResultsByTag_forFrontend();
				}
				
				// 9) - Show the time needed for this quiz and breakdown of results
				if (
					'quiz_block' == $this->unitQuizDetails->quiz_type &&  		// Timers only appear on blocking quizzes
					'on' == $this->unitQuizDetails->quiz_results_by_timer &&	// User actually wants to show the timer results 
					$this->check_timers_doWeHaveTimerDataForThisQuiz()			// Check if there is any timer data to show
					)
				{
					$rtnDetails['msg_results_by_timer'] = sprintf('<b>%s:</b> %s (%s %s)',
						__('Completion Time', 'wp_courseware'),
						WPCW_time_convertSecondsToHumanLabel($this->unitQuizProgress->quiz_completion_time_seconds),
						WPCW_time_convertMinutesToHumanLabel($this->unitQuizDetails->quiz_timer_mode_limit),
						__('allowed', 'wp_courseware')
					);
				} // end of timer check
			} 
			
			// Still have questions to grade.
			else 
			{
				$courseDetails = WPCW_courses_getCourseDetails($this->unitQuizDetails->parent_course_id);
				
				// Show the pending message if the quiz needs grading.
				if ('quiz_noblock' == $this->unitQuizDetails->quiz_type) {
					$rtnDetails['msg_noblock_grading'] = $courseDetails->course_message_quiz_open_grading_non_blocking;
				} else {
					$rtnDetails['msg_block_grading'] = $courseDetails->course_message_quiz_open_grading_blocking;
				}
			}			
			
			// Finally - show the message with the right style of box (error or success)
			if ($errorMode) {
				$rtnDetails['error_mode'] = true;
			} 
		} // end complex if
		
		return $rtnDetails;
	}
	
	/**
	 * Function called when showing the pass status for a quiz. Called in both complete and pending status boxes.
	 */
	function render_completionBox_quizPassStatus()
	{
		$html = false;
		$messageToShow = false;
		
		// Fetch the details that we need to render.
		$msgDetails = $this->check_quizzes_workoutQuizPassStatusDetails();
		
		// 1) Check for a download button
		if (isset($msgDetails['button_dl_results_url']))
		{
			$messageToShow .= sprintf('
				<div class="wpcw_fe_progress_download">
					<a href="%s" class="fe_btn fe_btn_completion fe_btn_small">%s</a>
				</div>',
					$msgDetails['button_dl_results_url'], 
					__('Download Results', 'wp_courseware')
				);
		}
		
		// 2) Check for messages by row. If we've got a row, then render each item.
		//    There are a maximum of 5 rows.
		for ($idx = 0; $idx < 5; $idx++)
		{
			if (!empty($msgDetails[$idx]))
			{
				// There may be multiple messages per row.
				foreach ($msgDetails[$idx] as $keyName => $stringToShow)
				{
					$messageToShow .= $stringToShow . ' ';
				}
				
				$messageToShow .= '<br><br>';
			}
		}
		
		// 3) Remove the extra trailing <br><br> characters if we have them
		$messageToShow = rtrim($messageToShow, '<br><br>');
		
		$progressBreakdown = false;
		
		
		// 4) Show the progress by tag or timer if present.
		if (isset($msgDetails['msg_results_by_tag']) && !empty($msgDetails['msg_results_by_tag']))
		{
			// Create wrapper for tags
			$progressBreakdown .= '<div class="wpcw_fe_progress_result_by_tag_wrap">';
			
				// Add a wrapper per line for the tag results.
				foreach ($msgDetails['msg_results_by_tag'] as $tagMessage)
				{
					$progressBreakdown .= '<div class="wpcw_fe_progress_result_by_tag">' . $tagMessage . '</div>';
				}

			// Close wrapper for tags
			$progressBreakdown .= '</div>';
		}
		
		if (isset($msgDetails['msg_results_by_timer']))
		{
			// Add class for padding if we have results by tag
			$extraClass = ($progressBreakdown ? 'wpcw_fe_progress_timing_pad' : '');
									
			$progressBreakdown .= sprintf('<div class="wpcw_fe_progress_timing %s">%s</div>', $extraClass, $msgDetails['msg_results_by_timer']);
		}
		
		
		// 5) Add wrapper for progress if present
		if ($progressBreakdown) {
			$progressBreakdown = '<div class="wpcw_fe_progress_breakdown_wrap">' . $progressBreakdown . '</div>';
			$messageToShow .= $progressBreakdown;
		}
		
		// 6) See if we need to mark up the message as an error.
		if ($messageToShow)
		{
			if ($msgDetails['error_mode']) {
				$html .= WPCW_UnitFrontend::message_createMessage_error($messageToShow);
			} else{
				$html .= WPCW_UnitFrontend::message_createMessage_success($messageToShow);
			}
		}
		
		return $html;
	}
	
	
	
	
	
		
	
	/**
	 * Creates a box to show that a unit is currently pending.
	 * 
	 */
	function render_completionBox_pending()
	{
		$html = false;
				
		// See if we have a quiz for this unit? If so, render it and allow the trainee to complete it. 
		if ($this->check_quizzes_validQuizDetails())
		{			
			if ($this->check_quizzes_hasUserCompletedQuiz())
			{
				// Show the user what their grade is (for non-surveys, if we have any existing data to show).		
				if ('survey' != $this->unitQuizDetails->quiz_type && $this->unitQuizProgress->quiz_needs_marking == 0)
				{
					$html .= $this->render_completionBox_quizPassStatus();
					
					// Blocking Quiz - User has failed, so offer chance to retake.
					if ('quiz_block' == $this->unitQuizDetails->quiz_type && !$this->check_quizzes_hasUserPassedQuiz() && 
					
						// If user has requested retake, we don't want to show this message.
						'retake_waiting' != $this->unitQuizProgress->quiz_next_step_type)
					{ 
						$messageToShow = false;
						$showRetakeButton = false;
						
						// Retake Needed A - Ordered by instructor.
						if ('retake_quiz' == $this->unitQuizProgress->quiz_next_step_type)
						{
							// Show a generic message that the quiz needs to be re-taken.
							$messageToShow .= wpautop(__('The course instructor has required that you retake this quiz.', 'wp_courseware'));
								
							// Add the custom message if there was one, which is personalised from the instructor.
							if ($this->unitQuizProgress->quiz_next_step_msg) {
								$messageToShow .= wpautop(stripslashes($this->unitQuizProgress->quiz_next_step_msg));
							}
							
							$showRetakeButton = true;
						}
						
						
						// Retake Needed B - Auto check as failed, and user can retake again if the retake count is 
						// less than the permitted count for the quiz. This filters out the state of quiz waiting too.
						else 
						{
							// Show message if there are any attempts left.
							$remainingAttempts = $this->fetch_quizzes_getRemainingAttempts();
							if ($remainingAttempts != 0)
							{
								// Show a generic message that the quiz needs to be re-taken.
								$messageToShow .= wpautop(__('The course instructor has allowed you to retake the quiz. To re-attempt the quiz, just click on the button below.', 'wp_courseware'));
								
								$showRetakeButton = true;
							}
							
							// No retakes left, so show a suitable message.
							else 
							{
								$showRetakeButton = false;
								$messageToShow .= wpautop(__('Unfortunately you have reached the maximum limit of attempts you are permitted for this quiz.', 'wp_courseware'));
							}
						}
						
						
						// The retake button (if allowed)
						if ($showRetakeButton)
						{
							$messageToShow .= sprintf('<div class="wpcw_fe_quiz_retake">
								<div class="wpcw_fe_submit_loader wpcw_loader">
									<img src="%simg/ajax_loader.gif" />
								</div>
								
								<a href="#" class="fe_btn fe_btn_completion btn_completion" data-wpcw_quiz="%d" data-wpcw_unit="%d">%s</a>						
							</div>',
								WPCW_plugin_getPluginPath(),
								$this->unitQuizDetails->quiz_id,
								$this->unitPost->ID,
								__('Retake Quiz', 'wp_courseware'));
						}

						// Finally show the message to the user.
						$html .= WPCW_UnitFrontend::message_createMessage_warning($messageToShow);						
					}
				}
					
				
				// User has completed this quiz, so we need to indicate if it's been marked or not. If it's not been marked
				// we show a message saying they are blocked until it's marked.
				if ($this->unitQuizProgress->quiz_needs_marking > 0) 
				{
					// Blocking quiz - show a status message saying that they can't continue until the quiz is graded. 
					if ('quiz_block' == $this->unitQuizDetails->quiz_type) {
						$html .= WPCW_UnitFrontend::message_createMessage_success($this->parentData->course_message_quiz_open_grading_blocking);
					} 
				}
			}
			
			// No quiz progress or we're ready for a retake, so show the quiz to be rendered for completion by the user.
			
			// Ensure that we render the quiz if we've not yet completed the quiz.
			if (!$this->check_quizzes_hasUserCompletedQuiz() || $this->check_quizzes_areWeWaitingForUserToRetakeQuiz()) 
			{
				$html .= $this->render_quizzes_handleQuizRendering();
			}
		}
		
		// Manually mark the unit as complete as there are no quiz questions.
		else 
		{
			// Render the message
			$html .= sprintf('
				<div class="wpcw_fe_progress_box_wrap" id="wpcw_fe_unit_complete_%d">
					<div class="wpcw_fe_progress_box wpcw_fe_progress_box_pending wpcw_fe_progress_box_updating">
						<div class="wpcw_fe_progress_box_mark">
							<img src="%s/ajax_loader.gif" class="wpcw_loader" style="display: none;" />
							<a href="#" class="fe_btn fe_btn_completion btn_completion" id="unit_complete_%d">%s</a>
						</div>
					%s</div>
				</div>', 
					$this->unitPost->ID, 
					WPCW_plugin_getPluginPath() . 'img',
					$this->unitPost->ID, __('Mark as Completed', 'wp_courseware'), 
					$this->parentData->course_message_unit_pending
				);
		}
			
		return $html;
	}
	
	
	/**
	 * Renders the specified question to HTML, reporting any errors.
	 * 
	 * @param Object $question The question to render as HTML.
	 * @param Array $resultsList The details of how to process results.
	 * @param Boolean $showErrorsOnForm If true, show the errors on the form, acts as an override to disable showing errors if they are not wanted. 
	 * @param Integer $questionNum The question number being rendered.
	 * @param Boolean $pagingMode Is this question being rendered in paging mode?
	 * @param Integer $questionIndex The index of the question within the quiz.
	 * 
	 * @return String The HTML to render the question as HTML.
	 */
	function render_quizzes_handleQuizRendering_singleQuestion($question, $resultsList, $showErrorsOnForm, $questionNum, $pagingMode = false, $questionIndex = 0)
	{
		// See if we want to show an answer
		$selectAnswer = false;
		if (isset($resultsList['answer_list'][$question->question_id])) {
			$selectAnswer = $resultsList['answer_list'][$question->question_id];
		}
		
		switch ($question->question_type)
		{
			case 'multi':
				$quObj = new WPCW_quiz_MultipleChoice($question);
				break;
				
			case 'open':					
				$quObj = new WPCW_quiz_OpenEntry($question);
				break;
				
			case 'upload':
				$quObj = new WPCW_quiz_FileUpload($question);
				break;
				
			case 'truefalse':
				$quObj = new WPCW_quiz_TrueFalse($question);
				break;
				
			case 'random_selection':
				die(__('This question cannot be rendered. This is an error.', 'wp_courseware'));
				break;							
				
			// Not expecting anything here... so not handling the error case.
			default:			
				die(__('Unexpected question type, aborting.', 'wp_courseware'));		
				break;
		}
		
		// Add extra CSS classes - this adds an index to the class to allow us to style certain questions.
		$quObj->cssClasses .= 'wpcw_fe_quiz_q_single_' . $questionIndex;
							
		// Use the objects to render the questions, showing an answer as wrong if appropriate.
		$showAsError = false;
		
		// Only worry about errors if actual data has been submitted.
		$errorToShow = false;
		if ($showErrorsOnForm && isset($_POST['submit']))
		{
			// Something went wrong
			if (isset($resultsList['error_answer_list'][$question->question_id]))
			{
				$errorToShow = $resultsList['error_answer_list'][$question->question_id]; 
				$showAsError = 'error';														
			}
			
			// No answer yet
			else if (!isset($resultsList['answer_list'][$question->question_id])) 
			{
				// Not missing if we're paging!
				if (!$pagingMode) {				
					$showAsError = 'missing';
				}
			}
			
			// Answer is wrong
			else if (isset($resultsList['wrong_answer_list'][$question->question_id])) {
				$showAsError = 'wrong';	
			}
		}
		
		// Use object to render
		return $quObj->renderForm_toString($this->unitQuizDetails, $questionNum, $selectAnswer, $showAsError, $errorToShow);
	}

	/**
	 * Shows the quiz for the unit, based on the type being shown.
	 * 
	 * @param Boolean $showErrorsOnForm If true, show the errors on the form, acts as an override to disable showing errors if they are not wanted.
	 * 
	 * @return String The HTML that renders the quiz for answering.
	 */
	function render_quizzes_handleQuizRendering($showErrorsOnForm = true)
	{
		// Hopefully not needed, but just in case.
		if (!$this->unitQuizDetails) {
			return false;
		}
		
		
		// Reload all raw answers user has created so far (if we have anything so far)
		if (!empty($this->unitQuizProgress) && 'incomplete' == $this->unitQuizProgress->quiz_paging_status) {
			$this->fetch_quizzes_loadRawAnswersSoFarForThisQuiz($this->unitQuizProgress->quiz_data);
		}
		
		// Render the wrapper for the quiz using the pending message section
		// Use the Quiz ID and Unit ID to validate permissions.
		$html = sprintf('<div class="wpcw_fe_quiz_box_wrap" id="wpcw_fe_quiz_complete_%d_%d">', $this->unitPost->ID, $this->unitQuizDetails->quiz_id);
		
			// Are we going to show the answers on a single for as part of the review?
			if ($this->check_paging_shouldWeShowReviewPage_rightNow())
			{
				// To do, show a message here about reviewing answers...
				$html .= WPCW_UnitFrontend::message_createMessage_warning(__('You can now review your answers before submitting them.',  'wp_courseware'));
			}
			
			// Use any raw selection data that we have so far to pre-fill answers.
			$resultsList = $this->unchecked_QuizAnswersToGrade;
		
				
			// enctype="multipart/form-data" for file uploads..
			// data-wpcw_expired="false" - this is used by the timer to indicate if the timer has expired or not. 
			$html .= sprintf('<form method="post" enctype="multipart/form-data" id="quiz_complete_%d_%d" data-wpcw_unit="%d" data-wpcw_quiz="%d" data-wpcw_expired="false">', 
				$this->unitPost->ID, $this->unitQuizDetails->quiz_id,
				$this->unitPost->ID, $this->unitQuizDetails->quiz_id
			);

				
			$html .= '<div class="wpcw_fe_quiz_box wpcw_fe_quiz_box_pending">';
			
				// #### 1 - Quiz Title - constant for all quizzes
				$html .= sprintf('<div class="wpcw_fe_quiz_title">%s</div>', $this->unitQuizDetails->quiz_title);
				
				// #### 2 - Pass mark - just needed for blocking quizes
				if ('quiz_block' == $this->unitQuizDetails->quiz_type)
				{
					$totalQs = count($this->unitQuizDetails->questions);
					$passQs  = ceil(($this->unitQuizDetails->quiz_pass_mark / 100) * $totalQs);
				
					$html .= '<div class="wpcw_fe_quiz_pass_mark">';
					$html .= sprintf(__('You\'ll need to correctly answer at least <b>%d of the %d</b> questions below (<b>at least %d%%</b>) to progress to the next unit.', 'wp_courseware'),
								$passQs, $totalQs, $this->unitQuizDetails->quiz_pass_mark);
					$html .= '</div>';
				}
				
				
				// #### 3 - The actual question form.
				if (!empty($this->unitQuizDetails->questions))
				{
					$questionNum 		= 1;
					$showQuestions 		= true;
										
					// Timer Mode
					if ('use_timer' == $this->unitQuizDetails->quiz_timer_mode)
					{						
						// We've not started the quiz yet, so we show the begin test button.
						if (empty($this->unitQuizProgress) || 'retake_waiting' == $this->unitQuizProgress->quiz_next_step_type)
						{
							$showQuestions = false;
							
							$html .= '<div class="wpcw_fe_quiz_q_hdr"></div>';
							
							// Begin Quiz
							$html .= sprintf('
									<div class="wpcw_fe_quiz_begin_quiz">
										<div class="wpcw_fe_quiz_begin_quiz_hdr">%s</div>
										<a href="#" class="fe_btn fe_btn_completion" id="wpcw_fe_quiz_begin_quiz" data-wpcw_quiz="%d" data-wpcw_unit="%d" >%s</a>
										
										<div class="wpcw_fe_submit_loader wpcw_loader">
											<img src="%simg/ajax_loader.gif" />
										</div>
									</div>',
							
									sprintf(__('You have <b>%s</b> to complete this quiz...', 'wp_coursware'), WPCW_time_convertMinutesToHumanLabel($this->unitQuizDetails->quiz_timer_mode_limit)), 
									$this->unitQuizDetails->quiz_id,
									$this->unitPost->ID,
									__('Begin Quiz...', 'wp_courseware'),
									WPCW_plugin_getPluginPath()
								);
							
						}
						
						// Show the timer with the remaining time.
						else 
						{
							// Total time in seconds for this quiz 
							$timerDetails_secondsLeft = $allowedTime = 60 * $this->unitQuizDetails->quiz_timer_mode_limit;
							
							// What time do we have left?
							$timeSoFar = strtotime($this->unitQuizProgress->quiz_started_date);
							$timeNow = current_time('timestamp');
							
							// We've started the quiz when the time so far is > 0
							if ($timeSoFar > 0) {
								$timerDetails_secondsLeft = $allowedTime - ($timeNow - $timeSoFar);
							}
							
							
							// Show the timer if we have time left.
							$html .= sprintf('<div id="wpcw_fe_timer_countdown" data-time_left="%d">%s</div>',  								
								$timerDetails_secondsLeft,
								WPCW_time_convertSecondsToHumanLabel($timerDetails_secondsLeft)
							);
						}
					}

					
					if ($showQuestions)
					{
						
						// We're paging, so check what question or questions to show next.
						if ($this->check_paging_areWePagingQuestions())
						{
							// Stores the index of the next question to show
							$questionToShowIndex = $this->fetch_paging_getQuestionIndex();
							$question = $this->fetch_paging_getQuestion($questionToShowIndex);							
							
							// Show progress of user through the questions.
							$html .= sprintf('<div class="wpcw_fe_paging_progress">%s</div>', 
								sprintf(__('Question %d of %d', 'wp_courseware'), 
								$questionToShowIndex+1, $this->fetch_paging_getQuestionCount())
							);
	
							// Header before questions (but show progress before the header line).
							$html .= '<div class="wpcw_fe_quiz_q_hdr"></div>';
							
							
							// Show the answer later button.
							if ($this->check_paging_shouldWeShowAnswerLaterButton())
							{
								$html .= sprintf('<div class="wpcw_fe_quiz_answer_later"><a href="#" class="fe_btn fe_btn_small fe_btn_navigation" id="wpcw_fe_quiz_answer_later">%s</a></div>', 
									__('Answer Later...', 'wp_courseware')
								);
							}
							
							// Render just this question
							$html .= $this->render_quizzes_handleQuizRendering_singleQuestion($question, $resultsList, $showErrorsOnForm, $questionToShowIndex+1, true, 0);
							
							
							// Work out what caption to show for the submit button. Change this if we're about to
							// submit the answers for the final question or review our answers.
							$buttonCaption = __('Save &amp; Next Question &raquo;', 'wp_courseware');
							
							
							// Are we showing the last incomplete question (and we're nearly complete)
							if ($this->unitQuizProgress && $this->unitQuizProgress->quiz_paging_incomplete <= 1 && $this->check_paging_isThisTheLastIncompleteQuestion($question->question_id))
							{
								// We appear to be on the last question. Are we going to review too?
								if ($this->check_paging_shouldWeShowReviewPage()) {
									$buttonCaption = __('Save &amp; Review Answers &raquo;', 'wp_courseware');
								}
								// No review, just submit
								else {
									$buttonCaption = __('Submit Answers', 'wp_courseware');
								}
							} 
							
							// Previous button - created if required. Using a button rather than a link to ensure the buttons look right when side-by-side.
							$buttonPreviousClicker = false;
							if ($this->check_paging_shouldWeShowPreviousButton())
							{
								$buttonPreviousClicker = sprintf('<input type="submit" class="fe_btn fe_btn_completion btn_completion" id="fe_btn_quiz_previous" name="previous_question" value="%s">', 
									__('&laquo; Previous Question', 'wp_courseware')
								);
							}						
							
							// #### 4A - The navigation buttons
							$html .= sprintf('<div class="wpcw_fe_quiz_submit wpcw_fe_quiz_submit_data">				
										%s								    
										<div class="wpcw_fe_submit_loader wpcw_loader">
											<img src="%simg/ajax_loader.gif" />
										</div>									
										%s						
										<input type="submit" class="fe_btn fe_btn_completion btn_completion" id="fe_btn_quiz_next" name="submit" value="%s">
									</div>', 
									
									WPCW_content_progressBar(0, 'wpcw_fe_upload_progress'), 
									WPCW_plugin_getPluginPath(),
									$buttonPreviousClicker,					
									$buttonCaption
								);	
						}
					
						// Are we showing the form with all questions?
						else 
						{
							// Header before questions
							$html .= '<div class="wpcw_fe_quiz_q_hdr"></div>';
							
							$questionIndex = 0;
							foreach ($this->unitQuizDetails->questions as $question) 
							{
								$html .= $this->render_quizzes_handleQuizRendering_singleQuestion($question, $resultsList, $showErrorsOnForm, $questionNum++, false, $questionIndex++);
							}		
	
							// #### 4B - The submit answers button. //<a href="#" class="fe_btn fe_btn_completion btn_completion" id="quiz_complete_%d_%d">%s</a>
							$html .= sprintf('<div class="wpcw_fe_quiz_submit wpcw_fe_quiz_submit_data">
					
										%s
									    
										<div class="wpcw_fe_submit_loader wpcw_loader">
											<img src="%simg/ajax_loader.gif" />
										</div>
										
										<input type="submit" class="fe_btn fe_btn_completion btn_completion" name="submit" value="%s">						
									</div>', 
									
									WPCW_content_progressBar(0, 'wpcw_fe_upload_progress'), 
									WPCW_plugin_getPluginPath(), 					
									__('Submit Answers', 'wp_courseware')
								);	
						} // end of check for paging.
					} 	// end of check of showing questions (e.g. timer mode)
				} // end of question check
				
		
			$html .= '</div>'; // .wpcw_fe_quiz_box
			$html .= '</form>'; 
		$html .= '</div>'; // .wpcw_fe_quiz_box_wrap
		
		return $html;
	}
	
	
	
	/**
	 * Creates a box to show that a unit has been completed.
	 * 
	 * @param Boolean $disableNextButton If true, then disable the next button (as the user can't continue).
	 * 
	 * @return String The HTML for the navigation box.
	 */
	function render_navigation_getNavigationBox($disableNextButton = false)
	{
		$nextAndPrev = $this->userProgress->getNextAndPreviousUnit($this->unitPost->ID);
		$html = false;	
		
		if ($nextAndPrev['prev'] > 0) 
		{
			$html .= sprintf('<a href="%s" class="fe_btn fe_btn_navigation">&laquo; %s</a> ',
				get_permalink($nextAndPrev['prev']),
				__('Previous Unit', 'wp_courseware')
			);
		}
		
		if ($nextAndPrev['next'] > 0) 
		{
			// Replace the URL if we're disabling the next button
			//$urlToUse = ($disableNextButton ? '#' : get_permalink($nextAndPrev['next']));
			//($disableNextButton ? 'fe_btn_navigation_disabled' : ''), // Add a disabled class if not active.
			
			$html .= sprintf('<a href="%s" class="fe_btn fe_btn_navigation %s">%s &raquo;</a>',
				get_permalink($nextAndPrev['next']),
				false,
				__('Next Unit', 'wp_courseware')
			);
		}
		
		// Only return navigation if we have any links.
		if ($html) {	
			return sprintf('<div class="wpcw_fe_progress_box_wrap">
				<div class="wpcw_fe_navigation_box">
					%s
				</div>
			</div>', $html);
		}
		
		return false;
	}
	
	
	/**
	 * Renders the results of the questions, grouped by tag as HTML for the frontend of the site.
	 * @return Array The list of formatted HTML lines to render the question results by tag.
	 */
	function render_quizzes_questionResultsByTag_forFrontend()
	{
		$html = array();		
		$tagBucketList = $this->fetch_quizzes_questionResultsByTag();		
		 
		if (!empty($tagBucketList))
		{
			foreach ($tagBucketList as $tagID => $tagDetails)
			{
				// Got open questions
				if ($tagDetails['question_open_count'] > 0)
				{
					$html[] = sprintf('<b>%s:</b> %s', 
						$tagDetails['tag_details']->question_tag_name, 
						sprintf(__('Your grade is %d%%', 'wp_courseware'), $tagDetails['score_total'])
					);
				}
				
				// Just closed questions, show out of message.
				else 
				{
					$html[] = sprintf('<b>%s:</b> %s', $tagDetails['tag_details']->question_tag_name, 
						sprintf(__('%d out of %d correct (%d%%)', 'wp_courseware'),
							$tagDetails['score_correct_questions'],
							$tagDetails['question_count'],
							$tagDetails['score_total']
						)
					);	
				} // end of question type check
			} // end foreach
			
		}
				
		return $html;
	}
	
	/**
	 * Fetches the details of the question results, grouped by tag for this user.
	 * Data is returned as an array with the calculations so that that it can 
	 * be used in any render that's needed (HTML, PDF or email)
	 * 
	 * @return Array A nested array containing a list of tags, the tag details, and a list of the scores summarising how the user did for each tag. 
	 */
	function fetch_quizzes_questionResultsByTag()
	{
		$tagBucketList = array();
		
		// Use the cached version if we have it.
		if ($this->cached_resultsByTag) {
			return $this->cached_resultsByTag;
		}
		
		// ### 1 - Check to make sure we have questions
		if (empty($this->unitQuizDetails) || empty($this->unitQuizDetails->questions) || empty($this->unitQuizProgress->quiz_data)) {
			$this->cached_resultsByTag = $tagBucketList;
			return $tagBucketList;
		}
		
		
		// ### 1A - Ensure that the questions all have tags (random questions will not have these tags, hence fetching 
		// them here).
		foreach ($this->unitQuizDetails->questions as $questionID => $questionObj)
		{
			// Tags should always exist for normal questions. So this will just be triggered for randoms.
			// This is as practically efficient as we can make it.
			if (!isset($questionObj->tags))  {
				$this->unitQuizDetails->questions[$questionID]->tags = WPCW_questions_tags_getTagsForQuestion($questionID);
			}
		}
		 		
		// ### 2 - Build a list of tags used by the questions
		foreach ($this->unitQuizDetails->questions as $questionID => $questionObj)
		{
			// ### 3 - Got some tags for this question
			if (!empty($questionObj->tags))
			{
				$userResponse = array('got_right' =>  false);
						
				// ### 4 - Get the question from the user's responses. Can't assume it's
				// there, hence the initialisation above.						
				if (isset($this->unitQuizProgress->quiz_data[$questionID])) {
					$userResponse = $this->unitQuizProgress->quiz_data[$questionID]; 
				} 
				
				$isQuestionRight = ('yes' == $userResponse['got_right']);
				
				
				// ###Ê5 - Work out if an open-ended question or not.
				$isQuestionOpen = false;
				if ('open' == $questionObj->question_type || 'upload' == $questionObj->question_type) {
					$isQuestionOpen = true;	
				}
				
				// ### 5 - For each tag, add the tag details to the list, and then
				// add the association for this question.
				foreach ($questionObj->tags as $tagObj)
				{
					// Is the tag already in the bucket?
					if (isset($tagBucketList[$tagObj->question_tag_id]))
					{
						// Append the response from the user.
						$tagBucketList[$tagObj->question_tag_id]['question_responses'][$questionID] = $userResponse;

						// Increase question count
						$tagBucketList[$tagObj->question_tag_id]['question_count']++;
						
						// Increase correct count (if correct)
						$tagBucketList[$tagObj->question_tag_id]['score_correct_questions']  += ($isQuestionRight ? 1 : 0);
						
						// Increase question open count 
						$tagBucketList[$tagObj->question_tag_id]['question_open_count']  += ($isQuestionOpen ? 1 : 0);
					}
					
					// Nope, add the tag to the bucket.
					else
					{
						$tagBucketList[$tagObj->question_tag_id] = array(
							// Question type only needed to be determined for the first tag.
							'question_open_count' 		=> ($isQuestionOpen ? 1 : 0),						
						
							// Used for score calculation if open ended questions
							'question_count' 			=> 1,						
						
							// Is the question right? (this will be false if open ended)							
							'score_correct_questions' 	=> ($isQuestionRight ? 1 : 0),
						
							// Stores the total grade for use later
							'score_total' 				=> 0,
						
							// Stores the details of the tag
							'tag_details'				=> $tagObj,					 
						
							// Stores the ID of the question => the score from the user. Build up a list of all
							// of these, hence using an array.
							'question_responses'		=> array($questionID => $userResponse) 	
						);
					}
				} // end of foreach
				
			} // Check of question tags
		} // end of questionforeach
		
		
		// ### 6 - Now we need to calculate the total for each of these tags using the questions responses we have.
		if (!empty($tagBucketList))
		{
			foreach ($tagBucketList as $tagID => $tagDetails)
			{
				$tagBucketList[$tagID]['score_total'] = WPCW_quizzes_calculateGradeForQuiz($tagDetails['question_responses'], 0);
				
				// Don't need responses now.
				unset($tagBucketList[$tagID]['question_responses']);
			}
		}
		
		// Update cache to save time later.
		$this->cached_resultsByTag = $tagBucketList;
		
		return $tagBucketList;
	}
	
	
	/**
	 * Creates a box to show an error message
	 * 
	 * @param String $message The message to show.
	 * @return String The error message as a formatted string.
	 */
	static function message_createMessage_error($message)
	{
		return sprintf('<div class="wpcw_fe_progress_box_wrap"><div class="wpcw_fe_progress_box wpcw_fe_progress_box_error">%s</div></div>', $message);
	}
	
	
	/**
	 * Creates a box to show a success message
	 * 
	 * @param String $message The message to show.
	 * @return String The success message as a formatted string.
	 */
	static function message_createMessage_success($message)
	{
		return sprintf('<div class="wpcw_fe_progress_box_wrap"><div class="wpcw_fe_progress_box wpcw_fe_progress_box_success">%s</div></div>', $message);
	}
	
	/**
	 * Creates a box to show an warning message
	 * 
	 * @param String $message The message to show.
	 * @return String The success message as a formatted string.
	 */
	static function message_createMessage_warning($message)
	{
		return sprintf('<div class="wpcw_fe_progress_box_wrap"><div class="wpcw_fe_progress_box wpcw_fe_progress_box_warning">%s</div></div>', $message);
	}
	
	/**
	 * Create a simple error if something goes wrong. 
	 */
	static function message_error_getCompletionBox_error()
	{
		return WPCW_UnitFrontend::message_createMessage_error(__('An error occured, so your unit progress was not updated. Please refresh the page and try again.', 'wp_courseware'));
	}
	
	
	/**
	 * Message to return if a user is not logged in.
	 * @return String The message to return if the user is not logged in.
	 */
	function message_user_notLoggedIn()
	{
		// Return no access error without content
		$newContent = WPCW_UnitFrontend::message_createMessage_error($this->parentData->course_message_unit_not_logged_in);
		
		// Modify the content if needed.
		return apply_filters('wpcw_front_unit_content_notloggedin', $newContent, $this->parentData, $this->unitPost);
	}
	
	
	/**
	 * Returns the message about the user not being able to access the course.
	 * @return String The message about the user not being able to access this course.
	 */
	function fetch_message_user_cannotAccessCourse()
	{
		return $this->parentData->course_message_unit_no_access;
	}
	
	/**
	 * Message to return if a user is not allowed to access course.
	 * @return String The message to return if the user is not allowed to access course.
	 */
	function message_user_cannotAccessCourse()
	{
		// Return no access error without content
		$newContent = WPCW_UnitFrontend::message_createMessage_error($this->parentData->course_message_unit_no_access);
		
		// Modify the content if needed.
		return apply_filters('wpcw_front_unit_content_noaccess', $newContent, $this->parentData, $this->unitPost);
	}

	/**
	 * Message to return if a user is not allowed to access a unit.
	 * @return String The message to return if the user is not allowed to access a unit.
	 */
	function message_user_cannotAccessUnit()
	{
		// Return no access error without content
		$newContent = WPCW_UnitFrontend::message_createMessage_error($this->parentData->course_message_unit_not_yet);
		
		// Modify the content if needed.
		return apply_filters('wpcw_front_unit_content_notyet', $newContent, $this->parentData, $this->unitPost);
	}		
	
	
	/**
	 * Extracts the checked answers and saves the question results for this user 
	 * so that it can be stored in the database.
	 * 
	 * @param Array $checkedAnswerList The list of answers that have been cleaned and checked. 
	 * 
	 * @return Array The list of status data for progress to store.
	 */
	function fetch_quizzes_extractQuizStatusFromAnswers($checkedAnswerList)
	{
		$fullDetails = array();
		
		foreach ($this->unitQuizDetails->questions as $singleQuestion)
		{
			$qItem = array();				
			$qItem['title'] = $singleQuestion->question_question;
	
			$openEndedQuestion = false;
			
			// ### A - We might not have an answer for this question. So set up the defaults.
			if (!isset($checkedAnswerList[$singleQuestion->question_id]))
			{
				$qItem['correct']   		= false;
				$qItem['got_right'] 		= false;
				$qItem['their_answer']  	= false;
				$qItem['their_answer_raw']  = false;				
				$qItem['is_incomplete']  	= true;
			}
			
			// ### B - We do have an answer for this question
			else 
			{
				// We know we have enough answers at this point, so know
				switch ($singleQuestion->question_type)
				{
					// There's definitely a right or wrong answer, so determine that now.
					case 'truefalse':
					case 'multi':
							$qItem['their_answer'] 	= $this->check_quizzes_getCorrectAnswer($singleQuestion, $checkedAnswerList[$singleQuestion->question_id]);			
						break;
						
					// Uploaded files and open-ended questions need marking, so it's just their raw answer.
					case 'upload':
					case 'open':
							$openEndedQuestion = true;
							$qItem['their_answer'] 	= $checkedAnswerList[$singleQuestion->question_id];
						break;
				}
				
				// Store raw answer (used by paging questions to mark later)
				$qItem['their_answer_raw']  = $checkedAnswerList[$singleQuestion->question_id];
				
				
				// If a survey, there are no correct answers.
				if ('survey' == $this->unitQuizDetails->quiz_type || $openEndedQuestion)
				{
					$qItem['correct']   = false;
					$qItem['got_right'] = false;
				}
				
				// Normal quiz with multiple-choice.
				else 
				{		
					// Get the correct answer
					$qItem['correct'] 		= $this->check_quizzes_getCorrectAnswer($singleQuestion);
					
					// Did the answers match?
					$qItem['got_right']		= ($qItem['their_answer'] == $qItem['correct'] ? 'yes' : 'no');				
				}
				
				// We have an answer, so it's not incomplete.
				$qItem['is_incomplete'] = false;
			}
			
			// Add the details back to the details to return.
			$fullDetails[$singleQuestion->question_id] = $qItem;
			
		} // end of foreach loop
		
		return $fullDetails;
	}
	
	
	/**
	 * Save the user's quiz progress to the database using their verbose answers.
	 * 
	 * @param Array $resultDetails The full result details for the user who's completed the quiz.
	 * @param Array $checkedAnswerList The full list of checked answers.
	 */
	function update_quizzes_saveUserProgress_completeQuiz($resultDetails, $checkedAnswerList)
	{			
		global $wpdb, $wpcwdb;
		$wpdb->show_errors();
		
		$data = array();
			
		$data['quiz_correct_questions'] = count($resultDetails['correct']);
		$data['quiz_question_total'] 	= count($this->unitQuizDetails->questions);
		$data['quiz_completed_date']  	= current_time('mysql');
		$data['user_id'] 				= $this->currentUserID;
		$data['unit_id'] 				= $this->unitPost->ID;
		$data['quiz_id'] 				= $this->unitQuizDetails->quiz_id;
		
		// Store which questions need marking
		$data['quiz_needs_marking'] 		= false;
		$data['quiz_needs_marking_list'] 	= false;
		
		// Calculate how long the quiz took, if it was timed.
		if (!empty($this->unitQuizProgress) && '0000-00-00 00-00-00' != $this->unitQuizProgress->quiz_started_date)
		{
			$data['quiz_completion_time_seconds'] = (strtotime($data['quiz_completed_date']) - strtotime($this->unitQuizProgress->quiz_started_date));
		}
		
		// Generate a full list of the quiz and the answers given.
		$fullDetails = $this->fetch_quizzes_extractQuizStatusFromAnswers($checkedAnswerList); 
		
		
		// Track how many questions need marking 
		$data['quiz_needs_marking'] 		= 0;
		if (isset($resultDetails['needs_marking']))
		{  
			// Just check that we have some items that need marking.
			if (is_array($resultDetails['needs_marking'])) 
			{
				$data['quiz_needs_marking_list'] = serialize(array_keys($resultDetails['needs_marking'])); // Only store the IDs.
				
				// If we have items that need answering, copy the number of items to be answered.
				if (!empty($resultDetails['needs_marking'])) {
					$data['quiz_needs_marking'] = count($resultDetails['needs_marking']);
				}
			} 
			
			else {
				$data['quiz_needs_marking_list'] = serialize(array()); // Creates an empty list.
			}	
		
		}
			
		// Use serialised data for storing full results.
		$data['quiz_data'] 	= serialize($fullDetails);
		$data['quiz_grade'] = WPCW_quizzes_calculateGradeForQuiz($fullDetails, $data['quiz_needs_marking']);
		
		
		$SQL = $wpdb->prepare("
			SELECT * 
			FROM $wpcwdb->user_progress_quiz
			WHERE user_id = %d
			  AND unit_id = %d
			  AND quiz_id = %d
			ORDER BY quiz_attempt_id DESC 
			LIMIT 1
		", $this->currentUserID, $this->unitPost->ID, $this->unitQuizDetails->quiz_id);
			
		$weNeedToUpdateTheMostRecentProgress = false;
		
		// Ensure this is marked as the latest and considered complete.
		$data['quiz_is_latest']     = 'latest';
		$data['quiz_paging_status'] = 'complete';
		
		
		// Already exists, so increment the quiz_attempt_id
		// If it doesn't exist, we'll just use the database default of 0.
		if ($existingProgress = $wpdb->get_row($SQL))
		{
			// Is the last attempt incomplete? If so, then we want to update the existing progress
			// to avoid incorrect duplication of attempts.
			if ('incomplete' == $existingProgress->quiz_paging_status)
			{
				$weNeedToUpdateTheMostRecentProgress = true;	
				
				// Retain attempt ID from previous item
				$data['quiz_attempt_id'] = $existingProgress->quiz_attempt_id;
			}
			
			// We're inserting, so need to increase attempt ID.
			else {
				$data['quiz_attempt_id'] = $existingProgress->quiz_attempt_id + 1;
			}
			
			// Regardless, we need to mark all previous progress items as not being the latest.
			$SQL = $wpdb->prepare("
				UPDATE $wpcwdb->user_progress_quiz
				SET quiz_is_latest = ''
				WHERE user_id = %d
				  AND unit_id = %d
				  AND quiz_id = %d
			", $this->currentUserID, $this->unitPost->ID, $this->unitQuizDetails->quiz_id);
			$wpdb->query($SQL);
			
		}
		
		// We're updating the last progress to be complete now.
		if ($weNeedToUpdateTheMostRecentProgress) {
			$SQL = arrayToSQLUpdate($wpcwdb->user_progress_quiz, $data, array('user_id', 'unit_id', 'quiz_id', 'quiz_attempt_id'));
		} 
		// No incomplete records before.
		else {
			$SQL = arrayToSQLInsert($wpcwdb->user_progress_quiz, $data);
		}
				
		$wpdb->query($SQL);
				
		// #### Update internal store of results once saved to database. 
		$this->unitQuizProgress = WPCW_quizzes_getUserResultsForQuiz($this->currentUserID, $this->unitPost->ID, $this->unitQuizDetails->quiz_id);
		
		// Try to work out which questions are still incomplete.
		$this->updateInternalVariable_quizzes_getIncompleteQuestions(); 
	}
	
	
	
	
	
	/**
	 * Return true if the quiz details are valid.
	 */
	function check_quizzes_validQuizDetails()
	{
		return ($this->unitQuizDetails && $this->unitQuizDetails->questions && count($this->unitQuizDetails) > 0);
	}
	
	
	/**
	 * Do we have valid parent data?
	 * @return Boolean True if valid data, false otherwise.
	 */
	function check_unit_doesUnitHaveParentData()
	{
		return (!empty($this->parentData));
	}
	
	
	/**
	 * Check that the specific quiz matches the quiz for this unit.
	 * @param Integer $quizID The ID of the quiz to check.
	 * @return Boolean True if the quiz matches, false otherwise.
	 */
	function check_quizzes_isQuizValidForUnit($quizID)
	{
		return $this->check_quizzes_validQuizDetails() && $quizID > 0 && $this->unitQuizDetails->quiz_id == $quizID;
	}	
	
	
	/**
	 * Check to see if the user has completed the quiz for this unit.
	 * @return Boolean True if they have completed the quiz (or there is no quiz), false otherwise.
	 */
	function check_quizzes_hasUserCompletedQuiz()
	{
		// No quiz details
		if (!$this->unitQuizDetails) {
			return true;
		}
		
		// No quiz progress - so it's not been attempted.
		if (!$this->unitQuizProgress) {
			return false;
		}
		
		// Been attempted, but it's not yet complete.
		if ('complete' == $this->unitQuizProgress->quiz_paging_status) {
			return true;
		}
		
		// Otherwise incomplete.
		return false;
	}	
	
	
	
	/**
	 * In paging mode, check the answers that have been provided and update the status in the 
	 * database if we're still progressing through getting answers to all questions.
	 * 
	 * @param Array $potentialAnswers The potential answers that need checking. 
	 * 
	 * @return Boolean Returns true when we have all of our answers, and we're allowed to carry on for grading.
	 */
	function check_quizzes_canWeContinue_checkAnswersFromPaging($potentialAnswers)
	{		
		global $wpdb, $wpcwdb;
		$wpdb->show_errors();
		
		// Check the raw data to see what questions we have.
		$resultsList = $this->check_quizzes_canWeContinue_extractAnswerData($potentialAnswers);
				
		// Need to build the data for the quiz progress, which should show that the quiz hasn't been graded as yet.
		$data = array();		
		$data['user_id'] 				= $this->currentUserID;
		$data['unit_id'] 				= $this->unitPost->ID;
		$data['quiz_id'] 				= $this->unitQuizDetails->quiz_id;
		
		$data['quiz_completed_date']  	= current_time('mysql');
		$data['quiz_correct_questions'] = 0;
		$data['quiz_question_total'] 	= count($this->unitQuizDetails->questions);
			
		// Total number of incomplete questions matches one less than question count (-1 to remove this Q). 
		$data['quiz_paging_incomplete'] = $data['quiz_question_total']-1; 		
		
		// Showing the next question if we've got here.
		$data['quiz_paging_next_q'] 	= 1;
		
		// We're still working on this quiz, so it's incomplete.
		$data['quiz_paging_status'] 	= 'incomplete';

		// Set to false, we'll fill it in later.
		$data['quiz_data']				= false;
		
		
		// Do we have a progress item already? Such as a prior attempt or previous question (when paging).
		$SQL = $wpdb->prepare("
			SELECT * 
			FROM $wpcwdb->user_progress_quiz
			WHERE user_id = %d
			  AND unit_id = %d
			  AND quiz_id = %d
			ORDER BY quiz_attempt_id DESC 
			LIMIT 1
		", $this->currentUserID, $this->unitPost->ID, $this->unitQuizDetails->quiz_id);
			
		$updateExistingProgress = false;
		
		// Already exists, so we just need to update the progress with where we're at.
		// If it doesn't exist, we'll just use the database default of 0.		
		if ($existingProgress = $wpdb->get_row($SQL))
		{
			// Got an existing incomplete paging progress, carry on working with it.
			if ('incomplete' == $existingProgress->quiz_paging_status)
			{
				$updateExistingProgress = true;
				
				// Need to progress to next item - BUT If the index is goes to the end of the list, 
				// then don't change it. This allows to detect if we're running through skipped questions.
				if ($existingProgress->quiz_paging_next_q < $existingProgress->quiz_question_total) {				
					$data['quiz_paging_next_q'] = $existingProgress->quiz_paging_next_q + 1;
				} else {
					$data['quiz_paging_next_q'] = $existingProgress->quiz_paging_next_q;
				}
				
				// Migrate data from the existing progress that's important.
				foreach ($existingProgress as $fieldName => $fieldDetails)
				{
					if (!isset($data[$fieldName]))  {
						$data[$fieldName] = $fieldDetails;
					}
				}
			}
			
			// Got an existing complete paging progress, so we need to unmark it as the latest. 
			// We're now going to insert a new record that is the latest.
			else
			{
				// Mark all previous progress items as not being the latest.
				$SQL = $wpdb->prepare("
					UPDATE $wpcwdb->user_progress_quiz
					SET quiz_is_latest = ''
					WHERE user_id = %d
					  AND unit_id = %d
					  AND quiz_id = %d
				", $this->currentUserID, $this->unitPost->ID, $this->unitQuizDetails->quiz_id);
				$wpdb->query($SQL);
				
				// Ensure this new progress marked as the latest.
				$data['quiz_is_latest']  = 'latest';
				$data['quiz_attempt_id'] = $existingProgress->quiz_attempt_id + 1;				
			}
		}
		
		// Do Insert or Update as needed.
		if ($updateExistingProgress) 
		{
			// Fetch the existing quiz data, as we need to merge the new complete data with
			// the existing data.
			$data['quiz_data']	= maybe_unserialize($existingProgress->quiz_data);
			if (!empty($data['quiz_data']))
			{
				$newData = $this->fetch_quizzes_extractQuizStatusFromAnswers($resultsList['answer_list']);
				if (!empty($newData))
				{
					// We're assuming that if the new data is complete, then we overwrite the old
					// data, as that should be incomplete. 
					foreach ($newData as $thisQuestionID => $thisAnswerDetails)
					{
						// Got a new complete item, so overwrite details
						if ($thisAnswerDetails['is_incomplete'] == 0) {
							$data['quiz_data'][$thisQuestionID] = $thisAnswerDetails;
						} 
					}
				}
				
				// Count up the total number of incomplete questions left.
				$data['quiz_paging_incomplete'] = 0;
				foreach ($data['quiz_data'] as $thisQuestionID => $thisAnswerDetails)
				{
					// Found another question that's not complete, so add 1 to count of incomplete questions.
					if ($thisAnswerDetails['is_incomplete'] == 1) {
						$data['quiz_paging_incomplete']++;
					}
				}
				
			}
			
			// Not no prior data, so use all of the defaults. 
			else
			{
				$data['quiz_data'] = $this->fetch_quizzes_extractQuizStatusFromAnswers($resultsList['answer_list']);
			}
			
			// Need to reserialize the data
			$data['quiz_data'] = serialize($data['quiz_data']);
			
			$SQL = arrayToSQLUpdate($wpcwdb->user_progress_quiz, $data, array('user_id', 'unit_id', 'quiz_id', 'quiz_attempt_id'));
		} 
		
		else
		{
			// Extract all quiz data including placeholders for questions not yet answered.
			$data['quiz_data'] 	= serialize($this->fetch_quizzes_extractQuizStatusFromAnswers($resultsList['answer_list']));	
			
			$SQL = arrayToSQLInsert($wpcwdb->user_progress_quiz, $data);
		}
		
		$wpdb->query($SQL);
				
		// Update internal store of results once saved to database. 
		$this->unitQuizProgress = WPCW_quizzes_getUserResultsForQuiz($this->currentUserID, $this->unitPost->ID, $this->unitQuizDetails->quiz_id);
		
		// Try to work out which questions are still incomplete.
		$this->updateInternalVariable_quizzes_getIncompleteQuestions();

		// Reload any raw answers that we have into the internal raw answer object.
		$this->fetch_quizzes_loadRawAnswersSoFarForThisQuiz($data['quiz_data']);
		
		
		// We can only continue if we no longer have any incomplete questions.
		return $this->unitQuizProgress->quiz_paging_incomplete == 0 && !$this->check_paging_shouldWeShowReviewPage();
	}
	
	
	
	/**
	 * In single-page quiz mode, check the answers that have been provided and update the status in the 
	 * database.
	 * 
	 * @param Array $potentialAnswers The potential answers that need checking. 
	 * 
	 * @return Boolean Returns true when we have all of our answers, and we're allowed to carry on for grading.
	 */	
	function check_quizzes_canWeContinue_checkAnswersFromOnePageQuiz($potentialAnswers)
	{
		$resultsList = array(
			'answer_list'		=> array(),
			'wrong_answer_list'	=> array(),
			'error_answer_list'	=> array(),
		);
		
		// ### 1 - Extract a list of actual answers from the potential answers. 
		$resultsList = $this->check_quizzes_canWeContinue_extractAnswerData($potentialAnswers);
		
		
		// ### 2 - Save the progress so far. This updates the internal progress variable too. No grading goes on
		// but it stores the answers to be graded in the next bit of code.
		$this->unchecked_QuizAnswersToGrade = $resultsList;
		
		
		// ### 3 - Has the timer expired? If so, then do no further checking and return true too allow the code 
		// to continue
		if ('expired' == WPCW_arrays_getValue($potentialAnswers, 'timerexpired')) {
			return true;
		}
		
		$answerCountSoFar = count($resultsList['answer_list']);
		
		
		// ### 4 - See if we expect uploads, because that may explain the difference in answers v.s. expected answer counts if we're
		// paging and now on the final review page. Uploads are a special case, and we add any missing ones back from what's been saved.
		if ($this->unitQuizDetails->want_uploads)
		{
			foreach ($this->unitQuizDetails->questions as $questionID => $questionDetails)
			{
				// Got an upload question where the answer is not in the final count
				if ('upload' == $questionDetails->question_type && !isset($resultsList['answer_list'][$questionID]) && isset($this->unitQuizProgress->quiz_data[$questionID]))
				{
					// Update internal variable of data we have, as we'll need this for saving
					$this->unchecked_QuizAnswersToGrade['answer_list'][$questionID] = $this->unitQuizProgress->quiz_data[$questionID]['their_answer_raw'];
					
					$answerCountSoFar++;
				}
			}
		}
		
		// ### 5 - Check that we have enough answers given how many questions there are.
		// If there are not enough answers, then re-render the form with the answered questions 
		// marked, and highlight the fields that have errors.
		if ($this->unitQuizDetails->questions &&  $answerCountSoFar < count($this->unitQuizDetails->questions)) 
		{
			// Error - not all questions are answered
			echo WPCW_UnitFrontend::message_createMessage_error(__('Please provide an answer for all of the questions on this page.', 'wp_courseware'));
			
			// This will trigger the form to show again with missing answers, using the ansers the user provided
			// through what has been stored in $this->unchecked_QuizAnswersToGrade
			
			// User may not continue - as quiz is not complete.
			return false; 
		}	

		
		return true;
	}
	
	
	/**
	 * Fetch the answer data and clean it up based on the type of question
	 * @param Array $potentialAnswers The potential answers that need checking.
	 * @return Array A list of the results.
	 */
	function check_quizzes_canWeContinue_extractAnswerData($potentialAnswers)
	{
		$resultsList = array(
			'answer_list'		=> array(),
			'wrong_answer_list'	=> array(),
			'error_answer_list'	=> array(),
		);
		
		// #### 1 - Extract a list of actual answers from the potential answers. There will
		// be some noise in that data. Check the raw data to see what questions we have.
		foreach ($potentialAnswers as $key => $value)
		{
			// Only considering answers to questions. Format of answer field is:
			// question_16_truefalse_48 (first ID is quiz, 2nd ID is question, middle string
			// is the question type.
			if (preg_match('/^question_(\d+)_([a-z]+)_(\d+)$/', $key, $matches)) 
			{
				$quizID     	= $matches[1];
				$questionID 	= $matches[3];
				$questionType 	= $matches[2];
				
				// Again, check that answer matches quiz we're expecting.
				// Probably a little paranoid, but it's worth checking
				// to ensure there's nothing naughty going on.			
				if ($quizID != $this->unitQuizDetails->quiz_id) {
					continue;  
				}
				
				// Clean up the submitted data based on the type of quiz using the static checks in each 
				// of the questions (to save loading whole class). If the data is valid, add the valid 
				// answer to the list of fully validate danswers.
				switch ($questionType)
				{
					case 'multi':
							$resultsList['answer_list'][$questionID] = WPCW_quiz_MultipleChoice::sanitizeAnswerData($value);
						break;
				
					case 'truefalse':
							$resultsList['answer_list'][$questionID] = WPCW_quiz_TrueFalse::sanitizeAnswerData($value);
						break;
						
					case 'open':
							$resultsList['answer_list'][$questionID] = WPCW_quiz_OpenEntry::sanitizeAnswerData($value);
						break;
	
					// Ignore uploads as a $_POST field, simply because the files should be stored in $_FILES
					// not in $_POST. So if we have a file in $_FILES, that's clearly an issue.
					case 'upload':					
						break;
				}
	
			} // end of question check
		} // end of potential answers loop
		
		
		// ### 2 - Check for file uploads if the quiz requires them. Only check for uploads
		// if the quiz details say there should be some uploads.
		if ($this->unitQuizDetails->want_uploads)
		{
			$uploadResultList = WPCW_quiz_FileUpload::validateFiles($_FILES, $this->unitQuizDetails);
			
			// Merge the valid results.
			// Basically if a file has been uploaded correctly, that answer is marked as being set.
			if (count($uploadResultList['upload_valid']) > 0) {			
				$resultsList['answer_list'] = $resultsList['answer_list'] + $uploadResultList['upload_valid'];
			}
			
			// Merge the error results
			if (count($uploadResultList['upload_errors']) > 0) {
				$resultsList['error_answer_list'] = $resultsList['error_answer_list'] + $uploadResultList['upload_errors'];
			}
			
			
		}
		
		return $resultsList;
	}
	
	
	/**
	 * Checks the supplied answers to see which questions are correct.
	 * 
	 * @return Boolean Returns true if the trainee can continue (either passed in blocking, or survey/non-blocking), false otherwise.
	 */ 
	function check_quizzes_gradeQuestionsForQuiz()
	{
		// Use the quiz answers that need grading, stored in the local variable.
		$resultsList = $this->unchecked_QuizAnswersToGrade;
		
				
		$resultDetails = array(
			'correct'	=> array(),
			'wrong'		=> array()
		);
		
		// Flag to indicate if grading is needed before the user continues.
		$gradingNeeded = false;
		$gradingNeededBeforeContinue = false;
		
		// If true, then we notify the user of the grade.
		$quizGradeNotificationNeeded = false;
		
		// ### 3 - Do we need to check for correct answers?
		if ('survey' == $this->unitQuizDetails->quiz_type)
		{
			// Show answers only if this is a survey where we want trainees to see the results.
			// @since V2.90
			$this->unitQuizDetails->quiz_show_answers = 'hide_answers';
			if ('show_responses' == $this->unitQuizDetails->quiz_show_survey_responses) { 
				$this->unitQuizDetails->quiz_show_answers = 'show_answers';
			}
	
			// No answers to check. Say thanks
			echo WPCW_UnitFrontend::message_createMessage_success(__('Thank you for your responses. This unit is now complete.', 'wp_courseware'));		
		}
		
		// #### Quiz Mode - so yes, we do check for correct answers.
		// We should answers for all questions by this point.
		else 
		{
			$resultDetails = $this->check_quizzes_checkForCorrectAnswers($resultsList['answer_list']);
			
			
			// #### Step A - have open-ended questions that need marking.
			if (!empty($resultDetails['needs_marking']))
			{ 
				$gradingNeeded = true;
				$courseDetails = WPCW_courses_getCourseDetails($this->unitQuizDetails->parent_course_id);
				
				// Non-blocking quiz - so allowed to continue, but will be graded later. 
				if ('quiz_block' == $this->unitQuizDetails->quiz_type) 
				{
					// Grading is needed before they continue, but don't want them to re-take the quiz.
					$gradingNeededBeforeContinue = true;
				} 
			}
			
			// #### Step B - we have no-open-ended questions, just T/F or Multiple-choice
			else 
			{
				// Copy over the wrong answers.
				$resultsList['wrong_answer_list'] = $resultDetails['wrong'];
				
				// Some statistics
				$correctCount 		= count($resultDetails['correct']); 
				$totalQuestions    	= count($this->unitQuizDetails->questions);		
				$correctPercentage  = number_format(($correctCount / $totalQuestions)*100, 1);
				
				// Non-blocking quiz. 
				if ('quiz_noblock' == $this->unitQuizDetails->quiz_type)
				{	
					// Store user quiz results  
					// 2014-07-11 - Deprecated. Now being generated by the message summary for quizzes.
					/*
					echo WPCW_UnitFrontend::message_createMessage_success(sprintf(__('You got <b>%d out of %d (%d%%)</b> questions correct! This unit is now complete.', 'wp_courseware'),
							$correctCount, $totalQuestions, $correctPercentage
						))*/
						
					// Notify the user of their grade.
					$quizGradeNotificationNeeded = true;
				} 
				
				// Blocking quiz (quiz_type == quiz_block).
				else 
				{
					// 2014-01-15 - Added ceil() calculation to ensure calculation is consistent when showing the quiz title 
					// above around the .wpcw_fe_quiz_title div.
					$minPassQuestions  = ceil(($this->unitQuizDetails->quiz_pass_mark / 100) * $totalQuestions);
					
					// They've passed. Report how many they got right. 
					if ($correctPercentage >= $this->unitQuizDetails->quiz_pass_mark)
					{
						// 2014-07-11 - Deprecated. Now being generated by the message summary for quizzes.
						/*echo WPCW_UnitFrontend::message_createMessage_success(sprintf(__('You got <b>%d out of %d (%d%%)</b> questions correct! This unit is now complete.', 'wp_courseware'),
							$correctCount, $totalQuestions, $correctPercentage
						));*/						
						
						// Notify the user of their grade.
						$quizGradeNotificationNeeded = true;
					}
					
					// They've failed. Report which questions are correct or incorrect.
					else 
					{
						// 2014-07-11 - Deprecated. Now being generated by the message summary for quizzes.
						// Show error that they've failed
						/*echo WPCW_UnitFrontend::message_createMessage_error(
							sprintf(__('Unfortunately, you only got <b>%d out of %d (%d%%)</b> questions correct. You need to correctly answer <b>at least %d questions (%d%%)</b>.', 'wp_courseware'),
							$correctCount, $totalQuestions, $correctPercentage,
							$minPassQuestions, $this->unitQuizDetails->quiz_pass_mark
						));*/
						
						// Save the user progress anyway, so that we can record attempts. 
						$this->update_quizzes_saveUserProgress_completeQuiz($resultDetails, $resultsList['answer_list']);
						
						// Have they run out of quiz attempts? If so, we need to notify the admin and mark the latest attempt
						// as being the the last blocked one they can use.
						if (!$this->check_quizzes_canUserRequestRetake())
						{
							// Update progress in database with next new step.
							$this->update_quizzes_setNextStepData('quiz_fail_no_retakes', false);
							
							// Notify the admin that this user is blocked and needs unblocking
							do_action('wpcw_quiz_user_needs_unblocking', $this->currentUserID, $this->unitQuizDetails);
						}
						
						// 2014-07-11 - Deprecated. Now being generated by the message summary for quizzes.
						// How many attempts have they now had?
						/*
						$attemptCount = $this->fetch_quizzes_getQuizAttemptCount();
						echo WPCW_UnitFrontend::message_createMessage_error(
							($attemptCount == 1 
									? __('You have already had 1 previous attempt at this quiz.', 'wp_courseware')  
									: sprintf(__('You have already had %d previous attempts at this quiz.', 'wp_courseware'), $attemptCount)
						));*/
						
						
						// 2014-07-11 - Deprecated. Now being generated by the message summary for quizzes.
						// If quiz is a no-answers quiz, show the form without the errors, so that the user cannot keep guesssing to pass.
						//echo $this->render_quizzes_old_handleQuizRendering($resultsList, ('no_answers' != $this->unitQuizDetails->quiz_show_answers));
						
						// Errors, so the user cannot progress yet.
						return false;
					}
					
				} // end of blocking quiz check
			}
			
			
		}	// end of survey check
		
		
		// ### 4 - Save the user progress 
		$this->update_quizzes_saveUserProgress_completeQuiz($resultDetails, $resultsList['answer_list']);
		
							
		// ### 5 - Notify the user of their grade.
		if ($quizGradeNotificationNeeded) {
			do_action('wpcw_quiz_graded', $this->currentUserID, $this->unitQuizDetails, false, false);
		}		
				
		// Questions need grading, notify the admin
		if ($gradingNeeded)
		{
			// Notify the admin that questions need answering.
			do_action('wpcw_quiz_needs_grading', $this->currentUserID, $this->unitQuizDetails);
		}
		
		// Questions need grading, so don't allow user to continue
		if ($gradingNeededBeforeContinue) {
			return false;
		}
	
		
		// If we get this far, the user may progress to next unit
		return true;
	}
	
	
	
	
	
	
	/**
	 * Get the correct answer for a question.
	 * 
	 * @param Object $question The question object.
	 * @param Mixed $providedAnswer If specified, use this to specify the correct answer. Otherwise use the correct answer for the question.
	 * 
	 * @return String The answer for the question.
	 */
	function check_quizzes_getCorrectAnswer($question, $providedAnswer = false)
	{
		$correctAnswer = false;
		if (!$providedAnswer) {
			$providedAnswer = $question->question_correct_answer;
		}
		
		switch ($question->question_type)
		{
			// Just use true or false if a t/f question
			case 'truefalse':
				if ('true' == $providedAnswer) {
					$correctAnswer = __('True', 'wp_courseware');	
				} else {
					$correctAnswer = __('False', 'wp_courseware');
				}
				break;
				
			// Multiple choice question - so turn list of answers into array
			// then use 1-indexing to get the text of the result.
			case 'multi':
					$answerListRaw = WPCW_quizzes_decodeAnswers($question->question_data_answers);
					$answerIdx = $providedAnswer;
									
					// Just double check that the selected answer is valid.
					// 2013-12-06 - Added <= rather than < count for the final answer to accept the final answer.
					// Because it's 1-indexed, not 0 indexed.
					if ($answerIdx >= 0 && $answerIdx <= count($answerListRaw) && isset($answerListRaw[$answerIdx])) {
						$correctAnswer = $answerListRaw[$answerIdx]['answer'];				
					}
				break;
		}
		
		return $correctAnswer;
	}
	
	
	/**
	 * Get the associated image for a multi-choice question based on the selected answer.
	 * 
	 * @param Object $question The question object.
	 * @param Mixed $answerIdx If specified, the index of the raw answer.
	 * 
	 * @return String The answer for the question.
	 */
	function fetch_quizzes_getImageForAnswer($question, $answerIdx)
	{
		// Multi-choice questions have images, no others do.
		if ('multi' != $question->question_type) {
			return false;
		}
		
		// Turn list of answers into array then use 1-indexing to get the image of the result.
		$answerListRaw = WPCW_quizzes_decodeAnswers($question->question_data_answers);
		$imageURL = false;
		
		// Just double check that the selected answer is valid.
		// 2013-12-06 - Added <= rather than < count for the final answer to accept the final answer.
		// Because it's 1-indexed, not 0 indexed.
		if ($answerIdx >= 0 && $answerIdx <= count($answerListRaw) && isset($answerListRaw[$answerIdx]))
		{
			if (isset($answerListRaw[$answerIdx]['image'])) {
				$imageURL = $answerListRaw[$answerIdx]['image'];				
			}
		}
		
		return $imageURL;
	}
	
	
	/**
	 * Checks all of the answers against the list of questions and return which answers are right or wrong.
	 * 
	 * @param Array $checkedAnswerList The answers to check.
	 * 
	 * @return Array Lists of the correct and wrong answers. (correct => Array, wrong => Array, 'needs_marking' => Array)
	 */
	function check_quizzes_checkForCorrectAnswers($checkedAnswerList)
	{
		$resultDetails = array(
			'correct'		=> array(),
			'wrong'			=> array(),
			'needs_marking' => array(),
		); 
		
		// Run through questions, and check each one for correctness.
		foreach ($this->unitQuizDetails->questions as $questionID => $question)
		{
			// Got to check the question type, as can't automatically score the open-ended question types.
			switch ($question->question_type)
			{
				case 'truefalse':
				case 'multi':					
						// Answer is completely empty
						if (!isset($checkedAnswerList[$questionID]) || is_null($checkedAnswerList[$questionID])) {
							$resultDetails['wrong'][$questionID] = false;
						}
						
						// Answer is just wrong
						else if ($checkedAnswerList[$questionID] != $question->question_correct_answer) {
							$resultDetails['wrong'][$questionID] = $checkedAnswerList[$questionID];
						}

						// If the answer is correct, add the question and answer to the correct list.						
						else {
							$resultDetails['correct'][$questionID] = $checkedAnswerList[$questionID];
							
						}				
					break;
					
				// Uploaded files and open-ended questions need marking
				case 'upload':
				case 'open':
						$resultDetails['needs_marking'][$questionID] = $checkedAnswerList[$questionID];
					break;
			}
		}
		
		return $resultDetails;
	}
	
	/**
	 * Can the user access this particular unit yet? 
	 * @return Boolean True if yes, false otherwise.
	 */
	function check_user_canUserAccessUnit()
	{
		return $this->userProgress && $this->userProgress->canUserAccessUnit($this->unitPost->ID);
	}
	
	/**
	 * Check to see if the unit has been completed or not. 
	 * @return Boolean True if the unit has been completed, false otherwise.
	 */
	function check_unit_isCompleted()
	{
		return $this->userProgress && $this->userProgress->isUnitCompleted($this->unitPost->ID);
	}	
	
	/**
	 * Is user logged in?
	 * @return Boolean True if logged in, false otherwise.
	 */
	function check_user_isUserLoggedIn()
	{
		return ($this->currentUserID > 0);
	}
	
	/**
	 * Can the user access this particular course (for this unit). 
	 * @return Boolean True if yes, false otherwise.
	 */
	function check_user_canUserAccessCourse()
	{
		return $this->userProgress && $this->userProgress->canUserAccessCourse();
	}
	
	
	/**
	 * Get the question number that we need to show to the user next.
	 * @return Integer The index of the question that we need to show next.
	 */
	function fetch_paging_getQuestionIndex()
	{
		// Not got any progress so far, so need to show the first question.
		if (!$this->unitQuizProgress || empty($this->unitQuizDetails->questions)) {
			return 0;
		}
		
		// Just need to validate that the question fits within the range of questions available.
		$questionIndexWeThinkWeNeed = $this->unitQuizProgress->quiz_paging_next_q;
		
		// Need first question
		if ($questionIndexWeThinkWeNeed < 0) {
			return 0;
		}
		
		// < is used because we know this should be 0-indexed.
		if ($questionIndexWeThinkWeNeed < count($this->unitQuizDetails->questions)) {
			return $questionIndexWeThinkWeNeed;
		}
		
		// Trying to access a question outside the range. Mostly likely we've got to the last
		// question, so now need to go through incomplete questions.
		if (empty($this->unitQuizProgress_incompleteQs)) {
			return 0;
		}
		
		return key($this->unitQuizProgress_incompleteQs);
	}
	
	
	/**
	 * Get the specified question to show the user. It should be 0-indexed.
	 * @param Integer $questionToFetch The index of the question to fetch.
	 * @return Object The question object, or false if the index is out of range.
	 */
	function fetch_paging_getQuestion($questionToFetch)
	{
		if ($questionToFetch < 0 || empty($this->unitQuizDetails->questions)) {
			return false;
		}
		
		// Get a list of index => Question IDs so that we can do a direct
		// index into the list of questions.
		$questionIDs = array_keys($this->unitQuizDetails->questions);
		
		// Ensure question is in range, and return it.
		if ($questionToFetch < count($questionIDs))
		{
			$objectID = $questionIDs[$questionToFetch];			
			return $this->unitQuizDetails->questions[$objectID];
		}
		
		return false;
	}
	
	
	/**
	 * Are we on the very last question that's also incomplete?
	 * @param Integer $questionID The ID of the question that we're checking. 
	 * @return Boolean True if it's the last question, false otherwise.
	 */
	function check_paging_isThisTheLastIncompleteQuestion($questionID)
	{
		if (!$questionID || !$this->unitQuizProgress) {
			return false;
		}
		
		// Reverse the order of the list so that we can start at the end and look 
		// for the first incomplete question.
		$reverseQuestionList = array_reverse($this->unitQuizProgress->quiz_data, true);
		foreach ($reverseQuestionList as $singleQuestionID => $questionDetails)
		{
			// Already got an incomplete question...
			if (1 == $questionDetails['is_incomplete'])
			{
				// So we can stop here. Return true if the ID we expect is this question.
				return ($singleQuestionID == $questionID);
			}
		}
		
		// No incomplete question was found...
		return false;
	}
	
	
	/**
	 * Function attempts to move the marker for the question selection to the previous item
	 * or next item when paging questions.
	 * 
	 * @param String $jumpMode If 'next' move forwards, if 'previous' move back by one question.
	 */
	function fetch_paging_getQuestion_moveQuestionMarker($jumpMode)
	{
		// No quiz progress so far, so create it.
		if (empty($this->unitQuizProgress)) {			
			$this->check_quizzes_canWeContinue_checkAnswersFromPaging(array());
			
			// Set index to first question
			$this->unitQuizProgress->quiz_paging_next_q = 0;
		}
		
		if (!empty($this->unitQuizProgress))
		{
			// Store the old index				
			$newIndex = $oldIndex = $this->unitQuizProgress->quiz_paging_next_q;
			
			// Moving to next question
			if ('next' == $jumpMode)
			{
				$newIndex++;
				
				// Check it's not out of range. Allowing to go past the last question
				// as that allows the last question to be skipped.
				if ($newIndex > $this->unitQuizProgress->quiz_question_total)
				{
					// Set to last question (count converted to 0-index)
					$newIndex = $this->unitQuizProgress->quiz_question_total-1;
				}
			}
			
			// Moving to previous question - Only attempt to move the marker if it's already > 0.
			else if ($this->unitQuizProgress->quiz_paging_next_q > 0)
			{
				$newIndex--;
			}			
			
			// We've detected a change, so move it.
			if ($oldIndex != $newIndex) 
			{
				$this->update_paging_getQuestion_saveQuestionMarker($newIndex);
			}
		} // end of check for question marker
	}
	
	
	/**
	 * Updates the database with the new index marker.
	 * @param Integer $newIndex The new marker to save to the database.
	 */
	function update_paging_getQuestion_saveQuestionMarker($newIndex)
	{
		// Update local variable
		$this->unitQuizProgress->quiz_paging_next_q = $newIndex;
		
		global $wpdb, $wpcwdb;
		$wpdb->show_errors();
		
		// Then just update the single count in the database.
		$wpdb->query($wpdb->prepare("
				UPDATE $wpcwdb->user_progress_quiz 
				SET quiz_paging_next_q = %d
				WHERE user_id = %d
				  AND unit_id = %d
				  AND quiz_id = %d
				  AND quiz_is_latest = 'latest'
			", 
				$newIndex,
				$this->currentUserID, $this->unitPost->ID, $this->unitQuizDetails->quiz_id 				
			));
	}
	
	
	/**
	 * Get a count of how many attempts there were for this quiz.
	 * 
	 * @return Object The quiz results as an object.
	 */ 
	function fetch_quizzes_getQuizAttemptCount()
	{
		global $wpdb, $wpcwdb;
		$wpdb->show_errors();
		
		return $wpdb->get_var($wpdb->prepare("
	    	SELECT COUNT(*) AS attempt_count
	    	FROM $wpcwdb->user_progress_quiz 
	    	WHERE user_id = %d 
	    	  AND unit_id = %d 
	    	  AND quiz_id = %d
	   	", $this->currentUserID, $this->unitPost->ID, $this->unitQuizDetails->quiz_id));
	}
	
	
	/**
	 * Loads any answers that we have for this user so that we can show them back to the user on the form. Updated
	 * the internal $this->unchecked_QuizAnswersToGrade object.
	 * 
	 * @param Array $quizAnswerData The answer data (which may be serialized)
	 */
	function fetch_quizzes_loadRawAnswersSoFarForThisQuiz($quizAnswerData)
	{
		$this->unchecked_QuizAnswersToGrade = array(
			'answer_list'		=> array(),
			'wrong_answer_list'	=> array(),
			'error_answer_list'	=> array(),
		);	
		
		if ($quizAnswerData)
		{			
			// Load the answer list from the saved data in the database into the unchecked answer 
			// list (ID => answer) for the rest of the code to handle the marking.
			$extractedAnswerList = array();
			$savedData = maybe_unserialize($quizAnswerData);
			if (!empty($savedData))
			{
				foreach ($savedData as $thisQuestionID => $thisQuestionDetails)
				{					
					// By default, copy what we have
					$extractedAnswerList[$thisQuestionID] = WPCW_arrays_getValue($thisQuestionDetails, 'their_answer_raw');
					
					// Handle incomplete questions.
					if (WPCW_arrays_getValue($thisQuestionDetails, 'is_incomplete') == 1) {
						$extractedAnswerList[$thisQuestionID] = null;
					}
				}
	
				// Copy the extracted data.
				$this->unchecked_QuizAnswersToGrade['answer_list'] = $extractedAnswerList;
			}
		} // end of quiz answer data check.
	}
	
	
	/**
	 * Gets the total number of questions for the paging mode.
	 * @return Integer The total number of questions.
	 */
	function fetch_paging_getQuestionCount()
	{
		if ($this->unitQuizDetails && !empty($this->unitQuizDetails->questions)) { 
			return count($this->unitQuizDetails->questions);
		}
		
		return 0;
	}

	
	/**
	 * Returns the user ID found by the class.
	 * @return Integer The ID of the user that's been found by the logged in user.
	 */
	function fetch_getUserID()
	{
		return $this->currentUserID;
	}
	
	/**
	 * Returns the unit ID found by the class.
	 * @return Integer The ID of the unit that's being shown.
	 */
	function fetch_getUnitID()
	{
		return $this->unitPost->ID;
	}
	
	/**
	 * Return the parent data for this unit.
	 * @return Object The parent data for this unit.
	 */
	function fetch_getUnitParentData()
	{
		return $this->parentData;
	}
	
	/**
	 * Return the quiz details for this unit.
	 * @return Object The quiz details for this unit.
	 */
	function fetch_getUnitQuizDetails()
	{
		return $this->unitQuizDetails;
	}
	
	/**
	 * Return the progress quiz details for this unit.
	 * @return Object The quiz progress details for this unit.
	 */
	function fetch_getQuizProgressDetails()
	{
		return $this->unitQuizProgress;
	}
	
	/**
	 * Check if we're showing questions over multiple pages.
	 * @return Boolean True if yes, false otherwise.
	 */
	function check_paging_areWePagingQuestions()
	{		
		return ($this->unitQuizDetails && 
				'use_paging' == $this->unitQuizDetails->quiz_paginate_questions && 
				!$this->check_paging_shouldWeShowReviewPage_rightNow()
			);
	}
	
	/**
	 * Check if we need to show the question review page generally.
	 * @return Boolean True if yes, false otherwise.
	 */
	function check_paging_shouldWeShowReviewPage()
	{		
		if (!$this->unitQuizDetails) {
			return false;
		}
		
		$pagingSettings = maybe_unserialize($this->unitQuizDetails->quiz_paginate_questions_settings);			
		return ('on' == WPCW_arrays_getValue($pagingSettings, 'allow_review_before_submission')); 
	}
	
	
	/**
	 * Check if we need to show the previous button.
	 * @return Boolean True if yes, false otherwise.
	 */
	function check_paging_shouldWeShowPreviousButton()
	{		
		if (!$this->unitQuizDetails) {
			return false;
		}
		
		$pagingSettings = maybe_unserialize($this->unitQuizDetails->quiz_paginate_questions_settings);			
		$allowPreviousButton = ('on' == WPCW_arrays_getValue($pagingSettings, 'allow_nav_previous_questions'));

		// Don't allow previous button to show on the very first question.
		return $allowPreviousButton && (!empty($this->unitQuizProgress) && $this->unitQuizProgress->quiz_paging_next_q > 0);
	}
	
	
	/**
	 * Check if we need to show the answer later button.
	 * @return Boolean True if yes, false otherwise.
	 */
	function check_paging_shouldWeShowAnswerLaterButton()
	{		
		if (!$this->unitQuizDetails) {
			return false;
		}
		
		// First check if settings allow it.
		$pagingSettings = maybe_unserialize($this->unitQuizDetails->quiz_paginate_questions_settings);			
		$allowAnswerLater = ('on' == WPCW_arrays_getValue($pagingSettings, 'allow_students_to_answer_later'));

		// True if settings allow it, and we're allowed to show it based on what question we're at.
		return ($allowAnswerLater && 
				(
					// Works on first question
					empty($this->unitQuizProgress) ||
					
					// Works on remaining questions   
					(!empty($this->unitQuizProgress) && $this->unitQuizProgress->quiz_paging_next_q < $this->unitQuizProgress->quiz_question_total)
				)
			); 
	}
	
	
	/**
	 * Check if we need to show the question review page right now.
	 * @return Boolean True if yes, false otherwise.
	 */
	function check_paging_shouldWeShowReviewPage_rightNow()
	{		
		if (!$this->unitQuizProgress) {
			return false;
		}
		
		// Not checking unitQuizDetails - as that's handled in the checking in check_paging_shouldWeShowReviewPage and 
		// we're ANDing the result, meaning $this->unitQuizProgress->quiz_paging_incomplete will never be checked if 
		// check_paging_shouldWeShowReviewPage() returns false.
		
		// The latter check sees if we're all complete on questions.
		return $this->check_paging_shouldWeShowReviewPage() && ('incomplete' == $this->unitQuizProgress->quiz_paging_status && $this->unitQuizProgress->quiz_paging_incomplete <= 0);
	}
	

	/**
	 * Update the next step data for the current quiz progress.
	 * @param String $type The type to switch to.
	 * @param String $message The message to use for this step.
	 */
	function update_quizzes_setNextStepData($type, $message)
	{
		global $wpdb, $wpcwdb;
		$wpdb->show_errors();
		
		$wpdb->query($wpdb->prepare("
				UPDATE $wpcwdb->user_progress_quiz 
				SET quiz_next_step_type = %s, quiz_next_step_msg = %s
				WHERE user_id = %d
				  AND unit_id = %d
				  AND quiz_id = %d
				  AND quiz_is_latest = 'latest'
			", 
				$type, $message,
				$this->currentUserID, $this->unitPost->ID, $this->unitQuizDetails->quiz_id 				
			));
			
		// Update internal variables rather than do a database reload.
		$this->unitQuizProgress->quiz_next_step_type = $type;
		$this->unitQuizProgress->quiz_next_step_msg = $message;
	}
	
	
	/**
	 * Tries to trigger the start of a quiz that's using a timer.
	 */
	function update_quizzes_beginQuiz()
	{
		// No quiz progress so far or we're doing a retake, so create a brand new progress item.
		if (empty($this->unitQuizProgress) || 'retake_waiting' == $this->unitQuizProgress->quiz_next_step_type)
		{			
			$this->check_quizzes_canWeContinue_checkAnswersFromPaging(array());
			
			// Set index to first question
			$this->unitQuizProgress->quiz_paging_next_q = 0;
			
			// And save...
			$this->update_paging_getQuestion_saveQuestionMarker(0);
			
			
			// Now update the start date of the quiz to now.
			global $wpdb, $wpcwdb;
			$wpdb->show_errors();
		
			// Then just update the single count in the database.
			$wpdb->query($wpdb->prepare("
				UPDATE $wpcwdb->user_progress_quiz 
				SET quiz_started_date = %s
				WHERE user_id = %d
				  AND unit_id = %d
				  AND quiz_id = %d
				  AND quiz_is_latest = 'latest'
			", 
				current_time('mysql'),
				$this->currentUserID, $this->unitPost->ID, $this->unitQuizDetails->quiz_id 				
			));
		}
	}
}