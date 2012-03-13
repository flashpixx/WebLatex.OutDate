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
require_once( dirname(__DIR__)."/management/group.class.php" );
require_once( dirname(__DIR__)."/management/right.class.php" );
require_once( __DIR__."/basedocument.class.php" );
require_once( __DIR__."/draft.class.php" );
require_once( __DIR__."/document.class.php" );


/** class for representating the directory structure **/
class directory implements basedocument {
    
    /** full qualified name **/
    private $mcFQN     = null;
    /** directory name **/
    private $mcName    = null;
    /** directory id **/
    private $mnID      = null;
    /** owner **/
    private $moOwner   = null;
    /** database object **/
    private $moDB      = null;
    
    
    /** returns the new directory object
     * @param $pcPath FQN path
     * @param $poUser owner user object
     * @return directory object
     * @todo check the Insert_ID() call for non-mysql databases
     **/
    static function create( $pcPath, $poUser ) {
        if ( (!is_string($pcPath)) || (!($poUser instanceof man\user)) )
            wl\main::phperror( "first argument must be string value, second argument a user object", E_USER_ERROR );
     
        //we need an absolut path, so check the first character
        if ($pcPath[0] != "/")
            wl\main::phperror( "string must be begin with a slash", E_USER_ERROR );
        
        $lnParent = null;
        $loDB     = wl\main::getDatabase();
        $laDir    = array_values(array_filter(explode("/", $pcPath), function ($el) { return !empty($el); } )); 
        if (empty($laDir))
            throw new \Exception( "directory data not found" );
        
        // check if a directory on the root is named "WebLaTeX"
        if ($laDir[0] == "WebLaTeX")
            throw new \Exception( "on the root note ther can not be a node with name [WebLaTeX]" );
            
        
        foreach($laDir as $lcItem) {
            $lcDir = trim($lcItem);
            if (empty($lcDir))
                continue;
            
            // the parent field can be null, so we must check in different cases
            if (empty($lnParent))
                $loResult = $loDB->Execute("SELECT id FROM directory WHERE parent is null AND name=?", array($lcDir));
            else
                $loResult = $loDB->Execute("SELECT id FROM directory WHERE parent=? AND name=?", array($lnParent, $lcDir));
            
            // get the parent id or insert the new dataset
            if (!$loResult->EOF)
                $lnParent = intval($loResult->fields["id"]);
            else {
                $loDB->Execute("INSERT IGNORE INTO directory (parent, name, owner) VALUES (?,?,?)", array($lnParent, $lcDir, $poUser->getID()));
                $lnParent = intval($loDB->Insert_ID());
            }
        }
        return new directory($lnParent);
    }
    
    /** deletes the path entry with all subdirectories (only the last entry will be deleted)
     * @param $pxVal path, id or directory object
     **/
    static function delete( $pxVal ) {
        $loDB = wl\main::getDatabase();
        
        if (is_string($pxVal)) {
            $lnID = self::getEntryID($loDB, $pxVal);
            if (!empty($lnID))
                $loDB->Execute("DELETE FROM directory WHERE id=?", array($lnID));
        }
        
        if (is_numeric($pxVal))
            $loDB->Execute("DELETE FROM directory WHERE id=?", array($pxVal));
        
        if ($pxVal instanceof directory)
            $loDB->Execute("DELETE FROM directory WHERE id=?", array($pxVal->getID()));
    }
        
    /** function for getting an entry id from a absolut path
     * @param $poDatabase adodb database object
     * @param $pcPath absolut path
     * @param $plAllIDs boolean of only one entry or all entries are returned
     * @return numeric id of the latest entry or array with all entry ids
     **/
    private static function getEntryID( $poDatabase, $pcPath, $plAllIDs = false ) {
        if (!is_bool($plAllIDs))
            wl\main::phperror( "third argument must be a boolean value", E_USER_ERROR );
        if (!is_string($pcPath))
            wl\main::phperror( "second argument must be a string value", E_USER_ERROR );
        if ($pcPath[0] != "/")
            wl\main::phperror( "string must be begin with a slash", E_USER_ERROR );
        
        $laIDs    = array();
        $lnParent = null;
        foreach(explode("/", $pcPath) as $lcItem) {
            $lcDir = trim($lcItem);
            if (empty($lcDir))
                continue;
            
            // the parent field can be null, so we must check in different cases
            if (empty($lnParent))
                $loResult = $poDatabase->Execute("SELECT id FROM directory WHERE parent is null AND name=?", array($lcDir));
            else
                $loResult = $poDatabase->Execute("SELECT id FROM directory WHERE parent=? AND name=?", array($lnParent, $lcDir));
            
            // get the parent id or insert the new dataset
            if ($loResult->EOF)
                return $plAllIDs ? $laIDs : $lnParent;
            
            $lnParent = intval($loResult->fields["id"]);
            array_push($laIDs, $lnParent);
        }
        
        return $plAllIDs ? $laIDs : $lnParent;
    }
    
    
    
