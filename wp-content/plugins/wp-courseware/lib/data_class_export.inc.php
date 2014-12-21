<?php

// Contains a list of what fields to import.
include_once 'data_common.inc.php';


/**
 * A class for exporting a selected training course into an XML file.
 */
class WPCW_Export 
{
		
	/**
	 * Default constructor - currently unused.
	 */
	function __construct() { }
	
	
	/**
	 * Stores a list of questions for this course.
	 * @var Array
	 */
	var $questionList;
	
	
	/**
	 * See if there's a course to export based on $_POST variables. If so, trigger the export and XML download.
	 * @param Boolean $triggerFileDownload If true, trigger a file download rather than just XML output as a page.
	 */
	public static function tryExportCourse($triggerFileDownload = true)
	{
		// See if course is being exported
		if (isset($_POST["update"]) && $_POST["update"] == 'wpcw_export' && current_user_can('manage_options'))
		{
			// Now check course is valid. If not, then don't do anything, and let
			// normal form handle the errors.
			$courseID = WPCW_arrays_getValue($_POST, 'export_course_id');
			$courseDetails = WPCW_courses_getCourseDetails($courseID);
			
			if ($courseDetails)
			{
				$moduleList = false;
				$questionList = false;
				
				// Work out what details to fetch and then export
				$whatToExport = WPCW_arrays_getValue($_POST, 'what_to_export');
				switch ($whatToExport)
				{
					// Course Settings: Yes
					// Module Settings: No
					// 			 Units: No
					// 	 	   Quizzes: No
					case 'just_course':
						break;			
						
					// Course Settings: Yes
					// Module Settings: Yes
					// 			 Units: No
					// 	 	   Quizzes: No					
					case 'course_modules':
						$moduleList = WPCW_courses_getModuleDetailsList($courseDetails->course_id);
						break;
						
					// Course Settings: Yes
					// Module Settings: Yes
					// 			 Units: Yes
					// 	 	   Quizzes: No
					case 'course_modules_and_units':
						$moduleList = WPCW_courses_getModuleDetailsList($courseDetails->course_id);
						if ($moduleList)
						{
							// Grab units for each module, in the right order, and associate with each module object.
							foreach ($moduleList as $module) 
							{
								// This might return false, but that's OK. We'll check for it later.
								$module->units = WPCW_units_getListOfUnits($module->module_id); 
							}
						}
						break;
						
					// Basically the whole course
					// Course Settings: Yes
					// Module Settings: Yes
					// 			 Units: Yes
					// 	 	   Quizzes: Yes
					default:
						$questionList = WPCW_questions_getAllQuestionsforCourse($courseDetails->course_id);
						
						$moduleList = WPCW_courses_getModuleDetailsList($courseDetails->course_id);
						if ($moduleList)
						{
							// Grab units for each module, in the right order, and associate with each module object.
							foreach ($moduleList as $module) 
							{
								// This might return false, but that's OK. We'll check for it later.
								$module->units = WPCW_units_getListOfUnits($module->module_id);
								
								// See if we have any units, and then check each for the associated quiz data.
								// Update the unit objects with details of the quizzes
								WPCW_Export::WPCW_quizzes_fetchQuizzesForUnits($module->units);	 							
							}
						}
						
						break;
				}
				
				
				// TODO ZZZ - DEBUG Tool - To enable debugging, comment this in so that the download is not triggered.
				$triggerFileDownload = true;
				
				// If true, trigger a file download of the XML file.	
				if ($triggerFileDownload)
				{
					$exportFile = "wp-courseware-export-" . sanitize_title($courseDetails->course_title) . '-' . date("Y-m-d") . ".xml";
					header('Content-Description: File Transfer');
					header("Content-Disposition: attachment; filename=$exportFile");
					header('Content-Type: text/xml; charset=' . get_option('blog_charset'), true);
				}
				
				else 
				{
					// When debugging, comment out the line above, and use the following line so that you can see
					// any error messages.
					header('Content-Type: text/plain');
				}
				
				
				$export = new WPCW_Export();
				echo $export->exportCourseDetails($courseDetails, $moduleList, $questionList);
												
				die();
			}
		}
		
		// If get here, then normal WPCW processing takes place.
	}
	
	
	/**
	 * Tries to get the quiz data for each unit that's in this list of units. Each unit is updated with
	 * the quiz details if any are associated with a unit. Kept in this function as not needed elsewhere
	 * so saving processing by putting it here.
	 * 
	 * @param Array The list of units to check
	 * @return Array The same list of units passed as a parameter.
	 */
	function WPCW_quizzes_fetchQuizzesForUnits($unitList)
	{
		if (!empty($unitList))
		{
			// Add quiz data for each unit in the list.
			foreach ($unitList as $unitID => $unitObj)
			{
				// Create field anyway, but update with quiz details if one was found.
				$unitObj->extradata_quiz_details = WPCW_quizzes_getAssociatedQuizForUnit($unitID, false, false);
			}
		}
		
		return $unitList;
	}
	
	
	/**
	 * Exports the course object, breaking down the course, modules and units.
	 *  
	 * @param Array $courseDetails The object containing the course details. 
	 * @param Array $moduleList The object containing the modules and units for this course.
	 * @param Array $questionList The list of ALL questions for this course.
	 * 
	 * @return String The XML that represents this course.
	 */
	function exportCourseDetails($courseDetails, $moduleList, $questionList)
	{
		$xml = "";
		
		$this->questionList = $questionList;
		
		global $fieldsToProcess_course, $fieldsToProcess_modules, $fieldsToProcess_units;
		
		// Nice whitespace padding to make XML readable.
		$padding = $this->export_indent(false);
		$parentNode = 'course';
		
		$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
		$xml .= $padding.sprintf('<%s version="%s">', $parentNode, WPCW_DATABASE_VERSION);
				                        
		
		
		
        // Debugging only - see if we have any missing fields from export checking the above. 
        // Check error_log for the results of this function - to see which fields are not included
        // in the export. 
        //$this->debug_courseCheckFieldsWeHave($courseDetails, $fieldsToProcess_course);

		// Export settings for course itself.
        $xml .= $this->export_objectToXML('settings', false, $courseDetails, $fieldsToProcess_course, false);
        
        // #### Add Questions First
        if (!empty($this->questionList))
        {
        	$xml .= $this->export_startBlock(false, 'questions');
        		
        		$xml .= $this->export_content_handleQuestionsAndTags();        	
        	
        	$xml .= $this->export_endBlock(false, 'questions');
        }
        
        
        
        // #### Add Module Details
        if (!empty($moduleList))
        {
        	$xml .= $this->export_startBlock(false, 'modules');

        	     	
        	// Render each module as modules > module > details
        	foreach ($moduleList as $moduleID => $moduleObj) 
        	{
				$parentPath = 'modules';
        		
				// Export main module data
        		$xml .= $this->export_objectToXML('module', false, $moduleObj, $fieldsToProcess_modules, $parentPath, false);
        		
        		
        		// Export unit data for this module, mostly just content. The IDs, post date, etc can be ignored.
        		// The ordering is assumed to be the order of the data in the file, so again, that can be ignored.
        		// This makes the XML as simple as possible, making it flexible for the future as possible.
        		if (isset($moduleObj->units) && !empty($moduleObj->units))
        		{
        			$unitParentPath = $parentPath . '/module';
        			$xml .= $this->export_startBlock($unitParentPath, 'units');
        			
        			foreach ($moduleObj->units as $unitObj)
        			{
        				// ###ÊUnits - Start
        				$xml .= $this->export_objectToXML('unit', false, $unitObj, $fieldsToProcess_units, $unitParentPath, false);
        				
        					// Export the quizzes for this unit
        					$xml .= $this->export_content_handleQuizzes($unitObj, $unitParentPath . '/unit');
        				
        				// ###ÊUnits - End
        				$xml .= $this->export_endBlock($unitParentPath, 'unit');
        			}
        			
        			$xml .= $this->export_endBlock($unitParentPath, 'units');
        		}
        		
        		
        		// Finally add closing tag for this module
        		$xml .= $this->export_endBlock($parentPath, 'module');
        	}
        	
        	$xml .= $this->export_endBlock(false, 'modules');
        }
        
        // Close parent tag
        $xml .= "$padding</$parentNode>";

        return $xml;
	}
	
	
	/**
	 * Handle exporting the XML for all of the questions.
	 * 
	 * @param String The XML for the quiz in this unit.
	 */
	function export_content_handleQuestionsAndTags()
	{
		$xml = false;
		
		if (empty($this->questionList)) {
			return $xml;
		}
		
		// Copied so we can update the master list only without adding to the work that this loop does.
		$questionIDListForExpanding = $this->questionList;
		
		// ### Expand Random Questions - If we have any random questions - we need all questions that they use.
		foreach ($questionIDListForExpanding as $questionID => $singleQuestionDetails)
		{
			// We've got a random selection, so we need to get all questions that fall into this 
			// particular selection, and remove this random selection before it's exported.
			if ('random_selection' == $singleQuestionDetails->question_type)
			{
				// Expand the tags into an array.
				$decodedTags = WPCW_quiz_RandomSelection::decodeTagSelection($singleQuestionDetails->question_question);				
				if (!empty($decodedTags))
				{
					// Got any questions for these tags?
					$questionsForTag = WPCW_quiz_RandomSelection::questionSelection_getAllQuestionsFromTags($decodedTags);
						
					// Yep, so add them to the list, ignoring duplicates.
					if (!empty($questionsForTag)) {
						$this->questionList = $this->questionList + $questionsForTag;
					}
						
					// If we have a whole pool flag, then we need to get all questions that exist. But this code
					// doesn't check for the whole pool flag, as this export is an expensive process anyway, so 
					// for simplicity, we're not checking for the single exception.
				}
				
				// Now remove this question, as it's a random question that can't be exported.
				unset($this->questionList[$questionID]);
				
			} // end if random selection check			
		}
		
		global $fieldsToProcess_quizzes, $fieldsToProcess_quiz_questions;
		$questionParentPath = '/questions';
		
		$newQuestionIndex = 1;
		
		foreach ($this->questionList as $questionID => $singleQuestionDetails)
		{
			// Check question is valid - just in case.
			$questionDetails = WPCW_questions_getQuestionDetails($questionID, true);
			if (!$questionDetails) {
				continue;
			}
			
			// Create a new hash for this question ID for the XML file so that we can map
			// questions to quizzes. Intentionally using odd numbers only, to help with spotting
			// any potential errors with import.
			$this->questionList[$questionID]->question_hash = $questionDetails->question_hash = 'wpcwqid_' . $newQuestionIndex;
			$newQuestionIndex += 2; // Increment by 2, to allow us to spot issues.
			
			// ### Question - Start 
			$xml .= $this->export_objectToXML('question', false, $questionDetails, $fieldsToProcess_quiz_questions, $questionParentPath, false);
			
			// Debug - check we have all the right fields for this question
        	//$this->debug_courseCheckFieldsWeHave($questionDetails, $fieldsToProcess_quiz_questions);
				
				// ### Questions - question_data_answers
				// Handle the serialized 'question_data_answers' field e.g.
				/*
				 [question_data_answers] => Array
                 	(
						[1] => Array
                        (
							[answer] => RHJpenpsZQ==
						)

                            [2] => Array
                                (
                                    [answer] => RHJpcA==
                                )

                            [3] => Array
                                (
                                    [answer] => Q2xvdWRidXJzdA==
                                )

                            [4] => Array
                                (
                                    [answer] => Q2F0cyAmIERvZ3M=
                                )

                        )
					 */
				$questionDetails->question_data_answers = maybe_unserialize($questionDetails->question_data_answers);
						
				// Use this to numerically order the answers
				$questionidx = 1;
					
        		if (!empty($questionDetails->question_data_answers) && is_array($questionDetails->question_data_answers))
				{
					$dataToBeExported = array();
					foreach ($questionDetails->question_data_answers as $idx => $details)
					{
						$dataToBeExported['possible_answer_' . $questionidx] = $details['answer'];
						
						// Do we have an image for this answer? If so, then add it with it's own tag for simplcity.
						if (isset($details['image'])) {
							$dataToBeExported['possible_answer_' . $questionidx . '_image'] = $details['image'];
						}
						
						
						$questionidx++;
					}
						
					$xml .= $this->export_arrayToXML('question_data_answers', false, $dataToBeExported, false, $questionParentPath . '/question', '/question_data_answers');
				}
				
				
				// ###ÊQuestion - Tags - Start
				if (!empty($questionDetails->tags))
				{
					$tagParentPath = $questionParentPath . '/question';
					$xml .= $this->export_startBlock($tagParentPath, 'tags');
					
					// Render tags as question->tags->tag
					foreach ($questionDetails->tags as $singleTag)
					{
						$xml .= $this->export_textData('tag', $singleTag->question_tag_name, $tagParentPath . '/tags/');
					}
					
					// ###ÊQuestion - Tags - End
					$xml .= $this->export_endBlock($questionParentPath . '/question', 'tags');
				}
			
			// ### Question - End
			$xml .= $this->export_endBlock($questionParentPath, 'question');	
			flush();
		}
		
		return $xml;
	}
	
	
	/**
	 * Handle exporting the XML for a quiz within a single unit.
	 * 
	 * @param Object $unitObj The unit with a quiz to export.
	 * @param String $unitParentPath The parent path for the unit.
	 * 
	 * @param String The XML for the quiz in this unit.
	 */
	function export_content_handleQuizzes($unitObj, $unitParentPath)
	{
		$xml = false;
		
		global $fieldsToProcess_quizzes, $fieldsToProcess_quiz_questions;
		
		// Show the quiz for this unit here... (if there are any).
		if (isset($unitObj->extradata_quiz_details) && !empty($unitObj->extradata_quiz_details))
		{
        	$quizObj = $unitObj->extradata_quiz_details;        	
        					
			// ###ÊQuizzes - Start
			// Expecting just 1 quiz per unit, no more.
			$quizzesParentPath = $unitParentPath . '/quizzes';
			$xml .= $this->export_startBlock($quizzesParentPath, 'quizzes');

			
				$quizParentPath = $quizzesParentPath . '/quiz';
			
				// ###ÊQuiz - Start
				$xml .= $this->export_objectToXML('quiz', false, $quizObj, $fieldsToProcess_quizzes, $quizParentPath, false);
				

					// ### Quiz Detail - show_answers_settings (serialized data)
					$quizObj->show_answers_settings = maybe_unserialize($quizObj->show_answers_settings);
					if (!empty($quizObj->show_answers_settings) && is_array($quizObj->show_answers_settings))
					{
						$innerPath = $quizParentPath . '/show_answers_settings';						
						$xml .= $this->export_arrayToXML('show_answers_settings', false, $quizObj->show_answers_settings, false, $innerPath, '/show_answers_settings');
					}
					
					// ### Quiz Detail - quiz_paginate_questions_settings (serialized data)
					$quizObj->quiz_paginate_questions_settings = maybe_unserialize($quizObj->quiz_paginate_questions_settings);
					if (!empty($quizObj->quiz_paginate_questions_settings) && is_array($quizObj->quiz_paginate_questions_settings))
					{
						$innerPath = $quizParentPath . '/quiz_paginate_questions_settings';						
						$xml .= $this->export_arrayToXML('quiz_paginate_questions_settings', false, $quizObj->quiz_paginate_questions_settings, false, $innerPath, '/quiz_paginate_questions_settings');
					}
					
        					
					// ### Questions
	        		$questionsParentPath = $quizParentPath . '/questions';
	        		$xml .= $this->export_startBlock($questionsParentPath, 'questions');
	        		
	        		$questionParentPath = $questionsParentPath . '/question';
	        		$tagSelectionsPath   = $questionParentPath. '/tag_selections/';
	        		$tagSelectionPath   = $tagSelectionsPath. '/tag_selection';
	        					
	        		if (!empty($quizObj->questions))
	        		{
	        			$questionOrder = 1;
	        			foreach ($quizObj->questions as $singleQuestion)
	        			{	        				
	        				if ('random_selection' == $singleQuestion->question_type)
	        				{
        						// Get the hash and the order of the question
        						$questionDetailsByHash = array(
        							'question_type' 	=> 'random_selection',
        							'question_order' 	=> $questionOrder++
        						);        						
        						
        						// Render the details as a single question.
        						$xml .= $this->export_arrayToXML('question', false, $questionDetailsByHash, false, $questionsParentPath . '/', false);
        						
        							// ### Start tag selections
									$xml .= $this->export_startBlock($questionParentPath . '/', 'tag_selections');
        						
		        					// Decode the tags and add them
		        					$decodedTags = WPCW_quiz_RandomSelection::decodeTagSelection($singleQuestion->question_question);				
									if (!empty($decodedTags))
									{
										foreach ($decodedTags as $tagType => $tagDetails)
										{
											// Whole pool - use this as a single string.
											if ('whole_pool' == $tagType) {
												$xml .= $this->export_textDataWithAttributes('tag_selection', 'whole_pool', $tagSelectionPath, array('count' => $tagDetails['count']));
											}
											
											// Just a normal tag
											else {
												$xml .= $this->export_textDataWithAttributes('tag_selection', $tagDetails['name'], $tagSelectionPath, array('count' => $tagDetails['count']));
											}
										}
									}
									
									// ### End tag selections
									$xml .= $this->export_endBlock($questionParentPath . '/', 'tag_selections');
								
								$xml .= $this->export_endBlock($questionsParentPath.'/', 'question');
	        				}
	        				
	        				// Normal question
	        				else
	        				{
	        					if (isset($this->questionList[$singleQuestion->question_id]))
	        					{
	        						// Get the hash details for the question so we can use the hash rather than quiz details.
	        						$storedQuestionDetails = $this->questionList[$singleQuestion->question_id];
	        						
	        						// Get the hash and the order of the question
	        						$questionDetailsByHash = array(
	        							'question_type' 	=> 'fixed',
	        							'question_hash' 	=> $storedQuestionDetails->question_hash,
	        							'question_order' 	=> $questionOrder++
	        						);
	        						
	        						// Render the details as a single question.
	        						$xml .= $this->export_arrayToXML('question', false, $questionDetailsByHash, false, $questionParentPath, '/question');
	        					}
	        					
	        					// Should never be false, but check anyway and log so that we can debug in case it occurs.
	        					else {
	        						error_log(__('Error exporting question in quiz.', 'wp_courseware') . '(' . print_r($singleQuestion, true) . ')');
	        					}
	        				} // end question type
	        				
	        			} // end foreach
	        		}
	        					
	        		// ###ÊQuestions
	        		// End the 'Quizzes' wrapper
	        		$xml .= $this->export_endBlock($questionsParentPath, 'questions');
        					
	        		$xml .= $this->export_content_handleQuizzes_customFeedbackMessages($quizObj, $quizParentPath);
	        		
        					
        		// ### Quiz - End 
        		$xml .= $this->export_endBlock($quizParentPath, 'quiz');
        		
			// ###ÊQuizzes - End			
			$xml .= $this->export_endBlock($quizzesParentPath, 'quizzes');
			
			//error_log(print_r($quizObj, true));
        					
		} // end of check for quiz data.
        return $xml;
	}
	
	
	/**
	 * Export custom feedback messages for a specific quiz.
	 */
	function export_content_handleQuizzes_customFeedbackMessages($quizObj, $feedbackParentPath)
	{
		$xml = false;
		$feedbackPath = $feedbackParentPath . '/custom_feedback_msgs';
		
		global $fieldsToProcess_quiz_custom_feedback;
		
		// Check for messages and render in the XML
		$messageList = WPCW_quizzes_feedback_getFeedbackMessagesForQuiz($quizObj->quiz_id);
		if (!empty($messageList))
		{
			// Start msgs block
			$xml .= $this->export_startBlock($feedbackPath, 'custom_feedback_msgs');
			
				// Show each single message
				foreach ($messageList as $singleMessage)
				{
					// Add the name of the tag rather than the ID, so that this can be matched
					// up later on import.
					$tagDetails = WPCW_questions_tags_getTagDetails($singleMessage->qfeedback_tag_id);
					$singleMessage->qfeedback_tag_name = $tagDetails->question_tag_name;
					
					$xml .= $this->export_objectToXML('custom_feedback_msg', false, $singleMessage, $fieldsToProcess_quiz_custom_feedback, $feedbackPath . '/', '/custom_feedback_msg');
				}
			
			// End msgs block
			$xml .= $this->export_endBlock($feedbackPath, 'custom_feedback_msgs');
		}
		
		return $xml;
	}
	
	
	/**
	 * Turn an object into XML and return it.
	 * 
	 * @param String $nodeName The name of the block to create from the object.
	 * @param Array $attributes The key => value list of items to save as attributes for the XML block.
	 * @param Object $rawDetails The raw object data.
	 * @param Array $fieldsToProcess The list of fields to extract from the raw data into XML.
	 * @param String $path The parent path to export this data to.
	 * @param Boolean $closeTag If true, close the final XML tag. If false, don't add the final section XML tags.
	 * 
	 * @return String The XML for this object.
	 */
	private function export_objectToXML($nodeName, $attributes, $rawDetails, $fieldsToProcess, $path, $closeTag = true)
	{
		$padding = $this->export_indent($path);
		$xml = false;
		
		// Open tag with any attributes
		$newPath = "$path/$nodeName";
        $padding = $this->export_indent($newPath);
        $xml .= "$padding<$nodeName";
        
        // See if there are any attributes to add to the node
        if ($attributes)
        {
        	foreach ($attributes as $name => $value)
        	{
        		$xml .= " $name=\"$value\"";
        	}
        }
        // Close tag
        $xml .= '>';		
		
		
        // Only include fields included in our list of details
		foreach ($fieldsToProcess as $fieldToUse)
        {
        	if (isset($rawDetails->$fieldToUse)) {
        		$xml .= $this->export_textData($fieldToUse, $rawDetails->$fieldToUse, $newPath.'/');
        	}
        }
		
        // Closing tag
        if ($closeTag) {
        	$xml .= "$padding</$nodeName>";
        }
		
		return $xml;
	}
	
	
/**
	 * Turn an object into XML and return it.
	 * 
	 * @param String $nodeName The name of the block to create from the object.
	 * @param Array $attributes The key => value list of items to save as attributes for the XML block.
	 * @param Object $rawDetails The raw object data.
	 * @param Array $fieldsToProcess The list of fields to extract from the raw data into XML.
	 * @param String $path The parent path to export this data to.
	 * @param Boolean $closeTag If true, close the final XML tag. If false, don't add the final section XML tags.
	 * 
	 * @return String The XML for this object.
	 */
	private function export_arrayToXML($nodeName, $attributes, $rawDetails, $fieldsToProcess, $path, $closeTag = true)
	{
		$padding = $this->export_indent($path);
		$xml = false;
		
		// Open tag with any attributes
		$newPath = "$path/$nodeName";
        $padding = $this->export_indent($newPath);
        $xml .= "$padding<$nodeName";
        
        // See if there are any attributes to add to the node
        if ($attributes)
        {
        	foreach ($attributes as $name => $value)
        	{
        		$xml .= " $name=\"$value\"";
        	}
        }
        // Close tag
        $xml .= '>';		
		
        // If we want to export a selection of fields, then use it.
		if ($fieldsToProcess)
		{        
	        // Only include fields included in our list of details
			foreach ($fieldsToProcess as $fieldToUse)
	        {
	        	if (isset($rawDetails[$fieldToUse])) {
	        		$xml .= $this->export_textData($fieldToUse, $rawDetails[$fieldToUse], $newPath.'/');
	        	}
	        }
		}
		
		// Just export all of the key value pairs in the array.
		else
		{
			foreach ($rawDetails as $key => $value) {
	        	$xml .= $this->export_textData($key, $value, $newPath.'/');
	        }	
		}
		
        // Closing tag
        if ($closeTag) {
        	$xml .= "$padding</$nodeName>";
        }
		
		return $xml;
	}
	

