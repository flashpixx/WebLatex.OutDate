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
require_once(__DIR__."/classes/management/group.class.php");
require_once(__DIR__."/classes/document/draft.class.php");



// create theme and run initialization
$loTheme = new wd\theme();    
$loUser  = $loTheme->init();

// the system "draft" right can do everything
$loDraftRight   = new wm\right( wl\config::$system_rights["draft"] );
    
    

// read draft object and save the new data, if the rights are correct
if ( (isset($_GET["id"])) || (isset($_POST["id"])) ) {
    if (isset($_GET["id"]))
        $loDraft = new doc\draft(intval($_GET["id"]));
    else
        $loDraft = new doc\draft(intval($_POST["id"]));
    
    $loLockUser = $loDraft->lock($loUser, true);
    if ($loLockUser instanceof wm\user)
        die("is locked");
    
    
    if ( (isset($_POST["tex"])) && (
         ($loUser->isEqual($loDraft->getOwner())) ||
         ($loDraftRight->hasRigh($loUser)) ||
         (wm\right::hasOne($loUser, $loDraft->getRights("write"))) ||
         (wl\main::any( wm\right::hasOne($loUser->getGroups(), $loDraft->getRights("write")) ))
         )
       ) {
        
        $loDraft->setArchivable( isset($_POST["archivable"]) && !empty($_POST["archivable"]) );
        if ( (isset($_POST["restore"])) && (!empty($_POST["restore"])) )
            $loDraft->restoreHistory(intval($_POST["restore"]));
        else {
            $loDraft->setContent($_POST["tex"]);
            $loDraft->save();
        }
    }
}
    
// delete draft objects if the rights are correct
if (isset($_POST["delete"])) {
    foreach($_POST["delete"] as $lnID) {
        $loDraft = new doc\draft(intval($lnID));
        
        $loLock = $loDraft->hasLock();
        if (!empty($loLock))
            continue;
        
        if ( ($loUser->isEqual($loDraft->getOwner())) ||
             ($loDraftRight->hasRight($loUser)) ||
             (wm\right::hasOne($loUser, $loDraft->getRights("write"))) ||
             (wl\main::any( wm\right::hasOne($loUser->getGroups(), $loDraft->getRights("write")) ))
           )
            doc\draft::delete($lnID);
    }
    unset($loDraft);
}
    
    

// create HTML header, body and main menu
if (empty($loDraft))
    $loTheme->header( $loUser );
else
    $loTheme->header( $loUser, 
                      wd\theme::getEditorCode("wl-autosavedraft.php?".http_build_query(array("sess" => session_id(), "id" => $loDraft->getID())), $loDraft->getHistory(), 
                                !( ($loUser->isEqual($loDraft->getOwner())) || ($loDraftRight->hasRight($loUser)) || 
                                   (wm\right::hasOne($loUser, $loDraft->getRights("write"))) || (wl\main::any( wm\right::hasOne($loUser->getGroups(), $loDraft->getRights("write")) )) 
                                 )
                      ).
                     
                     "<script type=\"text/javascript\">
                            $(window).unload( function() { $.ajax( { url : 'wl-unlock.php?".http_build_query(array("sess" => session_id(), "id" => $loDraft->getID(), "type" => "draft"))."', async : false } ); } );
                     </script>"
                    );

$loTheme->mainMenu( $loUser );


    
echo "<h1>".(empty($loDraft) ? _("draft list") : _("draft")." [".$loDraft->getName()."] "._("edit"))."</h1>\n";
echo "<div id=\"weblatex-document\">\n";

echo "<form action=\"".$_SERVER["PHP_SELF"]."\" method=\"post\">\n";
  
// if the ID parameter is set
if (!empty($loDraft)) {
    echo "<input type=\"hidden\" name=\"id\" value=\"".$loDraft->getID()."\"/>";
    echo "<div><textarea class=\"ckeditor\" name=\"tex\" rows=\"15\" cols=\"80\" tabindex=\"30\">".$loDraft->getContent()."</textarea></div>";
    echo "<input type=\"hidden\" name=\"archivable\" id=\"archivable\" value=\"".($loDraft->isArchivable() ? "1" : null)."\" />\n";
    echo "<input type=\"hidden\" name=\"restore\" id=\"restore\" value=\"\" />\n";
 
// if the ID not set, we create a list of drafts
} else {

    echo "<table>\n";
    echo "<tr><th>"._("delete")."</th><th>"._("draft name")."</th></tr>\n";
    foreach(doc\draft::getList() as $loDraft) {
        
        
        if ( ($loUser->isEqual($loDraft->getOwner())) ||
             ($loDraftRight->hasRight($loUser)) ||
             (wm\right::hasOne($loUser, $loDraft->getRights())) ||
             (wl\main::any( wm\right::hasOne($loUser->getGroups(), $loDraft->getRights()) ))
           ) {
            echo "<tr><td><input type=\"checkbox\" name=\"delete[]\" value=\"".$loDraft->getID()."\"/></td><td>";
            $loLockUser = $loDraft->hasLock();
            if (empty($loLockUser))
                echo "<a href=\"".$_SERVER["PHP_SELF"]."?".http_build_query(array("id" => $loDraft->getID()))."\">".$loDraft->getName()."</a>";
            else
                echo $loDraft->getName()." ("._("locked by")." ".$loLockUser->getName().")";
            echo "</td></tr>\n";
        }
    }
    echo "</table>\n";
    echo "<p><input type=\"submit\" name=\"submit\" class=\"weblatex-button\" value=\""._("delete")."\" tabindex=\"100\"/></p>\n";
}

echo "</form>\n";
echo "</div>\n";

// create HTML footer
$loTheme->footer( $loUser );


?>