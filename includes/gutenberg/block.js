blockStyle = { backgroundColor: '#900', color: '#fff', padding: '20px' };

var el = wp.element.createElement,
registerBlockType = wp.blocks.registerBlockType,
Button = wp.components.Button,
TextControl = wp.components.TextControl;
__ = wp.i18n.__;

registerBlockType( 'gutenberg-rcsplay/rcsplay-block', {

  title: 'Youmbrella',

  icon: {
    background: '#fff',
    foreground: '#e55c3e',
    src: 'video-alt3'
  },

  category: 'common',

  keywords: [
    __('youmbrella'),
    __('youtube'),
    __('embed')
  ],

  attributes: {
    video_input: {
      type: 'string',
    },
    video_vid: {
      type: 'string',
    },
    error_msg: {
      type: 'string',
    },
    error: {
      type: 'boolean',
    },
    success: {
      type: 'boolean',
    },
    processing: {
      type: 'boolean',
    },
    splay_url: {
      type: 'string',
      // source: 'attribute',
      // attribute: 'src',
      // selector: 'iframe'
    }
  },

  edit: function( props ) {
    var attributes = props.attributes;
    var className = props.className;

    var video_input = attributes.video_input; // To bind attribute link_text

    var processing = attributes.processing;
    var error = attributes.error;
    var error_msg = attributes.error_msg;
    var success = attributes.success;

    var splay_url = attributes.splay_url;
    var video_vid = attributes.video_id;

    return wp.element.createElement(
      'div',
      {id: 'rcsplay-block-editable-box' },
      ' ',
      wp.element.createElement(
        'div',
        {
          id:'rcsplay-block-editable-box-form',
          className: success ? "hidden" : ""
        },
        ' ',
        wp.element.createElement(
          'div',
          {
            className: 'edit-logo-top'
          },
          ''
        ),
        // wp.element.createElement(
        //   'p',
        //   null,
        //   'Youmbrella'
        // ),
        wp.element.createElement( TextControl, {
          type: 'text',
          value: props.attributes.video_input,
          placeholder: 'Youtube URL or Vídeo ID',
          disabled: processing ? true : false,
          onChange: function( video_input ) {
            props.setAttributes( { video_input: video_input } );
          }
        }),
        wp.element.createElement(
          'p',
          {
            className: error ? "rcsplay-error" : "hidden",
          },
          props.attributes.error_msg
        ),
        wp.element.createElement(
          'div',
          {
            className: processing ? "rcsplay-edit-loading" : "rcsplay-edit-loading hidden",
          },
          ''
        ),
        wp.element.createElement(Button, {
          id: 'btn-splay-gt',
          isLarge: true,
          isPrimary: true,
          isBusy: processing,
          className: processing ? "hidden" : "",
          onClick: async function( video_input) {
            if(!props.attributes.video_input){
              props.setAttributes( { error: true } );
              props.setAttributes( { error_msg: 'Informe o URL ou ID do vídeo.' } );
              return;
            }
            if(props.attributes.video_input.length < 3){
              props.setAttributes( { error: true } );
              props.setAttributes( { error_msg: 'Informe o URL ou ID do vídeo.' } );
              return;
            }
            props.setAttributes( { processing: true } );
            YOUTUBE_VIDEO_ID_REGEX = /^(?:https?:\/\/)?(?:www\.|m\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){11})(?:\S+)?$/,
            YOUTUBE_VIDEO_ID_REGEX_GAMING = /^(?:https?:\/\/)?(?:www\.)?(?:gaming.youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){11})(?:\S+)?$/;
            var ytVideoRegex = (props.attributes.video_input.indexOf('gaming') !== -1) ? YOUTUBE_VIDEO_ID_REGEX_GAMING : YOUTUBE_VIDEO_ID_REGEX,
            id = props.attributes.video_input.match(ytVideoRegex),
            vid = id !== null ? id[1] : props.attributes.video_input;

            props.setAttributes( { video_id: vid } );

            $.ajax({
              type: "POST",
              url: ajaxurl,
              data: { action: 'splayoumbrella', video_id: vid, video_input: props.attributes.video_input },
              success: function(data){
                props.setAttributes( { splay_url: 'https://youmbrella.com/embed/'.concat(data.resp.embedkey) } );
                  props.setAttributes( { success: true } );
                  props.setAttributes( { error: false } );
                  props.setAttributes( { error_msg: '' } );
                  props.setAttributes( { processing: false } );
              },
              error: function(XMLHttpRequest, textStatus, errorThrown) {
                props.setAttributes( { error: true } );
                props.setAttributes( { error_msg: XMLHttpRequest.responseJSON.message ? XMLHttpRequest.responseJSON.message : 'Não foi possível incorporar o vídeo' } );
                props.setAttributes( { processing: false } );
              }
            });


            // $.post( ajaxurl, { action: 'splayoumbrella', video_id: vid, video_input: props.attributes.video_input }, function( data ){
            // }).success(function(data){
            //   console.log(data);
            //
            //   props.setAttributes( { splay_url: 'https://youmbrella.com/embed/'.concat(data.resp.embedkey) } );
            //   props.setAttributes( { success: true } );
            //   props.setAttributes( { error: false } );
            //   props.setAttributes( { error_msg: '' } );
            //   props.setAttributes( { processing: false } );
            //
            //
            //   // props.setAttributes( { error: true } );
            //   // props.setAttributes( { error_msg: data.message ? data.message : 'Não foi possível incorporar o vídeo' } );
            //   // props.setAttributes( { processing: false } );
            //
            // });
          }
        }, "Proteger e Incorporar")
      ),

      // Iframe incorpoeada
      wp.element.createElement(
        'div',
        {
          id: 'rcsplay-block-editable-box-iframe',
          className: success ? "rcsplay_preview_div_iframe" : "hidden"
        },
        ' ',
        wp.element.createElement(
          'iframe',
          {
            src: splay_url,
            id: 'rcsplay_preview_iframe',
            className: 'rcsplay_preview_iframe'
          },
          'SPLAY - Vídeo protegido'
        )
      )
      // wp.element.createElement(
      //     'p',
      //     null,
      //     wp.element.createElement(
      //         'a',
      //         { href: 'https://github.com/eudesgit/gutenberg-blocks-sample' },
      //         'Find out more'
      //     )
      // )
    );


  },

  save: function( props ) {
    var attributes = props.attributes;
    var splay_url = attributes.splay_url;
    return wp.element.createElement(
      'div',
      {
        id: 'rcsplay-block-div-iframe',
        // className: success ? "rcsplay_preview_div_iframe" : "hidden"
      },
      ' ',

      wp.element.createElement(
        'iframe',
        {
          src: splay_url,
          frameborder: 0,
          id: 'rcsplay_view_iframe',
          // className: 'rcsplay_preview_iframe'
        },
        'SPLAY - Vídeo protegido'
      )
    )
  },
} );
