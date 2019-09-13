// public/js/controllers/listCtrl.js

function isIEPreVer9() { var v = navigator.appVersion.match(/MSIE ([\d.]+)/i); return (v ? v[1] < 9 : false); }

function getScrollbarWidth() {
  var inner = document.createElement('p');
  inner.style.width = "100%";
  inner.style.height = "200px";

  var outer = document.createElement('div');
  outer.style.position = "absolute";
  outer.style.top = "0px";
  outer.style.left = "0px";
  outer.style.visibility = "hidden";
  outer.style.width = "200px";
  outer.style.height = "150px";
  outer.style.overflow = "hidden";
  outer.appendChild (inner);

  document.body.appendChild (outer);
  var w1 = inner.offsetWidth;
  outer.style.overflow = 'scroll';
  var w2 = inner.offsetWidth;
  if (w1 == w2) w2 = outer.clientWidth;

  document.body.removeChild (outer);

  return (w1 - w2);
};

angular.module('reportSlickViewCtrl', ['dragtable'])

//untuk date picker secara ajax supaya dapat bekerja
.directive('datepickerDisable', function() {
  return function($scope, element) {
    element.datepicker({
      // minDate: 0
      dateFormat: 'dd/mm/yy',
      disabled : true
    });
  };
})

.directive('datepicker', function() {
  return function($scope, element) {
    element.datepicker({
      // minDate: 0
      dateFormat: 'dd/mm/yy',
    });
  };
})

.directive('sGrid', [function () {
    return {
        restrict: 'EA',
        link : function(scope, element, attrs){
            // for clearer present I initialize data right in directive
            // start init data
            // var columns = scope.columns;
            // var options = scope.options;
            // // end init data
            //
            // // finally render layout
            // var dataProvider = new TotalsDataView(scope.dataView, columns);
            // scope.grid = new Slick.Grid(element, dataProvider, columns, options);
            //
            // scope.totalsPlugin = new TotalsPlugin(getScrollbarWidth());
            // scope.grid.registerPlugin(scope.totalsPlugin);
            //
            //
            //
            // scope.grid.onSort.subscribe(function (e, args) {
            //     sortdir = args.sortAsc ? 1 : -1;
            //     sortcol = args.sortCol.field;
            //
            //       var comparer = function(a, b) {
            //         var x = a[sortcol], y = b[sortcol];
            //           return (x == y ? 0 : (x > y ? 1 : -1));
            //       }
            //
            //     if (isIEPreVer9()) {
            //       // using temporary Object.prototype.toString override
            //       // more limited and does lexicographic sort only by default, but can be much faster
            //       var percentCompleteValueFn = function () {
            //         var val = this["percentComplete"];
            //         if (val < 10) {
            //           return "00" + val;
            //         } else if (val < 100) {
            //           return "0" + val;
            //         } else {
            //           return val;
            //         }
            //       };
            //       // use numeric sort of % and lexicographic for everything else
            //       scope.dataView.fastSort((sortcol == "percentComplete") ? percentCompleteValueFn : sortcol, args.sortAsc);
            //     } else {
            //       // using native sort with comparer
            //       // preferred method but can be very slow in IE with huge datasets
            //       scope.dataView.sort(comparer, args.sortAsc);
            //     }
            //   });
            //
            //
            // // Make the grid respond to DataView change events.
            // scope.dataView.onRowCountChanged.subscribe(function (e, args) {
            //   scope.grid.updateRowCount();
            //   scope.grid.render();
            //   scope.totalsPlugin.render();
            // });
            //
            // scope.dataView.onRowsChanged.subscribe(function (e, args) {
            //   scope.grid.invalidateRows(args.rows);
            //   scope.grid.render();
            //   scope.totalsPlugin.render();
            // });

        }
    }
}])



