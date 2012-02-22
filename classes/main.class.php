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
    
namespace weblatex;
    
require_once( dirname(__DIR__)."/config.inc.php" );
require_once( dirname(__DIR__)."/tools/phppass/PasswordHash.php" );
require_once( dirname(__DIR__)."/tools/adodb5/adodb.inc.php" );
    

/** class with primary static functions
 * for create a function library
**/
class main {
    
	/** creates a database connection objekt (the call without any parameter creates
     * a database object that points to the database within the configuration)
     * @todo check encoding for other databases (we will use only utf8)
     * @param $pcType database type (see http://adodb.sourceforge.net/ )
     * @param $pcDatabaseHost host of the database
     * @param $pcDatabaseUser login user of the database
     * @param $pcDatabasePassword login password of the database
     * @param $pcDatabase name of the database
	 * @return database object
     **/
	static function getDatabase( $pcType = config::databasetype, $pcDatabaseHost = config::databasehost, $pcDatabaseUser = config::databaseuser, $pcDatabasePassword = config::databasepassword, $pcDatabase = config::databasename ) {
        $loDB = NewADOConnection( $pcType );
        $loDB->debug = config::debug;
        $loDB->Connect( $pcDatabaseHost, $pcDatabaseUser, $pcDatabasePassword, $pcDatabase );
        
        $loDB->Execute( "SET character_set_database=UTF8" );
        $loDB->Execute( "SET character_set_client=UTF8" );
        $loDB->Execute( "SET character_set_connection=UTF8" );
        $loDB->Execute( "SET character_set_results=UTF8" );
        $loDB->Execute( "SET character_set_server=UTF8" );
        $loDB->Execute( "SET names UTF8");
        
        return $loDB;
	}
    
    /** creates a php error trigger
     * @param pcMessage message
     * @param pnLevel error type (see http://www.php.net/manual/en/errorfunc.constants.php )
     **/
    static function phperror( $pcMessage, $pnLevel = E_USER_NOTICE ) {
        $laCaller = next( debug_backtrace() );
        trigger_error($pcMessage." in [".$laCaller["function"]."] called from [".$laCaller["file"]."] on line ".$laCaller["line"], $pnLevel);
    }
    
    /** generates a hash
     * @param $pcRaw input data
     * @return hash
     **/
    static function generateHash( $pcRaw ) {
        
        if (config::hashtype == "phppass") {
            $loHash = new \PasswordHash( config::hashiteration, false );
            return $loHash->HashPassword($pcRaw); 
        }
        
        return hash_hmac( config::hashtype, $pcRaw, config::hashkey );
    }
    
    /** validate a hash
     * @param $pcRaw input text
     * @param $pcHash hash which should be validate
     * @return boolean if both equal
     **/
    static function validateHash( $pcRaw, $pcHash ) {
        if (config::hashtype == "phppass") {
            $loHash = new \PasswordHash( config::hashiteration, false );
            return $loHash->CheckPassword($pcRaw, $pcHash);
        }
        
        return hash_hmac( config::hashtype, $pcRaw, config::hashkey ) == $pcHash;
    }
    
    /** returns the path to the theme relative to the webserver root 
     * @return path
     **/
    static function getThemeDir() {
        return "themes/".config::theme."/";
    }
	
}


?>
