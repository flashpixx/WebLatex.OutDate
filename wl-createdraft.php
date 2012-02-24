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
require_once(__DIR__."/classes/document/draft.class.php");



// create theme and run initialization
$loTheme = new wd\theme();    
$loUser  = $loTheme->init();
    
$loDraft = null;
if (isset($_POST["draft_name"])) {
    
    // catch exception, because the page is reloaded an error occurs
    try {
        $loDraft = doc\draft::create($_POST["draft_name"], $loUser);
    } catch (Exception $e) {
        $loDraft = new doc\draft($_POST["draft_name"]);
    }
    
    
    if ( (isset($_POST["draft_copyfrom"])) && (!empty($_POST["draft_copyfrom"])) ) {
        $loCopyDraft = new doc\draft($_POST["draft_copyfrom"]);
        $loDraft->setContent( $loCopyDraft->getContent() );
        unset($loCopyDraft);
    }
}
    
if ( (isset($_POST["elm1"])) && (isset($_POST["draft_id"])) ) {
    $loDraft = new doc\draft($_POST["draft_id"]);
    $loDraft->setContent( $_POST["elm1"] );
    $loDraft->save();
}


// create HTML header, body and main menu
if (empty($loDraft))
    $loTheme->header( $loUser );
else
    $loTheme->header( $loUser, wd\theme::tinymce );
$loTheme->mainMenu( $loUser );


echo "<h1>".(empty($loDraft) ? _("draft creation") : _("draft")." [".$loDraft->getName()."] "._("edit"))."</h1>\n";
echo "<div id=\"weblatex-document\">\n";
echo "<form action=\"".$_SERVER["PHP_SELF"]."\" method=\"post\">\n";

if (empty($loDraft)) {
    echo "<p><label for=\"draft_name\">"._("draftname")."<br/><input type=\"text\" name=\"draft_name\" size=\"45\" tabindex=\"10\"/></label></p>\n";
    echo "<p><label for=\"draft_copyfrom\">"._("copy from")."<br/>";
    echo "<select name=\"draft_copyfrom\" size=\"1\">\n";
    echo "<option value=\"\">"._("not copy")."</option>\n";
    foreach(doc\draft::getList() as $laItem) {
        print_r($laItem);
    
        // check if the user is owner of the draft
        if ( $loUser->isEqual($laItem["user"]) )
            echo "<option value=\"".$laItem["did"]."\">".$laItem["name"]."</option>\n";
        
        // if not the owner, user must be administrator or draft administrator or has the right
        else {
            $loDraft  = new doc\draft($laItem["did"]);
            if ( wm\right::hasOne($loUser, array_merge($loDraft->getRights(), array( wl\config::$system_groups["administrator"], wl\config::$system_groups["draft"] ))) )
                echo "<option value=\"".$laItem["did"]."\">".$laItem["name"]."</option>\n";
        }
    }
    echo "</select>\n";
} else {
    echo "<input type=\"hidden\" name=\"draft_id\" value=\"".$loDraft->getDID()."\"/>";
    echo "<div><textarea id=\"elm1\" name=\"elm1\" rows=\"15\" cols=\"80\" style=\"width: 80%\">".$loDraft->getContent()."</textarea></div>";
}
    
echo "<p><input type=\"submit\" name=\"submit\" class=\"weblatex-button\" value=\""._("save")."\" tabindex=\"100\"/></p>\n";
    
echo "</form>\n";
echo "</div>\n";


// create HTML footer
$loTheme->footer( $loUser );


?>