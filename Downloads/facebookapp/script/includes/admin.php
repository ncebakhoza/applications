<?php
// Facebook Multi Page/Group Poster v2.2
// Created by Novartis (Safwan)

// Admin Panel
if ( count( get_included_files() ) == 1 )
    die();
if ( $adminloggedIn ) {
    global $config, $adminOptions, $successImg, $failImg, $hardDemo;
    if ( ( isset( $_POST[ 'adminOptions' ] ) || isset( $_POST[ 'appID' ] ) ) && $hardDemo ) {
        $warn = "This is online Demo, therefore, settings cannot be changed";
    } elseif ( isset( $_POST[ 'adminOptions' ] ) ) {
        foreach ( $_POST as $key => $data ) {
            if ( $key != "adminOptions" )
                $adminOptions[ $key ] = $data;
        }
        if ( is_numeric( $_POST[ 'adminTimeZone' ] ) ) {
            $timezone = 'Etc/GMT' . ( $_POST[ 'adminTimeZone' ] > 0 ? '-' : '+' );
            $timezone .= abs( $_POST[ 'adminTimeZone' ] );
        } else {
            $timezone = $_POST[ 'adminTimeZone' ];
        }
        $adminOptions[ 'adminTimeZone' ] = $timezone;
        $adminOptions[ 'scriptLogo' ] = urlencode( $adminOptions[ 'scriptLogo' ] );
        $adminOptions[ 'scriptFooter' ] = urlencode( $adminOptions[ 'scriptFooter' ] );
        saveAdminOptions();
        setcookie( "FBMPGPLang", $adminOptions[ 'lang' ], time() + 86400 * 365 );
        header( "Location: ./?notify=" . $lang['Settings Saved'] );
        exit;
    } elseif ( isset( $_POST[ 'appID' ] ) && isset( $_POST[ 'appSecret' ] ) ) {
        if ( $db = new PDO( 'sqlite:' . $dbName . '-settings.db' ) ) {
            $statement = $db->prepare( "UPDATE Settings SET appid = \"" . $_POST[ 'appID' ] . "\", secret = \"" . $_POST[ 'appSecret' ] . "\" WHERE admin <> 0" );
            if ( $statement ) {
                $statement->execute();                
            } else {
                showHTML( "Application changing failed while executing database statement." );
            }
            if ( $db = new PDO( 'sqlite:' . $dbName . '-users.db' ) ) {
                $statement = $db->prepare( "UPDATE FB SET usertoken = \"\" WHERE username <> 0" );
                if ( $statement ) {
                    $statement->execute();                
                }
            }
            $adminOptions[ "admintoken" ] = "";
            saveAdminOptions();
            header( "Location: ./" );
            exit;
        }        
    }
    $app    = json_decode( readURL( 'https://graph.facebook.com/v2.3/' . $config[ 'appId' ] . '?access_token=' . $config[ 'appId' ] . '|' . $config[ 'secret' ] ) );
    $output = "<div id='admindiv'><h3>". $lang['Settings'] . " " . $lang['Information'] . ":</h3>";
    if ( isset( $app->id ) )
        $output .= "$successImg <strong>" . $lang['Application'] . " " . $lang['ID'] . "</strong>: " . $app->id;
    else
        $output .= "$failImg <strong>" . $lang['Application'] . " " . $lang['ID'] . "</strong>: <span title='App ID and/or App Secret is Invalid'>" . $config[ 'appId' ] . "</span>";
    
    $output .= "&nbsp;&nbsp;<a id=changeAppText><span title='" . $lang['Change App ID'] . "'>(" . $lang['Change'] . ")</span></a><br />
        <div id=changeApp class='lightbox ui-widget-content'><center>
          <h3 class='lightbox ui-widget-header'>" . $lang['Enter New Application'] . "</h3>
          <form name=changeAppForm id='changeAppForm' class='lightbox' method=post>
          <table>
          <tr><td>" . $lang['Application'] . " " . $lang['ID'] . "<td><input type=text size=10 name=appID class='textbox'><br />
          <tr><td>" . $lang['Application'] . " " . $lang['Secret'] . "<td><input type=text size=10 name=appSecret class='textbox'><br />
          </table><input type=submit id='changeAppSubmit' value='" . $lang['Save Settings'] . "'></form></div>";
    if ( isset( $app->name ) ) {
        $output .= "$successImg <strong>" . $lang['Application'] . " " . $lang['Name'] ."</strong>: " . $app->name . "<br />";
        if ( isset( $adminOptions[ "admintoken" ] ) && $adminOptions[ "admintoken" ] != "" ) {
            try {    
                $permissions = $fb->api( "/v2.3/me/permissions", array( "access_token" => $adminOptions[ "admintoken" ] ) );
                foreach ( $permissions[ 'data' ] as $perm ) {
                    if ( $perm[ 'status' ] == 'granted' ) {
                        $valid = true;
                        break;
                    }
                }    
                if ( isset( $valid ) )
                    $output .= "$successImg <strong>" . $lang['Application'] . " " . $lang['Administrator Token'] ."</strong>: " . $lang['Installed'] . " " . $lang['and'] . " " . $lang['valid'];
                else
                    $output .= "$failImg <strong>" . $lang['Application'] . " " . $lang['Administrator Token'] ."</strong>: " . $lang['Installed'] . " " . $lang['and'] . " " . $lang['invalid'] . "<form name=refresh id=adminToken method=get><input type=hidden name=rg value=1><input type=submit value='" . $lang['Reinstall Token'] . "'></form>";          
            } catch ( Exception $e ) {
                $output .= "$failImg <strong>" . $lang['Application'] . " " . $lang['Administrator Token'] ."</strong>: <span title='" . $e->getMessage() . "'>" . $lang['Installed'] . " " . $lang['and'] . " " . $lang['invalid'] . "</span><form name=refresh id=adminToken method=get><input type=hidden name=rg value=1><input type=submit value='" . $lang['Reinstall Token'] . "'></form>";
            }       
        } else {
            $output .= "$failImg <strong>" . $lang['Application'] . " " . $lang['Administrator Token'] ."</strong>:
            <span title='You may alternatively, logout and signup + authorize as a user who is an administrator of the configured facebook application to install this token'>
                " . $lang['Not Installed'] . "
            </span> <form name=refresh id=adminToken method=get><input type=hidden name=rg value=1><input type=submit value='" . $lang['Install Token'] . "'></form><br />";
        }
    }
    else {
        $output .= "$failImg <strong>" . $lang['Application'] . " " . $lang['Name'] ."</strong>: <br />";    
    }    
    $output .= "<br /><div><form method=POST id=adminForm name=adminForm><input type=hidden name=adminOptions value=1>
        <table class=user>
        <tr><th colspan=2>" . $lang['Admin'] . " " . $lang['Options'] . "
        <tr><td>" . $lang['Demo Mode'] . ":<td><input type=checkbox name=enableDemo " . ( $adminOptions[ 'enableDemo' ] == 0 ? "" : "checked" ) . ">
        <tr><td>" . $lang['Enable'] . " " . $lang['New User Registration'] . ":<td><input type=radio name=enableNUR value=1 " . ( $adminOptions[ 'enableNUR' ] == 1 ? "checked" : "" ) . ">" . $lang['Yes'] . "
            &nbsp;<input type=radio name=enableNUR value=2 " . ( $adminOptions[ 'enableNUR' ] == 2 ? "checked" : "" ) . ">" . $lang['Require Approval'] . "
            &nbsp;<input type=radio name=enableNUR value=0 " . ( $adminOptions[ 'enableNUR' ] == 0 ? "checked" : "" ) . ">" . $lang['No'] . "
        <tr><td>" . $lang['Automatic Role Assignments'] . ":<td><input type=checkbox name=enableARA " . ( $adminOptions[ 'enableARA' ] == 0 ? "" : "checked" ) . ">
        <tr><td>" . $lang['Enable'] . " " . $lang['CRON Scheduling'] . ":<td><input type=checkbox name=useCron " . ( $adminOptions[ 'useCron' ] == 0 ? "" : "checked" ) . ">";
    $output .= "<tr><td>" . $lang['Interface Language'] . ":<td><select name='lang'>";
    $langs = glob( "lang/*.php" );
    foreach ( $langs as $file ) {
        $filename = substr( $file, 5, -9 );
        $output .= "<option value='$filename'" . ( $filename == $adminOptions[ 'lang' ] ? " selected" : "" ) . ">" . strtoupper( $filename );
    }
    $output .= "</select>";
    $output .= "<tr><td>" . $lang['Admin'] . " " . $lang['Time Zone'] . ":<td>";
    $output .= '<select name="adminTimeZone" id="adminTimeZone" class="textbox">
                ' . file_get_contents( 'includes/timezones.html' ) . '
          </select><input type=hidden name=adminTimeZoneId>';
    $output .= "</table>
        <table class=user>
        <tr><th colspan=2>" . $lang['Posting'] . " " . $lang['Options'];    
    $output .= "<tr><td>" . $lang['Minimum'] . " " . $lang['Delay'] . ":<td><select name=minimumDelay>";
    for ( $i = 1; $i <= 180; ++$i ) {
        if ( $i == $adminOptions[ 'minimumDelay' ] )
            $output .= "<option value=$i selected>$i " . $lang['sec'] . "</option>";
        else
            $output .= "<option value=$i>$i " . $lang['sec'] . "</option>";
    }
    $output .= "</select>
        <tr><td>" . $lang['Default'] . " " . $lang['Delay'] . ":<td><select name=defaultDelay>";
    for ( $i = 1; $i <= 180; ++$i ) {
        if ( $i == $adminOptions[ 'defaultDelay' ] )
            $output .= "<option value=$i selected>$i " . $lang['sec'] . "</option>";
        else
            $output .= "<option value=$i>$i " . $lang['sec'] . "</option>";
    }
    $output .= "</select>
        </table>";
    $output .= "<br clear=all><table class=user>
        <tr><th colspan=2>" . $lang['Customization'] . " " . $lang['Options'] . "<br /><small>" . $lang['Leave blank default'] . "</small>
        <tr><td>" . $lang['Theme'] . ":<td><select name='theme' class='textbox'>";
    $themes = glob( "themes/*.html" );
    foreach ( $themes as $file ) {
        $filename = substr( $file, 7, -5 );
        $output .= "<option value='$filename'" . ( $filename == $adminOptions[ 'theme' ] ? " selected" : "" ) . ">" . ucwords( $filename );
    }
    $output .= "</select>
        <tr><td>" . $lang['Logo'] . " " . $lang['URL'] . ":<td><input type=text name='scriptLogo' class='textbox' placeholder='' value='" . $adminOptions[ 'scriptLogo' ] . "'>
        <tr><td>" . $lang['Webpage'] . " " . $lang['Title'] . ":<td><input type=text name='scriptTitle' class='textbox' placeholder='Facebook Multi Page Group Poster' value='" . $adminOptions[ 'scriptTitle' ] . "'>
        <tr><td>" . $lang['Main Heading'] . ":<td><input type=text name='scriptHeading' class='textbox' placeholder='Facebook Poster' value='" . $adminOptions[ 'scriptHeading' ] . "'>
        <tr><td>" . $lang['Extra Footer'] . ":<td><textarea name='scriptFooter' class='textbox' placeholder='HTML allowed. You may also place scripts (like Google Analytics etc.) here'>" . $adminOptions[ 'scriptFooter' ] . "</textarea>";        
    $output .= "</table>";
    if ( $adminOptions[ 'theme' ] != 'fbmpgp' ) {
        $output .= "<br clear=all><table class=user>
                    <tr><th colspan=2>" . $lang['Theme'] . " " . $lang['Options'] . "
                    <tr><td>" . $lang['Main BG Color'] . ":<td><input type=text name='modernMBGC' class='color {hash:true, required:false} textbox' value='" . $adminOptions[ 'modernMBGC' ] . "'>
                    <tr><td>" . $lang['Content BG Color'] . ":<td><input type=text name='modernCBGC' class='color {hash:true, required:false} textbox' value='" . $adminOptions[ 'modernCBGC' ] . "'>
                    <tr><td>" . $lang['Header BG Color'] . ":<td><input type=text name='modernHBGC' class='color {hash:true, required:false} textbox' value='" . $adminOptions[ 'modernHBGC' ] . "'>
                    </table>";
    }    
    $output .= "<br clear=all><p><center><input type=submit value='" . $lang['Save Settings'] . "'></center></p>
        </form></div>";
    
    $output .= "</div>
            <script>
            $( \"#admindiv\" ).tooltip();            
            $('#adminForm').submit(function(event){
                $('#adminForm').block({ 
                    message: '<img src=\"img/loading.gif\">', 
                    timeout: 10000,
                    css: { border: '0px', backgroundColor: 'rgba(255, 255, 255, 0)' },
                    overlayCSS:  { backgroundColor: '#fff', opacity: 0.8 } ,
                    fadeIn:  0
                }); 
                $('input[type=checkbox]').each(function() {
                    if (this.checked) {
                        this.value=1;
                    } else {
                        this.checked=true;
                        this.value=0;
                    }
                });
                document.forms[\"adminForm\"].adminTimeZoneId.value = document.forms[\"adminForm\"].adminTimeZone.selectedIndex;
            });
            $('#adminToken').easyconfirm({
                eventType: 'submit',
                locale: { title: '" . $lang['Important Note'] . "', text: '" . $lang['Admin Token Note'] . "', button: ['" . $lang['Cancel'] . "','" . $lang['Proceed'] . "']}
            });
            $('#changeAppText').easyconfirm({
                eventType: 'click',
                locale: { title: '" . $lang['Important Note'] . "', text: '" . $lang['App Change Note'] . "', button: ['" . $lang['Cancel'] . "','" . $lang['Proceed'] . "']}
            });
            $(document).ready(function() {        
                tz = parseFloat(" . $adminOptions[ 'adminTimeZoneId' ] . ");
                document.getElementById(\"adminTimeZone\").selectedIndex = tz; 
                $('#changeAppText').click(function(e) {
                    $('#changeApp').lightbox_me({
                        centered: true, 
                        onLoad: function() { 
                            $('#changeAppForm').find('input:first').focus()
                            }
                        });
                    //e.preventDefault();
                });
            });
           </script>";
    return $output;
}
?>