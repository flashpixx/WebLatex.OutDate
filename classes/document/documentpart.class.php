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
use weblatex as wl;
use weblatex\management as man;

require_once( dirname(dirname(__DIR__))."/config.inc.php" );
require_once( dirname(__DIR__)."/main.class.php" );
require_once( dirname(__DIR__)."/management/user.class.php" );
require_once( __DIR__."/baseedit.class.php" );


/** class of representation a document part like chapter or subsection **/
class documentpart implements baseedit {
    
    /* document id **/
    private $mnID           = null;
    /** document id **/
    private $mnDocument     = null;
    /** database object **/
    private $moDB           = null;
    
    
    
    /** constructor
     * @param $px document id
     **/
    function __construct( $px ) {
        if (!is_numeric($px)) 
            wl\main::phperror( "argument must be a numeric value", E_USER_ERROR );
        
        $this->moDB = wl\main::getDatabase();
        
        $loResult = $this->moDB->Execute( "SELECT document FROM documentpart WHERE id=?", array($px) );
        if ($loResult->EOF)
            throw new \Exception( "documentpart data not found" );
            
        $this->mnID         = $px;
        $this->mnDocument   = intval($loResult->fields["document"]);
    }
    
    /** returns the document part id
     * @return id
     **/
    function getID() {
        return $this->mnID;
    }
    
    /** returns the document part name / description
     * @return name or null
     **/
    function getName() {
        $loResult = $this->moDB->Execute( "SELECT description FROM documentpart WHERE id=?", array($this->mnID) );
        if (!$loResult->EOF)
            return $loResult->fields["description"];
        return null;        
    }
    
    /** sets the document part name
     * @param $pc description
     **/
    function setName($pc) {
        $this->moDB->Execute( "UPDATE documentpart SET description=? WHERE id=?", array($pc, $this->mnID) );
    }
    
    /** returns the position value
     * @return value
     **/
    function getPosition() {
        $loResult = $this->moDB->Execute( "SELECT position FROM documentpart WHERE id=?", array($this->mnID) );
        if (!$loResult->EOF)
            return intval($loResult->fields["position"]);
        return null; 
    }
    
    /** sets the position value
     * @param $pn null or uint position
     **/
    function setPosition($pn) {
        if (empty($pn))
            $this->moDB->Execute( "UPDATE documentpart SET position=? WHERE id=?", array(null, $this->mnID) );
        else {
            
            $loResult = $this->moDB->Execute( "SELECT id FROM documentpart WHERE id != ? AND position=?", array($this->mnID, $pn) );
            if (!$loResult->EOF)
                throw new \Exception( "position value is not unique" );
            
            $this->moDB->Execute( "UPDATE documentpart SET position=? WHERE id=?", array($pn, $this->mnID) );
        }
    }
    
    
    /** sets the part content
     * @param $pcContent text information
     **/
    function setContent( $pc ) {
        //check first the archivable flag and stores the old data
        if ($this->isArchivable())
            $this->moDB->Execute("INSERT IGNORE INTO documentpart_history (documentpartid, content) SELECT id, content FROM documentpart WHERE id=?", array($this->mnID));
        
        $this->moDB->Execute("UPDATE documentpart SET content=? WHERE id=?", array($pc, $this->mnID));
    }
    
    /** gets the content of the part
     * @return data
     **/
    function getContent() {
        $loResult = $this->moDB->Execute( "SELECT content FROM documentpart WHERE id=?", array($this->mnID) );
        if (!$loResult->EOF)
            return $loResult->fields["content"];
        return null;
    }
    
    /** gets the owner of the document part (equal to the owner of the document)
     * @return owner id
     **/
    function getOwner() {
        $loResult = $this->moDB->Execute( "SELECT owner FROM document WHERE id=?", array($this->mnDocument) );
        if ($loResult->EOF)
            return man\user( intval($loResult->fields["owner"]) );
        return null;
    }
    
    /** returns the access of an user
     * @param $poUser user object
     * @return null for no access, "r" read access and "w" for read-write access
     **/
    function getAccess($poUser) {
        if (!($poUser instanceof man\user))
            wl\main::phperror( "argument must be a user object", E_USER_ERROR );
        
        // document & administrator right
        $loDocumentRight = new man\right( wl\config::$system_rights["document"] );
        $loAdminRight    = new man\right( wl\config::$system_rights["administrator"] );
        
        // check if the user is the owner or has administrator or document right
        if ( ($poUser->isEqual($this->getOwner())) || ($loDocumentRight->hasRight($poUser)) || ($loAdminRight->hasRight($poUser)) )
            return "w";
        
        
        // get user groups
        $laGroups = $poUser->getGroups();
        
        // check if a user group has admin or document right
        if ( (wl\main::any( man\right::hasOne($laGroups, array($loDocumentRight)))) || (wl\main::any( man\right::hasOne($laGroups, array($loAdminRight)))) )
            return "w";
        
        
        //get read and write rights of this document
        $laReadRight  = $this->getRights("read");
        $laWriteRight = $this->getRights("write");
        
        
        // check the other rights of the user
        if (man\right::hasOne($poUser, $laReadRight))
            return "r";
        if (man\right::hasOne($poUser, $laWriteRight))
            return "w";
        
        // check groups of the user and their rights of this document
        if (wl\main::any( man\right::hasOne($laGroups, $laReadRight)))
            return "r";
        if (wl\main::any( man\right::hasOne($laGroups, $laWriteRight)))
            return "w";
        
        
        return null;
    }
    
