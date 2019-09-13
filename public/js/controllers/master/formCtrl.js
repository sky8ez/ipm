// public/js/controllers/formCtrl.js

angular.module('formCtrl', ['ngFileUpload'])

// .directive('myRepeatDirective', function() {
//   return function(scope, element, attrs) {
//     // angular.element(element).css('color','blue');
//     if (scope.$last){
//     //  window.alert("im the last!");
//       $('#datepicker1').datepicker();
//     }
//   };
// })

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

//untuk checking password sama atau tidak
.directive('pwCheck', [function () {
    return {
      require: 'ngModel',
      link: function (scope, elem, attrs, ctrl) {
        var firstPassword = '#' + attrs.pwCheck;
        elem.add(firstPassword).on('keyup', function () {
          scope.$apply(function () {
            var v = elem.val()===$(firstPassword).val();
            ctrl.$setValidity('pwmatch', v);
          });
        });
      }
    }
  }])

.controller('formController', function($scope, Upload, $routeParams, $mdDialog,$window, Forms) {

    // get all the datas first and bind it to the $scope.datas object
    // use the function we created in our service
    // GET RECORDS ==============

    $scope.loading = true;
    $scope.form_name = $routeParams.form;
    $scope.form_id = $routeParams.ID;
    $scope.form_table = "";
    $scope.data_before = "";
    $scope.role = "";
    $scope.category = "";
    $scope.prev = "";
    $scope.next = "";
    $scope.extra_menus = [];
    $scope.hide_button = false;

    $scope.insert = false;
    $scope.update = false;
    $scope.delete = false;
    $scope.print = false;
    $scope.nav = false;

    $scope.temp = [];

    if ($scope.form_id == null) {
      $scope.cond = "insert";
    } else {
      $scope.cond = "update";
    }

    $scope.testingval = 1;

    //---------------------------CUSTOM SECTION----------------------------------

    // $("img.lazy").lazyload({
    //     threshold : 200,
    //     effect : "fadeIn"
    // });



    $scope.changeFile = function(evt) {
      var tgt = evt.target || window.event.srcElement,
          files = tgt.files;

      // FileReader support
      if (FileReader && files && files.length) {
          var fr = new FileReader();
          fr.onload = function () {
              document.getElementById("imageCon").src = fr.result;
          }
          fr.readAsDataURL(files[0]);
      }
      // Not supported
      else {
          // fallback -- perhaps submit the input to an iframe and temporarily store
          // them on the server until the user's session ends.
      }
    }

    //print data of selected row
    $scope.printData = function() {
      $window.location.href = '#/print/' + $scope.form_name + '/' + $scope.form_id;
    }

    //print data of selected row
    $scope.printScreen = function() {
      $window.print();
    }


    // calculate untuk total kg, dan total satuan
    $scope.calculate = function() {
      if ($scope.form_table == 'project') {
        $scope.datas[4].value = parseFloat($scope.datas[3].value) * parseFloat($scope.datas[2].value);

        for(var i=0;i<$scope.datas[6].details.length;i++) {
          $scope.datas[6].details[i].qty_total = parseFloat($scope.datas[2].value) *  parseFloat($scope.datas[6].details[i].qty);
          $scope.datas[6].details[i].total = parseFloat($scope.datas[6].details[i].qty) *  parseFloat($scope.datas[6].details[i].price);
        }
      }

    }

    //custom function, jika dipilih transaction type = "packet", maka munculkan field paket dan customer,
    //serta buat menjadi mandatory,

    $scope.change_date = function(index) {
    }

    //jika dipilih payment type = "member",maka member muncul, pemilihan member menjadi mandatory, serta totalan kg menjadi 0 harganya
    $scope.change_select = function() {

    }


    //---------------------------CUSTOM SECTION----------------------------------

    $scope.previousData = function() {
       if ($scope.prev == "") {

       } else {
           $window.location.href =   $scope.prev;
       }

    }

    $scope.nextData = function() {
      if ($scope.next == "") {

      } else {
        $window.location.href =   $scope.next;
      }

    }


    $scope.addNewRow = function (index) {
      var row = {};
      for(i=0;i<$scope.datas[index].columns.length;i++) {
        row[$scope.datas[index].columns[i]['col_name']] = '';
      }
      $scope.datas[index].details.push(row);

      for(i=0;i<$scope.datas[index].details.length;i++) {
        $scope.datas[index].details[i]['sequence_no'] = (i+1);
      }
    }

    $scope.addNewRowCustom = function (index, value) {
      var row = {};
      for(i=0;i<$scope.datas[index].columns.length;i++) {
        if ($scope.datas[index].columns[i]['col_name'] == 'category') {
          row[$scope.datas[index].columns[i]['col_name']] = value;
          if (value == 'Expense') {
            row['color'] = '#e0b1b1';
          } else {
            row['color'] = '#b0dab0';
          }

        } else {
          row[$scope.datas[index].columns[i]['col_name']] = '';
        }

      }
      $scope.datas[index].details.push(row);

      for(i=0;i<$scope.datas[index].details.length;i++) {
        $scope.datas[index].details[i]['sequence_no'] = (i+1);
      }
    }

    $scope.deleteRow = function (parent_index,index) {
      var temp_row = $scope.datas[parent_index].details[index];
      if (temp_row['id'] != "") {
        $scope.datas[parent_index].deleted_details.push(temp_row);
      }
      $scope.datas[parent_index].details.splice(index,1);

      for(i=0;i<$scope.datas[parent_index].details.length;i++) {
        $scope.datas[parent_index].details[i]['sequence_no'] = (i+1);
      }

      $scope.calculate();
    }


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

    //show alert
    $scope.checkDataField = function(index) {
        if ($scope.datas[index].type == "data") {
          if ($scope.datas[index].value == "") {
            $scope.datas[index].value_id = "";
          }
        }
    }

    //show alert
    $scope.searchData = function(ev) {
      var cond1 = ev.currentTarget.attributes.cond.value;
      var index = ev.currentTarget.attributes.index.value;

      $mdDialog.show({
           locals: {table : ev.currentTarget.attributes.table.value,
                    cond : cond1,
                    parent_id : $scope.form_id},
           controller: 'searchController',
           templateUrl: '/pages/modules/search.html',
           parent: angular.element(document.body),
           targetEvent: ev,
           clickOutsideToClose:true,
           skipHide: true,
         })
         .then(function(answer) {

            $scope.datas[index].value_id = answer['id'];
            $scope.datas[index].value = answer['alias'];

            //CUSTOM DAHULU SEMENTARA UNTUK FIELD YANG BERUBAH SAAT SELECT DATA
            if ($scope.form_table == 'project') {
              if ($scope.datas[index].name == "item_name") {
                  //load default data
                  $scope.paramData = {};
                  $scope.paramData['item_id'] = $scope.datas[index].value_id;
                  Forms.refresh('api/' + $routeParams.form + '/load-default-detail', $scope.paramData)
                    .success(function(result) {
                      $scope.datas[6].details = result;
                      //  console.log(result);
                       $scope.loading = false;
                    })
                    .error(function(error, status) {
                       $scope.loading = false;
                       $scope.showAlert(error);
                    });
              }
            }

            // $scope.datas[index].$setDirty;
         }, function() {
            $scope.status = 'You cancelled the dialog.';
         });
    };

    //show display
    $scope.showDisplay = function( index) {
      $mdDialog.show({
           locals: {material_width : $scope.datas[8].details[index].width,
                    material_length : $scope.datas[8].details[index].qty,
                    x1 : $scope.datas[8].details[index].pattern_width1,
                    y1 : $scope.datas[8].details[index].pattern_height1,
                    total1 : $scope.datas[8].details[index].total1,
                    x2 : $scope.datas[8].details[index].pattern_width2,
                    y2 : $scope.datas[8].details[index].pattern_height2,
                    total2 : $scope.datas[8].details[index].total2
                   },
           controller: 'displayController',
           templateUrl: '/pages/modules/display.html',
           parent: angular.element(document.body),
          //  targetEvent: ev,
           clickOutsideToClose:true,
           skipHide: true,
         })
         .then(function(answer) {
            // $scope.datas[index].value_id = answer['id'];
            // $scope.datas[index].value = answer['alias'];


            // $scope.datas[index].$setDirty;
         }, function() {
            $scope.status = 'You cancelled the dialog.';
         });
    };


    //show alert
    $scope.searchDataDetail = function(ev, parent_index) {
      var cond1 = ev.currentTarget.attributes.cond.value;
      var index = ev.currentTarget.attributes.index.value;

      $mdDialog.show({
           locals: {table : ev.currentTarget.attributes.table.value,
                    cond : cond1,
                    parent_id : $scope.form_id},
           controller: 'searchController',
           templateUrl: '/pages/modules/search.html',
           parent: angular.element(document.body),
           targetEvent: ev,
           clickOutsideToClose:true,
           skipHide: true,
         })
         .then(function(answer) {
            //CUSTOM DAHULU SEMENTARA UNTUK FIELD YANG BERUBAH SAAT SELECT DATA
            if ($scope.form_table == 'user') {
              switch (ev.currentTarget.attributes.table.value) {
                case 'password-category':
                  $scope.datas[10].details[parent_index].password_category_id =  answer['id'];
                  $scope.datas[10].details[parent_index].password_category_name =  answer['alias'];

                  break;

                default:

              }

              //  $scope.calculate();
            }

            if ($scope.form_table == 'project') {
              switch (ev.currentTarget.attributes.table.value) {
                case 'material':
                  $scope.datas[6].details[parent_index].material_id =  answer['id'];
                  $scope.datas[6].details[parent_index].material_name =  answer['alias'];
                  $scope.datas[6].details[parent_index].material_width =  answer['extra_1'];
                  $scope.datas[6].details[parent_index].price =  answer['extra_2'];
                  break;
                case 'pattern':
                  $scope.datas[6].details[parent_index].pattern_id =  answer['id'];
                  $scope.datas[6].details[parent_index].pattern_name =  answer['alias'];
                  break;
                default:

              }

              //  $scope.calculate();
            }

            // $scope.datas[index].$setDirty;
         }, function() {
            $scope.status = 'You cancelled the dialog.';
         });
    };

    $scope.generatePassword = function() {
      $scope.loading = true;
      $scope.paramData = {};
      Forms.calculate('api/' + $routeParams.form + '/generate-password', $scope.paramData)
        .success(function(result) {
           $scope.datas[3].value = result['password'];
           $scope.loading = false;
        })
        .error(function(error, status) {
           $scope.loading = false;
           $scope.showAlert(error);
        });
    }

    $scope.generatePasswordRandomNumber = function() {
      $scope.loading = true;
      $scope.paramData = {};
      Forms.calculate('api/' + $routeParams.form + '/generate-password-random-number', $scope.paramData)
        .success(function(result) {
           $scope.datas[3].value = result['password'];
           $scope.loading = false;
        })
        .error(function(error, status) {
           $scope.loading = false;
           $scope.showAlert(error);
        });
    }

    $scope.calculateProject = function() {
      $scope.paramData = {};
      $scope.paramData['qty'] = 100;
      $scope.paramData['detail'] = $scope.datas[6].details;
      Forms.calculate('api/' + $routeParams.form + '/calculate-project', $scope.paramData)
        .success(function(result) {
          // if(result.status == 'OK') {
          //     $window.location.href = result.url;
          // } else {
          //  //  bootbox.alert(result.msg);
          //  $scope.showAlert(result.msg);
          // }
          $scope.datas[8].details = result.material_lists;
           $scope.datas[6].details = result.details;
           $scope.temp = result.datas;
           $scope.choosen_one = result.choosen_one;



           $scope.loading = false;
        })
        .error(function(error, status) {
           $scope.loading = false;
           $scope.showAlert(error);
        });
    }

    //show audit trail
    $scope.auditTrail = function(ev) {
      $mdDialog.show({
           locals: {  parent_category : $scope.form_name,
                      parent_id : $scope.form_id},
           controller: 'auditController',
           templateUrl: '/pages/modules/audit_trail.html',
           parent: angular.element(document.body),
           targetEvent: ev,
           clickOutsideToClose:true
         })
         .then(function(answer) {

            // $scope.datas[index].$setDirty;
         }, function() {
            $scope.status = 'You cancelled the dialog.';
         });
    };

    //duplicate value
    $scope.duplicate = function() {
        $scope.cond = "insert";
    };

    //---------------------------custom untuk payment----------------------------
    if ($scope.form_id == 'regular' || $scope.form_id == 'member' || $scope.form_id == 'new-member' || $scope.form_id == 'extend-member') {
        $scope.category = $scope.form_id;
      // $scope.form_id = null;
      $scope.cond = "insert";
    }
    //---------------------------custom untuk payment----------------------------



    //duplicate value
    $scope.getData = function(is_duplicate) {
      Forms.get('api/form/' + $routeParams.form + "/" + $scope.cond + "/" + $scope.form_id)
        .success(function(data) {
          $scope.loading = true;

          //password list
          if (data['form_id'] == "PASSWORD-LIST" && $scope.cond !== "insert") {
            Forms.get('api/access-password/' + data['form_id'] + "/"  + $scope.form_id)
              .success(function(data2) {
                $scope.loading = true;
                if (data2 == "true") {

                  //ada akses
                  $scope.datas = data['forms'];
                  $scope.datas_alias = data['forms_alias'];

                  $scope.form_table = data['form_table'];
                  $scope.data_before = data['data_before'];
                  $scope.role = data['role'];

                  $scope.prev =  data['prev_id'];
                  $scope.next =  data['next_id'];
                } else {
                  //tidak ada akses
                  $scope.showAlert("you don't have access to this page");
                  window.location.href = "/#/";
                }
                $scope.loading = false;
              })
              .error(function(data) {
                 $scope.loading = false;
                 $scope.showAlert(data);
              });
          } else {
              $scope.datas = data['forms'];
              $scope.datas_alias = data['forms_alias'];

              $scope.form_table = data['form_table'];
              $scope.data_before = data['data_before'];
              $scope.role = data['role'];

              $scope.prev =  data['prev_id'];
              $scope.next =  data['next_id'];
          }



          //check access
          Forms.get('api/access/' + data['form_id'])
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
               $scope.showAlert(data);
            });


          //check access

          $scope.extra_menus =  data['extra_menus'];

          if (data['hide_button'] != null) {
            $scope.hide_button = data['hide_button'];
          }

          if ($scope.cond != "insert") {//trigger event jika update
            $scope.change_select();
            $scope.calculate();
          } else {
            if ($scope.form_table == 'closing_day') {
                  $scope.calculate();
            }

            if ($scope.form_table == 'payment') {
              if ($scope.category !== "") {
                switch ($scope.category) {
                    case "member":
                      $scope.datas[3].value  = "member";
                      break;
                    case "new-member":
                      $scope.datas[3].value  = "packet";
                      break;
                    case "extend-member":
                      $scope.datas[3].value  = "extend-packet";
                      break;
                  default:

                }
                $scope.change_select();
              }
            }
          }


          $scope.access_list = data['access_list'];
          for(i=0;i<$scope.access_list.length;i++) {
            switch ($scope.access_list[i]['condition']) {
              case 'insert':
                $scope.insert = $scope.access_list[i]['cond_flag'];
              break;
              case 'update':
                $scope.update = $scope.access_list[i]['cond_flag'];
              break;
              case 'delete':
                $scope.delete = $scope.access_list[i]['cond_flag'];
              break;
              case 'print':
                $scope.print = $scope.access_list[i]['cond_flag'];
              break;
              case 'nav':
                $scope.nav = $scope.access_list[i]['cond_flag'];
              break;
              default:
            }
          }


          // $('[data-toggle="datepicker"]').datepicker({
          //     format: 'yyyy-mm-dd'
          //   });

          //FOR DATA AUTO COMPLETE
          var selector = '.tb_data';
          $(document).on('keydown.autocomplete', selector, function() {
              var index = $(this).attr('index');
            var cond1 = "";
            if ($scope.form_table == 'member') {
              if ($scope.datas[index].name == "member_added") {
                cond1 = cond1 + " and customer_id = '" + $scope.datas[0].value_id + "'"
              }
            }

            if ($scope.form_table == 'payment') {
              if ($scope.datas[index].name == "added_member") {
                cond1 = cond1 + " and customer_id = '" + $scope.datas[5].value_id + "'"
              }
            }

              $(this).autocomplete({
                source: "api/quick-search/" + $(this).attr('table') + '/' + $scope.form_id + '/' + $(this).attr('cond') + cond1,
                minLength: 0,
                select: function( event, ui ) {

                   $scope.datas[index].value_id = ui.item.id;
                   $scope.datas[index].value = ui.item.value;

                   //CUSTOM DAHULU SEMENTARA UNTUK FIELD YANG BERUBAH SAAT SELECT DATA
                   if ($scope.form_table == 'payment') {
                     if ($scope.datas[index].name == "packet") {
                       $scope.datas[18].value = ui.item.extra_1;
                       $scope.calculate();
                     }
                   }


                }
              }).focus(function(){
                  // The following works only once.
                  // $(this).trigger('keydown.autocomplete');
                  // As suggested by digitalPBK, works multiple times
                  $(this).data("autocomplete").search($(this).val());
              });
          });
          //FOR DATA AUTO COMPLETE
          $scope.loading = false;
        })
        .error(function(data) {
           $scope.loading = false;
           $scope.showAlert(data);
        });
    };

    $scope.getData(false);


    //show alert
    $scope.saveData = function() {
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
                        'hide' : $scope.datas[i]['hide'],
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
                        'hide' : $scope.datas[i]['hide'],
                        'type' : $scope.datas[i]['type'],
                        'value': value,
                        'required': $scope.datas[i]['required'],
                        'unique': $scope.datas[i]['unique']
                        };
               vals.push(val);
               $scope.savedData[$scope.datas[i]['name']] =  value;
         } else if ($scope.datas[i]['type'] == 'image') {
           var val = {'name' : $scope.datas[i]['name'],
                    'hide' : $scope.datas[i]['hide'],
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
                    'hide' : $scope.datas[i]['hide'],
                     'type' : $scope.datas[i]['type'],
                     'columns' : $scope.datas[i]['columns'],
                     'details' : $scope.datas[i]['details'],
                     'deleted_details' : $scope.datas[i]['deleted_details'],
                     'required': $scope.datas[i]['required'],
                     'unique': $scope.datas[i]['unique']
                     };
                    //  console.log(val);
            vals.push(val);
            $scope.savedData[$scope.datas[i]['name']] =  value;
           } else if ($scope.datas[i]['type'] == 'access-menu') {
             var val = {'name' : $scope.datas[i]['name'],
                      'hide' : $scope.datas[i]['hide'],
                       'type' : $scope.datas[i]['type'],
                       'table': $scope.datas[i]['table'],
                       'value': $scope.datas[i]['menu_detail'],
                       'required': $scope.datas[i]['required'],
                       'unique': $scope.datas[i]['unique']
                       };

                      //  console.log($scope.datas[i]['menu_detail']);
              vals.push(val);
              $scope.savedData[$scope.datas[i]['name']] =  $scope.datas[i]['menu_detail'];
            } else {
              var val = {'name' : $scope.datas[i]['name'],
                        'hide' : $scope.datas[i]['hide'],
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


          if ($scope.cond == "insert") {
            //save datas
            if ($scope.form_name == "file-manager") {
                // $scope.upload($('#fileinput').prop('files')[0], $scope.savedData);
                Upload.upload({
                    url: 'api/' + $routeParams.form,
                    data: {file: $('#fileinput').prop('files')[0], 'data': $scope.savedData}
                }).then(function (resp) {
                  // console.log('Success ' + resp.config.data.file.name + 'uploaded. Response: ' + resp.data);
                    if(resp.data.status == 'OK') {
                        $window.location.href = resp.data.url;
                    } else {
                     //  bootbox.alert(result.msg);
                     $scope.showAlert(resp.data.msg);
                    }
                     $scope.loading = false;

                }, function (resp) {
                    console.log('Error status: ' + resp.status);
                }, function (evt) {
                    var progressPercentage = parseInt(100.0 * evt.loaded / evt.total);
                    console.log('progress: ' + progressPercentage + '% ' + evt.config.data.file);
                });

            } else {
              Forms.save('api/' + $routeParams.form, $scope.savedData)
                .success(function(result) {
                  if(result.status == 'OK') {
                      $window.location.href = result.url;
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

          } else {
            //save datas
              if ($scope.form_name == "file-manager") {
                  Upload.upload({
                      url: 'api/' + $routeParams.form  + '/' + $scope.form_id,
                      data: {file: $('#fileinput').prop('files')[0], 'data': $scope.savedData}
                  }).then(function (resp) {
                    // console.log('Success ' + resp.config.data.file.name + 'uploaded. Response: ' + resp.data);
                      if(resp.data.status == 'OK') {
                          $window.location.href = resp.data.url;
                      } else {
                       //  bootbox.alert(result.msg);
                       $scope.showAlert(resp.data.msg);
                      }
                       $scope.loading = false;

                  }, function (resp) {
                      console.log('Error status: ' + resp.status);
                  }, function (evt) {
                      var progressPercentage = parseInt(100.0 * evt.loaded / evt.total);
                      console.log('progress: ' + progressPercentage + '% ' + evt.config.data.file);
                  });
              } else {
                console.log($scope.savedData);
                Forms.update('api/' + $routeParams.form + '/' + $scope.form_id, $scope.savedData)
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
              }


        } else {
          angular.forEach($scope.textForm.$error, function (field) {
               angular.forEach(field, function(errorField){
                   errorField.$setTouched();
               })
           });
        }
    };

});
