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
 * @file wl-documentpdf.php
 * creates the PDF of a document
 **/



use weblatex as wl;
use weblatex\management as wm;
use weblatex\document as doc;

require_once(__DIR__."/wl-config.inc.php");
require_once(__DIR__."/classes/main.class.php");
require_once(__DIR__."/classes/management/session.class.php");
require_once(__DIR__."/classes/management/right.class.php");
require_once(__DIR__."/classes/management/user.class.php");
require_once(__DIR__."/classes/document/document.class.php");


// read session manually and set language
wl\main::initLanguage();
wm\session::init();
$loUser = wm\session::getLoggedInUser();
   
if ( empty($loUser) ) {
    header("Content-type: text/xml");
    $loXML = new DOMDocument("1.0", "UTF-8");
    $loRoot = $loXML->createElement( "message" );
    $loRoot->appendChild($loXML->createElement("error", _("no active user session found")));
    $loXML->appendChild($loRoot);
    echo $loXML->saveXML();
    exit();
}
    
if ( (!isset($_GET["id"])) || (empty($_GET["id"])) ) {
    header("Content-type: text/xml");
    $loXML = new DOMDocument("1.0", "UTF-8");
    $loRoot = $loXML->createElement( "message" );
    $loRoot->appendChild($loXML->createElement("error", _("document id not set")));
    $loXML->appendChild($loRoot);
    echo $loXML->saveXML();
    exit();
}

    
$loDoc     = new doc\document(intval($_GET["id"]));
if (isset($_GET["build"])) {
    header("Content-type: text/xml");
    $loXML = new DOMDocument("1.0", "UTF-8");
    $loRoot = $loXML->createElement( "message" );
    
    try {
        $loDoc->generatePDF();
    } catch (Exception $e) {
        $loRoot->appendChild($loXML->createElement("error", _("PDF build error: ").$e->getMessage()));
    }
    
    $loXML->appendChild($loRoot);
    echo $loXML->saveXML();
    
} else {
    
    $lcFile = $loDoc->getPDF();
    if (!empty($lcFile)) {
        header("X-Frame-Options: DENY");
        header("Content-type: ".mime_content_type($lcFile));
        header("Content-Transfer-Encoding: binary");
        readfile($lcFile);
    }
}

?>
