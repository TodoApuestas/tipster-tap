var ManagePicks = function () {

    var displaySpinner = function(toggle){
        if( true === toggle ){
            jQuery('#manage-picks-spinner').show();
        } else {
            jQuery('#manage-picks-spinner').hide();
        }
    };

    var handleDatePicker = function(){
        jQuery('#yearmonth')
            .datepicker({
                dateFormat: "yy-mm",
                defaultDate: "today",
                changeMonth: true,
                changeYear: true,
                showButtonPanel: true,
                onClose: function(dateText, inst) {
                    var isDonePressed = function (){
                        return (jQuery('#ui-datepicker-div').html().indexOf('ui-datepicker-close ui-state-default ui-priority-primary ui-corner-all ui-state-hover') > -1);
                    };

                    if (isDonePressed()){
                        var month = jQuery("#ui-datepicker-div .ui-datepicker-month :selected").val();
                        var year = jQuery("#ui-datepicker-div .ui-datepicker-year :selected").val();
                        jQuery(this).datepicker('setDate', new Date(year, month, 1)).trigger('change');

                        jQuery('.date-picker').focusout()//Added to remove focus from datepicker input box on selecting date
                    }
                },
                beforeShow : function(input, inst) {
                    if ((datestr = jQuery(this).val()).length > 0) {
                        year = datestr.substring(datestr.length-4, datestr.length);
                        month = datestr.substring(0, 2);
                        jQuery(this).datepicker('option', 'defaultDate', new Date(year, month-1, 1));
                        jQuery(this).datepicker('setDate', new Date(year, month-1, 1));
                    }
                }
            })
            .datepicker("setDate", new Date())
        ;
    };

    var handlePicksTable = function () {
        var picksTable = jQuery('#dt-picks');
        var oPicksTable = picksTable.DataTable({
            "responsive": true,
            "dom": "<'row'<'col-md-6 col-sm-12'l><'col-md-6 col-sm-12'f>><'row'<'col-xs-12'r><'col-xs-12't>><'row'<'col-md-5 col-sm-12'i><'col-md-7 col-sm-12'p>>",
            "lengthMenu": [[1, 5, 10, 25, 50, 75, 100, -1], [1, 5, 10, 25, 50, 75, 100, "todos los"]],
            "pageLength": 10,
            "language": {
                "decimal": ",",
                "thousands": ".",
                "loadingRecords": " Cargando picks... ",
                "processing":     " Cargando picks... ",
                "lengthMenu": "Mostrar _MENU_ picks",
                "emptyTable": "No hay picks disponibles",
                "zeroRecords": "No se encontraron picks",
                "info": "Mostrando picks del _START_ al _END_ de un total de _TOTAL_",
                "infoFiltered": "(filtrado de un total de _MAX_ picks)",
                "infoEmpty": "No hay picks para mostrar",
                "infoPostFix": "",
                "paginate": {
                    "previous": "<<",
                    "next": ">>",
                    "last": ">>>>",
                    "first": "<<<<"
                },
                "search": "Buscar por:"//,
            },
            "processing": true,
            "deferRender": true,
            "serverSide": false,
            "searching": true,
            "paging": true,
            "order": [ 1, "asc" ],
            "columns":[
                {"data":"id", "className":"never"},
                {"data":{"_":"fecha.display", "sort":"fecha.sort"}, "className":"all"},
                {"data":{"_":"evento.display", "sort":"evento.sort"}, "className":"all", "class":"text-right"},
                {"data":"cuota", "className":"all", "class":"text-center", "searchable":false},
                {"data":"stake", "className":"all", "class":"text-center", "searchable":false},
                {"data":"ganancia", "className":"all", "class":"text-center", "searchable":false},
                {"data":{"_":"resultado.display", "sort":"resultado.sort"}, "className":"all", "class":"text-center"},
                {"data":"accion", "className":"not-mobile", "class":"text-left", "sortable":false, "searchable":false}
            ],
            "columnDefs": [{ "visible": false,  "targets": [ 0 ] }]
        });

        new jQuery.fn.dataTable.FixedHeader( oPicksTable );
    };

    var executeFiltrar = function() {
        var tipster = jQuery('#tipsters').val();
        var yearmonth = jQuery('#yearmonth').val();
        var endpoint = wpApiSettings.root + "tipster-tap/v4/picks/" + tipster + "/" + yearmonth
        jQuery.ajax({
            url: endpoint,
            cache: false,
            dataType: 'json',
            beforeSend: function(){
                displaySpinner(true);
            },
            success: function (dataResponse) {
                jQuery('#dt-picks').DataTable()
                    .clear()
                    .rows.add(dataResponse.data)
                    .draw();
                displaySpinner(false);
            }
        });
    };

    var handleFormFilter = function() {
        jQuery('#picks-form-filter').on('click', '#btnFilter', executeFiltrar);
    };

    var handleTipstersSelect = function(){
        jQuery('select#tipsters')
            .each(function() { executeFiltrar(); })
            .change(function() { executeFiltrar(); })
        ;
    };

    return {
        init: function () {
            handleDatePicker();
            handlePicksTable();
            handleFormFilter();
            handleTipstersSelect();
            executeFiltrar();
        }
    };

}();

jQuery(document).ready(function() {
    ManagePicks.init();
});
