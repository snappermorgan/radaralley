<?php

require_once('pdf/tcpdf/tcpdf_import.php');

/**
 * Allows a summary of a user's quiz results as a PDF to be created dynamically
 * by WP Courseware using the fpdf.php library.
 *
 */
class WPCW_QuizResults
{
	protected $pdffile; 
	
	/**
	 * Size parameters that store the size of the page.
	 */
	protected $size_width;
	protected $size_height;
	protected $size_name;
	
	/**
	 * Position on x-axis of where the signature starts.
	 * @var Integer
	 */
	protected $signature_X;

	/**
	 * Position on y-axis of line where signature should be.
	 * @var Integer
	 */
	protected $footer_Y;
	
	/**
	 * The length of the line for the footer lines.
	 * @var Integer
	 */
	protected $footer_line_length;
	
	/**
	 * A list of the settings to use for the certificate generation.
	 * @var Array
	 */
	protected $settingsList;
	
	/**
	 * The name of the trainee to render on the results.
	 * @var String
	 */
	protected $data_traineeName;
	
	/**
	 * The name of the quiz that these results are for.
	 * @var String
	 */
	protected $data_quizName;
	
	/**
	 * The name of the course that these are results for.
	 * @var String
	 */
	protected $data_courseName;
	
	/**
	 * The message data shown at the top of the page before showing the results.
	 * @var Array
	 */
	protected $data_Messages;
	
	/**
	 * The results data shown in the document.
	 * @var String
	 */
	protected $data_Results;
	
	/**
	 * The results feedback messages shown in the document.
	 * @var String
	 */
	protected $data_Feedback;
	
	
	function __construct($size = 'A4') 
	{
		$this->setSize($size);
		$this->setTraineeName(false);
		$this->setQuizName(false);
		$this->setCourseName(false);
	
		$this->data_Results = false;
		$this->data_Feedback = false;
		
		// Load the settings
		$this->settingsList = TidySettings_getSettings(WPCW_DATABASE_SETTINGS_KEY);		
		
		// Create basic page
		$this->pdffile = new WPCW_PDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		
		// Set margins
		$this->pdffile->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$this->pdffile->SetHeaderMargin(PDF_MARGIN_HEADER);
		$this->pdffile->SetFooterMargin(PDF_MARGIN_FOOTER);
		
		// Set auto page breaks
		$this->pdffile->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
				
		// Set page details
		$this->pdffile->setFooterString(get_bloginfo('title') . ' - ' . home_url('/'));
		
		$this->pdffile->AddPage();
	}
	
	
	/**
	 * Set up the internal variables for size.
	 */
	function setSize($size)
	{
		switch ($size)
		{
			// A4 Size
			default:
				$this->size_name 	= 'A4';		
				$this->size_width 	= 210;
				$this->size_height 	= 297;
			break;
		}
		
		
	}
	
	/**
	 * Store the trainee name for rendering.
	 */
	function setTraineeName($str) {
		$this->data_traineeName = $str;
	}
	
	/**
	 * Store the quiz name for rendering.
	 */
	function setQuizName($str) {
		$this->data_quizName = $str;
	}
	
	/**
	 * Store the course name for rendering.
	 */
	function setCourseName($str) {
		$this->data_courseName = $str;
	}
	
	/**
	 * Add the quiz messages for the document.
	 * @param Array $msgList The list of messages to add.
	 */
	function setQuizMessages($msgList) {
		$this->data_Messages = $msgList;
	}
	
