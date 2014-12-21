<?php
/**
 * WP Courseware
 * 
 * Functions relating to showing the documentation page.
 */
 


/**
 * Open a pane of content.
 * 
 * @param String $id The ID of the unit.
 * @param String $caption The caption of the unit.
 * @param String $extracss Any extra CSS styles to add.
 */
function WPCW_docs_showRHSPane_open($id, $caption, $extracss = 'wpcw_docs_rhs_content')
{
	?>
		<div id="<?php echo $id; ?>" class="postbox <?php echo $extracss; ?>">
			<div class="handlediv" title="Click to toggle"><br /></div>
			<h3 class="hndle"><span><?php echo $caption; ?></span></h3>
			<div class="inside">
	<?php
}


/**
 * Close the pane of content.
 */
function WPCW_docs_showRHSPane_close()
{
	printf('</div>');
	printf('</div>');
}



/**
 * Shows the documentation page for the plugin. 
 */
function WPCW_showPage_Documentation_load()
{	
	$page = new PageBuilder();
	
	// List of tabs to show
	$docTabs = array(
		'default'	=> array(
			'flag'	=> false,
			'fn' 	=> 'WPCW_showPage_Documentation_shortcodes',
			'label'	=> __('Shortcodes', 'wp_courseware')
		),
		
		'howto'	=> array(
			'flag'	=> 'howto',
			'fn' 	=> 'WPCW_showPage_Documentation_howto',
			'label'	=> __('How-To Videos', 'wp_courseware')
		),
	);
	
	// Allow modification of the documentation tabs.
	$docTabs = apply_filters('wpcw_back_documentation_tabs', $docTabs);
	
	printf('<div class="wrap">');
	
	$tabNames = array_keys($docTabs);
	
	// What tabs are active?
	$tabSel = WPCW_arrays_getValue($_GET, 'info');
	if (!in_array($tabSel, $tabNames)) {
		$tabSel = false;
	}
	
	// Create main settings tab URL
	$baseURL = admin_url('admin.php?page=WPCW_showPage_Documentation');
	
	// Header
	printf('<h2 class="nav-tab-wrapper">');
	
		// Icon
		printf('<div id="icon-pagebuilder" class="icon32" style="background-image: url(\'%s\'); margin: 0px 6px 0 6px;"><br></div>',
			 WPCW_icon_getPageIconURL()
		);
		
		foreach ($docTabs as $type => $tabDetails)
		{
			// Tabs
			$urlToUse = $baseURL; 
			if ($tabDetails['flag']) {
				$urlToUse = $baseURL . '&info=' . $tabDetails['flag'];
			}
			
			printf('<a href="%s" class="nav-tab %s">%s</a>', 
				$urlToUse, ($tabDetails['flag'] == $tabSel ? 'nav-tab-active' : ''), $tabDetails['label'] 
			);
		}
		
	printf('</h2>');
	
	// Create the doc header.
	$page->showPageHeader(false, '75%', false, true);
	
	// What settings do we show?
	if (in_array($tabSel, $tabNames)) {		
		call_user_func($docTabs[$tabSel]['fn']);
	} else {
		call_user_func($docTabs['default']['fn']);
	}
		
	
	
		
	// Needed to show RHS section for panels
	$page->showPageMiddle('23%');

	// RHS Support Information
	WPCW_docs_showSupportInfo($page);
	WPCW_docs_showSupportInfo_News($page);
	WPCW_docs_showSupportInfo_Affiliate($page);
	
	$page->showPageFooter();
	// Final div closed by showPageFooter().
	//printf('</div>');
}



/**
 * Documentation: Shortcodes. 
 */
