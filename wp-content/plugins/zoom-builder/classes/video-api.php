<?php defined('ABSPATH') or die; # Don't ever load this file directly

/**
 * ZOOM_Builder_Video_API Class
 *
 * Modified version of the WPZOOM Video API
 *
 * @package ZOOM_Builder
 * @subpackage Video_API
 */

class ZOOM_Builder_Video_API {

	/**
	 * Fetches the video thumbnail from the given embed URL, saves it to the media library attached to the given post (if it is not already), and returns the newly saved thumbnail ID
	 */
	public static function fetch_save_return_video_thumbnail( $embed_url, $post_id ) {

		if ( empty( $embed_url ) || filter_var( $embed_url, FILTER_VALIDATE_URL ) === false || empty( $post_id ) || absint( $post_id ) < 1 )
			return false;

		$attachment_id = self::attach_remote_video_thumb( trim( $embed_url ), absint( $post_id ) );

		return $attachment_id !== false && absint( $attachment_id ) > 0 ? absint( $attachment_id ) : false;

	}

	/**
	 * Fetches the thumbnail URL based on a given video URL using oEmbed
	 */
	public static function fetch_video_thumbnail( $embed_url, $post_id ) {

		$embed_url = trim($embed_url);
		$post_id = intval($post_id);

		if(empty($embed_url) || filter_var($embed_url, FILTER_VALIDATE_URL) === false || empty($post_id) || $post_id < 1) return false;

		$url = self::convert_embed_url($embed_url);
		if($url === false) return false;

		require_once(ABSPATH . WPINC . '/class-oembed.php');
		$oembed = _wp_oembed_get_object();

		$provider = $oembed->discover($url);
		if(!$provider) return false;

		$data = $oembed->fetch($provider, $url);
		if(!$data) return false;

		$output['thumb_url'] = isset($data->thumbnail_url) && !empty($data->thumbnail_url) ? $data->thumbnail_url : '';

		return $output;

	}

	/**
	 * Downloads and attaches the given remote thumbnail to the given post and returns the ID
	 */
	public static function attach_remote_video_thumb( $thumb_url, $post_id ) {

		$thumb_url = trim($thumb_url);
		$post_id = intval($post_id);

		if(empty($thumb_url) || filter_var($thumb_url, FILTER_VALIDATE_URL) === false || empty($post_id) || $post_id < 1) return false;

		$fetch = self::fetch_video_thumbnail($thumb_url, $post_id);
		if($fetch === false || !isset($fetch['thumb_url']) || empty($fetch['thumb_url'])) return false;
		$url = $fetch['thumb_url'];

		if(false === ($id=self::thumb_attachment_exists($url, $post_id)) || $id < 1) {
			$id = self::media_sideload_image($url, $post_id);
			if(!is_wp_error($id) && $id !== false && $id > 0) {
				add_post_meta($id, '_wpz_original_thumb_url', $url);
				return $id;
			} else {
				return false;
			}
		}

		return $id !== false && $id > 0 ? $id : false;

	}

	/**
	 * Checks to see if the given thumbnail is already attached to the given post and returns the ID
	 */
	public static function thumb_attachment_exists( $thumb_url, $post_id ) {

		$thumb_url = trim($thumb_url);
		$post_id = intval($post_id);

		if(empty($thumb_url) || filter_var($thumb_url, FILTER_VALIDATE_URL) === false || empty($post_id) || $post_id < 1) return false;

		$db = get_children(array(
			'post_type' => 'attachment',
			'post_parent' => $post_id,
			'post_mime_type' => 'image',
			'meta_key' => '_wpz_original_thumb_url',
			'meta_value' => $thumb_url,
			'numberposts' => 1
		));

		if(empty($db)) return false;

		foreach($db as $attachment_id => $attachment) $id = $attachment_id;

		return !empty($id) && $id > 0 ? $id : false;

	}

