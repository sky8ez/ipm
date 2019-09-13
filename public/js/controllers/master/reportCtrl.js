// public/js/controllers/listCtrl.js

angular.module('reportCtrl', [])

.controller('reportController', function($window, $scope, $routeParams,$mdDialog, List) {

    // get all the datas first and bind it to the $scope.datas object
    // use the function we created in our service
    // GET ALL DATAS ==============

    $scope.input_filter = "=";
    $scope.loading = true;
    $scope.form_name = $routeParams.reportName;
    $scope.skip = 1;
    $scope.hide_load_more = false;
    $scope.datas = [];

  

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

    $scope.loading = true;
    //get table list ajax
    List.get('api/report-list/')
      .success(function(data) {
          $scope.datas = data;
        $scope.loading = false;


      })
      .error(function(data) {
         $scope.loading = false;
         $scope.showAlert(data.error);
      });

});
