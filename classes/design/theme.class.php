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
    
require_once( dirname(dirname(__DIR__))."/config.inc.php" );
require_once( dirname(__DIR__)."/autoload.php" );
require_once( dirname(__DIR__)."/main.class.php" );


    
/** class for loading the user-defined theme data **/
class theme {

    /** theme object **/
    private $moTheme = null;
    
    
    /** contructor creates the theme object **/
    function __construct() {
        
        // create language addon via gettext
        $lcLang = wl\config::language;
        if (!empty($lcLang)) {
            putenv("LC_MESSAGES=".$lcLang.".UTF-8");
            setlocale(LC_MESSAGES, $lcLang);   
            wl\main::bindLanguage("weblatex", dirname(dirname(__DIR__))."/language/");
        }
        
        // create themes
        $lcTheme = wl\config::theme;
        if (empty($lcTheme))        
            throw new \Exception("theme is empty");
        
        eval("\$this->moTheme = new weblatex\\design\\".$lcTheme."();");
    }
    
    /** method that is used before the header is sended **/
    function init() {
        @session_start();
        $this->moTheme->init();
    }
    
    /** method that creates the header and body
     * @param $poUser user object
     **/
    function header( $poUser = null ) {
        $this->moTheme->header( $poUser );
        $this->moTheme->body( $poUser );
    }
    
    /** method that creates the footer
     * @param $poUser user object
     **/
    function footer( $poUser = null ) {
        $this->moTheme->footer($poUser);
    }
    
    /** main menu
     * @param $poUser user object
     **/
    function mainMenu( $poUser = null ) {
        echo "<div id=\"weblatex-menu\">";
        echo "<span class=\"weblatex-logomini\"><a href=\"http://code.google.com/p/weblatex/\" target=\"_blank\">Web<img src=\"images/latex.png\"></a></span>\n";
        echo "<ul>\n";
        echo "  <li><a>"._("documents")."</a>\n";
        echo "      <ul>\n";
        echo "          <li><a href=\"\">"._("new document")."</a></li>\n";
        echo "          <li><a href=\"\">"._("new draft")."</a></li>\n";
        echo "          <li><a href=\"\">"._("drafts")."</a></li>\n";
        echo "          <li><a href=\"\">"._("documents")."</a></li>\n";
        echo "      </ul>\n";
        echo "  </li>\n";
        
        echo "  <li><a>"._("directories")."</a>\n";
        echo "      <ul>\n";
        echo "          <li><a href=\"\">"._("directories")."</a></li>\n";
        echo "      </ul>\n";
        echo "  </li>\n";
        
        echo "  <li><a>"._("settings")."</a>\n";
        echo "      <ul>\n";
        echo "          <li><a href=\"wl-password.php\">"._("change password")."</a></li>\n";
        echo "      </ul>\n";
        echo "  </li>\n";
        
        echo "  <li><a href=\"wl-logout.php\">"._("logout")."</a></li>\n";
        
        echo "  <li><a>"._("help")."</a>\n";
        echo "      <ul>\n";
        echo "          <li><a href=\"\">GUI</a></li>\n";
        echo "          <li><a href=\"\">LaTeX</a></li>\n";
        echo "      </ul>\n";
        echo "  </li>\n";
        
        echo "</ul>\n";
        echo "</div>\n";
    }
    
}

?>
