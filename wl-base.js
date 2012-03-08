var goRefreshLock        = null;
var goLoadedURLParameter = null;
var glisLocked           = false;

function releaseDocument() {
    clearInterval(goRefreshLock);
    if (goLoadedURLParameter != null)
        $.ajax( { url : "wl-unlock.php?"+$.param(goLoadedURLParameter) } ); 
}

function documentTimer(poURLParameter) {
    if (goRefreshLock != null)
        clearInterval(goRefreshLock);
    
    goRefreshLock = setInterval( function()
        { $.ajax( {
              url     : "wl-lock.php?"+$.param(poURLParameter)
        } );
          
        $.ajax({ 
              url     : "wl-haslock.php?"+$.param(poURLParameter),
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

function setEditorInstance(poURLParameter) {
  if ($("#weblatex-editor").size() == 0)
      return;

  documentTimer(poURLParameter);
  
  $("#weblatex-editor").ckeditor({
      skin                : "office2003", 
      readOnly            : glisLocked,
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
                      goLoadedURLParameter = loURLParameter;  
                  });
              });
      }
  );
});
