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

namespace weblatex\management;
use weblatex as wl;
    
require_once( dirname(dirname(__DIR__))."/config.inc.php" );
require_once( dirname(__DIR__)."/base.class.php" );
require_once( dirname(__DIR__)."/main.class.php" );
require_once( __DIR__."/user.class.php" );
    
    

/** class of representation a group with the database **/
class group implements \weblatex\base {
	
    /** group name **/
    private $mcName    = null;
    /** group id **/
    private $mnID      = null;
    /** database object **/
    private $moDB      = null;
    /** owner **/
    private $moOwner   = null;
    
    
    
    /** creates a new group account if not exists 
     * @param $pcName group name
     * @param $poUser user object for setting the right owner
     * @return the new group object
     **/
    static function create( $pcName, $poUser = null ) {
        if (!is_string($pcName))
            wl\main::phperror( "first argument must be string value", E_USER_ERROR );
        if ( (!empty($poUser)) && (!($poUser instanceof user)) )
            wl\main::phperror( "second argument must be empty or an user object", E_USER_ERROR );
        
        $loDB     = wl\main::getDatabase();
        $loResult = $loDB->Execute( "SELECT id FROM groups WHERE name=?", array($pcName) );
        if (!$loResult->EOF)
            throw new \Exception( "group [".$pcName."] exists" );
        
        $lxID = null;
        if (!empty($poUser))
            $lxID = $poUser->getID();
        $loDB->Execute( "INSERT IGNORE INTO groups (name,owner) VALUES (?,?)", array($pcName, $lxID) );
        
        return new group($pcName);
    }
    
    /** deletes a group with the group id
     * @param $pnGID user id
     **/
    static function delete( $pnGID ) {
        if (!is_numeric($pnGID))
            wl\main::phperror( "argument must be a numeric value", E_USER_ERROR );

        wl\main::getDatabase()->Execute( "DELETE FROM groups WHERE id=?", array($pnGID) );
    }
    
    /** returns the grouplist
     * @param $poUser user object or null
     * @return array with group objects
     **/
    static function getList($poUser = null) {
        $la = array();
        
        if ($poUser instanceof user)
            $loResult = wl\main::getDatabase()->Execute( "SELECT id FROM groups WHERE owner=?", array($poUser->getID()));
        else
            $loResult = wl\main::getDatabase()->Execute( "SELECT id FROM groups" );
        
        if (!$loResult->EOF)
            foreach( $loResult as $laRow )
                array_push( $la, new group(intval($laRow["id"])) );
        
        return $la;
    }
    
    
    
    /** constructor
     * @param $px group id, name or object
     **/
    function __construct( $px ) {
        if ( (!is_numeric($px)) && (!is_string($px)) && (!($px instanceof $this)) )
            wl\main::phperror( "argument must be a numeric, string or group object value", E_USER_ERROR );
        
        $this->moDB = wl\main::getDatabase();
        
        if (is_numeric($px))
            $loResult = $this->moDB->Execute( "SELECT name, id, owner FROM groups WHERE id=?", array($px) );
        if ($px instanceof $this)
            $loResult = $this->moDB->Execute( "SELECT name, id, owner FROM groups WHERE id=?", array($px->getID()) );
        if (is_string($px))
            $loResult = $this->moDB->Execute( "SELECT name, id, owner FROM groups WHERE name=?", array($px) );
        
        if ($loResult->EOF)
            throw new \Exception( "group data not found" );
        
        $this->mcName   = $loResult->fields["name"];
        $this->mnID     = intval($loResult->fields["id"]);
        if (!empty($loResult->fields["owner"]))
            $this->moOwner = new user(intval($loResult->fields["owner"]));
    }
    
    /** returns the groupname
     * @return groupname
     **/
    function getName() {
        return $this->mcName;
    }
    
    /** returns the group id
     * @return group id
     **/
    function getID() {
        return $this->mnID;
    }
    
    /** returns the owner of the group
     * @return null or user object
     **/
    function getOwner() {
        return $this->moOwner;
    }
    
    /** sets the owner
     * @param null or user object
     **/
    /** sets the owner of this right 
     * @param $px user object or null
     **/
    function setOwner($px) {
        if ( (!empty($px)) && (!($px instanceof user)) )
            wl\main::phperror( "argument must be empty or an user object", E_USER_ERROR );
        
        if (empty($px))
            $this->moDB->Execute("UPDATE groups SET owner=? WHERE id=?", array(null, $this->mnID));
        else
            $this->moDB->Execute("UPDATE groups SET owner=? WHERE id=?", array($px->getID(), $this->mnID));
    }
    
    /** adds a user to the group
     * @param $poUser userobject
     **/
    function addUser( $poUser ) {
        if (!($poUser instanceof user))
            wl\main::phperror( "argument must be a user object", E_USER_ERROR );
        
        $this->moDB->Execute("INSERT IGNORE INTO user_groups VALUES (?,?)", array($poUser->getUID(), $this->mnID));
    }
    
    /** removes a user from a group
     * @param $poUser userobject
     **/
    function removeUser( $poUser ) {
        if (!($poUser instanceof user))
            wl\main::phperror( "argument must be a user object", E_USER_ERROR );
        
        $this->moDB->Execute("DELETE FROM user_groups WHERE user=? AND groupid=?", array($poUser->getUID(), $this->mnID));
    }
    
    /** check if a user within this group
     * @param $poUser userobject
     * @return boolean is user within the group
     **/
    function isUserIn( $poUser ) {
        if (!($poUser instanceof user))
            wl\main::phperror( "argument must be a user object", E_USER_ERROR );
        
        $loResult = $this->moDB->Execute("SELECT user FROM user_groups WHERE user=? AND groupid=?", array($poUser->getUID(), $this->mnID));
        return !$loResult->EOF;
    }
    
    /** returns an array with user objects which are all
     * within the group
     * @return array with userobjects
     **/
    function getUser() {
        $loResult = $this->moDB->Execute("SELECT user FROM user_groups WHERE groupid=?", array($this->mnID));
        
        $la = array();
        if (!$loResult->EOF)
            foreach($loResult as $laRow)
                array_push($la, new user($laRow["user"]) );
        return $la;
    }
 
    /** print method of the object
     * @return string representation
     **/
    function __toString() {
        return $this->mcName." (".$this->mnID.")";
    }
    
    /** checks if another group object points to the same group id
     * @param $poGroup group object
     * @return if the group id is equal
     **/
    function isEqual( $poGroup ) {
        if ($poGroup instanceof $this)
            return $poGroup->getID() === $this->mnID;
        return false;
    }
}

?>