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
require_once( dirname(__DIR__)."/management/group.class.php" );
require_once( dirname(__DIR__)."/management/right.class.php" );
require_once( __DIR__."/baseedit.class.php" );
    

    
/** class of representation a draft **/
class draft implements basedocument {
    
    /** draft name **/
    private $mcName    = null;
    /** draft id **/
    private $mnID      = null;
    /** draft data **/
    private $mcData    = null;
    /** owner **/
    private $moOwner   = null;
    /** database object **/
    private $moDB      = null;
    
    
    /** creates a new draft and returns the object
     * @param $pcName name of the draft
     * @param $poUser user object for setting the owner
     * @return new draft object
     **/
    static function create( $pcName, $poUser ) {
        if ( (!is_string($pcName)) || (!($poUser instanceof man\user)) )
            wl\main::phperror( "first argument must be string value, second argument a user object", E_USER_ERROR );
        
        $loDB     = wl\main::getDatabase();
        $loResult = $loDB->Execute( "SELECT id FROM draft WHERE name=?", array($pcName) );
        if (!$loResult->EOF)
            throw new \Exception( "draft [".$pcName."] exists" );
        
        $loDB->Execute("INSERT IGNORE INTO draft (name,owner) VALUES (?,?)", array($pcName, $poUser->getID()));
        return new draft($pcName);
    }
    
    /** deletes a draft
     * @param $pnDID draft id
     **/
    static function delete( $pnDID ) {
        if (!is_numeric($pnDID))
            wl\main::phperror( "argument must be a numeric value", E_USER_ERROR );
        
        wl\main::getDatabase()->Execute( "DELETE FROM draft WHERE id=?", array($pnDID) );
    }
    
    /** returns an array with drafts
     * @param $poUser user object, for getting drafts of this user
     * @return array with draft object
     **/
    static function getList( $poUser =  null) {
        $la = array();
        
        if ($poUser instanceof man\user)
            $loResult = wl\main::getDatabase()->Execute("SELECT id FROM draft WHERE owner=?", array($poUser->getID()));
        else    
            $loResult = wl\main::getDatabase()->Execute("SELECT id FROM draft");
        
        
        if (!$loResult->EOF)
            foreach($loResult as $laRow)
                array_push( $la, new draft(intval($laRow["id"])) );
        
        return $la;
    }
    
    
    
    /** constructor
     * @param $px draft id or draft name
     **/
    function __construct( $px ) {
        if ( (!is_numeric($px)) && (!is_string($px)) && (!($px instanceof $this)) )
            wl\main::phperror( "argument must be a numeric, string or draft object value", E_USER_ERROR );
        
        $this->moDB = wl\main::getDatabase();
        
        if (is_numeric($px))
            $loResult = $this->moDB->Execute( "SELECT name, id, owner, content FROM draft WHERE id=?", array($px) );
        if ($px instanceof $this)
            $loResult = $this->moDB->Execute( "SELECT name, id, owner, content FROM draft WHERE id=?", array($px->getID()) );        
        if (is_string($px))
            $loResult = $this->moDB->Execute( "SELECT name, id, owner, content FROM draft WHERE name=?", array($px) );
        
        if ($loResult->EOF)
            throw new \Exception( "draft data not found" );
        
        $this->mcName  = $loResult->fields["name"];
        $this->mnID    = intval($loResult->fields["id"]);
        $this->mcData  = $loResult->fields["content"];
        if (!empty($loResult->fields["owner"]))
            $this->moOwner = new man\user(intval($loResult->fields["owner"]));
    
    }
    
    /** returns the draftname
     * @return draftname
     **/
    function getName() {
        return $this->mcName;
    }
    
    /** returns the draft id
     * @return draft id
     **/
    function getID() {
        return $this->mnID;
    }
    
    /** returns the owner user object
     * @return user object
     **/
    function getOwner() {
        return $this->moOwner;
    }
    
    /** returns the draft content data
     * @return content data
     **/
    function getContent() {
        return $this->mcData;
    }
    
    /** sets the content data
     * @param $pc data
     **/
    function setContent( $pc ) {
        $this->mcData = $pc;
    }
    
