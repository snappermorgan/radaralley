<?php
/**
 * Account class.
 *
 * @since 1.0.0
 *
 * @package	OptinMonster
 * @author	Thomas Griffin
 */
class optin_monster_account {

	/**
	 * Constructor. Hooks all interactions into correct areas to start
	 * the class.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Shh...

	}

	/**
	 * Returns email providers and segments for a given account.
	 *
	 * @since 1.0.0
	 *
	 * @return array An array of email provider data.
	 */
	public function get_email_providers() {

        return get_option( 'optin_monster_providers' );

	}

	public function get_email_services() {

		$providers = array(
		    array(
				'name' => 'Custom HTML Optin Form',
				'value' => 'custom'
			),
		    array(
				'name' => 'Aweber',
				'value' => 'aweber'
			),
			array(
				'name' => 'Campaign Monitor',
				'value' => 'campaign-monitor'
			),
			array(
				'name' => 'Constant Contact',
				'value' => 'constant-contact'
			),
			array(
                'name' => 'GetResponse',
                'value' => 'getresponse'
            ),
			array(
                'name' => 'iContact',
                'value' => 'icontact'
            ),
            array(
			    'name' => 'Infusionsoft',
			    'value' => 'infusionsoft'
            ),
			array(
				'name' => 'Madmimi',
				'value' => 'madmimi'
			),
			array(
				'name' => 'MailChimp',
				'value' => 'mailchimp'
			),
			array(
				'name' => 'Pardot',
				'value' => 'pardot'
			)
		);

		// If MailPoet is active, add as a provider.
		if ( class_exists( 'WYSIJA' ) ) {
		    $providers[] = array(
				'name' => 'MailPoet (Wysija)',
				'value' => 'mailpoet'
			);
        }

		return $providers;

	}

	public function get_available_fonts( $all = true ) {

		$reg_fonts    = apply_filters( 'optin_monster_regular_fonts', array( 'Helvetica', 'Helvetica Neue', 'Arial', 'Tahoma', 'Verdana', 'Times New Roman', 'Georgia' ) );
		$google_fonts = apply_filters( 'optin_monster_google_fonts', array( 'Droid Sans', 'Droid Serif', 'Vollkorn', 'Lobster', 'Bree Serif', 'Playfair Display', 'Cabin', 'Cookie', 'Lora', 'Ubuntu', 'Open Sans', 'Josefin Slab', 'Arvo', 'Lato', 'Abril Fatface', 'Montserrat', 'PT Sans', 'PT Serif', 'Noto Serif', 'Libre Baskerville', 'Oswald' ) );
		if ( ! $all )
			return $google_fonts;
		else
			$fonts = array_merge( $reg_fonts, $google_fonts );

		sort( $fonts );
		array_unshift( $fonts, 'Select your font...' );
		return $fonts;

	}

