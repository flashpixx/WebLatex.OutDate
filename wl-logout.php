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
 
/** @file wl-logout.php
 * @brief file for destroying the session and removing the document locks
 *
 * This file destroys the session and remove all locks of the documents
 *
 *
 * @var object $loTheme
 * theme object for showing the HTML content
 *
 * @var object $loUser
 * user object, that will be stored within the session and identify the logged-in user
 **/
    
    
use weblatex as wl;
use weblatex\design as wd;
use weblatex\management as wm;

require_once(__DIR__."/classes/main.class.php");
require_once(__DIR__."/classes/design/theme.class.php");
require_once(__DIR__."/classes/management/user.class.php");

    
// create theme and run initialization
$loTheme = new wd\theme();    
$loUser  = $loTheme->init();

// remove all locks
if ($loUser instanceof wm\user) {
    $loDB = wl\main::getDatabase();
    
    $loDB->Execute("DELETE FROM draft_lock WHERE user=? AND session=?", array($loUser->getID(), session_id()));
}
    
// set session to null and refresh the location
$_SESSION["weblatex::loginuser"] = null;

@session_unset();
@session_destroy();
    
@header("Location: index.php");

?>