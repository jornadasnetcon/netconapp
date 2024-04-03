/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');
require('moment');
require('./bootstrap-datetimepicker.min');

$(document).ready(function () {
    $("#legal-accept").on("change", function(event) {
        if (document.getElementById('legal-accept').checked) {
            $("#register-button").prop("disabled", false);
        } else {
            $("#register-button").prop("disabled", true);
        }
    });

    var currentDate = "04/08/2020 08:00";
    var currentDateVal = $('.timepicker').val();
    if (currentDateVal) {
        currentDate = new Date(currentDateVal);
        var month = currentDate.getMonth() + 1;
        currentDate = month + "/" + currentDate.getDate() + "/" + currentDate.getFullYear() + " " + currentDate.getHours() + ":" + currentDate.getMinutes() + ":00";
    }
    $('.timepicker').datetimepicker({
        locale: 'es',
        format: 'DD/MM/YYYY HH:mm',
        date: currentDate,
        minDate: '2020-04-08',
        maxDate: '2020-04-13 08:00:00'
    });

});