	/**
	 * Add the feedback messages for the document.
	 * @param Array $msgList The list of feedback messages to add.
	 */
	function setQuizFeedback($msgList) {
		$this->data_Feedback = $msgList;
	}
	
	
	/**
	 * Add the quiz results for the document.
	 * @param Array $resultsData The list of results to add.
	 */
	function setQuizResults($resultsData)
	{
		$cssData = '
			<style>
			.wpcw_fe_quiz_q_title {
				font-weight: bold;
				font-size: 11pt;
			}
			img {
				border: 1px solid #ddd;
				background: #fff;
			}
			.wpcw_fe_quiz_q_result {
				font-weight: bold;
			}
			.wpcw_fe_quiz_q_result_correct {
				color: #008000;
			}
			.wpcw_fe_quiz_q_result_incorrect {
				color: red;
				font-weight: bold;
			}
			</style>
		';
		
		// We're fetching an array of data to render, simply because it's easier to space out on
		// the page for the PDF.
		if (!empty($resultsData))
		{
			// Do codepage conversions of text used in the certificate settings
			$encoding = WPCW_arrays_getValue($this->settingsList, 'certificate_encoding', 'ISO-8859-1');
			
			foreach ($resultsData as $key => $boxOfData)
			{
				// Replace paragraph tags as they look better once rendered. 
				$boxOfData = str_replace('<p>', '<br><br>', $boxOfData);
				$boxOfData = str_replace('</p>', '', $boxOfData);
				
				// Convert encoding of text
				$boxOfData = iconv('UTF-8', $encoding.'//TRANSLIT//IGNORE', $boxOfData);
				
				// Add CSS
				$boxOfData .= $cssData;
				
				// Update changes to local variable.
				$resultsData[$key] = $boxOfData;
			}
		}
		
		$this->data_Results = $resultsData; 
	}
	
	
	/**
	 * Given a string, write it to the center of the page.
	 * 
	 * @param String $str The string to center.
	 * @param Integer $y_pos The Y-coordinate of the string to position.
	 */
	function centerString($str, $y_pos)
	{
		$str_width = $this->pdffile->GetStringWidth($str);
		$str_x = $this->getLeftOfCentre($str_width);
		
		$this->pdffile->SetXY($str_x, $y_pos);
		$this->pdffile->Cell(0,0, $str, false, false);
	}
	
