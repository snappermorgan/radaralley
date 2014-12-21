<?php
 
/**
 * Email - Module Complete 
 */
define('EMAIL_TEMPLATE_COMPLETE_MODULE_SUBJECT', __("Module {MODULE_TITLE} - Complete.", 'wp_courseware'));
define('EMAIL_TEMPLATE_COMPLETE_MODULE_BODY', __('Hi {USER_NAME}

Great work for completing the "{MODULE_TITLE}" module!

{SITE_NAME}
{SITE_URL}', 'wp_courseware'));


/**
 * Email - Course Complete 
 */
define('EMAIL_TEMPLATE_COMPLETE_COURSE_SUBJECT', __("Course {COURSE_TITLE} - Complete", 'wp_courseware'));
define('EMAIL_TEMPLATE_COMPLETE_COURSE_BODY', __('Hi {USER_NAME}

Great work for completing the "{COURSE_TITLE}" training course! Fantastic!

{SITE_NAME}
{SITE_URL}', 'wp_courseware'));


/**
 * Email - Quiz Grade
 */
define('EMAIL_TEMPLATE_QUIZ_GRADE_SUBJECT', __('{COURSE_TITLE} - Your Quiz Grade - For "{QUIZ_TITLE}"', 'wp_courseware'));
define('EMAIL_TEMPLATE_QUIZ_GRADE_BODY', __('Hi {USER_NAME}

Your grade for the "{QUIZ_TITLE}" quiz is: 
{QUIZ_GRADE} 

This was for the quiz at the end of this unit:
{UNIT_URL}

{QUIZ_RESULT_DETAIL}

{SITE_NAME}
{SITE_URL}', 'wp_courseware')); 
		

/**
 * Email - Final Course Summary and Grade
 */
define('EMAIL_TEMPLATE_COURSE_SUMMARY_WITH_GRADE_SUBJECT', __('Your final grade summary for "{COURSE_TITLE}"', 'wp_courseware'));
define('EMAIL_TEMPLATE_COURSE_SUMMARY_WITH_GRADE_BODY', __( 'Hi {USER_NAME}

Congratulations on completing the "{COURSE_TITLE}" training course! Fantastic!

Your final grade is: {CUMULATIVE_GRADE} 

Here is a summary of your quiz results:
{QUIZ_SUMMARY}

You can download your certificate here:
{CERTIFICATE_LINK}

I hope you enjoyed the course!

{SITE_NAME}
{SITE_URL}', 'wp_courseware')); 
 


?>