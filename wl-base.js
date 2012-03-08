// global variable for storing runtime options
var goRuntime = {
    
    // object for the refresh timer
    refreshtimer       : null,
    // parameter of the loaded url
    loadedurlparameter : null,
    // boolean for storing the lock state of the editor
    islocked           : false
    
};


// calls the unlock PHP code for unlock the document and removes the
// refresh timer object
function releaseDocument() {
    clearInterval(goRuntime.refreshtimer);
    if (goRuntime.loadedurlparameter != null)
        $.ajax( { url : "wl-unlock.php?"+$.param(goRuntime.loadedurlparameter) } ); 
}

// creates the document timer, that refreshes a lock or updates the editor
// if the document release a lock
function documentTimer(poURLParameter) {
    if (goRuntime.refreshtimer != null)
        clearInterval(goRuntime.refreshtimer);
    
    goRuntime.refreshtimer = setInterval( function()
        { $.ajax( {
              url     : "wl-lock.php?"+$.param(goRuntime.loadedurlparameter)
        } );
          
        $.ajax({ 
              url     : "wl-haslock.php?"+$.param(goRuntime.loadedurlparameter),
              success : function(pcResponse) {
                  var llReadOnly = $(pcResponse).find("user").size() != 0;
                  $("#weblatex-editor").ckeditorGet().setReadOnly( llReadOnly );
                  if (!llReadOnly)
                      $("#weblatex-message").remove();
              }
          });
      },
      goConfig.autosavetime
    );
}

// creates the CKEditor instance
// @param poURLParameter url parameter for setting the autosave plugin
function setEditorInstance(poURLParameter) {
  if ($("#weblatex-editor").size() == 0)
      return;

  documentTimer(poURLParameter);

  $("#weblatex-editor").ckeditor({
      skin                : "office2003", 
      readOnly            : goRuntime.islocked,
      autoParagraph       : false,
      ignoreEmptyParagraph: true,
      extraPlugins        : "autosave",
      height              : $("#weblatex-content").height()*0.8 | 0,
      autosaveTargetUrl   : "wl-autosave.php?"+$.param(poURLParameter),
      autosaveRefreshTime : goConfig.autosavetime,
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



// document ready event handler
$(document).ready( function() {
                  
  $("#weblatex-directory").fileTree(
      {
          script      : "wl-directory.php?"+goConfig.sessionparam,
          loadMessage : goConfig.dirloadmessage
      },

      function(pcItem) {
          var laItem           = pcItem.split("$");
          if (laItem.length != 2)
              return;

          var lcURL            = null;
          var loURLParameter   = { id : laItem[1], type : laItem[0] };
          loURLParameter[goConfig.sessionname] = goConfig.sessionid;

          if (laItem[0] == "draft")
              lcURL = "wl-editdraft.php?"+$.param(loURLParameter);
          if (laItem[0] == "url")
              lcURL = laItem[1];
                                                        
          if (lcURL != null)
              $.get(lcURL, function(pcData) {
                  $("#weblatex-content").fadeOut("slow", function() {
                      var loEditor = CKEDITOR.instances["weblatex-editor"];

                      if (loEditor) {
                          loEditor.destroy();
                          releaseDocument();
                      }

                      $("#weblatex-content").html(pcData).fadeIn("slow");
                      setEditorInstance(loURLParameter)
                      goRuntime.loadedurlparameter = loURLParameter;  
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
        