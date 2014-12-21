<?php
/**
 * "Reports" tab class.
 *
 * @package      OptinMonster
 * @since        1.0.0
 * @author       Thomas Griffin <thomas@retyp.com>
 * @copyright    Copyright (c) 2013, Thomas Griffin
 * @license      http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Loads reports.
 *
 * @package      OptinMonster
 * @since        1.0.0
 */
class optin_monster_tab_reports {

	/**
	 * Prepare any base class properties.
	 *
	 * @since 1.0.0
	 */
	public $base, $user, $host, $protocol, $url, $db, $optin, $tab;

	/**
	 * Class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

        // Bring base class into scope.
        global $optin_monster_account, $wpdb;
        $this->base    = optin_monster::get_instance();
        $this->user    = wp_get_current_user();
        $this->optins  = get_posts( array( 'post_type' => 'optin', 'posts_per_page' => '-1' ) );
        $this->tab     = 'reports';
        $this->table   = $wpdb->prefix . 'om_hits_log';
		$this->qv	   = isset( $_GET['switch'] ) ? $_GET['switch'] : false;
		$this->optin   = false;

        add_action( 'optin_monster_tab_' . $this->tab, array( $this, 'do_tab' ) );
        add_action( 'admin_footer-' . $this->base->hook, array( $this, 'footer_scripts' ) );

    }

	/**
	 * Outputs the tab content.
	 *
	 * @since 1.0.0
	 */
	public function do_tab() {

	    // Load scripts.
	    wp_enqueue_script( 'om-flot', plugins_url( 'inc/js/jquery.flot.min.js', $this->base->file ), array( 'jquery' ), $this->base->version, true );
		wp_enqueue_script( 'om-flot-time', plugins_url( 'inc/js/jquery.flot.time.js', $this->base->file ), array( 'jquery', 'om-flot' ), $this->base->version, true );

		// Load reports view based on query.
		if ( ! $this->qv || 'all' == $this->qv ) :
			$this->post_content();
		else :
		    $optin = get_posts( array( 'post_type' => 'optin', 'name' => $this->qv, 'posts_per_page' => '1' ) );
		    $this->optin = $optin[0];
		    $this->single_post_content();
		endif;

	}

	public function footer_scripts() {

    	?>
    	<script type="text/javascript">
    	    jQuery(document).ready(function($){
        	    $('#om-report-switcher').attr('action', $('#om-report-switch-box option:selected').data('action'));
        	    $('#om-report-switch-box').on('change', function(){
            	    $('#om-report-switcher').attr('action', $(this).find(':selected').data('action'));
        	    });
    	    });
    	</script>
    	<?php

	}

	/**
	 * Loads the post content for the all-optin reports view.
	 *
	 * @since 1.0.0
	 */
	public function post_content() {

        $this->reports_graph( true );

	}

	/**
	 * Loads the post content for the single report view.
	 *
	 * @since 1.0.0
	 */
	public function single_post_content() {

        $this->reports_graph();

	}

