<?php
// Facebook Multi Page/Group Poster v2.2
// Created by Novartis (Safwan)

if ( count( get_included_files() ) == 1 )
    die();
$message = '
        <script type=text/javascript>
            function showFB(f) {
                s = document.getElementById("fbpost");
                if (f==1) {
                    s.innerHTML = "<div class=\"Row\"><label class=\"Label\">' . $lang['Message'] . ': </label><br/><textarea id=\"Text\" name=\"Message\" cols=\"58\" rows=\"8\"></textarea></div><div class=\"Row\"></div>";
                } else if (f==2) {
                    s.innerHTML = "<div class=\"Row\"><div class=\"RowSm\"><label class=\"Label\">' . $lang['Link'] . ' ' . $lang['URL'] . ':</label><br/><input name=\"URL\" id=\"Link\" type=\"text\" size=\"80\" /></div></div>
                    <div class=\"Row\"><label class=\"Label\">' . $lang['Message'] . ': </label><br/><textarea id=\"Text\" name=\"Message\" cols=\"58\" rows=\"8\"></textarea></div>
                    <div class=\"Row\"><label class=\"Label\">' . $lang['Link'] . ' ' . $lang['Title'] . ':</label><br/><input name=\"Title\" id=\"Title\" type=\"text\" size=\"80\"></div>
                    <div class=\"Row\"><label class=\"Label\">' . $lang['Link'] . ' ' . $lang['Description'] . ': </label><br/><textarea id=\"Desc\" name=\"Description\" cols=\"58\" rows=\"8\"></textarea></div>
                    <div class=\"Row\"><label class=\"Label\">' . $lang['Link'] . ' ' . $lang['Caption'] . ':</label><br/><input name=\"Caption\" id=\"Caption\" type=\"text\" size=\"80\"></div>
                    <div class=\"Row\"><label class=\"Label\">' . $lang['Picture'] . ' ' . $lang['URL'] . ':</label><br/><input name=\"Picture\" id=\"Picture\" type=\"text\" size=\"80\"></div>";
                } else if (f==3  || f==4) {
                    s.innerHTML = "<div class=\"Row\"><label class=\"Label\">' . $lang['Image'] . ' ' . $lang['Description'] . ': </label><br/><textarea id=\"Text\" name=\"Message\" cols=\"58\" rows=\"8\"></textarea></div><div class=\"Row\"><div class=\"RowSm\"><label class=\"Label\">' . $lang['Image'] . ' ' . $lang['URL'] . ':</label><br/><input name=\"URL\" id=\"Link\" type=\"text\" size=\"80\" /><br><input type=checkbox name=proxy>' . $lang['Use'] . ' ' . $lang['Image'] . ' ' . $lang['Proxy'] . '?</div></div>";
                } else if (f==5) {
                    s.innerHTML = "<div class=\"Row\"><label class=\"Label\">' . $lang['Video'] . ' ' . $lang['Title'] . ':</label><br/><input name=\"Title\" id=\"Title\" type=\"text\" size=\"80\"></div><div class=\"Row\"><label class=\"Label\">' . $lang['Video'] . ' ' . $lang['Description'] . ':</label><br/><textarea id=\"Text\" name=\"Message\" cols=\"58\" rows=\"8\"></textarea></div><div class=\"Row\"><div class=\"RowSm\"><label class=\"Label\" title=\"(Local Server Path, Youtube Video URL or Video file URL)\">' . $lang['Video'] . ' ' . $lang['URL'] . ':</label><br/><input name=\"URL\" id=\"Link\" type=\"text\" size=\"80\" /></div></div>";
                }
            }
        </script>
        <form id="FBform" method=post name="FBform">            		
        <input type=hidden name=pageid>            
        <div>
        <div class="clear container text-center">
            <div class="Row">
            <label class="Label">' . $lang['Wish Message'] . '</label><br />
            <div id="radioset" style="font-size: 0.7em;margin-top: 5px">
                &nbsp;<input type="radio" name="Type" onclick="showFB(1)" id="TypeT" value="T" checked="checked" /><label for="TypeT" class="RowSm">' . $lang['Text'] . ' ' . $lang['Post'] . '</label>
                &nbsp;<input type="radio" name="Type" onclick="showFB(2)" id="TypeL" value="L" /><label for="TypeL" class="RowSm">' . $lang['Link'] . '</label>
                &nbsp;<input type="radio" name="Type" onclick="showFB(3)" id="TypeI" value="I"/><label for="TypeI" class="RowSm">' . $lang['Image'] . '</label>
                &nbsp;<input type="radio" name="Type" onclick="showFB(4)" id="TypeA" value="A" /><label for="TypeA"class="RowSm">' . $lang['Album'] . ' ' . $lang['Post'] . '</label>
                &nbsp;<input type="radio" name="Type" onclick="showFB(5)" id="TypeV" value="V" /><label for="TypeV" class="RowSm">' . $lang['Video'] . '</label>
            </div></div><script>$( "#radioset" ).buttonset();</script>
            <br />
            <div id=fbpost>
                <div class="Row"><label class="Label">' . $lang['Message'] . ': </label><br/><textarea id="Text" name="Message" cols="58" rows="8"></textarea></div><div class="Row"></div>
            </div>
        </div>
        <br /><hr>
        <div class="Row">
          <div class="Left">
          <br />
          <div>' . $lang['Select'] . ' ' . $lang['Your'] . ' ' . $lang['Timezone'] . ':
          <select name="timezone" id="timezone">
                ' . file_get_contents( 'includes/timezones.html' ) . '
          </select></div><br /><br />
          <div>' . $lang['When to Post'] . ':<b>
          <label>' . $lang['Date'] . ':</label><input type=text id=date name=date size=15> <label>' . $lang['Time'] . ':</label><input type=text id=time name=time size=15></b></div>
          <br />
          <span id="Delay" title="--Advisable Delays For Posting--
               3-25 Groups/Pages: 3-10 sec, 25-50 Groups/Pages: 10-25 sec, 
               50+ Groups/Pages: at least 25 sec or more,  The Larger the delay, the less probability of getting blocked by Facebook.">' . $lang['Select'] . ' ' . $lang['Delay'] . ':</span> <select name=delay>';
