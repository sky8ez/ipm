// public/js/controllers/listCtrl.js

angular.module('reportViewCtrl', ['dragtable'])

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


// .directive('tablereports', function() {
//   return function($scope, element) {
//     element.dragtable({dragHandle:'.some-handle'});
//     element.tablesorter({widthFixed: true, widgets: ['zebra']});
//
//   };
// })

.directive('datepicker', function() {
  return function($scope, element) {
    element.datepicker({
      // minDate: 0
      dateFormat: 'dd/mm/yy',
    });
  };
})



.controller('reportViewController', function($window, $scope, $routeParams,$mdDialog, Forms, Excel, $timeout ) {

    // get all the datas first and bind it to the $scope.datas object
    // use the function we created in our service
    // GET ALL DATAS ==============

    $scope.loading = true;
    $scope.title = "";
    $scope.report_name = $routeParams.reportName;
    $scope.quick_date = "Today";

    //check access
    alert($scope.report_name );
    List.get('api/access/' + $scope.report_name)
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
        })
        .error(function(data) {
           $scope.loading = false;
           $scope.showAlert(data.error);
        });


    }

    $scope.export_excel = function() {
    //  $('#table_data').tableExport({type:'excel',escape:'false'});
          // $scope.exportHref=Excel.tableToExcel("#table_data",'sheet name');
          //  $timeout(function(){location.href=$scope.fileData.exportHref;},100); // trigger download
       var exportHref=Excel.tableToExcel("#table_data",'sheet name');
       $timeout(function(){location.href=exportHref;},100); // trigger download
    }

    $scope.export_pdf = function() {
      // $('#table_data').tableExport({type:'pdf',pdfFontSize:'7',escape:'false'});
      var rows = [];
      var width = [];
      var row = [];
      for(j=0;j<$scope.columns.length;j++) {
        width.push('auto');
        row.push($scope.columns[j]['header']);
      }
      rows.push(row);

      for(i=0;i<$scope.details.length;i++) {
        var row = [];
        for(j=0;j<$scope.columns.length;j++) {
          row.push($scope.details[i][$scope.columns[j]['col_name']]);
        }
        rows.push(row);
      }

      var row = [];
      for(j=0;j<$scope.footers.length;j++) {
        row.push($scope.footers[j]['value']);
      }
       rows.push(row);

      var docDefinition = {
          content: [{
            table: {
                // headers are automatically repeated if the table spans over multiple pages
                // you can declare how many rows should be treated as headers
                headerRows: 1,
                widths: width,
                body: rows
              }
          }]
      };
      pdfMake.createPdf(docDefinition).download("export.pdf");

    }

    //get table list ajax
    Forms.get('api/report-view/' + $scope.report_name)
      .success(function(data) {
        $scope.title = data['title'];
        $scope.search = data['search'];
        $scope.columns = data['columns'];
        $scope.details = data['details'];
        $scope.filter_no_flag = data['filter_no_flag'];

        $('.datefrom').datepicker("setDate", "0");
        $('.dateto').datepicker("setDate","0");

        $timeout(function(){
            $('table').dragtable({dragHandle:'.some-handle'});
            $("table")
                .tablesorter({widthFixed: true, widgets: ['zebra']});

        })

        $scope.loading = false;

        // var data = [{name: "Moroni", age: 50} /*,*/];
        // $scope.tableParams = new NgTableParams({}, { dataset: $scope.details});


      })
      .error(function(data) {
         $scope.loading = false;
         $scope.showAlert(data.error);
      });


});