.controller('reportSlickViewController', function($window, $scope, $routeParams,$mdDialog, Forms, Excel, $timeout ) {

    // get all the datas first and bind it to the $scope.datas object
    // use the function we created in our service
    // GET ALL DATAS ==============

    $scope.loading = true;
    $scope.title = "";
    $scope.report_name = $routeParams.reportName;
    $scope.quick_date = "Today";
    $scope.data = [];
    $scope.dataView = new Slick.Data.DataView();


    $scope.columns =[];



        //
        // {id: "title", name: "Title", field: "title",sortable: true},
        // {id: "duration", name: "Duration", field: "duration",sortable: true},
        // {id: "%", name: "% Complete", field: "percentComplete",sortable: true},
        // {id: "start", name: "Start", field: "start",sortable: true},
        // {id: "finish", name: "Finish", field: "finish",sortable: true},
        // {id: "effort-driven", name: "Effort Driven", field: "effortDriven",sortable: true, hasTotal: true}


   $scope.options = {
      enableColumnReorder: true,
      enableAddRow: false,
      enableCellNavigation: true,
      selectable: true,
      // showHeaderRow: true,
      // headerRowHeight: 30,
      // forceFitColumns: true
    };

    //show alert
    $scope.showAlert = function(msg) {
      $mdDialog.show(
        $mdDialog.alert()
          .clickOutsideToClose(true)
          .title('Alert')
          .htmlContent(msg)
          .ariaLabel('Alert Dialog')
          .ok('Close')
      );
    };


    $scope.change_date_quick = function() {
      switch ($scope.quick_date) {
        case "All":
          $('.datefrom').datepicker("setDate", new Date("1990-01-01"));
          $('.dateto').datepicker("setDate",new Date("2050-01-01"));
          break;
        case "Today":
          $('.datefrom').datepicker("setDate", "0");
          $('.dateto').datepicker("setDate","0");
          break;
        case "Last Month":
          var today = new Date();
          var datefrom = new Date(today.getFullYear(),today.getMonth() -1,1);
          var dateto = new Date(today.getFullYear(),today.getMonth(), datefrom.getDate() - 1 );
          $('.datefrom').datepicker("setDate", datefrom);
          $('.dateto').datepicker("setDate",dateto);
          break;
        case "Last Year":
          var today = new Date();
          var datefrom = new Date(today.getFullYear() -1,0,1);
          var dateto = new Date(today.getFullYear(),datefrom.getMonth(), datefrom.getDate() - 1 );
          $('.datefrom').datepicker("setDate", datefrom);
          $('.dateto').datepicker("setDate",dateto);
          break;
        case "This Month":
          var today = new Date();
          var datefrom = new Date(today.getFullYear(),today.getMonth(),1);
          var dateto = new Date(today.getFullYear(),today.getMonth() + 1, datefrom.getDate() - 1 );
          $('.datefrom').datepicker("setDate", datefrom);
          $('.dateto').datepicker("setDate",dateto);
          break;
        case "This Year":
          var today = new Date();
          var datefrom = new Date(today.getFullYear(),0,1);
          var dateto = new Date(today.getFullYear() + 1,datefrom.getMonth(), datefrom.getDate() - 1 );
          $('.datefrom').datepicker("setDate", datefrom);
          $('.dateto').datepicker("setDate",dateto);
          break;
        default:

      }
    }

    $scope.getDate = function(date1) {
      var year = date1.getFullYear();
      var month = date1.getMonth()+1;
      var day = date1.getDate();

      if (String(month).length == 1) {
        month = "0" + month;
      }

      if (String(day).length == 1) {
        day = "0" + day;
      }

      return year + '-' + month + '-' + day;
    }

    $scope.refresh_data = function() {
      $scope.loading = true;
      $scope.filterData = {};
      $scope.filterData['report_name'] = $scope.report_name;
      $scope.filterData['date_from'] = $scope.getDate($('.datefrom').datepicker("getDate"));
      $scope.filterData['date_to'] = $scope.getDate($('.dateto').datepicker("getDate"));
      $scope.filterData['no_from'] = $scope.nofrom;
      $scope.filterData['no_to'] = $scope.noto;




      for(i=0;i<$scope.search.length;i++) {
        $scope.filterData[$scope.search[i]['col_name']] = $scope.search[i]['val'];
      }
      Forms.refresh('api/report-view', $scope.filterData)
        .success(function(result) {
           $scope.details  = result.row;
           $scope.footers  = result.footers;
           $scope.loading = false;
          //  console.log($scope.details);
            $scope.dataView.beginUpdate();
           $scope.dataView.setItems( $scope.details);
           $scope.dataView.endUpdate();

        })
        .error(function(data) {
            console.log(data);
           $scope.loading = false;
           $scope.showAlert(data.error);
        });


      // for (var i = 0; i < 22000; i++) {
      //     var d = ($scope.data[i] = {});
      //
      //     d["id"] = "id_" + i;
      //     d["num"] = i;
      //     d["title"] = "Task " + i;
      //     d["duration"] = "5 days";
      //     d["percentComplete"] = Math.round(Math.random() * 100);
      //     d["start"] = "01/01/2009";
      //     d["finish"] = "01/05/2009";
      //     d["effortDriven"] = i;
      // }



    }

    $scope.export_excel = function() {
    //  $('#table_data').tableExport({type:'excel',escape:'false'});
          // $scope.exportHref=Excel.tableToExcel("#table_data",'sheet name');
          //  $timeout(function(){location.href=$scope.fileData.exportHref;},100); // trigger download
      //  var exportHref=Excel.tableToExcel("#table_data",'sheet name');
      //  $timeout(function(){location.href=exportHref;},100); // trigger download

      var data = JSON.stringify($scope.dataView.getItems());

      $scope.savedData = {};
      var form = $("<form></form>");
      form.attr('action', "api/report/export");
      form.attr('method', "post");
      form.append('<input type="hidden" name="_token" value="'+ Laravel.csrfToken +'">');
      form.append("<input type='hidden' name='data' value='" + data + "'>");
      form.append('<input type="hidden" name="tesss" value="asddassa">');

      form.appendTo('body').submit();

    }

    $scope.export_csv = function() {
    //  $('#table_data').tableExport({type:'excel',escape:'false'});
          // $scope.exportHref=Excel.tableToExcel("#table_data",'sheet name');
          //  $timeout(function(){location.href=$scope.fileData.exportHref;},100); // trigger download
      //  var exportHref=Excel.tableToExcel("#table_data",'sheet name');
      //  $timeout(function(){location.href=exportHref;},100); // trigger download

      var data = JSON.stringify($scope.dataView.getItems());

      $scope.savedData = {};
      var form = $("<form></form>");
      form.attr('action', "api/report/export-csv");
      form.attr('method', "post");
      form.append('<input type="hidden" name="_token" value="'+ Laravel.csrfToken +'">');
      form.append("<input type='hidden' name='data' value='" + data + "'>");
      form.append('<input type="hidden" name="tesss" value="asddassa">');

      form.appendTo('body').submit();

    }

    $scope.export_pdf = function() {
      var data = JSON.stringify($scope.dataView.getItems());

      $scope.savedData = {};
      var form = $("<form></form>");
      form.attr('action', "api/report/export-pdf");
      form.attr('method', "post");
      form.append('<input type="hidden" name="_token" value="'+ Laravel.csrfToken +'">');
      form.append("<input type='hidden' name='data' value='" + data + "'>");
      form.append('<input type="hidden" name="tesss" value="asddassa">');

      form.appendTo('body').submit();

      // $('#table_data').tableExport({type:'pdf',pdfFontSize:'7',escape:'false'});
      // var rows = [];
      // var width = [];
      // var row = [];
      // for(j=0;j<$scope.columns.length;j++) {
      //   width.push('auto');
      //   row.push($scope.columns[j]['header']);
      // }
      // rows.push(row);
      //
      // for(i=0;i<$scope.details.length;i++) {
      //   var row = [];
      //   for(j=0;j<$scope.columns.length;j++) {
      //     row.push($scope.details[i][$scope.columns[j]['col_name']]);
      //   }
      //   rows.push(row);
      // }
      //
      // var row = [];
      // for(j=0;j<$scope.footers.length;j++) {
      //   row.push($scope.footers[j]['value']);
      // }
      //  rows.push(row);
      //
      // var docDefinition = {
      //     content: [{
      //       table: {
      //           // headers are automatically repeated if the table spans over multiple pages
      //           // you can declare how many rows should be treated as headers
      //           headerRows: 1,
      //           widths: width,
      //           body: rows
      //         }
      //     }]
      // };
      // pdfMake.createPdf(docDefinition).download("export.pdf");



    }

    //get table list ajax
    Forms.get('api/report-view/' + $scope.report_name)
      .success(function(data) {
        $scope.title = data['title'];
        $scope.search = data['search'];
        $scope.columns1 = data['columns'];

        //check access
        Forms.get('api/access/' + data['report_id'])
          .success(function(data) {
            $scope.loading = true;
            if (data == "true") {
              //ada akses
            } else {
              //tidak ada akses
              $scope.showAlert("you don't have access to this page");
              window.location.href = "/#/";
            }
            $scope.loading = false;
          })
          .error(function(data) {
             $scope.loading = false;
             $scope.showAlert(data.error);
          });
        //check access

        // console.log($scope.columns1);
        $scope.columns = [];

            // {id: "title", name: "Title", field: "title",sortable: true},
            // {id: "duration", name: "Duration", field: "duration",sortable: true},
            // {id: "%", name: "% Complete", field: "percentComplete",sortable: true, hasTotal: true},
            // {id: "start", name: "Start", field: "start",sortable: true},
            // {id: "finish", name: "Finish", field: "finish",sortable: true},
            // {id: "effort-driven", name: "Effort Driven", field: "effortDriven",sortable: true, hasTotal: true}


        for(i=0;i<$scope.columns1.length;i++) {
          switch ($scope.columns1[i]['type']) {
            case 'payment_status':
              $scope.columns.push({id: $scope.columns1[i]['col_name'], name: $scope.columns1[i]['header'], field: $scope.columns1[i]['col_name'], sortable: true, hasTotal: $scope.columns1[i]['has_total'], width: 100, formatter: Slick.Formatters.PaidUnpaid });
              break;
            case 'text':
            if ( $scope.columns1[i]['col_name'] == "id") {
              $scope.columns.push({id: $scope.columns1[i]['col_name'], name: $scope.columns1[i]['header'], field: $scope.columns1[i]['col_name'], sortable: true, hasTotal: $scope.columns1[i]['has_total'], width: 1 });
            } else {
              $scope.columns.push({id: $scope.columns1[i]['col_name'], name: $scope.columns1[i]['header'], field: $scope.columns1[i]['col_name'], sortable: true, hasTotal: $scope.columns1[i]['has_total'], width: 100 });
            }
              break;
            case 'datetime':
              $scope.columns.push({id: $scope.columns1[i]['col_name'], name: $scope.columns1[i]['header'], field: $scope.columns1[i]['col_name'], sortable: true, hasTotal: $scope.columns1[i]['has_total'], width: 100 });
              break;
            case 'currency':
              $scope.columns.push({id: $scope.columns1[i]['col_name'], name: $scope.columns1[i]['header'], field: $scope.columns1[i]['col_name'], sortable: true, hasTotal: $scope.columns1[i]['has_total'],cssClass: "right-align", formatter: Slick.Formatters.Currency, width: 100});
              break;
            case 'number':
              $scope.columns.push({id: $scope.columns1[i]['col_name'], name: $scope.columns1[i]['header'], field: $scope.columns1[i]['col_name'], sortable: true, hasTotal: $scope.columns1[i]['has_total'],cssClass: "right-align", formatter: Slick.Formatters.Number, width: 100});
              break;
            default:

          }

        }

        // $scope.grid.setColumns($scope.columns);

        var columns = $scope.columns;
        var options = $scope.options;
        // end init data

        // finally render layout
        var dataProvider = new TotalsDataView($scope.dataView, columns);
        $scope.grid = new Slick.Grid("#slick", dataProvider, columns, options);

        $scope.totalsPlugin = new TotalsPlugin(getScrollbarWidth());
        $scope.grid.registerPlugin($scope.totalsPlugin);

        // $scope.grid.resizeCanvas();
        // $($scope.grid.getHeaderRow()).delegate(":input", "change keyup", function (e) {
        //   var columnId = $(this).data("columnId");
        //   if (columnId != null) {
        //     columnFilters[columnId] = $.trim($(this).val());
        //     $scope.dataView.refresh();
        //   }
        // });
        //
        // $scope.grid.onHeaderRowCellRendered.subscribe(function(e, args) {
        //     $(args.node).empty();
        //     $("<input type='text'>")
        //        .data("columnId", args.column.id)
        //        .val(columnFilters[args.column.id])
        //        .appendTo(args.node);
        // });

        $scope.grid.onSort.subscribe(function (e, args) {
            sortdir = args.sortAsc ? 1 : -1;
            sortcol = args.sortCol.field;

              var comparer = function(a, b) {
                var x = a[sortcol], y = b[sortcol];
                  return (x == y ? 0 : (x > y ? 1 : -1));
              }

            if (isIEPreVer9()) {
              // using temporary Object.prototype.toString override
              // more limited and does lexicographic sort only by default, but can be much faster
              var percentCompleteValueFn = function () {
                var val = this["percentComplete"];
                if (val < 10) {
                  return "00" + val;
                } else if (val < 100) {
                  return "0" + val;
                } else {
                  return val;
                }
              };
              // use numeric sort of % and lexicographic for everything else
              $scope.dataView.fastSort((sortcol == "percentComplete") ? percentCompleteValueFn : sortcol, args.sortAsc);
            } else {
              // using native sort with comparer
              // preferred method but can be very slow in IE with huge datasets
              $scope.dataView.sort(comparer, args.sortAsc);
            }
          });


        // Make the grid respond to DataView change events.
        $scope.dataView.onRowCountChanged.subscribe(function (e, args) {
          $scope.grid.invalidateAllRows(args.rows);
          $scope.grid.updateRowCount();
          $scope.grid.render();
          $scope.totalsPlugin.render();
        });

        $scope.dataView.onRowsChanged.subscribe(function (e, args) {
          $scope.grid.invalidateRows(args.rows);
          $scope.grid.render();
          $scope.totalsPlugin.render();

        });



        $scope.details = data['details'];
        $scope.filter_no_flag = data['filter_no_flag'];

        $('.datefrom').datepicker("setDate", "0");
        $('.dateto').datepicker("setDate","0");

        $scope.loading = false;

        // var data = [{name: "Moroni", age: 50} /*,*/];
        // $scope.tableParams = new NgTableParams({}, { dataset: $scope.details});


      })
      .error(function(data) {
         $scope.loading = false;
         $scope.showAlert(data.error);
      });

      // $(function () {
      //   for (var i = 0; i < 5; i++) {
      //      var d = (data[i] = {});
      //      d["title"] = "<a href='#' tabindex='0'>Task</a> " + i;
      //      d["duration"] = "5 days";
      //      d["percentComplete"] = Math.min(100, Math.round(Math.random() * 110));
      //      d["start"] = "01/01/2009";
      //      d["finish"] = "01/05/2009";
      //      d["effortDriven"] = (i % 5 == 0);
      //    }
      //    grid = new Slick.Grid("#myGrid", data, columns, options);
      //  })

      // a standard formatter returns a string
      function formatter(row, cell, value, columnDef, dataContext) {
          return value;
      }
      // an extended formatter returns an object { text , removeClasses, addClasses }
      // the classes are removed and then added during an update, or just added on cell creation
      function statusFormatter(row, cell, value, columnDef, dataContext) {
          var rtn = { text: value, removeClasses: 'red orange green' };
          if (value !== null || value !== "") {
            if (value < 33) {
              rtn.addClasses = "red";
            } else if (value < 66) {
              rtn.addClasses =  "orange";
            } else {
              rtn.addClasses =  "green";
            }
          }
          return rtn;
      }


});
