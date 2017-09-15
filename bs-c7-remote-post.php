<?php
/*
Plugin Name: Contact Form 7 Remote Post
Plugin URI: http://blueside.nl/
Description: When submitting a Contact Form 7, POST it to a specified web service
Author: Marlon Peeters
Author URI: http://www.blueside.nl/
Text Domain: bs-c7-post
Version: 1.0
*/

// URL to send the HTTP request to
define('URL_TO_WEBSERVICE', 'url-to-web-service');

// Comma separated 
define('FIELDS_TO_SEND', 'fields-to-send');

// The JSON parameter to store the contents of the uploaded file
define('FILE_CONTENT', 'FileContent');

add_action('wpcf7_before_send_mail', 'wpcf7_to_web_service');

function wpcf7_to_web_service ($WPCF7_ContactForm)
{
    // Get Contact Form 7 data
    $submission = WPCF7_Submission::get_instance();
	$posted_data = $submission->get_posted_data();	   
    $uploaded_files = $submission->uploaded_files();

    // Check if the hidden URL field is given
    if(isset($posted_data[URL_TO_WEBSERVICE]))
    {
        $url = $posted_data[URL_TO_WEBSERVICE];

        $fields_to_send = explode(",", $posted_data[FIELDS_TO_SEND]);

        // Trim extra whitespaces
        for($i = 0; $i < count($fields_to_send); ++$i)
        {
            $fields_to_send[$i] = trim($fields_to_send[$i]);
        }
        
        $payload = [];
        foreach ($fields_to_send as $key)
        {
            $payload[$key] = $posted_data[$key];
        }

        if(isset($uploaded_files))
        {
            foreach ($uploaded_files as $filePath)
            {
                $payload[FILE_CONTENT] = base64_encode(file_get_contents($filePath));
            }
    
        }

        $response = wp_remote_post(
            $url,
            array(
                'method' => 'POST',
                'timeout' => 45,
                'httpversion' => '1.0',
                'blocking' => true,
                'headers' => array(
                    'Content-Type' => 'application/json'
                                   ),
                'body' => json_encode($payload),
                  ));
    }
}