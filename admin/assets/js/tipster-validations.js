if ( typeof(jQuery.validator) !== 'undefined' ) {

    var FormValidation = function () {

        var handleValidation = function(){
            var form = jQuery('#post');

            form.validate({
                errorElement: 'span', //default input error message container
                errorClass: 'help-block help-block-error', // default input error message class
                focusInvalid: true, // focus the last invalid input
                // ignore: "",  // validate all fields including form hidden input
                rules: {
                    _tipster_aciertos_iniciales: {
                        required: true,
                        number: true
                    },
                    _tipster_fallos_iniciales: {
                        required: true,
                        number: true
                    },
                    _tipster_nulos_iniciales: {
                        required: true,
                        number: true
                    },
                    _tipster_unidades_jugadas_iniciales: {
                        required: true,
                        number: true
                    },
                    _tipster_unidades_ganadas_iniciales: {
                        required: true,
                        number: true
                    },
                    _tipster_unidades_perdidas_iniciales:  {
                        required: true,
                        number: true
                    },
                    _tipster_limit_statistics:  {
                        required: true,
                        number: true
                    }
                    _tipster_google_plus: {
                        url: true
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