// public/js/controllers/formCtrl.js

angular.module('floatingFormCtrl', [])

.controller('floatingFormController', function($scope,$rootScope, $routeParams,$mdDialog, Forms, table, parent_id, custom) {
    //show alert
    var parent = $rootScope;
    var link = table;
    if (custom !== "") {
      link = custom;
    }

    $scope.title = "Form " + table;
    $scope.form_table = "";
    $scope.parent_id = parent_id;
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

    //show alert
    $scope.searchData = function(ev) {
      $mdDialog.show({
           locals: {table : ev.currentTarget.attributes.table.value,
                    cond : "",
                    parent_id : $scope.form_id},
           controller: 'searchController',
           templateUrl: '/pages/modules/search.html',
           parent: angular.element(document.body),
           targetEvent: ev,
           clickOutsideToClose:true
         })
         .then(function(answer) {
            var index = ev.currentTarget.attributes.index.value;
            $scope.datas[index].value_id = answer['id'];
            $scope.datas[index].value = answer['alias'];

            //CUSTOM DAHULU SEMENTARA UNTUK FIELD YANG BERUBAH SAAT SELECT DATA
            if ($scope.form_table == 'payment') {
              if ($scope.datas[index].name == "packet") {
                $scope.datas[18].value = answer['extra_1'];
                $scope.calculate();
              }

              if ($scope.datas[index].name == "member") {
                $scope.datas[25].value = answer['extra_1'];
                $scope.calculate();
              }
            }

            // $scope.datas[index].$setDirty;
         }, function() {
            $scope.status = 'You cancelled the dialog.';
         });
    };

    //get table list ajax
    Forms.get('api/form/' + link)
      .success(function(data) {
        $scope.datas = data['forms'];
        $scope.form_table = data['form_table'];
      })
      .error(function(data) {
         $scope.loading = false;
         alert("data error");
      });

    $scope.save = function() {
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
                      'required': $scope.datas[i]['required']
                      };
             $scope.savedData[$scope.datas[i]['id']] = $scope.datas[i]['value_id'];
             $scope.savedData[$scope.datas[i]['name']] = $scope.datas[i]['value'];
             vals.push(val);
          } else {
            var val = {'name' : $scope.datas[i]['name'],
                      'type' : $scope.datas[i]['type'],
                      'value': $scope.datas[i]['value'],
                      'required': $scope.datas[i]['required']
                      };
             vals.push(val);
             $scope.savedData[$scope.datas[i]['name']] =  $scope.datas[i]['value'];
          }
        }


        if ("parent_table" in $scope.savedData) {
          var val = {'name' : "parent_table",
                    'type' : "parent",
                    'value': $scope.parent_table,
                    'required': true
                    };
           vals.push(val);

          $scope.savedData["parent_table"] = $scope.parent_table;
        }

        //if data has parent id (for contact / address in customer)
        if ("parent_id" in $scope.savedData) {
          var val = {'name' : "parent_id",
                    'type' : "parent",
                    'value': $scope.parent_id,
                    'required': true
                    };
           vals.push(val);

          $scope.savedData["parent_id"] = $scope.parent_id;
        }

        $scope.savedData['data'] = vals;

        if (custom == "") { // insert
          //save datas
          Forms.save('api/' + table, $scope.savedData)
            .success(function(result) {
              if(result.status == 'OK') {
                  // $scope.showAlert("Data Saved");
                  parent.saved_id = "1";
                  parent.saved_value = "2";
                  var value_search = result.value_search;
                  if (value_search != null) {
                    $mdDialog.hide(value_search);
                  } else {
                    $mdDialog.hide("");
                  }


              } else {
                // bootbox.alert(result.msg);
               $scope.showAlert(result.msg);
              }
               $scope.loading = false;
            })
            .error(function(data) {
               $scope.loading = false;
               $scope.showAlert(data.error);
            });

        } else { // update
          Forms.update('api/' + table + '/' + $scope.parent_id, $scope.savedData)
            .success(function(result) {
              if(result.status == 'OK') {
                  $window.location.href = result.url;
                  //  $scope.showAlert(result.tes);
              } else {
               //  bootbox.alert(result.msg);
               $scope.showAlert(result.msg);
              }
               $scope.loading = false;
            })
            .error(function(error, status) {
               $scope.loading = false;
               $scope.showAlert(error);
            });
        }
        // $mdDialog.hide("sudah disave");
      } else {
        angular.forEach($scope.textForm.$error, function (field) {
             angular.forEach(field, function(errorField){
                 errorField.$setTouched();
             })
         });
      }

        // if ($scope.textForm.$valid) {
        //   $scope.loading = true;
        //   $scope.savedData = {};
        //   for(i=0;i<$scope.datas.length;i++) {
        //     $scope.savedData[$scope.datas[i]['name']] = $scope.datas[i]['value'];
        //   }
        //
        //   //if data has parent id (for contact / address in customer)
        //   if ("parent_id" in $scope.savedData) {
        //     $scope.savedData["parent_id"] = $scope.parent_id;
        //   }
        //
        //   //save datas
        //   Forms.save('api/' + table, $scope.savedData)
        //     .success(function(result) {
        //       if(result.status == 'OK') {
        //           $scope.showAlert("Data Saved");
        //       } else {
        //        //  bootbox.alert(result.msg);
        //        $scope.showAlert(result.msg);
        //       }
        //        $scope.loading = false;
        //     })
        //     .error(function(data) {
        //        $scope.loading = false;
        //        alert("data error");
        //     });
        //   //  $mdDialog.hide(answer,id,value);
        // } else {
        //   angular.forEach($scope.textForm.$error, function (field) {
        //        angular.forEach(field, function(errorField){
        //            errorField.$setTouched();
        //        })
        //    });
        // }

    }


    $scope.cancel = function() {
      $mdDialog.cancel();
    }



});
