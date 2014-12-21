<?php

/**
 * The class that represents a multiple choice answer.
 */
class WPCW_quiz_TrueFalse extends WPCW_quiz_base
{
	/**
	 * Default constructor
	 * @param Object $quizItem The quiz item details.
	 */
	function __construct($quizItem)
	{
		parent::__construct($quizItem);
		$this->questionType = 'truefalse';
		$this->cssClasses = 'wpcw_question_type_truefalse';	

		$this->hint = __('(Optional) Use this to guide the user that they should make a selection.', 'wp_courseware');
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
		$errorClass_Answer 		= false;
	
		// Error Check - Have we got an issue with a lack of question or answer? 
		if ($this->showErrors) 
		{
			if (!$this->quizItem->question_question) {
				$errorClass_Question = 'wpcw_quiz_missing';	
				$this->gotError = true;
			}

			// Only an error if we need a correct answer and not got one currently.
			if ($this->needCorrectAnswers && !$this->quizItem->question_correct_answer) {
				$errorClass_Answer = 'wpcw_quiz_missing';
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
				
				// T/F Type
				$html .= sprintf('<td>');
					$html .= sprintf('<textarea name="question_question_%s">%s</textarea>',	$this->quizItem->question_id, htmlspecialchars($this->quizItem->question_question));
					$html .= sprintf('<input type="hidden" name="question_type_%s" value="truefalse" />', 	$this->quizItem->question_id);
					
					// Field storing order of question among other questions
					$html .= sprintf('<input type="hidden" name="question_order_%s" value="%s" class="wpcw_question_hidden_order" />', 		
								$this->quizItem->question_id, 
								$this->quizItem->question_order + 0
							);
							
				$html .= '</td>';
							
			$html .= '</tr>';
			
			
			// Render the section that allows an image to be shown.
			$html .= $this->getSection_showImageField($columnCount);
			
			
			// If correct answers are needed, show the row for collecting correct answers.
			$html .= sprintf('<tr class="wpcw_quiz_details_truefalse_answer wpcw_quiz_only_td alternate %s">', $errorClass_Answer);
			
				$html .= sprintf('<th>%s</th>', __('Correct Answer?', 'wp_courseware'));
				
				// T/F Selection
				$html .= sprintf('<td class="wpcw_quiz_details_truefalse_selection">');
					$html .= sprintf('<label><input type="radio" name="question_answer_sel_%s" value="true" %s /> %s</label>',						
						$this->quizItem->question_id, ($this->quizItem->question_correct_answer == 'true' ? 'checked="checked"' : false),
						__('True', 'wp_courseware')
					);
					
					$html .= sprintf('<label><input type="radio" name="question_answer_sel_%s" value="false" %s /> %s</label>', 						
						$this->quizItem->question_id, ($this->quizItem->question_correct_answer == 'false' ? 'checked="checked"' : false),
						__('False', 'wp_courseware')
					);
				$html .= '</td>';
							
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
		$this->answerList = array();
		$this->answerList[__('True', 'wp_courseware')] 	= 'true';
		$this->answerList[__('False', 'wp_courseware')] 	= 'false';
		
		// Add the hint if there is one
		if ($this->quizItem->question_answer_hint) {
			$this->extraQuizHTMLAfter .= sprintf('<div class="wpcw_fe_quiz_q_hint">%s</div>', $this->quizItem->question_answer_hint);
		}
		
		return parent::renderForm_toString_withClass($parentQuiz, $questionNum, $selectedAnswer, $showAsError, 'wpcw_fe_quiz_q_truefalse', $errorToShow);
	}

	
	/**
	 * Extract the correct answer for a True/False question, using the specified answer key to check $_POST.
	 * 
	 * @param String $correctAnswerKey The key to use to extract a correct answer.
	 * @return String The correct answer, if it was found.
	 */
	public static function editSave_extractCorrectAnswer($correctAnswerKey)
	{
		// Expecting a string of 'true' or 'false'.
		if (isset($_POST[$correctAnswerKey]) && in_array($_POST[$correctAnswerKey], array('true', 'false')))
		{
			return $_POST[$correctAnswerKey];
		}
		
		// Not found, so returning '' (empty string).
		return false;
	}
	
	
	/**
	 * Clean the answer data and return it to the user. Check for true or false answer.
	 * 
	 * @param String $rawData The data that's being cleaned.
	 * @return String The cleaned data.
	 */
	public static function sanitizeAnswerData($rawData)
	{
		if ('true' == $rawData || 'false' == $rawData) {
			return $rawData;
		}
		
		return false;
	}
}



?>