	public function reports_graph_controls( $all = false ) {
    	$date_options = array(
    		'today' 	    => __( 'Today', 'om' ),
    		'this_week' 	=> __( 'This Week', 'om' ),
    		'last_week' 	=> __( 'Last Week', 'om' ),
    		'this_month' 	=> __( 'This Month', 'om' ),
    		'last_month' 	=> __( 'Last Month', 'om' ),
    		'this_quarter'	=> __( 'This Quarter', 'om' ),
    		'last_quarter'	=> __( 'Last Quarter', 'om' ),
    		'this_year'		=> __( 'This Year', 'om' ),
    		'last_year'		=> __( 'Last Year', 'om' )
    	);
    	$dates = $this->get_report_dates();

    	$optins = get_posts( array( 'post_type' => 'optin', 'posts_per_page' => -1 ) );


    	?>
    	<div class="om-clear">
        	<form id="om-graphs-filter" method="get">
			    <h5 class="blue">Filter Report Data</h5>
		       	<select id="om-graphs-date-options" name="range">
		       		<?php
		       		foreach ( $date_options as $key => $option ) {
		       			echo '<option value="' . esc_attr( $key ) . '" ' . selected( $key, $dates['range'] ) . '>' . esc_html( $option ) . '</option>';
		       		}
		       		?>
		       	</select>

			    <input type="hidden" name="om_action" value="filter_reports" />
			    <input type="hidden" name="page" value="optin-monster" />
			    <input type="hidden" name="tab" value="<?php echo $this->tab; ?>" />
			    <?php if ( isset( $_GET['switch'] ) ) echo '<input type="hidden" name="switch" value="' . stripslashes( $_GET['switch'] ) . '" />'; else echo '<input type="hidden" name="switch" value="all" />';?>
		       	<input type="submit" class="button green" value="<?php _e( 'Filter', 'om' ); ?>"/>
        	</form>
        	<?php if ( $optins ) : ?>
        	<form id="om-report-switcher" method="get">
    			<div class="report-switcher">
    			    <h5 class="blue">Switch Report View</h5>
    			    <input type="hidden" name="page" value="optin-monster" />
    			    <input type="hidden" name="tab" value="<?php echo $this->tab; ?>" />
    			    <select id="om-report-switch-box" name="switch">
                    <option data-action="<?php echo add_query_arg( array( 'page' => 'optin-monster', 'tab' => $this->tab ), admin_url( 'admin.php' ) ); ?>" value="all">All</option>
    			    <?php foreach ( $optins as $optin ) : ?>
    			        <?php if ( $all ) : ?>
    			            <option data-action="<?php echo add_query_arg( array( 'page' => 'optin-monster', 'tab' => $this->tab, 'switch' => $optin->post_name ), admin_url( 'admin.php' ) ); ?>" value="<?php echo esc_attr( $optin->post_name ); ?>"><?php echo esc_html( ( ! empty( $optin->post_title ) ? $optin->post_title : $optin->post_name ) ); ?></option>
                        <?php else : ?>
                            <option data-action="<?php echo add_query_arg( array( 'page' => 'optin-monster', 'tab' => $this->tab, 'switch' => $optin->post_name ), admin_url( 'admin.php' ) ); ?>" value="<?php echo esc_attr( $optin->post_name ); ?>"<?php selected( $optin->post_name, $this->qv ); ?>><?php echo esc_html( ( ! empty( $optin->post_title ) ? $optin->post_title : $optin->post_name ) ); ?></option>
                        <?php endif; ?>
    			    <?php endforeach; ?>
    			    </select>
    		       	<input type="submit" class="button green" value="<?php _e( 'Switch', 'om' ); ?>"/>
    			</div>
        	</form>
        	<?php endif; ?>
    	</div>
    	<?php

    }

