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
 * @file wl-documentedit.php
 * file for creating the edit window of a document
 **/



use weblatex as wl;
use weblatex\management as wm;
use weblatex\document as doc;

require_once(__DIR__."/config.inc.php");
require_once(__DIR__."/classes/main.class.php");
require_once(__DIR__."/classes/management/session.class.php");
require_once(__DIR__."/classes/management/right.class.php");
require_once(__DIR__."/classes/management/user.class.php");
require_once(__DIR__."/classes/document/document.class.php");


// read session manually and set language
wl\main::initLanguage();
wm\session::init();
$loUser = wm\session::getLoggedInUser();
   
$loDocument = null;
if (isset($_GET["id"]))
    $loDocument = new doc\document(intval($_GET["id"]));

if ( (empty($loDocument)) || (empty($loUser)) )
    exit();
    
$lxAccess = $loDocument->getAccess($loUser);
if (empty($lxAccess))
    exit();

$loLockedUser = null;
if ($lxAccess == "w")
    $loLockedUser = $loDocument->lock($loUser, true);
    
// create content
echo "<h1>"._("document")." [".$loDocument->getName()."]</h1>\n";
if (($loLockedUser instanceof wm\user) || ($lxAccess == "r")) {
    echo "<p id=\"weblatex-message\">"._("is locked by")." [".$loLockedUser->GetName()."]</p>\n";
    // set the global lock state, because if this is set, the ckeditor ist not instantiate, so
    // the configuration in the main file, used the method for setting the state. We set
    // set it here, because only this scripts knows the lock of the document, otherwise
    // the lock will be refreshed after a while.
    echo "<script type=\"text/javascript\">if (webLaTeX !== undefined) webLaTeX.getInstance().setEditorLock(true);</script>\n";
}

echo "<script type=\"text/javascript\">$( \"#weblatex-documenttabs\" ).tabs();</script>\n";
    
echo "<div id=\"weblatex-documenttabs\">\n";
echo "<ul>";
echo "<li><a href=\"#configuration\">"._("configuration")."</a></li>";
echo "<li><a href=\"#rights\">"._("rights")."</a></li>";
echo "<li><a href=\"#draft\">"._("draft")."</a></li>";
echo "<li><a href=\"#latexmk\">"._("latexmk")."</a></li>";
echo "</ul>\n";

echo "<div id=\"configuration\">configuration</div>\n";
echo "<div id=\"rights\">rights</div>\n";
echo "<div id=\"draft\">draft</div>\n";
echo "<div id=\"latexmk\">latexmk</div>\n";
echo "</div>";

    #echo "<div id=\"weblatex-editor\">".$loDocument->getContent()."</div>";



?>