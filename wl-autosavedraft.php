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
require_once(__DIR__."/classes/management/right.class.php");
require_once(__DIR__."/classes/management/user.class.php");
require_once(__DIR__."/classes/document/draft.class.php");

    
// read session manually    
$loUser = null;    

if (isset($_GET["sess"]))
    @session_id($_GET["sess"]);
@session_start();
    
if ( (isset($_SESSION["weblatex::loginuser"])) && ($_SESSION["weblatex::loginuser"] instanceof wm\user) )
    $loUser = $_SESSION["weblatex::loginuser"];

    
    
// generate return XML
header("Content-type: text/xml");
$loXML = new DOMDocument("1.0", "UTF-8");

// user session not found
if (empty($loUser)) {
    $loRoot = $loXML->createElement( "error" );
    $loXML->appendChild($loRoot);
    
    $loAttr = $loXML->createAttribute("statuscode");
    $loAttr->value = "404";
    $loRoot->appendChild( $loAttr );

    $loAttr = $loXML->createAttribute("message");
    $loAttr->value = _("no active user session found");
    $loRoot->appendChild( $loAttr );
    
    echo $loXML->saveXML();
    exit();
}

// try to read draft id
if (!isset($_GET["id"])) {
    $loRoot = $loXML->createElement( "error" );
    $loXML->appendChild($loRoot);
    
    $loAttr = $loXML->createAttribute("statuscode");
    $loAttr->value = "404";
    $loRoot->appendChild( $loAttr );
    
    $loAttr = $loXML->createAttribute("message");
    $loAttr->value = _("draft id not found");
    $loRoot->appendChild( $loAttr );
    
    echo $loXML->saveXML();
    exit();
}
    
    
// create draft object and write data
if (isset($_POST["content"])) {
    $loDraft = new doc\draft(intval($_GET["id"]));
    $loDraft->refreshLock($loUser);
    
    if ( ($loUser->isEqual($loDraft->getOwner())) ||
         ($loDraftRight->hasRigh($loUser)) ||
         (wm\right::hasOne($loUser, $loDraft->getRights("write"))) ||
         (wl\main::any( wm\right::hasOne($loUser->getGroups(), $loDraft->getRights("write")) ))
       ) {
        $loDraft->setContent($_POST["content"]);
        $loDraft->save();
    }
}

    
// we can exit normally
$loRoot = $loXML->createElement( "result" );
$loXML->appendChild($loRoot);
    
$loAttr = $loXML->createAttribute("status");
$loAttr->value = "ok";
$loRoot->appendChild( $loAttr );

echo $loXML->saveXML();
?>