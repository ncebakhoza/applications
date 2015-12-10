<?php
// Facebook Multi Page/Group Poster v2.2
// Created by Novartis (Safwan)

ob_start();
error_reporting( E_ALL );
ini_set( 'display_errors', '0' ); //Set to 1 to allow showing errors, helpful for debugging.

if ( file_exists( 'config.php' ) )
    require_once( 'config.php' );
else
    require_once( 'functions.php' );
require_once( 'includes/RestrictCSRF.php' );

//DB existence check, Creates DB files if not present
if ( !file_exists( 'params.php' ) )
    require( 'includes/createdbs.php' );
else
    require_once( 'params.php' );
if ( !file_exists( $dbName . '-settings.db' ) || !file_exists( $dbName . '-logs.db' ) || !file_exists( $dbName . '-crons.db' ) || !file_exists( $dbName . '-users.db' ) )
    require( 'includes/createdbs.php' );

readSettings();

if ( ( isset( $_GET[ 'lang' ] ) || isset( $_COOKIE[ 'FBMPGPLang' ] ) ) && file_exists( 'lang/' . ( isset( $_GET[ 'lang' ] ) ? $_GET[ 'lang' ] : $_COOKIE[ 'FBMPGPLang' ] ) . '-lang.php' ) )
    require_once( 'lang/' . ( isset( $_GET[ 'lang' ] ) ? $_GET[ 'lang' ] : $_COOKIE[ 'FBMPGPLang' ] ) . '-lang.php' );
else
    require_once( 'lang/' . $adminOptions[ 'lang' ] . '-lang.php' );

if ( $adminOptions[ 'scriptTitle' ] != "" )
    $lang['Script Title'] = $adminOptions[ 'scriptTitle' ];
if ( $adminOptions[ 'scriptHeading' ] != "" )
    $lang['Heading'] = $adminOptions[ 'scriptHeading' ];

if ( isset( $_GET[ 'lang' ] ) && file_exists( 'lang/' . $_GET[ 'lang' ] . '-lang.php' ) ) {
    setcookie( "FBMPGPLang", $_GET[ 'lang' ], time() + 86400 * 365 );
    $_COOKIE[ 'FBMPGPLang' ] = $_GET[ 'lang' ];
}
if ( isset( $_COOKIE[ 'FBMPGPLang' ] ) && !file_exists( 'lang/' . $_COOKIE[ 'FBMPGPLang' ] . '-lang.php' ) ) {
    setcookie( "FBMPGPLang", '', time() - 50000 );
    unset( $_COOKIE[ 'FBMPGPLang' ] );
}

//Is this a logout request?
if ( isset( $_GET[ 'logout' ] ) ) {
    setcookie( "FBMPGPLogin", '', time() - 50000 );
    setcookie( "FBMPGPUserID", '', time() - 50000 );
    header( "Location: ./" );
    exit;
}

//Is this a logged in user show help/documentation request?
if ( isset( $_GET[ 'showhelp' ] ) ) {
    showHelp();
}

//At this point we check all Input for XSS/SQLInjection attack, terminate execution if found!
xssSqlClean();

//Is this an Image Proxy Request?
if ( isset( $_GET[ 'proxyurl' ] ) ) {
    require_once( 'includes/proxy.php' );
}

// initialize Facebook class using your own Facebook App credentials
require_once( "src/facebook.php" );
$fb = new Facebook( $config );

// Now we must check if the user is authorized. User might be logging in, authorizing the script or it may be a FB redirect request during the authorization process.

// So, first we check if we are on FB redirect during the authorization process.
if ( isset( $_GET[ 'code' ] ) ) {
    require_once( 'includes/fbauth.php' );
} elseif ( isset( $_POST[ 'un' ] ) && isset( $_POST[ 'pw' ] ) ) {
    // User is logging in...    
    $user        = strtolower( $_POST[ 'un' ] );
    $hashed_pass = md5( $_POST[ 'pw' ] );
    checkLogin( $user, $hashed_pass );
    if ( isset( $_POST[ 'rem' ] ) ) { // If user ticked 'Remember Me' while logging in
        $t = time() + 86400 * 365;
    } else {
        $t = 0;
    }
    setcookie( 'FBMPGPLogin', $cookie, $t );
    if ( $loggedIn )
        setcookie( 'FBMPGPUserID', $userId, $t );
} elseif ( isset( $_POST[ 'suun' ] ) ) {
    require_once( 'includes/signup.php' );
} elseif ( isset( $_COOKIE[ 'FBMPGPLogin' ] ) ) {
    // Authorization Check
    $cookie = base64_decode( $_COOKIE[ 'FBMPGPLogin' ] );
    if ( isset( $_COOKIE[ 'FBMPGPUserID' ] ) )
        $uid = $_COOKIE[ 'FBMPGPUserID' ];
    else
        $uid = 0;
    $cookie = base64_decode( $_COOKIE[ 'FBMPGPLogin' ] );
    list( $user, $hashed_pass ) = explode( ':', $cookie );
    checkLogin( $user, $hashed_pass, $uid );
} else {
    // No authorization found. Show login box
    showLogin();
}

