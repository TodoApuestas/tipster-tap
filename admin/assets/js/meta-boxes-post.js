(function ( $ ) {
	"use strict";

	$(function () {

    /**
     * Metabox of Post
     */
    $('select#_post_tipo_publicacion').each(function() {
      var post_custom_option = $(this).children('option:selected').val();
      if (post_custom_option == 'post') {
        $('#pick_informacion_general').hide();
      } else if (post_custom_option == 'pick') {
        $('#pick_informacion_general').show();
      } else {
        $('#pick_informacion_general').hide();
      }
    });

    $('select#_post_tipo_publicacion').change(function() {
      var post_custom_option = $(this).children('option:selected').val();
      if (post_custom_option == 'post') {
        $('#pick_informacion_general').hide();
      } else if (post_custom_option == 'pick') {
        $('#pick_informacion_general').show();
      } else {
        $('#pick_informacion_general').hide();
      }
    });
    /**
     * End Post
     */

	});

}(jQuery));