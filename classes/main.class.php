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
    
namespace weblatex;
    
require_once( dirname(__DIR__)."/wl-config.inc.php" );
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
    
    /** binds a language file to a domain, default domain is [weblatex]
     * @param $pcDomain domain name
     * @param $pcDirectory directory of the language files
     **/
    static function bindLanguage( $pcDomain, $pcDirectory ) {
        bindtextdomain($pcDomain, $pcDirectory);
        bind_textdomain_codeset($pcDomain, "UTF-8");
        textdomain($pcDomain);
    }
    
    /** creates the initialize language setup (must be call manually because jQuery calls) **/
    static function initLanguage() {
        $lcLang = config::language;
        if (!empty($lcLang)) {
            setlocale(LC_MESSAGES, $lcLang.".UTF-8");   
            putenv("LANG=".$lcLang.".UTF-8");
            putenv("LANGUAGE=".$lcLang.".UTF-8");
        
            self::bindLanguage("weblatex", dirname(dirname(__DIR__))."/language/");
        }
    }
    
    /** returns the path to the temporary directory (we use the path to render
     * the PDF with pdf2latex
     * @return temporary path
     **/
    static function getTempDir() {
        $lcConfigTemp = config::tempdir;
        if (!empty($lcConfigTemp))
            return realpath($lcConfigTemp);
        
        if ( function_exists("sys_get_temp_dir") )
            return realpath(sys_get_temp_dir());
        
        if ( (isset($_ENV["TMP"])) && (!empty($_ENV["TMP"])) )
            return realpath($_ENV["TMP"]);
        
        if ( (isset($_ENV["TEMP"])) && (!empty($_ENV["TEMP"])) )
            return realpath($_ENV["TEMP"]);
                            
        if ( (isset($_ENV["TMEPDIR"])) && (!empty($_ENV["TMEPDIR"])) )
            return realpath($_ENV["TMEPDIR"]);
        
        self::phperror( "temporary path not found", E_USER_ERROR );
    }
    
    /** returns true if all elements within the array are true
     * @param $pa boolean array
     * @return boolean if all is true
     **/
    static function all($pa) {
        if (!is_array($pa))
            self::phperror( "argument must be a boolean array", E_USER_ERROR );
        
        foreach($pa as $la)
            if (!$la)
                return false;
        return true;
    }
    
    /** returns true if any elements within the array are true
     * @param $pa boolean array
     * @return boolean if any is true
     **/
    static function any($pa) {
        if (!is_array($pa))
            self::phperror( "argument must be a boolean array", E_USER_ERROR );
        
        foreach($pa as $la)
            if ($la)
                return true;
        return false;
    }
	
}


?>
