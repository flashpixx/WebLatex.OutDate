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

namespace weblatex\design;
    
require_once( dirname(dirname(__DIR__))."/wl-config.inc.php" );
    
    

/** abstract class for the design themes **/
abstract class design {
    
    /** method for the HTML header
     * @param $poUser user object
     **/
    abstract function header( $poUser = null );
    
    /** method for the HTML footer
     * @param $poUser user object
     **/
    abstract function footer( $poUser = null );
    
    /** create html body structure **/
    abstract function body( $poUser = null );
    
    /** initialization function, before the header ist sended **/
    function init() {}

}


?>