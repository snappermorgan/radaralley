<?php
/*
 * This file is part of the Designmodo WordPress Plugin.
 *
 * (c) Designmodo Inc. <info@designmodo.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * This script provides async upload of media files to the WordPress.
 */
$result = array(
    'error' => array(
        'code' => 97245,
        'message' => 'Upload error.'
    )
);
try {
    if (! empty($_FILES)) {
        $allowedMimeTypes = array(
            'image/jpeg',
            'image/pjpeg',
            'image/png',
            'image/x-png',
            'image/gif',
            'image/svg+xml',
            'video/mp4',
            'video/ogg',
            'video/webm'
        );
        if (!in_array($_FILES['file']['type'], $allowedMimeTypes)) {
            throw new Exception($_FILES['file']['type'] . ' is not allowed file type.', 813223);
        }

        require_once (dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php');
        require_once (ABSPATH . 'wp-admin/includes/admin.php');
        if(!current_user_can('upload_files')) {
            throw new Exception('You are have no the upload_files permission.', 876554);
        }
        $id = media_handle_upload('file', 0);
        unset($_FILES);
        if (is_wp_error($id)) {
            throw new Exception('There was an error uploading your file. Make sure that file is png/jpeg/gif and not greater than ' . ini_get('upload_max_filesize'), 323335);
        }
        $maxWidth = ! empty($_REQUEST['max_width']) ? $_REQUEST['max_width'] : null;
        $maxHeight = ! empty($_REQUEST['max_height']) ? $_REQUEST['max_height'] : null;
        $crop = ! empty($_REQUEST['crop']) ? true : false;
        if ($maxWidth || $maxHeight) {
            $image = wp_get_image_editor((get_attached_file($id))); // Return an implementation that extends <tt>WP_Image_Editor</tt>
            if (! is_wp_error($image)) {
                //wp_delete_attachment($id, true);
                $image->resize($maxWidth, $maxHeight, $crop);
                $imageData = $image->save($image->generate_filename($image->get_suffix() . '-' . rand(10000, 99999)));
                $wp_upload_dir = wp_upload_dir();
                $attachment = array(
                    'guid' => $wp_upload_dir['url'] . '/' . basename($imageData['path']),
                    'post_mime_type' => $imageData['mime-type'],
                    'post_title' => preg_replace('/\.[^.]+$/', '', basename($imageData['path'])),
                    'post_content' => '',
                    'post_status' => 'inherit'
                );
                $id = wp_insert_attachment($attachment, $imageData['path']);
                $attachData = wp_generate_attachment_metadata($id, $imageData['path']);
                wp_update_attachment_metadata($id, $attachData);
            }
        }

        $result = array(
            'result' => str_replace(content_url(), '../..', wp_get_attachment_url($id))
        );
    }
} catch (Exception $e) {
    $result = array(
        'error' => array(
            'code' => $e->getCode(),
            'message' => $e->getMessage()
        )
    );
}
echo json_encode($result);