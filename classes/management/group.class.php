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
    
require_once( dirname(__DIR__)."/main.class.php" );
require_once( dirname(dirname(__DIR__))."/config.inc.php" );
    

/** class of representation a group with the database **/
class group implements \Serializable {
	
    /** group name **/
    private $mcName    = null;
    /** group id **/
    private $mnID      = null;
    /** system group **/
    private $mlSystem  = false;
    
    
    
    /** creates a new group account if not exists 
     * @param $pcName group name
     * @param $plSystem boolean for system group
     * @return the new group object
     **/
    static function create( $pcName, $plSystem = false ) {
        if ( (!is_string($pcName)) || (!is_boolean($plSystem)) )
            wl\main::phperror( "first argument must be string value, second argument a boolean value", E_USER_ERROR );
        
        $loDB     = wl\main::getDatabase();
        $loResult = $loDB->Execute( "SELECT id FROM groups WHERE name=?", array($pcName) );
        
        if (!$loResult->EOF)
            throw new \Exception( "group [".$pcName."] exists" );
        
        $loDB->Execute( "INSERT IGNORE INTO groups (name,system) VALUES (?,?)", array($pcName, ($plSystem ? "true" : "false")) );
        
        return new group($pcName);
    }
    
    /** deletes a group with the group id
     * @param $pnGID user id
     * @param $plForce system groups can be deleted only by setting force to true
     **/
    static function delete( $pnGID, $plForce = false ) {
        if (!is_numeric($pnGID))
            wl\main::phperror( "argument must be a numeric value", E_USER_ERROR );
        
        // we check the numeric value of the system groups, so that this groups cannot be deleted
        if (in_array($pnGID, array_values(wl\config::$system_groups), true)) {
            wl\main::phperror( "system group [".$pnGID."] cannot be deleted", E_USER_NOTICE );
            throw new \Exception( "system group [".$pnGID."] cannot be deleted" );
        }
        
        if ($plForce)
            wl\main::getDatabase()->Execute( "DELETE FROM groups WHERE id=?", array($pnGID) );
        else
            wl\main::getDatabase()->Execute( "DELETE FROM groups WHERE id=? AND system=?", array($pnGID, "false") );
    }
    
    /** returns the grouplist
     * @return assoc array with groupname (name), group id (id) and boolean (system) for system group
     **/
    static function getList() {
        $la = array();
        
        $loResult = wl\main::getDatabase()->Execute( "SELECT name, id, system FROM groups" );
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
        
        if (is_numeric($px))
            $loResult = wl\main::getDatabase()->Execute( "SELECT name, id, system FROM groups WHERE id=?", array($px) );
        if ($px instanceof $this)
            $loResult = wl\main::getDatabase()->Execute( "SELECT name, id, system FROM groups WHERE id=?", array($px->getID()) );
        if (is_string($px))
            $loResult = wl\main::getDatabase()->Execute( "SELECT name, id, system FROM groups WHERE name=?", array($px) );
        
        if ($loResult->EOF)
            throw new \Exception( "group data not found" );
        
        $this->mcName   = $loResult->fields["name"];
        $this->mnID     = intval($loResult->fields["id"]);
        $this->mlSystem = $loResult->fields["system"] === "true";
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
    
    /** returns if the group is a system group
     * @return boolean for system group
     **/
    function isSystem() {
        return $this->mlSystem;
    }
    
    /** adds a user to the group
     * @param $poUser userobject
     **/
    function addUser( $poUser ) {
        if (!($poUser instanceof user))
            wl\main::phperror( "argument must be a user object", E_USER_ERROR );
        
        wl\main::getDatabase()->Execute("INSERT IGNORE INTO user_groups VALUES (?,?)", array($poUser->getUID(), $this->mnID));
    }
    
    /** removes a user from a group
     * @param $poUser userobject
     **/
    function removeUser( $poUser ) {
        if (!($poUser instanceof user))
            wl\main::phperror( "argument must be a user object", E_USER_ERROR );
        
        wl\main::getDatabase()->Execute("DELETE FROM user_groups WHERE user=? AND groupid=?", array($poUser->getUID(), $this->mnID));
    }
    
    /** check if a user within this group
     * @param $poUser userobject
     * @return boolean is user within the group
     **/
    function isUserIn( $poUser ) {
        if (!($poUser instanceof user))
            wl\main::phperror( "argument must be a user object", E_USER_ERROR );
        
        $loResult = wl\main::getDatabase()->Execute("SELECT user FROM user_groups WHERE user=? AND groupid=?", array($poUser->getUID(), $this->mnID));
        return !$loResult->EOF;
    }
    
    /** returns an array with user objects which are all
     * within the group
     * @return array with userobjects
     **/
    function getUser() {
        $loResult = wl\main::getDatabase()->Execute("SELECT user FROM user_groups WHERE groupid=?", array($this->mnID));
        
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
        $lc = $this->mcName." (".$this->mnID;
        if ($this->mlSystem)
            $lc .= " | System";
        return $lc.")";
    }
    
    /** serializable method
     * @return serialized string
     **/
    function serialize() {
        return serialize( array("id" => $this->mnID, "name" => $this->mcName, "system" => $this->mlSystem) );
    }
    
    /** unserialize method
     * @param $pc string
     **/
    function unserialize($pc) {
        $la             = unserialize($pc);
        $this->mnID     = $la["id"];
        $this->mcName   = $la["name"];
        $this->mlSystem = $la["system"];
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