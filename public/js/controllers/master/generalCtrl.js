// public/js/controllers/formCtrl.js

angular.module('generalCtrl', [])

// .directive('myRepeatDirective', function() {
//   return function(scope, element, attrs) {
//     // angular.element(element).css('color','blue');
//     if (scope.$last){
//     //  window.alert("im the last!");
//       $('#datepicker1').datepicker();
//     }
//   };
// })

.controller('generalController', function($scope, Upload, $routeParams, $mdDialog,$window, Forms) {

    // get all the datas first and bind it to the $scope.datas object
    // use the function we created in our service
    // GET RECORDS ==============

    $scope.loading = true;
    $scope.form_name = $routeParams.form;
    $scope.form_id = $routeParams.ID;

    //check access
    Forms.get('api/access/GENERAL')
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

    //get table list ajax
    Forms.get('api/general')
      .success(function(data) {
        $scope.loading = true;
        $scope.datas = data['forms'];

        //FOR DATA AUTO COMPLETE
        $scope.loading = false;
      })
      .error(function(data) {
         $scope.loading = false;
         $scope.showAlert(data.error);
      });


    $scope.cleanUp = function() {

      // Appending dialog to document.body to cover sidenav in docs app
        var confirm = $mdDialog.confirm()
              .title('Delete Data')
              .textContent('Would you like to permanent delete until this date?')
              // .targetEvent(ev)
              .ok('Process Delete Data')
              .cancel('Cancel');

        $mdDialog.show(confirm).then(function() {
          if ($('.dateto').datepicker("getDate") == null) {
              $scope.showAlert("Date Must Be Selected First");
          } else {
            $scope.loading = true;
            $scope.savedData = {};
            $scope.savedData['date_to'] = $scope.getDate($('.dateto').datepicker("getDate"));
            Forms.save('api/general/cleanup', $scope.savedData)
              .success(function(result) {
                if(result.status == 'OK') {
                    $scope.showAlert(result.msg);
                    $window.location.href = result.url;

                } else {
                 //  bootbox.alert(result.msg);
                 $scope.showAlert(result.msg);
                }
                 $scope.loading = false;
              })
              .error(function(data) {
                 $scope.loading = false;
                 $scope.showAlert(data.error);
              });

          }


        }, function() {

        });


    }


    $scope.backup = function() {
      $scope.loading = true;
      $scope.savedData = {};
      Forms.save('api/general/backup', $scope.savedData)
        .success(function(result) {
          if(result.status == 'OK') {
              $scope.showAlert(result.msg);
              //$window.location.href = result.url;
          } else {
           //  bootbox.alert(result.msg);
           $scope.showAlert(result.msg);
          }
           $scope.loading = false;
        })
        .error(function(data) {
           $scope.loading = false;
           $scope.showAlert(data.error);
        });

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

    $scope.saveGeneral = function() {
      $scope.loading = true;
      $scope.savedData = {};
      $scope.savedData['data'] = $scope.datas;
      Forms.save('api/general/save', $scope.savedData)
        .success(function(result) {
          if(result.status == 'OK') {
              $scope.showAlert(result.msg);
              $window.location.href = result.url;
          } else {
           //  bootbox.alert(result.msg);
           $scope.showAlert(result.msg);
          }
           $scope.loading = false;
        })
        .error(function(data) {
           $scope.loading = false;
           $scope.showAlert(data.error);
        });

    }



});
