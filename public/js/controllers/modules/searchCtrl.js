// public/js/controllers/formCtrl.js

angular.module('searchCtrl', [])


.controller('searchController', function($scope,$window, $routeParams,$mdDialog, Forms, table, cond, parent_id) {

    //show alert
    $scope.savedData = {};

    $scope.title = "Search " + table;
    $scope.parent_id = parent_id;
    $scope.cond = cond;

    if ($scope.parent_id == "") {
      $scope.parent_id = "0";
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

    $scope.savedData['parent_id'] = $scope.parent_id;
    $scope.savedData['skip'] = 0;
    $scope.savedData['column'] = '';
    $scope.savedData['filter'] = '';
    $scope.savedData['cond'] = $scope.cond;
    Forms.refresh('api/search/' + table, $scope.savedData)
      .success(function(data) {
        $scope.headers = data['headers'];
        $scope.data_search = data['datas'];

        $scope.cmb_search = $scope.headers[0].value;
      })
      .error(function(data) {
         $scope.loading = false;
        //  $scope.showAlert(data.error);
        console.log(data);
      });

    // Forms.get('api/search/' + table + '/' + $scope.parent_id)
    //   .success(function(data) {
    //     $scope.headers = data['headers'];
    //     $scope.data_search = data['datas'];
    //
    //     $scope.cmb_search = $scope.headers[0].value;
    //   })
    //   .error(function(data) {
    //      $scope.loading = false;
    //     // alert("data error");
    //   });

    $scope.filterSearch = function(event) {
      $scope.loading = true;
       $scope.skip = 0;
       $scope.savedData = {};

       $scope.savedData['parent_id'] = $scope.parent_id;
       $scope.savedData['skip'] = $scope.skip;
       $scope.savedData['column'] = $scope.cmb_search;
       $scope.savedData['filter'] = $scope.tb_search;
       $scope.savedData['cond'] = $scope.cond;

       Forms.save('api/search/' + table, $scope.savedData)
         .success(function(data) {
             $scope.headers = data['headers'];
             $scope.data_search = data['datas'];
             if ($scope.data_search.length == 10) {
               $scope.hide_load_more = false;
             } else {
                $scope.hide_load_more = true;
             }
            $scope.loading = false;
            $scope.skip = 1;
            $scope.loading = false;
         })
         .error(function(data) {
            $scope.loading = false;
            $scope.showAlert(data);
         });

      // Forms.get('api/search/' + table + '/'  + $scope.parent_id + "/" + $scope.skip + "/" + $scope.cmb_search + "/" + $scope.tb_search)
      //   .success(function(data) {
      //     $scope.headers = data['headers'];
      //     $scope.data_search = data['datas'];
      //     if ($scope.data_search.length == 10) {
      //       $scope.hide_load_more = false;
      //     } else {
      //        $scope.hide_load_more = true;
      //     }
      //      $scope.loading = false;
      //      $scope.skip = 1;
      //   })
      //   .error(function(data) {
      //      $scope.loading = false;
      //     // alert("data error");
      //   });

    }

    $scope.loadMore = function(event) {
      $scope.loading = true;

      $scope.savedData['parent_id'] = $scope.parent_id;
      $scope.savedData['skip'] = $scope.skip;
      $scope.savedData['column'] = $scope.cmb_search;
      $scope.savedData['filter'] = $scope.tb_search;
      $scope.savedData['cond'] = $scope.cond;
      Forms.refresh('api/search/' + table, $scope.savedData)
        .success(function(data) {
            $scope.append = data['datas'];
            $scope.loading = false;
            $scope.data_search  =  $scope.data_search.concat($scope.append);
            $scope.skip  = $scope.skip + 1;
            if ($scope.append.length <=10) {
              $scope.hide_load_more = true;
            }
        })
        .error(function(data) {
           $scope.loading = false;
           $scope.showAlert(data);
        });

      // Forms.get('api/search/' + table + '/'  + $scope.parent_id  + "/" + $scope.skip + "/" + $scope.cmb_search + "/" + $scope.tb_search)
      //   .success(function(data) {
      //     $scope.append = data['datas'];
      //     $scope.loading = false;
      //     $scope.data_search  =  $scope.data_search.concat($scope.append);
      //     $scope.skip  = $scope.skip + 1;
      //     if ($scope.append.length <=10) {
      //       $scope.hide_load_more = true;
      //     }
      //   })
      //   .error(function(data) {
      //      $scope.loading = false;
      //     // alert("data error");
      //   });

    }

    //select selected row
    $scope.selectRow = function(event) {
      $('.table-search > tbody').find('.selected').removeClass('selected');
      $(event.currentTarget).addClass('selected');
      $scope.selected_id = event.currentTarget.id;
    }

    $scope.newData = function(ev) {
        // $window.location.href = '/#/form/' + table;
        // $mdDialog.hide();
        $mdDialog.show({
           locals: {table : table, custom : "", parent_id : $scope.parent_id},
           controller: 'floatingFormController',
           templateUrl: '/pages/master/floating_form.html',
           parent: angular.element(document.body),
           targetEvent: ev,
           clickOutsideToClose:true,
           skipHide: true
         })
         .then(function(answer, id, value) {
            $scope.tb_search = answer;
          //  alert("ok");
            $scope.filterSearch();
         }, function() {
           $scope.status = 'You cancelled the dialog.';
         });
    }

    $scope.setAnswer = function(answer, id , value) {
        answer = {'id' : $scope.selected_id,
                  'alias' : $('.' + $scope.selected_id + 'search').find('.row_alias').html(),
                  'extra_1' : $('.' + $scope.selected_id + 'search').find('.extra_1').html(),
                  'extra_2' : $('.' + $scope.selected_id + 'search').find('.extra_2').html(),
                  'extra_3' : $('.' + $scope.selected_id + 'search').find('.extra_3').html()};

        $mdDialog.hide(answer);
    }

    $scope.cancel = function() {
      $mdDialog.cancel();
    }



});
