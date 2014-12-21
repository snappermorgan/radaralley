<?php
/**
 * Plugin Name: Radar Extras
 * Plugin URI: http://radaralley.com
 * Description: Miscellaneous functions that are site wide and not to be tied to a theme
 * Version: .1
 * Author: Clyde Morgan
 * Author URI: http://radaralley.com
 * License: None
 */


function send_receipt($data,$form,$api){
 try {
		if($data['email']!=""){
			
				$subject="Your Order Receipt";
				$headline="Here are the details of your order.";

				$products = array();
				$product = array("name"=>$data["event_name"],"price"=>$data["event_price"]);
				$products[]=$product;
				$receipt_data=array("total"=>$data["total"],"products"=>$products);
				$card = create_receipt_card($receipt_data);
				$content="<p>".$card;
				$content .= "<p>Please keep this for your record. If you need assistance with your order, please contact <a href='mailto:support@radaralley.com'>support@radaralley.com</a>".
				mymail_send( $headline, $content, $to = $data['email'], $replace = array("subject"=>$subject), $attachments = array(), $template = 'notification.html' );
			
		}
	
	} catch (Exception $e) {
		if(function_exists('ggah_error_notification'))
	        {
	            $email = '';    // leave blank to use Admin Email
	            $subject = '';  // leave blank to use Default
	            $message = '';  // leave blank to use Default

	            ggah_error_notification($email, $api, $subject, $message);
	        }
	}
}
function addto_webinar_rsvp ($data, $form, $api)
{
    try {
		if($data['email']!=""){
			
				$subject="Your Webinar Schedule";
				$headline="You are registered to attend the following webinar";
				$card = create_event_card($data);
				$content="<p>".$card;
				$content .= "<p>You will need to login with your website credentials in order to attend this webinar. You may visit the Webinar Event page prior to the start time to verify you have access. If you are not able to view this page, please notify us immediately at <a href='mailto:support@radaralley.com'>support@radaralley.com</a>".
				mymail_send( $headline, $content, $to = $data['email'], $replace = array("subject"=>$subject), $attachments = array(), $template = 'notification.html' );
                                
                               // mymail_subscribe( $data['email'], array("firstname"=>$data['firstname'],"lastname"=>$data['lastname']), array($data["autoresponder"]), false, true, NULL,'notification.html' );
			
		}
	
	} catch (Exception $e) {
		if(function_exists('ggah_error_notification'))
	        {
	            $email = '';    // leave blank to use Admin Email
	            $subject = '';  // leave blank to use Default
	            $message = '';  // leave blank to use Default

	            ggah_error_notification($email, $api, $subject, $message);
	        }
	}
}

function subscribe_member($data,$form, $api){

try {
	if($data['email']!=""){
		if(isset($data['list']) && ($data['list'] !="")) {

			$list_array = explode(",",$data['list']);
			mymail_subscribe( $data['email'], array("firstname"=>$data['firstname'],"lastname"=>$data['lastname']), $list_array, true, true, NULL, 'notification.html' );
		}
	}
	
} catch (Exception $e) {
	if(function_exists('ggah_error_notification'))
        {
            $email = '';    // leave blank to use Admin Email
            $subject = '';  // leave blank to use Default
            $message = '';  // leave blank to use Default

            ggah_error_notification($email, $api, $subject, $message);
        }
}

}


function create_event_card($data){
	$card = '<table border=0"><tr><td>Webinar: '.$data["event_name"].'</td></tr>';
	$card .= '<tr><td>Start Date and Time: '.date("F j, Y, g:i a", $data["event_start_date"]).'</td></tr>';
	$card .= '<tr><td>End Date and Time: '.date("F j, Y, g:i a", $data["event_end_date"]).'</td></tr>';
	$card .= '<tr><td>Webinar URL: '.$data["event_url"].'</td></tr>';
	$card .= '</table>';
	return $card;
}

function create_receipt_card($data){

	$products = $data["products"];
	$card = '<table border=1><thead><tr><th>Product Name</th><th>Price</th></tr></thead>';
	$card .='<tfoot><tr><td>Sum</td><td>'.$data["total"].'</td></tr></tfoot><tbody>';
	foreach($products as $product){


		$card .= '<tr><td>'.$product["name"].'</td>';
		$card .= '<td>Price: '.$product["price"].'</td></tr>';
	}
	$card .="</tbody>";
	return $card;
}

