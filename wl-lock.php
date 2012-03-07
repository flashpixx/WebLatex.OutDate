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
 * @file wl-lock.php
 * @brief file for locking / refreshing a document lock
 *
 * The file creates with the session parameters a lock on
 * a draft or refresh a existing lock for the user
 *
 *
 * @var object $loUser
 * logged-in user object
 *
 * @var object $loDoc
 * document / draft object, on which the lock should be
 * created or refreshed
 **/
    
use weblatex\management as wm;
use weblatex\document as doc;

require_once(__DIR__."/classes/management/session.class.php");
require_once(__DIR__."/classes/management/user.class.php");
require_once(__DIR__."/classes/document/draft.class.php");


// get session data
wm\session::init();
$loUser = wm\session::getLoggedInUser();

if ( (!empty($loUser)) && (isset($_GET["id"])) && (isset($_GET["type"])) ) {
    
    // check which document should be refreshed
    $loDoc = null;
    switch (strtolower($_GET["type"])) {
            
        case "draft" :
            $loDoc    = new doc\draft( intval($_GET["id"]) );
            break;
            
    }
    
    if (!empty($loDoc))
        $loDoc->lock($loUser);
}
    


?>