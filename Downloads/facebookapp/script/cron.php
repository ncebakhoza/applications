#!/usr/local/bin/php
<?php
// Facebook Multi Page/Group Poster v2.2
// Created by Novartis (Safwan)

chdir( dirname( __FILE__ ) );

if ( !file_exists( 'params.php' ) )
    die( "No Prms\n" );
else
    require_once( 'params.php' );
require_once( 'functions.php' );

//DB existence check, die otherwise
if ( !file_exists( $dbName . '-settings.db' ) || !file_exists( $dbName . '-logs.db' ) || !file_exists( $dbName . '-crons.db' ) || !file_exists( $dbName . '-users.db' ) )
    die( "No DBs\n" );

readSettings();

if ( $db = new PDO( 'sqlite:' . $dbName . '-crons.db' ) ) {
    $statement = $db->prepare( "SELECT * FROM Crons WHERE date <= " . time() . " ORDER BY date DESC LIMIT 0,5" );
    if ( $statement ) {
        $statement->execute();
    } else {
        die( "DB Fail\n" );
    }
    $tempData = $statement->fetchAll();
    if ( !count( $tempData ) )
        die();    
    foreach ( $tempData as $v ) {
        $p      = explode( '|', $v[ 'params' ] );
        $params = array();
        foreach ( $p as $param ) {
            list( $paramName, $paramValue ) = explode( ',', $param );
            $params[ $paramName ] = urldecode( $paramValue );
        }
        $username = substr( $v[ 'user' ], strpos( $v[ 'user' ], "(" ) + 1, -1 );
        if ( $db2 = new PDO( 'sqlite:' . $dbName . '-users.db' ) ) {
            $statement2 = $db2->prepare( "SELECT usertoken FROM FB WHERE username = \"" . $username . "\"" ); //Should also check if user disabled
            if ( $statement2 ) {
                $statement2->execute();
            } else {
                echo( "User Fail\n" );
            }
            $tempData2 = $statement2->fetchAll();
            if ( count( $tempData2 ) ) {
                $params[ "access_token" ] = $tempData2[ 0 ][ 'usertoken' ];
                try {
                    require_once( "src/facebook.php" );
                    $fb  = new Facebook( $config );
                    $ret = $fb->api( $v[ 'feed' ], 'POST', $params );
                }
                catch ( Exception $e ) {
                    echo "Post Fail for " . $v[ 'status' ] . ": " . $e->getMessage() . "\n";
                }
            } else
                echo( "User Gone $username\n" );
        }        
        $statement = $db->prepare( "DELETE FROM Crons WHERE status = \"" . $v[ 'status' ] . "\"" );
        if ( $statement ) {
            $statement->execute();
        } else {
            echo "Del Fail for " . $v[ 'status' ] . "\n";
        }
    }
    $db = null;
}
?>