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
 **/



use weblatex as wl;
use weblatex\management as wm;
use weblatex\document as doc;

require_once(__DIR__."/wl-config.inc.php");
require_once(__DIR__."/classes/main.class.php");
require_once(__DIR__."/classes/management/session.class.php");
require_once(__DIR__."/classes/management/right.class.php");
require_once(__DIR__."/classes/management/user.class.php");
require_once(__DIR__."/classes/document/draft.class.php");


// read session manually and set language
wl\main::initLanguage();
wm\session::init();
$loUser = wm\session::getLoggedInUser();

// generate return XML
header("Content-type: text/xml");
$loXML = new DOMDocument("1.0", "UTF-8");
$loRoot = $loXML->createElement( "message" );
$loXML->appendChild($loRoot);

// check user session
if ( empty($loUser) ) {
    $loRoot->appendChild($loXML->createElement("error", _("no active user session found")));
    echo $loXML->saveXML();
    exit();
}
    
if (empty($_GET["path"])) {
    $loRoot->appendChild($loXML->createElement("error", _("path is empty")));
    echo $loXML->saveXML();
    exit();    
}

if (empty($_GET["name"])) {
    $loRoot->appendChild($loXML->createElement("error", _("draft name ist empty")));
    echo $loXML->saveXML();
    exit();    
}
    
    
$loDir = new doc\directory(urldecode($_GET["path"]));
if ($loDir->getAccess($loUser) != "w") {
    $loRoot->appendChild($loXML->createElement("error", _("write access denied")));
    echo $loXML->saveXML();
    exit();   
} else
    $loDir->addChild( doc\draft::create(urldecode($_GET["name"]), $loUser) );

echo $loXML->saveXML();
?>