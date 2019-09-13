// public/js/controllers/formCtrl.js

angular.module('displayCtrl', [])


.controller('displayController', function($scope,$window, $routeParams,$mdDialog, Forms, material_width
            , material_length, x1,y1,total1,x2,y2,total2) {
    //show alert
    $scope.savedData = {};
    $scope.title = "Display ";
    $scope.material_width = material_width;
    $scope.material_length = material_length + 2;

    if ($scope.parent_id == "") {
      $scope.parent_id = "0";
    }

    $scope.blocks = [];
    for(var i=0;i<total1;i++) {
      var block = {height : y1, width : x1};
      $scope.blocks.push(block);
    }

    for(var i=0;i<total2;i++) {
      var block = {height : y2, width : x2};
      $scope.blocks.push(block);
    }

    $scope.skip = 1;
    $scope.tb_search = "";
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

    // $scope.savedData['parent_id'] = $scope.parent_id;
    // $scope.savedData['skip'] = 0;
    // $scope.savedData['column'] = '';
    // $scope.savedData['filter'] = '';
    // $scope.savedData['cond'] = $scope.cond;
    // Forms.refresh('api/search/' + table, $scope.savedData)
    //   .success(function(data) {
    //     $scope.headers = data['headers'];
    //     $scope.data_search = data['datas'];
    //
    //     $scope.cmb_search = $scope.headers[0].value;
    //   })
    //   .error(function(data) {
    //      $scope.loading = false;
    //     //  $scope.showAlert(data.error);
    //     console.log(data);
    //   });

    $scope.cancel = function() {
      $mdDialog.cancel();
    }


});
