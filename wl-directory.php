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
use weblatex\management as wm;
use weblatex\document as doc;
    
require_once(__DIR__."/config.inc.php");
require_once(__DIR__."/classes/main.class.php");
require_once(__DIR__."/classes/management/right.class.php");
require_once(__DIR__."/classes/management/user.class.php");
require_once(__DIR__."/classes/management/group.class.php");
require_once(__DIR__."/classes/document/draft.class.php");
require_once(__DIR__."/classes/document/document.class.php");
require_once(__DIR__."/classes/document/directory.class.php");

    
// read session manually and set language
wl\main::initLanguage();
$loUser = null;    
    
if (isset($_GET["sess"]))
    @session_id($_GET["sess"]);
@session_start();

if ( (isset($_SESSION["weblatex::loginuser"])) && ($_SESSION["weblatex::loginuser"] instanceof wm\user) )
    $loUser = $_SESSION["weblatex::loginuser"];
    
$lcPath = null;
if (isset($_POST["dir"]))
    $lcPath = urldecode($_POST["dir"]);

if ( (empty($loUser)) || (empty($lcPath)) )
    exit();


    
echo "<ul class=\"jqueryFileTree\" style=\"display: none;\">\n";

// we check the $lcPath, if we found the system node (prefix /WebLaTeX/), than we creates manually the correct subtree
$laSystemItems = array_values(array_filter(explode("/", $lcPath), function ($el) { return !empty($el); } )); 
$lnSystemItems = count($laSystemItems);
if ( ($lnSystemItems > 0) && ($laSystemItems[0] == "WebLaTeX") ) {
   
    switch ($lnSystemItems) {
            
        case 1 :
            echo "<li class=\"directory collapsed\"><a href=\"#\" rel=\"/WebLaTeX/Settings/\">"._("settings")."</a></li>\n";
            echo "<li class=\"directory collapsed\"><a href=\"#\" rel=\"/WebLaTeX/Help/\">"._("help")."</a></li>\n";
            echo "<li class=\"file ext_exe\"><a href=\"wl-logout.php?".http_build_query(array("sess" => session_id()))."\">"._("logout")." (".$loUser->getName().")</a></li>\n";
            break;
            
        case 2 :
            
            switch ($laSystemItems[1]) {
            
                case "Settings" :
                    echo "<li class=\"file ext_exe\"><a href=\"#\" rel=\"url\$wl-password.php?".http_build_query(array("sess" => session_id()))."\">"._("change password")."</a></li>\n";
                    break;
            
                case "Help" :
                    echo "<li class=\"file ext_html\">"._("GUI")."</li>\n";
                    echo "<li class=\"file ext_html\">"._("Editor")."</li>\n";
                    echo "<li class=\"file ext_html\">"._("LaTeX")."</li>\n";
                    break;
                    
            }
    }


    echo "</ul>\n";
    exit();
}
    
// we check if the "mydrafts" folder used
if ( ($lnSystemItems > 0) && ($laSystemItems[0] == "myDrafts") ) {
    
    foreach( doc\draft::getList($loUser) as $loItem )
        echo "<li class=\"file ext_txt\"><a href=\"#\" rel=\"draft$".$loItem->getID()."\">".$loItem->getName()."</a></li>\n";
    
    exit();
}
    
    
    
// add database content
$loDirectory = new doc\directory($lcPath);
    
// if we within the root node, add the system menu nodes
if ($loDirectory->isRoot()) {
    echo "<li class=\"directory collapsed\"><a href=\"#\" rel=\"/WebLaTeX/\">WebLaTeX</a></li>\n";
    echo "<li class=\"directory collapsed\"><a href=\"#\" rel=\"/myDrafts/\">"._("my drafts")."</a></li>\n";
}

   
// read subtree
foreach($loDirectory->getChildren() as $loItem) {
    
    $lxAccess = $loItem->getAccess($loUser);
    if (empty($lxAccess))
        continue;
    
    
    if ($loItem instanceof doc\directory)
        // we need a slash at the end, otherwise a infinit loop
        echo "<li class=\"directory collapsed\"><a href=\"#\" rel=\"".$loItem->getFQN()."/\">".$loItem->getName()."</a></li>\n";
    
    if ($loItem instanceof doc\draft) {
        $lcName = "draft$".$loItem->getID();
        echo "<li class=\"file ext_txt\"><a href=\"#\" rel=\"".$lcName."\">".$loItem->getName()."</a></li>\n";
    }
        
    if ($loItem instanceof doc\document) {
        $lcName = "document$".$loItem->getID();
        echo "<li class=\"file ext_doc\"><a href=\"#\" rel=\"".$lcName."\">".$loItem->getName()."</a></li>\n";        
    }
    
}

    
// if we within the root node, we list all documents and draft, that are not linked within the tree
if ($loDirectory->isRoot())
    foreach($loDirectory->getChildrenNotLinked() as $loItem) {
        
        $lxAccess = $loItem->getAccess($loUser);
        if (empty($lxAccess))
            continue;

        if ($loItem instanceof doc\draft) {
            $lcName = "draft$".$loItem->getID();
            echo "<li class=\"file ext_txt\"><a href=\"#\" rel=\"".$lcName."\">".$loItem->getName()."</a></li>\n";
        }
            
        if ($loItem instanceof doc\document) {
            $lcName = "document$".$loItem->getID();
            echo "<li class=\"file ext_doc\"><a href=\"#\" rel=\"".$lcName."\">".$loItem->getName()."</a></li>\n";
        }
            
    }

echo "</ul>\n";
?>