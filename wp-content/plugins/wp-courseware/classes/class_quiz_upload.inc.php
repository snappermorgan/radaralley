<?php

/**
 * The class that represents a question where you can upload a file.
 */
class WPCW_quiz_FileUpload extends WPCW_quiz_base
{	
	/**
	 * Default constructor
	 * @param Object $quizItem The quiz item details.
	 */
	function __construct($quizItem)
	{
		parent::__construct($quizItem);
		$this->questionType = 'upload';		
		$this->cssClasses = 'wpcw_question_type_upload';
		
		$this->hint = __('(Optional) Use this to guide the user what they should upload.', 'wp_courseware');
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
		$errorClass_FileType 	= false;
	
		// Error Check - Have we got an issue with a lack of question or answer? 
		if ($this->showErrors) 
		{
			if (!$this->quizItem->question_question) {
				$errorClass_Question = 'wpcw_quiz_missing';	
				$this->gotError = true;
			}
			
			// Check that there's an input size field in the list.
			if (!$this->quizItem->question_answer_file_types) { 
				$errorClass_FileType = 'wpcw_quiz_missing';	
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
				
				// Upload Questions
				$html .= '<td>';
					$html .= sprintf('<textarea name="question_question_%s">%s</textarea>',	$this->quizItem->question_id, htmlspecialchars($this->quizItem->question_question));
										
					// Field storing order of question among other questions
					$html .= sprintf('<input type="hidden" name="question_type_%s" value="upload" />', 	$this->quizItem->question_id);
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
			$html .= sprintf('<tr class="alternate %s">', $errorClass_FileType);
				$html .= sprintf('<th>%s</th>', __('Permitted file extensions?', 'wp_courseware'));
			
				$html .= sprintf('<td class="wpcw_quiz_details_answer_file_type_selection">');
					$html .= sprintf('<input type="text" name="question_answer_file_types_%s" value="%s" />', $this->quizItem->question_id, $this->quizItem->question_answer_file_types);
									
					$html .= sprintf('<span>%s<br/>%s</span>', 
						__('Just list the permitted extensions without the dot.', 'wp_courseware'),
						__('e.g. "pdf, xls, mp3"', 'wp_courseware')
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
		// Generate the ID of the field, also used for the CSS ID
		$fieldID = sprintf('question_%d_%s_%d', $parentQuiz->quiz_id, $this->questionType, $this->quizItem->question_id);
		
		// Have they already uploaded a file? If so, tell them with a link to open the file.
		if ($selectedAnswer) 
		{			
			// Shows the link for the existing file.
			$this->extraQuizHTML .= sprintf('<div class="wpcw_fe_quiz_q_upload_existing">
												%s <b><a href="%s%s" target="_blank">.%s %s (%s)</a></b> %s 
											 </div>',
					__('You have uploaded a', 'wp_courseware'), 
					WP_CONTENT_URL, $selectedAnswer,
					pathinfo($selectedAnswer, PATHINFO_EXTENSION),
					__('file', 'wp_courseware'), 					
					WPCW_files_getFileSize_human($selectedAnswer),
					__('for this answer.', 'wp_courseware')
				); 
				
			// Shows the link to change the file
			$this->extraQuizHTML .= sprintf('<div class="wpcw_fe_quiz_q_upload_change_file_wrap">
												<a href="#" class="wpcw_fe_quiz_q_upload_change_file" data-fieldid="%s">%s</a>
												<a href="#" class="wpcw_fe_quiz_q_upload_change_file_cancel">%s</a>
												<div class="wpcw_fe_quiz_q_upload_change_holder"></div>
											</div>', 
											$fieldID,
											__('Click here to upload a different file...', 'wp_courseware'),
											__('Cancel uploading a different file', 'wp_courseware')
										);
		} 
		
		// Only show the file upload if we don't have it.
		else
		{
			// The file upload bit.
			$this->extraQuizHTML .= sprintf('<div class="wpcw_fe_quiz_q_upload_wrapper" id="%s">', $fieldID);
				$this->extraQuizHTML .= sprintf('<input type="file" name="%s" >', $fieldID);
			$this->extraQuizHTML .= '</div>';
		}
		

		
		// Work out what file types are permitted
		$fileTypes = WPCW_files_cleanFileExtensionList($this->quizItem->question_answer_file_types);
		$permittedFiles = false;
		if (!empty($fileTypes)) 
		{
			// Show message about permitted file types, which can be customised if needed.
			$permittedFiles =  apply_filters('wpcw_front_quiz_upload_permitted_files', __('Allowed file types: ', 'wp_courseware') . implode(', ', $fileTypes) . '. ', $fileTypes);	
		}
				
		// Add the hint if there is one
		if ($this->quizItem->question_answer_hint) {
			$this->extraQuizHTML .= sprintf('<div class="wpcw_fe_quiz_q_hint">%s</div>', $this->quizItem->question_answer_hint);
		}
		
		// Add the file type list if there are any
		if ($permittedFiles) {
			$this->extraQuizHTML .= sprintf('<div class="wpcw_fe_quiz_q_hint wpcw_fe_quiz_q_upload_permitted_files">%s</div>', $permittedFiles);
		}
		
		return parent::renderForm_toString_withClass($parentQuiz, $questionNum, $selectedAnswer, $showAsError, 'wpcw_fe_quiz_q_upload', $errorToShow);
	}	
	
	
	/**
	 * Validate the files that have been uploaded, checking them against the conditions of the quiz details.
	 * 
	 * @param Array $fileList The list of files to be checked for this quiz.
	 * @param Array $quizDetails The details of the quiz to check
	 * 
	 * @return Array The results of the file upload (upload_errors, upload_missing, upload_valid), which contain a list of the question ID and error messages.
	 */
	public static function validateFiles($fileList, $quizDetails)
	{
		// Assume that we have quiz details at this point.
		
		// Get a list of the questions that are expecting files.
		$questionsWithUploads = array();
		foreach ($quizDetails->questions as $qID => $qObj)
		{
			if ('upload' == $qObj->question_type) {
				$questionsWithUploads[$qID] = $qObj;
			}
		}
		
		// No questions to check for.
		if (count($questionsWithUploads) == 0) {
			return false;
		}
		
		// Generate a unique path for the file uploads that uses the user's private directory.		
		$userPathDetails = WPCW_files_getFileUploadDirectory_forUser($quizDetails, get_current_user_id());
		
		
		// Prepare results data
		$results = array(
			'upload_errors' 	=> array(),
			'upload_missing' 	=> array(),
			'upload_valid'		=> array()
		);
		
		// Check for each expected upload file that's in the list of questions
		// and do a little more validation (and handle moving the file too).
		foreach ($questionsWithUploads as $qID => $qObj)
		{
			// Generate the name of the file key to check e.g. question_16_upload_73
			$keyName = sprintf('question_%d_upload_%d', $quizDetails->quiz_id, $qID);
			
			// File was found, so need to some further checks to make sure the extension is valid
			// and then we can move the file to the right place.
			if (isset($fileList[$keyName])) 
			{
				// Uploaded file details
				$file_name 	= $fileList[$keyName]['name'];
				$file_tmp 	= $fileList[$keyName]['tmp_name'];
				$file_error = $fileList[$keyName]['error'];
				$file_size 	= $fileList[$keyName]['size'];

				// Got a PHP upload error?
				if ($file_error > 0)
				{
					$errMsg = __('Error. An unknown file upload error occurred.', 'wp_courseware');
					
					switch ($file_error)
					{
						case UPLOAD_ERR_FORM_SIZE:
						case UPLOAD_ERR_INI_SIZE:
								$errMsg = sprintf(__('Error. The uploaded file exceeds the maximum file upload size (%s).', 'wp_courseware'), WPCW_files_getMaxUploadSize());
							break;
													
						case UPLOAD_ERR_PARTIAL:
								$errMsg = __('Error. The uploaded file was only partially uploaded.', 'wp_courseware');
							break;
							
						case UPLOAD_ERR_NO_FILE:
								$errMsg = __('Error. No file was uploaded.', 'wp_courseware');
							break;
							
						case UPLOAD_ERR_NO_TMP_DIR:
								$errMsg = __('Error. The temporary upload directory does not exist.', 'wp_courseware');
							break;
							
						case UPLOAD_ERR_CANT_WRITE:
								$errMsg = __('Error. Could not write the uploaded file to disk.', 'wp_courseware');
							break;
	 
						case UPLOAD_ERR_EXTENSION:
								$errMsg = __('Error. An extension stopped the file upload.', 'wp_courseware');
							break;
					}
										
					// Store error and don't process file further
					$results['upload_errors'][$qID] = $errMsg;
					continue;
				}

				
				// Check the valid file extensions
				$extensionTypes = WPCW_files_cleanFileExtensionList($qObj->question_answer_file_types);
				$thisFileExtension = pathinfo($file_name, PATHINFO_EXTENSION);
				
				// File extension is not valid, so abort and move to next file.
				if (!in_array($thisFileExtension, $extensionTypes)) 
				{
					$results['upload_errors'][$qID] = sprintf(__('Error. Extension of file does not match allowed file types of %s.', 'wp_courseware'), implode(', ', $extensionTypes));
					continue;
				}
					
				// Move file to the new location, which is USERPATH/question_16_upload_73_user_4.ext so that we can ensure we have
				// completely safe URL for the file. And the naming convention helps the admin to a certain degree.
				$newFilename = $keyName . '_user_' . get_current_user_id() . '.' . $thisFileExtension;
				if (move_uploaded_file($file_tmp, $userPathDetails['dir_path'] . $newFilename) !== FALSE) 
				{
					// Store relative path of file as being a valid upload.
					$results['upload_valid'][$qID] = $userPathDetails['path_only'] . $newFilename; 
				}
				
				// Could not move file - might be out of space, or a write error.
				else {
					$results['upload_errors'][$qID] = __('Error. Could not move file to your training directory.', 'wp_courseware');
					continue;
				}
			}
			
			// Keep track of files that are missing.
			else 
			{
				$results['upload_missing'][$qID] = true;
			} 
			// end check of question in file list.
		} // end foreach
		
		
		return $results;
	} // end fn
}



?>