	/**
	 * Export any data that contains text/HTML, doing it safely to escape characters.
	 * 
	 * @param String $parentNode The name of the XML node to create for this data.
	 * @param String $value The actual data to save.
	 * @param String $path The path of this text data.
	 */
	private function export_textData($parentNode, $value, $path)
	{		
		$xml = "";
		$padding = $this->export_indent($path);
		
		$xml .= "$padding<$parentNode>" . $this->export_cdata($value) . "</$parentNode>";
		
        return $xml;
	}
	
	/**
	 * Export any data that contains text/HTML, doing it safely to escape characters.
	 * 
	 * @param String $parentNode The name of the XML node to create for this data.
	 * @param String $value The actual data to save.
	 * @param String $path The path of this text data.
	 * @param Array $attributes The list of attributes to add to the field.
	 */
	private function export_textDataWithAttributes($parentNode, $value, $path, $attributes)
	{		
		$xml = "";
		$padding = $this->export_indent($path);
		
		$attributeCode = false;
		if (!empty($attributes))
		{
			foreach ($attributes as $attribkey => $attribvalue)  {
				$attributeCode .= $attribkey .'="' .$attribvalue . '" ';
			}
		}
		
		// Remove any final spacing
		$attributeCode = trim($attributeCode);
		
		$xml .= "$padding<$parentNode $attributeCode>" . $this->export_cdata($value) . "</$parentNode>";
		
        return $xml;
	}
	
	
	
	

	
	/**
	 * Start a block of content 
	 * @param String $parentPath The current path of the parent object.
	 * @param String $thisPathName The new path string to append.
	 * @return The indented tag.
	 */
	private function export_startBlock($parentPath, $thisPathName)
	{
        $path = "$parentPath/$thisPathName";
        $padding = $this->export_indent($path);
        return "$padding<$thisPathName>";
	}
	
	
	/**
	 * End a block of content 
	 * @param String $parentPath The current path of the parent object.
	 * @param String $thisPathName The new path string to append.
	 * @return The indented tag.
	 */	
	private function export_endBlock($parentPath, $thisPathName)
	{
        $path = "$parentPath/$thisPathName";
        $padding = $this->export_indent($path);
        return "$padding</$thisPathName>";
	}

	
	/**
	 * Export a single line of data in XML.
	 */
	private function export_cdata($value)
	{
		// binary 00010 - sometimes it's not defined with PHP.
		if (!defined('ENT_XML1')) {
			define('ENT_XML1',    8);  
		}
        return "<![CDATA[" . htmlspecialchars($value, ENT_QUOTES | ENT_XML1, "UTF-8") . "]]>";
    }
	

	
	/**
	 * Indents the XML according to the depth of the path.
	 */
    private function export_indent($path)
    {
        $depth 	= sizeof(explode("/", $path)) - 1;
        $indent = "";
        $indent = str_pad($indent, $depth, "\t");
        return "\r\n" . $indent;
    }
    
    
	/**
	 * Check what fields we have in the fields to check for in an object.
	 */
	function debug_courseCheckFieldsWeHave($objectDetails, $fieldsToProcess)
	{
		error_log('-------- START --------');
		foreach ($objectDetails as $singleFieldName => $value)
		{
			$gotIt = false;
			if (!in_array($singleFieldName, $fieldsToProcess))
			{
				$gotIt = __('Missing', 'wp_courseware');
			}
			
			error_log(sprintf("%-'-50s%s", $singleFieldName, $gotIt));
		}
		error_log('-------- END --------');
	}
}


