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
 * @file wl-autosave.php
 * @brief file that connects the autosave CKEditor plugin with the database
 *
 * The file is called by the autosave CKEditor autosave plugin and stores
 * the data into the correct database entry and returns the status with
 * a XML file (see autosave documentation)
 *
 *
 * @var object $loUser
 * user object, that will be stored within the session and identify the logged-in user
 *
 * @var object $loXML
 * XML DOM object for creating the XML answer
 *
 * @var object $loAttr
 * XML attribute object
 **/
    
  
    
use weblatex as wl;
use weblatex\management as wm;
use weblatex\document as doc;
    
require_once(__DIR__."/wl-config.inc.php");
require_once(__DIR__."/classes/management/session.class.php");
require_once(__DIR__."/classes/management/right.class.php");
require_once(__DIR__."/classes/management/user.class.php");
require_once(__DIR__."/classes/document/draft.class.php");
require_once(__DIR__."/classes/document/document.class.php");

    
/** function for creating error XML structure
 * @param $poXML DOM XML object
 * @param $pcMsg error message
 * @return XML string
 **/
function createErrorMsg($poXML, $pcMsg) {
    $loRoot = $poXML->createElement( "error" );
    $poXML->appendChild($loRoot);
    
    $loAttr = $poXML->createAttribute("statuscode");
    $loAttr->value = "404";
    $loRoot->appendChild( $loAttr );
    
    $loAttr = $poXML->createAttribute("message");
    $loAttr->value = $pcMsg;
    $loRoot->appendChild( $loAttr );
    
    return $poXML->saveXML();
}
    
    
    
    
// read session manually and set language
wl\main::initLanguage();
wm\session::init();
$loUser = wm\session::getLoggedInUser();
    
    
// generate return XML
header("Content-type: text/xml");
$loXML = new DOMDocument("1.0", "UTF-8");
    
// check user session
if ( empty($loUser) ) {
    echo createErrorMsg($loXML, _("no active user session found"));
    exit();
}

// try to read the id
if (!isset($_GET["id"])) {
    echo createErrorMsg($loXML, _("document id not found"));
    exit();
}

// create the document object
$loDocument = null;
if (isset($_GET["type"]))
    switch ($_GET["type"]) {
        case "draft"        : $loDocument = new doc\draft(intval($_GET["id"]));         break;
        case "document"     : $loDocument = new doc\document(intval($_GET["id"]));      break;
        case "documentpart" : $loDocument = new doc\documentpart(intval($_GET["id"]));  break;
    }
  
    
// check content and document data
if ( (empty($loDocument)) || (!isset($_POST["content"])) ) {
    echo createErrorMsg($loXML, _("document can not be created"));
    exit();
}
    

// check write access
if ($loDocument->getAccess($loUser) != "w") {
    echo createErrorMsg($loXML, _("no write access"));  
    exit();
}
    
// we check the lock state
$loLockedUser = $loDocument->lock($loUser);
if ($loLockedUser instanceof wm\user) {
    echo createErrorMsg($loXML, _("draft is locked by")." [".$loLockedUser->getName()."]");
    exit();
}
    
// write data
$loDocument->setContent($_POST["content"]);

    
// we can exit normally
$loRoot = $loXML->createElement( "result" );
$loXML->appendChild($loRoot);
    
$loAttr = $loXML->createAttribute("status");
$loAttr->value = "ok";
$loRoot->appendChild( $loAttr );

echo $loXML->saveXML();
?>