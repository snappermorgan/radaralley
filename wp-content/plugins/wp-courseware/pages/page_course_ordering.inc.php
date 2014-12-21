<?php
/**
 * WP Courseware
 * 
 * Functions relating to showing the course settings page where units, modules and quizzes can be re-ordered.
 */



/**
 * Page where the modules of a course can be ordered.
 */
function WPCW_showPage_CourseOrdering_load()
{
	$page = new PageBuilder(false);
	$page->showPageHeader(__('Order Course Modules &amp; Units', 'wp_courseware'), '75%', WPCW_icon_getPageIconURL());
	
	$courseDetails = false;
	$courseID = false;
	
	// Trying to edit a course	
	if (isset($_GET['course_id'])) 
	{
		$courseID 		= $_GET['course_id'] + 0;
		$courseDetails 	= WPCW_courses_getCourseDetails($courseID);
	}
	
	// Abort if course not found.
	if (!$courseDetails)
	{		
		$page->showMessage(__('Sorry, but that course could not be found.', 'wp_courseware'), true);
		$page->showPageFooter();
		return;
	}	
	
	// ###ÊGenerate URLs for editing
	$modifyURL_quiz   = admin_url('admin.php?page=WPCW_showPage_ModifyQuiz');
	$modifyURL_module = admin_url('admin.php?page=WPCW_showPage_ModifyModule');
	$modifyURL_unit   = admin_url('post.php?action=edit');
	
	// Title of course being editied
	printf('<div id="wpcw_page_course_title"><span>%s</span> %s</div>', __('Editing Course:', 'wp_courseware'), $courseDetails->course_title);
	
	// Overall wrapper
	printf('<div id="wpcw_dragable_wrapper">');
	
	printf('<div id="wpcw_unassigned_wrapper" class="wpcw_floating_menu">');
	
		
	
		// ### Show a list of units that are not currently assigned to a module		
		printf('<div id="wpcw_unassigned_units" class="wpcw_unassigned">');	
		printf('<div class="wpcw_unassigned_title">%s</div>', __('Unassigned Units', 'wp_courseware'));
		
			printf('<ol class="wpcw_dragable_units_connected">');
			
			// Render each unit so that it can be dragged to a module. Still render <ol> list
			// even if there are no units to show so that we can drag units into unassociated list.
			$units = WPCW_units_getListOfUnits(0);
			if ($units)
			{
				foreach ($units as $unassUnit)
				{
					// Has unit got any existing quizzes?
					$existingQuiz = false;
					$quizObj = WPCW_quizzes_getAssociatedQuizForUnit($unassUnit->ID, false, false);
					if ($quizObj) {
						$existingQuiz = sprintf('<li id="wpcw_quiz_%d" class="wpcw_dragable_quiz_item">
								<div><a href="%s&quiz_id=%d" target="_blank" title="%s">%s (ID: %d)</a></div>
								<div class="wpcw_quiz_des">%s</div>
							</li>', 
							$quizObj->quiz_id, 
							$modifyURL_quiz, $quizObj->quiz_id, __('Edit this quiz...', 'wp_courseware'),
							$quizObj->quiz_title, $quizObj->quiz_id, 
							$quizObj->quiz_desc
						);
					}					
					
					printf('<li id="wpcw_unit_%d" class="wpcw_dragable_unit_item">						
						<div><a href="%s&post=%d" target="_blank" title="%s">%s (ID: %d)</a></div>
						<div class="wpcw_dragable_quiz_holder"><ol class="wpcw_dragable_quizzes_connected wpcw_one_only">%s</ol></div>
					</li>', 
					$unassUnit->ID,
					$modifyURL_unit, $unassUnit->ID, __('Edit this unit...', 'wp_courseware'), 
					$unassUnit->post_title, $unassUnit->ID, 
					$existingQuiz);					
				}
			}
			printf('</ol>');	
		printf('</div>');	
		
		
		// ### Show a list of quizzes that are not currently assigned to units		
		printf('<div id="wpcw_unassigned_quizzes" class="wpcw_unassigned">');	
		printf('<div class="wpcw_unassigned_title">%s</div>', __('Unassigned Quizzes', 'wp_courseware'));
		
			printf('<ol class="wpcw_dragable_quizzes_connected">');
			
			// Render each unit so that it can be dragged to a module. Still render <ol> list
			// even if there are no units to show so that we can drag units into unassociated list.
			$quizzes = WPCW_quizzes_getListOfQuizzes(0);
			if ($quizzes)
			{
				foreach ($quizzes as $quizObj)
				{
					printf('<li id="wpcw_quiz_%d" class="wpcw_dragable_quiz_item">
								<div><a href="%s&quiz_id=%d" target="_blank" title="%s">%s (ID: %d)</a></div>
								<div class="wpcw_quiz_des">%s</div>
							</li>', 
							$quizObj->quiz_id, 
							$modifyURL_quiz, $quizObj->quiz_id, __('Edit this quiz...', 'wp_courseware'),
							$quizObj->quiz_title, $quizObj->quiz_id, 
							$quizObj->quiz_desc
						);
				}
			}
			printf('</ol>');
		
		printf('</div>');	
	printf('</div>'); // end of printf('<div class="wpcw_unassigned_wrapper">');
	
	
	
	// ### Show list of modules and current units
	$moduleList = WPCW_courses_getModuleDetailsList($courseID);
	
	if ($moduleList) 
	{
		printf('<ol class="wpcw_dragable_modules">');
		foreach ($moduleList as $item_id => $moduleObj) 
		{	
			// Module
			printf('<li id="wpcw_mod_%d" class="wpcw_dragable_module_item">
						<div>
							<a href="%s&module_id=%d" target="_blank" title="%s"><b>%s %d - %s (ID: %d)</b></a>
						</div>', 
				$item_id, 
				$modifyURL_module, $item_id, __('Edit this module...', 'wp_courseware'),
				__('Module', 'wp_courseware'), $moduleObj->module_number, $moduleObj->module_title, 
				$item_id
			);
				
			
			// Test Associated Units
			printf('<ol class="wpcw_dragable_units_connected">');
			$units = WPCW_units_getListOfUnits($item_id); 			
			
			if ($units)
			{
				foreach ($units as $unassUnit)
				{
					$existingQuiz = false;
					
					// Has unit got any existing quizzes?
					$quizObj = WPCW_quizzes_getAssociatedQuizForUnit($unassUnit->ID, false, false);
					$existingQuiz = false; 
					if ($quizObj) 
					{
						$existingQuiz = sprintf('<li id="wpcw_quiz_%d" class="wpcw_dragable_quiz_item">
								<div><a href="%s&quiz_id=%d" target="_blank" title="%s">%s (ID: %d)</a></div>
								<div class="wpcw_quiz_des">%s</div>
							</li>', 
							$quizObj->quiz_id, 
							$modifyURL_quiz, $quizObj->quiz_id, __('Edit this quiz...', 'wp_courseware'),
							$quizObj->quiz_title, $quizObj->quiz_id, 
							$quizObj->quiz_desc
						);
					}					
					
					printf('<li id="wpcw_unit_%d" class="wpcw_dragable_unit_item">						
						<div><a href="%s&post=%d" target="_blank" title="%s">%s (ID: %d)</a></div>
						<div class="wpcw_dragable_quiz_holder"><ol class="wpcw_dragable_quizzes_connected wpcw_one_only">%s</ol></div>
					</li>', 
					$unassUnit->ID,
					$modifyURL_unit, $unassUnit->ID, __('Edit this unit...', 'wp_courseware'),					
					$unassUnit->post_title, $unassUnit->ID, 
					$existingQuiz);
				}
			}
			
			printf('</ol></li>');
		}
		printf('</ol>');
	} 
	else {
		_e('No modules yet.', 'wp_courseware');
	}
	
	?>
	<div id="wpcw_sticky_bar" style="display: none">
		<div id="wpcw_sticky_bar_inner">
			<a href="#" id="wpcw_dragable_modules_save" class="button-primary"><?php _e('Save Changes to Ordering', 'wp_courseware'); ?></a>
			<span id="wpcw_sticky_bar_status" title="<?php _e('Ordering has changed. Ready to save changes?', 'wp_courseware'); ?>"></span>
		</div>
	</div>
	<?php 
	
	// Close overall wrapper
	printf('</div>');
	$page->showPageFooter();
}

?>