for ( $z = $adminOptions[ 'minimumDelay' ]; $z <= 180; ++$z ) {
    if ( $z == $adminOptions[ 'defaultDelay' ] )
        $message .= "<option value=$z selected>$z " . $lang['sec'] . "</option>";
    else
        $message .= "<option value=$z>$z " . $lang['sec'] . "</option>";
}
$message .= '</select>
          <div class="submit"><input style="font-weight: bold; width: 70px;" type="button" value="' . $lang['Post'] . '" id=SubmitPosts>&nbsp;&nbsp;&nbsp;&nbsp;<input id="CloseBt" style="width: 70px;" class="bClose" type="button" value="' . $lang['Clear'] . '"><br /><br /><div id="submitting" style="display:block;visibility:hidden" >' . $lang['Please'] . ' ' . $lang['Wait'] . ' - ' . $lang['Posting'] . '<img style="vertical-align: middle;" src="img/sending.gif" /></div></div>
          <div id="LoaderPost" style="display: none";> <img src="img/loading.gif" /> ' . $lang['Posting'] . '...., ' . $lang['take time'] . '...  </div>
          <div class="Right">

      </div>
      </div>
      </div>
      </div></form>
      <div><h2>' . $lang['Select'] . ' ' . $lang['Pages'] . '/' . $lang['Groups'] . ':</h2>
      <p><center>
      <a href=# onclick="$(\'.chk\').prop(\'checked\', true);return false;">' . $lang['Select'] . ' ' . $lang['All'] . '</a>&nbsp;
      <a href=# onclick="$(\'.chkpage\').prop(\'checked\', true);return false;">' . $lang['Select'] . ' ' . $lang['All'] . ' ' . $lang['Pages'] . '</a>&nbsp;
      <a href=# onclick="$(\'.chkgroup\').prop(\'checked\', true);return false;">' . $lang['Select'] . ' ' . $lang['All'] . ' ' . $lang['Groups'] . '</a>&nbsp;
      <a href=# onclick="$(\'.chk\').prop(\'checked\', false);return false;">' . $lang['De Select'] . ' ' . $lang['All'] . '</a></center></p>';
if ( $adminOptions[ 'enableDemo' ] )
    $message .= '<div class="ui-state-highlight ui-corner-all" style="margin-top: 20px; padding: 0 .7em;">
                            <center><p><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
                            <strong>Online Demo Restriction:</strong> Only at most 2 Pages and 5 Groups will be shown.
                            <br /><center>Buy this script for full functionality.</p></center>
                        </div>';
$message .= "<br><div onclick='chkToggle(event,\"#chk$userId\")' class='page even'><input onclick='chkToggle(event,null)' id=chk$userId class='chk chkpage' type=checkbox value=$userId>" . $lang['Your'] . " " . $lang['Profile'] . "<a class='visit' href='http://www.facebook.com/$userId' target='_blank'>" . $lang['Visit'] . "</a><div id=$userId class=results onclick='chkToggle(event,null)'></div></div>\n";
$i = 0;
$message .= "<br>" . $lang['Pages'] . " (%%p%%):";
foreach ( $pages as $page ) {
    if ( $page != "" ) {
        ++$i;
        $p = explode( ":", $page );
        $message .= "<div onclick='chkToggle(event,\"#chk$p[0]\")' class='page " . ( ( $i % 2 ) == 0 ? 'even' : 'odd' ) . "'><input onclick='chkToggle(event,null)' id=chk$p[0] class='chk chkpage' type=checkbox value=$p[0]>" . htmlentities( urldecode( $p[ 2 ] ), ENT_COMPAT, 'UTF-8' ) . "<a class='visit' href='http://www.facebook.com/$p[0]' target='_blank'>" . $lang['Visit'] . "</a><div id=$p[0] class=results onclick='chkToggle(event,null)'></div></div>\n";
        if ( $adminOptions[ 'enableDemo' ] && $i == 2 )
            break;
    }
}
$j = 0;
$message .= "<br>" . $lang['Groups'] . " (%%g%%):";
foreach ( $groups as $group ) {
    if ( $group != "" ) {
        ++$j;
        $g = explode( ":", $group );
        @$message .= "<div onclick='chkToggle(event,\"#chk$g[0]\")' class='group " . ( ( $j % 2 ) == 0 ? 'even' : 'odd' ) . "'><input onclick='chkToggle(event,null)' id=chk$g[0] class='chk chkgroup' type=checkbox value=$g[0]>" . htmlentities( urldecode( $g[ 1 ] ), ENT_COMPAT, 'UTF-8' ) . "<a class='visit' href='http://www.facebook.com/$g[0]' target='_blank'>" . $lang['Visit'] . "</a><div id=$g[0] class=results onclick='chkToggle(event,null)'></div></div>\n";
        if ( $adminOptions[ 'enableDemo' ] && $j == 5 )
            break;
    }
}
$message = str_replace( array(
     "%%g%%",
    "%%p%%" 
), array(
     $j,
    $i 
), $message );
$message .= '</div>
    <div id="Result">&nbsp;</div>
    <script>';
