// === webLaTeX instance ==================================================================================================================================
// $LastChangedDate$
// $Author$

if (webLaTeX === undefined)
    var webLaTeX = (function(){
                    
        // instance variable
        var _instance;

        // object for storing runtime data of the page
        var _runtime = {
            // object for the refresh timer
            refreshtimer       : null,
            // extended URL parameter
            extendedurlparameter : {},
            // boolean for storing the lock state of the editor
            islocked           : false
        };
                    
        // configuration come from the PHP scripts, so we set it dynamically on the first getInstance call
        var _config = {
            // name of the PHP session for creating GET URL parameters
            sessionname     : null,
            // session ID
            sessionid       : null,
            // autosave time for refreshing the locks and save intervalle of the editor
            autosavetime    : null,
            // loding message (multilanguage) of the directory calls
            dirloadmessage  : null
        };
        
                    
                    
        // internal function for the object representation
        function webLaTeX() { return {
                    
            // create an own object for dialog function
            dialogs : {
                
                /** function for creating the directory create dialog
                 * @param pcParent parent directory
                 **/
                createDirectory : function(pcParent) {
                    if (!pcParent || 0 === pcParent.length)
                        throw "parent directory is empty or not set";
                    if (pcParent[pcParent.length-1] != "/")
                        throw "path must be end with a slash";
                    
                    // create the input datatypes
                    $("#weblatex-dialog").html("<p id=\"weblatex-dialog-message\">add a directory name</p><form><fieldset><label for=\"pathname\">directory name: "+pcParent+"</label> <input type=\"text\" name=\"pathname\" id=\"pathname\" class=\"text ui-widget-content ui-corner-all\" /></fieldset></form>");
                    
                    // create the dialog gui (the close call must be with the id name, because if the focuse is lost, the $(this) option can not close the dialog anymore)
                    $("#weblatex-dialog").dialog({
                        height     : 225,
                        width      : 400,
                        title      : "directory creating",
                        modal      : true,
                        resizable  : false,
                        buttons    : {
                            "create" : function() {
                                $.ajax({ url     : "wl-directorycreate.php?"+_instance.getSessionURLParameter({path : pcParent+$("#pathname").val()}),
                                         success : function(pcResponse) {	
                                            lcMsg = $(pcResponse).find("error");
                                            if (lcMsg.size() != 0)
                                                $("#weblatex-dialog-message").text(lcMsg.text()).addClass( "ui-state-highlight" );
                                            else
                                                $("#weblatex-dialog").dialog("close");
                                         }
                                });
                            },
                            Cancel   : function() { $("#weblatex-dialog").dialog("close"); }
                        },
                        close: function() { $("#weblatex-dialog").children().remove(); }
                    });
                },
                    
                
                /** deletes a directory
                 * @param pcDirectory FQN path
                 **/
                deleteDirectory : function(pcDirectory) {
                    if (!pcDirectory || 0 === pcDirectory.length)
                        throw "parent directory is empty or not set";
                    if (pcDirectory[pcDirectory.length-1] != "/")
                        throw "path must be end with a slash";
                    
                    $.ajax({ url     : "wl-directorydelete.php?"+_instance.getSessionURLParameter({path : pcDirectory}),
                             success : function(pcResponse) {	
                                lcMsg = $(pcResponse).find("error");
                                if (lcMsg.size() != 0) {
                          
                                    $("#weblatex-dialog").html("<p class=\"ui-state-highlight\">"+lcMsg.text()+"</p>");
                                    $("#weblatex-dialog").dialog({
                                         height     : 150,
                                         width      : 400,
                                         title      : "directory error",
                                         modal      : true,
                                         resizable  : false,
                                         close: function() { $("#weblatex-dialog").children().remove(); }
                                    });  
                                }
                             }
                    });
                }

                    
            },
                    
                    
            /** sets the lock state for the editor
             * @param pl boolean for set lock
             **/
            setEditorLock : function(pl) {
                _runtime.islocked = pl;
            },
                    
            /** returns the URL parameter for the PHP session
             * @return string with URL parameters
             **/
             getSessionURLParameter : function(po) {
                var lo2 = {}
                if (typeof po != "undefined")
                    lo2 = po;
                    
                var lo = {};
                lo[_config.sessionname] = _config.sessionid;  
                    
                return $.param($.extend(lo, lo2));
            },
                
            /** returns the loading message
             * @return string
             **/
            getDirLoadMsg : function() {
                return _config.dirloadmessage;
            },
                    
            /** calls the unlock PHP code for unlock the document and removes the
             * refresh timer object and the editor instance
             **/
            releaseDocument : function() {
                clearInterval(_instance.refreshtimer);
                    
                var loEditor = CKEDITOR.instances["weblatex-editor"];
                if (loEditor)
                    loEditor.destroy();
                $.ajax({ url : "wl-unlock.php?"+getURLParameter() }); 
            },
                    

            /** creates the CKEditor instance
             * @param po object with parameters
             **/
            setEditorInstance : function(po) {
                if ($("#weblatex-editor").size() == 0)
                    return;
                    
                _runtime.extendedurlparameter = po;
                setDocumentTimer();
                    
                $("#weblatex-editor").ckeditor({
                       skin                : "office2003", 
                       readOnly            : _runtime.islocked,
                       autoParagraph       : false,
                       ignoreEmptyParagraph: true,
                       extraPlugins        : "autosave",
                       height              : $("#weblatex-content").height()*0.8 | 0,
                       autosaveTargetUrl   : "wl-autosave.php?"+getURLParameter(),
                       autosaveRefreshTime : _config.autosavetime,
                       toolbar             : [
                                              { name: "document",    items : [ "NewPage","Autosave","DocProps","Print"] },
                                              { name: "clipboard",   items : [ "Cut","Copy","Paste","PasteText","PasteFromWord","-","Undo","Redo" ] },
                                              { name: "editing",     items : [ "Find","Replace","-","SelectAll","-","SpellChecker", "Scayt" ] },
                                              { name: "tools",       items : [ "Maximize","-","About" ] },
                                              "/",
                                              { name: "basicstyles", items : [ "Bold","Italic","Underline","-","RemoveFormat" ] },
                                              { name: "paragraph",   items : [ "NumberedList","BulletedList","-","Blockquote","-","JustifyLeft","JustifyCenter","JustifyRight","JustifyBlock" ] },
                                              { name: "insert",      items : [ "Image","Table","PageBreak" ] },
                                              { name: "styles",      items : [ "Styles","Format","Font","FontSize" ] },
                                             ]
                });
            }
                    
        }}
                    
                    
        // creates the document timer, that refreshes a lock or updates the editor
        // if the document release a lock
        function setDocumentTimer() {
            if (_runtime.refreshtimer != null)
                clearInterval(_runtime.refreshtimer);
        
            _runtime.refreshtimer = setInterval( function() {
                        $.ajax({ url     : "wl-lock.php?"+getURLParameter() });
                                            
                        $.ajax({ url     : "wl-haslock.php?"+getURLParameter(), 
                               success : function(pcResponse) {
                                    var llReadOnly = $(pcResponse).find("user").size() != 0;
                                    $("#weblatex-editor").ckeditorGet().setReadOnly( llReadOnly );
                                    if (!llReadOnly)
                                        $("#weblatex-message").remove();
                                }
                        });
            }, _config.autosavetime );
        }
                    
        // returns a URL string with the all needed parameter
        // @return string with parameters
        function getURLParameter() {
            var lo = {};
            lo[_config.sessionname] = _config.sessionid;
            return $.param($.extend(lo, _runtime.extendedurlparameter));
        }

                    
        // create the static function getInstance()
        return {
            getInstance: function(pcSessionName, pcSessionID, pnAutosavetime, pcDirloadMsg){
                    if (_instance === undefined) {
                        _instance = new webLaTeX();
                    
                        if ((pcSessionName === undefined) || (pcSessionID === undefined) || (pnAutosavetime === undefined) || (pcDirloadMsg === undefined))
                            throw "webLaTeX parameter undefined";
                    
                        _config.sessionname     = pcSessionName;
                        _config.sessionid       = pcSessionID;
                        _config.autosavetime    = pnAutosavetime;
                        _config.dirloadmessage  = pcDirloadMsg;
                    }
                    return _instance;
            }
        };

    })();
