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

/** @file index.php
 * @brief index file, that creates the login sreen or the main view
 *
 * This file creates the login screen, checks the login data and generates
 * the session data. If the session data exists a main empty page with the
 * menu will be shown. Other files, that checks the login data, redirects
 * to this file, if the session can not be verified
 *
 *
 * @var string $lcError
 * error text, if the login is incorrect
 *
 * @var object $loTheme
 * theme object for showing the HTML content
 *
 * @var object $loUser
 * user object, that will be stored within the session and identify the logged-in user
 **/
    
    
    
use weblatex\design as wd;
use weblatex\management as wm;
    
require_once(__DIR__."/classes/design/theme.class.php");
require_once(__DIR__."/classes/management/user.class.php");
require_once(__DIR__."/classes/management/session.class.php");
    
    
// create theme and run initialization
$loTheme = new wd\theme();    
$loUser  = $loTheme->init(false);
    
// sets the login data
$lcError = null;
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
                wm\session::setLoggedInUser($loUser);
            else
                $lcError = "<p id=\"weblatex-error\">"._("password incorrect")."</p>";

    if (!empty($lcError)) {
        wm\session::clearLoggedInUser();
        $loUser = null;
    }
}
 
$loTheme->header( $loUser );
    
// create HTML login form if the user is not logged in, otherweise show the main screen
if (!empty($loUser)) {
    echo "<div id=\"weblatex-menu\">";
    echo "<p class=\"weblatex-logomini\"><a href=\"http://code.google.com/p/weblatex/\" target=\"_blank\">Web<img src=\"images/latex.png\"></a></p>";
    echo "<div id=\"weblatex-directory\"></div>\n";
    echo "</div>";
    
    echo "<div id=\"weblatex-content\"></div>\n";
    
    
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