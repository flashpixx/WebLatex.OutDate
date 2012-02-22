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

namespace weblatex\document;
use weblatex as wl;
use weblatex\management as man;
    
require_once( dirname(__DIR__)."/main.class.php" );

    
/** class of representation a draft **/
class draft {
    
    /** draft name **/
    private $mcName    = null;
    /** draft id **/
    private $mnID      = null;
    /** draft data **/
    private $mcData    = null;
    /** owner **/
    private $moOwner   = null;
    
    
    /** creates a new draft and returns the object
     * @param $pcName name of the draft
     * @param $poUser user object for setting the owner
     * @return new draft object
     **/
    static function create( $pcName, $poUser ) {
        if ( (!is_string($pcName)) || (!($poUser instanceof man\user)) )
            wl\main::phperror( "first argument must be string value, second argument a user object", E_USER_ERROR );
        
        $loDB     = wl\main::getDatabase();
        $loResult = $loDB->Execute( "SELECT did FROM draft WHERE name=?", array($pcName) );
        if (!$loResult->EOF)
            throw new \Exception( "draft [".$pcName."] exists" );
        
        $loDB->Execute("INSERT IGNORE INTO draft (name,user) VALUES (?,?)", array($pcName, $poUser->getUID()));
        return new draft($pcName);
    }
    
    /** deletes a draft
     * @param $did draft id
     **/
    static function delete( $pnDID ) {
        if (!is_numeric($pnDID))
            wl\main::phperror( "argument must be a numeric value", E_USER_ERROR );
        
        wl\main::getDatabase()->Execute( "DELETE FROM draft WHERE did=?", array($pnDID) );
    }
    
    /** returns a assoc array with draft information
     * @return assoc array with draft id (did), user object (user) and name
     **/
    static function getList() {
        $la = array();
        
        $loResult = wl\main::getDatabase()->Execute("SELECT did, name, user FROM draft");
        if (!$loResult->EOF)
            foreach($loResult as $laRow)
                array_push($la, array("name" => $laRow["name"], "did" => $laRow["did"], "user" => (empty($laRow["user"]) ? null : new man\user($laRow["user"]))));
        
        return $la;
    }
    
    
    
    /** constructor
     * @param $px draft id or draft name
     **/
    function __construct( $px ) {
        if ( (!is_numeric($px)) && (!is_string($px)) )
            wl\main::phperror( "argument must be a numeric or string value", E_USER_ERROR );
        
        if (is_numeric($px))
            $loResult = wl\main::getDatabase()->Execute( "SELECT name, did, user, content FROM draft WHERE did=?", array($px) );
        else
            $loResult = wl\main::getDatabase()->Execute( "SELECT name, did, user, content FROM draft WHERE name=?", array($px) );
        
        if ($loResult->EOF)
            throw new \Exception( "draft data not found" );
        
        $this->mcName  = $loResult->fields["name"];
        $this->mnID    = $loResult->fields["did"];
        $this->mcData  = $loResult->fields["content"];
        $this->moOwner = new man\user($loResult->fields["user"]);
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
    function getDID() {
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
        wl\main::getDatabase()->Execute("UPDATE draft SET content=? WHERE did=?", array($this->mcData, $this->mnID));
    }
    
    /** adds a right or changes the access of the right
     * @param $poRight right object
     * @param $plWrite access
     **/
    function addRight( $poRight, $plWrite = false ) {
        if (!($poRight instanceof man\right))
            wl\main::phperror( "first argument must be a right object", E_USER_ERROR );
        
        $access = $plWrite ? "write" : "read";
        wl\main::getDatabase()->Execute("INSERT INTO draft_rights VALUES (?,?,?) ON DUPLICATE KEY UPDATE access=?", array($this->mnID, $poRight->getRID(), $access, $access));
    }
    
    /** deletes the right 
     * @param $poRight right object
     **/
    function deleteRight( $poRight ) {
        if (!($poRight instanceof man\right))
            wl\main::phperror( "argument must be a right object", E_USER_ERROR );
        
        wl\main::getDatabase()->Execute("DELETE FROM draft_rights WHERE draft=? AND right=?", array($this->mnID, $poRight->getRID()));
    }
    
    /** returns an array with right objects of this draft
     * @return array with right obejcts
     **/
    function getRights() {
        $loResult = wl\main::getDatabase()->Execute("SELECT right FROM draft_rights WHERE draft=?", array($this->mnID));
        
        $la = array();
        if (!$loResult->EOF)
            foreach($loResult as $laRow)
                array_push($la, new man\right($laRow["right"]));
        
        return $la;
    }
}

?>