    /** saves draft data to database **/
    function save() {
        //check first the archivable flag and stores the old data
        if ($this->isArchivable())
            $this->moDB->Execute("INSERT IGNORE INTO draft_history (draftid, content) SELECT id, content FROM draft WHERE id=?", array($this->mnID));
            
        $this->moDB->Execute("UPDATE draft SET content=? WHERE id=?", array($this->mcData, $this->mnID));
    }
    
    /** returns an array with the draft history
     * @returns assoc array with history, "content" data, "time" of the backup time, id history ID
     **/
    function getHistory() {
        $la = array();
        
        $loResult = $this->moDB->Execute("SELECT backuptime, id FROM draft_history WHERE draftid=?", array($this->mnID));
        foreach($loResult as $laRow)
            array_push($la, array("id" => intval($laRow["id"]), "time" => $laRow["backuptime"]));
        
        return $la;
    }
    
    /** returns the content of a history element
     * @param $pnID history id
     * @return content
     **/
    function getHistoryContent($pnID) {
        if (!is_numeric($pnID))
            wl\main::phperror( "first argument must be a numeric value", E_USER_ERROR );
        
        $loResult = $this->moDB->Execute("SELECT content FROM draft_history WHERE id=? AND draftid=?", array($pnID, $this->mnID));
        if (!$loResult->EOF)
            return $loResult->fields["content"];
        
        return null;
    }
    
    /** restores a draft history version
     * @param $pnID
     **/
    function restoreHistory($pnID) {
        if (!is_numeric($pnID))
            wl\main::phperror( "first argument must be a numeric value", E_USER_ERROR );
        
        $loResult = $this->moDB->Execute("SELECT content FROM draft_history WHERE id=? AND draftid=?", array($pnID, $this->mnID));
        if (!$loResult->EOF) {
            $this->mcData = $loResult->fields["content"];
            $this->moDB->Execute("UPDATE draft SET content=? WHERE id=?", array($this->mcData, $this->mnID));
        }
    }
    
    /** deletes a history entry or the whole history
     * @param $pxID null or history id / array
     **/
    function deleteHistory($pxID = null) {
        if ( (!empty($pxID)) && (!is_array($pxID)) && (!is_numeric($pcID)) )
            wl\main::phperror( "first argument must be a numeric value or an array of numeric values", E_USER_ERROR );
        
        if (empty($pxID))
            $this->moDB->Execute("DELETE FROM draft_history WHERE draftid=?", array($this->mnID));
    
        if (is_numeric($pxID))
            $this->moDB->Execute("DELETE FROM draft_history WHERE id=? AND draftid=?", array($pxID, $this->mnID));
        
        if (is_array($pxID))
            foreach($pxID as $id)
                $this->moDB->Execute("DELETE FROM draft_history WHERE id=? AND draftid=?", array($id, $this->mnID));
    }
    
    /** returns the access of the user on this draft
     * @param $poUser user object
     * @return null for no access, "r" read access and "w" for read-write access
     **/
    function getAccess($poUser) {
        if (!($poUser instanceof man\user))
            wl\main::phperror( "argument must be a user object", E_USER_ERROR );
        
        // draft & administrator right
        $loDraftRight = new man\right( wl\config::$system_rights["draft"] );
        $loAdminRight = new man\right( wl\config::$system_rights["administrator"] );
        
        // check if the user is the owner or has administrator or draft right
        if ( ($poUser->isEqual($this->getOwner())) || ($loDraftRight->hasRight($poUser)) || ($loAdminRight->hasRight($poUser)) )
            return "w";
        
        
        // get user groups
        $laGroups = $poUser->getGroups();
        
        // check if a user group has admin or draft right
        if ( (wl\main::any( man\right::hasOne($laGroups, array($loDraftRight)))) || (wl\main::any( man\right::hasOne($laGroups, array($loAdminRight)))) )
            return "w";
        
        
        //get read and write rights of this draft
        $laReadRight  = $this->getRights("read");
        $laWriteRight = $this->getRights("write");
        
        
        // check the other rights of the user
        if (man\right::hasOne($poUser, $laReadRight))
            return "w";
        if (man\right::hasOne($poUser, $laWriteRight))
            return "r";
        
        // check groups of the user and their rights of this draft
        if (wl\main::any( man\right::hasOne($laGroups, $laReadRight)))
            return "r";
        if (wl\main::any( man\right::hasOne($laGroups, $laWriteRight)))
            return "w";
        
            
        return null;
    }
    
