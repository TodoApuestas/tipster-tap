(function ( $ ) {
	"use strict";

	$(function () {

    /**
     * Metabox of Tipster
     */
    $('select#_tipster_incluir_datos_iniciales').each(function() {
        var post_custom_option = $(this).children('option:selected').val();
        if (post_custom_option == '0') {
            $('div.cmb2-id--tipster-aciertos-iniciales').hide();
            $('div.cmb2-id--tipster-fallos-iniciales').hide();
            $('div.cmb2-id--tipster-nulos-iniciales').hide();
            $('div.cmb2-id--tipster-unidades-jugadas-iniciales').hide();
            $('div.cmb2-id--tipster-unidades-ganadas-iniciales').hide();
            $('div.cmb2-id--tipster-unidades-perdidas-iniciales').hide();
        } else if (post_custom_option == '1') {
            $('div.cmb2-id--tipster-aciertos-iniciales').show();
            $('div.cmb2-id--tipster-fallos-iniciales').show();
            $('div.cmb2-id--tipster-nulos-iniciales').show();
            $('div.cmb2-id--tipster-unidades-jugadas-iniciales').show();
            $('div.cmb2-id--tipster-unidades-ganadas-iniciales').show();
            $('div.cmb2-id--tipster-unidades-perdidas-iniciales').show();
        } else {
            $('div.cmb2-id--tipster-aciertos-iniciales').hide();
            $('div.cmb2-id--tipster-fallos-iniciales').hide();
            $('div.cmb2-id--tipster-nulos-iniciales').hide();
            $('div.cmb2-id--tipster-unidades-jugadas-iniciales').hide();
            $('div.cmb2-id--tipster-unidades-ganadas-iniciales').hide();
            $('div.cmb2-id--tipster-unidades-perdidas-iniciales').hide();
        }
    });

    $('select#_tipster_incluir_datos_iniciales').change(function() {
        var post_custom_option = $(this).children('option:selected').val();
        if (post_custom_option == '0') {
            $('div.cmb2-id--tipster-aciertos-iniciales').hide();
            $('div.cmb2-id--tipster-fallos-iniciales').hide();
            $('div.cmb2-id--tipster-nulos-iniciales').hide();
            $('div.cmb2-id--tipster-unidades-jugadas-iniciales').hide();
            $('div.cmb2-id--tipster-unidades-ganadas-iniciales').hide();
            $('div.cmb2-id--tipster-unidades-perdidas-iniciales').hide();
        } else if (post_custom_option == '1') {
            $('div.cmb2-id--tipster-aciertos-iniciales').show();
            $('div.cmb2-id--tipster-fallos-iniciales').show();
            $('div.cmb2-id--tipster-nulos-iniciales').show();
            $('div.cmb2-id--tipster-unidades-jugadas-iniciales').show();
            $('div.cmb2-id--tipster-unidades-ganadas-iniciales').show();
            $('div.cmb2-id--tipster-unidades-perdidas-iniciales').show();
        } else {
            $('div.cmb2-id--tipster-aciertos-iniciales').hide();
            $('div.cmb2-id--tipster-fallos-iniciales').hide();
            $('div.cmb2-id--tipster-nulos-iniciales').hide();
            $('div.cmb2-id--tipster-unidades-jugadas-iniciales').hide();
            $('div.cmb2-id--tipster-unidades-ganadas-iniciales').hide();
            $('div.cmb2-id--tipster-unidades-perdidas-iniciales').hide();
        }
    });
    /**
     * End Tipster
     */

	});

}(jQuery));