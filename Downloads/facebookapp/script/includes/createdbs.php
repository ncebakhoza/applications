<?php
// Facebook Multi Page/Group Poster v2.2
// Created by Novartis (Safwan)

if ( count( get_included_files() ) == 1 )
    die();


if ( !file_exists( 'params.php' ) && !isset( $_POST[ 'form_id' ] ) && !file_exists( 'config.php' ) ) {
    showHTML( file_get_contents( 'includes/install.html' ), "Welcome to FB Multi Page/Group Poster" );
}
if ( isset( $_POST[ 'form_id' ] ) || file_exists( 'config.php' ) ) {
    if ( isset( $_POST[ 'form_id' ] ) ) {
        $dbName = $_POST[ 'dbprefix' ];
        if ( strlen( $_POST[ 'enckey' ] ) != 8 )
            $_POST[ 'enckey' ] = "safcomcl";
        define( "ENCRYPTION_KEY", $_POST[ 'enckey' ] );
    }
    $fp = fopen( 'params.php', "w" );
    fwrite( $fp, "<?php\n" );
    fwrite( $fp, "if ( count( get_included_files() ) == 1 ) die();\n" );
    fwrite( $fp, '$dbName = "' . ( isset( $_POST[ 'dbprefix' ] ) ? $_POST[ 'dbprefix' ] : $dbName ) . '";' . "\n" );
    fwrite( $fp, "define( 'ENCRYPTION_KEY', '" . ( isset( $_POST[ 'enckey' ] ) ? $_POST[ 'enckey' ] : ENCRYPTION_KEY ) . "' );\n" );
    fwrite( $fp, "?>\n" );
    fclose( $fp );
    if ( file_exists( 'config.php' ) ) {
        unlink( 'config.php' );
        header( 'Location: ./' );
        exit();
    }
}

if ( !file_exists( $dbName . '-settings.db' ) ) {
    if ( isset( $_POST[ 'form_id' ] ) ) {
        if ( $db = new PDO( 'sqlite:' . $dbName . '-settings.db' ) ) {
            $statement = $db->prepare( "CREATE TABLE Settings (appid TEXT, secret TEXT, admin TEXT COLLATE NOCASE, adminpass TEXT, adminoptions TEXT)" );
            if ( $statement ) {
                $statement->execute();
                $statement = $db->prepare( "INSERT INTO Settings VALUES (\"" . $_POST[ 'appid' ] . "\",\"" . $_POST[ 'appsecret' ] . "\",\"" . $_POST[ 'admin' ] . "\",\"" . encrypt( $_POST[ 'adminpass' ] ) . "\",\"\")" );
                if ( $statement ) {
                    $statement->execute();
                } else {
                    showHTML( "Settings Save failed while executing statement!" );
                }
                $config[ 'appId' ]  = $_POST[ 'appid' ];
                $config[ 'secret' ] = $_POST[ 'appsecret' ];
                $adminloggedIn      = true;
                $cookie             = base64_encode( $_POST[ 'admin' ] . ":" . md5( $_POST[ 'adminpass' ] ) );
                setcookie( 'FBMPGPLogin', $cookie );
                header( 'Location: ./' );
                exit();
            } else {
                showHTML( "Settings Table Creation failed!" );
            }
            $db = null;
        } else {
            showHTML( "Error - Unable to create settings database. Exiting..." );
        }
    } else {
        showHTML( file_get_contents( 'includes/install.html' ), "Welcome to FB Multi Page/Group Poster" );
    }
}
if ( !file_exists( $dbName . '-logs.db' ) ) {
    if ( $db = new PDO( 'sqlite:' . $dbName . '-logs.db' ) ) {
        $statement = $db->prepare( "CREATE TABLE Logs (date TEXT, user TEXT, type TEXT, target TEXT, targettype TEXT, action TEXT, status TEXT, permalink TEXT, params TEXT)" );
        if ( $statement ) {
            $statement->execute();
        }
        $db = null;
    } else {
        showHTML( "Error - Unable to create logs database. Exiting..." );
    }
}
if ( !file_exists( $dbName . '-crons.db' ) ) {
    if ( $db = new PDO( 'sqlite:' . $dbName . '-crons.db' ) ) {
        $statement = $db->prepare( "CREATE TABLE Crons (date TEXT, user TEXT, feed TEXT, params TEXT, status TEXT)" );
        if ( $statement ) {
            $statement->execute();
        }
        $db = null;
    } else {
        showHTML( "Error - Unable to create CRONS database. Exiting..." );
    }
}
if ( !file_exists( $dbName . '-users.db' ) ) {
    if ( $db = new PDO( 'sqlite:' . $dbName . '-users.db' ) ) {
        $statement = $db->prepare( "CREATE TABLE FB (userid TEXT, password TEXT, username TEXT COLLATE NOCASE, tokendate TEXT, usertoken TEXT, pagedata TEXT, groupdata TEXT, fullname TEXT, useroptions TEXT)" );
        if ( $statement ) {
            $statement->execute();
        }
        $db = null;
    } else {
        showHTML( "Error - Unable to create database. Exiting..." );
    }
}
?>