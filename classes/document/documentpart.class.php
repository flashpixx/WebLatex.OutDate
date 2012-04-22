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



/** class of representation a document part like chapter or subsection **/
class documentpart {
    
    /* document id **/
    private $mnID           = null;
    /** database object **/
    private $moDB           = null;
    /** document object **/
    private $moDocument     = null;
    
    
    /** constructor
     * @param $px document id or document object
     **/
    function __construct( $px ) {
        if ( (!is_numeric($px)) && (!($poDoc instanceof document)) )
            wl\main::phperror( "argument must be a numeric or document object value", E_USER_ERROR );
        
        $this->moDB = wl\main::getDatabase();
        if (is_numeric()) {
            $loResult = $this->moDB->Execute( "SELECT document FROM documentpart WHERE id=?", array($px) );
            if ($loResult->EOF)
                throw new \Exception( "documentpart data not found" );
            
            $this->mnID         = $px;
            $this->moDocument   = new document( intval($loResult->fields["document"]) );
        } else
            $this->moDocument = $poDoc;
    }
    
    /** returns a list of all document part objects of the document
     * @param $poUser user object, that have access to the parts
     * @return array with part objects
     **/
    function getAll( $poUser = null ) {
        $la = array();
        
        $loResult = $this->moDB->Execute( "SELECT id FROM documentpart WHERE document=?", array($this->moDocument->getID()) );
        if (!$loResult->EOF)
            foreach($loResult as $laRow) {
                $loPart = new documentpart(intval($laRow["id"]));
                
                if ($poUser instanceof man\user) {
                    $lxAccess = $loPart->getAccess($poUser);
                    if (!empty($lxAccess))
                        array_push( $la, $loPart );
                } else
                    array_push( $la, $loPart );
            }
        
        return $la;
        
    }
    
    function setContent( $pcContent ) {
        
    }
    
    
}


?>