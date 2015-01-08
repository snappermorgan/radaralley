/*
 * This one customize wp admin page
 */
jQuery(function() {
	var visualEditorButton = jQuery('#dm_meta_visual_editor').find('.button.visual_editor');
	var savePostButton = jQuery('#save-post');
	var updateButton = jQuery('#publishing-action').find('[name="save"]');

	var handleVisualEditorPress = function() {
		visualEditorButton.bind('click', function(e){
			e.preventDefault();
			// Save draft
			jQuery(this).after('<input name="visualEditorHref" type="hidden" id="visual_editor_redirect" value="'+visualEditorButton.attr('href')+'">');
			savePostButton.click();
			// Update
			updateButton.click();
		});
	};

	handleVisualEditorPress();
});