// Now the user must be logged in already for the below code to be executed

// Access Token Checking
if ( $userToken != "" ) {
    require_once( 'includes/fbtoken.php' );
} else {
    $message = '<div>' . $lang['Not Authorized'] . '.<br />
            ' . $lang['Click Authorize'] . '.<br /><br /><center>
            <form method=get id=Authorize action="https://www.facebook.com/v2.3/dialog/oauth">
            <input type=hidden name=client_id value="' . $config[ 'appId' ] . '">
            <input type=hidden name=redirect_uri value="http://' . $_SERVER[ 'HTTP_HOST' ] . $_SERVER[ 'SCRIPT_NAME' ] . '">
            <input type=hidden name=scope value="public_profile,user_photos,user_groups,manage_pages,publish_pages,publish_actions">
            <input type=hidden name=state value="' . $userName . '|safInit">    
            <input type=submit value="' . $lang['Authorize'] . '">
            </form></center>
        </div><br />
        <div style="font-size: x-small"><b>' . $lang['Permissions Required'] . ':</b><br />
            <b><em>' . $lang['Your Profile'] . ' - </em></b> ' . $lang['Profile Description'] . '.<br />
            <b><em>' . $lang['Your Photos'] . ' - </em></b> ' . $lang['Photos Description'] . '.<br />
            <b><em>' . $lang['Your Pages'] . ' - </em></b> ' . $lang['Pages Description'] . '.<br />
            <b><em>' . $lang['Publish Actions'] . ' - </em></b> ' . $lang['Publish Description'] . '.<br />
            <b><em>' . $lang['Groups List'] . ' - </em></b> ' . $lang['Groups Description'] . '.<br />
        </div>';
    $message .= "<script>            
            $('#Authorize').easyconfirm({
                eventType: 'submit',
                locale: { title: '" . $lang['Important Note'] . "', text: '" . $lang['User Auth Note'] . "', button: ['" . $lang['Cancel'] . "','" . $lang['Proceed'] . "']}
            });
            </script>";
    showHTML( $message, $lang['Welcome'] . " $userName" );
}

// Is this a Page/Groups Refresh Data Request?
if ( isset( $_GET[ 'rg' ] ) ) {
    require_once( 'includes/fbrg.php' );
}

// Is this a logged in user show help/documentation request?
if ( isset( $_GET[ 'usershowhelp' ] ) ) {
    showHelp();
} elseif ( isset( $_GET[ 'ucp' ] ) ) {
    //User Control Panel request?
    require_once( 'includes/usercp.php' );
} elseif ( isset( $_GET[ 'crons' ] ) ) {
    require_once( 'includes/showcrons.php' );
}

if ( $userOptions[ 'userDisabled' ] )
    showHTML( $userOptions[ 'disableReason' ] . "<br />" . $lang['Manual approval'], $lang['Welcome'] . " $userName" );

// Now we have all the data as user is logged into us
$pages       = explode( "\n", urldecode( $pageData ) );
$groups      = explode( "\n", urldecode( $groupData ) );
$isGroupPost = false;

if ( isset( $_POST[ 'pageid' ] ) ) {
    // This is a post submission. Time to actually post this submission to selected account.          
    require_once( 'includes/post.php' );
} else {
    // No pageid means not a post request, just show the fields and forms to fill-up
    require_once( 'includes/mainform.php' );
    require_once( 'includes/class.JavaScriptPacker.php' );
    $message = sanitizeOutput( $message );
    $packer  = new JavaScriptPacker( $script, 10, true, false );
    $script  = $packer->pack(); // We encrypt the javascript output to make copying difficult on public sites
    $message .= $script . '</script> ';
    showHTML( $message, "<img src='http://graph.facebook.com/v2.3/$userId/picture?redirect=1&height=64&type=normal&width=64' width=64 height=65 style='vertical-align:middle;'>&nbsp;" . $lang['Welcome'] . " $fullname" );
}
?>