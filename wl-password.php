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
    
$loUser  = null;
if ( (!isset($_SESSION["weblatex::loginuser"])) || (!($_SESSION["weblatex::loginuser"] instanceof wm\user)) )
    @header("Location: index.php");
$loUser = $_SESSION["weblatex::loginuser"];
    
    
    
// do password change
$lcError = null;
if ( (isset($_POST["password1"])) && (isset($_POST["password2"])) )
    if ($_POST["password1"] != $_POST["password2"])
        $lcError = "<p id=\"weblatex-error\">Passw&ouml;rter nicht gleich</p>";
    else
        if (empty($_POST["password1"]))
            $lcError = "<p id=\"weblatex-error\">Passwort darf nicht leer sein</p>";
        else
            $loUser->changePassword($_POST["password1"]);
    

    
// create HTML header, body and main menu
$loTheme->header( $loUser );
$loTheme->mainMenu( $loUser );
  
if (!empty($lcError))
    echo $lcError;
    
echo "<div id=\"weblatex-admin\">\n";
echo "<form action=\"".$_SERVER["PHP_SELF"]."\" method=\"post\">\n";
echo "<label for=\"password1\">neues Passwort (doppelte Eingabe)<br/><input type=\"password\" name=\"password1\" size=\"35\" tabindex=\"10\"/><input type=\"password\" name=\"password2\" size=\"35\" tabindex=\"20\"/></label>\n";
echo "<p><input type=\"submit\" name=\"submit\" class=\"weblatex-button\" value=\"&auml;ndern\" tabindex=\"100\"/></p>\n";
echo "</form>\n";
echo "</div>\n";
    
// create HTML footer
$loTheme->footer( $loUser );

?>