function WPCW_showPage_Documentation_shortcodes()
{
	printf('<h2 class="wpcw_doc_header first_heading">%s</h2>', __('Course Progress Shortcode', 'wp_courseware'));
	?>
	<p><?php _e('To show the course progress, you can use the <code>[wpcourse]</code> shortcode. Here\'s a summary of the shortcode parameters for <code>[wpcourse]</code>:','wp_courseware') ?></p>
		
	<h3><?php _e('Parameters:', 'wp_courseware'); ?></h3>
	<dl class="wpcw_doc_params">
		<dt><?php _e('course','wp_courseware') ?></dt>
		<dd><?php _e('<em>(Required)</em> The ID of the course to show.','wp_courseware') ?></dd>
		
		<dt><?php _e('show_title','wp_courseware') ?></dt>
		<dd><?php _e('<em>(Optional)</em> If true, show the course title. (It can be <b>true</b> or <b>false</b>. By default, it\'s <b>false</b>).','wp_courseware') ?></dd>
		
		<dt><?php _e('show_desc','wp_courseware') ?></dt>
		<dd><?php _e('<em>(Optional)</em> If true, show the course description. (It can be <b>true</b> or <b>false</b>. By default, it\'s <b>false</b>).','wp_courseware') ?></dd>		
		
		<dt><?php _e('module','wp_courseware') ?></dt>
		<dd><?php _e('<em>(Optional)</em> The number of the module to show from the specified course.','wp_courseware') ?></dd>
		
		<dt><?php _e('module_desc','wp_courseware') ?></dt>
		<dd><?php _e('<em>(Optional)</em> If true, show the module descriptions. (It can be <b>true</b> or <b>false</b>. By default, it\'s <b>false</b>).','wp_courseware') ?></dd>
	</dl>
	
	<br/>
	<h3><?php _e('Here are some examples of how <code>[wpcourse]</code> shortcode works:','wp_courseware') ?></h3>
	<dl class="wpcw_doc_examples">
		<dt><?php _e('Example 1: <code>[wpcourse course="2" module_desc="false" show_title="false" show_desc="false" /]</code>','wp_courseware') ?></dt>
		<dd><?php _e('Shows course 2, just with module and unit titles. Do not show course title, course description or module descriptions.','wp_courseware') ?></dd>
		
		<dt><?php _e('Example 2: <code>[wpcourse course="2" /]</code>','wp_courseware') ?></dt>
		<dd><?php _e('Exactly the same output as example 1.','wp_courseware') ?></dd>
		
		<dt><?php _e('Example 3: <code>[wpcourse course="1" module="4" module_desc="true" /]</code>','wp_courseware') ?></dt>
		<dd><?php _e('Shows module 4 from course 1, with module titles and descriptions, and unit titles.','wp_courseware') ?></dd>
		
		<dt><?php _e('Example 4: <code>[wpcourse course="1" module_desc="true" show_title="true" show_desc="true" /]</code>','wp_courseware') ?></dt>
		<dd><?php _e('Shows course 1, with course title, course description, module title, module description and unit titles.','wp_courseware') ?></dd>
	</dl>
	<?php 
	
	
	printf('<h2 class="wpcw_doc_header">%s</h2>', __('Overall Course Progress Shortcode', 'wp_courseware'));
	?>
	<p><?php _e('The <code>[wpcourse_progress]</code> shortcode creates a summary table of all courses that a user is signed up to, along with their progress for each course, and their grade so far.', 'wp_courseware'); ?>
	<?php _e('To be able to see their progress, a user needs to be logged in. If the user is not logged in, then a message saying that the user needs to be logged in will be shown.', 'wp_courseware'); ?></p>
	<p><?php _e('Here\'s a summary of the shortcode parameters for <code>[wpcourse_progress]</code>:','wp_courseware'); ?></p>
		
	<h3><?php _e('Parameters:', 'wp_courseware'); ?></h3>
	<dl class="wpcw_doc_params">
		<dt><?php _e('courses','wp_courseware') ?></dt>
		<dd><?php _e('<em>(Optional)</em> A comma-separated list of course IDs to show in the progress. If this is not specified, then all courses that the user is signed up to will be shown.','wp_courseware') ?></dd>
		
		<dt><?php _e('user_progress','wp_courseware') ?></dt>
		<dd><?php _e('<em>(Optional)</em> If true, then show a progress bar of the user\'s current progress for each course they are signed up to. (It can be <b>true</b> or <b>false</b>. By default, it\'s <b>true</b>).','wp_courseware') ?></dd>
		
		<dt><?php _e('user_grade','wp_courseware') ?></dt>
		<dd><?php _e('<em>(Optional)</em> If true, then show the user\'s average grade so far for each course they are signed up to. (It can be <b>true</b> or <b>false</b>. By default, it\'s <b>true</b>).','wp_courseware') ?></dd>
	</dl>
	
	<br/>
	<h3><?php _e('Here are some examples of how <code>[wpcourse_progress]</code> shortcode works:','wp_courseware') ?></h3>
	<dl class="wpcw_doc_examples">
		<dt><?php _e('Example 1: <code>[wpcourse_progress user_progress="true" user_grade="true" /]</code>','wp_courseware') ?></dt>
		<dd><?php _e('Shows all courses a user is signed up to, along with their progress and cumulative grade so far for each course.','wp_courseware') ?></dd>
	
		<dt><?php _e('Example 2: <code>[wpcourse_progress /]</code>','wp_courseware') ?></dt>
		<dd><?php _e('Does exactly the same as example 1, using the default parameter values.','wp_courseware') ?></dd>
		
		<dt><?php _e('Example 3: <code>[wpcourse_progress user_progress="false" user_grade="true" /]</code>','wp_courseware') ?></dt>
		<dd><?php _e('Shows all courses a user is signed up to and their cumulative grade so far for each course, but the progress bar for each course is hidden.','wp_courseware') ?></dd>
		
		<dt><?php _e('Example 4: <code>[wpcourse_progress user_progress="false" user_grade="false" /]</code>','wp_courseware') ?></dt>
		<dd><?php _e('Shows all courses a user is signed up to, but their progress and cumulative grades for each course are hidden.','wp_courseware') ?></dd>
		
		<dt><?php _e('Example 5: <code>[wpcourse_progress courses="1,2" user_progress="true" user_grade="true" /]</code>','wp_courseware') ?></dt>
		<dd><?php _e('Only shows courses with IDs of 1 and 2 if the user is signed to them. If the user is not signed up to any of those courses, then that course is not shown. Their progress and cumulative grade so far for each course is also shown.','wp_courseware') ?></dd>
	</dl>
	<?php 
}





