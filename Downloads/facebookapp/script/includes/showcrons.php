<?php
// Facebook Multi Page/Group Poster v2.2
// Created by Novartis (Safwan)

if ( count( get_included_files() ) == 1 )
    die();
if ( $adminloggedIn || $loggedIn ) {
    if ( $db = new PDO( 'sqlite:' . $dbName . '-crons.db' ) ) {
        if ( ( $_SERVER['REQUEST_METHOD'] == 'POST' ) && $hardDemo && $adminloggedIn ) {
            $warn = "This is online Demo, therefore, crons cannot be deleted";
        } elseif ( isset( $_POST[ 'del' ] ) ) { //Delete Cron
            if ( $_POST[ 'del' ] == 1 ) {
                if ( isset( $_POST[ 'cronid' ] ) ) {
                    list( $cronid, $userIdentifier ) = explode( "|", $_POST[ 'cronid' ] );
                    if ( $adminloggedIn || ( "$fullname ($user)" == $userIdentifier ) ) {
                        $statement = $db->prepare( "DELETE FROM Crons WHERE user=\"$userIdentifier\" AND status=\"$cronid\"");                
                        if ( $statement ) {
                            $statement->execute();
                        } else {
                            showHTML( $lang['CRON Deletion failed'] );
                        }
                    } else {
                        showHTML( $lang['CRON Deletion failed'] . " - " . $lang['not authorized'] );
                    }
                }
            }
            header( "Location: ./?crons&start=" . $_POST[ "start" ] );
            die();
        }
        
        if ( $loggedIn )
            $statement = $db->prepare( "SELECT COUNT(*) FROM Crons WHERE user = \"$fullname ($user)\"" );
        else
            $statement = $db->prepare( "SELECT COUNT(*) FROM Crons" );
        if ( $statement ) {
            $statement->execute();
        } else {
            showHTML( $lang['CRON Retrieval Failed'] );
        }
        $numr = $statement->fetchColumn();
        if ( !$numr )
            showHTML( $lang['No CRON jobs'], $lang['CRON Jobs List'] );
        $numPerPage = 15;
        if ( isset( $_GET[ "start" ] ) ) {
            $start = $_GET[ "start" ];
            if ( ( $start % $numPerPage ) != 0 ) {
                $start = $start - ( $start % $numPerPage );
            }
        } else {
            $start = 0;
        }
        $numPages = floor( $numr / $numPerPage );
        if ( ( $numr % $numPerPage ) != 0 )
            $numPages += 1;
        $curPage = ( $start / $numPerPage ) + 1;
        if ( $loggedIn )
            $statement = $db->prepare( "SELECT * FROM Crons WHERE user = \"$fullname ($user)\" ORDER BY date ASC LIMIT " . $start . "," . $numPerPage );
        else
            $statement = $db->prepare( "SELECT * FROM Crons ORDER BY date ASC LIMIT " . $start . "," . $numPerPage );
        if ( $statement ) {
            $statement->execute();
        } else {
            showHTML( $lang['CRON Retrieval Failed'] . " x2!" );
        }
        $tempData = $statement->fetchAll();
        $message  = "<form name=Account class='confirm' id=Account method=post>
            <input type=hidden name='start' value='" . $start . "'>
            <input type=hidden name='crons'>
            <input type=hidden name=del value='0'>
            <input type=hidden name=cronid value=''>
         </form>	
		<script>    
        function Accounts(e, pid, t) {
            e.stopPropagation();                
            document.forms['Account'].del.value=t;
            document.forms['Account'].cronid.value=pid;
            if (t==1) {
                $('.confirm').easyconfirm({
                    eventType: 'submit',
                    locale: { title: '" . $lang['Removing CRON Post'] . "', text: '" . $lang['remove this CRON'] . "', button: ['" . $lang['Cancel'] . "','" . $lang['Remove'] . "']}
                });
            }
            $('#Account').trigger('submit');
        }       
        </script>";
        $message  .= '<div><table class=user cols=4><tr><th>Time<th>' . $lang['Username'] . '<th>' . $lang['Page'] . '/' . $lang['Group'] . '<th>' . $lang['Params'] . '<th>' . $lang['Operations'] . '</tr>';
        foreach ( $tempData as $s ) {
            $p          = explode( '|', $s[ 'params' ] );
            $params     = array();
            $postParams = '';
            foreach ( $p as $param ) {
                list( $paramName, $paramValue ) = explode( ',', $param );
                $params[ $paramName ] = urldecode( $paramValue );
                if ( ( $paramName != "access_token" ) )
                    $postParams .= $paramName . ': ' . urldecode( $paramValue ) . '<br />';
            }
            if ( isset( $params[ "link" ] ) ) {
                $ptype = "L";
                $resp  = $lang['posted as link'];
            } else {
                $ptype = "T";
                $resp  = $lang['posted'];
            }
            $pageId = substr( $s[ 'feed' ], 6, strrpos( $s[ 'feed' ], "/" ) - 6 );
            //$pageId = $s[ 'feed' ];
            if ( $hardDemo && $adminloggedIn ) {
                $pageId = $s[ 'user' ] = '[hidden in demo]';               
            }
            $message .= "<tr>";
            $message .= "<td><img class=bottom src=\"img/";
            switch ( $ptype ) {
                case 'T':
                    $message .= "text.png\" title='TEXT'";
                    break;
                case 'L':
                    $message .= "link.png\" title='LINK'";
                    break;
            }
            $message .= " width=16 height=16 />&nbsp;";
            $message .= date( 'd-M-Y G:i', $s[ 'date' ] );
            $message .= "<td><strong>" . $s[ 'user' ] . "</strong>";
            $message .= "<td><a href='http://www.facebook.com/" . $pageId . "' target=_new>" . $pageId . "</a>";
            $message .= "<td>" . $postParams;
            $message .= "<td><img src='img/delete.png' width='16px' title='Delete CRON' onclick='Accounts(event,\"" . $s[ 'status' ] . "|" . $s[ 'user' ] . "\",1)'>";
        }
        $message .= "</table></div>";
        $message .= "<br><center>(" . $lang['Current Server Time'] . ": " . date( 'd-M-Y G:i' ) . ")</center><br>";
        //Pagination of Results
        $message .= "<br><div>";
        if ( $start > 0 ) {
            $message .= " | <a href='./?crons&start=0'>" . $lang['First'] . "</a>";
            if ( $curPage > 2 ) {
                $message .= " | <a href='./?crons&start=" . ( $start - $numPerPage ) . "'>" . $lang['Previous'] . "</a>";
            }
        }
        $message .= " | <b>" . $lang['Page'] . " $curPage of $numPages</b>";
        if ( $start < ( $numr - $numPerPage ) ) {
            if ( $curPage <= ( $numPages - 2 ) ) {
                $message .= " | <a href='./?crons&start=" . ( $start + $numPerPage ) . "'>" . $lang['Next'] . "</a>";
            }
            $message .= " | <a href='./?crons&start=" . ( ( $numPages * $numPerPage ) - $numPerPage ) . "'>" . $lang['Last'] . "</a>";
        }
        $message .= " |</div>";
        showHTML( $message, $lang['CRON Jobs List'] . " ($numr)" );
    }
}
?>