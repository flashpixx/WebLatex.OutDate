<?

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


/** global autoload function (used by theme and plugins)
 * @param $pcClassname
 **/
function __autoload( $pcClassname ) {
    
    // removing namespaces and checks which path should be loaded
    $laNSPath = explode("\\", $pcClassname);
    $lcPath   = null;
    $lcClass  = null;

    // checks if the namespace is a design
    if ( (count($laNSPath) == 3) && ($laNSPath[1] == "design") ) {
        $lcClass = $laNSPath[2];
        $lcPath  = dirname(__DIR__)."/themes/".$lcClass."/".$lcClass.".class.php";
    }

    // checks data and include the file
    if ( (empty($lcPath)) || (empty($lcClass)) )
        throw new \Exception("class data [".$pcClassname."] cannot be detected");
    
    if ((!file_exists($lcPath)) || (!is_readable($lcPath)) )
        throw new \Exception("theme [".$lcClass."] cannot be loaded");
    
    require_once($lcPath);
}   

?>