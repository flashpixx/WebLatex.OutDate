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


/** class of representation a user with the database **/
class user implements \Serializable {
    
    /** username **/
    private $mcName    = null;
    /* user id **/
    private $mnID      = null;

    
    
    /** creates a new user account if not exists 
     * @param $pcName username
     * @param $pcPassword unencrypted password
     **/
    static function create( $pcName, $pcPassword ) {
        if ( (!is_string($pcName)) || (!is_string($pcPassword)) )
            wl\main::phperror( "arguments must be string values", E_USER_ERROR );
        
        $loDB     = wl\main::getDatabase();
        $loResult = $loDB->Execute( "SELECT uid FROM user WHERE name=?", array($pcName) );
        
        if (!$loResult->EOF)
            throw new \Exception( "user [".$pcName."] exists" );
        
        $loDB->Execute( "INSERT IGNORE INTO user (name,hash) VALUES (?,?)", array($pcName, wl\main::generateHash($pcPassword)) );
    }
    
    /** deletes a user with the uid
     * @param $pnUID user id
     **/
    static function delete( $pnUID ) {
        if (!is_numeric($pnUID))
            wl\main::phperror( "argument must be a numeric value", E_USER_ERROR );
        
        wl\main::getDatabase()->Execute( "DELETE FROM user WHERE uid=?", array($pnUID) );
    }
    
    /** returns the userlist
     * @return assoc array with username (name) and user id (uid)
     **/
    static function getList() {
        $la = array();

        $loResult = wl\main::getDatabase()->Execute( "SELECT name, uid FROM user" );
        if (!$loResult->EOF)
            foreach( $loResult as $laRow )
                array_push( $la, array("name" => $laRow["name"], "uid" => $laRow["uid"]) );
        
        return $la;
    }
    
    
    
    /** constructor
     * @param $px userid or username
     **/
    function __construct( $px ) {
        if ( (!is_numeric($px)) && (!is_string($px)) && (!($px instanceof $this)) )
            wl\main::phperror( "argument must be a numeric, string or user object value", E_USER_ERROR );
        
        if (is_numeric($px))
            $loResult = wl\main::getDatabase()->Execute( "SELECT name, uid FROM user WHERE uid=?", array($px) );
        if ($px instanceof $this)
            $loResult = wl\main::getDatabase()->Execute( "SELECT name, uid FROM user WHERE uid=?", array($px->getUID()) );
        if (is_string($px))
            $loResult = wl\main::getDatabase()->Execute( "SELECT name, uid FROM user WHERE name=?", array($px) );
        
        if ($loResult->EOF)
            throw new \Exception( "user data not found" );
        
        $this->mcName = $loResult->fields["name"];
        $this->mnID   = $loResult->fields["uid"];
    }
    
    /** validate the user password
     * @param pcPassword raw password
     * @return boolean if authentification is correct
     **/
    function authentificate( $pcPassword ) {
        $loResult = wl\main::getDatabase()->Execute( "SELECT hash FROM user WHERE uid=?", array($this->mnID) );
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
     * @return uid
     **/
    function getUID() {
        return $this->mnID;
    }
    
    /** change the user password
     * @param $pcPassword password string
     **/
    function changePassword( $pcPassword ) {
        wl\main::getDatabase()->Execute( "UPDATE user SET hash=? WHERE uid=?", array(wl\main::generateHash($pcPassword), $this->mnID) );
    }
    
    /** change the login state
     * @param $plState boolean for enable (true) or disable (false) login
     **/
    function changeLoginState( $plState ) {
        wl\main::getDatabase()->Execute( "UPDATE user SET loginenable=? WHERE uid=?", array( ($plState ? "true" : "false"), $this->mnID) );
    }
    
    /** returns the login option
     * @return boolean, true login is enabled
     **/
    function canLogin() {
        $loResult = wl\main::getDatabase()->Execute("SELECT loginenable FROM user WHERE uid=?", array($this->mnID));
        
        if (!$loResult->EOF)
            return $loResult->fields["loginenable"] == "true";
        
        return false;
    }
    
    /** checks if the user is in the group
     * @param $poGroup group object
     * @return boolean if user is in
     **/
    function isGroupIn( $poGroup ) {
        if (!($poUser instanceof group))
            wl\main::phperror( "argument must be a group object", E_USER_ERROR );
        
        $loResult = wl\main::getDatabase()->Execute("SELECT user FROM user_groups WHERE groupid=? AND user=?", array($poGroup->getGID(), $this->mnID));
        return !$loResult->EOF;
    }
    
    /** returns an array with group objects in which 
     * the use is
     * @return array with groupobjects
     **/
    function getGroups() {
        $loResult = wl\main::getDatabase()->Execute("SELECT groupid FROM user_groups WHERE user=?", array($this->mnID));
        
        $la = array();
        if (!$loResult->EOF)
            foreach($loResult as $laRow)
                array_push($la, new group($laRow["groupid"]) );
        return $la;
    }
    
    /** print method of the object
     * @return string representation
     **/
    function __toString() {
        return $this->mcName." (".$this->mnID.")";
    }
    
    /** serializable method
     * @return serialized string
     **/
    function serialize() {
        return serialize( array("id" => $this->mnID, "name" => $this->mcName) );
    }
    
    /** unserialize method
     * @param $pc string
     **/
    function unserialize($pc) {
        $la            = unserialize($pc);
        $this->mnID    = $la["id"];
        $this->mcName  = $la["name"];
    }
    
    /** checks if another user object points to the same uid
     * @param $poUser user object
     * @return if the uid is equal
     **/
    function isEqual( $poUser ) {
        if ($poUser instanceof $this)
            return $poUser->getUID() === $this->mnID;
        return false;
    }
}

?>