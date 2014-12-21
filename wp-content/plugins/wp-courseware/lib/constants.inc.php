<?php



//Ê### Database Tables

/**
 * Creates an object containing all of the tables being used in this plugin.
 */
class WPCW_Database
{
	/** Constant: Database table of the courses. */	
	public $courses;	
	
	/** Constant: Database table of the training modules. */	
	public $modules;
	
	/** Constant: Database table of meta data stored for the module units. */	
	public $units_meta;
	
	/** Constant: Database table of which courses each user can access. */	
	public $user_courses;
	
	/** Constant: Database table that contains the user's progress. */	
	public $user_progress;
	
	/** Constant: Database table that contains the user's progress for quizzes. */	
	public $user_progress_quiz;
	
	/** Constant: Database table of the quizzes. */	
	public $quiz;
	
	/** Constant: Database table of the individual quiz questions. */	
	public $quiz_qs;
	
	/** Constant: Database table for quiz feedback. */
	public $quiz_feedback;
	
	/** Constant: Database table that maps questions to quizzes. */
	public $quiz_qs_mapping;
	
	/** Constant: Database table for the membership level mappings. */	
	public $map_member_levels;
	
	/** Constant: Database tags that stores a list of certificates. */	
	public $certificates;
	
	/** Constant: Database table of tags. */	
	public $question_tags;
	
	/** Constant: Database table of tags to questions mapping. */	
	public $question_tag_mapping;
	
	/** Constant: Table that handles locking a selection of random questions to a user. */	
	public $question_rand_lock;
	
		
	
	/**
	 * Initialise table names.
	 */
	function __construct() 
	{
		global $wpdb;	
		
		// Create full table names from Wordpress
		$this->courses 					= $wpdb->prefix . 'wpcw_courses';
		$this->modules 					= $wpdb->prefix . 'wpcw_modules';
		$this->units_meta 				= $wpdb->prefix . 'wpcw_units_meta';
		$this->user_courses 			= $wpdb->prefix . 'wpcw_user_courses';
		$this->user_progress 			= $wpdb->prefix . 'wpcw_user_progress';
			
		$this->user_progress_quiz 		= $wpdb->prefix . 'wpcw_user_progress_quizzes';
		
		$this->quiz 					= $wpdb->prefix . 'wpcw_quizzes';
		$this->quiz_qs					= $wpdb->prefix . 'wpcw_quizzes_questions';
		$this->quiz_qs_mapping			= $wpdb->prefix . 'wpcw_quizzes_questions_map';
		
		$this->quiz_feedback 			= $wpdb->prefix . 'wpcw_quizzes_feedback';
		
		$this->map_member_levels		= $wpdb->prefix . 'wpcw_member_levels';		
		$this->certificates				= $wpdb->prefix . 'wpcw_certificates';
		
		$this->question_tags			= $wpdb->prefix . 'wpcw_question_tags';
		$this->question_tag_mapping		= $wpdb->prefix . 'wpcw_question_tags_map';
		$this->question_rand_lock		= $wpdb->prefix . 'wpcw_question_random_lock';
	}
}

$wpcwdb = new WPCW_Database();



/**
 * HTML used to show that a field is optional.
 */
define('WPCW_HTML_OPTIONAL',						'<em class="wpcw_optional">(Optional)</em>&nbsp;&nbsp;');

/**
 * HTML used to show that a field is optional but recommended.
 */
define('WPCW_HTML_OPTIONAL_RECOMMENDED',			'<em class="wpcw_optional">(Optional, but Recommended)</em>&nbsp;&nbsp;');

/**
 * The width of the signature image in pixels.
 */
define('WPCW_CERTIFICATE_SIGNATURE_WIDTH_PX',			'170');

/**
 * The height of the signature image in pixels.
 */
define('WPCW_CERTIFICATE_SIGNATURE_HEIGHT_PX',			'40');


/**
 * The width of the signature image in pixels.
 */
define('WPCW_CERTIFICATE_LOGO_WIDTH_PX',				'160');

/**
 * The height of the signature image in pixels.
 */
define('WPCW_CERTIFICATE_LOGO_HEIGHT_PX',				'120');

/**
 * The width of the signature image in pixels.
 */
define('WPCW_CERTIFICATE_BG_WIDTH_PX',					'3508');

/**
 * The height of the signature image in pixels.
 */
define('WPCW_CERTIFICATE_BG_HEIGHT_PX',					'2480');

?>