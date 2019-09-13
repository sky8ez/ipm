// public/js/controllers/formCtrl.js

angular.module('dashboardCtrl', ['chart.js'])

// .directive('myRepeatDirective', function() {
//   return function(scope, element, attrs) {
//     // angular.element(element).css('color','blue');
//     if (scope.$last){
//     //  window.alert("im the last!");
//       $('#datepicker1').datepicker();
//     }
//   };
// })

.controller('dashboardController', function($scope, Upload, $routeParams, $mdDialog,$window, Forms, $timeout) {

    // get all the datas first and bind it to the $scope.datas object
    // use the function we created in our service
    // GET RECORDS ==============

    $scope.loading = true;
    $scope.form_name = $routeParams.form;
    $scope.form_id = $routeParams.ID;

    $scope.line = true;
    $scope.bar = false;
    $scope.donut = false;
    $scope.graphtype = "Line";
    $scope.quick_date = "This Month";
    $scope.group_by = "day";
    $scope.reportname = "Omzet Report";

    $scope.changeType = function() {
      switch ($scope.graphtype) {
        case "Line":
          $scope.line = true;
          $scope.bar = false;
          $scope.donut = false;
          break;
        case "Bar":
          $scope.line = false;
          $scope.bar = true;
          $scope.donut = false;
          break;
        case "Donut":
          $scope.line = false;
          $scope.bar = false;
          $scope.donut = true;
          break;
        default:

      }
    }


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


    $scope.onClick = function (points, evt) {
      console.log(points, evt);
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


});
