<?php
// Facebook Multi Page/Group Poster v2.2
// Created by Novartis (Safwan)

if ( count( get_included_files() ) == 1 )
    die();
// Access Token Checking
if ( !isset( $userOptions[ 'role' ] ) || $userOptions[ 'role' ] == "" ) {
    $roles = json_decode( readURL( 'https://graph.facebook.com/v2.3/' . $config[ 'appId' ] . '/roles?limit=10000&access_token=' . $config[ 'appId' ] . '|' . $config[ 'secret' ] ) );
    $role  = "";
    foreach ( $roles->data as $r ) {
        if ( $r->user == $userId ) {
            $role = $r->role;
            break;
        }
    }
    if ( $role != "" ) {
        $userOptions[ 'role' ] = $role;
        if ( $db2 = new PDO( 'sqlite:' . $dbName . '-users.db' ) ) { //Should use saveUserOptions
            $option = "";
            foreach ( $userOptions as $key => $value ) {
                if ( ( $key != "" ) && ( $value != "" ) ) {
                    if ( $option != "" )
                        $option .= "|";
                    $option .= $key . ":" . $value;
                }
            }
            $statement = $db2->prepare( "UPDATE FB SET useroptions=\"$option\" WHERE userid=\"$userId\"" );
            if ( $statement )
                $statement->execute();
            else
                showHTML( "Error x34353054" );
            authRedirect();
        } else {
            showHTML( "Error while opening users database." );
        }
    } else {
        if ( !$adminOptions[ 'enableARA' ] )
            $message = "<div>" . $lang['Congratulations'] . ". " . $lang['Signup success'] . ".<br /><br />
                        " . $lang['Manual approval'] . "<br />
                        " . $lang['recieve notification'] . "</div>";
        else
            $message = '<div>' . $lang['Congratulations'] . '. ' . $lang['almost complete'] . '. ' . $lang['steps remain'] . '<br /><br />
                        <strong>' . $lang['Step 1'] . ':</strong> ' . $lang['new notification'] . '<br />
                        <strong>' . $lang['Step 2'] . ':</strong> ' . $lang['click notification'] . '<br />
                        <strong>' . $lang['Step 3'] . ':</strong> ' . $lang['return here'] . '<br />
                        <br /><br />                                                        
                        <strong>' . $lang['Note'] . '</strong>: ' . $lang['Note full'] . '<br /></div>';
        showHTML( $message, $lang['Welcome'] . " $userName" );
    }
} else {
    //Validity checking
}
?>