    /** constructor
     * @param $px directory id, full path or directory object
     **/
    function __construct( $px ) {
        if ( (!is_numeric($px)) && (!is_string($px)) && (!($px instanceof $this)) )
            wl\main::phperror( "argument must be a numeric, string or draft object value", E_USER_ERROR );
        if ( is_string($px) && empty($px) )
            wl\main::phperror( "argument is an empty string", E_USER_ERROR );
        
        $this->moDB = wl\main::getDatabase();
        
        // if the parameter is only "/", it is the root node
        if ( (is_string($px)) && ($px == "/") ) {
            
            $this->mcName = "root";
            $this->mnID   = 0;
            $this->mcFQN  = "/";
        
        } else {
            
            if (is_string($px))
                $this->mnID = self::getEntryID($this->moDB, $px);
            if (is_numeric($px))
                $this->mnID = $px;
            if ($px instanceof $this)
                $this->mnID = $px->getID();
            
            $loResult = $this->moDB->Execute("SELECT name, owner FROM directory WHERE id=?", array($this->mnID));
            if ($loResult->EOF)
                throw new \Exception( "directory data not found" );

            $this->mcName = $loResult->fields["name"];
            if (!empty($loResult->fields["owner"]))
                $this->moOwner = new man\user(intval($loResult->fields["owner"]));
            
            
            // read the FQN path into a string
            $la = array($this->mcName);
            for(
                $loResult = $this->moDB->Execute("SELECT parent, name FROM directory WHERE id=(SELECT parent FROM directory WHERE id=?)", array($this->mnID));
                !$loResult->EOF;
                $loResult = $this->moDB->Execute("SELECT parent, name FROM directory WHERE id=?", array($loResult->fields["parent"]))
            )
                array_push($la, $loResult->fields["name"]);
            $this->mcFQN  = "/".implode("/", array_reverse($la));
        }
    }
    
    /** returns the full path
     * @return path
     **/
    function getFQN() {
        return $this->mcFQN;
    }
    
    /** returns the directory name
     * @return name
     **/
    function getName() {
        return $this->mcName;
    }
    
    /** returns the id
     * @return id
     **/
    function getID() {
        return $this->mnID;
    }
    
    /** returns a bool if the directory object is the root node
     * @return bool
     **/
    function isRoot() {
        return $this->mnID == 0;
    }
    
    /** returns the parent directory
     * @return directory object or null if there is no parent
     **/
    function getParent() {
        if ($this->mnID == 0)
            return null;
        
        $loResult = $this->moDB->Execute("SELECT parent FROM directory WHERE id=?", array($this->mnID));
        if (empty($loResult->fields["parent"]))
            return null;
            
        return new directory(intval($loResult->fields["parent"]));
    }
    
    /** returns all chrildren of the directory
     * @return array with directory, draft and document objects
     **/
    function getChildren() {
        $la = array();
        
        // get subdirectories
        if ($this->mnID == 0)
            $loResult = $this->moDB->Execute("SELECT id FROM directory WHERE parent is null");
        else
            $loResult = $this->moDB->Execute("SELECT id FROM directory WHERE parent=?", array($this->mnID));
        
        if (!$loResult->EOF)
            foreach($loResult as $laRow)
                array_push($la, new directory(intval($laRow["id"])));
        
        
        // get documents
        $loResult = $this->moDB->Execute("SELECT document FROM directory_document WHERE directory=?", array($this->mnID));
        if (!$loResult->EOF)
            foreach($loResult as $laRow)
                array_push($la, new document(intval($laRow["document"])));
        
        // get drafts
        $loResult = $this->moDB->Execute("SELECT draft FROM directory_draft WHERE directory=?", array($this->mnID));
        if (!$loResult->EOF)
            foreach($loResult as $laRow)
                array_push($la, new draft(intval($laRow["draft"])));
        
        return $la;
    }
    
    /** results all draft and documents that have no link to an directory index
     * @return array with draft or document objects
     **/
    function getChildrenNotLinked() {
        $la = array();

        // get documents
        $loResult = $this->moDB->Execute("SELECT d.id FROM document AS d LEFT JOIN directory_document AS dd ON dd.document=d.id WHERE dd.document is null");
        if (!$loResult->EOF)
            foreach($loResult as $laRow)
                array_push($la, new document(intval($laRow["id"])));
        
        // get drafts
        $loResult = $this->moDB->Execute("SELECT d.id FROM draft AS d LEFT JOIN directory_draft AS dd ON dd.draft=d.id WHERE dd.draft is null");
        if (!$loResult->EOF)
            foreach($loResult as $laRow)
                array_push($la, new draft(intval($laRow["id"])));
        
        return $la;
    }
    