	/**
	 * Takes a video embed code and returns the source URL
	 */
	public static function extract_url_from_embed( $embed_code ) {

		$embed_code = trim($embed_code);
		if(empty($embed_code)) return false;

		if(!class_exists('DOMDocument')) return false;
		libxml_use_internal_errors(true);
		$DOM = new DOMDocument;
		if($DOM->loadHTML($embed_code) === false) return false;

		$iframes = $DOM->getElementsByTagName('iframe');
		if(empty($iframes) || $iframes->length < 1) return false;

		$iframe = $iframes->item(0);
		if($iframe == null || !$iframe->hasAttributes()) return false;

		$src = trim($iframe->attributes->getNamedItem('src')->nodeValue);
		if(empty($src)) return false;
		$src = self::normalize_url_protocol($src);
		return !empty($src) && filter_var($src, FILTER_VALIDATE_URL) ? $src : false;

	}

	/**
	 * Takes an embed code URL and tries to figure out the oEmbed-compatible URL equivalent
	 * by extracting the domain and video ID
	 */
	public static function convert_embed_url( $url ) {

		$url = html_entity_decode(trim($url));
		if(empty($url)) return false;
		$url = self::normalize_url_protocol($url);
		if(empty($url) || filter_var($url, FILTER_VALIDATE_URL) === false) return false;

		$url_parts = parse_url($url);
		if($url_parts === false || empty($url_parts) || !is_array($url_parts) ||
		   !isset($url_parts['host']) || empty($url_parts['host']) ||
		   !isset($url_parts['path']) || empty($url_parts['path'])) return false;

		$host = preg_replace('#^www\.(.+\.)#i', '$1', $url_parts['host']);

		if($host == 'youtube.com' || $host == 'youtube-nocookie.com') {
			$id = trim(preg_replace('#^/embed/#i', '', $url_parts['path']));
			return !empty($id) ? 'http://youtube.com/watch?v=' . $id : false;
		} elseif($host == 'player.vimeo.com') {
			$id = trim(preg_replace('#^/video/#i', '', $url_parts['path']));
			return !empty($id) ? 'http://vimeo.com/' . $id : false;
		} elseif($host == 'dailymotion.com') {
			$id = trim(preg_replace('#^/embed/video/#i', '', $url_parts['path']));
			return !empty($id) ? 'http://dailymotion.com/video/' . $id : false;
		}

		return false;

	}

	/**
	 * Tries to make sure the given URL has a proper protocol, even if it was given without one (e.g. protocol-relative URLs)
	 */
	public static function normalize_url_protocol( $url ) {

		$url = trim($url);
		if(empty($url) || preg_match('#^https?://#i', $url)) return $url;
		return 'http' . (is_ssl() ? 's' : '') . '://' . preg_replace('#^https?://#i', '', ltrim($url, '/'));

	}

	/**
	 * Pretty much the same as the core media_sideload_image() function but with just a couple changes
	 */
	public static function media_sideload_image( $file, $post_id ) {

		if ( ! empty($file) ) {
			require_once(ABSPATH . 'wp-admin/includes/image.php');
			require_once(ABSPATH . 'wp-admin/includes/file.php');
			require_once(ABSPATH . 'wp-admin/includes/media.php');

			// Download file to temp location
			$tmp = download_url( $file );

			// Set variables for storage
			// fix file filename for query strings
			preg_match('/[^\?]+\.(jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|png|PNG)/', $file, $matches);
			$file_array['name'] = basename($matches[0]);
			$file_array['tmp_name'] = $tmp;

			// If error storing temporarily, unlink
			if ( is_wp_error( $tmp ) ) {
				@unlink($file_array['tmp_name']);
				$file_array['tmp_name'] = '';
			}

			// do the validation and storage stuff
			$id = media_handle_sideload( $file_array, $post_id, $desc );
			// If error storing permanently, unlink
			if ( is_wp_error($id) ) {
				@unlink($file_array['tmp_name']);
				return $id;
			}

			return $id;
		}

		return false;

	}

}