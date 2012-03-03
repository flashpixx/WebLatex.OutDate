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
use weblatex as wl;

require_once( dirname(dirname(__DIR__))."/classes/design/design.class.php" );


/** class of the default theme **/
class defaulttheme extends design {

    /** create HTML header
     * @param $poUser logged-in userobject
     **/
    function header( $poUser = null ) { 
        echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n\n";
        echo "<html xmlns=\"http://www.w3.org/1999/xhtml\">\n";
        echo "<head><title>WebLaTeX</title>\n\n";
        
        echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\n";
        echo "<meta http-equiv=\"Content-Style-Type\" content=\"text/css\" />\n";
        echo "<meta http-equiv=\"Content-Script-Type\" content=\"text/javascript\" />\n";
        
        echo "<link rel=\"stylesheet\" href=\"".wl\main::getThemeDir()."layout.css\" type=\"text/css\" media=\"screen\" />\n";
    }
    
    /** close the header and create the body **/
    function body( $poUser = null ) {
        echo "</head><body>\n";
    }

    /** create HTML footer
     * @param $poUser logged-in userobject
     **/
    function footer( $poUser = null ) {
        echo "</body></html>\n";
    }

}

?>