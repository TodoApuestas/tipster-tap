(function ( $ ) {
	"use strict";

	$(function () {

        var eachChange = function($e){
            var post_custom_option = $e.children('option:selected').val();
            if (post_custom_option === '0') {
                jQuery('div.cmb2-id--tipster-aciertos-iniciales').hide();
                jQuery('div.cmb2-id--tipster-fallos-iniciales').hide();
                jQuery('div.cmb2-id--tipster-nulos-iniciales').hide();
                jQuery('div.cmb2-id--tipster-unidades-jugadas-iniciales').hide();
                jQuery('div.cmb2-id--tipster-unidades-ganadas-iniciales').hide();
                jQuery('div.cmb2-id--tipster-unidades-perdidas-iniciales').hide();
            } else if (post_custom_option === '1') {
                jQuery('div.cmb2-id--tipster-aciertos-iniciales').show();
                jQuery('div.cmb2-id--tipster-fallos-iniciales').show();
                jQuery('div.cmb2-id--tipster-nulos-iniciales').show();
                jQuery('div.cmb2-id--tipster-unidades-jugadas-iniciales').show();
                jQuery('div.cmb2-id--tipster-unidades-ganadas-iniciales').show();
                jQuery('div.cmb2-id--tipster-unidades-perdidas-iniciales').show();
            } else {
                jQuery('div.cmb2-id--tipster-aciertos-iniciales').hide();
                jQuery('div.cmb2-id--tipster-fallos-iniciales').hide();
                jQuery('div.cmb2-id--tipster-nulos-iniciales').hide();
                jQuery('div.cmb2-id--tipster-unidades-jugadas-iniciales').hide();
                jQuery('div.cmb2-id--tipster-unidades-ganadas-iniciales').hide();
                jQuery('div.cmb2-id--tipster-unidades-perdidas-iniciales').hide();
            }
        };

        /**
         * Tipster's Metabox
         */
        jQuery('select#_tipster_incluir_datos_iniciales')
            .each(function() {
                eachChange(jQuery(this));
            })
            .change(function() {
                eachChange(jQuery(this));
            })
        ;
        /**
         * End Tipster's Metabox
         */

	});

}(jQuery));