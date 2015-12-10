<?php
// Facebook Multi Page/Group Poster v2.2
// Created by Novartis (Safwan)

if ( count( get_included_files() ) == 1 )
    die();
$allGood = false;
$error   = "";
if ( isset( $_POST[ 'suun' ] ) && isset( $_POST[ 'supw' ] ) && isset( $_POST[ 'suem' ] ) && isset( $_POST[ 'suuid' ] ) ) {
    if ( ( $_POST[ 'suun' ] != "" ) && ( $_POST[ 'supw' ] != "" ) && ( $_POST[ 'suem' ] != "" ) && ( $_POST[ 'suuid' ] != "" ) ) {
        if ( strlen( $_POST[ 'supw' ] ) >= 5 ) {
            if ( filter_var( $_POST[ 'suem' ], FILTER_VALIDATE_EMAIL ) ) {
                if ( preg_match( "/^[a-zA-Z][a-zA-Z0-9]*$/", $_POST[ 'suun' ] ) ) {
                    if ( preg_match( "/^[0-9]*$/", $_POST[ 'suuid' ] ) ) {
                        if ( $adminOptions[ 'enableNUR' ] ) {
                            if ( $db = new PDO( 'sqlite:' . $dbName . '-users.db' ) ) {
                                $user      = $_POST[ 'suun' ];
                                $statement = $db->prepare( "SELECT COUNT(*) FROM FB WHERE username = \"$user\"" );  //TODO: Check dup emails
                                if ( $statement ) {
                                    $statement->execute();
                                } else {
                                    die( "Statement failed while signing up 015x3!" );
                                }
                                if ( $statement->fetchColumn() == 0 ) {
                                    $pass        = encrypt( $_POST[ 'supw' ] );
                                    $userOptions = "email:" . $_POST[ 'suem' ] . "|guid:" . $_POST[ 'suuid' ] . "|signupDate:" . time();
                                    if ( $adminOptions[ 'enableNUR' ] == 2 )
                                        $userOptions .= "|userDisabled:2";
                                    $statement   = $db->prepare( "INSERT INTO FB VALUES (\"\",\"$pass\",\"$user\",\"\",\"\",\"\",\"\",\"\",\"$userOptions\")" );
                                    if ( $statement ) {
                                        $statement->execute();
                                        $cookie = base64_encode( "$user:" . md5( $_POST[ 'supw' ] ) );
                                        setcookie( 'FBMPGPLogin', $cookie );
                                        $allGood = true;
                                    } else {
                                        die( "Saving failed while signing up 005xx5!" );
                                    }
                                } else {
                                    $error = $lang['Username'] . " " . $lang['already exists'] . ". " . $lang['Choose another'];
                                }
                            } else {
                                $error = "Checking failed 015x2!";
                            }
                        } else {
                            $error = "New User Registration is disabled";
                        }
                    } else {
                        $error = $lang['Invalid'] . " " . $lang['ID'];
                    }
                } else {
                    $error = $lang['Only alphabets'] . " " . $lang['Username'];
                }
            } else {
                $error = $lang['Invalid'] . " " . $lang['email format'];
            }
        } else {
            $error = $lang['Password'] . " " . $lang['length'];
        }
    } else {
        $error = $lang['All fields'] . " " . $lang['are required'];
    }
} else {
    $error = $lang['All fields'] . " " . $lang['are required'];
}
if ( !$allGood )
    die( "<script>$.notify('Error: $error', {globalPosition: 'bottom right', className: 'error'});</script>" );
die( "OK" );
?>