/**
 * Retrieve all of the questions associated with this course.
 * 
 * @param Integer $courseID The ID of the course to get the questions for.
 * @return Array The list of question objects.
 */
function WPCW_questions_getAllQuestionsforCourse($courseID)
{
	global $wpcwdb, $wpdb;
	$wpdb->show_errors();
	
	// Get a list of all quizzes, as we'll need this to build a list of questions.
	$quizIDList = $wpdb->get_col($wpdb->prepare("
    	SELECT quiz_id 
    	FROM $wpcwdb->quiz q
    		LEFT JOIN $wpcwdb->units_meta um ON um.unit_id = q.parent_unit_id
    	WHERE q.parent_course_id = %d 
   	", $courseID));
	
	if (empty($quizIDList)) {
		return false;
	}
	
	$quizIDListStr = implode(',', $quizIDList);
	
	// Now we need to get a list of all question IDs being used in all of these quizzes.
	// Just getting IDs and type to save memory.
	$questions = $wpdb->get_results("
			SELECT qq.question_id, qq.question_type, qq.question_question
			FROM $wpcwdb->quiz_qs qq
				LEFT JOIN $wpcwdb->quiz_qs_mapping qqm ON qqm.question_id = qq.question_id
			WHERE qqm.parent_quiz_id IN ($quizIDListStr)   
			GROUP BY qq.question_id 
			");
	
	$list = array();
	if (!empty($questions))
	{
		// Create ID => details
		foreach ($questions as $singleQuestion)  {
			$list[$singleQuestion->question_id] = $singleQuestion;
		}
		
		return $list;
	}
	
	return false;
}


?>