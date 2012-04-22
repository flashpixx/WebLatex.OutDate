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
require_once( __DIR__."/draft.class.php" );
require_once( __DIR__."/documentpart.class.php" );
require_once( __DIR__."/baseedit.class.php" );

    

/** class of representation a document **/
class document implements baseedit {
    
    /* document id **/
    private $mnID           = null;
    /** owner object **/
    private $moOner         = null;
    /** path for generating the document **/
    private $mcGeneratePath = null;
    /** database object **/
    private $moDB      = null;

    
    
    
    /** creates a new user document
     * @param $pcName document name
     * @param $poUser user object of the owner
     * @return the new document object
     * @todo check the Insert_ID() call for non-mysql databases
     **/
    static function create( $pcName, $poUser ) {
        if ( (!is_string($pcName)) || (!($poUser instanceof mam\user)) )
            wl\main::phperror( "arguments must be string value and a user object", E_USER_ERROR );
        
        $loDB = wl\main::getDatabase();
        $loDB->Execute( "INSERT IGNORE INTO document (name,owner) VALUES (?,?)", array($pcName, $poUser->getID()) );
        return new document(intval($loDB->Insert_ID()));
    }
    
    /** deletes a document
     * @param $pnDID document id
     **/
    static function delete( $pnDID ) {
        if (!is_numeric($pnDID))
            wl\main::phperror( "argument must be a numeric value", E_USER_ERROR );
        
        wl\main::getDatabase()->Execute( "DELETE FROM document WHERE id=?", array($pnDID) );
    }
    
    /** returns an array with drafts
     * @param $poUser user object, for getting drafts of this user
     * @return array with draft object
     **/
    static function getList( $poUser =  null) {
        $la = array();
        
        if ($poUser instanceof man\user)
            $loResult = wl\main::getDatabase()->Execute("SELECT id FROM document WHERE owner=?", array($poUser->getID()));
        else    
            $loResult = wl\main::getDatabase()->Execute("SELECT id FROM document");
        
        
        if (!$loResult->EOF)
            foreach($loResult as $laRow)
                array_push( $la, new document(intval($laRow["id"])) );
        
        return $la;
    }
    
    
    
    /** constructor
     * @param $px document id or document name
     **/
    function __construct( $px ) {
        if ( (!is_numeric($px)) && (!is_string($px)) && (!($px instanceof $this)) )
            wl\main::phperror( "argument must be a numeric, string or document object value", E_USER_ERROR );
        
        // if the parameter is a string, it must be a FQN path, so split in dir- and draftname
        if (is_string($px)) {
            $loDir   = new directory( dirname($px) );
            $loDoc   = $loDir->getChildByName( basename($px), "document" );
            if (empty($loDoc))
                throw new \Exception( "document not found within the path" );
        }
        
        $this->moDB = wl\main::getDatabase();
        if (is_numeric($px))
            $loResult = $this->moDB->Execute( "SELECT id, owner FROM document WHERE id=?", array($px) );
        if ($px instanceof $this)
            $loResult = $this->moDB->Execute( "SELECT id, owner FROM document WHERE id=?", array($px->getID()) );
        if (is_string($px))
            $loResult = $this->moDB->Execute( "SELECT id, owner FROM document WHERE id=?", array($loDoc->getID()) );
        
        if ($loResult->EOF)
            throw new \Exception( "document data not found" );
        
        $this->mnID   = intval($loResult->fields["id"]);
        if (!empty($loResult->fields["owner"]))
            $this->moOwner = new man\user(intval($loResult->fields["owner"]));
        
        // set the generate path for the PDF
        $this->mcGeneratePath = wl\main::getTempDir()."/".session_id()."/".$this->mnID;
    }

    /** returns the unique id
     * @return id
     **/
    function getID() {
        return $this->mnID;
    }
    
    /** returns the user owner object
     * @return owner object or null if owner is deleted
     **/
	function getOwner() {
        return $this->moOwner;
    }
    
    /** get the document name **/
    function getName() {
        $loResult = $this->moDB->Execute( "SELECT name FROM document WHERE id=?", array($this->mnID) );
        return $loResult->fields["name"];
    }
    
    /** get the draft
     * @return draft object or draft content
     **/
    function getDraft() {
        $loResult = $this->moDB->Execute( "SELECT draft, draftid FROM document WHERE id=?", array($this->mnID) );
        if (empty($loResult->fields["draftid"]))
            return $loResult->fields["draft"];
        else
            return new draft(intval($loResult->fields["draftid"]));
    }
    
    /** returns the archivable flag
     * @return boolean is the document is archivable
     **/
    function isArchivable() {
        $loResult = $this->moDB->Execute( "SELECT archivable FROM document WHERE id=?", array($this->mnID) );
        return $loResult->fields["archivable"] == true;
    }
    
