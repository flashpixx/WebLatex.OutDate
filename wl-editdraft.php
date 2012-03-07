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
require_once(__DIR__."/classes/main.class.php");
require_once(__DIR__."/classes/design/theme.class.php");
require_once(__DIR__."/classes/management/right.class.php");
require_once(__DIR__."/classes/management/session.class.php");
require_once(__DIR__."/classes/management/user.class.php");
require_once(__DIR__."/classes/management/group.class.php");
require_once(__DIR__."/classes/document/draft.class.php");


// read session manually and set language
wl\main::initLanguage();
wm\session::init();
$loUser = wm\session::getLoggedInUser();
    

$loDraft = null;
if (isset($_GET["id"]))
    $loDraft = new doc\draft(intval($_GET["id"]));
    
if ( (empty($loDraft)) || (empty($loUser)) )
    exit();
    
$loLockedUser = $loDraft->lock($loUser, true);
    
    
// create content
echo "<h1>"._("draft")." [".$loDraft->getName()."]</h1>\n";
if ($loLockedUser instanceof wm\user)
    echo "<p id=\"weblatex-message\">"._("is locked by")." [".$loLockedUser->GetName()."]</p>\n";
    
echo "<div id=\"weblatex-editor\">".$loDraft->getContent()."</div>";

?>