	public function get_report_dates() {
    	$dates = array();

    	$dates['range']		= isset( $_GET['range'] )	? $_GET['range']	: 'today';
    	$dates['day']		= isset( $_GET['day'] ) 	? $_GET['day'] 		: strtotime( 'midnight', time() );
    	$dates['m_start'] 	= isset( $_GET['m_start'] ) ? $_GET['m_start'] 	: 1;
    	$dates['m_end']		= isset( $_GET['m_end'] ) 	? $_GET['m_end'] 	: 12;
    	$dates['year'] 		= isset( $_GET['year'] ) 	? $_GET['year'] 	: date( 'Y' );
    	$dates['year_end']	= date( 'Y' );

    	// Modify dates based on predefined ranges
    	switch( $dates['range'] ) :

    		case 'this_month' :

    			$dates['m_start'] 	= date( 'n' );
    			$dates['m_end']		= date( 'n' );
    			$dates['year']		= date( 'Y' );

    			break;

    		case 'last_month' :
    			if( $dates['m_start'] == 12 ) {
    				$dates['m_start'] = 12;
    				$dates['m_end']	  = 12;
    				$dates['year']    = date( 'Y' ) - 1;
    				$dates['year_end']= date( 'Y' ) - 1;
    			} else {
    				$dates['m_start'] = date( 'n' ) - 1;
    				$dates['m_end']	  = date( 'n' ) - 1;
    				$dates['year']    = date( 'Y' );
    			}


    			break;

    		case 'today' :

    			$dates['day']		= date( 'd' );
    			$dates['m_start'] 	= date( 'n' );
    			$dates['m_end']		= date( 'n' );
    			$dates['year']		= date( 'Y' );

    			break;

    		case 'this_week' :

    			$dates['day']       = date( 'd', time() - ( date( 'w' ) - 1 ) *60*60*24 );
    			$dates['day_end']   = $dates['day'] + 6;
    			$dates['m_start'] 	= date( 'n' );
    			$dates['m_end']		= date( 'n' );
    			$dates['year']		= date( 'Y' );
    			break;

    		case 'last_week' :

    			$dates['day']       = date( 'd', time() - ( date( 'w' ) - 1 ) *60*60*24 ) - 6;
    			$dates['day_end']   = $dates['day'] + 6;
    			$dates['m_start'] 	= date( 'n' );
    			$dates['m_end']		= date( 'n' );
    			$dates['year']		= date( 'Y' );
    			break;

    		case 'this_quarter' :

    			$month_now = date( 'n' );

    			if ( $month_now <= 3 ) {

    				$dates['m_start'] 	= 1;
    				$dates['m_end']		= 3;
    				$dates['year']		= date( 'Y' );

    			} else if ( $month_now <= 6 ) {

    				$dates['m_start'] 	= 4;
    				$dates['m_end']		= 6;
    				$dates['year']		= date( 'Y' );

    			} else if ( $month_now <= 9 ) {

    				$dates['m_start'] 	= 7;
    				$dates['m_end']		= 9;
    				$dates['year']		= date( 'Y' );

    			} else {

    				$dates['m_start'] 	= 10;
    				$dates['m_end']		= 12;
    				$dates['year']		= date( 'Y' );

    			}

    			break;

    		case 'last_quarter' :

    			$month_now = date( 'n' );

    			if ( $month_now <= 3 ) {

    				$dates['m_start'] 	= 10;
    				$dates['m_end']		= 12;
    				$dates['year']		= date( 'Y' ) - 1; // Previous year

    			} else if ( $month_now <= 6 ) {

    				$dates['m_start'] 	= 1;
    				$dates['m_end']		= 3;
    				$dates['year']		= date( 'Y' );

    			} else if ( $month_now <= 9 ) {

    				$dates['m_start'] 	= 4;
    				$dates['m_end']		= 6;
    				$dates['year']		= date( 'Y' );

    			} else {

    				$dates['m_start'] 	= 7;
    				$dates['m_end']		= 9;
    				$dates['year']		= date( 'Y' );

    			}

    			break;

    		case 'this_year' :

    			$dates['m_start'] 	= 1;
    			$dates['m_end']		= 12;
    			$dates['year']		= date( 'Y' );

    			break;

    		case 'last_year' :

    			$dates['m_start'] 	= 1;
    			$dates['m_end']		= 12;
    			$dates['year']		= date( 'Y' ) - 1;

    			break;

    	endswitch;

    	return $dates;
    }

