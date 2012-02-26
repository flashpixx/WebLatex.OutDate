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
require_once(__DIR__."/classes/management/right.class.php");
require_once(__DIR__."/classes/management/user.class.php");
require_once(__DIR__."/classes/document/draft.class.php");



// create theme and run initialization
$loTheme = new wd\theme();    
$loUser  = $loTheme->init();

    
// read draft object and save the new data
if ( (isset($_GET["id"])) || (isset($_POST["id"])) ) {
    if (isset($_GET["id"]))
        $loDraft = new doc\draft(intval($_GET["id"]));
    else
        $loDraft = new doc\draft(intval($_POST["id"]));
       
    if (isset($_POST["elm1"])) {
        $loDraft->setContent($_POST["elm1"]);
        $loDraft->save();
    }
}
    
// delete draft objects
if (isset($_POST["delete"])) {
    
}

// create HTML header, body and main menu
if (empty($loDraft))
    $loTheme->header( $loUser );
else
    $loTheme->header( $loUser, wd\theme::tinymce );
$loTheme->mainMenu( $loUser );

    
echo "<h1>".(empty($loDraft) ? _("draft list") : _("draft")." [".$loDraft->getName()."] "._("edit"))."</h1>\n";
echo "<div id=\"weblatex-document\">\n";
echo "<form action=\"".$_SERVER["PHP_SELF"]."\" method=\"post\">\n";
  
// if the ID parameter is set
if (!empty($loDraft)) {
    echo "<input type=\"hidden\" name=\"id\" value=\"".$loDraft->getID()."\"/>";
    echo "<div><textarea id=\"elm1\" name=\"elm1\" rows=\"15\" cols=\"80\" style=\"width: 80%\">".$loDraft->getContent()."</textarea></div>";

// if the ID not set, we create a list of drafts
} else {

    $loDraftRight   = new wm\right( wl\config::$system_groups["draft"] );
    $llShow         = $loDraftRight->hasRight($loUser);

    echo "<table>\n";
    echo "<tr><th>"._("delete")."</th><th>"._("draft name")."</th></tr>\n";
    foreach(doc\draft::getList() as $loDraft) {
    
        // check rights of the draft
        if (!$llShow) {
            $llShow = $loUser->isEqual($loDraft->getOwner());
        
            if (!$llShow)
                foreach($loDraft->getRights as $laRight)
                    if ($laRight["right"]->hasRight($loUser)) {
                        $llShow = true;
                        break;
                    }
        }
        
        // show draft entry
        if ($llShow)
            echo "<tr><td><input type=\"checkbox\" name=\"delete[]\" value=\"".$loDraft->getID()."\"/></td><td><a href=\"".$_SERVER["PHP_SELF"]."?".http_build_query(array("id" => $loDraft->getID()))."\">".$loDraft->getName()."</a></td></tr>\n";
    }
    echo "</table>\n";
}

echo "<p><input type=\"submit\" name=\"submit\" class=\"weblatex-button\" value=\""._("accept")."\" tabindex=\"100\"/></p>\n";
echo "</form>\n";
echo "</div>\n";

// create HTML footer
$loTheme->footer( $loUser );


?>