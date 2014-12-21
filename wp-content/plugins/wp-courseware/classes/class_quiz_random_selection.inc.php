<?php

/**
 * The class that represents a question where questions are selected from 
 * several tags.
 */
class WPCW_quiz_RandomSelection extends WPCW_quiz_base
{
	/**
	 * Default constructor
	 * @param Object $quizItem The quiz item details.
	 */
	function __construct($quizItem)
	{
		parent::__construct($quizItem);
		$this->questionType = 'random_selection';	
		$this->cssClasses = 'wpcw_question_type_random';

		// Hide usage
		$this->hideQuestionUsageCount = true;
	}
	
	
	/**
	 * Output the form that allows questions to be configured.
	 */	
	function editForm_toString()
	{
		$html = false;
		$columnCount = 1;
		
		// Render just the question area
		$html .= sprintf('<li id="wpcw_quiz_details_%s" class="%s"><table class="wpcw_quiz_details_questions_wrap" cellspacing="0">', $this->quizItem->question_id, $this->cssClasses);
		
			// Details of the question - top of the question details.
			$html .= $this->getSection_processHeader($columnCount);
		
			// Main question details here...		
			$html .= sprintf('<tr class="wpcw_quiz_row_question"><td>');
			
			
				// Show the human version
				$tagDetails = WPCW_quiz_RandomSelection::decodeTagSelection($this->quizItem->question_question);				
				$html .= sprintf('<ul class="wpcw_quiz_row_question_info">');
				
				if (!empty($tagDetails))
				{
					foreach ($tagDetails as $tagID => $tagDataToRender)
					{
						$html .= sprintf('<li>%s <b>%d</b> %s <b>%s</b>&nbsp;&nbsp;(%s)</li>',
							__('Show', 'wp_courseware'),
							$tagDataToRender['count'], 	
							__('questions from', 'wp_courseware'),
							$tagDataToRender['name'],
							sprintf(__('%d questions available', 'wp_courseware'), $tagDataToRender['tag_usage'])
						);
					}
				}
				$html .= sprintf('</ul>');
				
				// Add a call-to-action for editing the question
				// Postponed feature.
				//$html .= sprintf('<span class="wpcw_quiz_row_question_info_edit"><a href="#">%s</a></span>',
				//	__('Edit selection', 'wp_courseware')
				//);
				
				
				// And a blank version for use with jQuery for doing a live update.
				$html .= sprintf('<span class="wpcw_quiz_row_question_info_blank"><li>%s <b class="wpcw_count"></b> %s <b class="wpcw_name"></b></li></span>',
					__('Show', 'wp_courseware'),
					__('questions from', 'wp_courseware')
				);				
			
				// Load the question list direct from the question_question field - might as well reuse it.
				$html .= sprintf('<textarea name="question_question_%s" class="wpcw_quiz_row_question_list">%s</textarea>', 
					$this->quizItem->question_id, 
					$this->quizItem->question_question
				);
				
				// Order and type details
				$html .= sprintf('<input type="hidden" name="question_type_%s" value="random_selection" />', $this->quizItem->question_id);
					
				// Field storing order of question among other questions
				$html .= sprintf('<input type="hidden" name="question_order_%s" value="%s" class="wpcw_question_hidden_order" />', 		
							$this->quizItem->question_id, 
							$this->quizItem->question_order + 0
						);
				
			$html .= '</td></tr>';						
		
		// Extra fields at the bottom of a question.
		$html .= $this->getSection_processFooter($columnCount);
		
		// All done
		$html .= sprintf('</table></li>');
		
		return $html;
	}
	

