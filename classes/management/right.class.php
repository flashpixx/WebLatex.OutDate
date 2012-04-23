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

require_once( dirname(__DIR__)."/base.class.php" );
require_once( dirname(__DIR__)."/main.class.php" );
require_once( __DIR__."/user.class.php" );
require_once( __DIR__."/group.class.php" );
    

/** class of representation a right with the database **/
class right implements \weblatex\base {
    
    /** right name **/
    private $mcName    = null;
    /** right id **/
    private $mnID      = null;
    /** database object **/
    private $moDB      = null;
    /** owner **/
    private $moOwner   = null;
    
    
    
    /** creates a new right if not exists 
     * @param $pcName group name
     * @param $poUser user object for setting the right owner
     * @return new right object
     **/
    static function create( $pcName, $poUser = null ) {
        if (!is_string($pcName))
            wl\main::phperror( "first argument must be string value", E_USER_ERROR );
        if ( (!empty($poUser)) && (!($poUser instanceof user)) )
            wl\main::phperror( "second argument must be empty or an user object", E_USER_ERROR );
        
        $loDB     = wl\main::getDatabase();
        if (empty($poUser))
            $loResult = $loDB->Execute( "SELECT id FROM rights WHERE name=? AND owner is null", array($pcName) );
        else
            $loResult = $loDB->Execute( "(SELECT id FROM rights WHERE name=? AND owner is null) UNION (SELECT id FROM rights WHERE name=? AND owner=?)", array($pcName, $pcName, $poUser->getID()) );
        
        if (!$loResult->EOF)
            throw new \Exception( "right [".$pcName."] exists" );
        
        $lxID = null;
        if (!empty($poUser))
            $lxID = $poUser->getID();
        $loDB->Execute( "INSERT IGNORE INTO rights (name,owner) VALUES (?,?)", array($pcName, $lxID) );
        
        return new right($pcName);
    }
    
    /** deletes a right with the right id
     * @param $pnRID right id
     **/
    static function delete( $pnRID ) {
        if (!is_numeric($pnRID))
            wl\main::phperror( "argument must be a numeric value", E_USER_ERROR );
        
        // we check the numeric value of the system rights, so that this right cannot be deleted
        if (in_array($pnRID, array_values(wl\config::$system_rights), true)) {
            wl\main::phperror( "system right [".$pnRID."] cannot be deleted", E_USER_NOTICE );
            throw new \Exception( "system right [".$pnRID."] cannot be deleted" );
        }
        
        wl\main::getDatabase()->Execute( "DELETE FROM rights WHERE id=?", array($pnRID) );
    }
    
    /** returns the rightlist
     * @param $poUser user object or null
     * @return array with right objects
     **/
    static function getList($poUser = null) {
        $la = array();
        
        if ($poUser instanceof user)
            $loResult = wl\main::getDatabase()->Execute( "SELECT id FROM rights WHERE owner=?", array($poUser->getID()) );
        else
            $loResult = wl\main::getDatabase()->Execute( "SELECT id FROM rights" );
        
        if (!$loResult->EOF)
            foreach( $loResult as $laRow )
                array_push( $la, new right(intval($laRow["id"])) );
        
        return $la;
    }
    
    /** retuns a boolean if the user or group as all the rights
     * @param $pxUserGroup user or group object or array of them
     * @param $pa array with right ids or names
     * @return boolean if all rights are set (or array with boolean values for each group / user object)
     **/
    static function hasAll( $pxUserGroup, $pa ) {
        if (!is_array($pa))
            wl\main::phperror( "argument must be a numeric or string array", E_USER_ERROR );
        
        // if the user/group parameter is an array
        if (is_array($pxUserGroup)) {
            $la = array();
            foreach($pxUserGroup as $lo)
                array_push($la, self::hasAll($lo, $pa));
            return $la;
        }
            
        // returns the boolean
        foreach($pa as $lxItem) {
            $loRight = new right($lxItem);
            if (!$loRight->hasRight($pxUserGroup))
                return false;
        }
        
        return true;
    }
    
    /** returns a boolean if the user or group as one of the rights
     * @param $pxUserGroup user or group object or array of them
     * @param $pa array with right ids or names
     * @return boolean if one right is set  (or array with boolean values for each group / user object)
     **/
    static function hasOne( $pxUserGroup, $pa ) {
        if (!is_array($pa))
            wl\main::phperror( "argument must be a numeric or string array", E_USER_ERROR );
    
        // if the user/group parameter is an array
        if (is_array($pxUserGroup)) {
            $la = array();
            foreach($pxUserGroup as $lo)
                array_push($la, self::hasOne($lo, $pa));
            return $la;
        }
        
        // returns the boolean
        foreach($pa as $lxItem) {
            $loRight = new right($lxItem);
            if ($loRight->hasRight($pxUserGroup))
                return true;
        }
        
        return false;
    }
    
    
    