    /** sets the archivable flag
     * @param $plArchiveable boolean
     **/
    function setArchivable( $plArchiveable ) {
        wl\main::getDatabase()->Execute( "UPDATE document SET archivable=? WHERE id=?", array( ($plArchiveable ? "true" : "false"), $this->mnID) );
    }
    
    /** returns the modifiable flag
     * @return boolean is the document modifiable
     **/
    function isModifiable() {
        $loResult = $this->moDB->Execute( "SELECT modifiable FROM document WHERE id=?", array($this->mnID) );
        return $loResult->fields["modifiable"] == true;
    }
    
    /** sets the modifiable flag
     * @param $plModifiable boolean
     **/
    function setModifiable( $plModifiable ) {
        $this->moDB->Execute( "UPDATE document SET modifiable=? WHERE id=?", array( ($plModifiable ? "true" : "false"), $this->mnID) );
    }
    
    /** returns the access of an user
     * @param $poUser user object
     * @return null for no access, "r" read access and "w" for read-write access
     **/
    function getAccess($poUser) {
        if (!($poUser instanceof man\user))
            wl\main::phperror( "argument must be a user object", E_USER_ERROR );
        
        // document & administrator right
        $loDocumentRight = new man\right( wl\config::$system_rights["document"] );
        $loAdminRight    = new man\right( wl\config::$system_rights["administrator"] );
        
        // check if the user is the owner or has administrator or document right
        if ( ($poUser->isEqual($this->getOwner())) || ($loDocumentRight->hasRight($poUser)) || ($loAdminRight->hasRight($poUser)) )
            return "w";
        
        
        // get user groups
        $laGroups = $poUser->getGroups();
        
        // check if a user group has admin or document right
        if ( (wl\main::any( man\right::hasOne($laGroups, array($loDocumentRight)))) || (wl\main::any( man\right::hasOne($laGroups, array($loAdminRight)))) )
            return "w";
        
        
        //get read and write rights of this document
        $laReadRight  = $this->getRights("read");
        $laWriteRight = $this->getRights("write");
        
        
        // check the other rights of the user
        if (man\right::hasOne($poUser, $laReadRight))
            return "r";
        if (man\right::hasOne($poUser, $laWriteRight))
            return "w";
        
        // check groups of the user and their rights of this document
        if (wl\main::any( man\right::hasOne($laGroups, $laReadRight)))
            return "r";
        if (wl\main::any( man\right::hasOne($laGroups, $laWriteRight)))
            return "w";
        
        
        return null;
    }
    
    /** returns a list of document parts or a part
     * @return list of document part object
     **/
    function getParts() {
        $la = array();
        
        $loResult = $this->moDB->Execute( "SELECT id FROM documentpart WHERE document=?", array($this->mnID) );
        if (!$loResult->EOF)
            foreach($loResult as $laRow)
                array_push( $la, new documentpart(intval($laRow["id"])) );
        
        return $la;
    }
    
    /** adds a new document part to the document
     * @param $pcName description
     * @return the new document part object
     **/
    function addPart( $pcName = null ) {
        $this->moDB->Execute( "INSERT INTO documentpart (document, description) VALUES (?, ?)", array($this->mnID, $pcName) );
        return new documentpart( $this->moDB->Insert_ID() );
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
        $this->moDB->Execute("INSERT INTO document_rights VALUES (?,?,?) ON DUPLICATE KEY UPDATE access=?", array($this->mnID, $poRight->getID(), $access, $access));
    }
    
    /** deletes the right 
     * @param $poRight right object
     **/
    function deleteRight( $poRight ) {
        if (!($poRight instanceof man\right))
            wl\main::phperror( "argument must be a right object", E_USER_ERROR );
        
        $this->moDB->Execute("DELETE FROM document_rights WHERE document=? AND rights=?", array($this->mnID, $poRight->getID()));
    }
    
    /** returns an array with right objects
     * @param $pcType type of the right, empty all rights, "write" only write access, "read" only read access
     * @return array with rights
     **/
    function getRights($pcType = null) {
        if (empty($pcType))
            $loResult = $this->moDB->Execute("SELECT rights FROM document_rights WHERE document=?", array($this->mnID));
        else
            $loResult = $this->moDB->Execute("SELECT rights FROM document_rights WHERE document=? AND access=?", array($this->mnID, $pcType));
        
        $la = array();
        if (!$loResult->EOF)
            foreach($loResult as $laRow)
                array_push($la, new man\right(intval($laRow["rights"])));
        
        return $la;
    }   
    
    /** creates the lock of the document
     * @param $poUser user object
     **/
    function lock( $poUser ) {
        
    }
    
    /** unlocks the document **/
    function unlock() {
        
    }
    
    /** returns the user object if a lock exists
     * @return user object or null
     **/
    function hasLock() {
        
    }
    
    /** restore a history entry
     * @param $pnID history id
     **/
    function restoreHistory($pnID) {
        
    }
    
