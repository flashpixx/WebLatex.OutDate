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
            echo "  var goRefreshLock        = null;\n";
            echo "  var goLoadedURLParameter = null;\n";
            echo "\n";
            echo "  $(document).ready( function() {\n";
            echo "      $('#weblatex-directory').fileTree(\n";
            echo "          {\n";
            echo "              script      : 'wl-directory.php?".http_build_query(array("sess" => session_id()))."',\n";
            echo "              loadMessage : '"._("loading")."'\n";
            echo "          },\n";
            echo "\n";
            echo "          function(pcItem) {\n";
            echo "              var laItem           = pcItem.split('$');\n";
            echo "              if (laItem.length != 2)\n";
            echo "                  return;\n";
            echo "\n";
            echo "              var lcURL            = null;\n";
            echo "              var loURLParameter   = { sess : '".session_id()."', id : laItem[1], type : laItem[0] };\n";
            echo "\n";
            echo "              if (laItem[0] == 'draft')\n";
            echo "                  lcURL = 'wl-editdraft.php?'+$.param(loURLParameter);\n";
            echo "              if (laItem[0] == 'url')\n";
            echo "                  lcURL = laItem[1];\n";
            echo "\n";
            echo "              if (lcURL != null)\n";
            echo "                  $.get(lcURL, function(pcData) {\n";
            echo "                      $('#weblatex-content').fadeOut('slow', function() {\n";
            echo "                          var loEditor = CKEDITOR.instances['weblatex-editor'];\n";
            echo "\n";
            echo "                          if (loEditor) {\n";
            echo "                              loEditor.destroy();\n";
            echo "                              clearInterval(goRefreshLock);\n";
            echo "                              if (goLoadedURLParameter != null)\n";
            echo "                                  $.ajax( { url : 'wl-unlock.php?'+$.param(goLoadedURLParameter) } );\n"; 
            echo "                          }\n";
            echo "\n";
            echo "                          goLoadedURLParameter = loURLParameter;\n";
            echo "                          goRefreshLock = setInterval( function() { $.ajax( { url : 'wl-refreshlock.php?'+$.param(loURLParameter) } ); }, ".(wl\config::autosavetime*1000).");\n";
            echo "                          $('#weblatex-content').html(pcData).fadeIn('slow');\n";
            echo "\n";
            echo "                          $('#weblatex-editor').ckeditor({\n";
            echo "                              skin                : 'office2003',\n";
            echo "                              autoParagraph       : false,\n";
            echo "                              extraPlugins        : 'autosave',\n";
            echo "                              autosaveTargetUrl   : 'wl-autosave.php?'+$.param(loURLParameter),\n";
            echo "                              autosaveRefreshTime : ".wl\config::autosavetime.",\n";
            echo "                              toolbar             : [\n";
            echo "                                  { name: 'document',    items : [ 'NewPage','Autosave','DocProps','Print'] },\n";
            echo "                                  { name: 'clipboard',   items : [ 'Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo' ] },\n";
            echo "                                  { name: 'editing',     items : [ 'Find','Replace','-','SelectAll','-','SpellChecker', 'Scayt' ] },\n";
            echo "                                  { name: 'tools',       items : [ 'Maximize','-','About' ] },\n";
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

}

?>
