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
use weblatex\management as wm;

require_once( dirname(dirname(__DIR__))."/config.inc.php" );
require_once( dirname(__DIR__)."/main.class.php" );
require_once( dirname(__DIR__)."/management/user.class.php" );
require_once( __DIR__."/draft.class.php" );


/** class of representation a document **/
class document {
    
    /* document id **/
    private $mnID      = null;
    /** owner object **/
    private $moOner    = null;
    
    
    
    /** creates a new user document
     * @param $pcName document name
     * @param $poUser user object of the owner
     **/
    static function create( $pcName, $poUser ) {
        if ( (!is_string($pcName)) || (!($poUser instanceof wm\user)) )
            wl\main::phperror( "arguments must be string value and a user object", E_USER_ERROR );
        
        $loDB     = wl\main::getDatabase();
        $loResult = $loDB->Execute( "SELECT did FROM document WHERE name=?", array($pcName) );
        
        if (!$loResult->EOF)
            throw new \Exception( "document [".$pcName."] exists" );
        
        $loDB->Execute( "INSERT IGNORE INTO document (name,uid) VALUES (?,?)", array($pcName, $poUser->getUID()) );
    }
    
    /** deletes a document
     * @param $pnDID document id
     **/
    static function delete( $pnDID ) {
        if (!is_numeric($pnDID))
            wl\main::phperror( "argument must be a numeric value", E_USER_ERROR );
        
        wl\main::getDatabase()->Execute( "DELETE FROM document WHERE did=?", array($pnDID) );
    }
    
    
    
    /** constructor
     * @param $px document id or document name
     **/
    function __construct( $px ) {
        if ( (!is_numeric($px)) && (!is_string($px)) )
            wl\main::phperror( "argument must be a numeric or string value", E_USER_ERROR );
        
        if (is_numeric($px))
            $loResult = wl\main::getDatabase()->Execute( "SELECT did, uid FROM document WHERE did=?", array($px) );
        else
            $loResult = wl\main::getDatabase()->Execute( "SELECT did, uid FROM document WHERE name=?", array($px) );
        
        if ($loResult->EOF)
            throw new \Exception( "document data not found" );
        
        $this->mnID   = $loResult->fields["did"];
        if (!empty($loResult->fields["uid"]))
            $this->moOwner = new wm\user($loResult->fields["uid"]);
    }

    /** returns the user owner object
     * @return owner object or null if owner is deleted
     **/
	function getOwner() {
        return $this->moOwner;
    }
    
    /** get the document name **/
    function getName() {
        $loResult = wl\main::getDatabase()->Execute( "SELECT name FROM document WHERE did=?", array($this->mnID) );
        return $loResult->fields["name"];
    }
    
    /** get the draft
     * @return draft object or draft content
     **/
    function getDraft() {
        $loResult = wl\main::getDatabase()->Execute( "SELECT draft, draftid FROM document WHERE did=?", array($this->mnID) );
        if (empty($loResult->fields["draftid"]))
            return $loResult->fields["draft"];
        else
            return new draft($loResult->fields["draftid"]);
    }
    
    /** returns the archivable flag
     * @return boolean is the document is archivable
     **/
    function isArchivable() {
        $loResult = wl\main::getDatabase()->Execute( "SELECT archivable FROM document WHERE did=?", array($this->mnID) );
        return $loResult->fields["archivable"] == true;
    }
    
    /** sets the archivable flag
     * @param $plArchiveable boolean
     **/
    function setArchivable( $plArchiveable ) {
        wl\main::getDatabase()->Execute( "UPDATE document SET archivable=? WHERE did=?", array( ($plArchiveable ? "true" : "false"), $this->mnID) );
    }
    
    /** returns the modifiable flag
     * @return boolean is the document modifiable
     **/
    function isModifiable() {
        $loResult = wl\main::getDatabase()->Execute( "SELECT modifiable FROM document WHERE did=?", array($this->mnID) );
        return $loResult->fields["modifiable"] == true;
    }
    
    /** sets the modifiable flag
     * @param $plModifiable boolean
     **/
    function setModifiable( $plModifiable ) {
        wl\main::getDatabase()->Execute( "UPDATE document SET modifiable=? WHERE did=?", array( ($plModifiable ? "true" : "false"), $this->mnID) );
    }
    
    /** sets the owner id
     * @param $poUser user object
     **/
    function setOwner( $poUser ) {
        if (!($poUser instanceof wm\user))
            wl\main::phperror( "argument must be an user object", E_USER_ERROR );
        
        wl\main::getDatabase()->Execute( "UPDATE document SET uid=? WHERE did=?", array( $poUser->getUID(), $this->mnID) );
        $this->moOwner = $poUser;
    }
}

?>