// ========================================================================================================================================================



// document ready event handler for creating directory and editor calls
$(document).ready( function() {
                  
      // create the call for the directory structure
      $("#weblatex-directory").fileTree(
            {
                script      : "wl-directory.php?"+webLaTeX.getInstance().getSessionURLParameter(),
                loadMessage : webLaTeX.getInstance().getDirLoadMsg()
            },
                    
            function(pcItem) {
                var laItem           = pcItem.split("$");
                if (laItem.length != 2)
                    return;
                    
                var lcURL            = null;
                var loURLParameter   = { id : laItem[1], type : laItem[0] };
                    
                if (laItem[0] == "draft")
                    lcURL = "wl-editdraft.php?"+webLaTeX.getInstance().getSessionURLParameter(loURLParameter);
                if (laItem[0] == "url")
                    lcURL = laItem[1];
                    
                if (lcURL != null)
                    $.get(lcURL, function(pcData) {
                          $("#weblatex-content").fadeOut("slow", function() {
                                webLaTeX.getInstance().releaseDocument();
                                $("#weblatex-content").html(pcData).fadeIn("slow");
                                webLaTeX.getInstance().setEditorInstance(loURLParameter);                                                 
                          });
                    });
            }
    );
    
                  
    // add a simple context menu to the div tag to add directories on the root node
    $("#weblatex-menu").contextPopup({
        title : "Root Directory",
        items : [
                 { label  : "create directory",
                   action : function() { webLaTeX.getInstance().dialogs.createDirectory("/"); }
                 },
                 
                 { label  : "create draft",
                   action : function() {}
                 },
                 
                 { label  : "create document",
                   action : function() {}
                 }
                ]
    });
                  
    // add to all directory items the context menu
    $("#weblatex-directory ul.jqueryFileTree li.dircontext").livequery(function(){ 
                                                
        $(this).contextPopup({
            title : "Directory",
            items : [
                      { label  : "edit",
                        action : function() {}
                      },
                                                                                                    
                      { label  : "delete",
                        action : function(po) {
                            var lo = po.srcElement.childNodes;
                            if (lo.length != 1)
                                throw "node elements must be equal to one";
                     
                            lo = lo[0].parentNode.attributes;
                            if (lo.length != 2)
                                throw "attribute elements must be equal to two";
                     
                            webLaTeX.getInstance().dialogs.deleteDirectory(lo[1].value);
                        }
                      },
                                                                                                    
                      null,
                                                                                                    
                      { label  : "create directory",
                        action : function(po) {
                            var lo = po.srcElement.childNodes;
                            if (lo.length != 1)
                                throw "node elements must be equal to one";
                     
                            lo = lo[0].parentNode.attributes;
                            if (lo.length != 2)
                                throw "attribute elements must be equal to two";

                            webLaTeX.getInstance().dialogs.createDirectory(lo[1].value);
                     
                        }
                      },
                     
                      { label  : "create draft",
                        action : function() {}
                      },
                     
                      { label  : "create document",
                        action : function() {}
                      }
                    ]
        });
                                                
    });
    
    // add the context menu to all draft objects
    $("#weblatex-directory ul.jqueryFileTree li.draft").livequery(function(){ 
                                                                                    
        $(this).contextPopup({
            title : "Draft",
            items : [
                     { label  : "delete",
                       action : function() {}
                     },
                     
                     null,
                     
                     { label  : "is used by",
                       action : function() {}
                     },                     
                    ]
                                                                                
        });
                                                                               
    });
                  
    // add the context menu to all document objects
    $("#weblatex-directory ul.jqueryFileTree li.document").livequery(function(){ 
                                                                
        $(this).contextPopup({
            title : "Document",
            items : [
                     { label  : "delete",
                       action : function() {}
                     },
                                                                                              
                     null,
                                                                                              
                     { label  : "generate PDF",
                       action : function() {}
                     },                     
                    ]
                                                                                     
        });
                                                                
    });
                  
});



