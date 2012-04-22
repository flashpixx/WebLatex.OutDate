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
require_once( __DIR__."/baseedit.class.php" );


/** class of representation a document part like chapter or subsection **/
class documentpart implements baseedit {
    
    /* document id **/
    private $mnID           = null;
    /** document id **/
    private $mnDocument     = null;
    /** database object **/
    private $moDB           = null;
    
    
    
    /** constructor
     * @param $px document id or document object
     **/
    function __construct( $px ) {
        if (!is_numeric($px)) 
            wl\main::phperror( "argument must be a numeric value", E_USER_ERROR );
        
        $this->moDB = wl\main::getDatabase();
        
        $loResult = $this->moDB->Execute( "SELECT document FROM documentpart WHERE id=?", array($px) );
        if ($loResult->EOF)
            throw new \Exception( "documentpart data not found" );
            
        $this->mnID         = $px;
        $this->mnDocument   = intval($loResult->fields["document"]);
    }
    
    function getID() {
        return $this->mnID;
    }
    
    function getName() {
        $loResult = $this->moDB->Execute( "SELECT description FROM documentpart WHERE id=?", array($this->mnID) );
        if (!$loResult->EOF)
            return $loResult->fields["description"];
        return null;        
    }
    
    /** sets the part content
     * @param $pcContent text information
     **/
    function setContent( $pc ) {
        //check first the archivable flag and stores the old data
        if ($this->isArchivable())
            $this->moDB->Execute("INSERT IGNORE INTO documentpart_history (documentpartid, content) SELECT id, content FROM documentpart WHERE id=?", array($this->mnID));
        
        $this->moDB->Execute("UPDATE documentpart SET content=? WHERE id=?", array($pc, $this->mnID));
    }
    
    /** gets the content of the part
     * @return data
     **/
    function getContent() {
        $loResult = $this->moDB->Execute( "SELECT content FROM documentpart WHERE id=?", array($this->mnID) );
        if (!$loResult->EOF)
            return $loResult->fields["content"];
        return null;
    }
    
    /** gets the owner of the document part (equal to the owner of the document)
     * @return owner id
     **/
    function getOwner() {
        $loResult = $this->moDB->Execute( "SELECT owner FROM document WHERE id=?", array($this->mnDocument) );
        if ($loResult->EOF)
            return man\user( intval($loResult->fields["owner"]) );
        return null;
    }
    
    function getAccess($poUser) {
        
    }
    
    function addRight( $poRight, $plWrite = false ) {
        
    }
    
    function getRights($pcType = null) {
        
    }
    
    function deleteRight( $poRight ) {
        
    }
    
    function lock( $poUser ) {
    }
    
    function unlock() {
        
    }
    
    function hasLock() {
        
    }
    
    /** gets the archive flag (we use the archiv flag of the document)
     * @return boolean of the archive flag
     **/
    function isArchivable() {
        $loResult = $this->moDB->Execute( "SELECT archivable FROM document WHERE id=?", array($this->mnDocument) );
        return $loResult->fields["archivable"] == true;
    }
    
    /** implements interface method, but the method does nothing
     * because the document sets the archive flag only
     * @param boolean
     **/
    function setArchivable( $plArchiveable ) {}
    
    function restoreHistory($pnID) {
        
    }
    
    function deleteHistory($pxID = null) {
        
    }
    
    function getHistoryContent($pnID) {
        
    }
    
    function getHistory() {
        
    }
}


?>