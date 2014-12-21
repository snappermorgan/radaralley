var $j = jQuery.noConflict();
//$j(function()
jQuery(document).ready(function($j) 
{	
	// Marking a unit as completed
	$j('.wpcw_fe_progress_box_mark a').click(function() {
		
		var courseid = $j(this).attr('id');
		var data = {
				action: 		'wpcw_handle_unit_track_progress',
				id:				courseid,
				progress_nonce: wpcw_js_consts_fe.progress_nonce
			}; 
		
		$j(this).hide();
		$j(this).parent().find('.wpcw_loader').show();
		
		$j.post(wpcw_js_consts_fe.ajaxurl, data, function(response) {
			$j('#wpcw_fe_' + courseid).hide().html(response).fadeIn();
		});
		
		return false;
	});	
	
	
	// The quiz retake button.
	$j('#wpcw_fe_outer_wrap').on('click', '.wpcw_fe_quiz_retake a.fe_btn', function(e)
	{
		e.preventDefault();
		
		// Show loader and hide button
		$j(this).closest('.wpcw_fe_quiz_retake').find('.wpcw_loader').show();
		$j(this).hide();
		
		var wpcw_quizid = $j(this).attr('data-wpcw_quiz');
		var wpcw_unitid = $j(this).attr('data-wpcw_unit');
		
		// Trigger AJAX request to allow the user to retake the quiz.
		var data = {    	
				action 			: 'wpcw_handle_unit_quiz_retake_request',
				quizid 			: wpcw_quizid,
				unitid 			: wpcw_unitid, 
				progress_nonce	: wpcw_js_consts_fe.progress_nonce
			};
		
		jQuery.post(wpcw_js_consts_fe.ajaxurl, data, function(response)
        {
        	$j('#wpcw_fe_outer_wrap').html(response);
        	
        	// Re-do quiz data.
        	quizHandler_setup();
    	});
	});
	
	// The quiz timer begin button.
	$j('#wpcw_fe_outer_wrap').on('click', '#wpcw_fe_quiz_begin_quiz', function(e)
	{
		e.preventDefault();
		
		// Show loader and hide button
		$j(this).closest('.wpcw_fe_quiz_begin_quiz').find('.wpcw_loader').show();
		$j(this).hide();
		
		var wpcw_quizid = $j(this).attr('data-wpcw_quiz');
		var wpcw_unitid = $j(this).attr('data-wpcw_unit');
		
		// Trigger AJAX request to allow the user to retake the quiz.
		var data = {    	
				action 			: 'wpcw_handle_unit_quiz_timer_begin',
				quizid 			: wpcw_quizid,
				unitid 			: wpcw_unitid, 
				progress_nonce	: wpcw_js_consts_fe.progress_nonce
			};
		
		jQuery.post(wpcw_js_consts_fe.ajaxurl, data, function(response)
        {
        	$j('#wpcw_fe_outer_wrap').html(response);
        	
        	// Re-do quiz data.
        	quizHandler_setup();
    	});
		
	});
	
	
	// Paging - Upload Change - The button that allows a user to edit their choice of file that's been uploaded.
	$j('#wpcw_fe_outer_wrap').on('click', '.wpcw_fe_quiz_q_upload_change_file', function(e)
	{
		e.preventDefault();
		
		// Get wrapper for the changing file section.
		var changeWrap = $j(this).closest('.wpcw_fe_quiz_q_upload_change_file_wrap');
		
		// Get the field ID for the upload field, so we can create it dynamically
		var wpcw_fieldid = $j(this).attr('data-fieldid');
		
		// Hide this link, show the cancel link.
		$j(this).hide();
		changeWrap.find('.wpcw_fe_quiz_q_upload_change_file_cancel').show();
		
		// Now show the form.
		changeWrap.find('.wpcw_fe_quiz_q_upload_change_holder').html('<div class="wpcw_fe_quiz_q_upload_wrapper" id="' + wpcw_fieldid + '"><input type="file" name="' + wpcw_fieldid + '" ></div>');
	});
	
	
	
	
	// Paging - Upload Change - The button that allows a user to edit their choice of file that's been uploaded.
	$j('#wpcw_fe_outer_wrap').on('click', '.wpcw_fe_quiz_q_upload_change_file_cancel', function(e)
	{
		e.preventDefault();
		
		// Get wrapper for the changing file section.
		var changeWrap = $j(this).closest('.wpcw_fe_quiz_q_upload_change_file_wrap');
		
		// Hide this link, show the change link.
		$j(this).hide();
		changeWrap.find('.wpcw_fe_quiz_q_upload_change_file').show();
		
		// Now remove the form again.
		changeWrap.find('.wpcw_fe_quiz_q_upload_change_holder').html('');
	});
	
	
	
	// The previous button (next to the Next button)
	$j('#wpcw_fe_outer_wrap').on('click', '#fe_btn_quiz_previous', function(e)
	{
		e.preventDefault(); 
		quizHandler_navigateQuestion('previous');
	});
	
	
	// The answer later button.
	$j('#wpcw_fe_outer_wrap').on('click', '#wpcw_fe_quiz_answer_later', function(e)
	{
		e.preventDefault();
		quizHandler_navigateQuestion('next');
	});
	
	
	/**
	 * Function called when the timer reaches 0.
	 */
	function timerHandler_expiry()
	{				
		var quizForm = $j('.wpcw_fe_quiz_box_wrap form');
		
		// Update flag on form to show it's expired, then trigger submission.
		quizForm.attr('data-wpcw_expired', 'expired');		
		quizForm.submit();
	}
	
	
	/**
	 * Attempt to set up the timer handler based on how many minutes are remaining.
	 */
	function timerHandler_setup()
	{
		var timerHolder = $j('#wpcw_fe_timer_countdown');
		if (timerHolder.length > 0)
		{
			// How many seconds are left on the timer?
			var sLeft = parseInt(timerHolder.attr('data-time_left'));
			
			$j('#wpcw_fe_timer_countdown').countdown({
				// Labels with translation
				labels: ['y', 'm', 'w', 'd', wpcw_js_consts_fe.timer_units_hrs, wpcw_js_consts_fe.timer_units_mins, wpcw_js_consts_fe.timer_units_secs],				
				labels1: ['y', 'm', 'w', 'd', wpcw_js_consts_fe.timer_units_hrs, wpcw_js_consts_fe.timer_units_mins, wpcw_js_consts_fe.timer_units_secs],
				
				padZeroes: true, 
				until: '+' + sLeft + 's',
				format: 'MS',
				
				// Event for timer expiry
				onExpiry: timerHandler_expiry
			});
		}
		
		// Called when setting up AJAX form, so we don't need to call it
		// separately.
	}
	
	
	/**
	 * Remove any timers before doing an AJAX load to prevent errors.
	 */
	function timerHandler_cleanup()
	{
		$j('#wpcw_fe_timer_countdown').hide().countdown('destroy');
	}
	
	
	
	/**
	 * Skip a question or go to previous question.
	 */
	function quizHandler_navigateQuestion(direction)
	{		
		var quizForm = $j('.wpcw_fe_quiz_box_wrap form');
		
		// Show loader and hide buttons
		quizForm.find('.wpcw_loader').show();		
		quizForm.find('.wpcw_fe_quiz_submit input, a.fe_btn').hide();
		
		var wpcw_quizid = quizForm.attr('data-wpcw_quiz');
		var wpcw_unitid = quizForm.attr('data-wpcw_unit');
		
		// Trigger AJAX request to go to the previous item.
		var data = {    	
				action 			: 'wpcw_handle_unit_quiz_jump_question',
				quizid 			: wpcw_quizid,
				unitid 			: wpcw_unitid, 
				qu_direction	: direction, 
				progress_nonce	: wpcw_js_consts_fe.progress_nonce
			};
		
		timerHandler_cleanup();
		
		jQuery.post(wpcw_js_consts_fe.ajaxurl, data, function(response)
        {
        	$j('#wpcw_fe_outer_wrap').html(response);
        	
        	// Re-do quiz data.
        	quizHandler_setup();
    	});
	}
	
	
	
	
	
	// Function that's called when setting up the quiz form.
	function quizHandler_setup() 
	{
		var quizForm = $j('.wpcw_fe_quiz_box_wrap form');
		if (quizForm.length > 0)
		{
			// the ID of the quiz
			var quizid = quizForm.attr('id');
						
			// Progress bar details
			var bar = quizForm.find('.wpcw_fe_upload_progress .wpcw_progress_bar');
			var percent = quizForm.find('.wpcw_fe_upload_progress .wpcw_progress_percent');
						
			// Configure the AJAX request
			var configdata = {    	
				action 			: 'wpcw_handle_unit_quiz_response',
				id 				: quizid,
				timerexpired	: quizForm.attr('data-wpcw_expired'),
				progress_nonce	: wpcw_js_consts_fe.progress_nonce
			};
			
			// Configure form 
			var options = {
				target			: '.wpcw_fe_quiz_box_wrap#wpcw_fe_' + quizid,   	// Target to update on response
				//target			: '#wpcw_fe_outer_wrap',
				replaceTarget	: true,						// Replace completely, rather than just content.
				url				: wpcw_js_consts_fe.ajaxurl,		// PostURL
				data			: configdata,				// AJAX config details
				type 			: 'POST',	

				// Before submission - Handle validation here too.
				beforeSubmit : function(formData, jqForm, options) 
				{
					// Assume all fields are valid until checked, so remove error class.
					$j('.wpcw_fe_quiz_q_single').removeClass('wpcw_fe_quiz_q_error');
					
					// Has the timer expired
					var hasTimerExpired = 'expired' == quizForm.attr('data-wpcw_expired');
					
					// Ignore checking that fields are missing data if the timer has expired.
					if (!hasTimerExpired)
					{
						// formData is an array of objects representing the name and value of each field 
					    // that will be sent to the server. Check that each form entry has a value.
						// If a radio group is in the list, then it will not show here, so check for that
						// separately.
						var missingData = false;
						for (var i=0; i < formData.length; i++) 
						{ 
							// Flag question as having an error if empty						
					        if (!formData[i].value)
					        {				        	
					        	$j('#wpcw_fe_wrap_' + formData[i].name).addClass('wpcw_fe_quiz_q_error');
					        	missingData = true;				        	
					        }
						} // end for
						
						// Check for missing radio button selections
						$j('#' + quizid + ' .wpcw_fe_quiz_q_multi, #' + quizid + ' .wpcw_fe_quiz_q_truefalse').each(function()
						{
							if ($j(this).find('input:checked').length == 0) {
								$j(this).addClass('wpcw_fe_quiz_q_error');
								missingData = true;
							}
						});
	
						// Handle reporting the error, as missing some data.
						if (missingData)
					    {
				        	// Create the message area
				        	var quizFormParent = quizForm.closest('.wpcw_fe_quiz_box_wrap');
				        	var msgArea = $j('.wpcw_fe_progress_box_wrap .wpcw_fe_progress_box_error');
				        	if (msgArea.length == 0) 
				        	{
				        		// No error area, so add it with error message.
				        		$j('<div class="wpcw_fe_progress_box_wrap"><div class="wpcw_fe_progress_box wpcw_fe_progress_box_error">' + 
				        				wpcw_js_consts_fe.str_quiz_all_fields + 
				        			'</div></div>').insertBefore(quizFormParent);
				        	} 
				        	else {
				        		// Error area already exists, just update it with right error.
				        		msgArea.text(wpcw_js_consts_fe.str_quiz_all_fields);
				        	}
				        	
				        	// Scroll to the top of the quiz area to show error message.
					    	$j('html, body').animate({				
						         scrollTop: $j('.wpcw_fe_quiz_box_wrap').offset().top - 100
						     }, 200);
				            
				            return false; 
				        } // Something was missing. 
					} // end of check for expired timer.
										
					
					// Hide button, show that progress is happening.
					quizForm.find('.wpcw_fe_quiz_submit input').hide();
			    	quizForm.find('.wpcw_loader').show();
			    	
			    	// Hide any errors
			    	$j('.wpcw_fe_progress_box_wrap .wpcw_fe_progress_box_error').remove();
					
					var percentVal = '0%';
					bar.width(percentVal);
					percent.html(percentVal);
					
					// Disable timer before refreshing.
					timerHandler_cleanup();
					
					// Only show the progress bar if theres an upload field.
					if (quizForm.find('.wpcw_fe_quiz_q_upload_wrapper').length > 0) {
						$j('.wpcw_fe_upload_progress').show();
					}					
				},
				
				// Handle the upload progress
				uploadProgress : function(event, position, total, percentComplete)
				{					
					var percentVal = percentComplete + '%';
					bar.width(percentVal);
					percent.text(wpcw_js_consts_fe.str_uploading + ' ' + percentVal); // Show uploading message
				},

				// Called when the upload has completed.
				success : function() 
				{
					var percentVal = '100%';
					bar.width(percentVal);
					percent.text(wpcw_js_consts_fe.str_uploading + ' ' + percentVal); // Show uploading message
					
					// Scroll to the top of the quiz area.
			    	$j('html, body').animate({				
				         scrollTop: $j('.wpcw_fe_quiz_box_wrap').offset().top - 100
				     }, 200);
			    	
			    	// Re-attach ajax
			    	quizHandler_setup();
				}
				
				
			};
			
			// Set up the AJAX form request.
			$j('.wpcw_fe_quiz_box_wrap form').ajaxForm(options);
			
			// Set up timer again
			timerHandler_setup(); 
		}
	}
	quizHandler_setup(); // On load
	
	
    
	
	// Toggle visibility of modules in widget/shortcode
	$j('.wpcw_widget_progress .wpcw_fe_module').click(function(e)
	{
		e.preventDefault();
		var moduleID = $j(this).attr('id');
		
		// Show Mode
		if ($j(this).hasClass('wpcw_fe_module_toggle_hide')) {
			$j(this).find('.wpcw_fe_toggle').text('-');
			$j(this).addClass('wpcw_fe_module_toggle_show').removeClass('wpcw_fe_module_toggle_hide');
			$j(this).closest('.wpcw_widget_progress').find('.' + moduleID).show();
		} 		
		// Hide Mode
		else {
			$j(this).find('.wpcw_fe_toggle').text('+');
			$j(this).addClass('wpcw_fe_module_toggle_hide').removeClass('wpcw_fe_module_toggle_show');
			$j(this).closest('.wpcw_widget_progress').find('.' + moduleID).hide();			
		}
	});
	
	
	// Toggle visibility of course detail in course progress table.
	$j('.wpcw_fe_course_progress_course a').click(function(e)
	{
		e.preventDefault();
		var detailRowID = $j(this).data('toggle');
		
		// Show Mode
		if ($j(this).parent().hasClass('active')) {
			$j(this).parent().removeClass('active');
			$j(this).closest('.wpcw_fe_summary_course_progress').find('#' + detailRowID).fadeOut('fast');
		} 		
		// Hide Mode
		else {			
			$j(this).parent().addClass('active');
			$j(this).closest('.wpcw_fe_summary_course_progress').find('#' + detailRowID).fadeIn('fast');
		}
	});
	
	
	// Hide all modules (that need collapsing) on load
	$j('.wpcw_widget_progress .wpcw_fe_module_toggle_hide').each(function() {
		var moduleID = $j(this).attr('id');
		$j(this).closest('.wpcw_widget_progress').find('.' + moduleID).hide();
	});
	
	// Disable events on navigation buttons that are disabled.
	$j('a.fe_btn_navigation_disabled').click(function(e) {
		e.preventDefault();
	});
	
});