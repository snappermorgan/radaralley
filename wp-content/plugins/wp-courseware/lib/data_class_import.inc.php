<?php

// Contains a list of what fields to import.
include_once 'data_common.inc.php';

/**
 * A class for importing an XML file and creating a training course from it.
 */
class WPCW_Import 
{
	/**
	 * Import a course using the specified XML filename. Returns no errors if it worked correctly.
	 * 
	 * @param String $xmlFileName The name of the file to import.
	 * @return Array An array containing 'errors' with a list of errors, and 'course_id' of the newly created course ID.
	 */
	public static function importTrainingCourseFromXML($xmlFileName)
	{
		$errorList = array();
		
		libxml_use_internal_errors(true);
		$xml = simplexml_load_file($xmlFileName);
		
		// Replaced with actual ID of newly created course.
		$newCourseID = false;		
		
		
		// Error loading XML file, store errors and return them.
		if (!$xml)
		{	    	
		    foreach(libxml_get_errors() as $error) {
		        $errorList[] = sprintf(__('Line %d, Column %d, Error: %s', 'wp_courseware'), $error->line, $error->column, $error->message);
		    }
		}
		
		// No problems loading XML
		else {  
			$import = new WPCW_Import($xml);
			
			// At some point, might pass back errors from here.
			$errorList = $import->importCourseIntoDatabase();
			if (!$errorList) {			
				$newCourseID = $import->getNewCourseID();
			}
		}
        
		
        // Return false if no errors, for easier error checking
		if (count($errorList) == 0) {
			$errorList = false;
		}

		// Return details back to code for processing
        return array(
        	'errors' 	=> $errorList, 
        	'course_id' => $newCourseID
       	);
	}
	
	
	/**
	 * Reference to XML object used to import the course data.
	 */
	protected $xml;
	
	/**
	 * A list of any errors encountered.
	 * @var Array
	 */
	protected $errorList;
	
	/**
	 * The ID of the newly created course, or 0 if the course has not been created yet.
	 * @var Integer
	 */
	protected $course_id;
	
	/**
	 * Stores the current ordering of the unit for the course. This is reset
	 * each time a course is imported.
	 * @var Integer
	 */
	protected $unit_order;
	
	
	/**
	 * Stores the questions that have been exported.
	 * @var Array
	 */
	protected $questionData;
	
	
	/**
	 * Default constructor - takes a valid XML object as a parameter.
	 * 
	 * @param Object $xml The XML object with the training course data.
	 */	
	function __construct($xml)
	{
		$this->errorList = array();
		$this->xml = $xml;
		$this->course_id = false;
		$this->questionData = false;
	}	
	
