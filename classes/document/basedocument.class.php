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

namespace weblatex\document;

require_once( dirname(__DIR__)."/base.class.php" );
    
    

/** interface for the documents (directory / draft /document) **/
interface basedocument extends \weblatex\base {
    
    /** returns the owner user object of the document
     * @returns null or the owner user object
     **/
    function getOwner();
    
    /** returns the access of an user
     * @param $poUser user object
     * @return null for no access, "r" read access and "w" for read-write access
     **/
    function getAccess($poUser);
    
    /** returns an array with right objects
     * @param $pcType type of the right, empty all rights, "write" only write access, "read" only read access
     * @return array with rights
     **/
    function getRights($pcType = null);
    
    /** adds a right or changes the access of the right
     * @param $poRight right object
     * @param $plWrite write access
     **/
    function addRight( $poRight, $plWrite = false );
    
    /** deletes a right 
     * @param $poRight right object
     **/
    function deleteRight( $poRight );
    
}


?>