    /** returns a child with the name and type of the directory
     * @param $pcName name
     * @param $pcType type of return object, allowed values "directory", "draft" and "document"
     * @return null if no object is found, otherwise on of the allowed type objects
     **/
    function getChildByName( $pcName, $pcType ) {
        if ( (!is_string($pcName)) || (!is_string($pcType)) )
            wl\main::phperror( "arguments must be string values", E_USER_ERROR );
        if ( ($pcType != "draft") && ($pcType != "document") && ($pcType != "directory") )
            wl\main::phperror( "second arguments must be an allowed value [directory, draft, document]", E_USER_ERROR );
        
        $lo = null;
        switch ($pcType) {
            case "draft" :
                $loResult =  $this->moDB->Execute("SELECT dd.draft FROM directory_draft AS dd JOIN draft AS da ON da.id=dd.draft WHERE da.name=? AND dd.directory=?", array($pcName, $this->mnID));
                if (!$loResult->EOF)
                    $lo = new draft(intval($loResult->fields["draft"]));
                break;
                
                
            case "document" :
                $loResult =  $this->moDB->Execute("SELECT dd.document FROM directory_document AS dd JOIN document AS do ON do.id=dd.document WHERE do.name=? AND dd.directory=?", array($pcName, $this->mnID));
                if (!$loResult->EOF)
                    $lo = new document(intval($loResult->fields["document"]));
                break;      
                
                
            case "directory" :
                $loResult =  $this->moDB->Execute("SELECT id FROM directory WHERE name=? AND parent=?", array($pcName, $this->mnID));
                if (!$loResult->EOF)
                    $lo = new directory(intval($loResult->fields["id"]));
                break;      
        }
        
        return $lo;
    }
    
    /** adds a new child to the directory
     * @param $po draft, document or directory object
     **/
    function addChild($po){
        if ( (!($po instanceof draft)) && (!($po instanceof document)) )
            throw new \Exception( "argument must be a draft or document object" );
        if ($this->mnID == 0)
            throw new \Exception( "under the root node can not be add a draft or document" );
        
        if ($po instanceof draft) {
            $loResult = $this->moDB->Execute("SELECT d.id FROM draft AS d JOIN directory_draft AS dd on dd.draft=d.id WHERE dd.directory=? AND d.name=?", array($this->mnID, $po->getName()));
            if (!$loResult->EOF)
                throw new \Exception( "under a directory node the draft name must be unique" );
            
            $this->moDB->Execute("INSERT IGNORE INTO directory_draft VALUES (?,?)", array($po->getID(), $this->mnID));
        }
            
            
        if ($po instanceof document) {
            $loResult = $this->moDB->Execute("SELECT d.id FROM document AS d JOIN directory_document AS dd on dd.document=d.id WHERE dd.directory=? AND d.name=?", array($this->mnID, $po->getName()));
            if (!$loResult->EOF)
                throw new \Exception( "under a directory node the document name must be unique" );
        
            $this->moDB->Execute("INSERT IGNORE INTO directory_document VALUES (?,?)", array($po->getID(), $this->mnID));
        }
    }
    
    /** deletes a child entry of the directory 
     * @param $po draft, document or directory object
     **/
    function removeChild($po) {
        if ( (!($po instanceof $this)) && (!($po instanceof draft)) && (!($po instanceof document)) )
            throw new \Exception( "argument must be a directory, draft or document object" );
        
        if ($po instanceof $this)
            self::delete($po);
        
        if ($po instanceof draft)
            $this->moDB->Execute("DELETE FROM directory_draft WHERE draft=? AND directory=?", array($po->getID(), $this->mnID));
        
        if ($po instanceof document)
            $this->moDB->Execute("DELETE FROM directory_document WHERE document=? AND directory=?", array($po->getID(), $this->mnID));
    }
    
    /** changes the parent position of this directory
     * @param $px directory object or null
     **/
    function move($px) {
        if ( (!empty($px)) && (!($po instanceof $this)) )
            throw new \Exception( "argument must be a directory object" );
        if ($this->mnID)
            throw new \Exception( "root element can not be moved" );
        
        
        $lxParent = null;
        if (!empty($px))
            $lxParent = $px->getID();
            
        $this->moDB->Excute("UPDATE directory SET parent=? WHERE id=?", array($lxParent, $this->mnID));
    }
    
