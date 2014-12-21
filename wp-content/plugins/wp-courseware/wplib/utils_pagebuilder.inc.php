<?php
/**
 * Wordpress Page Builder Utility Class
 * 
 * A group of classes designed to make it easier and quicker to create pages 
 * within wordpress plugins for the admin section. Using this class should hopefully 
 * reduce development and debugging time.
 * 
 * This code is very much in alpha phase, and should not be distributed with plugins 
 * other than by Dan Harrison.
 *  
 *
 * Version History
 * 
 * V0.01 -  1st Jan	2011 - Initial version released.
 * V0.02 - 28th Mar 2013 - Removed reliance on PHP short tags.
 * V0.03 - 21st Oct 2013 - Added ability for a simpler header for tabbed pages.
 *
 */

/**
 * Class that represents a HTML table for the Wordpress admin area.
 */
if (!class_exists('PageBuilder')) { class PageBuilder {

	/**
	 * If true, then a pane is open and needs closing before opening a new one.
	 * @var Boolean True if a pane is open and needs closing, false otherwise.
	 */
	private $paneOpen;
	
	/**
	 * If true, then the first section is open and needs closing before opening a new one.
	 * @var Boolean True if the first section is open and needs closing, false otherwise.
	 */
	private $firstSectionOpen;	
	
	/**
	 * If true, then we have 2 resizable and re-locatable columns. 
	 * @var Boolean True for 2 column layout, false for 1 column layout.
	 */
	private $twoColumnLayout;
	
	
	
	/**
	 * Constructor
	 * @param $twoColumns If true, we're using a 2 column layout, if false, then it's a single column layout.
	 * @return unknown_type
	 */
	function PageBuilder($twoColumns = true) {
		$this->twoColumnLayout = $twoColumns;
	}

	
	/**
	 * Shows a status or error message for the user using the standard Wordpress admin format.
	 * @param String $message The message to show.
	 * @param String $errormsg If true, show an error message.
	 */
	function showMessage($message = "Settings saved.", $errormsg = false)
	{
		if ($errormsg) {
			echo '<div id="message" class="error">';
		}
		else {
			echo '<div id="message" class="updated fade">';
		}
	
		echo "<p><strong>$message</strong></p></div>";
	}	
	
	/**
	 * Show the array of errors as a formatted error message.
	 * @param Array $errors The list of errors.
	 */
	function showListOfErrors($errors, $customMessage = false)
	{
		if ($customMessage) {
			$message = $customMessage . '<br/><br/>';
		} else {
			$message = "Sorry, but unfortunately there were some errors. Please fix the errors and try again.<br><br>";
		}
	
		$message .= "<ul style=\"margin-left: 20px; list-style-type: square;\">";
		
		// Loop through all errors in the $error list
		foreach ($errors as $errormsg) {
			$message .= "<li>$errormsg</li>";
		}
					
		$message .= "</ul>";
		$this->showMessage($message, true);
	}
	
	
	/**
	 * Creates the first column for a page that has panes.
	 * @param $pagetitle The main title of the page.
	 * @param $width The width as a percentage of the first column.
	 * @param $pageIcon The URL for the icon to use for the page title, false otherwise.
	 */
	function showPageHeader($pagetitle, $width = "75%", $pageIcon = false, $simpleMode = false)
	{
		$this->paneOpen = false;
		$this->firstSectionOpen = true;
		
		if (!$simpleMode) 
		{
		?>
			<div class="wrap">
				<?php if ($pageIcon) : ?>
					<div id="icon-pagebuilder" class="icon32" style="background: url('<?php echo $pageIcon; ?>') no-repeat scroll 0% 0% transparent;" >
						<br/>
					</div>
				<?php else : ?>
					<div id="icon-edit-pages" class="icon32">
						<br/>
					</div>
				<?php endif; ?>				
				
				<h2><?php echo $pagetitle; ?></h2>
			<?php 
		}
			
		// Postbox Container is used for multi-column layout
		if ($this->twoColumnLayout) {
		?>
			<div class="postbox-container" style="width:<?php echo $width; ?>; margin-right: 20px;">
				<div class="metabox-holder">	
					<div class="meta-box-sortables">
		<?php 
		}
	}
	
	/**
	 * Closes the first column and creates the second column for a page that has panes.
	 * @param $width The width as a percentage of the second column.
	 */
	function showPageMiddle($width = "20%")
	{
		// If we've got a single column layout, we don't need a middle.
		if (!$this->twoColumnLayout) {
			return;
		}
		
		// Close previous pane if still open.
		if ($this->paneOpen) {
			$this->closePane();
		}
		
		// We're doing the 2nd section now, so close first section.
		$this->firstSectionOpen = false;
				
		?>
					</div>
				</div>
			</div>
			
			<div class="postbox-container" style="width:<?php echo $width; ?>;">
				<div class="metabox-holder">	
					<div class="meta-box-sortables">
		<?php 
	}
	
	/**
	 * Creates the footer for the page that has panes.
	 */
	function showPageFooter()
	{
		if ($this->firstSectionOpen) {
			$this->showPageMiddle();
		}
		
		// Close previous pane if still open.
		if ($this->paneOpen) {
			$this->closePane();
		}
				
		// If a 2 column layout, we have more divs to close.
		if ($this->twoColumnLayout) {
			?>
							</div>
					</div>
				</div>
			<?php
		} 
			
		?>
		</div>
		<?php 
	}
	
	/**
	 * Creates the header for a pane within a page.
	 * @param $id The ID of the pane to create, used in the HTML id attribute.
	 * @param $title The title of the pane.
	 * @param $closed If true, the pane is closed from view, if false, the pane is visible. False by default.
	 */
	function openPane($id, $title, $closed = false) 
	{
		// Don't need a pane if we've got a single column layout
		if (!$this->twoColumnLayout) {
			return;
		}
		
		// Close previous pane if still open.
		if ($this->paneOpen) {
			$this->closePane();
		}
		
		$this->paneOpen = true;
		
		$extracss = "";
		if ($closed) {
			$extracss = " closed";
		}
		
		?>
			<div id="<?php echo $id; ?>" class="postbox<?php echo $extracss; ?>">
				<div class="handlediv" title="Click to toggle"><br /></div>
				<h3 class="hndle"><span><?php echo $title; ?></span></h3>
				<div class="inside">
		<?php
	}
	
	/**
	 * Creates a footer for a pane within a page.
	 */
	function closePane()
	{
		$this->paneOpen = false;
		?>
				</div>
			</div>
		<?php
	}
}}

?>