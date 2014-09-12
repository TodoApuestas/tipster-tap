(function ( $ ) {
	"use strict";

	$(function () {

    /**
     * Metabox of Post
     */
    $('select#_tipster_incluir_datos_iniciales').each(function() {
      var post_custom_option = $(this).children('option:selected').val();
      if (post_custom_option == '0') {
        $('tr.cmb_id__tipster_aciertos_iniciales').hide();
        $('tr.cmb_id__tipster_fallos_iniciales').hide();
        $('tr.cmb_id__tipster_nulos_iniciales').hide();
        $('tr.cmb_id__tipster_unidades_jugadas_iniciales').hide();
        $('tr.cmb_id__tipster_unidades_ganadas_iniciales').hide();
        $('tr.cmb_id__tipster_unidades_perdidas_iniciales').hide();
      } else if (post_custom_option == '1') {
        $('tr.cmb_id__tipster_aciertos_iniciales').show();
        $('tr.cmb_id__tipster_fallos_iniciales').show();
        $('tr.cmb_id__tipster_nulos_iniciales').show();
        $('tr.cmb_id__tipster_unidades_jugadas_iniciales').show();
        $('tr.cmb_id__tipster_unidades_ganadas_iniciales').show();
        $('tr.cmb_id__tipster_unidades_perdidas_iniciales').show();
      } else {
        $('tr.cmb_id__tipster_aciertos_iniciales').hide();
        $('tr.cmb_id__tipster_fallos_iniciales').hide();
        $('tr.cmb_id__tipster_nulos_iniciales').hide();
        $('tr.cmb_id__tipster_unidades_jugadas_iniciales').hide();
        $('tr.cmb_id__tipster_unidades_ganadas_iniciales').hide();
        $('tr.cmb_id__tipster_unidades_perdidas_iniciales').hide();
      }
    });

    $('select#_tipster_incluir_datos_iniciales').change(function() {
      var post_custom_option = $(this).children('option:selected').val();
      if (post_custom_option == '0') {
        $('tr.cmb_id__tipster_aciertos_iniciales').hide();
        $('tr.cmb_id__tipster_fallos_iniciales').hide();
        $('tr.cmb_id__tipster_nulos_iniciales').hide();
        $('tr.cmb_id__tipster_unidades_jugadas_iniciales').hide();
        $('tr.cmb_id__tipster_unidades_ganadas_iniciales').hide();
        $('tr.cmb_id__tipster_unidades_perdidas_iniciales').hide();
      } else if (post_custom_option == '1') {
        $('tr.cmb_id__tipster_aciertos_iniciales').show();
        $('tr.cmb_id__tipster_fallos_iniciales').show();
        $('tr.cmb_id__tipster_nulos_iniciales').show();
        $('tr.cmb_id__tipster_unidades_jugadas_iniciales').show();
        $('tr.cmb_id__tipster_unidades_ganadas_iniciales').show();
        $('tr.cmb_id__tipster_unidades_perdidas_iniciales').show();
      } else {
        $('tr.cmb_id__tipster_aciertos_iniciales').hide();
        $('tr.cmb_id__tipster_fallos_iniciales').hide();
        $('tr.cmb_id__tipster_nulos_iniciales').hide();
        $('tr.cmb_id__tipster_unidades_jugadas_iniciales').hide();
        $('tr.cmb_id__tipster_unidades_ganadas_iniciales').hide();
        $('tr.cmb_id__tipster_unidades_perdidas_iniciales').hide();
      }
    });
    /**
     * End Post
     */

	});

}(jQuery));