	/**
	 * Given a width, find out the position of the left side of the object to be added.
	 * @param Integer $width The width of the item to position.
	 * @return Integer The x-coordinate of the item to position to center it.
	 */
	function getLeftOfCentre($width)
	{
		return (($this->size_width - $width) / 2);
	}
	
	
	/**
	 * Outputs a label at the top of the page.
	 */
	function outputLabel($label, $text, $labelWidth)
	{
		$this->pdffile->SetFont('', 'B');		
		$this->pdffile->Cell($labelWidth, 0, $label, false, 0, 'R', false, false, false, false, false,  'T');
		$this->pdffile->SetFont('', '');
		$this->pdffile->MultiCell(0, 0, $text, false, 'L', false);
		$this->pdffile->Ln(1);
		
		// A single line as a separator.
		$this->pdffile->Ln(2);
		$this->pdffile->Line(10, $this->pdffile->GetY(), 200, $this->pdffile->GetY());
		$this->pdffile->Ln(2);
	}
	
	
	/**
	 * Generate the results PDF.
	 * 
	 * @param String $showMode What type of export to do. ('download' to force a download or 'browser' to do it inline.)
	 */
	function generatePDF($showMode = 'download')
	{		
		// Start with main content
		$this->pdffile->setY(25);
		$this->pdffile->SetFont('Helvetica', '', 11, '', true);
		
		// Do codepage conversions of text used in the certificate.
		$encoding = WPCW_arrays_getValue($this->settingsList, 'certificate_encoding', 'ISO-8859-1');
		
		$this->data_traineeName    	= iconv('UTF-8', $encoding.'//TRANSLIT//IGNORE', $this->data_traineeName);
		$this->data_courseName 		= iconv('UTF-8', $encoding.'//TRANSLIT//IGNORE', $this->data_courseName);
		$this->data_quizName 		= iconv('UTF-8', $encoding.'//TRANSLIT//IGNORE', $this->data_quizName);
		
		// Work out the maximum width of labels to use so that the labels line up.
		$labelList = array(
			'course' 	=> __('Course Name:', 		'wp_courseware'),
			'quiz' 		=> __('Quiz Title:', 		'wp_courseware'),
			'trainee' 	=> __('Candidate Name:', 	'wp_courseware'),
		);
		
		$labelWidth = 20;
		foreach ($labelList as $key => $label) {
			$labelWidth = max($labelWidth, $this->pdffile->GetStringWidth($label));
		}
		$labelWidth += 4;
		
		$this->pdffile->SetLineWidth(.25);
		$this->pdffile->SetDrawColor(0, 0, 0);
		
		$this->pdffile->Ln(2);
		$this->pdffile->Line(10, $this->pdffile->GetY(), 200, $this->pdffile->GetY());
		$this->pdffile->Ln(2);
		
		// Course Title		
		$this->outputLabel($labelList['course'], $this->data_courseName, $labelWidth);
		//$this->outputLabel($labelList['course'], 'This is a reallly really long course title to test with to see if wrapping happens really nicely because this is really important and we want it to look good.', $labelWidth);
		
		// Quiz Title
		$this->outputLabel($labelList['quiz'], $this->data_quizName, $labelWidth);
		
		// Candidate Name
		$this->outputLabel($labelList['trainee'], $this->data_traineeName, $labelWidth);
		

		$this->pdffile->Ln(2);

		$this->pdffile->setImageScale(3);
				
		// Render the messages that we have.
		if (!empty($this->data_Messages))
		{
			$messageToShow = false;
			
			// 1) Check for messages by row. If we've got a row, then render each item.
			//    There are a maximum of 5 rows.
			for ($idx = 0; $idx < 5; $idx++)
			{
				if (!empty($this->data_Messages[$idx]))
				{
					// There may be multiple messages per row.
					foreach ($this->data_Messages[$idx] as $keyName => $stringToShow)
					{
						$messageToShow .= $stringToShow . ' ';
					}
					
					$messageToShow .= '<br><br>';
				}
			}
			
			// Render as a single box with padding
			if ($messageToShow) 
			{
				$this->pdffile->SetFont('Helvetica', '', 11);
				
				// Set text colour based on pass or fail.
				if ($this->data_Messages['error_mode']) {
					$this->pdffile->SetTextColor(255, 0, 0);
				} else {
					$this->pdffile->SetTextColor(0, 128, 0);
				}
				
				// Need to remove the final <br><br>
				$messageToShow = substr($messageToShow, 0, -8); // 8 due to 8 chars in <br><br>
				
				$this->pdffile->writeHTML($messageToShow);
				$this->pdffile->Ln(4);
				
				// Restore colour
				$this->pdffile->SetTextColor(0, 0, 0);
			}
			
			// Line underneath the message summary
			$this->pdffile->Line(10, $this->pdffile->GetY(), 200, $this->pdffile->GetY());
			$this->pdffile->Ln(3);
			
			// Set new body size 
			$this->pdffile->SetFont('Helvetica', '', 10);
			
			$showingTags = false;
			$showingTimer = false;
			
			// 2) Show the progress by tag if present.
			if (isset($this->data_Messages['msg_results_by_tag']) && !empty($this->data_Messages['msg_results_by_tag']))
			{
				$showingTags = true;
				
				// Create Results breakdown label
				$this->pdffile->SetFont('', 'B', 11);
				$this->pdffile->WriteHTML(__('Results breakdown:', 'wp_courseware'));
				$this->pdffile->Ln(2);
				
				$this->pdffile->SetFont('', 'B', 10);
				
				// Add a wrapper per line for the tag results.
				foreach ($this->data_Messages['msg_results_by_tag'] as $tagMessage)
				{
					$this->pdffile->WriteHTML('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $tagMessage);
					$this->pdffile->Ln(1);
				}
				
				$this->pdffile->Ln(0);
			}
		
			// 2) Show the progress by timer if present.
			if (isset($this->data_Messages['msg_results_by_timer']))
			{
				$showingTimer = false;
				
				if ($showingTags) 
				{
					$this->pdffile->Ln(3);
					$this->pdffile->Line(10, $this->pdffile->GetY(), 200, $this->pdffile->GetY());
					$this->pdffile->Ln(2);
				}
				
				$this->pdffile->WriteHTML($this->data_Messages['msg_results_by_timer']);
				$this->pdffile->Ln(1);
			}
			
			// 3) Show extra line for clarity
			if ($showingTags || $showingTimer)
			{
				$this->pdffile->Ln(2);
				$this->pdffile->Line(10, $this->pdffile->GetY(), 200, $this->pdffile->GetY());
				$this->pdffile->Ln(3);
			}
		}
		
		
		// 4) - Render the results data we have for this quiz
		if (!empty($this->data_Results))
		{
			// Create Results breakdown label
			$this->pdffile->Ln(4);	
			$this->pdffile->SetFont('Helvetica', 'B', 14);		
			$this->pdffile->Write(5, __('Your answer details:', 'wp_courseware'), false, false, 'C', true);
			$this->pdffile->Ln(5);
			
			// Set up text size and compact it
			$this->pdffile->SetFont('Helvetica', '', 10);
			$this->pdffile->setCellHeightRatio(.9);
						
			foreach ($this->data_Results as $singleLineOfData)
			{
				// Set colours
				$this->pdffile->SetFillColor(239, 239, 239);
				$this->pdffile->SetDrawColor(200, 200, 200);
				
				// Render each results box
				$this->pdffile->SetCellPadding(5);
				$this->pdffile->WriteHTMLCell(0, 0, 10, $this->pdffile->GetY(), $singleLineOfData, 'TBLR', true, true);
				$this->pdffile->Ln(5);
			}
		}
		
		
		// 5) - Render the custom feedback messages
		if (!empty($this->data_Feedback))
		{
			// Create Results breakdown label
			$this->pdffile->Ln(4);	
			$this->pdffile->SetFont('Helvetica', 'B', 14);		
			$this->pdffile->Write(5, __('Instructor Feedback:', 'wp_courseware'), false, false, 'C', true);
			$this->pdffile->Ln(3);
			
			// Set up text size and compact it
			$this->pdffile->SetFont('Helvetica', '', 10);
			$this->pdffile->setCellHeightRatio(1);
						
			foreach ($this->data_Feedback as $singleLineOfData)
			{
				// Set colours
				$this->pdffile->SetFillColor(239, 239, 239);
				$this->pdffile->SetDrawColor(200, 200, 200);
				
				// Render each results box
				$this->pdffile->SetCellPadding(5);
				$this->pdffile->WriteHTMLCell(0, 0, 10, $this->pdffile->GetY(), wpautop($singleLineOfData), 'TBLR', true, true);
				$this->pdffile->Ln(5);
			}
		}
		

		// Change output based on what's been specified as a parameter.
		$exportFile = "quiz-results-" . sanitize_title($this->data_quizName) . '-' . date("Y-m-d") . ".pdf";
		
		if ('browser' == $showMode) {
			$this->pdffile->Output($exportFile, 'I');
		} else {
			$this->pdffile->Output($exportFile, 'D');
		}
		
	}
	
}


/**
 * Class that extends FPDF to use basic HTML with the results details.
 */
class WPCW_PDF extends TCPDF
{
	var $footerString;

    /**
     * Render the page header.
     */
    public function Header() 
    {
    	$this->SetY(15);
    	
        // Set font
        $this->SetFont('helvetica', 'B', 20);
        
        // Title
        $this->Cell(0, 15, __('Your Quiz Results', 'wp_courseware'), 0, false, 'C', 0, '', 0, false, 'M', 'M');
    }

    
    /**
     * Render the page footer with page number and details.
     */
    public function Footer() 
    {
        // Set font
        $this->SetFont('helvetica', '', 8);
        
        // Page number
        $this->SetY(-18);
        $this->Cell(0, 8, sprintf(__('Page %s of %s', 'wp_courseware'), $this->getAliasNumPage(), $this->getAliasNbPages()), 0, false, 'C', 0, '', 0, false, 'T', 'M');
        
        // Copyight-style link
        $this->SetY(-12);
        $this->Cell(0, 0, $this->footerString, 0, 0,'C', 0, false);
    }
    
    
    /**
     * Set the string that appears in the footer.
     * @param String $str The string that appears in the footer.
     */
    function setFooterString($str) {
    	$this->footerString = $str;
    }    
}

?>