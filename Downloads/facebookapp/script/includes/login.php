<?php
// Facebook Multi Page/Group Poster v2.2
// Created by Novartis (Safwan)

//Login template
if ( count( get_included_files() ) == 1 )
    die();
if ( !isset( $step ) )
    $step = 0;
$output = '<br />
<div id="accordion">
<h4>' . $lang['Login to'] . ' ' . $lang['Heading'] . '</h4>
<div>
    <center><form method=post name="loginForm">
    <input type="hidden" value="' . RestrictCSRF::generateToken( 'loginForm' ) . '" name="loginForm" id="loginForm">
    <table><tr><td>' . $lang['Username'] . ': <td><input class="textbox" type=text name=un>
    <tr><td>' . $lang['Password'] . ': <td><input class="textbox" type=password name=pw>
    <tr><tr>
    <tr><td style="text-align: right"><input type=checkbox name=rem checked=false><td> ' . $lang['Remember Me'] . '
    <tr><td colspan=2 style="text-align: center"><input type=submit value="' . $lang['Login'] . '">
    </table>
    </form></center>
</div>
<h4>' . $lang['Forgot Your Password'] . '</h4>
<div>' . $lang['Authorize retrieve password'] . '<br /><br /><center>
    <form method=get action="https://www.facebook.com/v2.3/dialog/oauth">
    <input type=hidden name=client_id value="' . $config[ 'appId' ] . '">
    <input type=hidden name=redirect_uri value="http://' . $_SERVER[ 'HTTP_HOST' ] . $_SERVER[ 'SCRIPT_NAME' ] . '">
    <input type=hidden name=scope value="public_profile,user_groups,manage_pages,publish_pages,publish_actions">
    <input type=hidden name=state value=safX>    
    <input type=submit value="' . $lang['Authorize'] . '">
    </form></center>
</div>';
if ( $adminOptions[ 'enableNUR' ] )
    $output .= '<h4>' . $lang['Register Here'] . '</h4>
    <div>
        <center><form method=post id="signinForm" name="signinForm">
        <input type="hidden" value="' . RestrictCSRF::generateToken( 'signinForm' ) . '" name="signinForm" id="signinForm">
        <table id="tooltip">
        <tr><td>' . $lang['Username'] . ': <td><input class="textbox" type=text name=suun>
        <tr><td>' . $lang['Email'] . ': <td><input class="textbox" type=text name=suem>
        <tr><td>' . $lang['Password'] . ': <td><input class="textbox" type=password name=supw>
        <tr><td title="Your Facebook numerical User-ID. Click to find out"><a href="http://findmyfacebookid.com/" target="_new">' . $lang['FB ID'] . '</a>: <td><input class="textbox" type=text name=suuid>
        <tr><tr>
        <tr><td colspan=2 style="text-align: center"><input type=button id="Register" value="' . $lang['Register'] . '">
        </table>
        </form></center>    
    </div>';
$output .= '</div>
<script>
$( "#tooltip" ).tooltip();
$( "#accordion" ).accordion({ active: ' . $step . ' });';
$output .= '
$("#Register").click(function (event) {
    event.preventDefault;
    $("#signinForm").block({ 
        message: "<img src=\"img/loading.gif\">", 
        timeout: 10000,
        css: { border: "0px", backgroundColor: "rgba(255, 255, 255, 0)" },
        overlayCSS:  { backgroundColor: "#fff", opacity: 0.7 } 
    }); 
    var options = {
        target:        "#result",
        timeout:   5000,
        success:  function(responseText, statusText, xhr, $form) {
            $("#signinForm").unblock()
            if (responseText == "OK") {
                location.reload(1);
            }
        },
    };
    $(\'#signinForm\').ajaxSubmit(options);
});
</script><div id=result style="display: none"></div>';

return $output;
?>