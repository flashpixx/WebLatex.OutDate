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
    
    

/** interface for the documents **/
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

    /** creates the lock of the document
     * @param $poUser user object
     * @param $plRefresh if the user has a lock, the lock is refreshed
     **/
    function lock( $poUser, $plRefresh = false );
    
    /** refreshs the lock
     * @param $poUser user object
     **/
    function refreshLock( $poUser );
    
    /** unlocks the document **/
    function unlock();
    
    /** returns the user object if a lock exists
     * @return user object or null
     **/
    function hasLock();
    
    /** checks if the document can be archiveable
     * @return boolean of the flag
     **/
    function isArchivable();
    
    /** sets the archivable flag
     * @param $plArchiveable boolean for enabling / disabling the flag
     **/
    function setArchivable( $plArchiveable );
    
    /** restore a history entry
     * @param $pnID history id
     **/
    function restoreHistory($pnID);
    
    /** deletes the whole history or a single entry
     * @param $pxID null, numeric value or array of numeric values
     **/
    function deleteHistory($pxID = null);
    
    /** returns the content of a history entry
     * @param $pnID entry id
     * @return content
     **/
    function getHistoryContent($pnID);
    
    /** returns an array with ids and timestamps of the history entries
     * @return assoc. array
     **/
    function getHistory();
}


?>