	/**
	 * Check the tags that have been provided, and get the details for each tag.
	 * 
	 * @param unknown_type $dataToDecode The data to decode.
	 * 
	 * @return Array The validated tags as tag_[number] => array (count => $count, 'name' => $name).
	 */
	static function decodeTagSelection($dataToDecode)
	{
		$decodedData = json_decode($dataToDecode, true);
		$newDataToReturn = false;
		
		// Validate that the data is indeed JSON encoded.
		if (json_last_error() === JSON_ERROR_NONE)
		{
			if (!empty($decodedData))
			{
				foreach ($decodedData as $key => $value)
				{
					// Have we got the whole pool tag? If so, process and abort.
					// Should be no further tags in this section.
					if ('whole_pool' == $key) 
					{
						$newDataToReturn[$key] = array(
							'count' 		=> $value,
							'name'  		=> __('Entire Question Pool', 'wp_courseware'),
							'tag_id'		=> false,
							'tag_usage'		=> WPCW_questions_getQuestionCount()
						);
						break;
					}
					
					// Got normal tags, so we need to validate each tag.
					else 
					{
						if (preg_match('/^tag_([0-9]+)$/', $key, $matches))
						{
							// Extract details for this tag.
							$tagDetails = WPCW_questions_tags_getTagDetails($matches[1]);
							if ($tagDetails)	
							{
								$newDataToReturn[$key] = array(
									'count' 		=> $value,
									'name'  		=> $tagDetails->question_tag_name,
									'tag_id'		=> $tagDetails->question_tag_id,
									'tag_usage'		=> $tagDetails->question_tag_usage
								);
							} // end tag detail check
							
						} // end preg_match
					} // end if whole_pool
					
				} // end foreach
			} // end if (!empty($decodedData))
			
			
		}
				
		return $newDataToReturn;
	}

	
	/**
	 * Shows a the footer at the bottom of the question. Override to just show the move features.
	 *  
	 * @param Integer $columnCount The number of columns that are being rendered to show the question.
	 * @return String The HTML for rendering the footer section.
	 */
	function getSection_processFooter($columnCount)
	{
		$html = false;
		
		// Add icons for adding or removing a question.
		$html .= $this->getSection_actionButtons($columnCount);
		
		return $html;
	}
	

	
	
