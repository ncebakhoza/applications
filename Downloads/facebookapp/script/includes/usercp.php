<?php
// Facebook Multi Page/Group Poster v2.2
// Created by Novartis (Safwan)

if ( count( get_included_files() ) == 1 )
    die();
if ( $loggedIn ) {
    $output = "<h3>" . $lang['Access Token'] . " " . $lang['Information'] . ":</h3>";
    try {    
        $permissions = $fb->api( "/v2.3/me/permissions", array( "access_token" => $userToken ) );
        foreach ( $permissions[ 'data' ] as $perm ) {
            if ( $perm[ 'status' ] == 'granted' ) {
                if ( $perm[ 'permission' ] == 'public_profile' ) $public_profile = true;
                if ( $perm[ 'permission' ] == 'user_photos' ) $user_photos = true;
                if ( $perm[ 'permission' ] == 'user_groups' ) $user_groups = true;
                if ( $perm[ 'permission' ] == 'manage_pages' ) $manage_pages = true;
                if ( $perm[ 'permission' ] == 'publish_pages' ) $publish_pages = true;
                if ( $perm[ 'permission' ] == 'publish_actions' ) $publish_actions = true;
            }
        }    
        if ( isset( $public_profile ) )
            $output .= "$successImg " . $lang['Your Profile'] . " " . $lang['Permission Granted'] . "<br />";
        else
            $output .= "$failImg <strong>" . $lang['Your Profile'] . " " . $lang['Not Found'] . "</strong><br />";
        if ( isset( $user_photos ) )
            $output .= "$successImg " . $lang['Your Photos'] . " " . $lang['Permission Granted'] . "<br />";
        else
            $output .= "$failImg <strong>" . $lang['Your Photos'] . " " . $lang['Not Found'] . "</strong><br />";
        if ( isset( $user_groups ) )
            $output .= "$successImg " . $lang['Groups List'] . " " . $lang['Permission Granted'] . "<br />";
        else
            $output .= "$failImg <strong>" . $lang['Groups List'] . " " . $lang['Not Found'] . "</strong><br />";
        if ( isset( $manage_pages ) && isset( $publish_pages ) )
            $output .= "$successImg " . $lang['Your Pages'] . " " . $lang['Permission Granted'] . "<br />";
        else
            $output .= "$failImg <strong>" . $lang['Your Pages'] . " " . $lang['Not Found'] . "</strong><br />";
        if ( isset( $publish_actions ) )
            $output .= "$successImg " . $lang['Publish Actions'] . " " . $lang['Permission Granted'] . "<br />";
        else
            $output .= "$failImg <strong>" . $lang['Publish Actions'] . " " . $lang['Not Found'] . "</strong><br />"; 
    }
    catch ( Exception $e ) {
        $output .= "$failImg " . $e->getMessage();
    }    
    $output .= "<br /><form name=refresh id=userToken method=get><input type=hidden name=rg value=1><input type=submit title='" . $lang['Refresh Data message'] . "' value='" . $lang['Refresh Data'] . "'></form>"; 
    $output .= "<script>            
            $('#userToken').easyconfirm({
                eventType: 'submit',
                locale: { title: '" . $lang['Important Note'] . "', text: '" . $lang['User Token Note'] . "', button: ['" . $lang['Cancel'] . "','" . $lang['Proceed'] . "']}
            });
            </script>";
    $message = $output . "<hr><h4>" . $lang['Change'] . " " . $lang['password'] . ": </h4>
                <form name=userCP method=post action='?ucp'>
                <table><tr><td>" . $lang['Enter'] . " " . $lang['current'] . " " . $lang['password'] . ":<td> <input type=password name=oldP><br />
                <tr><td>" . $lang['Enter'] . " " . $lang['new'] . " " . $lang['password'] . ":<td> <input type=password name=newP><br />
                <tr><td>" . $lang['Repeat'] . " " . $lang['new'] . " " . $lang['password'] . ":<td> <input type=password name=renewP><br />
                <tr><td colspan=2 class='text-center'><input type=submit value='" .$lang['Submit'] . "'></table></form>";
    if ( isset( $_POST[ 'oldP' ] ) && isset( $_POST[ 'newP' ] ) && isset( $_POST[ 'renewP' ] ) ) {
        if ( $_POST[ 'oldP' ] != $password ) {
            $message .= "<span class='notice'>" . $lang['Incorrect'] . " " . $lang['password'] . "</span>";
        } elseif ( $_POST[ 'newP' ] != $_POST[ 'renewP' ] ) {
            $message .= "<span class='notice'>" . $lang['Passwords'] . " " . $lang['do not match'] . "</span>";
        } elseif ( strlen( $_POST[ 'newP' ] ) < 5 ) {
            $message .= "<span class='notice'>" . $lang['Password'] . " " . $lang['length'] . "</span>";
        } elseif ( $hardDemo && ( $userName == "Multi" ) ) {
            $message .= "<span class='notice'>Password cannot be changed for this user!</span>";
        } else {
            $newP = encrypt( $_POST[ 'newP' ] );
            if ( $db = new PDO( 'sqlite:' . $dbName . '-users.db' ) ) {
                $statement = $db->prepare( "UPDATE FB SET password = \"$newP\" WHERE username = \"$userName\"" );
                if ( $statement ) {
                    $statement->execute();
                    $message .= "<span class='notice'>" . $lang['Password'] . " " . $lang['Changed'] . " " . $lang['Successfully'] . "</span>";
                } else {
                    $message .= "<span class='notice'>" . $lang['Critical Error'] . " " . $lang['while changeing'] . " " . $lang['Password'] . "</span>";
                }
            } else {
                $message .= "<span class='notice'>Error opening database!</span>";
            }
        }
    }    
    showHTML( $message, $lang['User Control Panel'] );
}
?>