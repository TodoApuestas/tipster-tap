jQuery(document).ready(
  function($) {
    /**
     * Metabox of Post
     */
    $('select#_post_custom_options').each(function() {
      var post_custom_option = $(this).children('option:selected').val();
      if (post_custom_option == 'post_comun') {
        $('#partida_destacada_edit').hide();
      } else if (post_custom_option == 'partida_destacada') {
        $('#partida_destacada_edit').show();
      } else {
        $('#partida_destacada_edit').hide();
      }
    });

    $('select#_post_custom_options').change(function() {
      var post_custom_option = $(this).children('option:selected').val();
      if (post_custom_option == 'post_comun') {
        $('#partida_destacada_edit').hide();
      } else if (post_custom_option == 'partida_destacada') {
        $('#partida_destacada_edit').show();
      } else {
        $('#partida_destacada_edit').hide();
      }
    });
    /**
     * End Post
     */
  }
);