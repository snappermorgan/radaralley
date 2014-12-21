<?php

/**
 * Class that handles the rendering and editing of a custom feedback message created
 * by a trainer to give the trainee an opportunity to improve.
 */
class WPCW_quiz_CustomFeedback
{
	/**
	 * The ID of the quiz that we're showing the feedback for.
	 * @var Integer
	 */
	var $quizID;
	
	/**
	 * The details of the feedback message to use.
	 * @var Object
	 */
	var $feedbackMsgDetails;
	
	
	/**
	 * The ID for the quiz that we're creating this for. 
	 * @param Integer $quizID The ID of the quiz that this form belongs to.
	 * @param Object $feedbackMsgDetails The details of the feedback message to put into the form. 
	 */
	function __construct($quizID, $feedbackMsgDetails = false)
	{
		// Copy quiz ID - this is provided if this is an existing or new quiz.
		$this->quizID = $quizID;
		
		
		// If we've not got a valid $feedbackMsgDetails object, then set up up a correct blank one.
		if (empty($feedbackMsgDetails))
		{	
			$feedbackMsgDetails = new stdClass();
			$feedbackMsgDetails->qfeedback_id = 'new_message';
			
			$feedbackMsgDetails->qfeedback_tag_id 			= false;
			$feedbackMsgDetails->qfeedback_quiz_id 			= $quizID;
			$feedbackMsgDetails->qfeedback_summary 			= false;
			$feedbackMsgDetails->qfeedback_message 			= false;	
			$feedbackMsgDetails->qfeedback_score_type 		= 'below';
			$feedbackMsgDetails->qfeedback_score_grade 		= '50';
		} 
		
		$this->feedbackMsgDetails = $feedbackMsgDetails;
	}
	
	
	/**
	 * Determine if the grading scores tag data matches the criteria for this particular
	 * message.
	 * 
	 * @param Array $tagBucketList The list of tags and the grades for each.
	 * @return Boolean True if the criteria matches, false otherwise.
	 */
	function doesMessageMatchCriteria($tagBucketList)
	{
		// #### 1) Check to see if the details exist in the tag bucket. It may not
		// as we may have feedback, but not the associated tag score/grading.
		if (!isset($tagBucketList[$this->feedbackMsgDetails->qfeedback_tag_id])) {
			return false;
		}
		
		$thisTagDetails = $tagBucketList[$this->feedbackMsgDetails->qfeedback_tag_id];
		
		// #### 2) Check if the trigger grade is a match.
		switch ($this->feedbackMsgDetails->qfeedback_score_type)
		{
			case 'above':
					return $thisTagDetails['score_total'] > $this->feedbackMsgDetails->qfeedback_score_grade;
				break;
				
			case 'below':
					return $thisTagDetails['score_total'] <= $this->feedbackMsgDetails->qfeedback_score_grade;
				break;
		}
		
		// If we get here, we have an unknown score type. Default to false.
		return false;
	}
	
	
	/**
	 * Gets the message of this feedback for rendering.
	 */
	function getMessage()
	{
		return $this->feedbackMsgDetails->qfeedback_message;
	}
	
	
	/**
	 * Generates the form where the trainer can edit the details of the custom message.
	 */
	function generate_editForm()
	{
		$html = false;
		
		// Build the root of each field name, then append a suffix for each field.
		$fieldSuffix = '_' . $this->feedbackMsgDetails->qfeedback_id;
		$fieldPrefix = 'wpcw_qcfm_sgl_';
		
		$html .= sprintf('<div id="%swrapper%s"><table class="wpcw_quiz_custom_feedback_wrap_single" cellspacing="0"><tbody>', $fieldPrefix, $fieldSuffix);
		
		
			// ### 1) - Show the feedback message - summary field.
			$html .= sprintf('<tr class="wpcw_quiz_custom_feedback_hdr">');		
			
				// Label
				$html .= sprintf('<th>%s:<span class="wpcw_inner_hint">%s</span></th>',  
					__('Message Description', 'wp_courseware'),
					__('(Required) A quick summary for this message. This will not be displayed to students.')
				);	
								
				$html .= '<td>';
				
					// Entry field for message summary name
					$html .= sprintf('<input name="%s" type="text" value="%s" class="wpcw_qcfm_sgl_summary" placeholder="%s">',
						$fieldPrefix . 'summary' . $fieldSuffix,  
						$this->feedbackMsgDetails->qfeedback_summary,
						__('e.g. Low score on MyTag section', 'wp_courseware')
					);
					
					// Error message if incomplete
					$html .= sprintf('<span class="wpcw_quiz_custom_feedback_error">%s</span>', __('Please specify a quick summary for this message.', 'wp_courseware'));
					
				$html .= '</td>';	
				
				
				
				// Toggle field
				//$html .= sprintf('<td class="wpcw_quiz_custom_feedback_toggle">[+]</td>');	
							
			$html .= '</tr>';
			

			// ### 2) - Show tag selection field
			$html .= sprintf('<tr>');		
			
				// Label
				$html .= sprintf('<th>%s:<span class="wpcw_inner_hint">%s</span></th>',  
					__('Select Question Tag', 'wp_courseware'),
					__('(Required) Select the tag for which you want to provide feedback.')
				);	
				
				// Tag selection
				$html .= '<td>';
				
					// Dropdown for the tags
					$html .= WPCW_questions_tags_getTagDropdown(__('-- Please choose a tag ---', 'wp_courseware'), $fieldPrefix . 'tag' . $fieldSuffix, $this->feedbackMsgDetails->qfeedback_tag_id, 'wpcw_qcfm_sgl_tag', false, false);
					
					// Error message if incomplete
					$html .= sprintf('<span class="wpcw_quiz_custom_feedback_error">%s</span>', __('Please select a tag for this message.', 'wp_courseware'));
					
					// Shows the count of how many tags are available for this tag
					// This has not been continued for now, as it's an extremely expensive operation due to the random questions.
					/*
					if ($this->feedbackMsgDetails->qfeedback_tag_id > 0)
					{
						$questionCount = 3;//WPCW_quizzes_getQuestionCountForTag($this->quizID, $this->feedbackMsgDetails->qfeedback_tag_id);
						
						// Show count based on how many questions found for this tag.
						$html .= sprintf('<span class="wpcw_quiz_custom_feedback_question_count"><b>%d %s</b> %s</span>', 
							$questionCount,
							_n('question', 'questions', $questionCount, 'wp_courseware'),
							__('found for this tag in this quiz.', 'wp_courseware')
						);
					}
					
					// No selected tag, so just hide this.
					else {
						$html .= '<span class="wpcw_quiz_custom_feedback_question_count" style="display: none;"></span>';
					}*/
					
				$html .= '</td>';		

				// Empty toggle field.
				//$html .= sprintf('<td></td>');
			$html .= '</tr>';
			
			
			// ### 3) - Show the score level selection
			$html .= sprintf('<tr class="alternate">');		
			
				// Label
				$html .= sprintf('<th>%s:<span class="wpcw_inner_hint">%s</span></th>',  
					__('Select Question Tag', 'wp_courseware'),
					__('(Required) Select the tag for which you want to provide feedback.')
				);	
				
				$html .= '<td class="wpcw_quiz_custom_feedback_score">';
				
					$html .= sprintf('<span class="wpcw_quiz_custom_feedback_score_label_first">%s</span>', __('Display this message to students who score:', 'wp_courseware'));
				
					// Score condition check
					$html .= '<span class="wpcw_quiz_custom_feedback_score_radio_wrap">';
						
						$html .= sprintf('<label><input type="radio" name="%s" value="above" %s /> %s</label>', 
							$fieldPrefix . 'score_type' . $fieldSuffix,
							($this->feedbackMsgDetails->qfeedback_score_type == 'above' ? 'checked="checked"' : false),
							__('above', 'wp_courseware')
						);
						
						$html .= sprintf('<label><input type="radio" name="%s" value="below" %s /> %s</label>', 
							$fieldPrefix . 'score_type' . $fieldSuffix,
							($this->feedbackMsgDetails->qfeedback_score_type == 'below' ? 'checked="checked"' : false),
							__('at or below', 'wp_courseware')
						);
					$html .= '</span>';
					
					// Score selection
					$html .= WPCW_forms_createDropdown($fieldPrefix . 'score_grade' . $fieldSuffix, WPCW_quizzes_getPercentageList(false), $this->feedbackMsgDetails->qfeedback_score_grade);					
					$html .= sprintf('<span class="wpcw_quiz_custom_feedback_score_label_second">%s</span>', __('across all questions for the tag selected above', 'wp_courseware'));
					
					// Error message if incomplete
					$html .= sprintf('<span class="wpcw_quiz_custom_feedback_error">%s</span>', __('Please select a trigger score for this message.', 'wp_courseware'));

				$html .= '</td>';

			$html .= '</tr>';
			
			
			// ### 4) - Show the message form
			$html .= sprintf('<tr>');		
			
				// Label
				$html .= sprintf('<th>%s:<span class="wpcw_inner_hint">%s</span></th>',  
					__('Custom Feedback Message', 'wp_courseware'),
					__('(Required) Enter the message to display to students when conditions above are met.'),
					'test'
				);	
				
				$html .= '<td>';
				
					// Text area for message
					$html .= sprintf('<textarea name="%s" rows="7" class="wpcw_qcfm_sgl_message">%s</textarea>', 
						$fieldPrefix . 'message' . $fieldSuffix, 
						$this->feedbackMsgDetails->qfeedback_message
					);		
					
					// Error message if incomplete
					$html .= sprintf('<span class="wpcw_quiz_custom_feedback_error">%s</span>', __('Please enter a helpful feedback message for the trainee.', 'wp_courseware'));
					
				$html .= '</td>';

			$html .= '</tr>';
			
			
			// ### 5) Show the footer with the delete icon
			$html .= sprintf('<tr class="wpcw_quiz_row_footer">
				<td colspan="3" class="wpcw_question_actions">
					<a href="#" class="wpcw_delete_icon" rel="%s">Delete</a>
				</td>
			</tr>', __('Are you sure you wish to delete this custom feedback message?', 'wp_courseware'));
			
				
		$html .= '</tbody></table></div>';
		
		
		return $html;
	}
}

?>