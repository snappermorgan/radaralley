<?php

/**
 * The class that represents a open entry question, such as an essay or free-text input.
 */
class WPCW_quiz_OpenEntry extends WPCW_quiz_base
{
	/**
	 * Default constructor
	 * @param Object $quizItem The quiz item details.
	 */
	function __construct($quizItem)
	{
		parent::__construct($quizItem);
		$this->questionType = 'open';		
		$this->cssClasses = 'wpcw_question_type_open';
		
		$this->hint = __('(Optional) Use this to guide the user on the expected answer or length of answer. This is shown when the question is shown.', 'wp_courseware');
	}	
	
	
	/**
	 * Output the form that allows questions to be configured.
	 */	
	function editForm_toString()
	{
		// Keep track to see if this question has any errors
		$this->gotError = false;
	
		$html = false;
	
		// Extra CSS for errors
		$errorClass_Question 	= false;
		$errorClass_AnswerType 	= false;
	
		// Error Check - Have we got an issue with a lack of question or answer? 
		if ($this->showErrors) 
		{
			if (!$this->quizItem->question_question) {
				$errorClass_Question = 'wpcw_quiz_missing';	
				$this->gotError = true;
			}
			
			// Check that there's an input size field in the list.
			if (!$this->quizItem->question_answer_type) { 
				$errorClass_AnswerType = 'wpcw_quiz_missing';	
				$this->gotError = true;
			}
		}
	
		// Track columns needed to show question details
		$columnCount = 2;
		
		$html .= sprintf('<li id="wpcw_quiz_details_%s" class="%s"><table class="wpcw_quiz_details_questions_wrap" cellspacing="0">', $this->quizItem->question_id, $this->cssClasses);
		
			// Details of the question - top of the question details.
			$html .= $this->getSection_processHeader($columnCount);
		
			// Main question details here...
			$html .= sprintf('<tr class="wpcw_quiz_row_question %s">', $errorClass_Question);
			
				$html .= sprintf('<th>%s</th>', __('Question', 'wp_courseware'));
				
				// Open-Ended Question
				$html .= '<td>';
					$html .= sprintf('<textarea name="question_question_%s">%s</textarea>',	$this->quizItem->question_id, htmlspecialchars($this->quizItem->question_question));
										
					// Field storing order of question among other questions
					$html .= sprintf('<input type="hidden" name="question_type_%s" value="open" />', 	$this->quizItem->question_id);
					$html .= sprintf('<input type="hidden" name="question_order_%s" value="%s" class="wpcw_question_hidden_order" />', 		
								$this->quizItem->question_id, 
								$this->quizItem->question_order + 0
							);
							
				$html .= '</td>';
							
			$html .= '</tr>';
			
			// Render the section that allows an image to be shown.
			$html .= $this->getSection_showImageField($columnCount);
				
			// Show a bit of the form that allows the user to determine what kind of size
			// the answer box should be.
			$html .= sprintf('<tr class="alternate %s">', $errorClass_AnswerType);
				$html .= sprintf('<th>%s</th>', __('Size of box for user to type answer?', 'wp_courseware'));
			
				$html .= sprintf('<td class="wpcw_quiz_details_answer_type_selection"><li>');
				
					// Get constants for answer types.
					$answerTypes = self::getValidAnswerTypes();
					
					// Create radio buttons where the user can choose the size of the input field.
					foreach ($answerTypes as $answerTypeKey => $answerTypeLabel)
					{
						$html .= sprintf('<li><input type="radio" name="question_answer_type_%s" value="%s" %s /> &nbsp;%s</li>', 
							$this->quizItem->question_id,			
							$answerTypeKey, 
							($this->quizItem->question_answer_type == $answerTypeKey ? 'checked="checked"' : false),			
							$answerTypeLabel
						);
					}
					
					
				$html .= '</li></td>';			
			$html .= '</tr>';
			
			// Extra fields at the bottom of a question.
			$html .= $this->getSection_processFooter($columnCount);

		// All done
		$html .= '</table></li>';
		return $html;		
	}
	
	
	/**
	 * (non-PHPdoc)
	 * @see WPCW_quiz_base::renderForm_toString()
	 */
	public function renderForm_toString($parentQuiz, $questionNum, $selectedAnswer, $showAsError, $errorToShow = false)
	{
		// Generate the ID of the field, also used for the CSS ID
		$fieldID = sprintf('question_%d_%s_%d', $parentQuiz->quiz_id, $this->questionType, $this->quizItem->question_id);
		
		// Set CSS wrapper to type of answer.
		$cssClass = $this->quizItem->question_answer_type;
		
		// Add the open-entry field, based on what field type has been asked for.
		switch ($this->quizItem->question_answer_type)
		{
			case 'single_line':				
				$this->extraQuizHTML .= sprintf('<input type="text" name="%s" id="%s" value="%s"/>', $fieldID, $fieldID, $selectedAnswer); 
				break;
				
			case 'medium_textarea':
				$this->extraQuizHTML .= sprintf('<textarea name="%s" id="%s" rows="8">%s</textarea>', $fieldID, $fieldID, $selectedAnswer);
				break;
								
			case 'large_textarea':
				$this->extraQuizHTML .= sprintf('<textarea name="%s" id="%s" rows="15">%s</textarea>', $fieldID, $fieldID, $selectedAnswer);
				break;

			// case 'small_textarea':
			default:
				$cssClass = 'small_textarea'; // Just in case it's different to what we expect for the answer type.
				$this->extraQuizHTML .= sprintf('<textarea name="%s" id="%s" rows="4">%s</textarea>', $fieldID, $fieldID, $selectedAnswer);
				break;
		}
		
		// Add the hint if there is one
		if ($this->quizItem->question_answer_hint) {
			$this->extraQuizHTML .= sprintf('<div class="wpcw_fe_quiz_q_hint">%s</div>', $this->quizItem->question_answer_hint);
		}
		
		return parent::renderForm_toString_withClass($parentQuiz, $questionNum, $selectedAnswer, $showAsError, 
			'wpcw_fe_quiz_q_open wpcw_fe_quiz_q_open_'. $cssClass, 	// CSS Class is generic plus including the specific field size.
			$errorToShow
		);
	}
	

	
	/**
	 * Return a list of valid answer types for open-ended questions.
	 * @return Array A list of answer types and their keys.
	 */
	public static function getValidAnswerTypes() 
	{
		return $answerTypes = array(
			'single_line' 		=> __('<b>Single Line</b> of Text', 'wp_courseware'),
			'small_textarea' 	=> __('<b>Small</b> Text Box - about 4 Lines of Text', 'wp_courseware'),
			'medium_textarea' 	=> __('<b>Medium</b> Text Box - about 8 Lines of Text', 'wp_courseware'),
			'large_textarea' 	=> __('<b>Large</b> Text Box - about 15 Lines of Text', 'wp_courseware'),
		);
	}
	
	/**
	 * Clean the answer data and return it to the user. 
	 * 
	 * @param String $rawData The data that's being cleaned.
	 * @return String The cleaned data.
	 */
	public static function sanitizeAnswerData($rawData)
	{
		// Escape special characters.
		return stripslashes(filter_var($rawData, FILTER_SANITIZE_SPECIAL_CHARS));
	}
}



?>