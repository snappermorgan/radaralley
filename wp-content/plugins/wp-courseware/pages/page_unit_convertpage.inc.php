<?php
/**
 * WP Courseware
 * 
 * Code relating to converting an existing post or page to a WP Courseware unit.
 */


/**
 * Convert page/post to a course unit 
 */
function WPCW_showPage_ConvertPage_load()
{
	$page = new PageBuilder(false);
	$page->showPageHeader(__('Convert Page/Post to Course Unit', 'wp_courseware'), '75%', WPCW_icon_getPageIconURL());
	
	// Future Feature - Check user can edit other people's pages - use edit_others_pages or custom capability.
	if (!current_user_can('manage_options')) {
		$page->showMessage(__('Sorry, but you are not allowed to edit this page/post.', 'wp_courseware'), true);
		$page->showPageFooter();
		return false;
	}
	
	// Check that post ID is valid
	$postID = WPCW_arrays_getValue($_GET, 'postid') + 0;
	$convertPost = get_post($postID);
	if (!$convertPost) {
		$page->showMessage(__('Sorry, but the specified page/post does not appear to exist.', 'wp_courseware'), true);
		$page->showPageFooter();
		return false;
	}
	
	// Check that post isn't already a course unit before trying change. 
	// This is where the conversion takes place.	
	if ('course_unit' != $convertPost->post_type)
	{
		// Confirm we want to do the conversion
		if (!isset($_GET['confirm']))
		{
			$message = sprintf(__('Are you sure you wish to convert the <em>%s</em> to a course unit?', 'wp_courseware'), $convertPost->post_type);
			$message .= '<br/><br/>';
			
			// Yes Button
			$message .= sprintf('<a href="%s&postid=%d&confirm=yes" class="button-primary">%s</a>', 
				admin_url('admin.php?page=WPCW_showPage_ConvertPage'), 
				$postID,
				__('Yes, convert it', 'wp_courseware')
			);
			
			// Cancel
			$message .= sprintf('&nbsp;&nbsp;<a href="%s&postid=%d&confirm=no" class="button-secondary">%s</a>', 
				admin_url('admin.php?page=WPCW_showPage_ConvertPage'), 
				$postID,
				__('No, don\'t convert it', 'wp_courseware')
			);
			
			
			$page->showMessage($message);
			$page->showPageFooter();
			return false;
		}
		
		
		// Handle the conversion confirmation
		else 
		{
			// Confirmed conversion
			if ($_GET['confirm'] == 'yes')
			{
				$postDetails 				= array();
  				$postDetails['ID'] 			= $postID;
  				$postDetails['post_type'] 	= 'course_unit';
  				
  				// Update the post into the database
  				wp_update_post($postDetails);
			}
			
			// Cancelled conversion
			if ($_GET['confirm'] != 'yes') 
			{
				$page->showMessage(__('Conversion to a course unit cancelled.', 'wp_courseware'), false);
				$page->showPageFooter();
				return false;
			}
		}
  		
	}
	
	// Check conversion happened
	$convertedPost = get_post($postID);
	if ('course_unit' == $convertedPost->post_type)
	{
		$page->showMessage(sprintf(__('The page/post was successfully converted to a course unit. You can <a href="%s">now edit the course unit</a>.', 'wp_courseware'),
			admin_url(sprintf('post.php?post=%d&action=edit', $postID))
		));
	}
	
	else {
		$page->showMessage(__('Unfortunately, there was an error trying to convert the page/post to a course unit. Perhaps you could try again?', 'wp_courseware'), true);
	}
	
	$page->showPageFooter();
}


?>