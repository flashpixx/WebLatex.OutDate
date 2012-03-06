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


/** class of representation a user with the database **/
class user implements \weblatex\base {
    
    /** username **/
    private $mcName    = null;
    /* user id **/
    private $mnID      = null;
    /** database object **/
    private $moDB      = null;

    
    
    /** creates a new user account if not exists 
     * @param $pcName username
     * @param $pcPassword unencrypted password
     * @return new user object
     **/
    static function create( $pcName, $pcPassword ) {
        if ( (!is_string($pcName)) || (!is_string($pcPassword)) )
            wl\main::phperror( "arguments must be string values", E_USER_ERROR );
        
        $loDB     = wl\main::getDatabase();
        $loResult = $loDB->Execute( "SELECT id FROM user WHERE name=?", array($pcName) );
        
        if (!$loResult->EOF)
            throw new \Exception( "user [".$pcName."] exists" );
        
        $loDB->Execute( "INSERT IGNORE INTO user (name,hash) VALUES (?,?)", array($pcName, wl\main::generateHash($pcPassword)) );
        
        return new user($pcName);
    }
    
    /** deletes a user with the user id
     * @param $pnUID user id
     **/
    static function delete( $pnUID ) {
        if (!is_numeric($pnUID))
            wl\main::phperror( "argument must be a numeric value", E_USER_ERROR );
        
        wl\main::getDatabase()->Execute( "DELETE FROM user WHERE id=?", array($pnUID) );
    }
    
    /** returns the userlist
     * @return array with user objects
     **/
    static function getList() {
        $la = array();

        $loResult = wl\main::getDatabase()->Execute( "SELECT name, id FROM user" );
        if (!$loResult->EOF)
            foreach( $loResult as $laRow )
                array_push( $la, new user(intval($laRow["id"])) );
        
        return $la;
    }
    
    
    
    /** constructor
     * @param $px user id, name or object
     **/
    function __construct( $px ) {
        if ( (!is_numeric($px)) && (!is_string($px)) && (!($px instanceof $this)) )
            wl\main::phperror( "argument must be a numeric, string or user object value", E_USER_ERROR );
        
        $this->moDB = wl\main::getDatabase();
        
        if (is_numeric($px))
            $loResult = $this->moDB->Execute( "SELECT name, id FROM user WHERE id=?", array($px) );
        if ($px instanceof $this)
            $loResult = $this->moDB->Execute( "SELECT name, id FROM user WHERE id=?", array($px->getID()) );
        if (is_string($px))
            $loResult = $this->moDB->Execute( "SELECT name, id FROM user WHERE name=?", array($px) );
        
        if ($loResult->EOF)
            throw new \Exception( "user data not found" );
        
        $this->mcName = $loResult->fields["name"];
        $this->mnID   = intval($loResult->fields["id"]);
    }
    
    /** validate the user password
     * @param pcPassword raw password
     * @return boolean if authentification is correct
     **/
    function authentificate( $pcPassword ) {
        $loResult = $this->moDB->Execute( "SELECT hash FROM user WHERE id=?", array($this->mnID) );
        if ($loResult->EOF)
            wl\main::phperror( "user record not found", E_USER_ERROR );
        
        return wl\main::validateHash($pcPassword, $loResult->fields["hash"]);
    }
    
    /** returns the username
     * @return username
     **/
    function getName() {
        return $this->mcName;
    }
    
    /** returns the user id
     * @return user id
     **/
    function getID() {
        return $this->mnID;
    }
    
    /** change the user password
     * @param $pcPassword password string
     **/
    function changePassword( $pcPassword ) {
        $this->moDB->Execute( "UPDATE user SET hash=? WHERE id=?", array(wl\main::generateHash($pcPassword), $this->mnID) );
    }
    
    /** change the login state
     * @param $plState boolean for enable (true) or disable (false) login
     **/
    function changeLoginState( $plState ) {
        $this->moDB->Execute( "UPDATE user SET loginenable=? WHERE id=?", array( ($plState ? "true" : "false"), $this->mnID) );
    }
    
    /** returns the login option
     * @return boolean, true login is enabled
     **/
    function canLogin() {
        $loResult = $this->moDB->Execute("SELECT loginenable FROM user WHERE id=?", array($this->mnID));
        
        if (!$loResult->EOF)
            return $loResult->fields["loginenable"] === "true";
        
        return false;
    }
    
    /** checks if the user is in the group
     * @param $poGroup group object
     * @return boolean if user is in
     **/
    function isGroupIn( $poGroup ) {
        if (!($poUser instanceof group))
            wl\main::phperror( "argument must be a group object", E_USER_ERROR );
        
        $loResult = $this->moDB->Execute("SELECT user FROM user_groups WHERE groupid=? AND user=?", array($poGroup->getGID(), $this->mnID));
        return !$loResult->EOF;
    }
    
    /** returns an array with group objects in which 
     * the use is
     * @return array with groupobjects
     **/
    function getGroups() {
        $loResult = $this->moDB->Execute("SELECT groupid FROM user_groups WHERE user=?", array($this->mnID));
        
        $la = array();
        if (!$loResult->EOF)
            foreach($loResult as $laRow)
                array_push($la, new group(intval($laRow["groupid"])) );
        return $la;
    }
    
    /** print method of the object
     * @return string representation
     **/
    function __toString() {
        return $this->mcName." (".$this->mnID.")";
    }
    
    /** wakeup call for serialization **/
    function __wakeup() {
        $this->moDB = wl\main::getDatabase();
    }
    
    /** sleep method for writing down class data
     * @return array with property names
     **/
    function __sleep()
    {
        return array("mnID", "mcName");
    }
        
    /** checks if another user object points to the same user id
     * @param $poUser user object
     * @return if the user id is equal
     **/
    function isEqual( $poUser ) {
        if ($poUser instanceof $this)
            return $poUser->getID() === $this->mnID;
        return false;
    }
}

?>