(function() {
  // Register buttons
  tinymce.create('tinymce.plugins.MyButtons', {
    init: function( editor, url ) {
      // Add button that inserts shortcode into the current position of the editor
      editor.addButton( 'myoumbrella_button', {
        title: 'Youmbrella Vídeo',
        icon: false,
        onclick: function() {
          // Open a TinyMCE modal
          editor.windowManager.open({
            title: 'Add Youmbrella Protected Vídeo',
            body: [
              {
                type: 'label',
                text: 'Youtube URL ou Vídeo ID'
              },
              {
                type: 'textbox',
                name: 'video',
                size: 60,
              }
            ],
              onsubmit: function( e ) {
                // editor.insertContent( '[buybutton link="' + e.data.link + '" label="' + e.data.label + '" price="' + e.data.price + '"]' );
                if(e.data.video.length < 4){
                  tinymce.activeEditor.windowManager.alert('Informe o URL ou ID do vídeo.');
                  return false;
                };

                YOUTUBE_VIDEO_ID_REGEX = /^(?:https?:\/\/)?(?:www\.|m\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){11})(?:\S+)?$/,
                YOUTUBE_VIDEO_ID_REGEX_GAMING = /^(?:https?:\/\/)?(?:www\.)?(?:gaming.youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){11})(?:\S+)?$/;
                var ytVideoRegex = (e.data.video.indexOf('gaming') !== -1) ? YOUTUBE_VIDEO_ID_REGEX_GAMING : YOUTUBE_VIDEO_ID_REGEX,
                id = e.data.video.match(ytVideoRegex),
                vid = id !== null ? id[1] : e.data.video;

                if(vid.length > 3){

                  $.ajax({
                    type: "POST",
                    url: ajaxurl,
                    data: { action: 'splayoumbrella', video_id: vid, video_input: e.data.video  },
                    success: function(data){
                      editor.insertContent( '[youmbrella]'+data.resp.embedkey+'[/youmbrella]' );
                    },
                    error: function(XMLHttpRequest, textStatus, errorThrown) {
                      tinymce.activeEditor.windowManager.alert(XMLHttpRequest.responseJSON.message);
                      return false;
                    }
                  });
                }else{
                  tinymce.activeEditor.windowManager.alert('Informe o URL ou ID do vídeo.');
                  return false;
                }
                return true;
                // editor.insertContent( '[youmbrella]'+e.data.video+'[youmbrella]' );
              }
            });
          }
        });
      },
      createControl: function( n, cm ) {
        return null;
      }
    });
    // Add buttons
    tinymce.PluginManager.add( 'myoumbrella_button_script', tinymce.plugins.MyButtons );
  })();
