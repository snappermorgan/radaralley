var $j = jQuery.noConflict();
$j(function()
{
			
	// Adds a new tag via AJAX. Called via button or clicking 'enter' in input field.
	function wpcw_js_questions_addNewTag(senderObj)
	{
		// The container for the tags.
		var tagWrapper = senderObj.closest('.wpcw_quiz_details_question_tags');
		var tagWrapperID = tagWrapper.attr('id');
		
		// Has the question been saved? Determined by the data attribute for the parent <tr>
		var questionSaved = senderObj.closest('tr').attr('data-questionsaved');
		
		// Clear out what's in the box now
		var newTagIdea = tagWrapper.find('.wpcw_question_add_tag_input').val().trim();
		tagWrapper.find('.wpcw_question_add_tag_input').val('');
		
		// Abort - there is no tag to use.
		if (!newTagIdea) {
			return;
		}
		
		// Initialise AJAX message
        var data = {
        		action: 			'wpcw_handle_question_new_tag',
        		questionid: 		tagWrapper.attr('data-questionid'),
        		tagtext:			newTagIdea,
        		isquestionsaved: 	questionSaved
        	};
        
        jQuery.post(ajaxurl, data, function(response)
        {
        	// Added - so add a nice new tag item.
    		if (response.success && response.html) {
    			$j('#' + tagWrapperID).find('.wpcw_tag_list_wrapper').append(response.html);
    		} 
    		// Show error if there's one.
    		else {
    			alert(response.errormsg);
    		}
    	});
	}	

	// Add new tag - Button
	$j('.wpcw_dragable_question_holder').on('click', '.wpcw_question_add_tag_btn', function(e)
	{		
		e.preventDefault();
		wpcw_js_questions_addNewTag($j(this));
	});
	
	// Add new tag - Input
	$j('.wpcw_dragable_question_holder').on('keydown', '.wpcw_question_add_tag_input', function(e)
	{		
		if (e.keyCode == 13) {
			wpcw_js_questions_addNewTag($j(this));
			e.preventDefault();
	    }		
	});
	
	// Remove a tag (from a question)
	$j('.wpcw_dragable_question_holder').on('click', '.ntdelbutton', function(e)
	{		
		e.preventDefault();
		
		// Has the question been saved? Determined by the data attribute for the parent <tr>
		var questionSaved = $j(this).closest('tr').attr('data-questionsaved');
				
		// Initialise AJAX message
        var data = {
        		action: 			'wpcw_handle_question_remove_tag',
        		questionid: 		$j(this).attr('data-questionid'),
        		tagid: 				$j(this).attr('data-tagid'),
        		isquestionsaved: 	questionSaved
        	};
        
        // Remove the item now.
        $j(this).closest('span').fadeOut(200, function(){ 
            $j(this).remove();
        });
        
        jQuery.post(ajaxurl, data, function(response)
        {
        	// Assume tag removal has worked... don't process response.
    	});
	});
	
	
	// Prompt for resetting for whole course
	$j('#wpcw_course_btn_progress_reset_whole_course').click(function(e)
	{
		if (!confirm(wpcw_js_consts_adm.confirm_whole_course_reset)) {
			e.preventDefault();
			return false;
		} 
		
		return true;
	});
	
	// Prompt for access for whole course - all users
	$j('#wpcw_course_btn_access_all_existing_users').click(function(e)
	{
		if (!confirm(wpcw_js_consts_adm.confirm_access_change_users)) {
			e.preventDefault();
			return false;
		} 
		
		return true;
	});
	
	// Prompt for access for whole course - all admins
	$j('#wpcw_course_btn_access_all_existing_admins').click(function(e)
	{
		if (!confirm(wpcw_js_consts_adm.confirm_access_change_admins)) {
			e.preventDefault();
			return false;
		} 
		
		return true;
	});	
	
	
	// Draggable modules
	$j('.wpcw_dragable_modules').sortable({
		placeholder: "wpcw_dragable_modules_placeholder",	// Class for placeholder for CSS
		forcePlaceholderSize: true,							// Forces placeholder to be right size
		cursor: 'pointer',									// Sets useful UI cursor
		stop: function(event, ui) { showUnitsChanged(); }	// UI change because ordering has started
	}).disableSelection();
	
	// Draggable units
	$j('.wpcw_dragable_modules ol, #wpcw_unassigned_units ol').sortable({
		placeholder: "wpcw_dragable_units_placeholder",		// Class for placeholder for CSS
		forcePlaceholderSize: true,							// Forces placeholder to be right size
		cursor: 'pointer',									// Sets useful UI cursor
		connectWith: ".wpcw_dragable_units_connected",		// Links the units		
		stop: function(event, ui) { showUnitsChanged(); }	// UI change because ordering has started
	}).disableSelection();	
	
	// Draggable quizzes
	$j('.wpcw_dragable_quiz_holder ol, #wpcw_unassigned_quizzes ol').sortable({
		placeholder: "wpcw_dragable_quizzes_placeholder",	// Class for placeholder for CSS
		forcePlaceholderSize: true,							// Forces placeholder to be right size
		cursor: 'pointer',									// Sets useful UI cursor
		connectWith: ".wpcw_dragable_quizzes_connected",	// Links the units		
		stop: function(event, ui) { showUnitsChanged(); },	// UI change because ordering has started
		receive: function(event, ui) {
	        if ($j(this).hasClass('wpcw_one_only') &&		// Ensure only units restrict to 1 
	        	$j(this).children().length > 1) {			// See if more than 1.
	            $j(ui.sender).sortable('cancel');
	        }
	    }
	});
	
	// Draggable quiz questions for ordering.
	$j('.wpcw_dragable_question_holder').sortable({
		placeholder: "wpcw_dragable_questions_placeholder",		// Class for placeholder for CSS
		forcePlaceholderSize: true,								// Forces placeholder to be right size
		cursor: 'move',											// Sets useful UI cursor
		handle: '.wpcw_move_icon', 								// Move handle is move icon.
		stop: function(event, ui) { reorderQuizQuestions(); }	// Re-order the questions using the hidden field.
	});	
	
	
	// Delete Confirmation
	$j('.wpcw_delete_item').click(function()
	{
		// Use title to grab the message, to handle multi-language support.
		if (confirm($j(this).attr('title')) == true) {
			return true;
		}
		return false;
	});
	
	// Remove an answer from a quiz
	$j('#wpcw_section_break_quiz_questions').on('click', '.wpcw_question_remove', function(e)
	{
		e.preventDefault();
		
		// Get parent for later and remove this current row
		var parentTable = $j(this).closest('li').attr('id');
				
		// Get ID of this answer to remove related field rows.
		var rowToRemove = $j(this).closest('tr');
		var idForAnswerRow = rowToRemove.attr('data-answer-id');
		
		// Remove row and associated image row.  (using table ID to be to avoid deleting other rows).
		rowToRemove.remove();
		$j('#' + parentTable + ' tr.wpcw_quiz_row_answer_image_' + idForAnswerRow).remove();
		
		reorderQuestionAnswers(parentTable);
	});	
	
	// Add an answer to a question
	$j('#wpcw_section_break_quiz_questions').on('click', '.wpcw_question_add', function(e)
	{
		e.preventDefault();
		
		// ID of the parent table holding the items.
		var parentTable = $j(this).closest('li').attr('id');
		
		// Get this row, clone it
		var row = $j(this).closest('tr');
		var newAnswer = row.clone();
		
		// Empty row, ensure correct answer is not checked
		newAnswer.find('td input[type="text"]').val('');
		newAnswer.find('.wpcw_quiz_details_tick_correct input[type="radio"]').attr('checked', false);
		
		// Get the associated image row (using table ID to be as specific as possible to avoid cloning other rows).
		var idForAnswerRow = row.attr('data-answer-id');
		var rowImg = $j('#' + parentTable + ' tr.wpcw_quiz_row_answer_image_' + idForAnswerRow);
		var newAnswerImg = rowImg.clone();
		
		// Now clone that image row, and reset the details.
		newAnswerImg.find('td input[type="text"]').val('');
		
		// Add new rows after existing image field.
		newAnswer.insertAfter(row.next('.wpcw_quiz_row_answer_image'));
		newAnswerImg.insertAfter(newAnswer);
				
		reorderQuestionAnswers(parentTable);
	});
	
	
	// Re-order the answers for a question
	function reorderQuestionAnswers(tableid)
	{
		var count = 1;
		$j('#'+ tableid + ' .wpcw_quiz_row_answer').each(function(row) 
		{
			// Renumber the answer
			var qNum = $j(this).find('th span').text(count);
			
			// Renumber radio value
			$j(this).find('.wpcw_quiz_details_tick_correct input[type="radio"]').val(count);
			
			// Renumber the ID of the row.
			$j(this).attr('data-answer-id', count);
			
			// Renumber the input fields to use the right name 
			var ansField = $j(this).find('td input[type="text"]'); 
			var newName = ansField.attr('name').replace(/\[\d+\]/g, '[' + count + ']');
			ansField.attr('name', newName);
			
			// Renumber the image wrapper row
			var nextImgFieldRow = $j(this).next('.wpcw_quiz_row_answer_image');
			nextImgFieldRow.attr('class', 'wpcw_quiz_row_answer_image wpcw_quiz_row_answer_image_' + count);
			
			// Renumber the internal fields for the image row
			// Name field
			var imgAnsField = nextImgFieldRow.find('td input[type="text"]');
			var newName = imgAnsField.attr('name').replace(/\[\d+\]/g, '[' + count + ']');
			imgAnsField.attr('name', newName);
			
			// ID field
			var newID = imgAnsField.attr('id').replace(/_\d+$/g, '_' + count);
			imgAnsField.attr('id', newID);
			
			// Upload button
			var imgAnsFieldBtn = nextImgFieldRow.find('a.wpcw_insert_image');
			imgAnsFieldBtn.attr('data-target', newID);
			
			count++;
		});
	}
	
	
	// Check each question, and update hidden field to be the new order
	function reorderQuizQuestions()
	{
		var count = 1;		
		$j('.wpcw_dragable_question_holder .wpcw_question_hidden_order').each(function () 
		{
		    $j(this).val(count++);
		});
	}
	
	
	
	
	// Checks a list of units for quizes for sending back to server to
	// update ordering.
	function checkListOfUnitsForQuizzes(dataList, unitList)
	{
		// Now iterate over unit list to work out order of quizzes
		if (unitList.length > 0)
		{
			// Iterate over quizes to see if units have any quizzes.
			$j.each(unitList, function(index, unitid)
			{
				var unitQuizzes = $j('#' + unitid + ' .wpcw_dragable_quizzes_connected').sortable('toArray');
				if (unitQuizzes.length > 0) {
					dataList[unitid] = unitQuizzes;
				}
			});
		}
	}
	
	// Show or hide form aspects based on type.
	function toggleView_quiz_type() 
	{
		var quizType = $j('.wpcw_quiz_type_hide_pass:checked').val();
		$j('.wpcw_quiz_only').closest('tr').toggle(quizType != 'survey');
		$j('.wpcw_survey_only').closest('tr').toggle(quizType == 'survey');
		$j('.wpcw_quiz_block_only').closest('tr').toggle(quizType == 'quiz_block');
		
		// Table cells that are quiz only.
		$j('.wpcw_quiz_only_td').toggle(quizType != 'survey');
		
		// Hide tabs that are quiz only.
		$j('.wpcw_quiz_only_tab').toggle(quizType != 'survey');
	}
	
	// Has the quiz type field been clicked?
	$j('.wpcw_quiz_type_hide_pass').click(function(){
		toggleView_quiz_type();
	});
	
	
		
	// Save Reordered Units
	$j('#wpcw_dragable_modules_save').click(function()
	{
		// Get a list of all modules and their current order.
        var moduleList = $j('.wpcw_dragable_modules').sortable('toArray');
        
        // Units and quizzes that are unassigned
        var unassignedUnits = $j('#wpcw_unassigned_units ol').sortable('toArray');        
        var unassignedQuizzes = $j('#wpcw_unassigned_quizzes ol').sortable('toArray');
                
        // Initialise AJAX message
        var data = {
        		action: 		'wpcw_handle_unit_ordering_saving',
        		moduleList: 	moduleList,
        		unassunits:		unassignedUnits,
        		unassquizzes:	unassignedQuizzes,
        		order_nonce:	wpcw_js_consts_adm.order_nonce
        	};        
        
        // Check unassignedUnits for quiz changes.
        checkListOfUnitsForQuizzes(data, unassignedUnits);        
        
        // Handle module saving
        if (moduleList.length > 0) 
        {
        	// Iterate over each module
        	$j.each(moduleList, function(index, moduleid) 
        	{
        		// Now iterate over moduleList of units to work out what order they are in.
        		var moduleListUnits = $j('#' + moduleid + ' .wpcw_dragable_units_connected').sortable('toArray');
        		
        		// Create sublist of module ID => list of units in their respective orders.
        		data[moduleid] = moduleListUnits;
        		
        		checkListOfUnitsForQuizzes(data, moduleListUnits);        		
        	});
        }
                
        $j('#wpcw_sticky_bar_status').text('Saving changes...');
        $j('#wpcw_sticky_bar').attr('class', 'saving');
        
    	jQuery.post(ajaxurl, data, function(response) {
    		$j('#wpcw_sticky_bar_status').text('New ordering saved successfully.');
    		$j('#wpcw_sticky_bar').attr('class', 'done').delay(1000).slideUp('slow');
    	});
    	
    	return false;
    });    
	
	// Show Stickybar - Units Changed
	function showUnitsChanged()
	{
		if ($j('#wpcw_sticky_bar').is(":visible")) {
			return;
		} 		
		
		var textOrig = $j('#wpcw_sticky_bar_status').attr('title');
		$j('#wpcw_sticky_bar_status').text(textOrig);
		$j('#wpcw_sticky_bar').attr('class', 'ready').slideDown('slow');
	}
	
	// Show Stickybar - Quiz Grade Changed
	function showQuizGradesChanged()
	{
		if ($j('#wpcw_sticky_bar').is(":visible")) {
			return;
		} 		
		
		var textOrig = $j('#wpcw_sticky_bar_status').attr('title');
		$j('#wpcw_sticky_bar_status').text(textOrig);
		$j('#wpcw_sticky_bar').attr('class', 'ready').slideDown('slow');
	}
	
	
	
	// Add a new question - multi
	$j('#wpcw_add_question_multi').click(function(e) {
		cloneQuizForm('#wpcw_quiz_details_new_multi', "_new_multi");
	});
	
	// Add a new question - true/false
	$j('#wpcw_add_question_truefalse').click(function(e) {
		cloneQuizForm('#wpcw_quiz_details_new_tf', "_new_tf");
	});
	
	// Add a new question - open ended
	$j('#wpcw_add_question_open').click(function(e) {
		cloneQuizForm('#wpcw_quiz_details_new_open', "_new_open");
	});
	
	// Add a new question - upload
	$j('#wpcw_add_question_upload').click(function(e) {
		cloneQuizForm('#wpcw_quiz_details_new_upload', "_new_upload");
	});
	
	
	// Generic function that does the template quiz clone.
	function cloneQuizForm(templateQuizForm, strToReplace)
	{
		// Focus the questions tab
		$j('#wpcw_tab_wpcw_section_break_quiz_questions').trigger('click');
		
		// Get the ID of the new question, and update the count.
		var newQCount = parseInt($j('#wpcw_question_template_count').text());
		$j('#wpcw_question_template_count').text(++newQCount);
		
		// Duplicate the template form for a new question, renaming everything to use a custom ID
		var newForm = $j(templateQuizForm).clone().outerHTML().replace(new RegExp(strToReplace, 'g'), "_new_question_" + newQCount);
				
		$j('.wpcw_dragable_question_holder').append($j(newForm).fadeIn());
		reorderQuizQuestions();
	}
	
	
	/**
	 * Add a new feedback message.
	 */
	$j('#wpcw_quiz_custom_feedback_add_new').click(function(e) 
	{
		// Get the ID of the new message, and update the count.
		var newQCount = parseInt($j('#wpcw_quiz_custom_feedback_add_new_count').text());
		$j('#wpcw_quiz_custom_feedback_add_new_count').text(++newQCount);
		
		// Duplicate the template message form for a new message, renaming everything to use a custom ID
		var newForm = $j('#wpcw_qcfm_sgl_wrapper_new_message').clone().outerHTML().replace(new RegExp("_new_message", 'g'), "_new_message_" + newQCount);
		
		// Show and Set focus for summary field.
		$j('#wpcw_quiz_custom_feedback_holder').prepend($j(newForm).fadeIn());		
		$j('#wpcw_qcfm_sgl_wrapper_new_message_' + newQCount).find('.wpcw_quiz_custom_feedback_hdr input').focus();
	});
	 
	 
	/**
	 * Delete a custom feedback message with a confirmation message. 
	 */
	$j('#wpcw_quiz_custom_feedback_holder').on('click', '.wpcw_delete_icon', function(e)
	{		
		e.preventDefault();
		if (confirm($j(this).attr('rel'))) 
		{		
			var parent = $j(this).closest('table.wpcw_quiz_custom_feedback_wrap_single').parent();
			
			// Add hidden field to form to mark this item for deletion. 
			$j('#wpcw_quiz_custom_feedback_deletion_holder').append('<input type="hidden" name="delete_' + parent.attr('id') + '" value="true" />');
			
			// Hide item from view.
			parent.fadeOut('slow', function() { $j(this).remove(); });
		}
	});

	
	
	// Delete a question with confirmation message.
	$j('.wpcw_dragable_question_holder').on('click', '.wpcw_delete_icon', function(e)
	{		
		e.preventDefault();
		if (confirm($j(this).attr('rel'))) 
		{		
			var parent = $j(this).closest('li');
			
			// Add hidden field to form to mark this item for deletion. 
			$j('#wpcw_quiz_details_modify').append('<input type="hidden" name="delete_' + parent.attr('id') + '" value="true" />');
			
			// Hide item from view.
			parent.fadeOut('slow', function() { $j(this).remove(); });
		}
	});
	
	// Delete a quiz/question with confirmation message.
	$j('.wpcw_action_link_delete_quiz, .wpcw_action_link_delete_question').click(function(e) 
	{		
		return confirm($j(this).attr('rel')); 
	});
	
	
	// Get the outer HTML for an element
	jQuery.fn.outerHTML = function(s) {
	    return s
	        ? this.before(s).remove()
	        : jQuery("<p>").append(this.eq(0).clone()).html();
	};
	
	// Floating menu
	var name = ".wpcw_floating_menu";
	if ($j(name) && $j(name).css("top"))
	{
		menuYloc = parseInt($j(name).css("top").substring(0,$j(name).css("top").indexOf("px")));  
		$j(window).scroll(function () {  
	        var offset = menuYloc+$j(document).scrollTop()+"px";  
	        $j(name).animate({top:offset},{duration:200,queue:false});  
	    });   
	}
	
	
	// Show or hide certificate aspects based on type.
	function toggleView_certs_type() 
	{
		var certType = $j('.wpcw_cert_signature_type:checked').val();
		$j('.wpcw_cert_signature_type_text').closest('tr').toggle(certType == 'text');
		$j('.wpcw_cert_signature_type_image').closest('tr').toggle(certType != 'text');		
	}
	
	function toggleView_certs_logo() 
	{
		var certLogo = $j('.wpcw_cert_logo_enabled:checked').val();
		$j('.wpcw_cert_logo_url').closest('tr').toggle(certLogo == 'cert_logo');		
	}
	
	function toggleView_certs_bg_img() 
	{
		var bgCustom = $j('.wpcw_cert_background_type:checked').val();
		$j('.wpcw_cert_background_custom_url').closest('tr').toggle(bgCustom == 'use_custom');		
	}
	
	// Has the certificate type been changed?
	$j('.wpcw_cert_signature_type').click(function(){
		toggleView_certs_type();
	});
	
	// Has the certificate logo been enabled?
	$j('.wpcw_cert_logo_enabled').click(function(){
		toggleView_certs_logo();
	});
	
	// Has the custom certificate bg been enabled?
	$j('.wpcw_cert_background_type').click(function(){
		toggleView_certs_bg_img();
	});
	
	// Course and Quiz Tabs
	$j('.wpcw_tab_wrapper .wpcw_tab').click(function(e)
	{
		// Remove all active tabs.
		$j('.wpcw_tab_wrapper .wpcw_tab').removeClass('wpcw_tab_active');
		$j('.wpcw_tab_wrapper .form-table').removeClass('wpcw_tab_content_active');
		
		// Make the selected tab active
		$j(this).addClass('wpcw_tab_active');
		$j('.form-table#' + $j(this).attr('data-tab')).addClass('wpcw_tab_content_active');
		
		e.preventDefault();
	});
	
	
	// Quiz Questions - Instructor has changed grades
	$j('.wpcw_tbl_progress_quiz_answers_grade').change(function(e) {
		showQuizGradesChanged();
	});
	
	// Save button clicked on grading popup bar.
	$j('#wpcw_tbl_progress_quiz_grading_updated').click(function(e)
	{
		e.preventDefault();
		$j('#wpcw_tbl_progress_quiz_grading_form').submit();
	});
	
	// Grade for open-ended questions - click to edit
	$j('.wpcw_grade_already_graded a').click(function(e)
	{
		e.preventDefault();
		$j(this).closest('.wpcw_grade_view').hide();
		$j(this).closest('td').find('.wpcw_tbl_progress_quiz_answers_grade').show();
	});
	
	// Allow admin to choose when a quiz can be re-graded.
	$j('.wpcw_user_progress_failed_next_action label').click(function(e)
	{
		$j('.wpcw_user_progress_failed_reason').toggle($j(this).find('.wpcw_next_action_retake_quiz').is(':checked'));
	});
	
	
	// Question Edit - Multi - Allow random answers
	$j('#wpcw_section_break_quiz_questions').on('click', '.wpcw_quiz_details_randomize_answers .wpcw_quiz_details_enable', function(e)
	{
		console.log($j(this).is(':checked'));
		$j(this).closest('.wpcw_quiz_details_randomize_answers').find('.wpcw_quiz_details_count_wrap').toggle($j(this).is(':checked'));
	});
	
	
	
	// Function that shows the upload dialog box.
	// Based on http://mikejolley.com/2012/12/using-the-new-wordpress-3-5-media-uploader-in-plugins/
	var file_frame;
	var targetHolder; // Needed here as a global variable, otherwise dialog doesn't use target properly.
	function triggerUploadDialog(sender)
	{
		// If the media frame already exists, reopen it.
		if (file_frame) {
			file_frame.open();
			return;
		}

	    // Create the media frame.
		file_frame = wp.media.frames.file_frame = wp.media(
		{
			title: $j(sender).data('uploader_title'),
			button: {
				text: $j(sender).data('uploader_btn_text'),
			},
			multiple: false  // Not allowing multiple files.
	    });

	    // When an image is selected, run a callback.
		file_frame.on('select', function() 
		{
			// Fetch image and set to URL of the text field.
			attachment = file_frame.state().get('selection').first().toJSON();
			$j(targetHolder).val(attachment.url);
	    });

		// Finally, open the modal
		file_frame.open();
	}
	
	// Image uploader for quiz questions - when adding an image for a question	 
	$j('#wpcw_section_break_quiz_questions').on("click", ".wpcw_insert_image", function(e)  
	{
		e.preventDefault();
		
		// Use ID in data field to locate associated URL field.
		targetHolder = '#' + $j(this).data('target');
		triggerUploadDialog($j(this));
	});
	
	// Image Uploader - Certificate Config
	$j('#wpcw_form_settings_certificates').on("click", ".wpcw_insert_image", function(e)
	{
	    e.preventDefault();
	    
	    // Use ID in data field to locate associated URL field.
		targetHolder = '#' + $j(this).data('target');
		triggerUploadDialog($j(this));
	});
	
	// Show Answers - sub-checklist items - Show the right ones by default.
	function toggleView_quiz_showAnswers()
	{
		// Hide all, only show the ones for the selected item.		
		$j('.wpcw_quiz_details_modify_quiz_show_answers_tr .subelement_wrapper_all').each(function()
		{
			// Slide up something where the parent is not selected.
			if ($j(this).closest('.radio_item').find('input[name="quiz_show_answers"]').is(':checked')) {
				$j(this).slideDown('fast');
			}  else {
				$j(this).slideUp('fast');
			}			
		});
	}
	
	
	// Show Answers - sub-checklist items - when radio option changes
	$j('.wpcw_quiz_details_modify_quiz_show_answers_tr input[name="quiz_show_answers"]').click(function(e)
	{
		toggleView_quiz_showAnswers();
	});


	// Toggle message to be shown when no show answers items are selected.
	function toggleView_quiz_showItemsNeededMessage()
	{
		var checkedItems = $j('.quiz_show_answers-show_answers .subelement_wrapper_all input[type="checkbox"]:checked');
		$j('.wpcw_msg_error_show_answers_none_selected').toggle(!(checkedItems.length > 0));
	}
	
	
	// Show Answers - check what checkboxes are selected when something is clicked on.
	$j('.quiz_show_answers-show_answers input[type="checkbox"]').click(function(e)
	{
		toggleView_quiz_showItemsNeededMessage();
	});
	
	// Determine if we're showing explanation fields, and tidy up UI accordingly.
	function toggleView_quiz_showExplanationFields()
	{
		// The show answers field is ticked AND the show explanation checkbox is ticked.
		var showExplanationFields = $j('.quiz_show_answers-show_answers .show_answers_settings_show_explanation').is(':checked') && 
									$j('.quiz_show_answers-show_answers .wpcw_quiz_show_answers').is(':checked');
	
		// Show or hide the explanation fields.
		$j('.wpcw_quiz_details_question_explanation').closest('tr').toggle(showExplanationFields);
		
		// Show or hide the info field for no answers
		$j('.wpcw_msg_error_no_answers_selected').toggle($j('.quiz_show_answers-no_answers .wpcw_quiz_show_answers').is(':checked'));
	}
	
	// Handle if we're showing explanation fields based on if the show explanation option is clicked on.
	$j('.wpcw_quiz_details_modify_quiz_show_answers_tr input[type="radio"], .wpcw_quiz_details_modify_quiz_show_answers_tr input[type="checkbox"]').click(function(e)
	{
		toggleView_quiz_showExplanationFields();
	});
	
	
	// Use Paging - sub-checklist items - Show the right ones by default.
	function toggleView_quiz_usePaging()
	{
		// Hide all, only show the ones for the selected item.		
		$j('.wpcw_quiz_details_modify_quiz_paginate_questions_tr .subelement_wrapper_all').each(function()
		{
			// Slide up something where the parent is not selected.
			if ($j(this).closest('.radio_item').find('input[name="quiz_paginate_questions"]').is(':checked')) {
				$j(this).slideDown('fast');
			}  else {
				$j(this).slideUp('fast');
			}			
		});
	}
	
	// Use Paging - sub-checklist items - when radio option changes
	$j('.wpcw_quiz_details_modify_quiz_paginate_questions_tr input[name="quiz_paginate_questions"]').click(function(e)
	{
		toggleView_quiz_usePaging();
	});
	
	
	// Use Paging - sub-checklist items - Show the right ones by default.
	function toggleView_quiz_useTimer()
	{
		var timerEnabled = $j('.wpcw_quiz_details_modify_quiz_timer_mode_tr .quiz_timer_mode-use_timer input[type="radio"]').is(':checked');
		$j('.wpcw_quiz_timer_mode_active_only').closest('tr').toggle(timerEnabled);
	}
	
	// Use Timer - show time field - when radio option changes
	$j('.wpcw_quiz_details_modify_quiz_timer_mode_tr input[name="quiz_timer_mode"]').click(function(e)
	{
		toggleView_quiz_useTimer();
	});
	
	

	/**
	 * Improves Thickbox for dialogs to ensure that they use up to 1200px of the screen
	 * and use all available vertical space.
	 */
	jQuery(function($)
	{
	    tb_position = function() {
	        var tbWindow = $('#TB_window');
	        var width = $(window).width();
	        var H = $(window).height();
	        var W = ( 1200 < width ) ? 1200 : width;

	        if ( tbWindow.size() ) {
	            tbWindow.width( W - 50 ).height( H - 45 );
	            $('#TB_ajaxContent').width( W - 80 ).height( H - 92 );	            
	            $('#TB_iframeContent').width( W - 50 ).height( H - 75 );
	            tbWindow.css({'margin-left': '-' + parseInt((( W - 50 ) / 2),10) + 'px'});
	            if ( typeof document.body.style.maxWidth != 'undefined' )
	                tbWindow.css({'top':'20px','margin-top':'0'});
	            $('#TB_title').css({'background-color':'#fff','color':'#cfcfcf'});
	        };

	        return $('a.thickbox').each( function() {
	            var href = $(this).attr('href');
	            if ( ! href ) return;
	            href = href.replace(/&width=[0-9]+/g, '');
	            href = href.replace(/&height=[0-9]+/g, '');
	            $(this).attr( 'href', href + '&width=' + ( W - 80 ) + '&height=' + ( H - 85 ) );
	        });
	    };

	    jQuery('a.thickbox').click(function(){
	        if ( typeof tinyMCE != 'undefined' &&  tinyMCE.activeEditor ) {
	            tinyMCE.get('content').focus();
	            tinyMCE.activeEditor.windowManager.bookmark = tinyMCE.activeEditor.selection.getBookmark('simple');
	        }
	    });

	    $(window).resize( function() { tb_position() } );
	});
	
	
	/**
	 * Question Pool Checkboxes - toggle all/none.
	 */
	$j('#wpcw_tbl_question_pool th.wpcw_select_cb input[type="checkbox"]').click(function(e)
	{
		var checkBoxes = $j('#wpcw_tbl_question_pool .wpcw_select_cb input[type="checkbox"]').not($j(this));
		checkBoxes.prop("checked", $j(this).prop('checked'));
	});
	
	/**
	 * Question Pool Thickbox - Handler
	 */
	function wpcw_js_questionPool_refreshAJAXPage(pagenumstr, searchstr, filterstr)
	{
		var data = {
        		action: 		'wpcw_handle_tb_action_question_pool',
        		pagenum: 		pagenumstr,
        		s: 				searchstr,
        		filter: 		filterstr
        	};
        
		$j('#wpcw_tb_question_pool_inner .wpcw_loader').show();
        jQuery.post(ajaxurl, data, function(response)
        {
        	$j('#wpcw_tb_question_pool_inner').html(response);
    	});
	}
	
	
	/**
	 * Question Pool Thickbox - Handle question paging.
	 */
	$j('#wpcw_tb_question_pool_inner').on('click', '.wpbs_paging a', function(e)
	{
		e.preventDefault();
		
		// Ensure search overrides the filtering.
		var searchStr = $j('#wpcw_tb_question_pool_inner #wpcw_questions_search_input').val();
		var filterStr = null;
		if (searchStr.length <= 0)
		{
			filterStr = $j('#wpcw_tb_question_pool_inner .wpcw_questions_tag_filter').val();
		}
		
		wpcw_js_questionPool_refreshAJAXPage($j(this).attr('data-pagenum'), searchStr, '');
	});
	
	/**
	 * Question Pool Thickbox - Handle tag filtering.
	 */
	$j('#wpcw_tb_question_pool_inner').on('click', '.wpcw_questions_tag_filter_wrap input[type="submit"]', function(e)
	{	
		e.preventDefault();
		wpcw_js_questionPool_refreshAJAXPage(1, '', $j('#wpcw_tb_question_pool_inner .wpcw_questions_tag_filter').val());
		
	});
	
	/**
	 * Question Pool Thickbox - Handle search box
	 */
	$j('#wpcw_tb_question_pool_inner').on('click', '#wpcw_questions_search_box input[type="submit"]', function(e)
	{		
		e.preventDefault();
		wpcw_js_questionPool_refreshAJAXPage(1, $j('#wpcw_tb_question_pool_inner #wpcw_questions_search_input').val(), '');
	});
	
	/**
	 * Question Pool Thickbox - Handle reset click
	 */
	$j('#wpcw_tb_question_pool_inner').on('click', '.wpcw_search_count a', function(e)
	{		
		e.preventDefault();
		wpcw_js_questionPool_refreshAJAXPage(1, '', '');
	});
	
	/**
	 * Question Pool Thickbox - Handle 'Add' click
	 */
	$j('#wpcw_tb_question_pool_inner').on('click', '.wpcw_action_link_list a.wpcw_tb_action_add', function(e)
	{		
		e.preventDefault();
		
		// Get question number
		var question_number = $j(this).attr('data-questionnum');
		var parentItem = $j(this).closest('tr');
		parentItem.find('.wpcw_action_status_added').hide();
		
		// Change button status when added.
		$j(this).addClass('button-secondary').removeClass('button-primary');
		
		// See if the question already exists?
		if($j(".wpcw_dragable_question_holder #wpcw_quiz_details_" + question_number).length != 0)
		{
			// Yep - the question exists already. 
			alert(wpcw_js_consts_adm.msg_question_duplicate);
			
			// Show added cue
			parentItem.find('.wpcw_action_status_added').fadeIn();
			
			return;
		}
		
		
		var data = {
        		action: 		'wpcw_handle_tb_action_add_question',
        		questionnum: 	question_number
        	};
        
		// Show visual cues that something is happening.
		$j('#wpcw_tb_question_pool_inner .wpcw_loader').show();
		
		
        jQuery.post(ajaxurl, data, function(response)
        {
        	// Update cues
        	$j('#wpcw_tb_question_pool_inner .wpcw_loader').hide();
        	parentItem.find('.wpcw_action_status_added').fadeIn();
        	
        	// Add new item as jQuery object
        	$j('.wpcw_dragable_question_holder').append($j(response).fadeIn());
        	
        	// We need to re-do the count as we've added a new question
        	reorderQuizQuestions();
    	});
	});
	
	
	
	/**
	 * Random Questions - Insert new rule 
	 */
	$j('#wpcw_tb_random_question_inner_insert').click(function(e)
	{
		// Not preventing default, we want the page to jump to the new question.
		//e.preventDefault();
				
		var arrayOfTags = {};
		var tagCount = 0;
		var tagCountValid = 0;
		var htmlForHumans = $j('<div>');
		
		//wpcw_js_consts_adm.name_tag_whole_pool
		// Get the human HTML element for showing what's been selected
		var humanHTMLTemplate = $j('#wpcw_quiz_details_new_random_selection .wpcw_quiz_row_question_info_blank').html();
		
		
		// Using the whole pool.
		if ('whole_pool' == $j('#wpcw_tb_random_question_inner input[type="radio"]:checked').val())
		{
			var quCount = $j('#wpcw_tb_option_wrap_whole_pool .wpcw_spinner').val();
			
			// Add whole pool to list (JSON bit)
			arrayOfTags['whole_pool'] = quCount;
			tagCountValid = tagCount = 1;
			
			// Add the human text, replacing the count and name of this item.
			var newText = $j(humanHTMLTemplate); 
			newText.find('.wpcw_name').html(wpcw_js_consts_adm.name_tag_whole_pool);
			newText.find('.wpcw_count').html(quCount);
			
			// Need to keep wrapping li, hence outerHTML().
			htmlForHumans.append(newText.outerHTML());
		}
		
		// Got a selection of tags
		else
		{
			// Add each selection to the list
			$j('.wpcw_tb_option_wrap_question_tags_row').each(function()
			{
				var tagID   = $j(this).find('.wpcw_tb_option_tag_select').val();
				var quCount = $j(this).find('.wpcw_spinner').val();
				
				tagCount++;
				
				// Tag for this line hasn't been selected.
				if (0 == tagID)
				{
					// There's no selected tag, so can't stop until one has been selected.
					$j(this).addClass('wpcw_quiz_missing');
				}
				
				// Got a valid tag
				else 
				{
					// Clear errors
					$j(this).removeClass('wpcw_quiz_missing');
					tagCountValid++;
					
					// Create JSON version of selection
					arrayOfTags['tag_' + tagID] = quCount;
					
					
					// Add the human text, replacing the count and name of this tag.
					var newText = $j(humanHTMLTemplate); 
					newText.find('.wpcw_name').html($j(this).find('option:selected').attr('data-content-two'));
					newText.find('.wpcw_count').html(quCount);
					
					// Need to keep wrapping li, hence outerHTML().
					htmlForHumans.append(newText.outerHTML());
				}
			});
			
		}
		
		// Got valid tags if tag count = valid count, so insert and update.
		if (tagCountValid == tagCount)
		{
			// Get the ID for a new question, and update the count.
			var newQCount = parseInt($j('#wpcw_question_template_count').text());
			$j('#wpcw_question_template_count').text(++newQCount);
			
			var templateQuizForm = '#wpcw_quiz_details_new_random_selection';
			var strToReplace    = "_new_random_selection";
			
			// Duplicate the template form for a new question, renaming everything to use a custom ID
			var newForm = $j(templateQuizForm).clone().outerHTML().replace(new RegExp(strToReplace, 'g'), "_new_question_" + newQCount);			
			var newFormObj = $j(newForm);
			
			// Show the human list
			newFormObj.find('ul.wpcw_quiz_row_question_info').html(htmlForHumans.html());
			
			// Turn the list of entries into JSON for saving to the database.
			newFormObj.find('.wpcw_quiz_row_question_list').val(JSON.stringify(arrayOfTags));
			
			// Show the form
			$j('.wpcw_dragable_question_holder').append(newFormObj.fadeIn());
			reorderQuizQuestions();
									
			// Close the dialog box.
			tb_remove();
		}
	});
	
	/**
	 * Set spinner Max - Whole Pool - load from attribute to set the max
	 */
	// 
	$j('#wpcw_tb_option_wrap_whole_pool .wpcw_spinner').spinner(
	{
		min: 1,
		max: parseInt($j('#wpcw_tb_option_wrap_whole_pool .wpcw_spinner').attr('data-wpcw-max'), 10)
	});
	
	// Enable the first spinner for the tags
	$j('#wpcw_tb_option_wrap_question_tags_list .wpcw_tb_option_wrap_question_tags_row .wpcw_spinner').spinner({
		min: 1
	});
	
	/* 
	 * Set spinner max - when dropdown changes.
	 */
	$j('#wpcw_tb_option_wrap_question_tags_list').on('change', '.wpcw_tb_option_tag_select', function(e)
	{
		var parentRow = $j(this).closest('.wpcw_tb_option_wrap_question_tags_row');
		
		// If we select a valid tag, remove the error wrapper class.
		if ($j(this).val() > 0) {
			parentRow.removeClass('wpcw_quiz_missing');
		}		
		
		parentRow.find('.wpcw_spinner').spinner("option" , "max", $j(this).find('option:selected').attr('data-content'));
	});
	
	
	
	/**
	 * Random Questions - Add New 
	 */
	$j('#wpcw_tb_option_wrap_question_tags_add').click(function(e)
	{
		e.preventDefault();
				
		// Clone the most recent version
		var latestItem = $j('#wpcw_tb_option_wrap_question_tags_list .wpcw_tb_option_wrap_question_tags_row:last-child').clone();
		
		// Show the deletion icon
		latestItem.find('.wpcw_delete_icon').css('display', 'inline-block');
		
		// Remove the error class if there is one.
		latestItem.removeClass('wpcw_quiz_missing');
		
		// Remove the extra span wrapper added by the first spinner, which doesn't work in clone. So we remove the HTML
		// and live add the spinner back again.
		latestItem.find('.wpcw_spinner').unwrap();
		
		// Enable the spinner
		latestItem.find('.wpcw_spinner').spinner({
			min: 1
		});
		
		// Fade in the new element.
		$j('#wpcw_tb_option_wrap_question_tags_list').append(latestItem.fadeIn());
	});
	
	
	/**
	 * Random Questions - Delete Row 
	 */
	$j('#wpcw_tb_option_wrap_question_tags_list').on('click', '.wpcw_delete_icon', function(e)
	{
		e.preventDefault();
		$j(this).closest('.wpcw_tb_option_wrap_question_tags_row').fadeOut().remove();
	});
	
	/** 
	 * Random Question - Radio Buttons - Change UI when selected
	 */
	$j('#wpcw_tb_random_question_inner input[type="radio"]').click(function(e)
	{
		// Remove active class from all
		$j('#wpcw_tb_random_question_inner .wpcw_tb_option_wrap').removeClass('wpcw_tb_option_wrap_active');
		
		// Add back to this parent
		if ($j(this).is(':checked')) {
			$j(this).closest('.wpcw_tb_option_wrap').addClass('wpcw_tb_option_wrap_active');
		}
		
	});
	
	/**
	 * Question Compact/Expand
	 */
	$j('.wpcw_quiz_tool_compact .wpcw_quiz_tool_compact_compact').click(function(e)
	{
		// Toggle visibility of link
		$j(this).hide();
		$j('.wpcw_quiz_tool_compact .wpcw_quiz_tool_compact_expand').show();
		
		// Hide the details for the quiz contents
		$j('.wpcw_quiz_details_questions_wrap').addClass('wpcw_quiz_details_questions_wrap_compact');
	});
	
	$j('.wpcw_quiz_tool_compact .wpcw_quiz_tool_compact_expand').click(function(e)
	{
		// Toggle visibility of link
		$j(this).hide();
		$j('.wpcw_quiz_tool_compact .wpcw_quiz_tool_compact_compact').show();
		
		// Show the details for the quiz contents
		$j('.wpcw_quiz_details_questions_wrap').removeClass('wpcw_quiz_details_questions_wrap_compact');
	});
	
	/**
	 * Question Pool - Bulk Action Chooser - Select
	 */
	$j('#wpcw_tbl_question_pool_bulk_actions_chooser').change(function(e)
	{
		// Hide labels/selects regardless.
		$j('#wpcw_tbl_question_pool_bulk_actions .wpcw_bulk_action_label').hide();
		$j('#wpcw_tbl_question_pool_bulk_actions .wpcw_bulk_action_select_tag').hide();
		
		// Show the one that matches the selection in the chooser.
		$j('#wpcw_tbl_question_pool_bulk_actions .wpcw_bulk_action_' + $j(this).val()).fadeIn('fast');
	});
	
	/**
	 * Question Pool - Bulk Action - Submit Chosen - Check we have something to do.
	 */
	$j('#wpcw_tbl_question_pool_bulk_actions input[type="submit"]').click(function(e)
	{
		var gotBlockingErrors = false;
		
		// Show a message if there are no selected items
		var checkedItems = $j('#wpcw_tbl_question_pool .wpcw_select_cb input[type="checkbox"]:checked');
		$j('#wpcw_bulk_action_message_no_questions').toggle(checkedItems.length <= 0);
		if (checkedItems.length <= 0) {
			gotBlockingErrors = true;
		}
		
		// Show message - first tag not selected
		var selTagOne = $j('#wpcw_tbl_question_pool_bulk_actions .wpcw_bulk_action_select_tag_a').val();
		$j('#wpcw_bulk_action_message_no_tag_first').toggle(selTagOne.length == 0);
		if (selTagOne.length == 0) {
			gotBlockingErrors = true;
		}
		
		// Show message - 2nd tag not selected and we're replacing a tag
		var selTagTwo = $j('#wpcw_tbl_question_pool_bulk_actions .wpcw_bulk_action_select_tag_b').val();
		var invalidTagTwo = ($j('#wpcw_tbl_question_pool_bulk_actions_chooser').val() == 'replace_tag') && (selTagTwo.length == 0);
		$j('#wpcw_bulk_action_message_no_tag_second').toggle(invalidTagTwo);
		if (invalidTagTwo) {
			gotBlockingErrors = true;
		}
		
		// If there are no selections, then prevent the submission.
		if (gotBlockingErrors) {
			e.preventDefault();
		}				
	});
	
	
	/**
	 * Handle the error checking before submitting. This checks the fields
	 * in questions and in custom messages for any errors before submitting.
	 */
	$j('#wpcw_quiz_details_modify input[type="submit"]').click(function(e)
	{
		// Work out if we're in survey mode
		var surveyMode = ('survey' == $j('.wpcw_quiz_type_hide_pass:checked').val());
		
		var errorCount_feedback = 0;
		var errorCount_questions = 0;
		
		// 1) Check all feedback items for any missing details.
		if (!surveyMode)
		{
			$j('#wpcw_quiz_custom_feedback_holder .wpcw_quiz_custom_feedback_wrap_single').each(function(row)
			{
				var fld_summary = $j(this).find('.wpcw_qcfm_sgl_summary');
				var fld_tag 	= $j(this).find('.wpcw_qcfm_sgl_tag');
				var fld_message = $j(this).find('.wpcw_qcfm_sgl_message');
				
				// Check for empty summary
				var fld_summary_invalid = (fld_summary.val().length <= 0);
				if (fld_summary_invalid) { 
					errorCount_feedback++;
				}
				fld_summary.closest('tr').toggleClass('wpcw_quiz_custom_feedback_row_error', fld_summary_invalid);
				
				// Check for not selecting a tag.
				var fld_tag_invalid = (fld_tag.find('option:selected').val().length <= 0);
				if (fld_tag_invalid) { 
					errorCount_feedback++;
				}
				fld_tag.closest('tr').toggleClass('wpcw_quiz_custom_feedback_row_error', fld_tag_invalid);
				
				// Check for not selecting a message
				var fld_message_invalid = (fld_message.val().length <= 0);
				if (fld_message_invalid) { 
					errorCount_feedback++; 
				}
				fld_message.closest('tr').toggleClass('wpcw_quiz_custom_feedback_row_error', fld_message_invalid);
			});
		}
		
		
		// 2) Check all of the questions for any missing details.
		$j('.wpcw_dragable_question_holder li').each(function(row)
		{
			var fld_question = $j(this).find('tr.wpcw_quiz_row_question textarea');
			
			// Check for empty question
			var fld_question_invalid = (fld_question.val().length <= 0);
			if (fld_question_invalid) { 
				errorCount_questions++;
			}
			fld_question.closest('tr').toggleClass('wpcw_quiz_missing', fld_question_invalid);
			
			if (!surveyMode)
			{
				// Check multis - ensure there's a selected answer. If not, create an error.
				if ($j(this).hasClass('wpcw_question_type_multi'))
				{
					// Extract the name for the answer selection in each group
					var answerRadioName = $j(this).find('.wpcw_quiz_row_answer input[type="radio"]').attr('name');
					var fld_no_answer = false;
					
					// See if any of the answers are missing selections or not.
					if (!$j('#' + $j(this).attr('id') + ' input:radio[name="' + answerRadioName + '"]').is(":checked")) {
						errorCount_questions++;
						fld_no_answer = true;
					}
					
					$j(this).find('tr.wpcw_quiz_row_answer, tr.wpcw_quiz_row_answer_image').toggleClass('wpcw_quiz_missing', fld_no_answer);
				} 
						
				// Check true/false - ensure there's a selected answer. If not, create an error.
				else if ($j(this).hasClass('wpcw_question_type_truefalse'))
				{
					var answerRadioName = $j(this).find('.wpcw_quiz_details_truefalse_selection input[type="radio"]').attr('name');				
					var fld_no_answer = false;
					
					// See if true or false has been selected are missing selections or not.
					if (!$j('#' + $j(this).attr('id') + ' input:radio[name="' + answerRadioName + '"]').is(":checked")) {
						errorCount_questions++;
						fld_no_answer = true;
					}
					
					$j(this).find('tr.wpcw_quiz_details_truefalse_answer').toggleClass('wpcw_quiz_missing', fld_no_answer);
				}
			} // end of survey mode check
		});
		
		
		// 3) Show the tab as having an error if there are any issues with the feedback or question details.
		$j('#wpcw_tab_wpcw_section_break_quiz_custom_feedback').toggleClass('wpcw_tab_has_error', (errorCount_feedback != 0));
		$j('#wpcw_tab_wpcw_section_break_quiz_questions').toggleClass('wpcw_tab_has_error', (errorCount_questions != 0));
		
		
		// 4) Show the error above the tabs if there is something missing.
		if (errorCount_feedback == 0 && errorCount_questions == 0) {
			$j('.wpcw_section_error_within_tabs').hide();
		} else {
			$j('.wpcw_section_error_within_tabs').fadeIn();
		}
		
		// 5) Prevent submission if there are any errors.
		if (errorCount_feedback != 0 || errorCount_questions != 0) {
			e.preventDefault();
		}
	});
	
	
	
	// ### On load
	toggleView_quiz_showAnswers();
	toggleView_quiz_usePaging();
	toggleView_quiz_showItemsNeededMessage();
	toggleView_quiz_showExplanationFields();	
	toggleView_quiz_type(); 
	toggleView_quiz_useTimer();
	toggleView_certs_type(); 
	toggleView_certs_logo();
	toggleView_certs_bg_img();
		
	// Show selected tab.
	$j('.wpcw_tab_wrapper a.wpcw_tab_active').trigger('click');
});