    public function reports_graph( $all = false ) {

    	$data = $this->get_impressions_data( $all );
    	$conv = $this->get_conversions_data( $all );
    	$title = empty( $_GET['switch'] ) && empty( $_GET['range'] ) ? 'All Optins' : '';
    	$title = isset( $_GET['switch'] ) && empty( $_GET['range'] ) ? 'Today' : $title;
    	$title = isset( $_GET['range'] ) ? ucwords( str_replace( '_', ' ', $_GET['range'] ) ) : $title;
    	$person_title = $title;
    	if ( 'All Optins' !== $title )
    	    $person_title = $title . '\'s';

    	ob_start(); ?>
    	<div class="alert alert-success"><p><strong>Report data is refreshed every 30 minutes. For the latest impression and conversion stats, visit the Overview screen.</strong></p></div>
    	<script type="text/javascript">
    	   jQuery(document).ready(function($){
    	   	   var impressions_data = {
    		   	   data: [<?php echo $data['plot']; ?>],
    		   	   label: "Impressions",
    		   	   id: 'impressions'
    	   	   },
    	   	   conversions_data = {
    		   	   data: [<?php echo $conv['plot']; ?>],
    		   	   label: "Conversions",
    		   	   id: 'conversions'
    	   	   },
    	   	   opts = {
    	   	   	   	series: {
                       lines: { show: true },
                       points: { show: true }
                	},
                	grid: {
               			show: true,
    					aboveData: false,
    					color: '#ccc',
    					backgroundColor: '#fff',
    					borderWidth: 2,
    					borderColor: '#ccc',
    					clickable: true,
    					hoverable: true
               		},
               		xaxis: {
    	   				mode: 'time',
    	   				timeFormat: '<?php echo $data['format']; ?>',
    	   				minTickSize: [1, '<?php echo $data['tick']; ?>']
       				}
       	   	   };
    		   $.plot($("#om_graph_report"),[impressions_data, conversions_data], opts);
    		   // add some hovering logic to each point...
    		   var previousPoint = null;
    		   $("#om_graph_report").bind("plothover", function(event, pos, item){
    		   		$("#x").text(pos.x.toFixed(2));
    		        $("#y").text(pos.y.toFixed(2));
    	            if (item) {
    	                if (previousPoint != item.datapoint) {
    	                    previousPoint = item.datapoint;
    	                    $("#tooltip").remove();
    	                    var x = item.datapoint[0].toFixed(2), y = item.datapoint[1].toFixed(2);
    	                    showTooltip(item.pageX, item.pageY, y.replace( '.00', '' ) + ' ' + item.series.label);
    	                }
    	            }
    	            else {
    	                $("#tooltip").remove();
    	                previousPoint = null;
    	            }
    		    });

    		    // show the tooltip
    		    function showTooltip(x, y, contents) {
    		        $('<div id="tooltip">' + contents + '</div>').css( {
    		            position: 'absolute',
    		            display: 'none',
    		            top: y - 35,
    		            left: x + 5,
    		            border: '1px solid #fdd',
    		            padding: '2px',
    		            'background-color': '#fee',
    		            opacity: 0.80
    		        }).appendTo("body").show();
    		    }
    	   });
        </script>

        <?php if ( $all ) : ?>
        <h3 style="margin-bottom: 40px;">Impression and Conversion Stats for <span class="blue">All Optins</span></h3>
        <?php else : ?>
    	<h3 style="margin-bottom: 40px;">Impression and Conversion Stats for <span class="blue"><?php echo ( ! empty( $this->optin->post_title ) ? $this->optin->post_title : $this->optin->post_name ); ?></span></h3>
    	<?php endif; ?>
    		<div class="om-clear">
    			<?php $this->reports_graph_controls( $all ); ?>
    			<div id="om_graph_report" class="om-clearfix" style="height: 350px; margin: 45px 0 25px;"></div>
    			<div class="om-clear">
        			<div class="report-stats">
            			<h4 style="margin-bottom: 25px;"><?php echo $person_title; ?> Stats Breakdown</h4>
            			<p class="no-margin" style="padding-bottom: 5px;">Total <strong>impressions</strong> for <?php echo strtolower( $title ); ?>: <strong><?php echo number_format( (int) $data['total'] ); ?></strong></p>
            			<p class="no-margin" style="padding-bottom: 5px;">Total <strong>conversions</strong> for <?php echo strtolower( $title ); ?>: <strong><?php echo number_format( (int) $conv['total'] ); ?></strong></p>
            			<p class="no-margin" style="padding-bottom: 5px;">Percentage of <strong>conversions</strong> for <?php echo strtolower( $title ); ?>: <strong><?php if ( 0 == $data['total'] ) echo '0.00'; else echo number_format( ($conv['total']/$data['total']) * 100, 2 ); ?>%</strong></p>
        			</div>
        			<div class="report-download">
        			    <h4 style="margin-bottom: 25px;">Export <?php echo $person_title; ?> Conversions to CSV</h4>
        			    <?php if ( 0 == $conv['total'] ) : ?>
        			    <p class="red">There is no conversion data to download at this time.</p>
        			    <?php else : ?>
        			    <a class="button button-primary button-large green" href="<?php echo add_query_arg( array( 'page' => 'optin-monster', 'tab' => $this->tab, 'switch' => $this->qv, 'range' => isset( $_GET['range'] ) ? $_GET['range'] : 'today', 'download_csv' => true ), admin_url( 'admin.php' ) ); ?>" title="Download Report Data to CSV">Download <?php echo $person_title; ?> Conversions</a>
        			    <?php endif; ?>
        			</div>
    			</div>
    		</div>
    	<?php
    	echo ob_get_clean();

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

    public function get_impressions_data( $all = false ) {

    	// Retrieve the queried dates
    	$dates = $this->get_report_dates();
    	global $wpdb;
    	$table_name = $this->table;
    	$ret = '';

    	// Determine graph options
    	switch( $dates['range'] ) :
    		case 'today' :
    			$time_format 	= '%d/%b';
    			$tick_size		= 'hour';
    			$day_by_day		= true;
    			break;
    		case 'last_year' :
    			$time_format 	= '%b';
    			$tick_size		= 'month';
    			$day_by_day		= false;
    			break;
    		case 'this_year' :
    			$time_format 	= '%b';
    			$tick_size		= 'month';
    			$day_by_day		= false;
    			break;
    		case 'last_quarter' :
    			$time_format	= '%b';
    			$tick_size		= 'month';
    			$day_by_day 	= false;
    			break;
    		case 'this_quarter' :
    			$time_format	= '%b';
    			$tick_size		= 'month';
    			$day_by_day 	= false;
    			break;
    		default:
    			$time_format 	= '%d/%b'; 	// Show days by default
    			$tick_size		= 'day'; 	// Default graph interval
    			$day_by_day 	= true;
    			break;
    	endswitch;

    	$hits_this_period = 0;
    	$cons_this_period = 0;

    	if ( 'today' == $dates['range'] ) :
    		// Hourly impression report.
    		$hour = 0;
    		while ( $hour <= 23 ) :
    			$beginning = strtotime( 'midnight', time() ) + ($hour * 3600);
    			$ending    = strtotime( 'midnight', time() ) + (($hour + 1) * 3600);
    			if ( $all ) {
    			    if ( false === ( $hits = get_transient( 'ohi_all_' . $dates['range'] . '_' . $hour ) ) ) {
            			$hits = $wpdb->query( $wpdb->prepare( "SELECT hit_id FROM $table_name WHERE hit_type = %s AND hit_date >= FROM_UNIXTIME(%d) AND hit_date <= FROM_UNIXTIME(%d)", 'impression', $beginning, $ending ) );
            			set_transient( 'ohi_all_' . $dates['range'] . '_' . $hour, $hits, 1800 );
            		}
    			} else {
    			    if ( false === ( $hits = get_transient( 'ohi_' . $this->optin->ID . '_' . $dates['range'] . '_' . $hour ) ) ) {
            			$hits = $wpdb->query( $wpdb->prepare( "SELECT hit_id FROM $table_name WHERE optin_id = %d AND hit_type = %s AND hit_date >= FROM_UNIXTIME(%d) AND hit_date <= FROM_UNIXTIME(%d)", $this->optin->ID, 'impression', $beginning, $ending ) );
            			set_transient( 'ohi_' . $this->optin->ID . '_' . $dates['range'] . '_' . $hour, $hits, 1800 );
            		}
                }
    			$hits_this_period += $hits;
    			$date = mktime( $hour, 0, 0, date( 'n' ), $dates['day'], $dates['year'] );
    			$ret .= '[' . $date * 1000 . ', ' . $hits . '],';
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
    			if ( $all ) {
    			    if ( false === ( $hits = get_transient( 'ohi_all_' . $dates['range'] . '_' . $day ) ) ) {
            			$hits = $wpdb->query( $wpdb->prepare( "SELECT hit_id FROM $table_name WHERE hit_type = %s AND hit_date >= FROM_UNIXTIME(%d) AND hit_date <= FROM_UNIXTIME(%d)", 'impression', $beginning, $ending ) );
            			set_transient( 'ohi_all_' . $dates['range'] . '_' . $day, $hits, 1800 );
            		}
    			} else {
    			    if ( false === ( $hits = get_transient( 'ohi_' . $this->optin->ID . '_' . $dates['range'] . '_' . $day ) ) ) {
            			$hits = $wpdb->query( $wpdb->prepare( "SELECT hit_id FROM $table_name WHERE optin_id = %d AND hit_type = %s AND hit_date >= FROM_UNIXTIME(%d) AND hit_date <= FROM_UNIXTIME(%d)", $this->optin->ID, 'impression', $beginning, $ending ) );
            			set_transient( 'ohi_' . $this->optin->ID . '_' . $dates['range'] . '_' . $day, $hits, 1800 );
            		}
                }
    			$hits_this_period += $hits;
    			$date = mktime( 0, 0, 0, $month, $day, $dates['year'] );
    			$ret .= '[' . $date * 1000 . ', ' . $hits . '],';
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
    					if ( $all ) {
            			    if ( false === ( $hits = get_transient( 'ohi_all_' . $dates['range'] . '_' . $i . '_' . $d ) ) ) {
                    			$hits = $wpdb->query( $wpdb->prepare( "SELECT hit_id FROM $table_name WHERE hit_type = %s AND hit_date >= FROM_UNIXTIME(%d) AND hit_date <= FROM_UNIXTIME(%d)", 'impression', $beginning, $ending ) );
                    			set_transient( 'ohi_all_' . $dates['range'] . '_' . $i . '_' . $d, $hits, 1800 );
                    		}
            			} else {
            			    if ( false === ( $hits = get_transient( 'ohi_' . $this->optin->ID . '_' . $dates['range'] . '_' . $i . '_' . $d ) ) ) {
                    			$hits = $wpdb->query( $wpdb->prepare( "SELECT hit_id FROM $table_name WHERE optin_id = %d AND hit_type = %s AND hit_date >= FROM_UNIXTIME(%d) AND hit_date <= FROM_UNIXTIME(%d)", $this->optin->ID, 'impression', $beginning, $ending ) );
                    			set_transient( 'ohi_' . $this->optin->ID . '_' . $dates['range'] . '_' . $i . '_' . $d, $hits, 1800 );
                    		}
                        }
    					$hits_this_period += $hits;
    					$date = mktime( 0, 0, 0, $i, $d, $dates['year'] );
    					$ret .= '[' . $date * 1000 . ', ' . $hits . '],';
    					$d++;
    				endwhile;
    			else :
    				$beginning  = strtotime( $i . '/1/' . $dates['year'] );
    				$ending		= strtotime( ($i + 1) . '/1/' . $dates['year'] );
    				if ( $all ) {
        			    if ( false === ( $hits = get_transient( 'ohi_all_' . $dates['range'] . '_' . $i ) ) ) {
                			$hits = $wpdb->query( $wpdb->prepare( "SELECT hit_id FROM $table_name WHERE hit_type = %s AND hit_date >= FROM_UNIXTIME(%d) AND hit_date <= FROM_UNIXTIME(%d)", 'impression', $beginning, $ending ) );
                			set_transient( 'ohi_all_' . $dates['range'] . '_' . $i, $hits, 1800 );
                		}
        			} else {
        			    if ( false === ( $hits = get_transient( 'ohi_' . $this->optin->ID . '_' . $dates['range'] . '_' . $i ) ) ) {
                			$hits = $wpdb->query( $wpdb->prepare( "SELECT hit_id FROM $table_name WHERE optin_id = %d AND hit_type = %s AND hit_date >= FROM_UNIXTIME(%d) AND hit_date <= FROM_UNIXTIME(%d)", $this->optin->ID, 'impression', $beginning, $ending ) );
                			set_transient( 'ohi_' . $this->optin->ID . '_' . $dates['range'] . '_' . $i, $hits, 1800 );
                		}
                    }
    				$hits_this_period += $hits;
    				$date = mktime( 0, 0, 0, $i, 1, $dates['year'] );
    				$ret .= '[' . $date * 1000 . ', ' . $hits . '],';
    			endif;
    			$i++;
    		endwhile;
    	endif;

    	return array( 'plot' => $ret, 'total' => $hits_this_period, 'format' => $time_format, 'tick' => $tick_size );

    }

    public function get_conversions_data( $all = false ) {

    	// Retrieve the queried dates
    	$dates = $this->get_report_dates();
    	global $wpdb;
    	$table_name = $this->table;
    	$ret = '';
    	$time = time();

    	// Determine graph options
    	switch( $dates['range'] ) :
    		case 'today' :
    			$time_format 	= '%d/%b';
    			$tick_size		= 'hour';
    			$day_by_day		= true;
    			break;
    		case 'last_year' :
    			$time_format 	= '%b';
    			$tick_size		= 'month';
    			$day_by_day		= false;
    			break;
    		case 'this_year' :
    			$time_format 	= '%b';
    			$tick_size		= 'month';
    			$day_by_day		= false;
    			break;
    		case 'last_quarter' :
    			$time_format	= '%b';
    			$tick_size		= 'month';
    			$day_by_day 	= false;
    			break;
    		case 'this_quarter' :
    			$time_format	= '%b';
    			$tick_size		= 'month';
    			$day_by_day 	= false;
    			break;
    		default:
    			$time_format 	= '%d/%b'; 	// Show days by default
    			$tick_size		= 'day'; 	// Default graph interval
    			$day_by_day 	= true;
    			break;
    	endswitch;

    	$hits_this_period = 0;

    	if ( 'today' == $dates['range'] ) :
    		// Hourly impression report.
    		$hour = 0;
    		while ( $hour <= 23 ) :
    			$beginning = strtotime( 'midnight', $time ) + ($hour * 3600);
    			$ending    = strtotime( 'midnight', $time ) + (($hour + 1) * 3600);
    			if ( $all ) {
    			    if ( false === ( $hits = get_transient( 'ohc_all_' . $dates['range'] . '_' . $hour ) ) ) {
            			$hits = $wpdb->query( $wpdb->prepare( "SELECT hit_id FROM $table_name WHERE hit_type = %s AND hit_date >= FROM_UNIXTIME(%d) AND hit_date <= FROM_UNIXTIME(%d)", 'conversion', $beginning, $ending ) );
            			set_transient( 'ohc_all_' . $dates['range'] . '_' . $hour, $hits, 1800 );
            		}
    			} else {
    			    if ( false === ( $hits = get_transient( 'ohc_' . $this->optin->ID . '_' . $dates['range'] . '_' . $hour ) ) ) {
            			$hits = $wpdb->query( $wpdb->prepare( "SELECT hit_id FROM $table_name WHERE optin_id = %d AND hit_type = %s AND hit_date >= FROM_UNIXTIME(%d) AND hit_date <= FROM_UNIXTIME(%d)", $this->optin->ID, 'conversion', $beginning, $ending ) );
            			set_transient( 'ohc_' . $this->optin->ID . '_' . $dates['range'] . '_' . $hour, $hits, 1800 );
            		}
                }
    			$hits_this_period += $hits;
    			$date = mktime( $hour, 0, 0, date( 'n' ), $dates['day'], $dates['year'] );
    			$ret .= '[' . $date * 1000 . ', ' . $hits . '],';
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
    			if ( $all ) {
    			    if ( false === ( $hits = get_transient( 'ohc_all_' . $dates['range'] . '_' . $day ) ) ) {
            			$hits = $wpdb->query( $wpdb->prepare( "SELECT hit_id FROM $table_name WHERE hit_type = %s AND hit_date >= FROM_UNIXTIME(%d) AND hit_date <= FROM_UNIXTIME(%d)", 'conversion', $beginning, $ending ) );
            			set_transient( 'ohc_all_' . $dates['range'] . '_' . $day, $hits, 1800 );
            		}
    			} else {
    			    if ( false === ( $hits = get_transient( 'ohc_' . $this->optin->ID . '_' . $dates['range'] . '_' . $day ) ) ) {
            			$hits = $wpdb->query( $wpdb->prepare( "SELECT hit_id FROM $table_name WHERE optin_id = %d AND hit_type = %s AND hit_date >= FROM_UNIXTIME(%d) AND hit_date <= FROM_UNIXTIME(%d)", $this->optin->ID, 'conversion', $beginning, $ending ) );
            			set_transient( 'ohc_' . $this->optin->ID . '_' . $dates['range'] . '_' . $day, $hits, 1800 );
            		}
                }
    			$hits_this_period += $hits;
    			$date = mktime( 0, 0, 0, $month, $day, $dates['year'] );
    			$ret .= '[' . $date * 1000 . ', ' . $hits . '],';
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
    					if ( $all ) {
            			    if ( false === ( $hits = get_transient( 'ohc_all_' . $dates['range'] . '_' . $i . '_' . $d ) ) ) {
                    			$hits = $wpdb->query( $wpdb->prepare( "SELECT hit_id FROM $table_name WHERE hit_type = %s AND hit_date >= FROM_UNIXTIME(%d) AND hit_date <= FROM_UNIXTIME(%d)", 'conversion', $beginning, $ending ) );
                    			set_transient( 'ohc_all_' . $dates['range'] . '_' . $i . '_' . $d, $hits, 1800 );
                    		}
            			} else {
            			    if ( false === ( $hits = get_transient( 'ohc_' . $this->optin->ID . '_' . $dates['range'] . '_' . $i . '_' . $d ) ) ) {
                    			$hits = $wpdb->query( $wpdb->prepare( "SELECT hit_id FROM $table_name WHERE optin_id = %d AND hit_type = %s AND hit_date >= FROM_UNIXTIME(%d) AND hit_date <= FROM_UNIXTIME(%d)", $this->optin->ID, 'conversion', $beginning, $ending ) );
                    			set_transient( 'ohc_' . $this->optin->ID . '_' . $dates['range'] . '_' . $i . '_' . $d, $hits, 1800 );
                    		}
                        }
    					$hits_this_period += $hits;
    					$date = mktime( 0, 0, 0, $i, $d, $dates['year'] );
    					$ret .= '[' . $date * 1000 . ', ' . $hits . '],';
    					$d++;
    				endwhile;
    			else :
    				$beginning  = strtotime( $i . '/1/' . $dates['year'] );
    				$ending		= strtotime( ($i + 1) . '/1/' . $dates['year'] );
    				if ( $all ) {
        			    if ( false === ( $hits = get_transient( 'ohc_all_' . $dates['range'] . '_' . $i ) ) ) {
                			$hits = $wpdb->query( $wpdb->prepare( "SELECT hit_id FROM $table_name WHERE hit_type = %s AND hit_date >= FROM_UNIXTIME(%d) AND hit_date <= FROM_UNIXTIME(%d)", 'conversion', $beginning, $ending ) );
                			set_transient( 'ohc_all_' . $dates['range'] . '_' . $i, $hits, 1800 );
                		}
        			} else {
        			    if ( false === ( $hits = get_transient( 'ohc_' . $this->optin->ID . '_' . $dates['range'] . '_' . $i ) ) ) {
                			$hits = $wpdb->query( $wpdb->prepare( "SELECT hit_id FROM $table_name WHERE optin_id = %d AND hit_type = %s AND hit_date >= FROM_UNIXTIME(%d) AND hit_date <= FROM_UNIXTIME(%d)", $this->optin->ID, 'conversion', $beginning, $ending ) );
                			set_transient( 'ohc_' . $this->optin->ID . '_' . $dates['range'] . '_' . $i, $hits, 1800 );
                		}
                    }
    				$hits_this_period += $hits;
    				$date = mktime( 0, 0, 0, $i, 1, $dates['year'] );
    				$ret .= '[' . $date * 1000 . ', ' . $hits . '],';
    			endif;
    			$i++;
    		endwhile;
    	endif;

    	return array( 'plot' => $ret, 'total' => $hits_this_period, 'format' => $time_format, 'tick' => $tick_size );

    }

}

// Initialize the class.
$optin_monster_tab_reports = new optin_monster_tab_reports();