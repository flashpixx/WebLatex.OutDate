<?php
    
/** 
 @cond
 ############################################################################
 # LGPL License                                                             #
 #                                                                          #
 # This file is part of the WebLaTeX system           .                     #
 # Copyright (c) 2010, Philipp Kraus, <philipp.kraus@flashpixx.de>          #
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


/** class of representation a right with the database **/
class right implements \Serializable {
    
    /** right name **/
    private $mcName    = null;
    /** right id **/
    private $mnID      = null;
    /** system right **/
    private $mlSystem  = false;
    
    
    
    /** creates a new right if not exists 
     * @param $pcName group name
     * @param $plSystem boolean for system right
     **/
    static function create( $pcName, $plSystem = false ) {
        if ( (!is_string($pcName)) || (!is_boolean($plSystem)) )
            wl\main::phperror( "first argument must be string value, second argument a boolean value", E_USER_ERROR );
        
        $loDB     = wl\main::getDatabase();
        $loResult = $loDB->Execute( "SELECT rid FROM rights WHERE name=?", array($pcName) );
        
        if (!$loResult->EOF)
            throw new \Exception( "right [".$pcName."] exists" );
        
        $loDB->Execute( "INSERT IGNORE INTO rights (name,system) VALUES (?,?)", array($pcName, ($plSystem ? "true" : "false")) );
    }
    
    /** deletes a right with the rid
     * @param $pnGID right id
     * @param $plForce system rights can be deleted only by setting force to true
     **/
    static function delete( $pnRID, $plForce = false ) {
        if (!is_numeric($pnRID))
            wl\main::phperror( "argument must be a numeric value", E_USER_ERROR );
        
        if ($plForce)
            wl\main::getDatabase()->Execute( "DELETE FROM rights WHERE rid=?", array($pnRID) );
        else
            wl\main::getDatabase()->Execute( "DELETE FROM rights WHERE rid=? AND system=?", array($pnRID, "false") );
    }
    
    /** returns the rightlist
     * @return assoc array with rightname (name), right id (rid) and boolean (system) for system right
     **/
    static function getList() {
        $la = array();
        
        $loResult = wl\main::getDatabase()->Execute( "SELECT name, rid, system FROM rights" );
        if (!$loResult->EOF)
            foreach( $loResult as $laRow )
                array_push( $la, array("name" => $laRow["name"], "rid" => $laRow["rid"], "system" => ($laRow["system"]==true)) );
        
        return $la;
    }
    
    
    
    /** constructor
     * @param $px groupid or groupname
     **/
    function __construct( $px ) {
        if ( (!is_numeric($px)) && (!is_string($px)) )
            wl\main::phperror( "argument must be a numeric or string value", E_USER_ERROR );
        
        if (is_numeric($px))
            $loResult = wl\main::getDatabase()->Execute( "SELECT name, rid, system FROM rights WHERE rid=?", array($px) );
        else
            $loResult = wl\main::getDatabase()->Execute( "SELECT name, rid, system FROM rights WHERE name=?", array($px) );
        
        if ($loResult->EOF)
            throw new \Exception( "right data not found" );
        
        $this->mcName   = $loResult->fields["name"];
        $this->mnID     = $loResult->fields["rid"];
        $this->mlSystem = $loResult->fields["system"] == "true";
    }
    
    /** returns the rightname
     * @return rightname
     **/
    function getName() {
        return $this->mcName;
    }
    
    /** returns the right id
     * @return rid
     **/
    function getRID() {
        return $this->mnID;
    }
    
    /** returns if the right is a system right
     * @return boolean for system right
     **/
    function isSystem() {
        return $this->mlSystem;
    }
    
    /** returns an array with group objects which 
     * have the rights
     * @return array with groupobjects
     **/
    function getGroups() {
        $loResult = wl\main::getDatabase()->Execute("SELECT group FROM group_rights WHERE right=?", array($this->mnID));
        
        $la = array();
        if (!$loResult->EOF)
            foreach($loResult as $laRow)
                array_push($la, new group($laRow["group"]));
        
        return $la;
    }
    
    /** returns an array with user objects which 
     * have the rights
     * @return array with userobjects
     **/
    function getUser() {
        $loResult = wl\main::getDatabase()->Execute("SELECT user FROM user_rights WHERE right=?", array($this->mnID));
        
        $la = array();
        if (!$loResult->EOF)
            foreach($loResult as $laRow)
                array_push($la, new user($laRow["user"]));
        
        return $la;
    }
    
    /** checks if the group or user is member of the right
     * @param $px group or user object
     * @return boolean value (true if the right is set)
     **/
    function hasRight( $px ) {
        if ( (!($px instanceof user)) && (!($px instanceof group)) )
            wl\main::phperror( "argument must be a user or group object", E_USER_ERROR );
        
        if ($px instanceof user)
            $loResult = wl\main::getDatabase()->Execute("SELECT user FROM user_rights WHERE user=? AND right=?", array($px->getUID(), $this->mnID));
        else
            $loResult = wl\main::getDatabase()->Execute("SELECT group FROM group_rights WHERE group=? AND right=?", array($px->getGID(), $this->mnID));
    
        return !$loResult->EOF;
    }
    
    /** sets the right to a group or user
     * @param $px group or user object
     **/
    function addUserGroup( $px ) {
        if ( (!($px instanceof user)) && (!($px instanceof group)) )
            wl\main::phperror( "argument must be a user or group object", E_USER_ERROR );
        
        if ($px instanceof user)
            $loResult = wl\main::getDatabase()->Execute("INSERT IGNORE INTO user_rights VALUES (?,?)", array($px->getUID(), $this->mnID));
        else
            $loResult = wl\main::getDatabase()->Execute("INSERT IGNORE INTO  group_rights VALUES (?,?)", array($px->getGID(), $this->mnID));
    }
    
    /** removes the right of the group or user
     * @param $px group or user object
     **/
    function removeUserGroup( $px ) {
        if ( (!($px instanceof user)) && (!($px instanceof group)) )
            wl\main::phperror( "argument must be a user or group object", E_USER_ERROR );
        
        if ($px instanceof user)
            wl\main::getDatabase()->Execute("DELETE FROM user_rights WHERE user=? AND right=?", array($px->getUID(), $this->mnID));
        else
            wl\main::getDatabase()->Execute("DELETE FROM group_rights WHERE group=? AND right=?", array($px->getGID(), $this->mnID));
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
    
}

?>