	/**
	 * Given a user ID, get the selection of questions for this random question selection. This
	 * will lock the selection to the user if the selection has not been locked already.
	 * 
	 * @param Integer $userID The ID of the user to get the selection of questions for.
	 * @param Integer $parentUnitID The ID of the parent unit to show this random question on.
	 * 
	 * @return Array The list of questions that have been locked.
	 */
	public function questionSelection_getLockedQuestionSelection($userID, $parentUnitID)
	{
		global $wpcwdb, $wpdb;
		$wpdb->show_errors();
		
		// #### 1 - See if the question is locked for a user? If so, return locked selection.
		$lockedSelection = $wpdb->get_var($wpdb->prepare("
				SELECT question_selection_list 
				FROM $wpcwdb->question_rand_lock
				WHERE question_user_id = %d				  
				  AND rand_question_id = %d
				  AND parent_unit_id   = %d 
			", $userID, $this->quizItem->question_id, $parentUnitID));
		
		// #### 2 - Got a selection, turn this into question IDs.
		if ($lockedSelection)
		{
			// Quickly validate that it's just numbers and commas, then use directly in SQL
			// for an array search of question IDs that match the selection.  If the string
			// does not validate as a string of numbers and commas, then we'll generate a new
			// selection using the code below.
			if (preg_match('/^([0-9]+,?)+$/', $lockedSelection))
			{				
				$questionsToUseWithIDKey = array();
				
				// ORDER BY FIELD is a function that will sort the questions in the order they appear in
				// the comma-separated list.
				$questionsToUse = $wpdb->get_results("
					SELECT *
					FROM $wpcwdb->quiz_qs
					WHERE question_id IN ($lockedSelection)
					  AND question_type != 'random_selection'
					ORDER BY FIELD(question_id, $lockedSelection)
				");
				
				if (!empty($questionsToUse))
				{
					// Need to remap to use ID => question
					foreach ($questionsToUse as $questionsToUse_single) {
						$questionsToUseWithIDKey[$questionsToUse_single->question_id] = $questionsToUse_single;
					}
				}
				
				return $questionsToUseWithIDKey;
			}
		}
		
		
		// #### 3 - Selection is not locked, so we generate a new selection for the user and then lock/save it to the database.
		$tagDetails = WPCW_quiz_RandomSelection::decodeTagSelection($this->quizItem->question_question);
		$questionsToUse = WPCW_quiz_RandomSelection::questionSelection_getRandomQuestionsFromTags($tagDetails);
		
		if (!empty($questionsToUse))
		{
			// Need to get the IDs of this question to lock it. Don't need any other data.
			$questionIDList = array();
			foreach ($questionsToUse as $questionDetails)
			{
				$questionIDList[] = $questionDetails->question_id;
			}
			
			// Now insert this list into the database to create the lock. Handling 
			// duplicates elegantly here by using REPLACE INTO intentionally
			$wpdb->query($wpdb->prepare("
				REPLACE INTO $wpcwdb->question_rand_lock
				(question_user_id, rand_question_id, question_selection_list, parent_unit_id) 
				VALUES (%d, %d, %s, %d)
			", $userID, $this->quizItem->question_id, implode(',', $questionIDList), $parentUnitID));
		}				
		
		return 	$questionsToUse;
	}
	
	
	
	/**
	 * Randomly choose from all of the questions available based on these tags, limited by how many
	 * is requested per tag based on selections in dialog box. 
	 * 
	 * @param Array $tagDetails The list of tag details that we want to use.
	 * 
	 * @return Array The list of questions to render for as part of a random selection.
	 */
	public static function questionSelection_getRandomQuestionsFromTags($tagDetails)
	{
		$questionsToUse = array();
		if (!empty($tagDetails))
		{
			global $wpcwdb, $wpdb;
			$wpdb->show_errors();
			
			foreach ($tagDetails as $singleTag => $singleTagDetails)
			{
				$questionList = false;
				
				// Whole pool
				if ('whole_pool' == $singleTag)
				{
					// Get the count number of questions from whole pool
					$questionList = $wpdb->get_results($wpdb->prepare("
						SELECT * 
						FROM $wpcwdb->quiz_qs qq
						WHERE qq.question_type != 'random_selection'
						ORDER BY RAND()
						LIMIT %d
					", $singleTagDetails['count'])); 					
				}
				
				// Tags - normal
				else
				{
					// Get the count number of questions that have the specified tag.
					$questionList = $wpdb->get_results($wpdb->prepare("
						SELECT * 
						FROM $wpcwdb->quiz_qs qq
							LEFT JOIN $wpcwdb->question_tag_mapping qtm ON qtm.question_id = qq.question_id
						WHERE tag_id = %d
						AND qq.question_type != 'random_selection'
						ORDER BY RAND()
						LIMIT %d
					", $singleTagDetails['tag_id'], $singleTagDetails['count'])); 
				}

				
				// Got some questions in this selection, so add to the main list.
				if (!empty($questionList)) 
				{
					foreach ($questionList as $questionDetails)
					{
						// Using question ID, so that any duplicate questions get unified
						// by using the same question key.
						$questionsToUse[$questionDetails->question_id] = $questionDetails;
					}
				}
			}
			
			// If we have multiple tags, questions will only be random per tag due to the SQL sort, so
			// randomize the questions here to mix them up between tags. 
			if (!empty($questionsToUse))
			{
				// Shuffle/randomize - but preserving keys (shuffle() doesn't by default).
				$shuffleKeys = array_keys($questionsToUse);
				shuffle($shuffleKeys);
				
				$questionsToUse_shuffled = array();
				foreach ($shuffleKeys as $key) {
				    $questionsToUse_shuffled[$key] = $questionsToUse[$key];
				}
				
				$questionsToUse = $questionsToUse_shuffled;
			}
							
		} // end if we have tag details.
		
		return $questionsToUse;
	}
	
	
	/**
	 * Fetch all questions for the selected tags, with no limitatons. Ordered by question ID.
	 * 
	 * @param Array $tagDetails The list of tag details that we want to use.
	 * 
	 * @return Array The list of questions.
	 */
	public static function questionSelection_getAllQuestionsFromTags($tagDetails)
	{
		$questionsToUse = array();
		if (!empty($tagDetails))
		{
			global $wpcwdb, $wpdb;
			$wpdb->show_errors();
			
			foreach ($tagDetails as $singleTag => $singleTagDetails)
			{
				$questionList = false;
				
				// Got a whole pool tag
				if ('whole_pool' == $singleTag)
				{
					// Just get all questions in the pool.
					$questionList = $wpdb->get_results("
						SELECT qq.question_id, qq.question_type, qq.question_question
						FROM $wpcwdb->quiz_qs qq
						WHERE question_type != 'random_selection'
						ORDER BY qq.question_id
					"); 
				}
				
				// Got a single tag
				else 
				{
					// Get the questions that have the specified tag.
					$questionList = $wpdb->get_results($wpdb->prepare("
						SELECT qq.question_id, qq.question_type, qq.question_question
						FROM $wpcwdb->quiz_qs qq
							LEFT JOIN $wpcwdb->question_tag_mapping qtm ON qtm.question_id = qq.question_id
						WHERE tag_id = %d
						 AND question_type != 'random_selection'
						ORDER BY qq.question_id
					", $singleTagDetails['tag_id'])); 
				}

				
				// Got some questions in this selection, so add to the main list.
				if (!empty($questionList)) 
				{
					foreach ($questionList as $questionDetails)
					{
						// Using question ID, so that any duplicate questions get unified
						// by using the same question key.
						$questionsToUse[$questionDetails->question_id] = $questionDetails;
					}
				}
			} // end foreach
							
		} // end if we have tag details.
		
		return $questionsToUse;
	}
}



?>