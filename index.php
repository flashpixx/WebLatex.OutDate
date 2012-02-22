<?php
    
/** 
 @cond
 ############################################################################
 # LGPL License                                                             #
 #                                                                          #
 # This file is part of the WebLaTeX system           .                     #
 # Copyright (c) 2012 <http://code.google.com/p/weblatex/>                  #
 # This program is free software: you can redistribute it and/or modify     #
 # it under the terms of the GNU Lesser General Public License as           #
 # published by the Free Software Foundation, either version 3 of the       #
 # License, or (at your option) any later version.                          #
 #                                                                          #
 # This program is distributed in the hope that it will be useful,          #
 # but WITHOUT ANY WARRANTY; without even the implied warranty of           #
 # MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            #
 # GNU Lesser General Public License for more details.                      #
 #                                                                          #
 # You should have received a copy of the GNU Lesser General Public License #
 # along with this program. If not, see <http://www.gnu.org/licenses/>.     #
 ############################################################################
 @endcond
 **/

use weblatex\design as wd;
use weblatex\management as wm;
    
require_once(__DIR__."/classes/design/theme.class.php");
require_once(__DIR__."/classes/management/user.class.php");

    
    
// create theme and run initialization
$loTheme = new wd\theme();    
$loTheme->init();
    
// check login data and initialize session data
$lcError = null;

$loUser  = null;
if ( (isset($_SESSION["weblatex::loginuser"])) && ($_SESSION["weblatex::loginuser"] instanceof wm\user) )
    $loUser = $_SESSION["weblatex::loginuser"];
   
if ( (empty($loUser)) && (isset($_POST["user_login"])) && (isset($_POST["user_pass"])) ) {
    
    try {
        $loUser = new wm\user($_POST["user_login"]);
    } catch (Exception $e) {
        $lcError = "<p id=\"weblatex-error\">"._("login not found")."</p>";
    }
    
    if (!empty($loUser))
        if (!$loUser->canLogin())
            $lcError = "<p id=\"weblatex-error\">"._("login disable")."</p>";
        else
            if ($loUser->authentificate($_POST["user_pass"]))
                $_SESSION["weblatex::loginuser"] = $loUser;
            else
                $lcError = "<p id=\"weblatex-error\">"._("password incorrect")."</p>";

    if (!empty($lcError)) {
        $_SESSION["weblatex::loginuser"] = null;
        $loUser                          = null;
    }
}
 
    
    
// create HTML header & body
$loTheme->header( $loUser );

// create HTML login form if use is not logged in, otherweise show "my documents"
if (!empty($loUser)) {
    $loTheme->mainMenu( $loUser );
} else {
    echo "<div id=\"weblatex-admin\">\n";
    echo "<p id=\"weblatex-logo\"><a href=\"http://code.google.com/p/weblatex/\" target=\"_blank\">Web<img src=\"images/latex.png\"></a></p>\n";
    echo $lcError;
    echo "<form action=\"".$_SERVER["PHP_SELF"]."\" method=\"post\">\n";
    echo "<p><label for=\"user_login\">"._("username")."<br/><input type=\"text\" name=\"user_login\" size=\"35\" tabindex=\"10\"/></label></p>\n";
    echo "<p><label for=\"user_pass\">"._("password")."<br /><input type=\"password\" name=\"user_pass\" size=\"35\" tabindex=\"20\"/></label></p>\n";
    echo "<p><input type=\"submit\" name=\"submit\" class=\"weblatex-button\" value=\""._("login")."\" tabindex=\"100\"/></p>\n";
    echo "</form></div>\n";
}
    
// create HTML footer
$loTheme->footer( $loUser );
    
    
?>