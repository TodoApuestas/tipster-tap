(function ( $ ) {
	"use strict";

	$(function () {

        $.datepicker.regional["es"] = {
            closeText: "Cerrar", // Display text for close link
            prevText: "&#x3C;Ant", // Display text for previous month link
            nextText: "Sig&#x3E;", // Display text for next month link
            currentText: "Hoy", // Display text for current month link
            monthNames: ["Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"], // Names of months for drop-down and formatting
            monthNamesShort: ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"], // For formatting
            dayNames: ["Domingo", "Lunes", "Martes", "Miercoles", "Jueves", "Viernes", "Sabado"], // For formatting
            dayNamesShort: ["Dom", "Lun", "Mar", "Mie", "Jue", "Vie", "Sab"], // For formatting
            dayNamesMin: ["Do","Lu","Ma","Mi","Ju","Vi","Sa"], // Column headings for days starting at Sunday
            weekHeader: "Sm", // Column header for week of the year
            dateFormat: "dd/mm/yy", // See format options on parseDate
            firstDay: 0, // The first day of the week, Sun = 0, Mon = 1, ...
            isRTL: false, // True if right-to-left language, false if left-to-right
            showMonthAfterYear: false, // True if the year select precedes month, false for month then year
            yearSuffix: "" // Additional text to append to the year in the month headers
        };

        $.datepicker.setDefaults($.datepicker.regional['es']);
        $.datepicker.setDefaults({minDate:"0D"});

	});

}(jQuery));