    public function get_downloads_data( $all = false, $switch = '' ) {

        // Load DB class.
    	global $wpdb;
		$table_name = $wpdb->prefix . 'om_hits_log';

        if ( ! empty( $switch ) ) {
    	    $optin = get_posts( array( 'post_type' => 'optin', 'name' => $switch, 'posts_per_page' => 1 ) );
            $this->optin = $optin[0];
        } else {
            $this->optin = false;
        }

    	// Retrieve the queried dates
    	$dates = $this->get_report_dates();
    	$ret = '';
    	$time = time();
    	$data = array();

    	if ( 'today' == $dates['range'] ) :
    		// Hourly impression report.
    		$hour = 0;
    		while ( $hour <= 23 ) :
    			$beginning = strtotime( 'midnight', $time ) + ($hour * 3600);
    			$ending    = strtotime( 'midnight', $time ) + (($hour + 1) * 3600);
    			if ( $all )
    			    $data[] = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE hit_type = %s AND hit_date >= FROM_UNIXTIME(%d) AND hit_date <= FROM_UNIXTIME(%d)", 'conversion', $beginning, $ending ), OBJECT );
    			else
    			    $data[] = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE optin_id = %d AND hit_type = %s AND hit_date >= FROM_UNIXTIME(%d) AND hit_date <= FROM_UNIXTIME(%d)", $this->optin->ID, 'conversion', $beginning, $ending ), OBJECT );
    			$hour++;
    		endwhile;
    	elseif ( 'this_week' == $dates['range'] || 'last_week' == $dates['range'] ) :
    		// Daily impression report.
    		$day     = $dates['day'];
    		$day_end = $dates['day_end'];
    		$month   = $dates['m_start'];
    		$m_end	 = $dates['m_end'];
    		while ( $day <= $day_end ) :
    			$beginning  = strtotime( $month . '/' . $day . '/' . $dates['year'] );
    			$ending		= strtotime( $m_end . '/' . $day . '/' . $dates['year_end'] ) + 86400;
    			if ( $all )
    			    $data[] = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE hit_type = %s AND hit_date >= FROM_UNIXTIME(%d) AND hit_date <= FROM_UNIXTIME(%d)", 'conversion', $beginning, $ending ), OBJECT );
    			else
    			    $data[] = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE optin_id = %d AND hit_type = %s AND hit_date >= FROM_UNIXTIME(%d) AND hit_date <= FROM_UNIXTIME(%d)", $this->optin->ID, 'conversion', $beginning, $ending ), OBJECT );
    			$day++;
    		endwhile;
    	else :
    		$i = $dates['m_start'];
    		while ( $i <= $dates['m_end'] ) :
    			if ( $day_by_day ) :
    				$num_of_days 	= cal_days_in_month( CAL_GREGORIAN, $i, $dates['year'] );
    				$d 				= 1;
    				while ( $d <= $num_of_days ) :
    					$beginning  = strtotime( $i . '/' . $d . '/' . $dates['year'] );
    					$ending		= strtotime( $i . '/' . $d . '/' . $dates['year'] ) + 86400;
    					if ( $all )
    					    $data[] = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE hit_type = %s AND hit_date >= FROM_UNIXTIME(%d) AND hit_date <= FROM_UNIXTIME(%d)", 'conversion', $beginning, $ending ), OBJECT );
    					else
    					    $data[] = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE optin_id = %d AND hit_type = %s AND hit_date >= FROM_UNIXTIME(%d) AND hit_date <= FROM_UNIXTIME(%d)", $this->optin->ID, 'conversion', $beginning, $ending ), OBJECT );
    					$d++;
    				endwhile;
    			else :
    				$beginning  = strtotime( $i . '/1/' . $dates['year'] );
    				$ending		= strtotime( ($i + 1) . '/1/' . $dates['year'] );
    				if ( $all )
    				    $data[] = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE hit_type = %s AND hit_date >= FROM_UNIXTIME(%d) AND hit_date <= FROM_UNIXTIME(%d)", 'conversion', $beginning, $ending ), OBJECT );
    				else
    				    $data[] = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE optin_id = %d AND hit_type = %s AND hit_date >= FROM_UNIXTIME(%d) AND hit_date <= FROM_UNIXTIME(%d)", $this->optin->ID, 'conversion', $beginning, $ending ), OBJECT );
    			endif;
    			$i++;
    		endwhile;
    	endif;

    	return $data;

    }

}

// Instantiate the class.
global $optin_monster_account;
$optin_monster_account = new optin_monster_account();