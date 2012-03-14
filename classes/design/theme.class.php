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
use weblatex\management as wm;
    
require_once( dirname(dirname(__DIR__))."/config.inc.php" );
require_once( dirname(__DIR__)."/autoload.php" );
require_once( dirname(__DIR__)."/main.class.php" );
require_once( dirname(__DIR__)."/management/user.class.php" );
require_once( dirname(__DIR__)."/management/session.class.php" );


    
/** class for loading the user-defined theme data **/
class theme {
    
    /** theme object **/
    private $moTheme = null;
    
    
    /** contructor creates the theme object **/
    function __construct() {
        
        // create language
        wl\main::initLanguage();
        
        // create themes
        $lcTheme = wl\config::theme;
        if (empty($lcTheme))        
            throw new \Exception("theme is empty");
        
        eval("\$this->moTheme = new weblatex\\design\\".$lcTheme."();");
    }
    
    /** method that is used before the header is sended
     * @param $plLocation sends if the session is not active to the login
     * @return user object
     **/
    function init( $plLocation = true ) {
        wm\session::init();
        $this->moTheme->init();
        $loUser = wm\session::getLoggedInUser();
        
        // check logged in user and create a redirect
        if ( (empty($loUser)) && ($plLocation) )
            @header("Location: index.php");
        
        return $loUser;
    }
    
    /** method that creates the header and body
     * @param $poUser user object
     * @param $pcHeader additional header information
     **/
    function header( $poUser = null, $pcHeader = null ) {
        $this->moTheme->header( $poUser );
        echo $pcHeader;
        
        if (!empty($poUser)) {
            
            // use the minified javascript- and css-script for a better performance
            echo "<link type=\"text/css\" href=\"tools/minify/?g=basecss\" rel=\"stylesheet\" />\n";
            echo "<script type=\"text/javascript\" src=\"tools/minify/?g=basejs\"></script>\n";
            
            // we set the configuration data for the session in the namespace "weblatex"
            echo "<script type=\"text/javascript\">";
            echo "if (webLaTeX !== undefined)";
            echo "webLaTeX.getInstance(\"".wm\session::$sessionname."\", \"".session_id()."\", ".(wl\config::autosavetime*1000).", ";
            echo "{ ";
            
            echo "  create           : \""._("Create")."\",";
            echo "  edit             : \""._("Edit")."\",";
            echo "  del              : \""._("Delete")."\",";
            
            echo "  directory        : \""._("directory")."\",";
            echo "  draft            : \""._("draft")."\",";
            echo "  right            : \""._("right")."\",";
            echo "  group            : \""._("group")."\",";
            
            echo "  directoryload    : \""._("loading")."\", ";
            echo "  directoryadd     : \""._("set directory name")."\", ";
            echo "  directorycreate  : \""._("directory creating")."\", ";
            echo "  directoryerror   : \""._("directory error")."\",";
            
            echo "  draftadd         : \""._("set draft name")."\", ";
            echo "  draftcreate      : \""._("draft creating")."\",";
            
            echo "  drafterror       : \""._("draft error")."\",";

            echo "  labelcreatedir   : \""._("create directory")."\",";
            echo "  labelcreatedraft : \""._("create draft")."\",";
            echo "  labelcreatedoc   : \""._("create document")."\",";
            echo "  labelcreateright : \""._("create right")."\",";
            echo "  labelcreategroup : \""._("create group")."\",";
            echo "  labelisusedby    : \""._("is used by")."\",";
            echo "  labelgeneratepdf : \""._("generate PDF")."\"";
            
            echo "});";
            echo "</script>";
            
            // CKEditor does not work with minify, so we set the references manually
            echo "<script type=\"text/javascript\" src=\"tools/ckeditor/ckeditor.js\"></script>\n";
            echo "<script type=\"text/javascript\" src=\"tools/ckeditor/adapters/jquery.js\"></script>\n";
        }
        
        $this->moTheme->body( $poUser );
    }
    
    /** method that creates the footer
     * @param $poUser user object
     **/
    function footer( $poUser = null ) {
        $this->moTheme->footer($poUser);
    }

}

?>
