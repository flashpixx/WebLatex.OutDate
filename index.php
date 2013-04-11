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

/**
 * @todo change base structure to Etherpad (http://etherpad.org/) / Collaborative Working - additional plugins https://github.com/ether/etherpad-lite/wiki/HTTP-API / https://github.com/ether/etherpad-lite-jquery-plugin / https://github.com/ether/etherpad-lite/blob/master/doc/api/http_api.md#creategrouppadgroupid-padname--text
 *
 * @todo add working context menu for file and directory objects (create / deleting)
 * with modal dialogs and jQuery calls
 *
 * @todo srcElement in the JavaScript creates problems on Firefox (is undefined)
 *
 * @todo adding HTTP authentification for using NTLM login on the browser (insert it in the global config)
 *
 * @todo with the PHP option "session.auto_start = 0" there are session problems (session ist not be recovered correctly)
 *
 * @todo adding own exception for determin different errors
 *
 * @todo add a own session table in the database for storing the session id and refernce
 * this id to the lock tables and chat table for removing data rows if the session is released
 *
 * @todo remove directory css & images to the theme directory, so that
 * all can be setup with the theme (rename the css classes / ids maybe)
 *
 * @todo add document editing (the document class must be completed)
 *
 * @todo adding ckeditor plugin for managing the document history
 *
 * @todo adding media plugin for the chkeditor and also a own subpage
 *
 * @todo check gettext calls, is seems to be broken
 *
 * @todo thinkig about another editor https://github.com/adobe/brackets
 *
 * @todo add media browser to the chkeditor with a plugin (images should
 * be shown on fly
 *
 * @todo add plugin for math formula to the ckeditor, that creates
 * the correct TeX code and shows the formula with an image on fly (enable / disable rendering with the config)
 *
 * @todo add export / import function via XML for drafts and documents (descripte XML with XSD schema)
 *
 * @todo create Ajax calls for passwort changing
 *
 * @todo adding some user informations like name, adress, other and add support
 * to the lock and chat commuication
 * 
 * @todo add administrator interface (group / user / right interface)
 *
 * @todo add interface for creating substitues (TeX renewcommand)
 *
 * @todo adding biblatex support
 *
 * @todo add lco support for creating own lco files
 *
 * @todo create installation script like Wordpress installation
 *
 * @todo add an Ajax chat to chat with a user, that locked a document
 *
 * @todo adding a ckeditor plugin for full TikZ support (http://www.texample.net/tikz/)
 *
 * @todo create portet SQL statements for other database systems
 **/
    
    
/**
 * @file index.php
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
    echo "<div id=\"weblatex-dialog\"></div>\n";
    
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