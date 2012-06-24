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

require_once( dirname(dirname(__DIR__))."/wl-config.inc.php" );
require_once( dirname(__DIR__)."/main.class.php" );
require_once( dirname(__DIR__)."/management/user.class.php" );
require_once( dirname(__DIR__)."/management/group.class.php" );
require_once( dirname(__DIR__)."/management/right.class.php" );
require_once( __DIR__."/baseedit.class.php" );



/** class of representation a LaTeXMK element **/
class latexmk implements baseedit {
    
    /** latexmk name **/
    private $mcName    = null;
    /** latexmk id **/
    private $mnID      = null;
    /** owner **/
    private $moOwner   = null;
    /** database object **/
    private $moDB      = null;
    
    
    /** creates a new latexmk object and returns the object
     * @param $pcName name of the latexmk
     * @param $poUser user object for setting the owner
     * @return new latexmk object
     * @todo check the Insert_ID() call for non-mysql databases
     **/
    static function create( $pcName, $poUser ) {
        if ( (!is_string($pcName)) || (!($poUser instanceof man\user)) )
            wl\main::phperror( "first argument must be string value, second argument a user object", E_USER_ERROR );
        
        $loDB = wl\main::getDatabase();
        
        $loResult = $loDB->Execute( "SELECT id FROM latexmk WHERE name=? AND owner=?", array($pcName, $poUser->getID()) );
        if (!$loResult->EOF)
            throw new \Exception( "a latexmk object exists with this name and this owner" );
        
        
        $loDB->Execute("INSERT IGNORE INTO latexmk (name,owner) VALUES (?,?)", array($pcName, $poUser->getID()));
        return new latexmk(intval($loDB->Insert_ID()));
    }
    
    /** deletes a latexmk
     * @param $px latexmk id
     **/
    static function delete( $px ) {
        if ( (!is_numeric($px)) && (!($px instanceof latexmk)) )
            wl\main::phperror( "argument must be a numeric value or a latexmk object", E_USER_ERROR );
        
        if (is_numeric($px))
            wl\main::getDatabase()->Execute( "DELETE FROM latexmk WHERE id=?", array($px) );
        else
            wl\main::getDatabase()->Execute( "DELETE FROM latexmk WHERE id=?", array($px->getID()) );
    }
    
    /** returns an array with latexmk objects
     * @param $poUser user object, for getting latexmk objects of this user
     * @return array with latexmk object
     **/
    static function getList( $poUser =  null) {
        $la = array();
        
        if ($poUser instanceof man\user)
            $loResult = wl\main::getDatabase()->Execute("SELECT id FROM latexmk WHERE owner=?", array($poUser->getID()));
        else    
            $loResult = wl\main::getDatabase()->Execute("SELECT id FROM latexmk");
        
        
        if (!$loResult->EOF)
            foreach($loResult as $laRow)
            array_push( $la, new latexmk(intval($laRow["id"])) );
        
        return $la;
    }
    
    
    
    /** constructor
     * @param $px latexmk id, latexmk object or latexmk name
     * @param $poUser user object (needed if the first parameter is a string parameter)
     **/
    function __construct( $px, $poUser = null ) {
        if ( (!is_numeric($px)) && (!is_string($px)) && (!($px instanceof $this)) )
            wl\main::phperror( "argument must be a numeric, string or latexmk object value", E_USER_ERROR );
        if ( (is_string($px)) && (!($poUser instanceof man\user)) )
            wl\main::phperror( "on a string argument the second parameter must be an user object", E_USER_ERROR );
        
        
        $this->moDB = wl\main::getDatabase();
        if (is_numeric($px))
            $loResult = $this->moDB->Execute( "SELECT name, id, owner FROM latexmk WHERE id=?", array($px) );
        if ($px instanceof $this)
            $loResult = $this->moDB->Execute( "SELECT name, id, owner FROM latexmk WHERE id=?", array($px->getID()) );        
        if (is_string($px))
            $loResult = $this->moDB->Execute( "SELECT name, id, owner FROM latexmk WHERE name=? AND owner=?", array($px, $poUser->getID()) );
        
        if ($loResult->EOF)
            throw new \Exception( "latexmk data not found" );
        
        $this->mcName  = $loResult->fields["name"];
        $this->mnID    = intval($loResult->fields["id"]);
        if (!empty($loResult->fields["owner"]))
            $this->moOwner = new man\user(intval($loResult->fields["owner"]));
    }
    
