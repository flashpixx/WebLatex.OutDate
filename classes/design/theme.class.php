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


    
/** class for loading the user-defined theme data **/
class theme {
    
    /** theme object **/
    private $moTheme = null;
    
    
    /** contructor creates the theme object **/
    function __construct() {
        
        // create language addon via gettext
        $lcLang = wl\config::language;
        if (!empty($lcLang)) {
            setlocale(LC_MESSAGES, $lcLang.".UTF-8");   
            putenv("LANG=".$lcLang.".UTF-8");
            putenv("LANGUAGE=".$lcLang.".UTF-8");
            wl\main::bindLanguage("weblatex", dirname(dirname(__DIR__))."/language/");
        }
        
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
        @session_start();
        $this->moTheme->init();
        
        // read login user object
        if ( (!isset($_SESSION["weblatex::loginuser"])) || (!($_SESSION["weblatex::loginuser"] instanceof wm\user)) ) {
            if ($plLocation)
                @header("Location: index.php");
            return null;
        }
        return $_SESSION["weblatex::loginuser"];
    }
    
    /** method that creates the header and body
     * @param $poUser user object
     * @param $pcHeader additional header information
     **/
    function header( $poUser = null, $pcHeader = null ) {
        $this->moTheme->header( $poUser );
        echo $pcHeader;
        $this->moTheme->body( $poUser );
    }
    
    /** method that creates the footer
     * @param $poUser user object
     **/
    function footer( $poUser = null ) {
        $this->moTheme->footer($poUser);
    }
    
    /** returns the code of the JavaScript editor
     * @return html configuration code 
     **/
    static function getEditorCode( $pcAutoSaveURL = null ) {
        return "<script type=\"text/javascript\" src=\"tools/ckeditor/ckeditor.js\"></script>
                <script type=\"text/javascript\">
        
                CKEDITOR.plugins.add( 'Archive', {
                    init : function(editor){
                
                        editor.addCommand( 'Archiveable', {
                            exec : function( editor ) {    
                                var lo = document.getElementById('archivable');
                                if (lo == null)
                                    return;
                
                                if (lo.value == '')
                                    lo.value = '1';
                                else
                                    lo.value = '';
                            }
                        });
                
                        editor.ui.addButton( 'Archive', {
                            label   : 'data will be archived',
                            command : 'Archiveable'
                        });
                    }
                });
                
                
                CKEDITOR.config.skin              = 'office2003';
                CKEDITOR.config.autoParagraph     = false;
                CKEDITOR.config.extraPlugins      = 'Archive,autosave';
                CKEDITOR.config.autosaveTargetUrl = '".$pcAutoSaveURL."';
                
                
                CKEDITOR.config.toolbar         = 
                [
                    { name: 'document',    items : [ 'Save','NewPage','DocProps','Print'] },
                    { name: 'clipboard',   items : [ 'Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo' ] },
                    { name: 'editing',     items : [ 'Find','Replace','-','SelectAll','-','SpellChecker', 'Scayt' ] },
                    { name: 'tools',       items : [ 'Archive','Autosave','-','Maximize','-','About' ] },
                    '/',
                    { name: 'basicstyles', items : [ 'Bold','Italic','Underline','-','RemoveFormat' ] },
                    { name: 'paragraph',   items : [ 'NumberedList','BulletedList','-','Blockquote','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock' ] },
                    { name: 'insert',      items : [ 'Image','Table','PageBreak' ] },
                    { name: 'styles',      items : [ 'Styles','Format','Font','FontSize' ] },
                ];
        
            </script>
        ";
        
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
        echo "          <li><a href=\"wl-createdocument.php\">"._("new document")."</a></li>\n";
        echo "          <li><a href=\"wl-createdraft.php\">"._("new draft")."</a></li>\n";
        echo "          <li><a href=\"wl-editdraft.php\">"._("drafts")."</a></li>\n";
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
