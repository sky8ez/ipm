// public/js/controllers/formCtrl.js

angular.module('auditCtrl', [])


.controller('auditController', function($scope,$window, $routeParams,$mdDialog, Forms, parent_category, parent_id) {

    //show alert
    $scope.savedData = {};

    $scope.title = "Audit Trail";
    $scope.parent_category = parent_category;
    $scope.parent_id = parent_id;

    if ($scope.parent_id == "") {
      $scope.parent_id = "0";
    }

    $scope.filterData = {};
    $scope.filterData['category'] = $scope.parent_category;
    $scope.filterData['id'] = $scope.parent_id;
    Forms.refresh('api/audit', $scope.filterData)
      .success(function(result) {
         $scope.details  = result['datas'];
         $scope.print_details  = result['print_datas'];
         $scope.loading = false;
      })
      .error(function(data) {
         $scope.loading = false;
         $scope.showAlert(data.error);
      });



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


        $scope.cancel = function() {
          $mdDialog.cancel();
        }



});