	/**
	 * Function that performs the actual course import.
	 * @return Integer The ID of the newly imported course.
	 */
	public function importCourseIntoDatabase()
	{
		if (!current_user_can('manage_options')) {
			return $this->returnErrorList(__('Sorry, you\'re not allowed to import courses into WordPress.', 'wp_courseware'));
		}
		
		// ### 1) Extract XML data into a clean array of information.
		// The functions below may handle detailed verification of data in 
		// future releases.
		$courseData = $this->loadCourseData();		

		// ### 2) Turn the course into an actual database entry
		if (count($courseData) == 0) {			
			return $this->returnErrorList(__('There was no course data to import.', 'wp_courseware'));
		}
		
		// ### 3) Now try to load module and question data. Most load question data first, as the load module data 
		// 		  function relies on it.
		$this->questionData = $this->loadQuestionData();
		$moduleData = $this->loadModuleData();
		
		
		
		global $wpdb, $wpcwdb;
		$wpdb->show_errors();
		
		$queryResult = $wpdb->query(arrayToSQLInsert($wpcwdb->courses, $courseData));
		
		// Check query succeeded.
		if ($queryResult === FALSE) {
			return $this->returnErrorList(__('Could not create course in database.', 'wp_courseware'));
		}
		$this->course_id = $wpdb->insert_id;
		
		// Track how many units we add
		$unitCount = 0;
		$this->unit_order = 0;
		
		// ### 4) Check for the module data, and then try to add this to the system
		if ($moduleData)
		{	
			$moduleCount = 0;
			
			foreach ($moduleData as $moduleItem)
			{
				// Extract Unit Data from module info, so it doesn't interfere with database add
				$unitData = $moduleItem['units'];
				unset($moduleItem['units']);
				
				$moduleCount++;
				
				// Add parent course details, plus order details.
				$moduleItem['parent_course_id'] 	= $this->course_id;
				$moduleItem['module_order'] 		= $moduleCount;
				$moduleItem['module_number'] 		= $moduleCount;
										
				$queryResult = $wpdb->query(arrayToSQLInsert($wpcwdb->modules, $moduleItem));
				
				// Check query succeeded.
				if ($queryResult === FALSE) {
					return $this->returnErrorList(__('There was a problem inserting the module into the database.', 'wp_courseware'));
				}
				
				$currentModuleID = $wpdb->insert_id;

				// ### 4) Check for any units
				$unitCount += $this->addUnitsToDatabase($unitData, $currentModuleID);				
			}
		} // end if $moduleData
		

		// Update unit counts
		// 31st May 2013 - V1.26 - Fix - Incorrectly referring to $course_id - which is empty.
		// Changed to $this->course_id to fix issue.
		$courseDetails = WPCW_courses_getCourseDetails($this->course_id);
		do_action('wpcw_course_details_updated', $courseDetails);
		
		// Return any errors if there are any
		return $this->errorList;
	}
	
	
	/**
	 * Try to add the units to the database.
	 * @param Array $unitData The list of units to add
	 * @param Integer $moduleID The ID of the parent module
	 * @return Integer The number of units added.
	 */
	private function addUnitsToDatabase($unitData, $moduleID) 
	{
		if (!$unitData || count($unitData) < 1)
			return 0;
			
		global $wpdb, $wpcwdb;
		$wpdb->show_errors();
			
		$unitCount = 0;
		foreach ($unitData as $singleUnit)
		{
			// ### 1 - Create unit as a WP Post
			$unitPost = array(
			     'post_title' 	=> $singleUnit['post_title'],			     
				 'post_name' 	=> $singleUnit['post_name'],
			     'post_status' 	=> 'publish',
			     'post_type' 	=> 'course_unit',
			
				// Since V2.90.
				// 2014-05-05 - Added slashes as wp_insert_post removes them on insert. So add extra layer here
				// before insert to preserve them.
			     'post_content' => addslashes($singleUnit['post_content'])
			  );
			
			// Insert the post into the database
			$unitID = wp_insert_post($unitPost);
			if (!$unitID) {
				$this->errorList[] = sprintf(__('Could not create course unit "%s". So this was skipped.', 'wp_courseware'), $singleUnit['post_title']);
				continue;
			}
			
			// ### 2 - Update the post with the meta of the related module
			update_post_meta($unitID, 'wpcw_associated_module', $moduleID);
			
			// ### 3 - Create the meta data for WPCW for this unit
			$unitCount++;
			$this->unit_order += 10;
			
			$unitmeta = array();
			$unitmeta['unit_id'] 			= $unitID;
			$unitmeta['parent_module_id'] 	= $moduleID;
			$unitmeta['parent_course_id'] 	= $this->course_id;
			$unitmeta['unit_order'] 		= $this->unit_order; // The order overall in whole course
			$unitmeta['unit_number'] 		= $unitCount; 		 // The number of the unit within module			
			
			// This is an update, as wp_insert_post will create meta entry.
			$queryResult = $wpdb->query(arrayToSQLUpdate($wpcwdb->units_meta, $unitmeta, 'unit_id'));
				
			// Check query succeeded.
			if ($queryResult === FALSE) {
				return $this->returnErrorList(__('There was a problem adding unit meta data into the database.', 'wp_courseware'));
			}
			
			// ### 4 - Create the meta data for the quiz entry (if there are any)
			if (isset($singleUnit['quizzes']) && !empty($singleUnit['quizzes']))
			{
				$quizData = $singleUnit['quizzes'];
				unset($singleUnit['quizzes']);
				
				// And add the quizzes for this unit.
				$this->loadQuizData_addQuizzesToDatabase($quizData, $unitID);	
			}			
		}

		return $unitCount;
	}
	
	
	
	
	/**
	 * Function that only returns a list of errors if there were any errors, or false
	 * if there are none.
	 * 
	 * @param String $messageToAdd If specified, add this message first before returning it.
	 */
	private function returnErrorList($messageToAdd = false)
	{
		if ($messageToAdd) {
			$this->errorList[] = $messageToAdd;
		}
		
		if (count($this->errorList) > 0) {
			return $this->errorList;
		}
		return false;
	}
	
	
	/**
	 * Returns the newly creatd course ID.
	 */
	public function getNewCourseID() {
		return $this->course_id;
	}
	
	
	/**
	 * Loads the data needed to create the course.
	 */
	private function loadCourseData()
	{
       	global $fieldsToProcess_course;
        
        $dbdata = array();
        foreach ($fieldsToProcess_course as $fieldName)
        {
        	// Put data into database, but assume data is blank if not set in XML.        	
        	$dbdata[$fieldName] = (isset($this->xml->settings->$fieldName) ? (string)$this->xml->settings->$fieldName : '');
        	
        	// Use html_entity_decode to remove HTML entities.
        	$dbdata[$fieldName] = html_entity_decode($dbdata[$fieldName], ENT_QUOTES);
        }
        
        // Update course title to use 'imported'
        $dbdata['course_title'] .= __(' (Imported)', 'wp_courseware');
        
        return $dbdata;
	}
	
	
	/**
	 * Loads the data needed to create the modules and units.
	 */
	private function loadModuleData()
	{	
		// Need at least 1 module to continue.
		if (!isset($this->xml->modules) && !isset($this->xml->modules->module[0])) {
			return false;
		}
		
		$moduleData = array();
		
		// Modules will contain unit data if units are being exported too. 
		foreach ($this->xml->modules->module as $singleModule)
		{
			$moduleData[] = $this->loadModuleData_Single($singleModule);
		}
		
		return $moduleData;
	}
	
	

