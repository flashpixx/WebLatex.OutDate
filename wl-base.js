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
            // strings for getting the translation that is generate with PHP
            translation     : {}
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
                    $("#weblatex-dialog").html("<p id=\"weblatex-dialog-message\">"+_config.translation.directoryadd+"</p><form><fieldset><label for=\"pathname\">directory name: "+pcParent+"</label> <input type=\"text\" name=\"pathname\" id=\"pathname\" class=\"text ui-widget-content ui-corner-all\" /></fieldset></form>");
                    
                    // build the buttons option first, because we will use translation names
                    var loButtons                           = {  Cancel   : function() { $("#weblatex-dialog").dialog("close"); }  }
                    loButtons[_config.translation.create]   = function() {
                        $.ajax({ url     : "wl-directorycreate.php?"+_instance.getSessionURLParameter({path : pcParent+$("#pathname").val()}),
                                 success : function(pcResponse) {	
                                    lcMsg = $(pcResponse).find("error");
                                    if (lcMsg.size() != 0)
                                        $("#weblatex-dialog-message").text(lcMsg.text()).addClass( "ui-state-highlight" );
                                    else {
                                        $("#weblatex-dialog").dialog("close");
                               
                                        // we get the parent node, check if it is opend, and reopen the node, because the new directory is shown directly
                                        // otherwise we do nothing. We read the a tag, because the rel attribute stores the parent, if it exists the node
                                        // is opend
                                        var loNode = $("a[rel='"+pcParent+"']");
                                        if (loNode.size() != 0) {
                                            if (loNode.parent().hasClass("expanded")) {
                                                loNode.trigger("click");
                                                loNode.trigger("click");
                                            }
                                         } else
                                            console.log("refresh on root does not work");
                                    }
                                 }
                        });
                    };
                    
                    
                    // create the dialog gui (the close call must be with the id name, because if the focuse is lost, the $(this) option can not close the dialog anymore)
                    $("#weblatex-dialog").dialog({
                        height     : 225,
                        width      : 400,
                        title      : _config.translation.directorycreate,
                        modal      : true,
                        resizable  : false,
                        buttons    : loButtons,
                        close      : function() { $("#weblatex-dialog").children().remove(); }
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
                                         title      : _config.translation.directoryerror,
                                         modal      : true,
                                         resizable  : false,
                                         close: function() { $("#weblatex-dialog").children().remove(); }
                                    });  
                                }
                             }
                    });
                },
                    
                /** creates a new draft
                 * @param path
                 **/
                createDraft : function(pcPath) {
                    if (!pcPath || 0 === pcPath.length)
                        throw "parent directory is empty or not set";
                    if (pcPath[pcPath.length-1] != "/")
                        throw "path must be end with a slash";
                    
                    // create the input datatypes
                    $("#weblatex-dialog").html("<p id=\"weblatex-dialog-message\">"+_config.translation.draftadd+"</p><form><fieldset><label for=\"draftname\">draft name: "+pcPath+"</label> <input type=\"text\" name=\"draftname\" id=\"draftname\" class=\"text ui-widget-content ui-corner-all\" /></fieldset></form>");
                    
                    // build the buttons option first, because we will use translation names
                    var loButtons                           = {  Cancel   : function() { $("#weblatex-dialog").dialog("close"); }  }
                    loButtons[_config.translation.create]   = function() {
                        $.ajax({ url     : "wl-draftcreate.php?"+_instance.getSessionURLParameter({path : pcPath, name : $("#draftname").val()}),
                                 success : function(pcResponse) {	
                                    lcMsg = $(pcResponse).find("error");
                                    if (lcMsg.size() != 0)
                                        $("#weblatex-dialog-message").text(lcMsg.text()).addClass( "ui-state-highlight" );
                                    else {
                                        $("#weblatex-dialog").dialog("close");
                               
                                        // we get the parent node, check if it is opend, and reopen the node, because the new directory is shown directly
                                        // otherwise we do nothing. We read the a tag, because the rel attribute stores the parent, if it exists the node
                                        // is opend
                                        var loNode = $("a[rel='"+pcPath+"']");
                                        if (loNode.size() != 0) {
                                            if (loNode.parent().hasClass("expanded")) {
                                                loNode.trigger("click");
                                                loNode.trigger("click");
                                            }
                                        } else
                                            console.log("refresh on root does not work");
                                    }
                                 }
                        });
                    };
                    
                    
                    // create the dialog gui (the close call must be with the id name, because if the focuse is lost, the $(this) option can not close the dialog anymore)
                    $("#weblatex-dialog").dialog({
                        height     : 225,
                        width      : 400,
                        title      : _config.translation.draftcreate,
                        modal      : true,
                        resizable  : false,
                        buttons    : loButtons,
                        close      : function() { $("#weblatex-dialog").children().remove(); }
                    });
                },
                    
                /** create a new document 
                 * @param pcPath path
                 **/
                createDocument : function(pcPath) {
                    if (!pcPath || 0 === pcPath.length)
                        throw "parent directory is empty or not set";
                    if (pcPath[pcPath.length-1] != "/")
                        throw "path must be end with a slash";
                    
                    // create the input datatypes
                    $("#weblatex-dialog").html("<p id=\"weblatex-dialog-message\">"+_config.translation.documentadd+"</p><form><fieldset><label for=\"documentname\">document name: "+pcPath+"</label> <input type=\"text\" name=\"documentname\" id=\"documentname\" class=\"text ui-widget-content ui-corner-all\" /></fieldset></form>");
                    
                    var loButtons                           = {  Cancel   : function() { $("#weblatex-dialog").dialog("close"); }  }
                    loButtons[_config.translation.create]   = function() {};
                    
                    // create the dialog gui (the close call must be with the id name, because if the focuse is lost, the $(this) option can not close the dialog anymore)
                    $("#weblatex-dialog").dialog({
                        height     : 225,
                        width      : 400,
                        title      : _config.translation.documentcreate,
                        modal      : true,
                        resizable  : false,
                        buttons    : loButtons,
                        close      : function() { $("#weblatex-dialog").children().remove(); }
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
                
            /** returns the translation object
             * @return object
             **/
            getTranslation : function() {
                return _config.translation;
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

                    
        /* create the static function getInstance()
         * @param pcSessionName name of the session parameter
         * @param pcSessionID session id
         * @param pnAutosavetime refresh time for autosaving and refresh locking
         * @param poTranslation object with translation strings
         **/
        return {
            getInstance: function(pcSessionName, pcSessionID, pnAutosavetime, poTranslation){
                    if (_instance === undefined) {
                        if ((pcSessionName === undefined) || (pcSessionID === undefined) || (pnAutosavetime === undefined) || (poTranslation === undefined))
                            throw "webLaTeX parameter undefined";
                    
                        _instance               = new webLaTeX();
                        _config.sessionname     = pcSessionName;
                        _config.sessionid       = pcSessionID;
                        _config.autosavetime    = pnAutosavetime;
                        _config.translation     = poTranslation;
                    
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
                loadMessage : webLaTeX.getInstance().getTranslation().directoryload
            },
                    
            function(pcItem) {
                var laItem           = pcItem.split("$");
                if (laItem.length != 2)
                    throw "seperater can not be found correctly"
                    
                var lcURL            = null;
                var loURLParameter   = { id : laItem[1], type : laItem[0] };
                    
                if (laItem[0] == "draft")
                    lcURL = "wl-draftedit.php?"+webLaTeX.getInstance().getSessionURLParameter(loURLParameter);
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
        title : webLaTeX.getInstance().getTranslation().directory,
        items : [
                 { label  : webLaTeX.getInstance().getTranslation().labelcreatedir,
                   action : function() { webLaTeX.getInstance().dialogs.createDirectory("/"); }
                 },
                 
                 null,
                 
                 { label  : webLaTeX.getInstance().getTranslation().labelcreatedraft,
                    action : function() { webLaTeX.getInstance().dialogs.createDraft("/"); }
                 },
                 
                 { label  : webLaTeX.getInstance().getTranslation().labelcreatedoc,
                   action : function() { webLaTeX.getInstance().dialogs.createDocument("/"); }
                 },
                 
                 null,
                 
                 { label  : webLaTeX.getInstance().getTranslation().labelcreateright,
                   action : function() {}
                 },
                 
                 { label  : webLaTeX.getInstance().getTranslation().labelcreategroup,
                   action : function() {}
                 },
                ]
    });
                  
    // add to all directory items the context menu
    $("#weblatex-directory ul.jqueryFileTree li.dircontextmenu").livequery(function(){ 
        //$(this).draggable({ opacity : 0.7, helper : "clone" });                                        
        $(this).contextPopup({
            title : webLaTeX.getInstance().getTranslation().directory,
            items : [
                      { label  : webLaTeX.getInstance().getTranslation().edit,
                        action : function() {}
                      },
                                                                                                    
                      { label  : webLaTeX.getInstance().getTranslation().del,
                        action : function(po) {
                     
                            //@bug srcElement does not exists in Firefox
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
                                                                                                    
                      { label  : webLaTeX.getInstance().getTranslation().labelcreatedir,
                        action : function(po) {
                     
                            //@bug srcElement does not exists in Firefox
                            var lo = po.srcElement.childNodes;
                            if (lo.length != 1)
                                throw "node elements must be equal to one";
                     
                            lo = lo[0].parentNode.attributes;
                            if (lo.length != 2)
                                throw "attribute elements must be equal to two";

                            webLaTeX.getInstance().dialogs.createDirectory(lo[1].value);
                     
                        }
                      },
                     
                      { label  : webLaTeX.getInstance().getTranslation().labelcreatedraft,
                        action : function(po) {
                     
                            //@bug srcElement does not exists in Firefox
                            var lo = po.srcElement.childNodes;
                            if (lo.length != 1)
                                throw "node elements must be equal to one";
                     
                            lo = lo[0].parentNode.attributes;
                            if (lo.length != 2)
                                throw "attribute elements must be equal to two";
                     
                            webLaTeX.getInstance().dialogs.createDraft(lo[1].value);
                     
                        }
                      },
                     
                      { label  : webLaTeX.getInstance().getTranslation().labelcreatedoc,
                        action : function(po) {
                            
                            //@bug srcElement does not exists in Firefox
                            var lo = po.srcElement.childNodes;
                            if (lo.length != 1)
                                throw "node elements must be equal to one";
                             
                             lo = lo[0].parentNode.attributes;
                             if (lo.length != 2)
                                throw "attribute elements must be equal to two";
                             
                             webLaTeX.getInstance().dialogs.createDocument(lo[1].value);
                      
                        }
                      }
                    ]
        });
                                                
    });
    
    // add the context menu to all draft objects
    $("#weblatex-directory ul.jqueryFileTree li.draft").livequery(function(){ 
        //$(this).draggable({ opacity : 0.7, helper : "clone" });
        $(this).contextPopup({
            title : webLaTeX.getInstance().getTranslation().draft,
            items : [
                     { label  : webLaTeX.getInstance().getTranslation().del,
                       action : function(po) {
                     
                            //@bug srcElement does not exists in Firefox
                            var lo = po.srcElement.childNodes;
                            if (lo.length != 1)
                                throw "node elements must be equal to one";
                     
                            lo = lo[0].parentNode.attributes;
                            if (lo.length != 2)
                                throw "attribute elements must be equal to two";
                     
                            var laItem           = lo[1].value.split("$");
                            if (laItem.length != 2)
                                throw "seperater can not be found correctly"
                     
                            $.ajax({ url     : "wl-draftdelete.php?"+webLaTeX.getInstance().getSessionURLParameter({ id : laItem[1] }),
                                     success : function(pcResponse) {	
                     
                                        lcMsg = $(pcResponse).find("error");
                                        if (lcMsg.size() != 0) {
                            
                                            $("#weblatex-dialog").html("<p class=\"ui-state-highlight\">"+lcMsg.text()+"</p>");
                                            $("#weblatex-dialog").dialog({
                                                height     : 150,
                                                width      : 400,
                                                title      : _config.translation.drafterror,
                                                modal      : true,
                                                resizable  : false,
                                                close      : function() { $("#weblatex-dialog").children().remove(); }
                                            });  
                                        }
                                     }
                            });
                     
                       }
                     },
                     
                     null,
                     
                     { label  : webLaTeX.getInstance().getTranslation().labelisusedby,
                       action : function() {}
                     },                     
                    ]
                                                                                
        });
                                                                               
    });
                  
    // add the context menu to all document objects
    $("#weblatex-directory ul.jqueryFileTree li.document").livequery(function(){ 
        //$(this).draggable({ opacity : 0.7, helper : "clone" });                                                        
        $(this).contextPopup({
            title : webLaTeX.getInstance().getTranslation().document,
            items : [
                     { label  : webLaTeX.getInstance().getTranslation().del,
                       action : function() {}
                     },
                                                                                              
                     null,
                                                                                              
                     { label  : webLaTeX.getInstance().getTranslation().labelgeneratepdf,
                       action : function(po) {
                     
                            //@bug srcElement does not exists in Firefox
                            var lo = po.srcElement.childNodes;
                            if (lo.length != 1)
                                throw "node elements must be equal to one";
                     
                            lo = lo[0].parentNode.attributes;
                            if (lo.length != 2)
                                throw "attribute elements must be equal to two";
                     
                            var laItem           = lo[1].value.split("$");
                            if (laItem.length != 2)
                                throw "seperater can not be found correctly"
                            
                            $.ajax({ url     : "wl-documentpdf.php?"+webLaTeX.getInstance().getSessionURLParameter({ id : laItem[1], build : null }), async : false,
                                     success : function(pcResponse) {	
                                   
                                        lcMsg = $(pcResponse).find("error");
                                        if (lcMsg.size() != 0) {
                                            $("#weblatex-dialog").html("<p class=\"ui-state-highlight\">"+lcMsg.text()+"</p>");
                                            $("#weblatex-dialog").dialog({
                                                height     : 150,
                                                width      : 400,
                                                title      : webLaTeX.getInstance().getTranslation().pdfbuilderror,
                                                modal      : true,
                                                resizable  : false,
                                                close      : function() { $("#weblatex-dialog").children().remove(); }
                                            });  
                                        } else
                                            window.open("wl-documentpdf.php?"+webLaTeX.getInstance().getSessionURLParameter({ id : laItem[1] }), "weblatex"+laItem[1]);
                                   }
                            });  
                       }
                     },                     
                    ]
                                                                                     
        });
                                                                
    });
                  
});



