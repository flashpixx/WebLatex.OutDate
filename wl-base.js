// === webLaTeX instance ==================================================================================================================================
if (typeof webLaTeX == "undefined")
    var webLaTeX = (function()
    {
                // singletone instance
                var _instance = null;
                // object for storing runtime data of the page
                var _runtime = {
                    // object for the refresh timer
                    refreshtimer       : null,
                    // extended URL parameter
                    extendedurlparameter : {},
                    // boolean for storing the lock state of the editor
                    islocked           : false
                };
                    
                
                // constructor call
                function init() { return {
                    
                    // configuration come from the PHP scripts, so we set it dynamically (later)
                    // @bug config can be changed everytime, so move it to a private variable and
                    // set it only with a constructor call
                    config : {
                        // name of the PHP session for creating GET URL parameters
                        sessionname     : null,
                        // session ID
                        sessionid       : null,
                        // autosave time for refreshing the locks and save intervalle of the editor
                        autosavetime    : null,
                        // loding message (multilanguage) of the directory calls
                        dirloadmessage  : null,
                    },
                    
                    /** sets the lock state for the editor
                     * @param pl boolean for set lock
                     **/
                    setEditorLock : function(pl) {
                        _runtime.islocked = pl;
                    },
                    
                    /** calls the unlock PHP code for unlock the document and removes the
                     * refresh timer object and the editor instance
                     **/
                    releaseDocument : function() {
                        clearInterval();

                        var loEditor = CKEDITOR.instances["weblatex-editor"];
                        if (loEditor)
                            loEditor.destroy();
                        $.ajax({ url : "wl-unlock.php?"+getURLParameter() }); 
                    },
                    
                    /** returns the URL parameter for the PHP session
                     * @return string with URL parameters
                     **/
                    getSessionURLParameter : function(po) {
                        var lo2 = {}
                        if (typeof po != "undefined")
                            lo2 = po;
                        
                        var lo = {};
                        lo[_instance.config.sessionname] = _instance.config.sessionid;  
                        
                        return $.param($.extend(lo, lo2));
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
                           autosaveRefreshTime : _instance.config.autosavetime,
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
                        clearInterval();
                    
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
                    }, _instance.config.autosavetime );
                }
                
                // returns a URL string with the all needed parameter
                // @return string with parameters
                function getURLParameter() {
                    var lo = {};
                    lo[_instance.config.sessionname] = _instance.config.sessionid;
                    return $.param($.extend(lo, _runtime.extendedurlparameter));
                }
                    
                // returning instance if needed
                if (!_instance)
                    _instance = init();
                return _instance; 
    })()
// ========================================================================================================================================================



// document ready event handler for creating directory and editor calls
$(document).ready( function() {
                  
  $("#weblatex-directory").fileTree(
      {
          script      : "wl-directory.php?"+webLaTeX.getSessionURLParameter(),
          loadMessage : webLaTeX.config.dirloadmessage
      },

      function(pcItem) {
          var laItem           = pcItem.split("$");
          if (laItem.length != 2)
              return;

          var lcURL            = null;
          var loURLParameter   = { id : laItem[1], type : laItem[0] };

          if (laItem[0] == "draft")
              lcURL = "wl-editdraft.php?"+webLaTeX.getSessionURLParameter(loURLParameter);
          if (laItem[0] == "url")
              lcURL = laItem[1];
                                                        
          if (lcURL != null)
              $.get(lcURL, function(pcData) {
                  $("#weblatex-content").fadeOut("slow", function() {
                    webLaTeX.releaseDocument();
                     $("#weblatex-content").html(pcData).fadeIn("slow");
                     webLaTeX.setEditorInstance(loURLParameter);                                                 
                  });
              });
      }
  )
});

// create popup menu for directory and file objects in the directory tree object
//                $('li.directory').contextPopup({
//                                             title: 'My Popup Menu',
//                                           items: [
//                                                   { label:'Some Item',
//                                                   action:function() { alert('clicked 1') } },
//                                                   { label:'Another Thing',
//                                                   action:function() { alert('clicked 2') } },
//                                                   // null can be used to add a separator to the menu items
//                                                   null,
//                                                   { label:'Blah Blah',
//                                                   action:function() { alert('clicked 3') } }
//                                                   ]})
        