$script = '
    function readCookie(cookieName) {
        var theCookie=" "+document.cookie;
        var ind=theCookie.indexOf(" "+cookieName+"=");
        if (ind==-1) ind=theCookie.indexOf(";"+cookieName+"=");
        if (ind==-1 || cookieName=="") return "";
        var ind1=theCookie.indexOf(";",ind+1);
        if (ind1==-1) ind1=theCookie.length; 
        return unescape(theCookie.substring(ind+cookieName.length+2,ind1));
    }
    function chkToggle(e,f) {
        e.stopPropagation();
        if (f !== null) $(f).click();
    }
    function setTimeZone() {
        tz = parseFloat(readCookie("FBMPGPTimezone"));
        document.getElementById("timezone").selectedIndex = tz;
    }
    $("#CloseBt").click(function (event) {
        $(\'#FBform\').resetForm();
        $(".results").html("");
        $("#submitting").css("display","none");
        showFB(1);
        setTimeZone();
    });
    $("#SubmitPosts").click(function (event) {
        //event.preventDefault;
        i = 0;
        j = 0;
        $("input:checkbox:checked").each(function() {
                if (this.name != "proxy") ++j;
            }
        );
        if (j>0) {
            tz = document.forms["FBform"].timezone.selectedIndex;
            tv = document.forms["FBform"].timezone.value;
            document.cookie="FBMPGPTimezone="+tz+"; expires=Tue, 31 Dec 2052 12:00:00 UTC";
            document.cookie="FBMPGPTimezoneValue="+tv+"; expires=Tue, 31 Dec 2052 12:00:00 UTC";
            document.getElementById("SubmitPosts").disabled = true;
            document.getElementById("CloseBt").disabled = true;
            $("#submitting").css("visibility","visible");
        }
        pDelay = parseInt(document.forms["FBform"].delay.value)*1000;
        $("input:checkbox:checked").each(
        function()
        {
            t = this.value;
            //alert(this.name);
            if (this.name == "proxy") return;
            setTimeout((function(x,k){
                return function() {
                    document.forms["FBform"].pageid.value=x;
                    //alert(document.forms["FBform"].pageid.value);
                    if (j-k == 1) fn = showResp; else fn = null;
                    var options = {
                        target:        "#"+x,   // target element(s) to be updated with server response
                        //async: false,
                        //timeout:   5000 ,
                        beforeSubmit:  function(formData, jqForm, options) {
                            var queryString = $.param(formData);
                            //alert(formData[0].value);
                            document.getElementById(formData[0].value).innerHTML=" <img src=\"img/loading.gif\" class=bottom /> ' . $lang['Posting'] . '...., ' . $lang['take time'] . '... ";
                        }, // pre-submit callback
                        success: fn
                        // other available options:
                        //url:       url         // override for form\'s \'action\' attribute
                        //type:      type        // \'get\' or \'post\', override for form\'s \'method\' attribute
                        //dataType:  null        // \'xml\', \'script\', or \'json\' (expected server response type)
                        //clearForm: true        // clear all form fields after successful submit
                        //resetForm: true        // reset the form after successful submit
                
                        // $.ajax options can be used here too, for example:
                    };
                    $(\'#FBform\').ajaxSubmit(options);
                }
            })(t,i),i*pDelay);
            ++i;
        });
    });    
    $(document).ready(function() {
        $("#Delay").tooltip();
        $(\'#date\').click(function() { $(\'#date\').pickadate({
                                                        today: \'\',
                                                        max: 150,
                                                        min: true,
                                                        format: \'d mmmm yyyy\'
                                                    }); } );
        $(\'#time\').click(function() { $(\'#time\').pickatime({
                                                        editable: true
                                                    }); } );
        setTimeZone();
    });
    function showResp(responseText, statusText, xhr, $form)  {
        $("#submitting").css("visibility","hidden");
        document.getElementById("SubmitPosts").disabled = false;
        document.getElementById("CloseBt").disabled = false;
    }';
?>