	/**
	 * Loads the data needed to create the questions.
	 */
	private function loadQuestionData()
	{	
		// Need at least 1 module to continue.
		if (!isset($this->xml->questions) && !isset($this->xml->questions->question[0])) {
			return false;
		}
		
		$questionData = array();

		// Questions will contain a hash ID which we will match up in the quizzes later. 
		foreach ($this->xml->questions->question as $singleQuestion)
		{
			// Extract the raw details from the XML to use
			$rawData = $this->loadQuestionData_fullData($singleQuestion);

			// Need the raw hash ID to allow us to do mappings.
			if (!empty($rawData) && $hashID = WPCW_arrays_getValue($rawData, 'question_hash'))
			{
				// Don't add raw hash to database
				unset($rawData['question_hash']);
				
				// Add the details to the database
				$questionID = $this->loadQuestionData_addQuestionToDatabase($rawData);
				
				// Database insert worked, so now add the DB ID to the hash in our list. 
				if ($questionID > 0) {
					$questionData[$hashID] = $questionID;
				}
			} // end if there's any raw data.
		} // end foreach
		
		return $questionData;
	}
	
	
	
	/**
	 * Extract details from the XML for this single module. 
	 */
	private function loadModuleData_Single($singleModule)
	{
		global $fieldsToProcess_modules;
        
        $dbdata = array();
        foreach ($fieldsToProcess_modules as $fieldName)
        {
        	// Put data into database, but assume data is blank if not set in XML.
        	$dbdata[$fieldName] = (isset($singleModule->$fieldName) ? (string)$singleModule->$fieldName : '');
        	
        	// Use html_entity_decode to remove HTML entities.
        	$dbdata[$fieldName] = html_entity_decode($dbdata[$fieldName], ENT_QUOTES);
        }
        
        // Check for units in this module
        if (isset($singleModule->units) && isset($singleModule->units->unit[0]))
        {
        	$dbdata['units'] = array();
        	foreach ($singleModule->units->unit as $singleUnit)  	
        	{
        		$dbdata['units'][] = $this->loadUnitData_Single($singleUnit);
        	}
        }
        
        else {
        	$dbdata['units'] = false;	
        }
        
        return $dbdata;
	}
	
	
	/**
	 * Extract details from the XML for this single unit. 
	 */
	private function loadUnitData_Single($singleUnit)
	{
        global $fieldsToProcess_units;
        
        $dbdata = array();
        foreach ($fieldsToProcess_units as $fieldName)
        {
        	// Put data into database, but assume data is blank if not set in XML.
        	$dbdata[$fieldName] = (isset($singleUnit->$fieldName) ? (string)$singleUnit->$fieldName : '');
        	
        	// Use html_entity_decode to remove HTML entities.
        	$dbdata[$fieldName] = html_entity_decode($dbdata[$fieldName], ENT_QUOTES);
        }
        	
        // Check for quizzes in this unit	        
        if (isset($singleUnit->quizzes) && isset($singleUnit->quizzes->quiz[0]))
        {
        	$dbdata['quizzes'] = array();
        	foreach ($singleUnit->quizzes->quiz as $singleQuiz)  	
        	{	        		
        		$dbdata['quizzes'][] = $this->loadQuizData_Single($singleQuiz);
        	}
        }
        
        else {
        	$dbdata['quizzes'] = false;	
        }
        
        
        return $dbdata;
	}

	
	/**
	 * Extract details from the XML for this single quiz (that's associated with a single unit).
	 */
	private function loadQuizData_Single($singleQuiz)
	{
        global $fieldsToProcess_quizzes;
        
        // ### 1 - Extract fields we want
        $dbdata = array();
        foreach ($fieldsToProcess_quizzes as $fieldName)
        {
        	// Put data into database, but assume data is blank if not set in XML.
        	$dbdata[$fieldName] = (isset($singleQuiz->$fieldName) ? (string)$singleQuiz->$fieldName : '');
        	
        	// Use html_entity_decode to remove HTML entities.
        	$dbdata[$fieldName] = html_entity_decode($dbdata[$fieldName], ENT_QUOTES);
        }        	
        
        
        // ### 2 - Inner - show_answers_settings 
		global $fieldsToProcess_quizzes_inner__show_answers_settings;
		$dbdata['show_answers_settings'] = array();
		
		foreach ($fieldsToProcess_quizzes_inner__show_answers_settings as $innerName => $validValueList)
		{
			// Set the default value for this field, just in case it doesn't appear in the XML
			reset($validValueList);
        	$valueForSettingFromXML = current($validValueList);
						
			// Check value is set....
			if (isset($singleQuiz->show_answers_settings->$innerName)) 
			{
				// And that it's valid (i.e. in the list of possible values).
				$valueFound = (string)$singleQuiz->show_answers_settings->$innerName;
				if (in_array($valueFound, $validValueList))
				{
					$valueForSettingFromXML = $valueFound;
        		}
        	}
        	
        	// Use the found value or the default and update the setting.
        	$dbdata['show_answers_settings'][$innerName] = $valueForSettingFromXML;        		
        }
        
        
		// ### 3 - Inner - quiz_paginate_questions_settings 
		global $fieldsToProcess_quizzes_inner__quiz_paginate_questions_settings;
		$dbdata['quiz_paginate_questions_settings'] = array();
		
		foreach ($fieldsToProcess_quizzes_inner__quiz_paginate_questions_settings as $innerName => $validValueList)
		{
			// Set the default value for this field, just in case it doesn't appear in the XML
			reset($validValueList);
        	$valueForSettingFromXML = current($validValueList);
						
			// Check value is set....
			if (isset($singleQuiz->quiz_paginate_questions_settings->$innerName)) 
			{
				// And that it's valid (i.e. in the list of possible values).
				$valueFound = (string)$singleQuiz->quiz_paginate_questions_settings->$innerName;
				if (in_array($valueFound, $validValueList))
				{
					$valueForSettingFromXML = $valueFound;
        		}
        	}
        	
        	// Use the found value or the default and update the setting.
        	$dbdata['quiz_paginate_questions_settings'][$innerName] = $valueForSettingFromXML;        		
        }
                
        	        	
		// ### 4 - Questions - translate into a mapping
		/*
		<questions>
			<question>
				<question_type><![CDATA[fixed]]></question_type>
				<question_hash><![CDATA[wpcwqid_1]]></question_hash>
				<question_order><![CDATA[1]]></question_order>
			</question>
			<question>
				<question_type><![CDATA[fixed]]></question_type>
				<question_hash><![CDATA[wpcwqid_3]]></question_hash>
				<question_order><![CDATA[2]]></question_order>
			</question>
			<question>
				<question_type><![CDATA[fixed]]></question_type>
				<question_hash><![CDATA[wpcwqid_5]]></question_hash>
				<question_order><![CDATA[3]]></question_order>
			</question>
		</questions> 	 
		*/       
		if (isset($singleQuiz->questions) && isset($singleQuiz->questions->question[0]))
		{
			$dbdata['questions'] = array();
			foreach ($singleQuiz->questions->question as $singleQuestion)  	
			{
				$dbdata['questions'][] = $this->loadQuestionData_quizMappingData($singleQuestion);
			}
		}
	        
		else {
			$dbdata['questions'] = false;	
		}
		
		
		// ### 5 - Custom Feedback - translate ready to use. Assume tags loaded by the time we get here.
		if (isset($singleQuiz->custom_feedback_msgs) && isset($singleQuiz->custom_feedback_msgs->custom_feedback_msg[0]))
		{
			$dbdata['custom_feedback'] = array();
			foreach ($singleQuiz->custom_feedback_msgs->custom_feedback_msg as $singleFeedbackMsg)  	
			{
				$dbdata['custom_feedback'][] = $this->loadQuizData_customFeedback($singleFeedbackMsg);
			}
		}
	        
		else {
			$dbdata['custom_feedback'] = false;	
		}
        
        return $dbdata;
	}	
	
	
	/**
	 * Extracts the data for the custom feedback directly from the quiz XML data.
	 */
	private function loadQuizData_customFeedback($singleFeedbackMsg)
	{
		global $fieldsToProcess_quiz_custom_feedback;
		
		// ### 1 - Extract fields we want
        $dbdata = array();
        foreach ($fieldsToProcess_quiz_custom_feedback as $fieldName)
        {
        	// Put data into database, but assume data is blank if not set in XML.
        	$dbdata[$fieldName] = (isset($singleFeedbackMsg->$fieldName) ? (string)$singleFeedbackMsg->$fieldName : '');
        	
        	// Use html_entity_decode to remove HTML entities.
        	$dbdata[$fieldName] = html_entity_decode($dbdata[$fieldName], ENT_QUOTES);
        }    
        
        // ### 2 Convert the tag name to a tag ID and remove the name
        $tagNameForMsg = trim($dbdata['qfeedback_tag_name']);
        $dbdata['qfeedback_tag_id'] = 0; // Default of 0 just in case.
        
        if ($tagNameForMsg)
        {
	        $tagDetails = WPCW_questions_tags_getTagDetails_byName($tagNameForMsg);
	        if ($tagDetails)
	        {
	        	// ID for tag
	        	$dbdata['qfeedback_tag_id'] = $tagDetails->question_tag_id;    	
	        }
	        
	        // May have a tag that hasn't been added with questions, so add it. 
	        else
	        {
	        	$wpdb->query($wpdb->prepare("
					INSERT INTO $wpcwdb->question_tags
					(question_tag_name, question_tag_usage) VALUES (%s, 1)  
					", $tagNameForMsg));
				
				$dbdata['qfeedback_tag_id'] = $wpdb->insert_id;
	        }
        }
        
        // Need to remove the name, as we can't use that for the insert.
        unset($dbdata['qfeedback_tag_name']);
        
        return $dbdata;
	}
	

	/**
	 * Try to add the quizzes to the database.
	 * @param Array $quizData The list of quizzes to add
	 * @param Integer $unitID The ID of the parent unit.
	 * @return Integer The number of quizzes added.
	 */
	private function loadQuizData_addQuizzesToDatabase($quizData, $unitID) 
	{
		if (!$quizData || empty($quizData))
			return 0;
			
		global $wpdb, $wpcwdb;
		$wpdb->show_errors();
			
		$quizCount = 0;
		foreach ($quizData as $singleQuiz)
		{
			$quizCount++;
			
			// ### 1 - Initialise with details we need to add dymaically. 
			$quizDetailsToAdd = array(
			     'parent_unit_id' 		=> $unitID,
			     'parent_course_id' 	=> $this->course_id
			  );
			
			// ### 2 - Copy details from the fields as we expect to find them
			global $fieldsToProcess_quizzes;
			foreach ($fieldsToProcess_quizzes as $expectedFieldName)
			{
				// Copy detail - if it exists or not.
				$quizDetailsToAdd[$expectedFieldName] = WPCW_arrays_getValue($singleQuiz, $expectedFieldName);
			}
			
			// ### 3 - Handle the insert of the special arrays that need serialising. We know these exist
			// as we have specifically validated them.
			$quizDetailsToAdd['show_answers_settings'] 				= serialize($singleQuiz['show_answers_settings']); 
			$quizDetailsToAdd['quiz_paginate_questions_settings'] 	= serialize($singleQuiz['quiz_paginate_questions_settings']);
			
									
			// ### 4 - Insert details of this particular quiz into the database.
			$queryResult = $wpdb->query(arrayToSQLInsert($wpcwdb->quiz, $quizDetailsToAdd));
			
			// Store the ID of the newly inserted ID
			$currentQuizID = $wpdb->insert_id;
				
			// Check query succeeded.
			if ($queryResult === FALSE) {
				return $this->returnErrorList(__('There was a problem adding the quiz into the database.', 'wp_courseware'));
			}
			
			// ### 5 - Now associate the detailed questions data with the quizzes.
			$singleQuiz['quiz_id'] = $currentQuizID;
			$this->loadQuizData_addQuestionsAssociations($singleQuiz);
			
			// ### 6 - Now any custom feedback messages.
			if (!empty($singleQuiz['custom_feedback']))
			{
				foreach ($singleQuiz['custom_feedback'] as $singleMessage)
				{
					// Add this quiz ID
					$singleMessage['qfeedback_quiz_id'] = $currentQuizID;
					
					// Now add the feedback to the database
					$wpdb->query(arrayToSQLInsert($wpcwdb->quiz_feedback, $singleMessage));
				}
			}
		}

		return $quizCount;
	}
	
	
	/**
	 * Function used to sort a list of questions by question order.
	 */
	function sortQuestionsByOrder($a, $b)
	{
	    return $a['question_order'] - $b['question_order'];
	}
	
	/**
	 * Create the associations of the questions 
	 * @param Object $singleQuiz The quiz details to check for questions.
	 */
	function loadQuizData_addQuestionsAssociations($singleQuiz)
	{
		// No questons to worry about
		if (!isset($singleQuiz['questions']) || empty($singleQuiz['questions'])) {
			return false;
		}
		
		// ### 1 - Ensure the questions are ordered correctly.
		usort($singleQuiz['questions'], array($this, 'sortQuestionsByOrder'));
		
		global $wpdb, $wpcwdb;
		$wpdb->show_errors();
		
		// ### 2 - Need to now do the question mapping for this quiz.
		$questionOrder = 0;
		foreach ($singleQuiz['questions'] as $singleQuestionToAdd)
		{
			// ### 3 - Fixed - questions are a simple association.
			if ('fixed' == $singleQuestionToAdd['question_type'])
			{
				// Only do association if ID is valid
				if ($singleQuestionToAdd['question_real_id'])
				{
					$wpdb->query($wpdb->prepare("
						INSERT INTO $wpcwdb->quiz_qs_mapping 
						(question_id, parent_quiz_id, question_order)
						VALUES (%d, %d, %d)
					", $singleQuestionToAdd['question_real_id'], $singleQuiz['quiz_id'], $questionOrder++));
					
					// ### 4 - Update the mapping counts now that we've added all of the questions.
					WPCW_questions_updateUsageCount($singleQuestionToAdd['question_real_id']);
					
					// DEFERRED - Report an error here if real ID is invalid?
				}
			}
			
			// ### 4 - Random - questions need a little more work to create these. We essentially
			// add a new question, then map it to the parent quiz.
			else
			{
				$toSaveList = array();
				if (!empty($singleQuestionToAdd['tag_selections']))
				{
					$totalNumberOfSelections = 0;
					
					// ### 5 - Check each selection
					foreach ($singleQuestionToAdd['tag_selections'] as $tagName => $selectionCount)
					{
						// This is the simple one. Assume that selection counts are valid.
						if ('whole_pool' == $tagName) 
						{
							$toSaveList[$tagName] = $selectionCount;
							
							// Need to keep track of how many questions there might be.
							$totalNumberOfSelections += $selectionCount;
						}
						
						// Right, we have a tag. We need to get it's ID.
						else
						{
							// Effectively we'll validate the tags here.
							$tagDetails = WPCW_questions_tags_getTagDetails_byName($tagName);
							if ($tagDetails)
							{
								// Need to create tag_11 => count
								$toSaveList['tag_' . $tagDetails->question_tag_id] = $selectionCount;
								
								// Need to keep track of how many questions there might be.
								$totalNumberOfSelections += $selectionCount;
							}
							
						} // end of selection type
					} // end of loop for tag selections
					
					
					// ### 6 - Now add the question to the database. Usage count is always 1, as we're only
					//         creating this for this quiz.
					$wpdb->query($wpdb->prepare("
						INSERT INTO $wpcwdb->quiz_qs 
						(question_type, question_question, question_usage_count, question_expanded_count)
						VALUES ('random_selection', %s, 1, %d)
					", json_encode($toSaveList), $totalNumberOfSelections));
					
					$newSelectionQuestionID = $wpdb->insert_id;
					
					
					// ### 7 - Now associate with the parent quiz.
					$wpdb->query($wpdb->prepare("
						INSERT INTO $wpcwdb->quiz_qs_mapping 
						(question_id, parent_quiz_id, question_order)
						VALUES (%d, %d, %d)
					", $newSelectionQuestionID, $singleQuiz['quiz_id'], $questionOrder++));
					
					
				} // end of check for tag selections
				
			} // end check of question type.
		}
	}
	
	
	/**
	 * Extract details from the XML that associates a single question with a quiz. The full details
	 * of the question have already been extracted by this point. So this is just the data that maps
	 * a question (or random quiz selection) to a specific quiz.
	 * 
	 * @param String $singleQuestion The details of the question to extract.
	 */
	private function loadQuestionData_quizMappingData($singleQuestion)
	{
		/*
		<questions>
			<question>
				<question_type><![CDATA[fixed]]></question_type>
				<question_hash><![CDATA[wpcwqid_1]]></question_hash>
				<question_order><![CDATA[1]]></question_order>
			</question>
			<question>
				<question_type><![CDATA[fixed]]></question_type>
				<question_hash><![CDATA[wpcwqid_3]]></question_hash>
				<question_order><![CDATA[2]]></question_order>
			</question>
			<question>
				<question_type><![CDATA[fixed]]></question_type>
				<question_hash><![CDATA[wpcwqid_5]]></question_hash>
				<question_order><![CDATA[3]]></question_order>
			</question>
		</questions> 	 
		
		<questions>
			<question>
				<question_type><![CDATA[random_selection]]></question_type>
				<question_order><![CDATA[1]]></question_order>
				<tag_selections>
					<tag_selection count="4"><![CDATA[Dan Test 2]]></tag_selection>
					<tag_selection count="1"><![CDATA[Nate&#039;s Tag]]></tag_selection>
				</tag_selections>
			</question>
			<question>
				<question_type><![CDATA[fixed]]></question_type>
				<question_hash><![CDATA[wpcwqid_7]]></question_hash>
				<question_order><![CDATA[2]]></question_order>
			</question>
			<question>
				<question_type><![CDATA[random_selection]]></question_type>
				<question_order><![CDATA[3]]></question_order>
				<tag_selections>
					<tag_selection count="2"><![CDATA[whole_pool]]></tag_selection>
				</tag_selections>
			</question>
		</questions>
		*/  
		
		$fieldsToProcess = array(
			'question_type',
			'question_hash',
			'question_order',
			'question_type',
		);
		
		// ### 1 - Load main fields
        $dbdata = array();
        foreach ($fieldsToProcess as $fieldName)
        {
        	// Put data into database, but assume data is blank if not set in XML.
        	$dbdata[$fieldName] = (isset($singleQuestion->$fieldName) ? (string)$singleQuestion->$fieldName : '');
        	
        	// Use html_entity_decode to remove HTML entities.
        	$dbdata[$fieldName] = html_entity_decode($dbdata[$fieldName], ENT_QUOTES);
        }
        
        // ### 2 - Load any tag selections for random questions
		if (isset($singleQuestion->tag_selections) && isset($singleQuestion->tag_selections->tag_selection[0]))
        {
        	$dbdata['tag_selections'] = array();
        	foreach ($singleQuestion->tag_selections->tag_selection as $singleTag)  	
        	{
        		// Need selection count - <tag_selection count="4"><![CDATA[Tag Name]]></tag_selection>
        		$count = intval((string)WPCW_arrays_getValue($singleTag, 'count'));
        		
	        	// Use html_entity_decode to remove HTML entities.
	        	$tagName = html_entity_decode((string)$singleTag, ENT_QUOTES);
        		
        		// Add to list of clean tags
        		$dbdata['tag_selections'][$tagName] = $count;
        	}
        }
        
        
        // ### 3 - Attempt to get the real ID of the question now that it's been added to the database and it
        // 		   should have a database ID.
        $dbdata['question_real_id'] = false;
        if ($dbdata['question_hash']) {
        	 $dbdata['question_real_id'] = WPCW_arrays_getValue($this->questionData, $dbdata['question_hash']);
        }
        
        return $dbdata;
	}
	
	
	/**
	 * Extract details from the XML for this single question.
	 * @param String $singleQuestion The details of the question to extract.
	 */
	private function loadQuestionData_fullData($singleQuestion)
	{
        global $fieldsToProcess_quiz_questions;
        
        // ### 1 - Load main fields
        $dbdata = array();
        foreach ($fieldsToProcess_quiz_questions as $fieldName)
        {
        	// Put data into database, but assume data is blank if not set in XML.
        	$dbdata[$fieldName] = (isset($singleQuestion->$fieldName) ? (string)$singleQuestion->$fieldName : '');
        	
        	// Use html_entity_decode to remove HTML entities.
        	$dbdata[$fieldName] = html_entity_decode($dbdata[$fieldName], ENT_QUOTES);
        }
        
        
		// ### 2 - Load question_data_answers
		$dbdata['question_data_answers'] = array();
		if (isset($singleQuestion->question_data_answers) && !empty($singleQuestion->question_data_answers))
		{	
			// Need to extract the values from the question
			// SimpleXMLElement Object (
			// [possible_answer_1] => SimpleXMLElement Object
			// [possible_answer_2] => SimpleXMLElement Object
			// )
			
			// It's assumed that the possible_answer numbers are in numerical order starting at 1.
			// This is reasonable if we did the export.
			$i = 1;
			
			// Need to create variable for use in the loop
			$possibleAnswerField = 'possible_answer_' . $i;
			
			while (isset($singleQuestion->question_data_answers->$possibleAnswerField))
			{
				// Copy answer accross. (<possible_answer_1><![CDATA[UGlnZ3k=]]></possible_answer_1>)
				$dbdata['question_data_answers'][$i] = array('answer' => (string)$singleQuestion->question_data_answers->$possibleAnswerField);
				
				// Is there an image for this particular answer too?
				// e.g. <possible_answer_1_image>URL goes here...</possible_answer_1_image>
				$possibleAnswerField_image = $possibleAnswerField . '_image';
				if (isset($singleQuestion->question_data_answers->$possibleAnswerField_image))
				{
					$dbdata['question_data_answers'][$i]['image'] = (string)$singleQuestion->question_data_answers->$possibleAnswerField_image;
				}
								
				// Try next entry
				$i++;
				$possibleAnswerField = 'possible_answer_' . $i;
			}
		}
		
		// ### 3 - Load tags
		// Check for units in this module
        if (isset($singleQuestion->tags) && isset($singleQuestion->tags->tag[0]))
        {
        	$dbdata['tags'] = array();
        	foreach ($singleQuestion->tags->tag as $singleTag)  	
        	{
        		// Here, we're just converting the tag name to a string
        		$tagName = (string)$singleTag;
        	
	        	// Use html_entity_decode to remove HTML entities.
	        	$tagName = html_entity_decode($tagName, ENT_QUOTES);
        		
        		// Add to list of clean tags
        		$dbdata['tags'][] = $tagName;
        	}
        }
        
        return $dbdata;
	}
	

	
	/**
	 * Try to add this specific question to the database (to the pool).
	 * @param Array $singleQuestionData Specific question data to add.
	 * @return Integer The ID of the newly inserted question ID, or false if it wasn't added.
	 */
	private function loadQuestionData_addQuestionToDatabase($singleQuestionData) 
	{
		if (!$singleQuestionData || empty($singleQuestionData))
			return false;
			
		global $wpdb, $wpcwdb;
		$wpdb->show_errors();
	
		// ### 1 - Initialise data to be added for question. 
		$questionDetailsToAdd = $singleQuestionData;
			
		// ### 2 - Need to strip out question order, as there is no question_order field
		// in the database, so we need to use it in the mappings data.
		$question_order =  WPCW_arrays_getValue($singleQuestionData, 'question_order');
		unset($questionDetailsToAdd['question_order']);
			
			
		// ### 3 - Handle the insert of the special arrays that need serialising. We know these exist
		// as we have specifically validated them.
		$questionDetailsToAdd['question_data_answers'] = false;		
		if (!empty($singleQuestionData['question_data_answers']))
		{
			$questionDetailsToAdd['question_data_answers'] = serialize($singleQuestionData['question_data_answers']);	
		} 
		
		// ### 4 - Extract tags from the question data so that we can insert the question into
		//		   the database without any errors.
		$tagList = array();
		if (isset($questionDetailsToAdd['tags'])) {
			$tagList = $questionDetailsToAdd['tags'];
		}
		unset($questionDetailsToAdd['tags']);
		
									
		// ### 5 - Insert details of this particular question into the database.
		$queryResult = $wpdb->query(arrayToSQLInsert($wpcwdb->quiz_qs, $questionDetailsToAdd));		
			
		// ### 6 - Store the ID of the newly inserted ID, we'll need it for associating tags.
		$currentQuestionID = $wpdb->insert_id;
				
		// ### 7 - Check query succeeded.
		if ($queryResult === FALSE) {
			$this->errorList[] = __('There was a problem adding the question into the database.', 'wp_courseware');
			return false;
		}
		
		// ### 8 - Now we create the tag associations.
		WPCW_questions_tags_addTags($currentQuestionID, $tagList);
			
		return $currentQuestionID;
	}
	
}