    /** renames the directory
     * @param $pcName new name
     **/
    function rename($pcName) {
        if (!is_string($pcName))
            wl\main::phperror( "argument must be a string value", E_USER_ERROR );
        if ($this->mnID)
            throw new \Exception( "root element can not be renamed" );
        
        // check if an entry exists with the same name
        $loResult = $this->moDB->Execute("SELECT id FROM directory WHERE name=? AND parent=(SELECT parent FROM directory WHERE id=?)", array($pcName, $this->mnID));
        if (!$loResult->EOF)
            throw new \Exception( "an entry with the same name exists" );
        
        $this->moDB->Execute("UPDATE directory SET name=? WHERE id=?", array($this->mnID));
        $this->mcName = $pcName;
    }
    
    /** returns the owner user object of the document
     * @returns null or the owner user object
     **/
    function getOwner() {
        return $this->moOwner;
    }
    
    /** adds a right or changes the access of the right
     * @param $poRight right object
     * @param $plWrite write access
     **/
    function addRight( $poRight, $plWrite = false ) {
        if (!($poRight instanceof man\right))
            wl\main::phperror( "first argument must be a right object", E_USER_ERROR );
        if (!is_bool($plWrite))
            wl\main::phperror( "second argument must be a boolean value", E_USER_ERROR );
        
        $access = $plWrite ? "write" : "read";
        $this->moDB->Execute("INSERT INTO directory_rights VALUES (?,?,?) ON DUPLICATE KEY UPDATE access=?", array($this->mnID, $poRight->getID(), $access, $access));
    }
    
    /** deletes a right 
     * @param $poRight right object
     **/
    function deleteRight( $poRight ) {
        if (!($poRight instanceof man\right))
            wl\main::phperror( "argument must be a right object", E_USER_ERROR );
        
        $this->moDB->Execute("DELETE FROM directory_rights WHERE directory=? AND rights=?", array($this->mnID, $poRight->getID()));
    }
    
    /** returns an array with right objects
     * @param $pcType type of the right, empty all rights, "write" only write access, "read" only read access
     * @return array with rights
     **/
    function getRights($pcType = null) {
        if (empty($pcType))
            $loResult = $this->moDB->Execute("SELECT rights FROM directory_rights WHERE directory=?", array($this->mnID));
        else
            $loResult = $this->moDB->Execute("SELECT rights FROM directory_rights WHERE directory=? AND access=?", array($this->mnID, $pcType));
        
        $la = array();
        if (!$loResult->EOF)
            foreach($loResult as $laRow)
                array_push($la, new man\right(intval($laRow["rights"])));
        
        return $la;
    }
    
    /** returns the access of an user
     * @param $poUser user object
     * @return null for no access, "r" read access and "w" for read-write access
     **/
    function getAccess($poUser) {
        if (!($poUser instanceof man\user))
            wl\main::phperror( "argument must be a user object", E_USER_ERROR );
        
        // we can not get information if the directory is the root node (because the node is not stored)
        // so we return on the root node all users write access, because the right creates the visibility
        // to the node
        if ($this->mnID == 0)
            return "w";
        
        
        // administrator and directory right
        $loAdminRight = new man\right( wl\config::$system_rights["administrator"] );
        $loDirRight   = new man\right( wl\config::$system_rights["directory"] );        
        
        // check if the user is the owner or has administrator or draft right
        if ( ($poUser->isEqual($this->getOwner())) || ($loAdminRight->hasRight($poUser)) || ($loDirRight->hasRight($poUser)) )
            return "w";
        
        // get user groups
        $laGroups = $poUser->getGroups();
        
        // check if a user group has admin or draft right
        if ( (wl\main::any( man\right::hasOne($laGroups, array($loAdminRight)))) || (wl\main::any( man\right::hasOne($laGroups, array($loDirRight)))) )
            return "w";
        
        //get read and write rights of this draft
        $laReadRight  = $this->getRights("read");
        $laWriteRight = $this->getRights("write");
        
        
        // check the other rights of the user
        if (man\right::hasOne($poUser, $laReadRight))
            return "w";
        if (man\right::hasOne($poUser, $laWriteRight))
            return "r";
        
        // check groups of the user and their rights of this draft
        if (wl\main::any( man\right::hasOne($laGroups, $laReadRight)))
            return "r";
        if (wl\main::any( man\right::hasOne($laGroups, $laWriteRight)))
            return "w";
        
        
        return null;
    }
    
    /** print method of the object
     * @return string representation
     **/
    function __toString() {
        return $this->mcFQN;
    }
}
    
?>