    /** returns the latexmk name
     * @return name
     **/
    function getName() {
        return $this->mcName;
    }
    
    /** returns the latexmk id
     * @return latexmk id
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
 
    /** returns the access of the user on this latexmk
     * @param $poUser user object
     * @return null for no access, "r" read access and "w" for read-write access
     **/
    function getAccess($poUser) {
        return "w";
    }
    
    /** adds a right or changes the access of the latexmk
     * @param $poRight right object
     * @param $plWrite write access
     **/
    function addRight( $poRight, $plWrite = false ) {
        if (!($poRight instanceof man\right))
            wl\main::phperror( "first argument must be a right object", E_USER_ERROR );
        if (!is_bool($plWrite))
            wl\main::phperror( "second argument must be a boolean value", E_USER_ERROR );
    }
    
    /** deletes the right 
     * @param $poRight right object
     **/
    function deleteRight( $poRight ) {
    }
    
    /** returns an array with right objects
     * @param $pcType type of the right, empty all rights, "write" only write access, "read" only read access
     * @return array with rights
     **/
    function getRights($pcType = null) {
    }
    
    /** returns the latexmk content data
     * @return content data
     **/
    function getContent() {
        $loResult = $this->moDB->Execute( "SELECT content FROM latexmk WHERE id=?", array($this->mnID) );
        if (!$loResult->EOF)
            return $loResult->fields["content"];
        return null;
    }
    
    /** save the content data
     * @param $pc data
     * @todo check the quotes, because the,
     * CKEditor creates slashes take a look to the magic quote option
     * 
     **/
    function setContent( $pc ) {
        //check first the archivable flag and stores the old data
        if ($this->isArchivable())
            $this->moDB->Execute("INSERT IGNORE INTO latexmk_history (latexmkid, content) SELECT id, content FROM latexmk WHERE id=?", array($this->mnID));
        
        $this->moDB->Execute("UPDATE latexmk SET content=? WHERE id=?", array($pc, $this->mnID));
    }
    
    /** tries to create a lock of the latexmk and remove old locks if needed
     * @param $poUser user object
     * @return null if the lock can be stored, the user object, which hold the lock
     * @bug if the user has no write access a lock can be created
     **/
    function lock( $poUser ) {
        if (!($poUser instanceof man\user))
            wl\main::phperror( "argument must be a user object", E_USER_ERROR );
        
        // remove old locks
        $this->moDB->Execute("DELETE FROM latexmk_lock WHERE lastactivity < DATE_SUB(NOW(), INTERVAL ? SECOND)", array(wl\config::locktime));
        
        // check if a lock exists
        $loLockUser = $this->hasLock();
        if (!empty($loLockUser)) 
            return $loLockUser;
        
        // try to set the lock or refresh the lock if exists
        $this->moDB->Execute("INSERT INTO latexmk_lock (latexmk, user, session) VALUES (?,?,?) ON DUPLICATE KEY UPDATE user=?, session=?, lastactivity=NOW()", array($this->mnID, $poUser->getID(), session_id(), $poUser->getID(), session_id()));
        
        return null;
    }
    
    /** returns the user object if a lock exists
     * @return null (for no lock) or use object
     **/
    function hasLock() {
        $loResult = $this->moDB->Execute("SELECT user FROM latexmk_lock WHERE latexmk=? AND session <> ?", array($this->mnID, session_id()));
        if (!$loResult->EOF)
            return new man\user( intval($loResult->fields["user"]) );
        
        return null;
    }
    
    /** remove the lock of the draft **/
    function unlock() {
        $this->moDB->Execute("DELETE FROM latexmk_lock WHERE latexmk=? AND session=?", array($this->mnID, session_id()));
    }
    
    /** checks if the document can be archiveable
     * @return boolean of the flag
     **/
    function isArchivable() {

    }
    
    /** sets the archivable flag
     * @param $plArchiveable boolean for enabling / disabling the flag
     **/
    function setArchivable( $plArchiveable ) {
        
    }
    
    /** restore a history entry
     * @param $pnID history id
     **/
    function restoreHistory($pnID) {
        
    }
    
    /** deletes the whole history or a single entry
     * @param $pxID null, numeric value or array of numeric values
     **/
    function deleteHistory($pxID = null) {
        
    }
    
    /** returns the content of a history entry
     * @param $pnID entry id
     * @return content
     **/
    function getHistoryContent($pnID) {
        
    }
    
    /** returns an array with ids and timestamps of the history entries
     * @return assoc. array
     **/
    function getHistory() {
        
    }
}

?>