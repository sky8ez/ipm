// public/js/controllers/formCtrl.js

angular.module('printDetailCtrl', [])

.directive('colorpicker', function() {
  return function($scope, element) {
    element.colorpicker();
  };
})

.directive('autocomplete1', function() {
  return function($scope, element) {
    element.autocomplete({
      source: $scope.columns_detail
    });
  };
})





.controller('printDetailController', function($scope,$window, $routeParams,$mdDialog, Forms, table, parent_id ,columns_detail) {

    //show alert
    $scope.title = "Detail Print";
    $scope.parent_id = parent_id;
    $scope.columns_detail = columns_detail;

    $scope.data = table;
    $scope.tb_image_flag = ($scope.data['imageFlag'] == "true");
    $scope.tb_type = $scope.data['kind'];
    $scope.tb_header_text = $scope.data['headerText'];
    $scope.tb_value_type = $scope.data['valueType'];
    $scope.tb_value_format = $scope.data['valueFormat'];
    $scope.tb_value = $scope.data['value'];
    $scope.tb_line_height = $scope.data['lineHeight'];
    $scope.tb_padding_x = $scope.data['paddingX'];
    $scope.tb_padding_y = $scope.data['paddingY'];
    $scope.tb_top = $scope.data['top'];
    $scope.tb_left = $scope.data['left'];
    $scope.tb_width = $scope.data['width'];
    $scope.tb_height = $scope.data['height'];
    $scope.tb_font_size = $scope.data['fontSize'];
    $scope.tb_font_family = $scope.data['fontFamily'];
    $scope.tb_font_color = $scope.data['fontColor'];
    $scope.tb_use_parent_font = ($scope.data['useParentFontFlag'] == "true");
    $scope.tb_font_style = $scope.data['fontStyle'];
    $scope.tb_font_weight = $scope.data['fontWeight'];
    $scope.tb_align_h = $scope.data['horizontalAlign'];
    $scope.tb_align_v = $scope.data['verticalAlign'];
    $scope.tb_border_flag = ($scope.data['borderFlag'] == "true");
    $scope.tb_border_width = $scope.data['borderWidth'];
    $scope.tb_border_color = $scope.data['borderColor'];
    $scope.tb_border_style = $scope.data['borderStyle'];





  //
    // $('#tb_border_color').colorpicker();

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

    $scope.browseImage = function(ev) {
      $mdDialog.show({
           locals: {table : "file",
                    cond : "",
                    parent_id : 0},
           controller: 'searchController',
           templateUrl: '/pages/modules/search.html',
           parent: angular.element(document.body),
           targetEvent: ev,
           clickOutsideToClose:true,
           skipHide: true
         })
         .then(function(answer) {
          //  console.log(answer);
           $scope.tb_value = answer['extra_1'];
            // var index = ev.currentTarget.attributes.index.value;
            // $scope.datas[index].value_id = answer['id'];
            // $scope.datas[index].value = answer['alias'];

            // $scope.datas[index].$setDirty;
         }, function() {
            $scope.status = 'You cancelled the dialog.';
         });
    }


    // Forms.get('api/detail/' + table + '/' + $scope.parent_id)
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

    $scope.setAnswer = function(answer, id , value) {
        $scope.tb_font_color= $('#tb_font_color').val();
        $scope.tb_border_color= $('#tb_border_color').val();
        answer = {'kind' : $scope.tb_type,
                  'headerText' : $scope.tb_header_text,
                  'valueType' : $scope.tb_value_type,
                  'valueFormat' : $scope.tb_value_format,
                  'value' : $scope.tb_value,
                  'lineHeight' : $scope.tb_line_height,
                  'paddingX' : $scope.tb_padding_x,
                  'paddingY' : $scope.tb_padding_y,
                  'top' : $scope.tb_top,
                  'left' : $scope.tb_left,
                  'width' : $scope.tb_width,
                  'height' : $scope.tb_height,
                  'fontSize' : $scope.tb_font_size,
                  'fontFamily' : $scope.tb_font_family,
                  'fontColor' : $scope.tb_font_color,
                  'useParentFontFlag' : $scope.tb_use_parent_font,
                  'fontStyle' : $scope.tb_font_style,
                  'fontWeight' : $scope.tb_font_weight,
                  'horizontalAlign' : $scope.tb_align_h,
                  'verticalAlign' : $scope.tb_align_v,
                  'borderFlag' : $scope.tb_border_flag,
                  'borderWidth' : $scope.tb_border_width,
                  'borderColor' : $scope.tb_border_color,
                  'borderStyle' : $scope.tb_border_style,
                  'imageFlag' : $scope.tb_image_flag
                };
        $mdDialog.hide(answer);
    }

    $scope.cancel = function() {
      $mdDialog.cancel();
    }



});
