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

use weblatex as wl;
use weblatex\design as wd;
use weblatex\management as wm;
use weblatex\document as doc;

require_once(__DIR__."/config.inc.php");
require_once(__DIR__."/classes/design/theme.class.php");
require_once(__DIR__."/classes/management/user.class.php");
require_once(__DIR__."/classes/document/document.class.php");
require_once(__DIR__."/classes/document/draft.class.php");

    
    
// create theme and run initialization
$loTheme = new wd\theme();    
$loUser  = $loTheme->init();
    
    
// create HTML header, body and main menu
$loTheme->header( $loUser );
$loTheme->mainMenu( $loUser );
    

echo "<div id=\"weblatex-document\">\n";
echo "<form action=\"".$_SERVER["PHP_SELF"]."\" method=\"post\">\n";
    
echo "<select name=\"draft\" size=\"5\">\n";
foreach(doc\draft::getList() as $laItem)
    // check if the user is owner of the draft
    if ( $loUser->isEqual($laItem["user"]) )
        echo "<option value=\"".$laItem["did"]."\">".$laItem["name"]."</option>\n";
    
    // if not the owner, user must be administrator or draft administrator or has the right
    else {
        $loDraft  = new doc\draft($laItem["did"]);
        if ( wm\right::hasOne($loUser, array_merge($loDraft->getRights(), array( wl\config::$system_rights["administrator"], wl\config::$system_rights["draft"] ))) )
            echo "<option value=\"".$laItem["did"]."\">".$laItem["name"]."</option>\n";
    }
echo "</select>\n";

echo "</form>\n";
echo "</div>\n";


// create HTML footer
$loTheme->footer( $loUser );
        

?>