<?php

require_once('pdf/tcpdf/tcpdf_import.php');

/**
 * Allows PDF certificates to be created dynamically
 * by WP Courseware using the fpdf.php library.
 *
 */
class WPCW_Certificate
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
	
	
	
	function __construct($size = 'A4') 
	{
		// Update size variables to allow calculations for distance.
		$this->setSize($size);
		
		// Create basic page layout
		$this->pdffile = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
		$this->pdffile->AddPage();
						
		// Load the certificate settings
		$this->settingsList = TidySettings_getSettings(WPCW_DATABASE_SETTINGS_KEY);		
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
	 * Draw a centered line at the specified height.
	 * 
	 * @param Integer $width The width of the line.
	 * @param Integer $y_pos The Y-coordinate of the string to position.
	 */
	function centerLine($width, $y_pos)
	{				
		$x = $this->getLeftOfCentre($width);
		$this->pdffile->Line($x, $y_pos, $x+$width, $y_pos);
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
				$this->size_width 	= 297;
				$this->size_height 	= 210;
			break;
		}
		
		
	}
	
	
	
	
	/**
	 * Generate the certificate PDF.
	 * 
	 * @param String $student The name of the student.
	 * @param String $courseName The name of the course.
	 * @param String $certificateDetails The raw certificate details.
	 * @param String $showMode What type of export to do. ('download' to force a download or 'browser' to do it inline.)
	 */
	function generatePDF($student, $courseName, $certificateDetails, $showMode = 'download')
	{		
		// Do codepage conversions of text used in the certificate.
		$encoding = WPCW_arrays_getValue($this->settingsList, 'certificate_encoding', 'ISO-8859-1');
		
		$student    = iconv('UTF-8', $encoding.'//TRANSLIT//IGNORE', $student);
		$courseName = iconv('UTF-8', $encoding.'//TRANSLIT//IGNORE', $courseName);
		
		
		$topLineY = 45;
		
		// Set the background image
		$bgType = WPCW_arrays_getValue($this->settingsList, 'cert_background_type', 'use_default');
		$bgImg  = WPCW_arrays_getValue($this->settingsList, 'cert_background_custom_url');
		
		// Disable auto-page-break
		$this->pdffile->SetAutoPageBreak(false, 0);
		
		// Use custom image
		if ($bgType == 'use_custom') {
			if ($bgImg) {
				$this->pdffile->Image($bgImg, 0, 0, $this->size_width, $this->size_height);	
			}
		}
		
		// Use default image
		else {
			$this->pdffile->Image(WPCW_plugin_getPluginDirPath() . 'img/certificates/certificate_bg.jpg', 0, 0, 297, 210, '', '', '', false, 300, '', false, false, 0);
		}
		 
		
		// ...Certify...
		$this->pdffile->SetFont('Helvetica','B', 32);		
		$this->centerString(strtoupper(__('This is to certify that', 'wp_courseware')), $topLineY);
		
		// Name with a line underneath
		$this->pdffile->SetFont('ArchitectsDaughter','', 16, false, false);
		$this->centerString($student, $topLineY + 20);		
		$this->centerLine(120, $topLineY + 27);
		
		// ...Completed...
		$this->pdffile->SetFont('Helvetica','B', 32);
		$this->centerString(strtoupper(__('has successfully completed', 'wp_courseware')), $topLineY + 50);
		
		// Course
		$this->pdffile->SetFont('ArchitectsDaughter','', 16);
		$this->centerString($courseName, $topLineY + 70);		
		$this->centerLine(180, $topLineY + 77);
		
		
		$this->footer_line_length = 60;
		$this->footer_Y = 162;
		$footer_font_size = 15;
		
		$date_X 		= 40;
		$this->signature_X 	= $this->size_width - 100;
		
		$this->pdffile->SetFont('Helvetica','', 14);
		
		// Date - field		
		$this->pdffile->SetXY($date_X, $this->footer_Y+8);
		$this->pdffile->Cell(0, 0, __('Date', 'wp_courseware'), false, false, 'L');		    	
				
		// Signature - field
		$this->pdffile->SetXY($this->signature_X, $this->footer_Y+8);
		$this->pdffile->Cell(0,0, __('Instructor', 'wp_courseware'), false, false, 'L');
		
		// Lines - Date, Signature
		$this->pdffile->Line($date_X, 		$this->footer_Y+7, $date_X + $this->footer_line_length,	 	 $this->footer_Y+7);
		$this->pdffile->Line($this->signature_X, 	$this->footer_Y+7, $this->signature_X + $this->footer_line_length, $this->footer_Y+7);
		
		
		// Date - the date itself. Centre on the line
		$this->pdffile->SetFont('ArchitectsDaughter','', $footer_font_size);
				
		// Use date of completion if available from certificate details
		$completeDate = false;
		if ($certificateDetails && $certificateDetails->cert_generated) {
			$completeDate = strtotime($certificateDetails->cert_generated);
		}		
		
		// Use current date if not available.
		if ($completeDate <= 0) {
			$completeDate = current_time('timestamp');
		}
		
		$date_localFormat = get_option('date_format');
		$date_str = date_i18n($date_localFormat, $completeDate);		
		$date_str_len = $this->pdffile->GetStringWidth($date_str);
				
		$this->pdffile->SetXY($date_X + (($this->footer_line_length - $date_str_len)/2), $this->footer_Y);
		$this->pdffile->Cell(0,0, $date_str, false, false);
		
		// Signature - signature itself			
		$this->render_handleSignature();
		
		// Logo - handle rendering a logo if one exists
		$this->render_handleLogo();
	
		// Change output based on what's been specified as a parameter.
		if ('browser' == $showMode) {
			$this->pdffile->Output('certificate.pdf', 'I');
		} else {
			$this->pdffile->Output('certificate.pdf', 'D');
		}
		
	}
	
	/**
	 * Convert a measurement from pixels to millimetres at 72dpi.
	 * @param Integer $px Measurement in pixels
	 * @return Float Millimetres
	 */
	static function px2mm($px){
	    return $px*25.4/72;
	}
	
	/**
	 * Convert a measurement from millimetres into pixels at 72dpi.
	 * @param Integer $mm Measurement in mm.
	 * @return Float Pixels
	 */
	static function mm2px($mm){
	    return ($mm*72)/25.4;
	}
	
	
	/**
	 * Renders the logo provided by the user.
	 */
	function render_handleLogo()
	{
		$logoShow = WPCW_arrays_getValue($this->settingsList, 'cert_logo_enabled');
		$logoImg = WPCW_arrays_getValue($this->settingsList, 'cert_logo_url');
		
		// No logo to work with, abort.
		if ('cert_logo' != $logoShow || !$logoImg) {
			return;
		}
		
		// Image is fetched using URL, and resized to match the space.
		$logoWidth = WPCW_Certificate::px2mm(WPCW_CERTIFICATE_LOGO_WIDTH_PX);
		$logoHeight = WPCW_Certificate::px2mm(WPCW_CERTIFICATE_LOGO_HEIGHT_PX);
			
		$this->pdffile->Image($logoImg, $this->getLeftOfCentre($logoWidth), 134, $logoWidth); // Only force width
	}
	
	
	/**
	 * Renders the signature area for the certificate.
	 */
	function render_handleSignature()
	{
		// Have we got a text or image signature?
		$signature = '';
		$signatureType = WPCW_arrays_getValue($this->settingsList, 'cert_signature_type', 'text');
		$signatureImg  = WPCW_arrays_getValue($this->settingsList, 'cert_sig_image_url');
		
		// Get the text for the signature
		if ('text' == $signatureType)
		{
			// Use codepage translation of signature text
			$encoding = WPCW_arrays_getValue($this->settingsList, 'certificate_encoding', 'ISO-8859-1');
			$signature = iconv('UTF-8', $encoding.'//TRANSLIT//IGNORE', WPCW_arrays_getValue($this->settingsList, 'cert_sig_text'));
			
			// Nothing to do, signature is empty
			if (!$signature) {
				return;
			}
			
			// Create the signature
			$signature_len = $this->pdffile->GetStringWidth($signature);
			$this->pdffile->SetXY($this->signature_X + (($this->footer_line_length - $signature_len)/2), $this->footer_Y);
			$this->pdffile->Cell(0,0, $signature, false, false);
		}
		
		// Image - see if we have anything to use.
		else 
		{
			// No image to work with
			if (!$signatureImg) {
				return;
			}
			
			// Image is fetched using URL, and resized to match the space. We're using
			// an image that's twice the size to get it to scale nicely.
			$sigWidth = WPCW_Certificate::px2mm(WPCW_CERTIFICATE_SIGNATURE_WIDTH_PX);
			$sigHeight = WPCW_Certificate::px2mm(WPCW_CERTIFICATE_SIGNATURE_HEIGHT_PX);
			
			// Only force width
			$this->pdffile->Image($signatureImg, $this->signature_X + ($this->footer_line_length - $sigWidth)/2, $this->footer_Y - $sigHeight + 6, $sigWidth); 
			
		}
				
		
	}
}


?>