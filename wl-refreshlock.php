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


use weblatex\management as wm;
use weblatex\document as doc;

require_once(__DIR__."/classes/management/user.class.php");
require_once(__DIR__."/classes/document/draft.class.php");


// read session manually    
$loUser = null;    

if (isset($_GET["sess"]))
    @session_id($_GET["sess"]);
@session_start();

if ( (isset($_SESSION["weblatex::loginuser"])) && ($_SESSION["weblatex::loginuser"] instanceof wm\user) )
    $loUser = $_SESSION["weblatex::loginuser"];


if ( ($loUser instanceof wm\user) && (isset($_GET["id"])) && (isset($_GET["type"])) ) {
    
    // check which document should be refreshed
    switch (strtolower($_GET["type"])) {
            
        case "draft" :
            $loDraft    = new doc\draft( intval($_GET["id"]) );
            $loDraft->lock($loUser, true);
            break;
            
    }
    
}


?>