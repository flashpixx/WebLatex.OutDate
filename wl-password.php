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
 * $LastChangedDate$
 * $Author$
 *
 * @file wl-password.php
 * @brief file for changing the user password
 *
 * The file creates the HTML content for the password change page
 * and runs the changing code
 **/
    
use weblatex as wl;
use weblatex\management as wm;
    
require_once(__DIR__."/classes/main.class.php");
require_once(__DIR__."/classes/management/user.class.php");
require_once(__DIR__."/classes/management/session.class.php");
    
   
    
    // do password change
/*
    $lcError = null;
    if ( (isset($_POST["password1"])) && (isset($_POST["password2"])) )
    if ($_POST["password1"] != $_POST["password2"])
    $lcError = "<p id=\"weblatex-error\">"._("password are not equal")."</p>";
    else
    if (empty($_POST["password1"]))
    $lcError = "<p id=\"weblatex-error\">"._("password should not be empty")."</p>";
    else
    $loUser->changePassword($_POST["password1"]);
  */  
    
    
// read session manually and set language
wl\main::initLanguage(); 
wm\session::init();
$loUser = wm\session::getLoggedInUser();
    
if (empty($loUser))
    exit();
   
// create content
echo "<h1>"._("change password")."</h1>\n";
//http://net.tutsplus.com/tutorials/javascript-ajax/submit-a-form-without-page-refresh-using-jquery/
echo "<form action=\"".$_SERVER["PHP_SELF"]."\" method=\"post\">\n";
echo "<label for=\"password1\">"._("new password (insert twice)")."<br/><input type=\"password\" name=\"password1\" size=\"35\" tabindex=\"10\"/><input type=\"password\" name=\"password2\" size=\"35\" tabindex=\"20\"/></label>\n";
echo "<p><input type=\"submit\" name=\"submit\" class=\"weblatex-button\" value=\""._("change")."\" tabindex=\"100\"/></p>\n";
echo "</form>\n";
?>