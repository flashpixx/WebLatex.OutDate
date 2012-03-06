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
        
        if (!empty($poUser)) {
        
            // add jQuery, tree view, editor and context menu code
            echo "<script src=\"tools/jquery.min.js\" type=\"text/javascript\"></script>\n";
            echo "<script src=\"tools/jquery.easing.1.3.js\" type=\"text/javascript\"></script>\n";
        
            echo "<script src=\"tools/jcontextMenu/jquery.contextMenu.js\" type=\"text/javascript\"></script>\n";
            echo "<link href=\"tools/jcontextMenu/jquery.contextMenu.css\" rel=\"stylesheet\" type=\"text/css\" />\n";
        
            echo "<script src=\"tools/jqueryFileTree/jqueryFileTree.js\" type=\"text/javascript\"></script>\n";
            echo "<link href=\"tools/jqueryFileTree/jqueryFileTree.css\" rel=\"stylesheet\" type=\"text/css\" media=\"screen\" />\n";
        
            echo "<script type=\"text/javascript\" src=\"tools/ckeditor/ckeditor.js\"></script>\n";
            echo "<script type=\"text/javascript\" src=\"tools/ckeditor/adapters/jquery.js\"></script>\n";
        
            // add jQuery data for visualization
            echo "<script type=\"text/javascript\">\n";
            echo "  var goRefreshLock     = null;\n";
            echo "  var gcOldURLParameter = null;\n";
            echo "\n";
            echo "  $(document).ready( function() {\n";
            echo "      $('#weblatex-directory').fileTree(\n";
            echo "          {\n";
            echo "              script      : 'wl-directorylist.php?".http_build_query(array("sess" => session_id()))."',\n";
            echo "              loadMessage : '"._("loading")."'\n";
            echo "          },\n";
            echo "\n";
            echo "          function(pcItem) {\n";
            echo "              var laItem           = pcItem.split('$');\n";
            echo "              if (laItem.length != 2)\n";
            echo "                  return;\n";
            echo "\n";
            echo "              var lcURL            = null;\n";
            echo "              var lcURLParameter   = $.param( { sess : '".session_id()."', id : laItem[1], type : laItem[0] } );\n";
            echo "\n";
            echo "              if (laItem[0] == 'draft')\n";
            echo "                  lcURL = 'wl-editdraft.php?'+lcURLParameter;\n";
            echo "\n";
            echo "              if (lcURL != null)\n";
            echo "                  $.get(lcURL, function(pcData) {\n";
            echo "                      $('#weblatex-content').fadeOut('slow', function() {\n";
            echo "                          var loEditor = CKEDITOR.instances['weblatex-editor'];\n";
            echo "\n";
            echo "                          if (loEditor) {\n";
            echo "                              loEditor.destroy();\n";
            echo "                              clearInterval(goRefreshLock);\n";
            echo "                              if (gcOldURLParameter != null)\n";
            echo "                                  $.ajax( { url : 'wl-unlock.php?'+gcOldURLParameter } );\n"; 
            echo "                          }\n";
            echo "\n";
            echo "                          gcOldURLParameter = lcURLParameter;\n";
            echo "                          goRefreshLock = setInterval( function() { $.ajax( { url : 'wl-refreshlock.php?'+lcURLParameter } ); }, ".(wl\config::autosavetime*1000).");\n";
            echo "                          $('#weblatex-content').html(pcData).fadeIn('slow');\n";
            echo "\n";
            echo "                          $('#weblatex-editor').ckeditor({\n";
            echo "                              skin                : 'office2003',\n";
            echo "                              autoParagraph       : false,\n";
            echo "                              extraPlugins        : 'autosave',\n";
            echo "                              autosaveTargetUrl   : 'wl-autosavedraft.php?'+lcURLParameter,\n";
            echo "                              autosaveRefreshTime : ".wl\config::autosavetime.",\n";
            echo "                              toolbar             : [\n";
            echo "                                  { name: 'document',    items : [ 'Save', 'NewPage','DocProps','Print'] },\n";
            echo "                                  { name: 'clipboard',   items : [ 'Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo' ] },\n";
            echo "                                  { name: 'editing',     items : [ 'Find','Replace','-','SelectAll','-','SpellChecker', 'Scayt' ] },\n";
            echo "                                  { name: 'tools',       items : [ 'Autosave', 'Maximize','-','About' ] },\n";
            echo "                                  '/',\n";
            echo "                                  { name: 'basicstyles', items : [ 'Bold','Italic','Underline','-','RemoveFormat' ] },\n";
            echo "                                  { name: 'paragraph',   items : [ 'NumberedList','BulletedList','-','Blockquote','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock' ] },\n";
            echo "                                  { name: 'insert',      items : [ 'Image','Table','PageBreak' ] },\n";
            echo "                                  { name: 'styles',      items : [ 'Styles','Format','Font','FontSize' ] },\n";
            echo "                              ]\n";
            echo "                          });\n";
            echo "                      });\n";
            echo "                  });\n";
            echo "          }\n";
            echo "      );";
            echo "\n";
            /*
            echo "      $('#gibtsnicht').contextMenu(\n";
            echo "          {\n";
            echo "              menu : 'weblatex-filemenu'\n";
            echo "          },\n";
            echo "\n";
            echo "          function(action, el, pos) {\n";
            echo "              alert(action+' '+$(el).attr('href'));\n";
            echo "          }\n";
            echo "      );\n";
            */
            echo "  });\n";
            echo "</script>";
        }
        
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
     **
    static function getEditorCode( $pcAutoSaveURL = null, $paArchiveList = null, $plReadOnly = false ) {
        if ( (!empty($pcAutoSaveURL)) && (!is_string($pcAutoSaveURL)) )
            wl\main::phperror( "first argument must be a string", E_USER_ERROR );
        if ( (!empty($paArchiveList)) && (!is_array($paArchiveList)) )
            wl\main::phperror( "second argument must be an array", E_USER_ERROR );
        if (!is_bool($plReadOnly))
            wl\main::phperror( "third argument must be a boolean", E_USER_ERROR );
        
        
        $lcArchive = null;
        if (is_array($paArchiveList))
            foreach($paArchiveList as $laItem)
                $lcArchive .= "this.add( '".$laItem["id"]."', '".$laItem["time"]."', '".$laItem["time"]."' );\n"; 
        
        $lcReturn = "
        <script src=\"http://code.jquery.com/jquery-latest.js\" type=\"text/javascript\"></script>
        <script src=\"tools/jquery.easing.1.3.js\" type=\"text/javascript\"></script>
        
        <script src=\"tools/jcontextMenu/jquery.contextMenu.js\" type=\"text/javascript\"></script>
        <link href=\"tools/jcontextMenu/jquery.contextMenu.css\" rel=\"stylesheet\" type=\"text/css\" />
        
        <script src=\"tools/jqueryFileTree/jqueryFileTree.js\" type=\"text/javascript\"></script>
        <link href=\"tools/jqueryFileTree/jqueryFileTree.css\" rel=\"stylesheet\" type=\"text/css\" media=\"screen\" />
        
                <script type=\"text/javascript\" src=\"tools/ckeditor/ckeditor.js\"></script>
                <script type=\"text/javascript\" src=\"/ckeditor/adapters/jquery.js\"></script>
                <script type=\"text/javascript\">$('.ckeditor').ckeditor();</script>
        ";
        /*        <script type=\"text/javascript\">
        
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
        
                        editor.ui.addRichCombo( 'ArchiveList', {
                            label       : 'Archive',
                            multiSelect : false,
        
                            panel : {
                                css : [ CKEDITOR.getUrl( editor.skinPath + 'editor.css' ) ].concat( editor.config.contentsCss )
                            },
        
                            init : function() {
                                this.startGroup( 'Archives' );
                                this.add( '', '---', '---' );
                                ".$lcArchive."    
                                this.setValue( '', '---');
                            },
        
                            onClick : function( value ) {
                                var lo = document.getElementById('restore');
                                if (lo == null)
                                    return;
        
                                lo.value = value;
                            }
                        });
        
                    }
                });
            
                
                CKEDITOR.config.skin                = 'office2003';
                CKEDITOR.config.autoParagraph       = false;
                CKEDITOR.config.extraPlugins        = 'Archive,autosave';
                CKEDITOR.config.autosaveTargetUrl   = '".$pcAutoSaveURL."';
                CKEDITOR.config.autosaveRefreshTime = ".wl\config::autosavetime.";
                
                CKEDITOR.config.toolbar         = 
                [
                    { name: 'document',    items : [ ".($plReadOnly ? null : "'Save',")."'NewPage','DocProps','Print'] },
                    { name: 'clipboard',   items : [ 'Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo' ] },
                    { name: 'editing',     items : [ 'Find','Replace','-','SelectAll','-','SpellChecker', 'Scayt' ] },
                    { name: 'tools',       items : [ 'Autosave','Archive','ArchiveList','-','Maximize','-','About' ] },
                    '/',
                    { name: 'basicstyles', items : [ 'Bold','Italic','Underline','-','RemoveFormat' ] },
                    { name: 'paragraph',   items : [ 'NumberedList','BulletedList','-','Blockquote','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock' ] },
                    { name: 'insert',      items : [ 'Image','Table','PageBreak' ] },
                    { name: 'styles',      items : [ 'Styles','Format','Font','FontSize' ] },
                ];
        
            </script>

        
        if ($plReadOnly)
            $lcReturn .= "<script type=\"text/javascript\">
                            CKEDITOR.on( 'instanceReady', function( poEvent ) {
                                poEvent.editor.setReadOnly( 'none' );
                            });
                        </script>";
        
        return $lcReturn;
    }
    */
    /** main menu
     * @param $poUser user object
     **
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
        
        echo "  <li><a>"._("settings")."</a>\n";
        echo "      <ul>\n";
        echo "          <li><a href=\"wl-password.php\">"._("change password")."</a></li>\n";
        echo "      </ul>\n";
        echo "  </li>\n";
        
        echo "  <li><a href=\"wl-logout.php\">"._("logout").(empty($poUser) ? null : " (".$poUser->getName().")")."</a></li>\n";
        
        echo "  <li><a>"._("help")."</a>\n";
        echo "      <ul>\n";
        echo "          <li><a href=\"\">GUI</a></li>\n";
        echo "          <li><a href=\"\">LaTeX</a></li>\n";
        echo "      </ul>\n";
        echo "  </li>\n";
        
        echo "</ul>\n";
        echo "</div>\n";
    }
    */
}

?>
