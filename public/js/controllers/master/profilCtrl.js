// public/js/controllers/formCtrl.js

angular.module('profilCtrl', [])

// .directive('myRepeatDirective', function() {
//   return function(scope, element, attrs) {
//     // angular.element(element).css('color','blue');
//     if (scope.$last){
//     //  window.alert("im the last!");
//       $('#datepicker1').datepicker();
//     }
//   };
// })

.controller('profilController', function($scope, Upload, $routeParams, $mdDialog,$window, Forms) {

    // get all the datas first and bind it to the $scope.datas object
    // use the function we created in our service
    // GET RECORDS ==============

    $scope.loading = true;
    $scope.form_name = $routeParams.form;
    $scope.form_id = $routeParams.ID;
    $scope.data_before = "";

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
    Forms.get('api/profil')
      .success(function(data) {
        $scope.loading = true;
        $scope.datas = data['forms'];
        $scope.data_before = data['data_before'];

        //FOR DATA AUTO COMPLETE
        $scope.loading = false;
      })
      .error(function(data) {
         $scope.loading = false;
         $scope.showAlert(data.error);
      });

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

    $scope.saveProfil = function() {
      // console.log($('#fileinput').prop('files')[0]);
        // if ($scope.textForm.file.$valid && $scope.picFile) {
          // alert("ok");

        // }

      if ($scope.textForm.$valid) {
        $scope.loading = true;
        $scope.savedData = {};
        var vals = [];

        for(i=0;i<$scope.datas.length;i++) {
          if ($scope.datas[i]['type'] == 'data') {
            var val = {'name' : $scope.datas[i]['name'],
                      'type' : $scope.datas[i]['type'],
                      'value': $scope.datas[i]['value'],
                      'id': $scope.datas[i]['id'],
                      'value_id': $scope.datas[i]['value_id'],
                      'required': $scope.datas[i]['required'],
                      'unique': $scope.datas[i]['unique']
                      };
             $scope.savedData[$scope.datas[i]['id']] = $scope.datas[i]['value_id'];
             $scope.savedData[$scope.datas[i]['name']] = $scope.datas[i]['value'];
             vals.push(val);
          } else if ($scope.datas[i]['type'] == 'datetime') {
            var value = new Date($(".datepick" + i).datepicker("getDate"));
            value =  $scope.getDate(value);
            var val = {'name' : $scope.datas[i]['name'],
                      'type' : $scope.datas[i]['type'],
                      'value': value,
                      'required': $scope.datas[i]['required'],
                      'unique': $scope.datas[i]['unique']
                      };
             vals.push(val);
             $scope.savedData[$scope.datas[i]['name']] =  value;
       } else if ($scope.datas[i]['type'] == 'image') {
         var val = {'name' : $scope.datas[i]['name'],
                   'type' : $scope.datas[i]['type'],
                   'value': $scope.datas[i]['value'],
                   'id': $scope.datas[i]['id'],
                   'value_id': $scope.datas[i]['value_id'],
                   'required': $scope.datas[i]['required'],
                   'unique': $scope.datas[i]['unique']
                   };
          $scope.savedData[$scope.datas[i]['name']] = $scope.datas[i]['value'];
          vals.push(val);
       } else if ($scope.datas[i]['type'] == 'table') {
         var val = {'name' : $scope.datas[i]['name'],
                   'type' : $scope.datas[i]['type'],
                   'columns' : $scope.datas[i]['columns'],
                   'details' : $scope.datas[i]['details'],
                   'deleted_details' : $scope.datas[i]['deleted_details'],
                   'required': $scope.datas[i]['required'],
                   'unique': $scope.datas[i]['unique']
                   };
                   console.log(val);
          vals.push(val);
          $scope.savedData[$scope.datas[i]['name']] =  value;
         } else if ($scope.datas[i]['type'] == 'access-menu') {
           var val = {'name' : $scope.datas[i]['name'],
                     'type' : $scope.datas[i]['type'],
                     'table': $scope.datas[i]['table'],
                     'value': $scope.datas[i]['menu_detail'],
                     'required': $scope.datas[i]['required'],
                     'unique': $scope.datas[i]['unique']
                     };

                     console.log($scope.datas[i]['menu_detail']);
            vals.push(val);
            $scope.savedData[$scope.datas[i]['name']] =  $scope.datas[i]['menu_detail'];
          } else {
            var val = {'name' : $scope.datas[i]['name'],
                      'type' : $scope.datas[i]['type'],
                      'value': $scope.datas[i]['value'],
                      'required': $scope.datas[i]['required'],
                      'unique': $scope.datas[i]['unique']
                      };
             vals.push(val);
             $scope.savedData[$scope.datas[i]['name']] =  $scope.datas[i]['value'];
          }
        }
        $scope.savedData['data'] = vals;
        $scope.savedData['data_before'] = $scope.data_before;


        Forms.save('api/profil/save', $scope.savedData)
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

      } else {
        angular.forEach($scope.textForm.$error, function (field) {
             angular.forEach(field, function(errorField){
                 errorField.$setTouched();
             })
         });
      }

    }



});