/**
 * Documentation: Howto Videos 
 */
function WPCW_showPage_Documentation_howto()
{
	?>	
	<div class="wpcw_vids"><h2><?php _e('How to create a new course','wp_courseware') ?></h2>
		<iframe width="640" height="360" src="http://www.youtube.com/embed/x7q6T0R7vLg?rel=0" frameborder="0" allowfullscreen></iframe>
	</div>

	<div class="wpcw_vids"><h2><?php _e('How to create a new module','wp_courseware') ?></h2>
		<iframe width="640" height="360" src="http://www.youtube.com/embed/v2h2y3iIOio?rel=0" frameborder="0" allowfullscreen></iframe>
	</div>
	
	<div class="wpcw_vids"><h2><?php _e('How to create a new unit and assign it to a module','wp_courseware') ?></h2>
		<iframe width="640" height="360" src="http://www.youtube.com/embed/3nrLv0wxK3w?rel=0" frameborder="0" allowfullscreen></iframe>
	</div>

	<div class="wpcw_vids"><h2><?php _e('How to edit and convert a post into a unit','wp_courseware') ?></h2>
		<iframe width="640" height="360" src="http://www.youtube.com/embed/zpnQSqKTePM?rel=0" frameborder="0" allowfullscreen></iframe>
	</div>

	<div class="wpcw_vids"><h2><?php _e('How to add a course outline page','wp_courseware') ?></h2>
		<iframe width="640" height="360" src="http://www.youtube.com/embed/JR4k5SRlSD8?rel=0" frameborder="0" allowfullscreen></iframe>
	</div>

	<div class="wpcw_vids"><h2><?php _e('How to create a survey','wp_courseware') ?></h2>
		<iframe width="640" height="360" src="http://www.youtube.com/embed/I_uq57IHBnw?rel=0" frameborder="0" allowfullscreen></iframe>
	</div>

	<div class="wpcw_vids"><h2><?php _e('How to a create a blocking quiz','wp_courseware') ?></h2>
		<iframe width="640" height="360" src="http://www.youtube.com/embed/B8uSQ13Pp2s?rel=0" frameborder="0" allowfullscreen></iframe>
	</div>

	<div class="wpcw_vids"><h2><?php _e('How to create a non-blocking quiz','wp_courseware') ?></h2>
		<iframe width="640" height="360" src="http://www.youtube.com/embed/1ujT-OFj_nQ?rel=0" frameborder="0" allowfullscreen></iframe>
	</div>

	<div class="wpcw_vids"><h2><?php _e('How to enroll students and track their progress','wp_courseware') ?></h2>
		<iframe width="640" height="360" src="http://www.youtube.com/embed/-1Gfh-3_Mxw?rel=0" frameborder="0" allowfullscreen></iframe>
	</div>

	<div class="wpcw_vids"><h2><?php _e('How to reset student progress','wp_courseware') ?></h2>
		<iframe width="640" height="360" src="http://www.youtube.com/embed/Imbawimf-Xg?rel=0" frameborder="0" allowfullscreen></iframe>
	</div>

	<div class="wpcw_vids"><h2><?php _e('How to enroll all users as students into a course','wp_courseware') ?></h2>
		<iframe width="640" height="360" src="http://www.youtube.com/embed/zZBkveZ0szE?rel=0" frameborder="0" allowfullscreen></iframe>
	</div>

	<div class="wpcw_vids"><h2><?php _e('How to enroll students in bulk via CSV','wp_courseware') ?></h2>
		<iframe width="640" height="360" src="http://www.youtube.com/embed/SPl2N9075LQ?rel=0" frameborder="0" allowfullscreen></iframe>
	</div>

	<div class="wpcw_vids"><h2><?php _e('How to create a course progress page','wp_courseware') ?></h2>
		<iframe width="640" height="360" src="http://www.youtube.com/embed/vtSvinDfOsE?rel=0" frameborder="0" allowfullscreen></iframe>
	</div>

	<div class="wpcw_vids"><h2><?php _e('How to use the grade book','wp_courseware') ?></h2>
		<iframe width="640" height="360" src="http://www.youtube.com/embed/dsQrDqew8yk?rel=0" frameborder="0" allowfullscreen></iframe>
	</div>

	<div class="wpcw_vids"><h2><?php _e('How to add a course menu widget to the sidebar','wp_courseware') ?></h2>
		<iframe width="640" height="360" src="http://www.youtube.com/embed/mwsE7l9sfmg?rel=0" frameborder="0" allowfullscreen></iframe>
	</div>

	<div class="wpcw_vids"><h2><?php _e('How to import and export a course','wp_courseware') ?></h2>
		<iframe width="640" height="360" src="http://www.youtube.com/embed/lw9FjeeVrHg?rel=0" frameborder="0" allowfullscreen></iframe>
	</div>

	<div class="wpcw_vids"><h2><?php _e('How to Generate a PDF Certificate of Completion for Your Course','wp_courseware') ?></h2>
		<iframe width="640" height="360" src="http://www.youtube.com/embed/5bPUkGlNefI?rel=0" frameborder="0" allowfullscreen></iframe>
	</div>
	
	<?php 
}

?>