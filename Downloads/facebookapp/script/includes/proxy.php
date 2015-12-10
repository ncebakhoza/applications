<?php
// Facebook Multi Page/Group Poster v2.2
// Created by Novartis (Safwan)

if ( count( get_included_files() ) == 1 )
    die();
$proxyUrl = decrypt( $_GET[ 'proxyurl' ] );
$ext      = substr( $proxyUrl, -3 ); // get the extension from filename
switch ( $ext ) { // set the MIME type according to the extension obtained above.
    case 'jpg':
        $mime = 'image/jpeg';
        break;
    case 'gif':
        $mime = 'image/gif';
        break;
    case 'png':
        $mime = 'image/png';
        break;
    default:
        $mime = false;
        break;
}
if ( !$mime && ( strpos( $proxyUrl, "http" ) !== false ) ) {
    $proxyUrl = urldecode( $proxyUrl );
    $mime = 'image/jpeg';
}
// if a valid MIME type exists, display the image by sending appropriate headers and streaming the file
if ( $mime ) {
    $opts    = array(
         'http' => array(
            'method' => "GET",
            'header' => array(
                 "User-Agent: Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/31.0.1650.57 Safari/537.36",
                "Accept-Language:en-US,en;q=0.8",
                "Cache-Control:max-age=0",
                "Referrer: " . $proxyUrl 
            ) 
        ) 
    );
    $context = stream_context_create( $opts );
    $file    = fopen( $proxyUrl, 'rb', false, $context );
    if ( $file ) {
        header( 'Content-type: ' . $mime );
        fpassthru( $file );
        exit;
    }
}
die();
?>