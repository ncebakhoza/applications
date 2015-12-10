<?php
// Facebook Multi Page/Group Poster v2.2
// Created by Novartis (Safwan)

if ( count( get_included_files() ) == 1 )
    die();
$access_token = '';
if ( $userId == $_POST[ 'pageid' ] ) {
    $access_token      = $userToken;
}
if ( !$access_token ) {
    foreach ( $pages as $page ) {
        if ( $page != "" ) {
            $p = explode( ":", $page );
            if ( $p[ 0 ] == $_POST[ 'pageid' ] ) {
                $access_token = $p[ 3 ];
                break;
            }
        }
    }
}
if ( !$access_token ) {
    // No pages found with matching id, lets check Groups
    foreach ( $groups as $group ) {
        if ( $group != "" ) {
            $g = explode( ":", $group );
            if ( $g[ 0 ] == $_POST[ 'pageid' ] ) {
                // so this IS a group post, we need the usertoken for this
                $access_token = $userToken;
                $isGroupPost  = true;
                break;
            }
        }
    }
}
if ( !$access_token ) {
    die( $lang['No Token'] );
}
$resp   = $lang['posted'];
$params = array(
     "access_token" => $access_token // see: https://developers.facebook.com/docs/facebook-login/access-tokens/
);
if ( isset( $_POST[ 'Type' ] ) ) {
    $ptype = $_POST[ 'Type' ];
} else {
    $ptype = "T";
}
if ( ( ( $ptype == "A" ) || ( $ptype == "I" ) ) && isset( $_POST[ 'proxy' ] ) ) {
    // If proxy option is selected by user
    $_POST[ 'URL' ] = 'http://' . $_SERVER[ 'HTTP_HOST' ] . $_SERVER[ 'SCRIPT_NAME' ] . '?proxyurl=' . encrypt( $_POST[ 'URL' ] );
}
$postlink = "http://www.facebook.com/";
$spintax = new Spintax();
if ( $ptype == "I" ) {
    // It is an Image Post
    if ( $_POST[ 'URL' ] == '' ) {
        die( $failImg . " " . $lang['No image'] );
    }
    $params[ "message" ] = $spintax -> process( $_POST[ 'Message' ] );
    $params[ "url" ]     = $_POST[ 'URL' ];
    $feed                = '/v2.3/' . $_POST[ 'pageid' ] . '/' . "photos";
    $postlink .= "photo.php?fbid=";
} elseif ( $ptype == "A" ) {
    // Album Post
    if ( !isset( $_POST[ 'AlbumID' ] ) ) {
        if ( $_POST[ 'URL' ] == '' ) {
            die( $failImg . " " . $lang['No album'] );
        }
        try {           
            $albums = $fb->api( '/v2.3/' . $_POST[ 'pageid' ] . '/albums', array(
                     "access_token" => $access_token,
                     "limit" => "10000"
                ) );
            echo "<form id=frm" . $_POST[ 'pageid' ] . " method=post>
                <input type=hidden name=pageid value='" . $_POST[ 'pageid' ] . "'>
                <input type=hidden name=Type value='" . $ptype . "'>
                <input type=hidden name=Message value='" . $_POST[ 'Message' ] . "'>
                <input type=hidden name=URL value='" . $_POST[ 'URL' ] . "'>
                <input type=hidden name=timezone value='" . $_POST[ 'timezone' ] . "'>
                <input type=hidden name=date value='" . $_POST[ 'date' ] . "'>
                <input type=hidden name=time value='" . $_POST[ 'time' ] . "'>";
            echo $lang['Almost done'] . " - " . $lang['Select Album'] . ": <select name='AlbumID'>";
            foreach ( $albums[ 'data' ] as $album ) {
                if ( $album[ 'name' ] == 'Cover Photos' || $album[ 'name' ] == 'Profile Pictures' ) continue; // we cannot post to these node from this API edge
                echo "<option value='" . $album[ 'id' ] . "'>" . $album[ 'name' ] . "</option>\n"; // should return whole form here to support multiple postings
            }
            echo "</select>
                    <input type=submit value=Continue>
                    </form>
                    <script>
                        var options = {
                                    target:        '#" . $_POST[ 'pageid' ] . "',
                                    //timeout:   5000 ,
                                    beforeSubmit:  function(formData, jqForm, options) {
                                        var queryString = $.param(formData);
                                        //alert(formData[0].value);
                                        document.getElementById(formData[0].value).innerHTML=' <img src=\"img/loading.gif\" class=bottom /> " . $lang['Posting'] . "...., " . $lang['take time'] . "... ';
                                    } // pre-submit callback
                                    //success:       showResponse  // post-submit callback
                                };
                        $('#frm" . $_POST[ 'pageid' ] . "').ajaxForm(options);
                    </script>";
            die( 0 );
        }
        catch ( Exception $e ) {
            die( $failImg . " " . $e->getMessage() );
        }
    } else {
        $params[ "message" ] = $spintax -> process( $_POST[ 'Message' ] );
        $params[ "url" ]     = $_POST[ 'URL' ];
        $feed                = '/v2.3/' . $_POST[ 'AlbumID' ] . '/photos';
        $postlink .= "photo.php?fbid=";
    }
} elseif ( $ptype == "L" ) {
    // Link Post
    if ( $_POST[ 'URL' ] == '' ) {
        die( $failImg . " " . $lang['No link'] );
    }
    $params[ "link" ] = $_POST[ 'URL' ];
    if ( isset( $_POST[ 'Title' ] ) && ( $_POST[ 'Title' ] != '' ) )
        $params[ "name" ] = $spintax -> process( $_POST[ 'Title' ] );
    if ( isset( $_POST[ 'Description' ] ) && ( $_POST[ 'Description' ] != '' ) )
        $params[ "description" ] = $spintax -> process( $_POST[ 'Description' ] );
    if ( isset( $_POST[ 'Message' ] ) && ( $_POST[ 'Message' ] != '' ) )
        $params[ "message" ] = $spintax -> process( $_POST[ 'Message' ] );
    if ( isset( $_POST[ 'Caption' ] ) && ( $_POST[ 'Caption' ] != '' ) )
        $params[ "caption" ] = $spintax -> process( $_POST[ 'Caption' ] );
    if ( isset( $_POST[ 'Picture' ] ) && ( $_POST[ 'Picture' ] != '' ) )
        $params[ "picture" ] = $_POST[ 'Picture' ];
    $feed = '/v2.3/' . $_POST[ 'pageid' ] . '/' . "feed";
    $postlink .= $_POST[ 'pageid' ] . "/posts/";
} elseif ( $ptype == "V" ) {
    // Video Post    
    /* if ( $adminOptions[ 'enableDemo' ] )
        die( "$failImg " . $lang['Video uploading'] . " " . $lang['disabled in demo'] . ". " . $lang['Buy script'] ); */
    if ( $_POST[ 'URL' ] == '' ) {
        die( $failImg . " " . $lang['No video'] );
    }
    $params[ "title" ]       = $spintax -> process( $_POST[ 'Title' ] );
    $params[ "description" ] = $spintax -> process( $_POST[ 'Message' ] );
    $feed                    = '/v2.3/' . $_POST[ 'pageid' ] . '/' . "videos";
    $postlink .= "photo.php?v=";
    
    //video checker for youtube
    $vid        = parseYtUrl( $_POST[ 'URL' ] );
    if ( $vid ) {
        $format = "video/mp4"; // the MIME type of the video. e.g. video/mp4, video/webm, etc.
        parse_str( readURL( "http://www.youtube.com/get_video_info?video_id=" . $vid ), $info ); //decode the data
        if ( isset( $info[ 'errorcode' ] ) )
            die( "$failImg " . $info[ 'reason' ] );
        $streams = $info[ 'url_encoded_fmt_stream_map' ]; //the video's location info            
        $streams = explode( ',', $streams );
        foreach ( $streams as $stream ) {
            parse_str( urldecode( $stream ), $data ); //decode the stream
            if ( stripos( $data[ 'type' ], $format ) !== false ) {
                // We've found the right stream with the correct format
                $url   = $data[ 'url' ];
                $sig   = $data[ 'signature' ];
                $params[ "file_url" ] = str_replace( '%2C', ',', $url . '&' . http_build_query( $data ) . '&signature=' . $sig );
                unset( $data );                
                break;
            }
        }
    } elseif ( !file_exists( $_SERVER[ 'DOCUMENT_ROOT' ] . $_POST[ 'URL' ] ) ) {
        $params[ "file_url" ] = $_POST[ 'URL' ];
    } else {
        $params[ "file_url" ] = $_SERVER['HTTP_HOST'] . $_POST[ 'URL' ];
    }
} else {
    // simple status update
    if ( $_POST[ 'Message' ] == '' ) {
        die( $failImg . " " . $lang['empty message'] );
    }
    $params[ "message" ] = $spintax -> process( $_POST[ 'Message' ] );
    $feed                = '/v2.3/' . $_POST[ 'pageid' ] . '/' . "feed";
    $postlink .= $_POST[ 'pageid' ] . "/posts/";
}
if ( ( !$isGroupPost && ( $userId != $_POST[ 'pageid' ] ) ) || $adminOptions[ 'useCron' ] ) {
    // Group/Profile posts cannot be scheduled unless cron enabled by Admin
    if ( isset( $_POST[ 'timezone' ] ) ) {
        if ( is_numeric( $_POST[ 'timezone' ] ) ) {
            $timezone = 'Etc/GMT' . ( $_POST[ 'timezone' ] > 0 ? '-' : '+' );
            $timezone .= abs( $_POST[ 'timezone' ] );
        } else {
            $timezone = $_POST[ 'timezone' ];
        }
        date_default_timezone_set( $timezone );
    }
    $dt = $_POST[ 'date' ];
    $tm = $_POST[ 'time' ];
    if ( $dt || $tm ) {
        if ( !$dt ) {
            $dt = date( 'd-M-Y' );
        }
        if ( !$tm ) {
            $tm = date( 'G:i' );
        }
        $schedule = strtotime( "$dt $tm" );
        if ( !$isGroupPost && ( ( $schedule - time() ) > 900 ) && ( $userId != $_POST[ 'pageid' ] ) ) {
            $params[ "scheduled_publish_time" ] = $schedule;
            $params[ "published" ]              = false;
            $resp                               = $lang['scheduled for'] . " $dt $tm";
        }
        if ( $isGroupPost || ( $userId == $_POST[ 'pageid' ] ) ) {
            $isCronJob = true;
            $resp      = $lang['scheduled for'] . " $dt $tm";
        }
    }
    if ( isset( $_POST[ 'timezone' ] ) )
        date_default_timezone_set( $adminOptions[ 'adminTimeZone' ] );
}
if ( $isGroupPost && $ptype != "V" && $ptype != "I" && $ptype != "A" ) {
    $postlink = "https://www.facebook.com/groups/" . $_POST[ 'pageid' ] . "/permalink/";
}
try {
    if ( isset( $isCronJob ) ) {
        if ( $db = new PDO( 'sqlite:' . $dbName . '-crons.db' ) ) {
            $pv = "";
            foreach ( $params as $pk => $ps ) {
                if ( $pv != "" )
                    $pv .= "|";
                $pv .= $pk . "," . urlencode( $ps );
            }
            $statement = $db->prepare( "INSERT INTO Crons VALUES (\"$schedule\",\"$fullname ($user)\",\"$feed\",\"$pv\",\"" . microtime() . "\")" );
            if ( $statement ) {
                $statement->execute();
                echo $successImg . ' ' . $lang['Successfully'] . ' ' . $resp . " " . $lang['using CRON'];
            } else {
                die( $failImg . " " . $lang['Cron failed'] );
            }
        }
    } else {
        $ret = $fb->api( $feed, 'POST', $params );
        if ( strpos( $ret[ 'id' ], "_" ) !== false ) {
            $postlink .= substr( strstr( $ret[ 'id' ], "_" ), 1 );
        } else {
            $postlink .= $ret[ 'id' ];
        }
        echo $successImg . $lang['Successfully'] . ' ' . $resp . " " . $lang['to Facebook'] . " - <a href='$postlink' target=sf>" . $lang['Post Link'] . "</a>";        
    }
}
catch ( Exception $e ) {
    die( $failImg . " " . $e->getMessage() );
}
?>