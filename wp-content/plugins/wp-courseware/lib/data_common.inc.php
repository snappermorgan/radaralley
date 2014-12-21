<?php

// #### Course - Fields to sue
$fieldsToProcess_course = array(
	'course_title', 
	'course_desc', 
	'course_opt_completion_wall', 
	'course_opt_user_access',
	'course_from_name',
	'course_from_email',
	'course_to_email',
	'course_message_unit_complete',
	'course_message_unit_not_logged_in',
	'course_message_unit_pending',
	'course_message_unit_no_access',
	'course_message_unit_not_yet',	
	'email_complete_module_option_admin',
	'email_complete_module_option',
	'email_complete_module_subject',
	'email_complete_module_body',
	'email_complete_course_option_admin',
	'email_complete_course_option',
	'email_complete_course_subject',
	'email_complete_course_body',
        
	// Added in V2.60
	'course_message_course_complete',
	'course_opt_use_certificate',
        
	// Added in V2.70
	'course_message_quiz_open_grading_blocking',
	'course_message_quiz_open_grading_non_blocking',
	'email_quiz_grade_option',
	'email_quiz_grade_subject',
	'email_quiz_grade_body',
	'email_complete_course_grade_summary_subject',
	'email_complete_course_grade_summary_body',
);

// ### Modules - Fields to use
$fieldsToProcess_modules = array(
	'module_title', 
	'module_desc', 
	'module_order', 
	'module_number'
);
        	
// ### Units - Fields to use
$fieldsToProcess_units = array(
	'post_title', 
	'post_content', 
	'post_name',
	'comment_status',
	'ping_status',
);  

// ### Quizzes - Fields to use
$fieldsToProcess_quizzes = array(
	'quiz_title', 
    'quiz_desc', 
	'quiz_type',
	'quiz_pass_mark',
	'quiz_show_answers',

	// @since V2.90
	'quiz_show_survey_responses',
	
	// @since V3.00
	'quiz_attempts_allowed',
	'quiz_paginate_questions',	
	'quiz_timer_mode',
	'quiz_timer_mode_limit',
	'quiz_results_by_tag',
	'quiz_results_by_timer'
	
	// Not these fields, as we handle them separately as they are arrays
    // show_answers_settings
    // quiz_paginate_questions_settings	
);


// ### Quiz Questions - Fields to use
$fieldsToProcess_quiz_questions = array(
	'question_type', 
    'question_question', 
	'question_correct_answer',
	'question_order',
	'question_answer_type',
	'question_answer_hint',	
	'question_answer_file_types',
	'question_answer_explanation',
	'question_image',

	// @since V3.00
	'question_hash',
	'question_multi_random_enable',
	'question_multi_random_count'

	// Not this field, as we handle it separately as it's an array.
    //'question_data_answers'
    
	// Legacy fields - not used
	//'question_answers'
);

// ### Quiz Custom Feedback - Fields to use
$fieldsToProcess_quiz_custom_feedback = array(
	'qfeedback_score_type', 
    'qfeedback_score_grade', 
	'qfeedback_message',
	'qfeedback_summary',
	'qfeedback_tag_name',
);


// ### Quizzes - inner - show_answers_settings
$fieldsToProcess_quizzes_inner__show_answers_settings = array(
	'show_correct_answer'		=> array('on', 'off'), 
	'show_user_answer'			=> array('on', 'off'),
	'show_explanation'			=> array('on', 'off'),
	'mark_answers'				=> array('on', 'off'),
	'show_results_later'		=> array('on', 'off'),
);

// ### Quizzes - inner - quiz_paginate_questions_settings
$fieldsToProcess_quizzes_inner__quiz_paginate_questions_settings = array(
	'allow_review_before_submission'	=> array('on', 'off'), 
	'allow_students_to_answer_later'	=> array('on', 'off'),
	'allow_nav_previous_questions'		=> array('on', 'off'),
);



?>