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
 * @file wl-haslock.php
 * @brief file for checking if a lock exists on a document or draft
 *
 * The file checks on a document or draft the lock with the user session
 * data and returns a XML document, that is empty if there is no lock
 * or the lock is created by the current user, otherwise it results
 * the tree with the user data and id of the lock
 *
 *
 * @var object $loUser
 * logged-in user object
 *
 * @var object $loXML
 * DOM XML object for creating the XML content
 *
 * @var object $loRoot
 * DOM XML node, that represents the root element
 *
 * @var object $loDocument
 * document or draft object that is checked for the lock state
 *
 * @var object $loLockedUser
 * user object of the locked user or empty if there is no lock
 *
 * @var object $loXMLUser
 * XML object for the node that represent the locked user data
 *
 * @var object $loAttr
 * DOM XML attribute object, for adding the data to the node
 **/


use weblatex\management as wm;
use weblatex\document as doc;

require_once(__DIR__."/classes/management/session.class.php");
require_once(__DIR__."/classes/management/user.class.php");
require_once(__DIR__."/classes/document/draft.class.php");


// get session data
wm\session::init();
$loUser = wm\session::getLoggedInUser();

if ( (empty($loUser)) || (!isset($_GET["id"])) || (!isset($_GET["type"])) || ($_GET["type"] != "draft") && ($_GET["type"] != "dcoument") )
    exit();
    
    
    
    
// generate return XML
header("Content-type: text/xml");
$loXML = new DOMDocument("1.0", "UTF-8");
$loRoot = $loXML->createElement( "lock" );
$loXML->appendChild($loRoot);

$loDocument = null;
switch ($_GET["type"]) {
    case "draft"        : $loDocument = new doc\draft(intval($_GET["id"]));     break;
    case "document"     : $loDocument = new doc\document(intval($_GET["id"]));  break;
    case "documentpart" : $loDoc = new doc\document(intval($_GET["id"]));       $loDocument = $loDoc->getPart( intval($_GET["pid"]) );  break;
}

$loLockedUser = null;
if (!empty($loDocument)) {
    $loLockedUser = $loDocument->hasLock();
    
    if (!empty($loLockedUser)) {
        $loXMLUser = $loXML->createElement( "user" );
        $loRoot->appendChild($loXMLUser);
        
        $loAttr = $loXML->createAttribute("name");
        $loAttr->value = $loLockedUser->getName();
        $loXMLUser->appendChild( $loAttr );
        
        $loAttr = $loXML->createAttribute("id");
        $loAttr->value = $loLockedUser->getID();
        $loXMLUser->appendChild( $loAttr );
    }
}
    
echo $loXML->saveXML();

?>