    /** adds a right or changes the access of the right
     * @param $poRight right object
     * @param $plWrite write access
     **/
    function addRight( $poRight, $plWrite = false ) {
        if (!($poRight instanceof man\right))
            wl\main::phperror( "first argument must be a right object", E_USER_ERROR );
        if (!is_bool($plWrite))
            wl\main::phperror( "second argument must be a boolean value", E_USER_ERROR );
        
        $access = $plWrite ? "write" : "read";
        $this->moDB->Execute("INSERT INTO documentpart_rights VALUES (?,?,?) ON DUPLICATE KEY UPDATE access=?", array($this->mnID, $poRight->getID(), $access, $access));
    }
    
    /** returns an array with right objects
     * @param $pcType type of the right, empty all rights, "write" only write access, "read" only read access
     * @return array with rights
     **/
    function getRights($pcType = null) {
        if (empty($pcType))
            $loResult = $this->moDB->Execute("SELECT rights FROM documentpart_rights WHERE documentpart=?", array($this->mnID));
        else
            $loResult = $this->moDB->Execute("SELECT rights FROM documentpart_rights WHERE documentpartt=? AND access=?", array($this->mnID, $pcType));
        
        $la = array();
        if (!$loResult->EOF)
            foreach($loResult as $laRow)
                array_push($la, new man\right(intval($laRow["rights"])));
        
        return $la;
    }
    
    /** deletes the right 
     * @param $poRight right object
     **/
    function deleteRight( $poRight ) {
        if (!($poRight instanceof man\right))
            wl\main::phperror( "argument must be a right object", E_USER_ERROR );
        
        $this->moDB->Execute("DELETE FROM documentpart_rights WHERE documentpart=? AND rights=?", array($this->mnID, $poRight->getID()));
    }
    
    /** tries to create a lock of the documentpart and remove old locks if needed
     * @param $poUser user object
     * @return null if the lock can be stored, the user object, which hold the lock
     * @bug if the user has no write access a lock can be created
     **/
    function lock( $poUser ) {
        if (!($poUser instanceof man\user))
            wl\main::phperror( "argument must be a user object", E_USER_ERROR );
        
        // remove old locks
        $this->moDB->Execute("DELETE FROM documentpart_lock WHERE lastactivity < DATE_SUB(NOW(), INTERVAL ? SECOND)", array(wl\config::locktime));
        
        // check if a lock exists
        $loLockUser = $this->hasLock();
        if (!empty($loLockUser)) 
            return $loLockUser;
        
        // try to set the lock or refresh the lock if exists
        $this->moDB->Execute("INSERT INTO documentpart_lock (documentpart, user, session) VALUES (?,?,?) ON DUPLICATE KEY UPDATE user=?, session=?, lastactivity=NOW()", array($this->mnID, $poUser->getID(), session_id(), $poUser->getID(), session_id()));
        
        return null;
    }
    
    /** returns the user object if a lock exists
     * @return user object or null
     **/
    function hasLock() {
        $loResult = $this->moDB->Execute("SELECT user FROM documentpart_lock WHERE documentpart=? AND session <> ?", array($this->mnID, session_id()));
        if (!$loResult->EOF)
            return new man\user( intval($loResult->fields["user"]) );
        
        return null;
    }
    
    /** unlocks the document **/
    function unlock() {
        $this->moDB->Execute("DELETE FROM documentpart_lock WHERE documentpart=? AND session=?", array($this->mnID, session_id()));
    }
    
    /** gets the archive flag (we use the archiv flag of the document)
     * @return boolean of the archive flag
     **/
    function isArchivable() {
        $loResult = $this->moDB->Execute( "SELECT archivable FROM document WHERE id=?", array($this->mnDocument) );
        return $loResult->fields["archivable"] == true;
    }
    
    /** implements interface method, but the method does nothing
     * because the document sets the archive flag only
     * @param boolean
     **/
    function setArchivable( $plArchiveable ) {}
    
    function restoreHistory($pnID) {
        
    }
    
    function deleteHistory($pxID = null) {
        
    }
    
    function getHistoryContent($pnID) {
        
    }
    
    function getHistory() {
        
    }
}


?>