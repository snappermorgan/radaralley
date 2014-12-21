<?php


/**
 * Generates the tab header for the page.
 * 
 * @param Array $tabList The list of tabs to create (name => label)
 * @param String $tabID The CSS ID of the tab wrapper for the page.
 * @param String $currentTab The current tab to show (using name)
 * 
 * @return String The rendered tab header for the page. A closing </div> is needed to complete this wrapper.
 */
function WPCW_tabs_generateTabHeader($tabList, $tabID, $currentTab = false)
{
	$html = false;
	
	// Generate the tabs		
    $html .= sprintf('<div class="wpcw_tab_wrapper" id="%s"><div class="wpcw_tab_wrapper_tabs">', $tabID);
    
    // Select the first tab if no tab has been selected
    if (!$currentTab) {
    	$currentTab = current(array_keys($tabList)); 
    }
    
    // Now render each of the tabs on the page.
    foreach ($tabList as $tabName => $tabDetails)
    {
    	// Work out the CSS class if selected
        $class = ($tabName == $currentTab) ? ' wpcw_tab_active' : '';
        
        // Any extra classes
        if (isset($tabDetails['cssclass'])) {
        	$class .= ' ' . $tabDetails['cssclass'];
        }
        
        $html .= sprintf('<a class="wpcw_tab%s" href="#" data-tab="%s" id="wpcw_tab_%s">%s</a>', $class, $tabName, $tabName, $tabDetails['label']);
    }
    $html .= '</div>'; // .wpcw_tab_wrapper_tabs
    
    return $html;
}


/**
 * Safe method to find a subitem on the menu and remove it.
 * @param $submenuName The name of the submenu to search.
 * @param $menuItemID The id of the menu item to be removed.
 */
function WPCW_menu_removeSubmenuItem($submenuName, $menuItemID)
{
	global $submenu;	

	// Not found
	if (!isset($submenu[$submenuName])) {
		return false;
	}

	// Search each item of the submenu
	foreach ($submenu[$submenuName] as $index => $details)
	{
		// Found a matching subitem title
		if ($details[2] == $menuItemID) {
			unset($submenu[$submenuName][$index]);			
			
			// No need to continue searching
			return;
		}
	}
}


/**
 * Shows the question pool page.
 */
function WPCW_showPage_QuestionPool()
{
	require_once  WPCW_plugin_getPluginDirPath() . 'pages/page_question_pool.inc.php';
	WPCW_showPage_QuestionPool_load();
}

/**
 * Shows the documentation page for the plugin. 
 */
function WPCW_showPage_Documentation()
{
	require_once  WPCW_plugin_getPluginDirPath() . 'pages/page_documentation.inc.php';
	WPCW_showPage_Documentation_load();
}

/**
 * Shows the quiz summary page. 
 */
function WPCW_showPage_QuizSummary()
{
	require_once  WPCW_plugin_getPluginDirPath() . 'pages/page_quiz_summary.inc.php';
	WPCW_showPage_QuizSummary_load();
}

/**
 * Function that allows a quiz to be created or edited.
 */
function WPCW_showPage_ModifyQuiz() 
{
	require_once  WPCW_plugin_getPluginDirPath() . 'pages/page_quiz_modify.inc.php';
	WPCW_showPage_ModifyQuiz_load();
}

/**
 * Function that allows a quiz to be created or edited.
 */
function WPCW_showPage_ModifyQuestion() 
{
	require_once WPCW_plugin_getPluginDirPath() . 'pages/page_question_modify.inc.php';
	WPCW_showPage_ModifyQuestion_load();
}


/**
 * Function that allows a module to be created or edited.
 */
function WPCW_showPage_ModifyModule() 
{
	require_once WPCW_plugin_getPluginDirPath() . 'pages/page_module_modify.inc.php';
	WPCW_showPage_ModifyModule_load();
}


/**
 * Shows the page to do with importing/exporting training courses.
 */
function WPCW_showPage_ImportExport()
{
	require_once  WPCW_plugin_getPluginDirPath() . 'pages/page_import_export.inc.php';
	WPCW_showPage_ImportExport_load();
}

/**
 * Shows the documentation page for the plugin. 
 */
function WPCW_showPage_GradeBook()
{
	require_once  WPCW_plugin_getPluginDirPath() . 'pages/page_gradebook.inc.php';
	WPCW_showPage_GradeBook_load();
}

/**
 * Page where the modules of a course can be ordered.
 */
function WPCW_showPage_CourseOrdering()
{
	require_once  WPCW_plugin_getPluginDirPath() . 'pages/page_course_ordering.inc.php';
	WPCW_showPage_CourseOrdering_load();
}


/**
 * Function that show a summary of the training courses.
 */
function WPCW_showPage_Dashboard() 
{
	require_once  WPCW_plugin_getPluginDirPath() . 'pages/page_course_dashboard.inc.php';
	WPCW_showPage_Dashboard_load();
}


/**
 * Function that allows a course to be created or edited.
 */
function WPCW_showPage_ModifyCourse() 
{
	require_once  WPCW_plugin_getPluginDirPath() . 'pages/page_course_modify.inc.php';
	WPCW_showPage_ModifyCourse_load();
}


/**
 * Shows the settings page for the plugin.
 */
function WPCW_showPage_Settings()
{
	require_once  WPCW_plugin_getPluginDirPath() . 'pages/page_settings.inc.php';
	WPCW_showPage_Settings_load();
}

/**
 * Shows the settings page for the plugin.
 */
function WPCW_showPage_Settings_Network()
{
	require_once  WPCW_plugin_getPluginDirPath() . 'pages/page_settings.inc.php';
	WPCW_showPage_Settings_Network_load();
}


/**
 * Show the page where the user can set up the certificate settings. 
 */
function WPCW_showPage_Certificates()
{
	require_once  WPCW_plugin_getPluginDirPath() . 'pages/page_settings_certificates.inc.php';
	WPCW_showPage_Certificates_load();
}


/**
 * Shows a detailed summary of the user progress.
 */
function WPCW_showPage_UserProgess()
{
	require_once  WPCW_plugin_getPluginDirPath() . 'pages/page_user_progress.inc.php';
	WPCW_showPage_UserProgess_load();
}

/**
 * Shows a detailed summary of the user's quiz or survey answers.
 */
function WPCW_showPage_UserProgess_quizAnswers()
{
	require_once  WPCW_plugin_getPluginDirPath() . 'pages/page_user_progress.inc.php';
	WPCW_showPage_UserProgess_quizAnswers_load();
}



/** 
 * Page where the site owner can choose which courses a user is allowed to access.
 */
function WPCW_showPage_UserCourseAccess()
{
	require_once  WPCW_plugin_getPluginDirPath() . 'pages/page_user_courseaccess.inc.php';
	WPCW_showPage_UserCourseAccess_load();
}


/**
 * Convert page/post to a course unit 
 */
function WPCW_showPage_ConvertPage()
{
	require_once  WPCW_plugin_getPluginDirPath() . 'pages/page_unit_convertpage.inc.php';
	WPCW_showPage_ConvertPage_load();
}



/**
 * Handle saving questions to the database.
 * 
 * @param Integer $quizID The quiz for which the questions apply to. 
 * @param Boolean $singleQuestionMode If true, then we're updating a single question, and we do things slightly differently.
 */
