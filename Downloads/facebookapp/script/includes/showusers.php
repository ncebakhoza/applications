<?php
// Facebook Multi Page/Group Poster v2.2
// Created by Novartis (Safwan)

if ( count( get_included_files() ) == 1 )
    die();
if ( $adminloggedIn ) {
    if ( $db = new PDO( 'sqlite:' . $dbName . '-users.db' ) ) {
        if ( ( $_SERVER['REQUEST_METHOD'] == 'POST' ) && $hardDemo ) {
            $warn = "This is online Demo, therefore, users cannot be changed";
        } elseif ( isset( $_POST[ 'del' ] ) ) { //Delete/Enable/Disable user
            if ( $_POST[ 'del' ] == 1 ) {
                if ( is_numeric( $_POST[ 'accid' ] ) )
                    $statement = $db->prepare( "DELETE FROM FB WHERE userid=" . $_POST[ 'accid' ] );
                elseif ( strpos( $_POST[ 'accid' ], "#" ) !== false )
                    $statement = $db->prepare( "DELETE FROM FB WHERE username='" . substr( $_POST[ 'accid' ], 0, -1 ) . "'" );
                else
                    $statement = $db->prepare( "DELETE FROM FB WHERE username='" . $_POST[ 'accid' ] . "'" );
                if ( $statement ) {
                    $statement->execute();
                } else {
                    showHTML( "User Deletion failed!" );
                }
            } else {
                if ( is_numeric( $_POST[ 'accid' ] ) )
                    $statement = $db->prepare( "SELECT * FROM FB WHERE userid=" . $_POST[ 'accid' ] );
                else
                    $statement = $db->prepare( "SELECT * FROM FB WHERE username='" . $_POST[ 'accid' ] . "'" );
                if ( $statement ) {
                    $statement->execute();
                    $tempData                      = $statement->fetchAll();
                    $userOptions                   = readOptions( $tempData[ 0 ][ 'useroptions' ] );
                    $userOptions                   = checkUserOptions( $userOptions );
                    $userOptions[ 'disableReason' ]    = $_POST[ 'reason' ];
                    $userOptions[ 'userDisabled' ] = ( $userOptions[ 'userDisabled' ] == 2 ? 0 : !$userOptions[ 'userDisabled' ] );
                    $userId                        = $_POST[ 'accid' ];
                    saveUserOptions();
                } else {
                    showHTML( "User Disabling failed!" );
                }
            }
            header( "Location: ./?users" );
            die();
        }
        
        $statement = $db->prepare( "SELECT COUNT(*) FROM FB" );
        if ( $statement ) {
            $statement->execute();
        } else {
            showHTML( "Users Retrieval Failed!" );
        }
        $numr = $statement->fetchColumn();
        if ( !$numr )
            showHTML( $lang['No user yet'], $lang['Users List'] );
        $statement = $db->prepare( "SELECT * FROM FB" );
        if ( $statement ) {
            $statement->execute();
        } else {
            showHTML( "User Retrieval Failed x2!" );
        }
        $tempData = $statement->fetchAll();
        $message  = "<br /><div id=banReason class='lightbox ui-widget-content'><center>
              <form name=Account class='confirm' id=Account method=post>
              <h3 class='lightbox ui-widget-header'>" . $lang['Disabling'] . " " . $lang['User'] . "</h3>
              " . $lang['Are you sure'] . " " . $lang['disable'] . " " . $lang['user'] . " <span id=userIdentity></span>?
              <table><tr><td>" . $lang['Disable'] . " " . $lang['Message'] . "<td id=reasonCell><input type=text size=10 name=reason placeholder='Optional' class='textbox'><input type=hidden name='users'>
              <input type=hidden name=del value='0'>
              <input type=hidden name=accid value=''>
              </table>
              <input type=submit default value='" . $lang['Disable'] . "'> <input type=button value='" . $lang['Cancel'] . "'  onclick=\"$('#banReason').trigger('close');\">
              </form><br /></center>
            </div>            	
		<script>    
        function Accounts(e, pid, t) {
            e.stopPropagation();                
            document.forms['Account'].del.value=t;
            document.forms['Account'].accid.value=pid;
            if (t==1) {
                $('.confirm').easyconfirm({
                    eventType: 'submit',
                    locale: { title: '" . $lang['Removing'] . " " . $lang['User'] . "', text: '" . $lang['Are you sure'] . " " . $lang['remove'] . " " . $lang['user'] . " '+pid+'?', button: ['" . $lang['Cancel'] . "','" . $lang['Remove'] . "']}
                });
                $('#Account').trigger('submit');
            } else if (t==-1) {
                $('#userIdentity').html(pid);
                $('#banReason').lightbox_me({
                    centered: true, 
                    onLoad: function() { 
                        $('#reasonCell').find('input:first').focus()
                    }
                });                
            } else if (t==0) {
                $('.confirm').easyconfirm({
                    eventType: 'submit',
                    locale: { title: '" . $lang['Enabling'] . " " . $lang['User'] . "', text: '" . $lang['Are you sure'] . " " . $lang['enable'] ." " . $lang['user'] . " '+pid+'?', button: ['" . $lang['Cancel'] . "','" . $lang['Enable'] . "']}
                });
                $('#Account').trigger('submit');
            }            
        }       
        </script>";        
        $message .= '<div>
            Search: <input id="filter" type="text" style="width:auto;" />
            &nbsp;Filter: <select class="filter-status">
                <option></option>
                <option value="Enabled">Enabled</option>
                <option value="Disabled">Disabled</option>
                <option value="Awaiting Approval">Awaiting Approval</option>
            </select>
            <table class="user footable table" cols=7 data-page-size="20" data-filter="#filter">
            <thead><tr>
            <th class="ui-widget-header" colspan=2 data-hide="phone" data-type="alpha">FB ' . $lang['User'] . '
            <th class="ui-widget-header" data-type="alpha">' . $lang['Username'] . '
            <th class="ui-widget-header" data-hide="phone" data-type="alpha">' . $lang['Email'] . '
            <th class="ui-widget-header" data-type="numeric" data-hide="phone">' . $lang['Register'] . ' ' . $lang['Date'] . '
            <th class="ui-widget-header" data-type="numeric" data-hide="phone">' . $lang['Last'] . ' ' . $lang['Visit'] . '
            <th class="ui-widget-header">' . $lang['Operations'] . '</tr></thead>
            <tbody>';
        foreach ( $tempData as $s ) {
            $userOptions  = readOptions( $s[ 'useroptions' ] );
            $userOptions  = checkUserOptions( $userOptions );
            if ( $hardDemo ) {
                $tempID = $s[ 'userid' ];
                $s[ 'username' ] = $s[ 'userid' ] = $userOptions[ 'email' ] = '[hidden in demo]';
                if ( $s[ 'fullname' ] )
                    $s[ 'fullname' ] = substr( $s[ 'fullname' ], 0, 1) . str_repeat( '*', strlen( $s[ 'fullname' ] ) -1 );                
            }
            $userIdentity = ( $s[ 'userid' ] != '' ? $s[ 'userid' ] : $s[ 'username' ] );
            if ( is_numeric( $userIdentity ) && ( $s[ 'userid' ] == '' ) )  //fix for all numeric usernames from prev. versions
                $userIdentity .= "#";            
            $message .= "<tr><td data-value='" . $s[ 'fullname' ] . "'><img ";
            if ( $hardDemo && $tempID )
                $message .= "src='?proxyurl=" . urlencode( encrypt( "http://graph.facebook.com/v2.3/" . $tempID . "/picture?redirect=1&height=32&type=normal&width=32" ) ) . "'";
            elseif ( !$hardDemo && ( $s[ 'userid' ] != "" ) )
                $message .= "src='http://graph.facebook.com/v2.3/" . $s[ 'userid' ] . "/picture?redirect=1&height=32&type=normal&width=32'";  
            $message .= " width=32 height=32 style='vertical-align:middle;'><td><strong><a href='http://www.facebook.com/" . $s[ 'userid' ] . "' target=_new>" . $s[ 'fullname' ] . "</a></strong>";
            $message .= "<td>" . $s[ 'username' ] . "</a>";
            $message .= "<td><a href='mailto:" . $userOptions[ 'email' ] . "'>" . $userOptions[ 'email' ];
            $message .= "<td data-value=" . $userOptions[ 'signupDate' ] . ">" . ( $userOptions[ 'signupDate' ] ? date( 'd-M-Y G:i', $userOptions[ 'signupDate' ] ) : '-' ) . "";
            $message .= "<td data-value=" . $userOptions[ 'lastActive' ] . ">" . ( $userOptions[ 'lastActive' ] ? date( 'd-M-Y G:i', $userOptions[ 'lastActive' ] ) : '-' ) . "";
            $message .= "<td data-value='" . ( $userOptions[ 'userDisabled' ] ? ( $userOptions[ 'userDisabled' ] == 1 ? 'Disabled' : 'Awaiting Approval' ) : 'Enabled' ) . "'><img src='img/" . ( $userOptions[ 'userDisabled' ] ? ( $userOptions[ 'userDisabled' ] == 1 ? 'disabled' : 'awaiting' ) : 'enabled' ) . ".png' title='Click to " . ( $userOptions[ 'userDisabled' ] ? ( $userOptions[ 'userDisabled' ] == 1 ? 'enable (Disable Reason: ' . $userOptions[ 'disableReason' ] . ')' : 'approve' ) : 'disable' ) . " User' onclick='Accounts(event,\"$userIdentity\"," . ( $userOptions[ 'userDisabled' ] ? '0' : '-1' ) . ")'>";
            $message .= "&nbsp;&nbsp;<img src='img/delete.png' width='16px' title='Delete User' onclick='Accounts(event,\"$userIdentity\",1)'>";
        }
        $message .= "</tbody>
            <tfoot><tr><td colspan=7><br /><div class='pagination pagination-centered hide-if-no-paging'></div></tfoot>
            </table></div>";
        $message .= "<script type='text/javascript'>
                    $(function () {
                     
                        $('.footable').footable();
                        
                        $('.footable').footable().bind('footable_filtering', function (e) {
                          var selected = $('.filter-status').find(':selected').text();
                          if (selected && selected.length > 0) {
                            e.filter += (e.filter && e.filter.length > 0) ? ' ' + selected : selected;
                            e.clear = !e.filter;
                          }
                        });
                        
                        $('.filter-status').change(function (e) {
                          e.preventDefault();
                          $('table.footable').trigger('footable_filter', {filter: $('#filter').val()});
                        });
                     
                    });
                    </script>"; 
        showHTML( $message, $lang['Users List'] . " ($numr " . $lang['users'] . ")" );
    }
}
?>