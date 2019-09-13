// public/js/controllers/listCtrl.js

angular.module('listCtrl', [])

.directive('arrowSelector',['$document',function($document){
    return{
        restrict:'A',
        link:function(scope,elem,attrs,ctrl){
            var elemFocus = false;
            elem.on('mouseenter',function(){
                elemFocus = true;
            });
            elem.on('mouseleave',function(){
                elemFocus = false;
            });
            $document.bind('keydown',function(e){
                if(elemFocus){
                    if(e.keyCode == 38){
                        console.log(scope.selected_id);
                        if(scope.selected_id == 0){
                            return;
                        }
                        scope.selected_id--;
                        scope.$apply();
                        e.preventDefault();
                    }
                    if(e.keyCode == 40){
                        if(scope.selected_id == scope.datas.length - 1){
                            return;
                        }
                        scope.selected_id++;
                        scope.$apply();
                        e.preventDefault();
                    }
                }
            });
        }
    };
}])

.controller('listController', function($window, $scope, $routeParams,$mdDialog, List, Excel, $timeout, $filter) {

    // get all the datas first and bind it to the $scope.datas object
    // use the function we created in our service
    // GET ALL DATAS ==============

    $scope.input_filter = "=";
    $scope.loading = true;
    $scope.form_name = $routeParams.form;
    $scope.skip = 1;
    $scope.hide_load_more = false;
    $scope.role = "";
    $scope.checkall = false;
    $scope.select_input = false;

    $scope.insert = false;
    $scope.update = false;
    $scope.delete = false;
    $scope.print = false;
    $scope.nav = false;



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

    $scope.export_excel = function() {
           var exportHref=Excel.tableToExcel("#table_data",'sheet name');
           $timeout(function(){location.href=exportHref;},100); // trigger download
    }

    $scope.export_csv = function() {
      $('#table_data').tableExport({type:'csv',pdfFontSize:'6',escape:'false'});
    }

    $scope.export_pdf = function() {
      $('#table_data').tableExport({type:'pdf',pdfFontSize:'6',escape:'false'});
    }

    //----------------------CUSTOM FUNCTION--------------------
    $scope.editRak = function(ev) {
      // $window.location.href = '/#/form/' + table;
      // $mdDialog.hide();
      $mdDialog.show({
         locals: {table : 'payment', parent_id : $('.selected').attr('id'), custom : 'payment-rak/update/' + $('.selected').attr('id') },
         controller: 'floatingFormController',
         templateUrl: '/pages/master/floating_form.html',
         parent: angular.element(document.body),
         targetEvent: ev,
         clickOutsideToClose:true,
         skipHide: true,
       })
       .then(function(answer, id, value) {
         alert(answer);
       }, function() {
         $scope.status = 'You cancelled the dialog.';
       });
    }
    //----------------------CUSTOM FUNCTION--------------------------

    //reset
    $scope.resetLoadMore = function() {
      $scope.skip = 1;
      if ($scope.datas.length <= 25) {
        $scope.hide_load_more = false;
      } else {
         $scope.hide_load_more = true;
      }
    };

    //reset
    $scope.checkedChange = function() {
      $('.tb_check').prop("checked", $('.checkall').prop('checked'));
    };

    $scope.autocompleteSearch = function() {
      if ($scope.input_filter == "=") {
        var result = $filter('getById')($scope.headers, $scope.input_search);
        if (result != null) {
          jQuery(".input_value").removeData('autocomplete');

          $( ".input_value" ).datepicker( "destroy" );
          $( ".input_value" ).removeClass("hasDatepicker").removeAttr('id');
          $( ".input_value" ).show();

            switch (result['type']) {
              case "select":
                //munculkan select, hide text
                $scope.select_input = true;
                $scope.selects = result['options'];
              break;
              case "boolean":
                //munculkan select, hide text
                $scope.select_input = true;
                $scope.selects = ['yes','no'];
              break;
              case "datetime":
                  $scope.select_input = false;
                $(".input_value").datepicker({
                  // minDate: 0
                  dateFormat: 'dd/mm/yy',
                  disabled : false
                });
              break;
              case "data":
                  $scope.select_input = false;
                  $(".input_value").autocomplete({
                    source: "api/quick-search/" + result['table'],
                    minLength: 0,
                    select: function( event, ui ) {
                      var index = $(this).attr('index');
                      $scope.input_value = ui.item.value;
                      //  $scope.datas[index].value_id = ui.item.id;
                      //  $scope.datas[index].value = ui.item.value;

                    }
                  }).focus(function(){
                    // alert($(".input_value").val());
                    if  ($(".input_value").data("autocomplete") != null) {
                        $(".input_value").data("autocomplete").search($(".input_value").val());
                    }
                  });
                break;
              default:
                  $scope.select_input = false;

            }

        }

      }

    }

    //get table list ajax
    List.get('api/' + $routeParams.form)
        .success(function(data) {
            $scope.headers = data['headers'];
            $scope.datas = data['datas'];
            $scope.count = data['count'];
            $scope.form_id = data['form_id'];
            $scope.role = data['role'];

            //check access
            List.get('api/access/' + $scope.form_id)
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
              .error(function(response) {

                 $scope.loading = false;
                 $scope.showAlert(response);
              });
            //check access

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

          // console.log(  $scope.access_list);
            $( ".input_search" ).autocomplete({
              source: $scope.headers,
              minLength: 0,
              select: function (event, ui) {
                  $scope.input_search = ui.item.label;
                  $( ".input_search" ).val(ui.item.label);
                  $scope.input_search_id = '';
                  $scope.input_search_column = ui.item.value;
                  $scope.input_search_type = ui.item.type;
                  $scope.input_search_table = ui.item.table;
                  $scope.autocompleteSearch();
                  return false;
              }
            }).focus(function(){
                    $(this).data("autocomplete").search($(this).val());
            });

            List.getFilter($scope.form_id)
              .success(function (data) {
                $scope.filter_datas = data;
                $scope.loading = false;
              })

        })
        .error(function(data) {
          List.get('remove-filter/' + $routeParams.form)
              .success(function(data) {});

           $scope.loading = false;
           $scope.showAlert(data);
        });

    //refresh data of table
    $scope.refreshData = function() {

      $scope.loading = true;
      List.get('api/' + $routeParams.form)
          .success(function(data) {
              $scope.headers = data['headers'];
              $scope.datas = data['datas'];
              $scope.count = data['count'];
              $scope.loading = false;
              $scope.resetLoadMore();

              console.log(data['test']);
          })
          .error(function(data) {
             $scope.loading = false;
             $scope.showAlert(data);
          });
    }

    //print data of selected row
    $scope.printData = function() {
      List.get('api/check-print/' + $scope.form_name + '/' + $('.selected').attr('id'))
          .success(function(data) {
            if (data.status == 'error') {
               $scope.showAlert(data.msg);
            } else {
                $window.location.href = '#/print/' + $scope.form_name + '/' + $('.selected').attr('id');
            }

        })
        .error(function(data) {
           $scope.loading = false;
           $scope.showAlert(data);
        });


    }

    //insert new data
    $scope.insertData = function() {
      $window.location.href = '#/form/' + $scope.form_name;
    }

    //update new data
    $scope.updateData = function() {
        if ($('.selected').attr('id') != undefined) {
          $window.location.href = '#/form/' + $scope.form_name + '/' + $('.selected').attr('id');
        }

    }

    //delete data
    $scope.deleteData = function(ev) {


      // Appending dialog to document.body to cover sidenav in docs app
        var confirm = $mdDialog.confirm()
              .title('Delete Record')
              .textContent('Would you like to delete this record?')
              .targetEvent(ev)
              .ok('Delete')
              .cancel('Cancel');

        $mdDialog.show(confirm).then(function() {
            $scope.loading = true;
          var count = $('.tb_check:checked').length;
          if (count > 0) {

              $scope.deletedData = {};
              var datas = [];
            $('.tb_check:checked').each(function(){
              datas.push($(this).attr('id'));
            })
            $scope.deletedData['data'] = datas;
            // console.log($scope.deletedData['data']);
            List.destroyMany('api/' + $routeParams.form + '-many', $scope.deletedData )
              .success(function(result) {
                if(result.status == 'OK') {
                     $scope.showAlert("Delete Success");
                     $scope.refreshData();
                } else {
                 //  bootbox.alert(result.msg);
                 $scope.showAlert(result.msg);
                }
                 $scope.loading = false;
              })
              .error(function(data) {
                 $scope.loading = false;
                 $scope.showAlert(data);
              });

          } else {
            List.destroy('api/' + $routeParams.form, $('.selected').attr('id'))
              .success(function(result) {
                if(result.status == 'OK') {
                     $scope.showAlert("Delete Success");
                     $scope.refreshData();
                } else {
                 //  bootbox.alert(result.msg);
                 $scope.showAlert(result.msg);
                }
                 $scope.loading = false;
              })
              .error(function(data) {
                 $scope.loading = false;
                 $scope.showAlert(data);
              });
          }

        }, function() {

        });
    }

    //select selected row
    $scope.selectRow = function(index) {
      // $('.table-list > tbody').find('.selected').removeClass('selected');
      // $(event.currentTarget).addClass('selected');
      // $scope.selected_id = event.currentTarget.id;
      $scope.selected_id = index;
    }

    //select selected row
    $scope.loadMore = function() {
      $scope.loading = true;
      List.get('api/' + $routeParams.form + '/' + $scope.skip)
          .success(function(data) {
              $scope.append = data['datas'];
              $scope.loading = false;
              $scope.datas  =  $scope.datas.concat($scope.append);
              $scope.skip  = $scope.skip + 1;
              if ($scope.append.length <25) {
                $scope.hide_load_more = true;
              }
          })
          .error(function(data) {
             $scope.loading = false;
             $scope.showAlert(data);
          });
    }

    //set sort
    $scope.sortColumn = function(event) {
         $scope.loading = true;
        // save the datas
        // use the function we created in our service
        var sort = event.currentTarget.attributes.sort.value;
        if (sort == "sort") {
          sort = "asc";
        } else if (sort == "asc") {
          sort = "desc";
        } else {
          sort = "asc";
        }
        $scope.filterData = {
                    'id' : event.currentTarget.id,
                    'category' : 'SORT',
                    'alias' : event.currentTarget.attributes.alias.value,
                    'column_name' : event.currentTarget.attributes.columnname.value,
                    'column_type' : '',
                    'column_table' : '',
                    'filter' : sort,
                    'value' : '',
                    'form_id' : $scope.form_id
        };

        List.setFilter($scope.filterData)
            .success(function(result) {
                // if successful, we'll need to refresh the datas list and table list
               if(result.status == 'OK') {
                 List.get('api/' + $routeParams.form)
                     .success(function(data) {
                         $scope.headers = data['headers'];
                         $scope.datas = data['datas'];
                         $scope.count = data['count'];
                         $scope.loading = false;
                         $scope.resetLoadMore();
                         List.getFilter($scope.form_id)
                           .success(function (data) {
                             $scope.filter_datas = data;
                           })
                     })
                     .error(function(data) {
                        $scope.loading = false;
                        $scope.showAlert(data);
                     });
               } else {
                //  bootbox.alert(result.msg);
                $scope.showAlert(result.msg);
               }
                $scope.loading = false;
            })
            .error(function(data) {
                //console.log(data);
            });
    };

    //set filter to filter block
    $scope.setFilter = function() {
         $scope.loading = true;
        // save the datas
        // use the function we created in our service
        var value = $scope.input_value;

        var result = $filter('getById')($scope.headers, $scope.input_search);
        if (result != null) {
          switch (result['type']) {
            case "datetime":
            value = $(".input_value").datepicker('getDate');
            value = $scope.getDate(value);
            break;
            case "boolean":
             if ($scope.input_value == 'yes') {
               value = 1;
             } else {
               value = 0;
             }
            break;
            default:
          }

        }

        $scope.filterData = {
                    'id' : $scope.input_search_id,
                    'category' : 'FILTER',
                    'alias' : $scope.input_search,
                    'column_name' : $scope.input_search_column,
                    'column_type' : $scope.input_search_type,
                    'column_table' : $scope.input_search_table,
                    'filter' : $scope.input_filter,
                    'value' : value,
                    'form_id' : $scope.form_id
        };

        List.setFilter($scope.filterData)
            .success(function(result) {
                // if successful, we'll need to refresh the datas list and table list
               if(result.status == 'OK') {
                 List.get('api/' + $routeParams.form)
                     .success(function(data) {
                         $scope.datas = data['datas'];
                         $scope.count = data['count'];
                         $scope.loading = false;
                         $scope.resetLoadMore();
                         List.getFilter($scope.form_id)
                           .success(function (data) {
                             $scope.filter_datas = data;
                           })
                     })
                     .error(function(data) {
                        $scope.loading = false;
                        $scope.showAlert(data);
                     });
               } else {
                //  bootbox.alert(result.msg);
                $scope.showAlert(result.msg);
               }
                $scope.loading = false;
            })
            .error(function(data) {
              //  console.log(data);
            });
    };

    // get filter from filter block
    $scope.getFilter = function(event) {
      $scope.input_search = event.currentTarget.attributes.columnalias.value;
      $scope.input_search_id = event.currentTarget.id;
      $scope.input_search_column = event.currentTarget.attributes.columnname.value;
      $scope.input_search_type = event.currentTarget.attributes.columntype.value;
      $scope.input_search_table = event.currentTarget.attributes.columntable.value;

      $scope.input_filter = event.currentTarget.attributes.filter.value;

      var value  = event.currentTarget.attributes.value.value;

      var result = $filter('getById')($scope.headers, $scope.input_search);
      if (result != null) {
        switch (result['type']) {
          case "datetime":

          break;
          case "boolean":
           if (value == 1) {
             value = 'yes';
           } else {
             value = 'no';
           }
          break;
          default:
        }

      }


      $scope.input_value = value;

      $scope.autocompleteSearch();
    }

    // destroy filter from filter block
    $scope.destroyFilter = function(event) {
      $scope.loading = true;
      List.destroyFilter(event.currentTarget.id)
          .success(function(result) {
            if(result.status == 'OK') {
                List.get('api/' + $routeParams.form)
                    .success(function(data) {
                        $scope.datas = data['datas'];
                        $scope.count = data['count'];
                        $scope.loading = false;
                        $scope.resetLoadMore();
                        List.getFilter($scope.form_id)
                          .success(function (data) {
                            $scope.filter_datas = data;
                          })
                    })
                    .error(function(data) {
                       $scope.loading = false;
                       $scope.showAlert(data);
                    });
              } else {
                bootbox.alert(result.msg);
              }
             $scope.loading = false;
          })
          .error(function(data) {
              //console.log(data);
          });
    }


});