    /** constructor
     * @param $px right id, name or object
     **/
    function __construct( $px ) {
        if ( (!is_numeric($px)) && (!is_string($px)) && (!($px instanceof $this)) )
            wl\main::phperror( "argument must be a numeric, string or right object value", E_USER_ERROR );
        
        $this->moDB = wl\main::getDatabase();
        
        if (is_numeric($px))
            $loResult = $this->moDB->Execute( "SELECT name, owner, id FROM rights WHERE id=?", array($px) );
        if ($px instanceof $this)
            $loResult = $this->moDB->Execute( "SELECT name, owner, id FROM rights WHERE id=?", array($px->getID()) );
        if (is_string($px))
            $loResult = $this->moDB->Execute( "SELECT name, owner, id FROM rights WHERE name=?", array($px) );
        
        if ($loResult->EOF) 
            throw new \Exception( "right data not found" );
        
        $this->mcName   = $loResult->fields["name"];
        $this->mnID     = intval($loResult->fields["id"]);
        if (!empty($loResult->fields["owner"]))
            $this->moOwner = new user(intval($loResult->fields["owner"]));
    }
    
    /** returns the rightname
     * @return rightname
     **/
    function getName() {
        return $this->mcName;
    }
    
    /** returns the right id
     * @return id
     **/
    function getID() {
        return $this->mnID;
    }
    
    /** returns the owner of the right
     * @return user object or null
     **/
    function getOwner() {
        return $this->moOwner;
    }
    
    /** sets the owner of this right 
     * @param $px user object or null
     **/
    function setOwner($px) {
        if ( (!empty($px)) && (!($px instanceof user)) )
            wl\main::phperror( "argument must be empty or an user object", E_USER_ERROR );
        
        if (empty($px))
            $this->moDB->Execute("UPDATE rights SET owner=? WHERE id=?", array(null, $this->mnID));
        else
            $this->moDB->Execute("UPDATE rights SET owner=? WHERE id=?", array($px->getID(), $this->mnID));
    }
    
    /** returns an array with group objects which 
     * have the rights
     * @return array with groupobjects
     **/
    function getGroups() {
        $loResult = $this->moDB->Execute("SELECT group FROM group_rights WHERE rights=?", array($this->mnID));
        
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
        $loResult = $this->moDB->Execute("SELECT user FROM user_rights WHERE rights=?", array($this->mnID));
        
        $la = array();
        if (!$loResult->EOF)
            foreach($loResult as $laRow)
                array_push($la, new user($laRow["user"]));
        
        return $la;
    }
    
    /** checks if the group or user is member of the right
     * @param $px group or user object (or array of them)
     * @return boolean value (true if the right is set) or array of booleans
     **/
    function hasRight( $px ) {
        if (is_array($px)) {
            $la = array();
            foreach($px as $loObj)
                array_push($la, $this->hasRight($loObj));
            return $la;
        }
        
        if ( (!($px instanceof user)) && (!($px instanceof group)) )
            wl\main::phperror( "argument must be a user or group object", E_USER_ERROR );
        
        if ($px instanceof user)
            $loResult = $this->moDB->Execute("SELECT user FROM user_rights WHERE user=? AND rights=?", array($px->getID(), $this->mnID));
        else
            $loResult = $this->moDB->Execute("SELECT group FROM group_rights WHERE group=? AND rights=?", array($px->getID(), $this->mnID));

        return !$loResult->EOF;
    }
    
    /** sets the right to a group or user
     * @param $px group or user object
     **/
    function addUserGroup( $px ) {
        if ( (!($px instanceof user)) && (!($px instanceof group)) )
            wl\main::phperror( "argument must be a user or group object", E_USER_ERROR );
        
        if ($px instanceof user)
            $loResult = $this->moDB->Execute("INSERT IGNORE INTO user_rights VALUES (?,?)", array($px->getID(), $this->mnID));
        else
            $loResult = $this->moDB->Execute("INSERT IGNORE INTO  group_rights VALUES (?,?)", array($px->getID(), $this->mnID));
    }
    
    /** removes the right of the group or user
     * @param $px group or user object
     **/
    function removeUserGroup( $px ) {
        if ( (!($px instanceof user)) && (!($px instanceof group)) )
            wl\main::phperror( "argument must be a user or group object", E_USER_ERROR );
        
        if ($px instanceof user)
            $this->moDB->Execute("DELETE FROM user_rights WHERE user=? AND rights=?", array($px->getID(), $this->mnID));
        else
            $this->moDB->Execute("DELETE FROM group_rights WHERE group=? AND rights=?", array($px->getID(), $this->mnID));
    }
    
    /** print method of the object
     * @return string representation
     **/
    function __toString() {
        return $this->mcName." (".$this->mnID.")";
    }
    
    /** checks if another right object points to the same right id
     * @param $poRight right object
     * @return if the right id is equal
     **/
    function isEqual( $poRight ) {
        if ($poRight instanceof $this)
            return $poRight->getID() === $this->mnID;
        return false;
    }
    
}

?>