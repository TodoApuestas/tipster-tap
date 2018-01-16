if ( typeof(jQuery.validator) !== 'undefined' ) {

    jQuery.validator.addMethod( "time", function( value, element ) {
        return this.optional( element ) || /^([01]\d|2[0-3]|[0-9])(:[0-5]\d){1,2}$/.test( value );
    }, "Por favor, escribe una hora válida, entre 00:00 y 23:59" );

    var FormValidation = function () {

        var handleValidation = function(){
            var form = jQuery('#post');

            form.validate({
                errorElement: 'span', //default input error message container
                errorClass: 'help-block help-block-error', // default input error message class
                focusInvalid: true, // focus the last invalid input
                // ignore: "",  // validate all fields including form hidden input
                rules: {
                    _pick_evento: {
                        required: true
                    },
                    _pick_fecha_evento: {
                        required: true,
                        date: true
                    },
                    _pick_hora_evento: {
                        required: true,
                        time: true
                    },
                    _pick_pronostico: {
                        required: true
                    },
                    _pick_cuota: {
                        required: true,
                        number: true
                    },
                    _pick_stake:  {
                        required: true,
                        number: true
                    }
                },
                highlight: function(element){
                    jQuery(element).parents('.cmb-row').addClass('notice notice-error');
                },
                unhighlight: function (element) { // revert the change done by hightlight
                    jQuery(element).parents('.cmb-row').removeClass('notice notice-error'); // set error class to the control group
                }
            });
        };

        return {
            init: function(){
                handleValidation();
            }
        }

    }();

    jQuery(document).ready(function () {
        FormValidation.init();
    });

}