    /** deletes the whole history or a single entry
     * @param $pxID null, numeric value or array of numeric values
     **/
    function deleteHistory($pxID = null) {
        
    }
    
    /** returns the content of a history entry
     * @param $pnID entry id
     * @return content
     **/
    function getHistoryContent($pnID) {
        
    }
    
    /** returns an array with ids and timestamps of the history entries
     * @return assoc. array
     **/
    function getHistory() {
        
    }
    
    /** converts the HTML code to TeX code
     * @param $pc input HTML code
     * @return TeX code
     **/
    private static function convert2TeX($pc) {
        return mb_convert_encoding(strip_tags(html_entity_decode($pc)), "UTF-8", "auto");
    }
    
    /** converts the HTML code to the LaTeXMK command code
     * @param $pc input text
     * @return LaTeX MK code
     **/
    private static function convert2MK($pc)  {
        return mb_convert_encoding(strip_tags(html_entity_decode($pc)), "UTF-8", "auto");
    }
    
    /** generates the PDF with the pdf2latex calls and returns the
     * absolut path to the PDF. Errors will be thrown with an exception
     **/
    function generatePDF() {
        if (empty($this->mcGeneratePath))
            throw new \Exception( "temporary path is empty, so can not create any PDF" );
        
        if ( (!is_dir($this->mcGeneratePath)) && (!@mkdir($this->mcGeneratePath, 0700, true)) )
            throw new \Exception( "temporary path can not be created" );
        
        // clear first the PDF is exists
        if (file_exists($this->mcGeneratePath."/document.pdf"))
            @unlink($this->mcGeneratePath."/document.pdf");
        
        
        // get the draft
        $loResult = $this->moDB->Execute( "SELECT draft.content AS draft, MD5(draft.content) AS hash_draft, document.draft AS localdraft, MD5(document.draft) AS hash_localdraft, latexmk, MD5(latexmk) AS hash_latexmk FROM document LEFT JOIN draft ON draft.id=document.draftid WHERE document.id=?", array($this->mnID) );
        if ($loResult->EOF)
            throw new \Exception( "document data not found" );
        
        // write the LatexMK file
        $lcLatexMKrc = $this->mcGeneratePath."/latexmkrc";
        if ( (!empty($loResult->fields["latexmk"])) && ((!file_exists($lcLatexMKrc)) || ($loResult->fields["hash_latexmk"] != @md5_file($lcLatexMKrc))) ) 
            @file_put_contents($lcLatexMKrc, self::convert2MK($loResult->fields["latexmk"]));
        else
            $lcLatexMKrc = null;
        
        
        // write the main document
        $lcFilename = $this->mcGeneratePath."/document.tex";
        $lcHash     = null;
        if (file_exists($lcFilename))
            $lcHash = @md5_file($lcFilename);
            
        // extract the draft content of the draft table data 
        if (!empty($loResult->fields["draft"])) {
            // check if the ###content### string found in the draft
            if ($loResult->fields["hash_draft"] != $lcHash)
                @file_put_contents($lcFilename, self::convert2TeX($loResult->fields["draft"]));
                    
        } else {
                
            // check if the ###content### string found in the draft
            if ($loResult->fields["hash_localdraft"] != $lcHash)
                @file_put_contents($lcFilename, self::convert2TeX($loResult->fields["localdraft"]));
        }
        
        
        // extract draft, search within the draft the ###content### section for adding the content
        // extract document parts and replace the ###content### with the include calls
        // extract media data and bibtex data
        
        // convert HTML document code with XSLT into TeX code
        
        // run latexmk.pl (it seems it is a better choice for pdf2latex, changing configuration option)
        // with options: -cd -pdf -gg -f -silent
        // pdf for generate PDF, gg rebuild aux-files, -f for running more than one times, -silent for
        // run without stopping on errors
        // There is no option to getting errors after the runs, take a look to the output of the script
        

        // set the environment variable, so latexmk find all command, call perl interpreter with latexmk command and parameter,
        // and store the result into a log file
        $lcCMD = wl\config::perl." ".wl\config::latexmk." -cd -pdf -f ".(empty($lcLatexMKrc) ? null : "-r ".$lcLatexMKrc)." ".$this->mcGeneratePath."/document.tex";
        
        ob_start();
        putenv("PATH=".wl\config::texbin);
        system($lcCMD, $lnError);
        $lcReturn = ob_get_contents();
        @ob_end_clean();
        
        file_put_contents($this->mcGeneratePath."/weblatex.log", $lcCMD."\n\n".$lcReturn."\n");
        if ($lnError)
            throw new \Exception( "LaTeXMK call creates an error" );
    }

    /** gets the path to the PDF if exists
     * @return null or absolut path
     **/
    function getPDF() {
        $lcPDF = null;
        if (file_exists($this->mcGeneratePath."/document.pdf"))
            $lcPDF = $this->mcGeneratePath."/document.pdf";
        
        return $lcPDF;
    }
}

?>