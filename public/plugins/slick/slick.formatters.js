/***
 * Contains basic SlickGrid formatters.
 *
 * NOTE:  These are merely examples.  You will most likely need to implement something more
 *        robust/extensible/localizable/etc. for your use!
 *
 * @module Formatters
 * @namespace Slick
 */

(function ($) {
  // register namespace
  $.extend(true, window, {
    "Slick": {
      "Formatters": {
        "PercentComplete": PercentCompleteFormatter,
        "PercentCompleteBar": PercentCompleteBarFormatter,
        "YesNo": YesNoFormatter,
        "Checkmark": CheckmarkFormatter,
        "Checkbox": CheckboxFormatter,
        "Currency": CurrencyFormatter,
        "Number": NumberFormatter,
        "PaidUnpaid": PaidUnpaidFormatter

      }
    }
  });

  function PercentCompleteFormatter(row, cell, value, columnDef, dataContext) {
    if (value == null || value === "") {
      return "-";
    } else if (value < 50) {
      return "<span style='color:red;font-weight:bold;'>" + value + "%</span>";
    } else {
      return "<span style='color:green'>" + value + "%</span>";
    }
  }

  function PercentCompleteBarFormatter(row, cell, value, columnDef, dataContext) {
    if (value == null || value === "") {
      return "";
    }

    var color;

    if (value < 30) {
      color = "red";
    } else if (value < 70) {
      color = "silver";
    } else {
      color = "green";
    }

    return "<span class='percent-complete-bar' style='background:" + color + ";width:" + value + "%'></span>";
  }

  function YesNoFormatter(row, cell, value, columnDef, dataContext) {
    return value ? "Yes" : "No";
  }

  function PaidUnpaidFormatter(row, cell, value, columnDef, dataContext) {
    if (value == "Paid") {
      return "<i class='fa fa-check' style='color:green'></i>";
    } else {
      return "<i class='fa fa-times' style='color:red'></i>";
    }
  }

  function CurrencyFormatter(row, cell, value, columnDef, dataContext) {

    if (value === null || value === "" || !(value !== 0)) {

      return "Rp " + Number();

    } else {

      return "Rp " + addCommas(value);

    }

  }

  function NumberFormatter(row, cell, value, columnDef, dataContext) {

    if (value === null || value === "" || !(value !== 0)) {

      return  Number();

    } else {

      return  addCommas(value);

    }

  }

  function CheckboxFormatter(row, cell, value, columnDef, dataContext) {
    return '<img class="slick-edit-preclick" src="../images/' + (value ? "CheckboxY" : "CheckboxN") + '.png">';
  }

  function CheckmarkFormatter(row, cell, value, columnDef, dataContext) {
    return value ? "<img src='../images/tick.png'>" : "";
  }

  function addCommas(nStr) {
      nStr += '';
      var x = nStr.split('.');
      var x1 = x[0];
      var x2 = x.length > 1 ? '.' + x[1] : '';
      var rgx = /(\d+)(\d{3})/;
      while (rgx.test(x1)) {
          x1 = x1.replace(rgx, '$1' + ',' + '$2');
      }
      return x1 + x2;
  }
})(jQuery);
