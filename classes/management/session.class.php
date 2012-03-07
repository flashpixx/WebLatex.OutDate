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

require_once( __DIR__."/user.class.php" );
    

/** class for encapsuling the session data. The class
 * is needed, because the jQuery calls the session id
 * must be setup manually
 */
class session {
    
    /** prefix for all session data names **/
    private static $prefix      = "weblatex::";
    /** name of the session parameter **/
    public static $sessionname  = "sess";
    
    
    
    /** starts the session **/
    static function init() {
        
        if (isset($_GET[self::$sessionname]))
            @session_id($_GET[self::$sessionname]);
        if (isset($_POST[self::$sessionname]))
            @session_id($_POST[self::$sessionname]);
        
        @session_start();
    }
    
    /** returns the logged-in user object of exists
     * @return user object or null
     **/
    static function getLoggedInUser() {
        $loUser = null;
        
        if (isset($_SESSION[self::$prefix."loginuser"])) {
            $loUser = $_SESSION[self::$prefix."loginuser"];
            if (!($loUser instanceof user))
                $loUser = null;
        }
        
        return $loUser;
    }
    
    /** sets the logged in user object
     * @param $po user object
     **/
    static function setLoggedInUser( $po ) {
        if (!($po instanceof user))
            wl\main::phperror( "argument must be a user object", E_USER_ERROR );
        
        $_SESSION[self::$prefix."loginuser"] = $po;
    }
    
    /** remove the loggedin user object of a session **/
    static function clearLoggedInUser() {
        if (isset($_SESSION[self::$prefix."loginuser"]))
            unset($_SESSION[self::$prefix."loginuser"]);
    }
    
    /** returns a session object
     * @param $pcName name of the session object
     **/
    static function get( $pcName ) {
        if (!is_string($pcName))
            wl\main::phperror( "argument must be a string value", E_USER_ERROR );
        if ($pcName == "loginuser")
            wl\main::phperror( "use the getLoggedInUser() method of this class", E_USER_ERROR );
        
        if (isset($_SESSION[self::$prefix.$pcName]))
            return $_SESSION[self::$prefix.$pcName];
        
        return null;
    }
    
    /** sets a value to the session
     * @param $pcName name of the data
     * @param $pxValue value
     **/
    static function set( $pcName, $pxValue ) {
        if (!is_string($pcName))
            wl\main::phperror( "argument must be a string value", E_USER_ERROR );
        if ($pcName == "loginuser")
            wl\main::phperror( "use the setLoggedInUser() method of this class", E_USER_ERROR );
        
        $_SESSION[self::$prefix.$pcName] = $pxValue;
    }
    
    /** builds the get parameters with session id
     * @param $pa array with parameters
     * @return string
     **/
    static function buildURLParameter( $pa = null ) {
        if ( (!empty($pa)) && (!is_array($pa)) )
            wl\main::phperror( "argument must be an array", E_USER_ERROR );
        
        if (empty($pa))
            return http_build_query(array(self::$sessionname => session_id()));
        
        return http_build_query(array_merge(array(self::$sessionname => session_id()), $pa));
    }
    
}





?>