function WPCW_handler_questions_processSave($quizID, $singleQuestionMode = false)
{
	global $wpdb, $wpcwdb;
	$wpdb->show_errors();	
	
	$questionsToSave    = array();
	$questionsToSave_New = array();	
	
	// Check $_POST data for the 
	foreach ($_POST as $key => $value)
	{
		// #### 1 - Check if we're deleting a question from this quiz
		// We're not just deleting the question, just the association. This is because questions remain in the 
		// pool now.
		if (preg_match('/^delete_wpcw_quiz_details_([0-9]+)$/', $key, $matches))
		{
			// Remove mapping from the mapping table.
			$SQL = $wpdb->prepare("
				DELETE FROM $wpcwdb->quiz_qs_mapping
				WHERE question_id = %d
				  AND parent_quiz_id = %d
			", $matches[1], $quizID);
						
			$wpdb->query($SQL);
			
			// Update usage counts
			WPCW_questions_updateUsageCount($matches[1]);
						
			// Just a deletion - move on to next array item to save processing time.
			continue;
		}
		
		
		// #### 2 - See if we have a question to check for.
		if (preg_match('/^question_question_(new_question_)?([0-9]+)$/', $key, $matches))
		{
			// Got the ID of the question, now get answers and correct answer.
			$questionID = $matches[2];
			
			// Store the extra string if we're adding a new question.
			$newQuestionPrefix = $matches[1];
			 
			$fieldName_Answers 		= 'question_answer_' . 				$newQuestionPrefix . $questionID;
			$fieldName_Answers_Img	= 'question_answer_image_' . 		$newQuestionPrefix . $questionID;
			$fieldName_Correct 		= 'question_answer_sel_' . 			$newQuestionPrefix . $questionID;
			$fieldName_Type			= 'question_type_' . 				$newQuestionPrefix . $questionID;
			$fieldName_Order		= 'question_order_' . 				$newQuestionPrefix . $questionID;		
			$fieldName_AnswerType 	= 'question_answer_type_' . 		$newQuestionPrefix . $questionID;
			$fieldName_AnswerHint 	= 'question_answer_hint_' . 		$newQuestionPrefix . $questionID;
			$fieldName_Explanation 	= 'question_answer_explanation_' . 	$newQuestionPrefix . $questionID;
			$fieldName_Image 		= 'question_image_' . 				$newQuestionPrefix . $questionID;
			
			// For Multi-Choice - Answer randomization
			$fieldName_Multi_Random_Enable 		= 'question_multi_random_enable_' . $newQuestionPrefix . $questionID;
			$fieldName_Multi_Random_Count 		= 'question_multi_random_count_'  . $newQuestionPrefix . $questionID;
				
			// Order should be a number
			$questionOrder = 0;
			if (isset($_POST[$fieldName_Order])) {
				$questionOrder = $_POST[$fieldName_Order] + 0;
			} 
			
			// Default types
			$qAns 			= false;
			$qAnsCor 		= false;
			$qAnsType   	= false; // Just used for open question types. 
			$qAnsFileTypes 	= false; // Just used for upload file types.
			
			// Get the hint - Just used for open and upload types. Allow HTML.
			$qAnsHint = trim(WPCW_arrays_getValue($_POST, $fieldName_AnswerHint));
			
			// Get the explanation - All questions. Allow HTML.
			$qAnsExplain = trim(WPCW_arrays_getValue($_POST, $fieldName_Explanation));
			
			// The image URL to use. No HTML. Table record is 300 chars, hence cropping.
			$qQuesImage = trim(substr(strip_tags(WPCW_arrays_getValue($_POST, $fieldName_Image)), 0, 300));
			
			// How many questions are there is this selection? 1 by default for non-random questions.
			$expandedQuestionCount = 1;
			
			// For Multi-Choice - Answer randomization
			$qMultiRandomEnable = false;
			$qMultiRandomCount  = 5; 
			
			// What type of question do we have?
			$questionType = WPCW_arrays_getValue($_POST, $fieldName_Type);
			switch ($questionType)
			{
				case 'multi':
						$qAns = WPCW_quiz_MultipleChoice::editSave_extractAnswerList($fieldName_Answers, $fieldName_Answers_Img);
						$qAnsCor = WPCW_quiz_MultipleChoice::editSave_extractCorrectAnswer($qAns, $fieldName_Correct);
						
						// Provide the UI with at least once slot for an answer.
						if (!$qAns) {
							$qAns = array('1' => array('answer' => ''), '2' => array('answer' => ''));
						}
						
						// Check randomization values (boolean will be 'on' to enable, as it's a checkbox)
						$qMultiRandomEnable = 'on' == WPCW_arrays_getValue($_POST, $fieldName_Multi_Random_Enable);
						$qMultiRandomCount  = intval(WPCW_arrays_getValue($_POST, $fieldName_Multi_Random_Count));
					break;
					
				case 'open':					
						// See if there's a question type that's been sent back to the server.
						$answerTypes = WPCW_quiz_OpenEntry::getValidAnswerTypes();
						$thisAnswerType = WPCW_arrays_getValue($_POST, $fieldName_AnswerType);
						
						// Validate the answer type is in the list. Don't create a default so that user must choose.
						if (isset($answerTypes[$thisAnswerType])) {
							$qAnsType = $thisAnswerType;
						} 
												
						// There's no correct answer for an open question.
						$qAnsCor = false; 
					break;
					
				case 'upload':
						$fieldName_FileType 	= 'question_answer_file_types_' . 	$newQuestionPrefix . $questionID;
					
						// Check new file extension types, parsing them.
						$qAnsFileTypesRaw = WPCW_files_cleanFileExtensionList(WPCW_arrays_getValue($_POST, $fieldName_FileType));
						$qAnsFileTypes = implode(',', $qAnsFileTypesRaw);
					break;
					
				case 'truefalse':
						$qAnsCor = WPCW_quiz_TrueFalse::editSave_extractCorrectAnswer($fieldName_Correct);
					break;
					
				// Validate the the JSON data here... ensure all the tags are valid (not worried about the counts).
				// Then save back to database. 
				case 'random_selection':

						// Reset to zero for counting below.
						$expandedQuestionCount = 0;
						 
						$decodedTags = WPCW_quiz_RandomSelection::decodeTagSelection(stripslashes($value));

						// Capture just ID and count and resave back to database.
						$toSaveList = false;
						if (!empty($decodedTags))
						{
							$toSaveList = array();
							foreach ($decodedTags as $decodedKey => $decodedDetails)
							{
								$toSaveList[$decodedKey] = $decodedDetails['count'];
								
								// Track requested questions
								$expandedQuestionCount += $decodedDetails['count'];
							}
						}
						
						// Overwrite $value to use cleaned question
						$value = json_encode($toSaveList);
					break;
					
					
				// Not expecting anything here... so not handling the error case.
				default:					
					break;
			}
			
			// ### 4a - Encode the answer data		
			$encodedqAns = $qAns;	
			if (!empty($qAns))
			{
				foreach ($encodedqAns as $idx => $data) 
				{
					$encodedqAns[$idx]['answer'] = base64_encode($data['answer']);
				}				
			}
						
			// ### 4b - Save new question data as a list ready for saving to the database.			
			$quDataToSave = array(
					'question_answers' 				=> false, // Not needed, legacy column.
					'question_question' 			=> stripslashes($value),  	// Clean up each answer if slashes used for escape characters.
					'question_data_answers'			=> serialize($encodedqAns), // Answers need to be serialised.
					'question_correct_answer' 		=> $qAnsCor,
					'question_type'					=> $questionType,
					'question_order'				=> $questionOrder,
					'question_answer_type'			=> $qAnsType,
					'question_answer_hint'			=> stripslashes($qAnsHint),
					'question_answer_explanation'	=> stripslashes($qAnsExplain),
					'question_answer_file_types' 	=> $qAnsFileTypes,
					'question_image'				=> $qQuesImage,
					'question_expanded_count'		=> $expandedQuestionCount,
			
					// Multi only
					'question_multi_random_enable' => $qMultiRandomEnable,
					'question_multi_random_count'  => $qMultiRandomCount,

					// Default placeholder of tags to save - if any.
					'taglist'						=> array(),
				);
				
				
			// ### 5 - Check if there are any tags to save. Only happens for questions that
			// haven't been saved, so that we can save when we do a $_POST save.
			$tagFieldForNewQuestions = 'tags_to_add_' . $newQuestionPrefix . $questionID;
			if (isset($_POST[$tagFieldForNewQuestions]))
			{
				if (!empty($_POST[$tagFieldForNewQuestions]))
				{
					// Validate each tag ID we have, add to list to be stored for this question later.
					foreach ($_POST[$tagFieldForNewQuestions] as $idx => $tagText)
					{
						$tagText = trim(stripslashes($tagText));
						if ($tagText) {
							$quDataToSave['taglist'][] = $tagText;
						}
					}
				}
			}			
				
			
			// Not a new question - so not got question ID as yet
			if ($newQuestionPrefix) {
				$questionsToSave_New[] = $quDataToSave;
			}
			
			// Existing question - so keep question ID
			else 
			{
				$quDataToSave['question_id']	= $questionID;
				$questionsToSave[$questionID]   = $quDataToSave;
			}
			
		} // end if question found.
	}
	
	
	// Only need to adjust quiz settings when editing a quiz and not a single question.
	if (!$singleQuestionMode)
	{
		// #### 6 - Remove association of all questions for this quiz
		//          as we're going to re-add them.
		$wpdb->query($wpdb->prepare("
					DELETE FROM $wpcwdb->quiz_qs_mapping
					WHERE parent_quiz_id = %d
				", $quizID));
	}
		
	
	
	// #### 7 - Check we have existing questions to save
	if (count($questionsToSave))
	{
		// Now save all data back to the database.
		foreach ($questionsToSave as $questionID => $questionDetails)
		{		 
			// Extract the question order, as can't save order with question in DB
			$questionOrder = $questionDetails['question_order'];
			unset($questionDetails['question_order']);
			
			// Tag list only used for new questions, so remove this field
			unset($questionDetails['taglist']);
			
			// Save question details back to database.
			$wpdb->query(arrayToSQLUpdate($wpcwdb->quiz_qs, $questionDetails, 'question_id'));
			
			
			// No need to update counts/associations when editing a single lone question
			if (!$singleQuestionMode)
			{
				// Create the association for this quiz/question.
				$wpdb->query($wpdb->prepare("
					INSERT INTO $wpcwdb->quiz_qs_mapping 
					(question_id, parent_quiz_id, question_order)
					VALUES (%d, %d, %d)
				", $questionID, $quizID, $questionOrder));
				
				// Update usage count for question.
				WPCW_questions_updateUsageCount($questionID);
			}
		}
	}	
	
	// #### 8 - Save the new questions we have
	if (count($questionsToSave_New))
	{
		// Now save all data back to the database.
		foreach ($questionsToSave_New as $questionDetails)
		{		 
			// Extract the question order, as can't save order with question in DB
			$questionOrder = $questionDetails['question_order'];
			unset($questionDetails['question_order']);
			
			// Extract the tags added for this question - we'll save manually.
			$tagsToAddList = $questionDetails['taglist'];
			unset($questionDetails['taglist']);
			
			// Create question in database
			$wpdb->query(arrayToSQLInsert($wpcwdb->quiz_qs, $questionDetails));
			$newQuestionID = $wpdb->insert_id;
			
			// No need to update counts/associations when editing a single lone question
			if (!$singleQuestionMode)
			{			
				// Create the association for this quiz/question.
				$wpdb->query($wpdb->prepare("
					INSERT INTO $wpcwdb->quiz_qs_mapping 
					(question_id, parent_quiz_id, question_order)
					VALUES (%d, %d, %d)
				", $newQuestionID, $quizID, $questionOrder));
				
				// Update usage
				WPCW_questions_updateUsageCount($newQuestionID);
			}
			
			// Add associations for tags for this unsaved question now we finally have a question ID.
			if (!empty($tagsToAddList))
			{
				WPCW_questions_tags_addTags($newQuestionID, $tagsToAddList);
			}
		}
	}
}



/**
 * Show standard support information.
 * 
 * @param Object $page A reference to the page object showing information.
 */
function WPCW_docs_showSupportInfo($page)
{
	$page->openPane('wpcw-docs-support', __('Need help?', 'wp_courseware'));
		
	echo '<p>'.__("If you need assistance with WP Courseware, please visit the <a href='admin.php?page=WPCW_showPage_Documentation'>documentation section</a> first. If you would like to submit a support request, please login to the <a href='http://flyplugins.com/member-portal'>Member Portal</a> and click on the support tab.", 'wp_courseware').'</p>';	

	$page->closePane();
}

/**
 * Show information on being an affiliate.
 * 
 * @param Object $page A reference to the page object showing information.
 */
function WPCW_docs_showSupportInfo_Affiliate($page)
{
	$page->openPane('wpcw-docs-affiliate', __('Want to become an affiliate?', 'wp_courseware'));
	
	echo '<p>'.__("If you're interested in making money by promoting WP Courseware, please login to the <a href='http://flyplugins.com/member-portal'>Member Portal</a> and click on the affiliates tab.", 'wp_courseware').'</p>';	
	
	$page->closePane();
}

/**
 * Show the latest news.
 * 
 * @param Object $page A reference to the page object showing information.
 */
function WPCW_docs_showSupportInfo_News($page)
{
	$page->openPane('wpcw-docs-support-news', __('Latest news from FlyPlugins.com', 'wp_courseware'));
	
	$rss = fetch_feed('http://feeds.feedburner.com/FlyPlugins');

	// Got items, so show the news
	if (!is_wp_error($rss)) 
	{
		$rss_items = $rss->get_items(0, $rss->get_item_quantity(2));	
		
		$content = '<ul>';
		if ( !$rss_items ) {
		    $content .= '<li class="fly">'.__( 'No news items, feed might be broken...', 'wp_courseware' ).'</li>';
		} else {
		    foreach ( $rss_items as $item ) {
		    	$url = preg_replace( '/#.*/', '', esc_url( $item->get_permalink(), $protocolls=null, 'display' ) );
				$content .= '<li class="fly">';
				$content .= '<a class="rsswidget" href="'.$url.'">'. esc_html( $item->get_title() ) .'</a> ';
				$content .= '</li>';
		    }
		}		
		$content .= '</ul>';
	}

	$content .= '<ul class="wpcw_connect">';
	$content .= '<li class="facebook"><a href="http://facebook.com/flyplugins">'.		__( 'Like Fly Plugins on Facebook', 'wp_courseware' ).'</a></li>';
	$content .= '<li class="twitter"><a href="http://twitter.com/flyplugins">'.			__( 'Follow Fly Plugins on Twitter', 'wp_courseware' ).'</a></li>';
	$content .= '<li class="youtube"><a href="http://www.youtube.com/flyplugins">'.			__( 'Watch Fly Plugins on YouTube', 'wp_courseware' ).'</a></li>';
	//$content .= '<li class="googleplus"><a href="https://plus.google.com/115118636946624261241">'.	__( 'Circle Fly Plugins on Google+', 'wp_courseware' ).'</a></li>';
	//$content .= '<li class="rss"><a href="http://feeds.feedburner.com/FlyPlugins">'.	__( 'Subscribe with RSS', 'wp_courseware' ).'</a></li>';
	//$content .= '<li class="email"><a href="http://feedburner.google.com/fb/a/mailverify?uri=FlyPlugins&amp;loc=en_US">'.__( 'Subscribe by email', 'wp_courseware' ).'</a></li>';
	$content .= '</ul>';

	echo '<div class="wpcw_fly_support_news">'. $content .'</div>';	
	
	$page->closePane();
}











/**
 * Translation strings to use with each form.
 * @return Array The translated strings.
 */
function WPCW_forms_getTranslationStrings()
{
	return array(
			"Please fill in the required '%s' field." 	=> __("Please fill in the required '%s' field.", 'wp_courseware'),
			"There's a problem with value for '%s'." 	=> __("There's a problem with value for '%s'.", 'wp_courseware'),
			'required' 									=> __('required', 'wp_courseware')
	);
}


/**
 * Create a dropdown box using the list of values provided and select a value if $selected is specified.
 * @param $name String The name of the drop down box.
 * @param $values String  The values to use for the drop down box.
 * @param $selected String  If specified, the value of the drop down box to mark as selected.
 * @param $cssid String The CSS ID of the drop down list.
 * @param $cssclass String The CSS class for the drop down list.
 * @return String The HTML for the select box.
 */
function WPCW_forms_createDropdown($name, $values, $selected, $cssid = false, $cssclass = false)
{
	if (!$values) {
		return false;
	}
	
	$selectedhtml = 'selected="selected" ';
	
	// CSS Attributes
	$css_attrib = false;
	if ($cssid) {
		$css_attrib = "id=\"$cssid\" ";
	}
	if ($cssclass) {
		$css_attrib .= "class=\"$cssclass\" ";
	}
	
	$html = sprintf('<select name="%s" %s>', $name, $css_attrib);	
	
	foreach ($values as $key => $details)
	{
		// Handle value => array('label' => '', 'data' => '', 'data2' => '')
		if (is_array($details))
		{
			// This adds extra HTML5 data.
			$html .= sprintf('<option value="%s" data-content="%s" data-content-two="%s" %s>%s&nbsp;&nbsp;</option>', 
				$key, $details['data'], $details['data2'], ($key == $selected ? $selectedhtml : ''), $details['label']
			);
		}
		
		// Handle value => data
		else {
			$html .= sprintf('<option value="%s" %s>%s&nbsp;&nbsp;</option>', $key, ($key == $selected ? $selectedhtml : ''), $details);
		}
	}
		
	return $html . '</select>';
}





/**
 * Create a break bar for the forms as a tab, with a save button too.
 * 
 * @return String The HTML for the section break.
 */
function WPCW_forms_createBreakHTML_tab() 
{
	$html = false;	
	$html .= '<div class="wpcw_form_break_tab"></div>';
	return $html;
}


/**
 * Create a break bar for the forms, with a save button too.
 * @param String $title The title for the section.
 * @param String $buttonText The text for the button on the break section.
 * @param String $extraCSSClass Any extra CSS for styling the break.
 * 
 * @return String The HTML for the section break.
 */
function WPCW_forms_createBreakHTML($title, $buttonText = false, $hideButton = false, $extraCSSClass = false) 
{
	if (!$hideButton) {
		$buttonText = __('Save ALL Settings', 'wp_courseware');
	}
	
	$btnHTML = false;
	if ($buttonText && !$hideButton) {
		$btnHTML = sprintf('<input type="submit" value="%s" name="Submit" class="button-primary">', $buttonText);
	}
	
	return sprintf('
		<div class="wpcw_form_break %s">			
			%s
			<h3>%s</h3>
			<div class="wpcw_cleared">&nbsp;</div>
		</div>
	', 
	$extraCSSClass, 
	$btnHTML, 
	$title);
}


/**
 * Function to get the details of a question (to ensure it exists).
 * 
 * @param Integer $questionID The ID of the question to get the details for.
 * @param Boolean $getTagsToo If true, then get the list of these tags too. 
 * 
 * @return Object The details of the question as an object.
 */
function WPCW_questions_getQuestionDetails($questionID, $getTagsToo = false)
{
	if (!$questionID) {
		return false;
	}	
	
	global $wpcwdb, $wpdb;
	$wpdb->show_errors();
	
	$SQL = $wpdb->prepare("SELECT * 
			FROM $wpcwdb->quiz_qs	
			WHERE question_id = %d 
			", $questionID);
	
	$obj = $wpdb->get_row($SQL);
	
	// Also grab any tags that this question has.
	if ($getTagsToo) 
	{
		$obj->tags = $wpdb->get_results($wpdb->prepare("
			SELECT qt.*
			FROM $wpcwdb->question_tag_mapping qtm
				LEFT JOIN $wpcwdb->question_tags qt ON qtm.tag_id = qt.question_tag_id
			WHERE question_id = %d
			ORDER BY question_tag_name ASC
		", $obj->question_id));
	}
	
	return $obj; 
}



/**
 * Update a question to track how many quizzes are using this question.
 * @param Integer $questionID The ID of the question to update with the count.
 * @return Integer The number of quizzes using a question. 
 */
function WPCW_questions_updateUsageCount($questionID)
{
	if (!intval($questionID)) {
		return;
	}
	
	global $wpdb, $wpcwdb;
	$wpdb->show_errors();
	
	// Update tag popularity
	$usageCountForQuestion = $wpdb->get_var($wpdb->prepare("
		SELECT COUNT(*) 
		 FROM $wpcwdb->quiz_qs_mapping
		WHERE question_id = %d
	", $questionID));
	
	// Update the count in the tag field
	$wpdb->query($wpdb->prepare("
		UPDATE $wpcwdb->quiz_qs 
		SET question_usage_count = %d
		WHERE question_id = %d
	", $usageCountForQuestion, $questionID));
	
	return $usageCountForQuestion;
}


/**
 * Update the popularity stats for a tag.
 * @param Integer $tagID The ID of the tag to update the popularity for.
 */
function WPCW_questions_tags_updatePopularity($tagID)
{		
	if (!intval($tagID)) {
		return;
	}
	
	global $wpdb, $wpcwdb;
	$wpdb->show_errors();
	
	// Update tag popularity
	$usageCountForTag = $wpdb->get_var($wpdb->prepare("
		SELECT COUNT(*) 
		  FROM $wpcwdb->question_tag_mapping
		WHERE tag_id = %d
	", $tagID));
	
	// Update the count in the tag field
	$wpdb->query($wpdb->prepare("
		UPDATE $wpcwdb->question_tags 
		SET question_tag_usage = %d
		WHERE question_tag_id = %d
	", $usageCountForTag, $tagID));
	
	
}





/**
 * Given the tag and question IDs, remove the association.
 * 
 * @param Integer $questionID The ID of the question to remove the tag from.
 * @param Integer $tagID The ID of the tag to remove from the questio
 */
function WPCW_questions_tags_removeTag($questionID, $tagID)
{
	global $wpdb, $wpcwdb;
	$wpdb->show_errors();
	
	// Check that the tag exists first...
	$tagFound = $wpdb->get_row($wpdb->prepare("
			SELECT *
			  FROM $wpcwdb->question_tag_mapping 
			WHERE question_id = %d
			  AND tag_id = %d
		", $questionID, $tagID));
	
	if ($tagFound)
	{
		// Only remove if found...
		$wpdb->query($wpdb->prepare("
			DELETE FROM $wpcwdb->question_tag_mapping 
			WHERE question_id = %d
			  AND tag_id = %d
		", $questionID, $tagID));
		
		// Update tag usage count.
		WPCW_questions_tags_updatePopularity($tagID);
	}
}


/**
 * Given a list of tags, try to add them without adding them to a specific question.
 * 
 * @param Array $tagList The list of tags to add.
 * @return Array The list of tags to be rendered again.
 */
function WPCW_questions_tags_addTags_withoutQuestion($tagList)
{
	global $wpdb, $wpcwdb;
	$wpdb->show_errors();
	
	$taglistToReturn = array();
	
	// Just get the IDs of tags (and add the missing ones)
	foreach ($tagList as $tagToAdd)
	{
		$tagDetails = $wpdb->get_row($wpdb->prepare("
			SELECT question_tag_id
			  FROM $wpcwdb->question_tags 
			WHERE question_tag_name = %s
		", $tagToAdd));
		
		// Got a tag already, so need the tag ID
		if ($tagDetails)
		{
			// Add to list that we're turning to AJAX.
			$taglistToReturn[$tagDetails->question_tag_id] = $tagToAdd;
		}
		
		// We need to insert the tag to the tag table.
		else
		{
			$wpdb->query($wpdb->prepare("
				INSERT INTO $wpcwdb->question_tags
				(question_tag_name, question_tag_usage) VALUES (%s, 1)  
				", $tagToAdd));
			
			$taglistToReturn[$wpdb->insert_id] = $tagToAdd;
		}
	}
	
	return $taglistToReturn;
}


/**
 * Given a list of tags, try to add them to the specified question.
 * 
 * @param Integer $questionID The ID of the question that we're adding the tag for.
 * @param Array $tagList The list of tags to add.
 * @return Array The list of tags to be rendered again.
 */
function WPCW_questions_tags_addTags($questionID, $tagList)
{
	if (empty($tagList)) {
		return;
	}	
	
	global $wpdb, $wpcwdb;
	$wpdb->show_errors(); 
	
	$taglistToReturn = WPCW_questions_tags_addTags_withoutQuestion($tagList);
	
	// Now we need to work through and associate each tag with the question
	foreach ($taglistToReturn as $tagID => $tagText)
	{
		// Create association with a question
		$wpdb->query($wpdb->prepare("
			INSERT IGNORE INTO $wpcwdb->question_tag_mapping 
			(question_id, tag_id)
			VALUES (%d, %d)
		", $questionID, $tagID));
		
		WPCW_questions_tags_updatePopularity($tagID);
	}
		
	return $taglistToReturn;
}



/**
 * Given a list of tags, render them for admin control.
 * 
 * @param Integer $questionID The ID of the question that we're adding/removing the tag for.
 * @param Array $tagList The list of tags to add (a list of tag objects.
 * 
 * @return String The HTML for rendering the tags.
 */
function WPCW_questions_tags_render($questionID, $tagList)
{
	$html = '<span class="wpcw_tag_list_wrapper tagchecklist">';
	
	// Nothing to do, but still return wrapper for adding via AJAX.
	if (empty($tagList)) {
		$html .= '</span>';
		return $html;
	}
	
	// Render list of tags
	foreach ($tagList as $tagDetails)
	{
		$html .= sprintf('<span><a data-questionid="%d" data-tagid="%d" class="ntdelbutton">X</a>&nbsp;%s</span>', 
			$questionID, $tagDetails->question_tag_id, stripslashes($tagDetails->question_tag_name)
		);
	}
	
	$html .= '</span>';
	return $html;
}



/**
 * Shows a list of tags and a button to filter the question pool by tag.
 * 
 * @param String $currentTag The current tag that has been selected.
 * @param String $pageForURL The name of the page to show this form on (where page=WPCW_showPage_QuestionPool)
 * 
 * @return String The HTML to render the tag filtering code.
 */
function WPCW_questions_tags_createTagFilter($currentTag, $pageForURL) 
{
	$html = sprintf('<div class="wpcw_questions_tag_filter_wrap"><form method="get" action="%s">', admin_url('admin.php?page=' . $pageForURL));
	
		// Page that this form is being shown on.
		$html .= sprintf('<input type="hidden" name="page" value="%s" />', $pageForURL);
	
		// Select
		$html .= sprintf('<label for="wpcw_questions_tag_filter">%s</label>', __('Filter By:', 'wp_courseware'));
		$html .= WPCW_questions_tags_getTagDropdown(__('-- View All Tags --', 'wp_courseware'), 'filter', $currentTag, 'wpcw_questions_tag_filter');
		
		// CTA
		$html .= sprintf('<input type="submit" class="button-secondary" value="%s" />', __('Filter', 'wp_courseware'));		
	return $html . '</form></div>';
}



/**
 * Create a dropdown tag list.
 * 
 * @param Boolean $showBlank If true, add a blank to the start.
 * @param String $fieldName The name of the HTML field.
 * @param String $currentTag If specified, the current tag to mark as selected.
 * @param String $cssClassName The CSS name for the field.
 * @param Boolean $showQuestionStr If true, show the string 'questions' after each tag.
 * @param Boolean $showCountOfQuestions If true, then show the count of questions per tag. 
 * 
 * @return String The HTML for rendering the dropdown.
 */
function WPCW_questions_tags_getTagDropdown($showBlank = false, $fieldName, $currentTag, $cssClassName, $showQuestionStr = false, $showCountOfQuestions = true) 
{
	global $wpcwdb, $wpdb;
	$wpdb->show_errors();
	
	$tagsToShow = array();
	
	// Save the SQL query, used the cached variable if available.
	static $tagsToShow_cached;
	
	
	if (!$tagsToShow_cached)
	{	
		$tagList = $wpdb->get_results("
			SELECT * 
			  FROM $wpcwdb->question_tags
			 WHERE question_tag_usage > 0
			ORDER BY question_tag_name ASC
		");	
	
		if (!empty($tagList))
		{
			foreach ($tagList as $singleTag)
			{
				// Create downdown with tag selection and number of questions that exist for that tag).
				// The HTML5 data tag is useful for setting the max on spinners.
				$tagsToShow[$singleTag->question_tag_id] = 	
					array(
						'label' => $singleTag->question_tag_name,
						'data'	=> $singleTag->question_tag_usage,
						'data2'	=> $singleTag->question_tag_name
					);
					
				// Add count if requested.
				if ($showCountOfQuestions)
				{
					$tagsToShow[$singleTag->question_tag_id]['label'] .= ' (' . $singleTag->question_tag_usage . ($showQuestionStr ? ' ' . __('Questions', 'wp_courseware') : '') . ')';
				}
			}
		}
		
		$tagsToShow_cached = $tagsToShow;
	}
	
	// Copy out of the cache to use it.
	else {
		$tagsToShow = $tagsToShow_cached;
	}
	
	// Create the blank item to use, added to the front, but not cached.
	if ($showBlank) {
		$tagsToShow = array('' => $showBlank) + $tagsToShow;
	}
	
	// Save to static variable to save execution again in same page load.
	return WPCW_forms_createDropdown($fieldName, $tagsToShow, $currentTag, false, $cssClassName);
}




/**
 * Function to show a list of questions in the question pool for use by a standard page or the AJAX thickbox.
 * 
 * @param Integer $itemsPerPage The number of items to show on each table page.
 * @param Array $paramSrc The array of parameters to use for filtering/searching the question pool.
 * @param String $actionMode The type of mode we're in (ajax or std). 
 * @param PageBuilder $page The current page object (optional).
 */
function WPCW_questionPool_showPoolTable($itemsPerPage, $paramSrc, $actionMode = 'std', $page = false)
{
	global $wpcwdb, $wpdb;
	$wpdb->show_errors();
	
	// AJAX loader
	if ('ajax' == $actionMode) {
		printf('<img src="%simg/ajax_loader.gif" class="wpcw_loader" style="display: none;" />', WPCW_plugin_getPluginPath());
	}
	
	// Check to see if we've got questions to process
	if ('std' == $actionMode)
	{
		WPCW_showPage_QuestionPool_processActionForm($page);
	}
	
	$paging_pageWanted = WPCW_arrays_getValue($paramSrc, 'pagenum') + 0;
	if ($paging_pageWanted == 0) {
		$paging_pageWanted = 1;
	}
	
	// Handle the sorting and filtering
	$orderBy = WPCW_arrays_getValue($paramSrc, 'orderby');
	$ordering  = WPCW_arrays_getValue($paramSrc, 'order');
	
	// Validate ordering
	switch ($orderBy)
	{
		case 'question_question':
		case 'question_type':
			break;

		// Default and question_id
		//case 'question_id':
		default:
				$orderBy = 'qs.question_id';
 			break;
	}
	
	// Create opposite ordering for reversing it.
	$ordering_opposite = false;
	switch ($ordering)
	{
		case 'desc':
				$ordering_opposite = 'asc';
			break;
			
		case 'asc':
				$ordering_opposite = 'desc';
			break;
		
		default:
				$ordering = 'desc';
				$ordering_opposite = 'asc';
 			break;
	}
	

	// Was a search string specified? Or a specific item?
	$searchString = WPCW_arrays_getValue($paramSrc, 's');
	
	// Create WHERE string based search - Title or Description of Quiz
	$SQL_WHERE = false;
	if ($searchString) {
		$SQL_WHERE = $wpdb->prepare(" AND question_question LIKE %s", '%' . $searchString . '%');		
	}
	
	$summaryPageURL = admin_url('admin.php?page=WPCW_showPage_QuestionPool');
	
	// Show the form for searching						
	?>			
	<form id="wpcw_questions_search_box" method="get" action="<?php echo $summaryPageURL; ?>">
	<p class="search-box">
		<label class="screen-reader-text" for="wpcw_questions_search_input"><?php _e('Search Questions', 'wp_courseware'); ?></label>
		<input id="wpcw_questions_search_input" type="text" value="<?php echo $searchString ?>" name="s"/>
		<input class="button" type="submit" value="<?php _e('Search Questions', 'wp_courseware'); ?>"/>
		
		<input type="hidden" name="page" value="WPCW_showPage_QuestionPool" />
	</p>
	</form>
	<?php 	
	
	
	
	$SQL_TAG_FILTER = false;
	$tagFilter = intval(WPCW_arrays_getValue($paramSrc, 'filter', false));
	
	// See if we have any tag filtering to do.
	if ($tagFilter > 0)
	{
		// Ensure we add the tag mapping table to the query
		$SQL_TAG_FILTER = "
			LEFT JOIN $wpcwdb->question_tag_mapping qtm ON qtm.question_id = qs.question_id	
		";
		
		$SQL_WHERE .= $wpdb->prepare("
			AND qtm.tag_id = %d
			AND qs.question_question IS NOT NULL  
		", $tagFilter);
		
		//
	}
		
	$SQL_PAGING = "
			SELECT COUNT(*) as question_count 
			FROM $wpcwdb->quiz_qs qs
			$SQL_TAG_FILTER
			WHERE question_type <> 'random_selection'
			$SQL_WHERE 
		";
	
	$paging_resultsPerPage  = $itemsPerPage;
	$paging_totalCount		= $wpdb->get_var($SQL_PAGING);
	$paging_recordStart 	= (($paging_pageWanted-1) * $paging_resultsPerPage) + 1;
	$paging_recordEnd 		= ($paging_pageWanted * $paging_resultsPerPage);
	$paging_pageCount 		= ceil($paging_totalCount/$paging_resultsPerPage);	
	$paging_sqlStart		= $paging_recordStart - 1;

	// Show search message - that a search has been tried.
	if ($searchString) 
	{
		printf('<div class="wpcw_search_count">%s "%s" (%s %s) (<a href="%s">%s</a>)</div>',
			__('Search results for', 'wp_courseware'), 
			htmlentities($searchString), 
			$paging_totalCount,
			_n('result', 'results', $paging_totalCount, 'wp_courseware'),  
			$summaryPageURL,
			__('reset', 'wp_courseware')
		);
	}	
		
	// Do main query
	$SQL = "SELECT * 
			FROM $wpcwdb->quiz_qs qs			
			$SQL_TAG_FILTER			
			WHERE question_type <> 'random_selection'
			$SQL_WHERE
			ORDER BY $orderBy $ordering
			LIMIT $paging_sqlStart, $paging_resultsPerPage			 
			"; // These are already checked, so they are safe, hence no prepare()
			
	// Generate paging code
	$baseURL = WPCW_urls_getURLWithParams($summaryPageURL, 'pagenum')."&pagenum=";	
	
	
	$questions = $wpdb->get_results($SQL);
		
	$tbl = new TableBuilder();
	$tbl->attributes = array(
		'id' 	=> 'wpcw_tbl_question_pool',
		'class'	=> 'widefat wpcw_tbl'
	);
	
	// Checkbox Col
	//$tblCol = new TableColumn(false, 'question_selection');
	//$tblCol->cellClass = "question_selection wpcw_center";
	//$tbl->addColumn($tblCol);
	
	// Wanting sorting links... in standard mode
	if ('std' == $actionMode)
	{
		// Checkbox field (no name, as we'll use jQuery to do a check all)
		$tblCol = new TableColumn('<input type="checkbox" />', 'question_id_cb');
		$tblCol->cellClass = "wpcw_center wpcw_select_cb";
		$tblCol->headerClass = "wpcw_center wpcw_select_cb";
		$tbl->addColumn($tblCol);
		
		// ID - sortable
		$sortableLink = sprintf('<a href="%s&order=%s&orderby=question_id"><span>%s</span><span class="sorting-indicator"></span></a>', 
			$baseURL, 
			('question_id' == $orderBy ? $ordering_opposite : 'asc'),
			__('ID', 'wp_courseware')
		);
		
		// ID - render
		$tblCol = new TableColumn($sortableLink, 'question_id');
		$tblCol->headerClass = ('question_id' == $orderBy ? 'sorted '.$ordering : 'sortable');
		$tblCol->cellClass = "question_id";
		$tbl->addColumn($tblCol);
	
		// Question - sortable
		$sortableLink = sprintf('<a href="%s&order=%s&orderby=question_question"><span>%s</span><span class="sorting-indicator"></span></a>', 
			$baseURL, 
			('question_question' == $orderBy ? $ordering_opposite : 'asc'),
			__('Question', 'wp_courseware')
		);
			
		// Question - render
		$tblCol = new TableColumn($sortableLink, 'question_question');
		$tblCol->headerClass = ('question_question' == $orderBy ? 'sorted '.$ordering : 'sortable');
		$tblCol->cellClass = "question_question";
		$tbl->addColumn($tblCol);
		
		// Question Type - sortable
		$sortableLink = sprintf('<a href="%s&order=%s&orderby=question_type"><span>%s</span><span class="sorting-indicator"></span></a>', 
			$baseURL, 
			('question_type' == $orderBy ? $ordering_opposite : 'asc'),
			__('Question Type', 'wp_courseware')
		);
		
		// Question Type - render
		$tblCol = new TableColumn($sortableLink, 'question_type');
		$tblCol->headerClass = ('question_type' == $orderBy ? 'sorted '.$ordering : 'sortable') . ' wpcw_center';
		$tblCol->cellClass = "question_type";
		$tbl->addColumn($tblCol);
	
	}
	
	// No sorting links...
	else 
	{
		$tblCol = new TableColumn(__('ID', 'wp_courseware'), 'question_id');
		$tblCol->cellClass = "question_id";
		$tbl->addColumn($tblCol);
		
		$tblCol = new TableColumn(__('Question', 'wp_courseware'), 'question_question');
		$tblCol->cellClass = "question_question";
		$tbl->addColumn($tblCol);
		
		$tblCol = new TableColumn(__('Question Type', 'wp_courseware'), 'question_type');
		$tblCol->cellClass = "question_type";
		$tbl->addColumn($tblCol);
	}
		
		
	
	
	$tblCol = new TableColumn(__('Associated Quizzes', 'wp_courseware'), 'associated_quizzes');
	$tblCol->headerClass = "wpcw_center";
	$tblCol->cellClass = "associated_quizzes wpcw_center";
	$tbl->addColumn($tblCol);
	
	$tblCol = new TableColumn(__('Tags', 'wp_courseware'), 'question_tags');	
	$tblCol->cellClass = "question_tags wpcw_center";
	$tbl->addColumn($tblCol);

	// Actions
	$tblCol = new TableColumn(__('Actions', 'wp_courseware'), 'actions');
	$tblCol->cellClass = "actions actions_right";
	$tblCol->headerClass = "actions_right";
	$tbl->addColumn($tblCol);
	
	// Stores course details in a mini cache to save lots of MySQL lookups.
	$miniCourseDetailCache = array();
	
	// Format row data and show it.
	if ($questions)  
	{
		$odd = false;
		foreach ($questions as $singleQuestion)
		{
			$data = array();
			
			// URLs
			$editURL   			= admin_url('admin.php?page=WPCW_showPage_ModifyQuestion&question_id=' . $singleQuestion->question_id);
			
			// Maintain paging where possible.
			$deleteURL 			= $baseURL . '&action=delete&question_id=' . $singleQuestion->question_id;			
						
			// Basic Details
			$data['question_id']  		= $singleQuestion->question_id;								
			$data['question_type']  	= WPCW_quizzes_getQuestionTypeName($singleQuestion->question_type);
			$data['question_id_cb']  	= sprintf('<input type="checkbox" name="question_%d" />', $singleQuestion->question_id);
			
						
			// Association Count
			$data['associated_quizzes']  = $singleQuestion->question_usage_count;			
			
			
			// Actions - Std mode
			if ('std' == $actionMode)
			{
				// Edit by clicking
				$data['question_question']  = sprintf('<a href="%s">%s</a>', $editURL, $singleQuestion->question_question);
				
				$data['actions']	= '<ul class="wpcw_action_link_list">';
				
					$data['actions']	.= sprintf('<li><a href="%s" class="button-primary">%s</a></li>', 	$editURL, 	__('Edit', 'wp_courseware'));				
					$data['actions']	.= sprintf('<li><a href="%s" class="button-secondary wpcw_action_link_delete_question wpcw_action_link_delete" rel="%s">%s</a></li>', 	
							$deleteURL,
							__('Are you sure you wish to delete this question? This cannot be undone.', 'wp_courseware'), 	
							__('Delete', 'wp_courseware'));
												
				$data['actions']	.= '</ul>';
			}
			
			// Actions - AJAX mode
			else if ('ajax' == $actionMode)
			{
				// No Edit by clicking
				$data['question_question']  = $singleQuestion->question_question . sprintf('<span class="wpcw_action_status wpcw_action_status_added">%s</span>', __('Added', 'wp_courseware'));
				
				$data['actions']	= '<ul class="wpcw_action_link_list">';				
					$data['actions']	.= sprintf('<li><a href="#" class="button-primary wpcw_tb_action_add" data-questionnum="%d">%s</a></li>',														
														$singleQuestion->question_id, 
														__('Add To Quiz', 'wp_courseware')
													);				
				$data['actions']	.= '</ul>';
			}
			
			// Tags
			$data['question_tags'] = sprintf('<span class="wpcw_quiz_details_question_tags" data-questionid="%d" id="wpcw_quiz_details_question_tags_%d">',  
				$singleQuestion->question_id,
				$singleQuestion->question_id
			);
			$data['question_tags'] .= WPCW_questions_tags_render($singleQuestion->question_id, WPCW_questions_tags_getTagsForQuestion($singleQuestion->question_id));
			$data['question_tags'] .= '</span>';
			
			// Odd/Even row colouring.
			$odd = !$odd;
			$tbl->addRow($data, ($odd ? 'alternate' : ''));	
		}
	}
	
	else {
		// No questions - show error in table.
		$tbl->addRowObj(new RowDataSimple('wpcw_center wpcw_none_found', __('There are currently no questions to show.', 'wp_courseware'), 7));
	}
	
	// Add the form for the start of the multiple-add 
	$formWrapper_start = false;
	if ('std' == $actionMode)
	{
		// Set the action URL to preserve parameters that we have.
		$formWrapper_start = sprintf('<form method="POST" action="%s">', WPCW_urls_getURLWithParams($summaryPageURL, 'pagenum'));	
	}	
	
	
	// Create tag filter (uses a form)
	$tagFilter = WPCW_questions_tags_createTagFilter($tagFilter, 'WPCW_showPage_QuestionPool');
	
	// Work out paging and filtering
	$paging = WPCW_tables_showPagination($baseURL, $paging_pageWanted, $paging_pageCount, $paging_totalCount, $paging_recordStart, $paging_recordEnd, $tagFilter);
	
	
	// Show the actions
	$formWrapper_end = false;
	if ('std' == $actionMode)
	{
		$formWrapper_end = WPCW_showPage_QuestionPool_actionForm();		
		
		// Form tag - needed for processing 
		$formWrapper_end .= '</form>';
	}
	
	// Finally show table
	return $paging . $formWrapper_start . $tbl->toString() . $formWrapper_end . $paging;
}



/**
 * Update the user summary columns to show our custom fields, and hide cluttering ones.
 * @param Array $column_headers The list of columns to show (before showing them).
 * @return Array The actual list of columns to show.
 */
function WPCW_users_manageColumns($column_headers)
{
	// Remove list of posts
    unset($column_headers['posts']);
    
    // Remove name and email address (so that we can combine it)
   	unset($column_headers['name']);
   	unset($column_headers['email']);
	unset($column_headers['role']);
    
    // Add new name column
    $column_headers['wpcw_col_user_details'] = __('Details', 'wp_courseware');
    
    // Training Course Allocations
    $column_headers['wpcw_col_training_courses'] 		= __('Training Course Progress', 'wp_courseware');
    $column_headers['wpcw_col_training_courses_access'] = __('Actions', 'wp_courseware');
    
    
    return $column_headers;
}




/**
 * Creates the column columns of data.
 * 
 * @param String $colContent The content of the column.
 * @param String $column_name The name of the column we're changing.
 * @param Integer $user_id The ID of the user we're rendering.
 * 
 * @return String The formatted HTML code for the table.
 */
function WPCW_users_addCustomColumnContent($colContent, $column_name, $user_id) 
{
	
	switch ($column_name)
	{
		// #### Basically condense user details.
		case 'wpcw_col_user_details': 		
	    	// Format nice details of name, email and role to save space.
	    	$userDetails = get_userdata($user_id);
	    	
	    	// Ensure role is valid and it exists.
	    	$roleName = false;
	    	if (!empty($userDetails->roles)) {
	    		$roleName = $userDetails->roles[0];
	    	}
	    	
			$colContent = sprintf('<span class="wpcw_col_cell_name">%s</span>', $userDetails->data->display_name);
			$colContent .= sprintf('<span class="wpcw_col_cell_email"><a href="mailto:%s" target="_blank">%s</a></span>', $userDetails->data->user_email, $userDetails->data->user_email);
			$colContent .= sprintf('<span class="wpcw_col_cell_role">%s</span>', ucwords($roleName));
	    break;
	    
	    
    
	    // ####ÊThe training course statuses.
	    case 'wpcw_col_training_courses':
	    	// Got some associated courses, so render progress.
	    	$courseData = WPCW_users_getUserCourseList($user_id);
	    	if ($courseData)
	    	{
	    		foreach ($courseData as $courseDataItem) {
	    			$colContent .= WPCW_stats_convertPercentageToBar($courseDataItem->course_progress, $courseDataItem->course_title);
	    		}
	    	} 
	    	
	    	// No courses
	    	else {
	    		$colContent = __('No associated courses', 'wp_courseware');
	    	}
	    break;
	    
	    
	    // #### Links to change user access for courses.
	    case 'wpcw_col_training_courses_access':
	    	$colContent = sprintf('<span><a href="%s&user_id=%d" class="button-primary">%s</a></span>',
	    		admin_url('users.php?page=WPCW_showPage_UserProgess'), 
	    		$user_id,
	    		__('View Detailed Progress', 'wp_courseware')
	    	);
	    	
	    	// View the full progress of the user.
	    	$colContent .= sprintf('<span><a href="%s&user_id=%d" class="button-secondary">%s</a></span>',
	    		admin_url('users.php?page=WPCW_showPage_UserCourseAccess'), 
	    		$user_id,
	    		__('Update Course Access Permissions', 'wp_courseware')
	    	);
	    	
	    	// Allow the user progress to be reset 
	    	$courseData = WPCW_users_getUserCourseList($user_id);
	    	$courseIDList = array();
	    	if (!empty($courseData)) 
	    	{
	    		// Construct a simple list of IDs that we can use for filtering.
	    		foreach ($courseData as $courseDetails)
	    		{
	    			$courseIDList[] = $courseDetails->course_id;
	    		}
	    	}
	    	
	    	// Construct the mini form for resetting the user progress.
	    	$colContent .= '<span>';
	    	
	    		$colContent .= '<form method="get">';
	    		    		
	    		// Using this method of the user ID automaticallyed added the first user to any bulk action, which is clearly a bug.
	    		// So the field had to be renamed.
	    		//$colContent .= sprintf('<input type="hidden" name="users[]" value="%d" >', $user_id);
	    		$colContent .= sprintf('<input type="hidden" name="wpcw_users_single" value="%d" >', $user_id);
	    		
	    		// The dropdown for this.
	    		$colContent .= WPCW_courses_getCourseResetDropdown(
	    				'wpcw_user_progress_reset_point_single', 
	    				$courseIDList, 
	    				__('No associated courses.', 'wp_courseware'),  
	    				__('Reset this user to beginning of...', 'wp_courseware'), 
	    				'', 
	    				'wpcw_user_progress_reset_select wpcw_user_progress_reset_point_single'
	    			);
	    		
	    		$colContent .= '</form>';
	    	$colContent .= '</span>';
	    break;
    }
    
    
    
    return $colContent;
}


/**
 * Creates the dropdown form and button that allows the bulk-reset of users on their respective courses.
 */
function WPCW_users_showUserResetAbility()
{
	$html = '<div class="wpcw_user_bulk_progress_reset">';
			
		$html .= WPCW_courses_getCourseResetDropdown(
			'wpcw_user_progress_reset_point_bulk', 
			false, 
			__('No courses yet.', 'wp_courseware'), 
			__('Reset User Progress to beginning of...', 'wp_courseware'), 
			'wpcw_user_progress_reset_point_bulk',
			'wpcw_user_progress_reset_select'
		);		
		$html .= sprintf('<input id="wpcw_user_progress_reset_point_bulk_btn" name="wpcw_user_bulk_progress_reset" type="submit" class="button" value="Reset">');
	
	$html .= '</div>';
	echo $html; 
}



/**
 * Shows reset success message in right place in HTML to not trigger errors.
 */
function WPCW_users_processUserResetAbility_showSuccess()
{
	if (isset($_GET['wpcw_reset']))
	{
		printf('<div id="message" class="updated"><p>%s</p></div>', __('User progess has been reset.', 'wp_courseware'));
	}
}


/**
 * This function removes the user progress for the specified list of users and units.
 * 
 * @param Array $userList The list of users to reset.
 * @param Array $unitList The list of units to remove from their progress.
 * @param Object $courseDetails The details of the course.
 * @param Integer $totalUnitCount The total number of units in this course.
 */
function WPCW_users_resetProgress($userList, $unitList, $courseDetails, $totalUnitCount)
{
	global $wpcwdb, $wpdb;
	$wpdb->show_errors();
	
	// Nothing to do!
	if (empty($userList) || empty($unitList)) {
		return;
	}
	
	$SQL_units = '(' . implode(',', $unitList) . ')';
	$SQL_users = '(' . implode(',', $userList) . ')';
	
	// Delete all data in user progress in one hit
	$SQL = "DELETE FROM $wpcwdb->user_progress
			WHERE user_id IN $SQL_users
			  AND unit_id IN $SQL_units
			";
	$wpdb->query($SQL);
	
	// Delete all quiz data in one hit
	$SQL = "DELETE FROM $wpcwdb->user_progress_quiz
			WHERE user_id IN $SQL_users
			  AND unit_id IN $SQL_units
			";
	$wpdb->query($SQL);
	
	// Delete all user locks
	$SQL = "DELETE FROM $wpcwdb->question_rand_lock
			WHERE question_user_id IN $SQL_users
			  AND parent_unit_id IN $SQL_units
			";
	$wpdb->query($SQL);
	
	
	// Now update the user progress.
	foreach ($userList as $aUser)
	{
		$progressExists = $wpdb->get_row($wpdb->prepare("
			SELECT * 
			FROM $wpcwdb->user_courses 
			WHERE user_id = %d 
			 AND course_id = %d
		", $aUser, $courseDetails->course_id));
		
		if ($progressExists)
		{
			// Update the progress with their actual progress count.
			WPCW_users_updateUserUnitProgress($courseDetails->course_id, $aUser, $totalUnitCount);
		}
	}
}






/**
 * Code that checks if we're resetting the user progress.
 */
function WPCW_users_processUserResetAbility()
{
	// Check bulk by default, otherwise check the single user change.
	$resetTypeCommand = WPCW_arrays_getValue($_GET, 'wpcw_user_progress_reset_point_bulk');
	if (!$resetTypeCommand) {
		$resetTypeCommand = WPCW_arrays_getValue($_GET, 'wpcw_user_progress_reset_point_single');
	}
	
	// Detect the reset command.
	if ($resetTypeCommand)
	{
		// Check for a specific module/unit/course to reset. If none found, then refresh.
		if (!preg_match('/^(course|module|unit)_([0-9]+)$/', $resetTypeCommand, $matches))
		{
			// No parameter found, reset.
			wp_redirect(add_query_arg('wpcw_reset', false, 'users.php'));
			die();
		}		
		
		$userList = array();
		
		// Check array of users first (as not triggered for a single update)
		if (isset($_GET['users'])) 
		{		
			// Check if we've chosen any users to reset. If not, reset.
			$userList = array_map('intval', (array)$_GET['users']);
		}
		
		// Check single user - checking here, as user array is rarer.
		else if (isset($_GET['wpcw_users_single']) && isset($_GET['wpcw_user_progress_reset_point_single']) && $_GET['wpcw_user_progress_reset_point_single']) 
		{
			// Add a single user ID.
			$userList[] = intval($_GET['wpcw_users_single']);
		}		
		
		// No users at all.
		if (empty($userList)) {
			wp_redirect(add_query_arg('wpcw_reset', false, 'users.php'));
			die();
		}

		// See what we tried to reset to.
		$unitList = false;
		$courseMap = new WPCW_CourseMap(); 
		switch ($matches[1])
		{
			case 'unit':
					$courseMap->loadDetails_byUnitID($matches[2]);
					$unitList = $courseMap->getUnitIDList_afterUnit($matches[2]);
				break;
				
			case 'module':
					$courseMap->loadDetails_byModuleID($matches[2]);
					$unitList = $courseMap->getUnitIDList_afterModule($matches[2]);
				break;
			
			case 'course':
					$courseMap->loadDetails_byCourseID($matches[2]);
					$unitList = $courseMap->getUnitIDList_forCourse();
				break;
				
			default:
					// No parameter found, reset.
					wp_redirect(add_query_arg('wpcw_reset', false, 'users.php'));
					die();
				break;
		}
		
		// Now do the reset of the progress.
		WPCW_users_resetProgress($userList, $unitList, $courseMap->getCourseDetails(), $courseMap->getUnitCount());

		// Redirect to remove the GET flags from the URL.
		wp_redirect(add_query_arg('wpcw_reset', 'true', 'users.php'));		
		die();
	}

}



/**
 * Generate a list of filters for a table, that ultimately is used to trigger an SQL filter on the view
 * of items in a table.
 * 
 * @param Array $filterList The list of items to use in the filter.
 * @param String $baseURL The string to use at the start of the URL to ensure it works correctly.
 * @param String $activeItem The key that matches the item that's currently selected.
 * 
 * @return String The HTML to render the filter.
 */
function WPCW_table_showFilters($filterList, $baseURL, $activeItem)
{
	$html = '<div class="subsubsub wpcw_table_filter">';
	foreach ($filterList as $filterKey => $filterLabel)
	{
		$html .= sprintf('<a href="%s%s" class="%s">%s</a>', 
			$baseURL, $filterKey,
			($activeItem == $filterKey ? 'wpcw_table_filter_active' : ''),  
			$filterLabel
		);
	}
	
	return $html . '</div>';
}



/**
 * Show the section that deals with pagination.
 * 
 * @param String $baseURL The URL to use that starts of the paging.
 * @param Integer $pageNumber The current page.
 * @param Integer $pageCount The number of pages.
 * @param Integer $dataCount The number of data rows.
 * @param Integer $recordStart The current record number.
 * @param Integer $recordEnd The ending record number.
 * @param String $leftControls The HTML for controls shown on the left.
 */
function WPCW_tables_showPagination($baseURL, $pageNumber, $pageCount, $dataCount, $recordStart, $recordEnd, $leftControls = false)
{	
	$html = '<div class="tablenav wpcw_tbl_paging">';
	
	$html .= '<div class="wpbs_paging tablenav-pages">';
	$html .= sprintf('<span class="displaying-num">Displaying %s &ndash; %s of %s</span>',
				$recordStart,
				($dataCount < $recordEnd ? $dataCount : $recordEnd), // ensure that the upper number of the record matches how many are left.
				$dataCount
			); 

	// Got more than 1 page?				
	if ($pageCount > 1) 
	{
		if ($pageNumber > 1) 
		{
			$html .= sprintf('<a href="%s%d" class="prev page-numbers" data-pagenum="%d">&laquo;</a>'."\n",
						$baseURL,
						$pageNumber-1,
						$pageNumber-1						
					);
		}
		
		$pageList = array();
						
		// Always have first and last page linked
		$pageList[] = 1;
		$pageList[] = $pageCount;
		
		// Have 3 pages either side of page we're on
		if ($pageNumber-3 > 1) {
			$pageList[] = $pageNumber-3;
		}
		
		if ($pageNumber-2 > 1) {
			$pageList[] = $pageNumber-2;
		}
		if ($pageNumber-1 > 1) {
			$pageList[] = $pageNumber-1;
		}
		if ($pageNumber+1 < $pageCount) {
			$pageList[] = $pageNumber+1;
		}
		if ($pageNumber+2 < $pageCount) {
			$pageList[] = $pageNumber+2;
		}
		if ($pageNumber+3 < $pageCount) {
			$pageList[] = $pageNumber+3;
		}				

		// Plus we want the current page
		if ($pageNumber != $pageCount && $pageNumber != 1) {
			$pageList[] = $pageNumber;
		}
		
		// Sort pages in order and then render them
		sort($pageList);
		$previous = 0;
		foreach ($pageList as $pageLink)
		{
			// Add dots if a large gap between numbers
			if ($previous > 0 && ($pageLink - $previous) > 1) {
				$html .= '<span class="page-numbers dots">...</span>';
			}
			
			$html .= sprintf('<a href="%s%d" class="page-numbers %s" data-pagenum="%d">%s</a>',
				$baseURL,
				$pageLink,
				($pageNumber == $pageLink ? 'current' : ''),
				$pageLink,
				$pageLink
				);

			// Want to check what the previous one is
			$previous = $pageLink;
		}
		
		// Got pages left at the end
		if ($pageCount > $pageNumber) {
			$html .= sprintf('<a href="%s%s" class="next page-numbers" data-pagenum="%d">&raquo;</a>',
						$baseURL, 
						$pageNumber+1, 
						$pageNumber+1
						);
		}
	
	} // end of it pageCount > 1
	$html .= '</div>'; // end of tablenav-pages
	
	
	$html .= '</div>'; // end of tablenav
	$html .= $leftControls;
	
	return $html;
}




/**
 * Get the URL for the desired page, preserving any parameters.
 * @param String $pageBase The based page to fetch.
 * @param Mixed $ignoreFields The array or string of parameters not to include.
 * @return String The newly formed URL. 
 */
function WPCW_urls_getURLWithParams($pageBase, $ignoreFields = false)
{
	// Parameters to extract from URL to keep in the URL.
	$params = array (
		's' 			=> false, 
		'pagenum' 		=> false, 
		'filter'		=> false
	);
	
	// Got fields we don't want in the URL? Handle both a string and
	// arrays
	if ($ignoreFields) 
	{
		if (is_array($ignoreFields)) {	
			foreach ($ignoreFields as $field) {
				unset($params[$field]);
			}
		} else {
			unset($params[$ignoreFields]);
		}
	}	
	 
	foreach ($params as $paramName => $notused)
	{
		$value = WPCW_arrays_getValue($_GET, $paramName);
		if ($value) {
			$pageBase .=  '&' . $paramName . '=' . $value;
		}
	}
	
	return $pageBase;
}



/**
 * Method called whenever a post is saved, which will check that any course units 
 * save their meta data. 
 * 
 * @param Integer $post_id The ID of the post being saved.
 */
function WPCW_units_saveUnitPostMetaData($post_id, $post)
{	 
	// Check we have a course unit, not any other type (including revisions).
    if ('course_unit' != $post->post_type) {
        return;
    }
    
	// Check user is allowed to edit the post.    
    if ( !current_user_can( 'edit_post', $post_id)) {
        return;
    }
    
    global $wpdb, $wpcwdb;
	$wpdb->show_errors();
	
	// See if there's an entry in the courseware table
	$SQL = $wpdb->prepare("
		SELECT * 
		FROM $wpcwdb->units_meta 
		WHERE unit_id = %d
	", $post_id);
	
	// Ensure there's a blank entry in the database for this post.
	if (!$wpdb->get_row($SQL))
	{
		$SQL = $wpdb->prepare("
			INSERT INTO $wpcwdb->units_meta (unit_id, parent_module_id) 
			VALUES (%d, 0)
		", $post_id);
		
		$wpdb->query($SQL);		
	}
	
	// Update the selection for the unit template
	update_post_meta($post_id, WPCW_TEMPLATE_META_ID, WPCW_arrays_getValue($_POST, 'wpcw_units_choose_template_list'));
}



 
/**
 * Creates the column columns of data.
 * 
 * @param String $column_name The name of the column we're changing.
 * @param Integer $post_id The ID of the post we're rendering.
 * 
 * @return String The formatted HTML code for the table.
 */
function WPCW_units_addCustomColumnContent($column_name, $post_id)
{
	switch ($column_name)
	{
		// Associated quiz link
		case 'wpcw_col_quiz':
			if ($quizDetails = WPCW_quizzes_getAssociatedQuizForUnit($post_id, false, false))
			{
				printf('<a href="%s&quiz_id=%d">%s</a>', admin_url('admin.php?page=WPCW_showPage_ModifyQuiz'), $quizDetails->quiz_id, $quizDetails->quiz_title );
			} 
			// No quiz
			else {
				echo '-';
			}
			break;
		
		case 'wpcw_col_module_and_course':				
			$parentObj = WPCW_units_getAssociatedParentData($post_id);
	
			if (!$parentObj) {
				_e('n/a', 'wp_courseware');
			}
			// Got parent items, render away
			else {
				printf('<span class="wpcw_col_cell_module"><b>%s %d</b> -  %s</span>
						<span class="wpcw_col_cell_course"><b>%s:</b> %s</span>',
					__('Module', 'wp_courseware'), 
					$parentObj->module_number,
					$parentObj->module_title,
					__('Course', 'wp_courseware'),
					$parentObj->course_title
				);
			}
		break; // wpcw_col_module_and_course
	}
}


/**
 * Function called when a post is being deleted by WordPress. Want to check
 * if this relates to a unit, and if so, remove it from our tables.
 * 
 * @param Integer $post_id The ID of the post being deleted.
 */
function WPCW_units_deleteUnitHandler($post_id)
{
	global $wpdb, $wpcwdb;
	$wpdb->show_errors();
	
	// See if we've got data on this unit in the meta table
	$SQL = $wpdb->prepare("SELECT * FROM $wpcwdb->units_meta WHERE unit_id = %d", $post_id);
	if ($unitDetails = $wpdb->get_row($SQL))
	{
		// Right, it's one of our units, so need to delete the meta data
		$SQL = $wpdb->prepare("DELETE FROM $wpcwdb->units_meta WHERE unit_id = %d", $post_id);
		$wpdb->query($SQL);
		
		// Delete it from the user progress too
		$SQL = $wpdb->prepare("DELETE FROM $wpcwdb->user_progress WHERE unit_id = %d", $post_id);
		$wpdb->query($SQL);
		
		// Associated with a course?
		$parentData = WPCW_units_getAssociatedParentData($post_id);
		if ($unitDetails->parent_course_id > 0) {		
			// Need to update the course unit count and progresses
			do_action('wpcw_course_details_updated', $unitDetails->parent_course_id);
		}		
		
		// Quiz - Unconnect it from the quiz that it's associated with.
		$SQL = $wpdb->prepare("UPDATE $wpcwdb->quiz SET parent_unit_id = 0, parent_course_id = 0 WHERE parent_unit_id = %d", $post_id);
		$wpdb->query($SQL);
		
		// Quiz Progress - Unconnect it from this quiz. 
		$SQL = $wpdb->prepare("UPDATE $wpcwdb->user_progress_quiz SET unit_id = 0 WHERE unit_id = %d", $post_id);
		$wpdb->query($SQL);
	}	
}



/**
 * Update the course unit summary columns to shows the related modules and courses.
 * @param Array $column_headers The list of columns to show (before showing them).
 * @return Array The actual list of columns to show.
 */
function WPCW_units_manageColumns($column_headers)
{    
	// Copy date column
	$oldDate = $column_headers['date']; 
	unset($column_headers['date']);
	
    // Add new columns
    $column_headers['wpcw_col_module_and_course'] 	= __('Associated Module &amp; Course', 'wp_courseware');
    $column_headers['wpcw_col_quiz'] 				= __('Associated Quiz', 'wp_courseware');
    
    // Put date at the end
    $column_headers['date'] = $oldDate;
    
    return $column_headers;
}





/**
 * Add a duplicate post link.
 */
function WPCW_units_admin_addActionRows($actions, $post)
{
	// Only add duplicate for units.
	if ('course_unit' == $post->post_type)
	{
		// Create a nonce & add an action to duplicate this unit.  
  		$actions['duplicate_post'] = sprintf('<a class="wpcw_units_admin_duplicate" data-nonce="%s" data-postid="%d" href="#">%s</a>', 
  			wp_create_nonce('wpcw_ajax_unit_change'), 
  			$post->ID,
  			__('Duplicate Unit', 'wp_courseware')
  		);
	}
	
	return $actions;
}
add_filter( 'post_row_actions', 'WPCW_units_admin_addActionRows', 10, 2);



/**
 * Attaches the meta boxes to posts and pages to add extra information to them.
 */
function WPCW_units_showEditScreenMetaBoxes()
{
	// Posts - Shows the conversion metabox to convert the post type
	add_meta_box( 
        'wpcw_units_convert_post',
        __( 'Convert Post to Course Unit', 'wp_courseware' ),
        'WPCW_units_metabox_showConversionTool',
        'post',
        'side',
        'low'        
    );
    
    // Pages - Shows the conversion metabox to convert the post type
    add_meta_box(
        'wpcw_units_convert_post',
        __( 'Convert Page to Course Unit', 'wp_courseware' ), 
        'WPCW_units_metabox_showConversionTool',
        'page',
        'side',
        'low'
    );
    
    // Course Units - template selection
    add_meta_box(
        'wpcw_units_choose_template',
        __( 'Course Unit Template', 'wp_courseware' ), 
        'WPCW_metabox_showTemplateSelectionTool',
        'course_unit',
        'side',
        'default'
    );
}


/**
 * Constructs the inner form to convert the post type to a course unit.
 */
function WPCW_units_metabox_showConversionTool()
{
	global $post;
	$conversionURL = admin_url('admin.php?page=WPCW_showPage_ConvertPage&postid=' . $post->ID);
	
	?><p>	
	<?php printf(__('Click to <a href="%s">convert this <b>%s</b> to a Course Unit</a>.', 'wp_courseware'), $conversionURL, get_post_type($post->ID)); ?>
	
	</p><?php	
}




/**
 * Generate a course list for resetting the progress for a user.
 * 
 * @param $fieldName String The name to use for the name attribute for this dropdown.
 * @param $courseIDList Array If specified, this is a list of IDs to determine which courses to use in the reset box.
 * @param $blankMessage String the message to show if there are no courses.
 * @param $addBlank String Use this string as the first item in the dropdown.
 * @param $cssID String The CSS ID to use for the select box.
 * @param $cssClass String The CSS class to use for the select box.
 *  
 * @return String The course reset dropdown box.
 */
function WPCW_courses_getCourseResetDropdown($fieldName, $courseIDList = false, $blankMessage, $addBlank, $cssID, $cssClass)
{	
	$selectDetails = array('' => $addBlank);
	
	// Need all courses
	$courseList = WPCW_courses_getCourseList();
	if (!empty($courseList))
	{
		$blankCount = 2;
		foreach ($courseList as $courseID => $aCourse)
		{
			// Filter out unwanted courses.
			if (is_array($courseIDList) && !in_array($courseID, $courseIDList)) {
				continue;
			}			
			
			// Have sentinel of course_ to identify a course.
			$selectDetails['course_' . $courseID] = $aCourse;
			
			// Now we add the modules for this course
			$moduleList = WPCW_courses_getModuleDetailsList($courseID);
			if (!empty($moduleList))
			{
				foreach ($moduleList as $moduleID => $moduleDetails)
				{
					// Now we add the units for this course
					$units = WPCW_units_getListOfUnits($moduleID);
					if (!empty($units))
					{
						// Only add a module if it has units, to make resetting easier.
						$selectDetails['module_' . $moduleID] = sprintf('&nbsp;&nbsp;- %s %d: %s',  
							__('Module', 'wp_courseware'),
							$moduleDetails->module_number,  
							$moduleDetails->module_title						
						);
						
						foreach ($units as $unitID => $unitDetails)
						{
							$selectDetails['unit_' . $unitID] = sprintf('&nbsp;&nbsp;-- %s %d: %s',  
								__('Unit', 'wp_courseware'),
								$unitDetails->unit_meta->unit_number,  
								$unitDetails->post_title						
							);
						}
					} // end of unit list check
					
				}// end of foreach module
			} // end of module list check
			
			// Add a blank sentinel to space out courses.
			$paddingKey = str_pad(false, $blankCount++, ' ');
			$selectDetails[$paddingKey] = '&nbsp';
		}
		
	}
	
	// No courses... show meaningful message to the trainer.
	if (count($selectDetails) == 1) {
		$selectDetails[' '] = $blankMessage;
	}
	
	// Generate the select box. Use the $cssID as the name of the field too.
	return WPCW_forms_createDropdown($fieldName, $selectDetails, false, $cssID, $cssClass);
}


/**
 * Calculate the actual number of questions in a quiz - supporting random questions. 
 * 
 * @param Integer $quizID The ID of the quiz to get a count for.
 * @return Integer The actual number of questions in the quiz.
 */
function WPCW_quizzes_calculateActualQuestionCount($quizID)
{
	global $wpdb, $wpcwdb;
	$wpdb->show_errors();
	
	return $wpdb->get_var($wpdb->prepare("
		SELECT SUM(q.question_expanded_count) as total_questions 
		FROM $wpcwdb->quiz_qs_mapping qm
			LEFT JOIN $wpcwdb->quiz_qs q ON q.question_id = qm.question_id
		WHERE qm.parent_quiz_id = %d
	", $quizID));
}



/**
 * Get a list of all quizzes and surveys for a training course, in the order that they are used.
 * 
 * @param Integer $courseID The ID of the course to get the quizzes for.
 * 
 * @return Array A list of the quizzes in order.
 */
function WPCW_quizzes_getAllQuizzesAndSurveysForCourse($courseID)
{
	global $wpcwdb, $wpdb;
	$wpdb->show_errors();
		
	return $wpdb->get_results($wpdb->prepare("
    	SELECT * 
    	FROM $wpcwdb->quiz q
    		LEFT JOIN $wpcwdb->units_meta um ON um.unit_id = q.parent_unit_id
    	WHERE q.parent_course_id = %d 
    	ORDER BY unit_order
   	", $courseID));
}


/**
 * Generate an array of pass marks for a select box.
 * @param String $addBlank If specified, add a blank entry to the top of the list
 * @return Array A list of pass marks.
 */
function WPCW_quizzes_getPercentageList($addBlank = false)
{
	$list = array();
	
	if ($addBlank) {
		$list[] = $addBlank;
	}
		
	for ($i = 100; $i > 0; $i--) {
		$list[$i] = $i . '%';
	}
	
	return $list;
}


/**
 * Return the number of quizzes that are pending grading or need unblocking for a user.
 * @return Integer The total number of quizzes that need attention.
 */
function WPCW_quizzes_getCoursesNeedingAttentionCount()
{
	global $wpdb, $wpcwdb;
    $wpdb->show_errors();
 
    return $wpdb->get_var("
		SELECT COUNT(*)
		FROM $wpcwdb->user_progress_quiz
		WHERE quiz_is_latest = 'latest'
		  AND (quiz_needs_marking > 0 
		       OR quiz_next_step_type = 'quiz_fail_no_retakes') 
	");    
}








/**
 * Translates a question type into its proper name.
 * 
 * @param String $questionType The type of the quiz question.
 * @return String The question type as a label.
 */
function WPCW_quizzes_getQuestionTypeName($questionType)
{
	$questionTypeStr = __('n/a', 'wp_courseware');
	switch ($questionType)
	{
		case 'truefalse':
				$questionTypeStr = __('True/False', 'wp_courseware');
			break;
		
		case 'multi':
				$questionTypeStr = __('Multiple Choice', 'wp_courseware');
			break;
	
		case 'upload':
				$questionTypeStr = __('File Upload', 'wp_courseware');
			break;
			
		case 'open':
				$questionTypeStr = __('Open Ended', 'wp_courseware');
			break;
			
		case 'random_selection':
				$questionTypeStr = __('Random Selection', 'wp_courseware');
			break;
	}
	return $questionTypeStr;
}


/**
 * Determine if any of the specified list of questions require manual grading.
 * 
 * @param Array $quizItems The items to check
 * @return Boolean True if the items need manual grading, false otherwise.
 */
function WPCW_quizzes_containsQuestionsNeedingManualGrading($quizItems)
{
	if (!$quizItems) {
		return false;
	}
	
	foreach ($quizItems as $quizItem)
	{
		// Open or upload questions
		if ('open' == $quizItem->question_type || 'upload' == $quizItem->question_type) {
			return true;	
		}			
	}
	
	return false;
}




?>