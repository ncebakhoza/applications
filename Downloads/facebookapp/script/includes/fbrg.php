<?php
// Facebook Multi Page/Group Poster v2.2
// Created by Novartis (Safwan)

if ( count( get_included_files() ) == 1 )
    die();

// Page/Groups Refresh Data
if ( $hardDemo && $userName == "Multi" )
    return;
authRedirect();
?>