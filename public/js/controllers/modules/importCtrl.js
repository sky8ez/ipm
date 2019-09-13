// public/js/controllers/formCtrl.js

angular.module('importCtrl', [])


.controller('importController', function($scope,$window, $routeParams,$mdDialog, Forms, Upload) {

    //show alert
    $scope.savedData = {};
    $scope.file_name = "";
    $scope.logs = "";

    $scope.title = "Import / Export";
    $scope.cmb_import = "CUSTOMER";
    $scope.cmb_export = "CUSTOMER";
    $scope.with_data = false;

    //check access
    Forms.get('api/access/IMPORT-EXPORT')
      .success(function(data) {
        $scope.loading = true;
        if (data == "true") {
          //ada akses
        } else {
          //tidak ada akses
          window.location.href = "/#/";
        }
        $scope.loading = false;
      })
      .error(function(data) {
         $scope.loading = false;
         $scope.showAlert(data.error);
      });
    //check access

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

    $scope.import = function() {
          $scope.savedData = {};
          $scope.savedData['table'] = $scope.cmb_import;
          $scope.loading = true;
          // Forms.save('api/tools/import-export/import',  $scope.savedData)
          // .success(function(data) {
          //     $scope.loading = false;
          //     $scope.logs = data;
          // })
          // .error(function(data) {
          //    $scope.loading = false;
          //    $scope.showAlert(data);
          // });
          Upload.upload({
              url: 'api/tools/import-export/import',
              data: {file: $('#fileinput').prop('files')[0], 'data': $scope.savedData}
          }).then(function (resp) {
              if(resp.data.status == 'OK') {
                  $scope.showAlert(resp);
              } else {
               //  bootbox.alert(result.msg);
               $scope.showAlert(resp.data.msg);
              }
               $scope.loading = false;

          }, function (resp) {
              $scope.loading = false;
            console.log('Error status: ' + resp.status);
          }, function (evt) {
              var progressPercentage = parseInt(100.0 * evt.loaded / evt.total);
              console.log('progress: ' + progressPercentage + '% ' + evt.config.data.file);
          });
    }

    $scope.export = function() {
      $scope.savedData = {};
      var form = $("<form></form>");
      form.attr('action', "api/tools/import-export/download-template");
      form.attr('method', "post");
      form.append('<input type="hidden" name="_token" value="'+ Laravel.csrfToken +'">');
      form.append('<input type="hidden" name="table" value="' + $scope.cmb_export + '">');
      form.append('<input type="hidden" name="with_data" value="' + $scope.with_data + '">');

      form.appendTo('body').submit();

      // $scope.savedData = {};
      // $scope.savedData['table'] = $scope.cmb_export;
      // $scope.loading = true;
      // Forms.save('api/tools/import-export/download-template',  $scope.savedData)
      // .success(function(data) {
      //     $scope.loading = false;
      //     // $scope.logs = data;
      //     console.log(data);
      // })
      // .error(function(data) {
      //    $scope.loading = false;
      //    $scope.showAlert(data);
      // });
    }


    $scope.cancel = function() {
      $mdDialog.cancel();
    }



});