    /** tries to create a lock of the draft and remove old locks if needed
     * @param $poUser user object
     * @return null if the lock can be stored, the user object, which hold the lock
     **/
    function lock( $poUser ) {
        if (!($poUser instanceof man\user))
            wl\main::phperror( "argument must be a user object", E_USER_ERROR );
        
        // remove old locks
        $this->moDB->Execute("DELETE FROM draft_lock WHERE lastactivity < DATE_SUB(NOW(), INTERVAL ? SECOND)", array(wl\config::locktime));
        
        // check if a lock exists
        $loLockUser = $this->hasLock();
        if (!empty($loLockUser)) 
            return $loLockUser;
            
        // try to set the lock or refresh the lock if exists
        $this->moDB->Execute("INSERT INTO draft_lock (draft, user, session) VALUES (?,?,?) ON DUPLICATE KEY UPDATE user=?, session=?", array($this->mnID, $poUser->getID(), session_id(), $poUser->getID(), session_id()));
        
        return null;
    }
    
    /** returns the user object if a lock exists
     * @return null (for no lock) or use object
    **/
    function hasLock() {
        $loResult = $this->moDB->Execute("SELECT user FROM draft_lock WHERE draft=? AND session <> ?", array($this->mnID, session_id()));
        if (!$loResult->EOF)
            return new man\user( intval($loResult->fields["user"]) );
        
        return null;
    }
    
    /** refresh the lock time
     * @param $poUser user object
     **/
    function refreshLock( $poUser ) {
        if (!($poUser instanceof man\user))
            wl\main::phperror( "argument must be a user object", E_USER_ERROR );
        
        $this->moDB->Execute("UPDATE draft_lock SET lastactivity=NOW() WHERE draft=? AND user=? AND session=?", array($this->mnID, $poUser->getID(), session_id()));
    }
    
    /** remove the lock of the draft **/
    function unlock() {
        $this->moDB->Execute("DELETE FROM draft_lock WHERE draft=? AND session=?", array($this->mnID, session_id()));
    }
    
    /** adds a right or changes the access of the right
     * @param $poRight right object
     * @param $plWrite access
     **/
    function addRight( $poRight, $plWrite = false ) {
        if (!($poRight instanceof man\right))
            wl\main::phperror( "first argument must be a right object", E_USER_ERROR );
        
        $access = $plWrite ? "write" : "read";
        $this->moDB->Execute("INSERT INTO draft_rights VALUES (?,?,?) ON DUPLICATE KEY UPDATE access=?", array($this->mnID, $poRight->getID(), $access, $access));
    }
    
    /** deletes the right 
     * @param $poRight right object
     **/
    function deleteRight( $poRight ) {
        if (!($poRight instanceof man\right))
            wl\main::phperror( "argument must be a right object", E_USER_ERROR );
        
        $this->moDB->Execute("DELETE FROM draft_rights WHERE draft=? AND right=?", array($this->mnID, $poRight->getID()));
    }
    
    /** returns an array with right objects
     * @param $pcType type of the right, empty all rights, "write" only write access, "read" only read access
     * @return array with rights
     **/
    function getRights($pcType = null) {
        if (empty($pcType))
            $loResult = $this->moDB->Execute("SELECT rights FROM draft_rights WHERE draft=?", array($this->mnID));
        else
            $loResult = $this->moDB->Execute("SELECT rights FROM draft_rights WHERE draft=? AND access=?", array($this->mnID, $pcType));
        
        $la = array();
        if (!$loResult->EOF)
            foreach($loResult as $laRow)
                array_push($la, new man\right($laRow["right"]));
        
        return $la;
    }

    /** returns the archivable flag
     * @return boolean is the document is archivable
     **/
    function isArchivable() {
        $loResult = $this->moDB->Execute( "SELECT archivable FROM draft WHERE id=?", array($this->mnID) );
        return $loResult->fields["archivable"] === "true";
    }
    
    /** sets the archivable flag
     * @param $plArchiveable boolean
     **/
    function setArchivable( $plArchiveable ) {
        $this->moDB->Execute( "UPDATE draft SET archivable=? WHERE id=?", array( ($plArchiveable ? "true" : "false"), $this->mnID) );
    }
}

?>