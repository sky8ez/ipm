// public/js/controllers/formCtrl.js

angular.module('editorCtrl', [])

// .directive('myRepeatDirective', function() {
//   return function(scope, element, attrs) {
//     // angular.element(element).css('color','blue');
//     if (scope.$last){
//     //  window.alert("im the last!");
//       $('#datepicker1').datepicker();
//     }
//   };
// })

.directive('colResizeable', function() {
  return {
    restrict: 'A',
    link: function(scope, elem) {
      setTimeout(function() {
        elem.colResizable({
          liveDrag: true,
          gripInnerHtml: "<div class='grip'></div>",
          draggingClass: "dragging",
          onDrag: function() {
            //trigger a resize event, so width dependent stuff will be updated
            $(window).trigger('resize');
          }
        });
      });
    }
  }
})

.controller('editorController', function($scope, $routeParams, $mdDialog,$window, Editor) {

    // get all the datas first and bind it to the $scope.datas object
    // use the function we created in our service
    // GET RECORDS ==============

    $scope.loading = false;
    $scope.form_name = $routeParams.form;
    $scope.form_id = $routeParams.ID;
    $scope.form_table = "";
    $scope.data_before = "";
    $scope.hide_table = false;

    $scope.selected = $([]), offset = {top:0, left:0}; //to store selected object

    $scope.columns_header = [];
    $scope.columns_detail = [];

    if ($scope.form_id == null) {
      $scope.cond = "insert";
    } else {
      $scope.cond = "update";
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

    $scope.updateColumnDetail = function(datas) {
      $mdDialog.show({
           locals: {table :datas,
                    parent_id : $scope.form_id,
                    columns_detail : $scope.columns_detail},
           controller: 'printDetailController',
           templateUrl: '/pages/modules/print_detail.html',
           parent: angular.element(document.body),
          //  targetEvent: ev,
           clickOutsideToClose:true
         })
         .then(function(answer) {
            var column = $('th.col-selected');
            column.html(answer['headerText']);
            column.css("width", answer['width']);
            column.attr("valuetype", answer['valueType']);
            column.attr("valueformat", answer['valueFormat']);
            column.attr("value", answer['value']);
            column.attr("lineheight", answer['lineHeight']);
            column.attr("paddingx", answer['paddingX']);
            column.attr("paddingy", answer['paddingY']);

            column.attr("fontsize", answer['fontSize']);
            column.attr("fontfamily", answer['fontFamily']);
            column.attr("fontcolor", answer['fontColor']);
            column.attr("fontflag", answer['useParentFontFlag']);
            column.attr("fontstyle",answer['fontStyle']);
            column.attr("fontweight", answer['fontWeight']);
            column.attr("textalign", answer['horizontalAlign']);
            column.attr("verticalalign", answer['verticalAlign']);


            // $scope.datas[index].$setDirty;
         }, function() {
            $scope.status = 'You cancelled the dialog.';
         });
    };


    $scope.updateDetail = function(datas) {
      $mdDialog.show({
           locals: {table :datas,
                    parent_id : $scope.form_id,
                  columns_detail : []},
           controller: 'printDetailController',
           templateUrl: '/pages/modules/print_detail.html',
           parent: angular.element(document.body),
          //  targetEvent: ev,
           clickOutsideToClose:true
         })
         .then(function(answer) {
            // var index = ev.currentTarget.attributes.index.value;
            // $scope.datas[index].value_id = answer['id'];
            // $scope.datas[index].value = answer['alias'];
            //
            // //CUSTOM DAHULU SEMENTARA UNTUK FIELD YANG BERUBAH SAAT SELECT DATA
            // if ($scope.form_table == 'payment') {
            //   if ($scope.datas[index].name == "packet") {
            //     $scope.datas[18].value = answer['extra_1'];
            //     $scope.calculate();
            //   }
            //
            //   if ($scope.datas[index].name == "member") {
            //     $scope.datas[25].value = answer['extra_1'];
            //     $scope.calculate();
            //   }
            // }
          //  var column = $('th.col-selected');
            var row = $('.selected');
            row.find("textarea").val(answer['value']);
            row.css("top",answer['top']);
            row.css("left",answer['left']);
            row.css("width",answer['width']);
            row.css("height",answer['height']);
            row.find("textarea").css("font-size",answer['fontSize']);
            row.find("textarea").css("font-family",answer['fontFamily']);
            row.find("textarea").css("color",answer['fontColor']);
            row.data("kind",answer['kind']);
            row.data("parent_flag",answer['useParentFontFlag']);
            row.find("textarea").css("font-style",answer['fontStyle']);
            row.find("textarea").css("font-weight",answer['fontWeight']);
            row.find("textarea").css("text-align",answer['horizontalAlign']);
            row.find("textarea").css("vertical-align",answer['verticalAlign']);

            row.css("border",answer['borderWidth'] + ' ' + answer['borderColor'] + ' ' + answer['borderStyle']);


            row.data("border_flag",answer['borderFlag']);
            row.attr("valuetype",answer['valueType']);
            row.attr("valueformat",answer['valueFormat']);
            row.attr("image_flag",answer['imageFlag']);

            if (answer['imageFlag'] == true) {
              row.css("background-image","url('" + answer['value'] + "')");
              row.css("background-size","cover");
              row.css("color","transparent");
            } else {
              row.css("background-image","");
              row.css("background-size","cover");
                row.css("color","black");
            }

            // $scope.datas[index].$setDirty;
         }, function() {
            $scope.status = 'You cancelled the dialog.';
         });
    };



    $scope.save = function() {
        // $("#data_header").val(JSON.stringify(getDataHeader()));
        // $("#data_detail").val(JSON.stringify(getDataDetail()));
        $scope.savedData = {};

          $scope.data_header = JSON.stringify($scope.getDataHeader());
         $scope.data_detail = JSON.stringify($scope.getDataDetail());

         //console.log($scope.getDataDetail());

          // console.log($scope.data_detail);

           $scope.savedData['header'] = $scope.data_header;
           $scope.savedData['detail'] = $scope.data_detail;

           //save datas
           Editor.save('api/' + $routeParams.form + '/' + $scope.form_id + '/editor', $scope.savedData)
             .success(function(result) {
               if(result.status == 'OK') {
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

    $( "#print_form" ).selectable({
      selected : function(event, ui) {
          if($(ui.selected).hasClass('p_textbox') == false && $(ui.selected).hasClass('p_label') == false
          && $(ui.selected).hasClass('p_border') == false){
            $(ui.selected).removeClass('ui-selected');
          }


        },
        stop: function( event, ui ) {
          $('.p_textbox').resizable( "option", "alsoResize", ".ui-selected" );
          $('.p_label').resizable( "option", "alsoResize", ".ui-selected" );
          $('.p_border').resizable( "option", "alsoResize", ".ui-selected" );
        }
    });

    // manually trigger the "select" of clicked elements
    $( "#print_form" ).on('click','.p_textbox', function(e){
      if (e.metaKey == false) {
          // if command key is pressed don't deselect existing elements
          $( "#print_form > .p_textbox" ).removeClass("ui-selected");
          $(this).addClass("ui-selecting");
      }
      else {
          if ($(this).hasClass("ui-selected")) {
              // remove selected class from element if already selected
              $(this).removeClass("ui-selected");
          }
          else {
              // add selecting class if not
              $(this).addClass("ui-selecting");
          }
      }

      // $( "#print_form" ).data("selectable")._mouseStop(null);
    });

    $scope.getDataHeader = function() {
      var table_con = $('#table_container');
      var results = {'table_row_height' : $('#table1 tbody').find('tr:first-child').css("height"),
                     'table_top' : $('#table_container').css("top"),
                     'table_row_count' : $('#table1 tbody tr').length,
                     'table_border_style' : $('#table1').data("borderstyle")};
      return results;
    }

    $scope.getDataDetail = function(TableId) {
        var results = [];
        $("#print_form .p_textbox").each(function() {
          $this = $(this)
          var p_id = $this.attr("id");
          if (p_id == null) {
            p_id = "";
          }
          var deleteflag = "";
          if ($this.css("display") == "none") {
            deleteflag = "true"
          } else {
            deleteflag = "false"
          }
          var p_kind= $this.data("kind");
          var p_value = $this.find("textarea").val();
          var p_top = $this.css("top");
          var p_footer_top = $this.position().top - ($('#table_container').position().top + $('#table_container').height()) + 'px';
          var p_left = $this.css("left");
          var p_width = $this.css("width");
          var p_height = $this.css("height");
          var p_horizontal_align = $this.find("textarea").css("text-align");
          var p_vertical_align = $this.find("textarea").css("vertical-align");
          var p_padding_x = $this.css("padding-top");
          var p_padding_y = $this.css("padding-left");
          var p_line_height = $this.find("textarea").css("line-height");
          var p_font_weight = $this.find("textarea").css("font-weight");
          var p_font_style = $this.find("textarea").css("font-style");
          var p_font_size = $this.find("textarea").css("font-size");
          var p_font_family = $this.find("textarea").css("font-family");
          var p_font_color = $this.find("textarea").css("color");
          var p_use_parent_flag = $this.data("parent_flag");
          var p_value_type = $this.attr("valuetype");
          var p_value_format = $this.attr("valueformat");
          var p_border_flag = $this.data("border_flag");
          var p_border_width = $this.css("border-top-width");
          var p_border_color = $this.css("border-top-color");
          var p_border_style = $this.css("border-top-style");

          result_prop = {'footer_top' : p_footer_top,'top' : p_top, 'left' : p_left,
                        'width' : p_width, 'height' : p_height,'horizontal_align' : p_horizontal_align, 'vertical_align' : p_vertical_align,
                        'padding_x': p_padding_x, 'padding_y' : p_padding_y,
                        'line_height' : p_line_height,'font_weight' : p_font_weight, 'font_style' : p_font_style, 'font_size' : p_font_size, 'font_family' : p_font_family,
                        'font_color' : p_font_color, 'use_parent_font_flag' : p_use_parent_flag, 'border_flag' : p_border_flag,
                        'border_width' : p_border_width, 'border_color' : p_border_color, 'border_style' : p_border_style,
                        'value_type' : p_value_type, 'value_format' : p_value_format}

          var result = {'sequence_no' : 0, 'deleteflag' : deleteflag,'kind' : p_kind, 'type' : 'textbox', 'id' : p_id, 'value' : p_value,'value_type' : p_value_type, 'value_format' : p_value_format, 'properties' : result_prop};
          results.push(result);
        });


        $("#print_form .p_label").each(function() {
          $this = $(this)
          var p_id = $this.attr("id");
          if (p_id == null) {
            p_id = "";
          }
          var deleteflag = "";
          if ($this.css("display") == "none") {
            deleteflag = "true"
          } else {
            deleteflag = "false"
          }
          var p_value = $this.find("textarea").val();
          var p_image_flag = $this.attr("image_flag");
          var p_top = $this.css("top");
          var p_footer_top = $this.position().top - ($('#table_container').position().top + $('#table_container').height()) + 'px';
          var p_left = $this.css("left");
          var p_width = $this.css("width");
          var p_height = $this.css("height");
          var p_kind = $this.data("kind");
          var p_horizontal_align = $this.find("textarea").css("text-align");
          var p_vertical_align = $this.find("textarea").css("vertical-align");
          var p_padding_x = $this.css("padding-top");
          var p_padding_y = $this.css("padding-left");
          var p_line_height = $this.find("textarea").css("line-height");
          var p_font_weight = $this.find("textarea").css("font-weight");
          var p_font_style = $this.find("textarea").css("font-style");
          var p_font_size = $this.find("textarea").css("font-size");
          var p_font_family = $this.find("textarea").css("font-family");
          var p_font_color = $this.find("textarea").css("color");
          var p_use_parent_flag = $this.data("parent_flag");
          var p_value_type = $this.attr("valuetype");
          var p_value_format = $this.attr("valueformat");
          var p_border_flag = $this.data("border_flag");
          var p_border_width = $this.css("border-top-width");
          var p_border_color = $this.css("border-top-color");
          var p_border_style = $this.css("border-top-style");

          var result_prop = { 'footer_top' : p_footer_top, 'top' : p_top, 'left' : p_left,
                        'width' : p_width, 'height' : p_height, 'horizontal_align' : p_horizontal_align, 'vertical_align' : p_vertical_align,
                        'padding_x': p_padding_x, 'padding_y' : p_padding_y,
                        'line_height' : p_line_height,'font_weight' : p_font_weight, 'font_style' : p_font_style, 'font_size' : p_font_size, 'font_family' : p_font_family,
                        'font_color' : p_font_color, 'use_parent_font_flag' : p_use_parent_flag, 'border_flag' : p_border_flag,
                        'border_width' : p_border_width, 'border_color' : p_border_color, 'border_style' : p_border_style, 'image_flag' : p_image_flag,
                        'value_type' : p_value_type, 'value_format' : p_value_format};

          var result = {'sequence_no' : 0, "deleteflag" : deleteflag, 'kind' : p_kind,'type' : 'label' , 'id' : p_id, 'value' : p_value, 'value_type' : p_value_type, 'value_format' : p_value_format, 'properties' : result_prop};
          results.push(result);
        });

        $("#print_form .p_border").each(function() {
          $this = $(this)
          var p_id = $this.attr("id");
          if (p_id == null) {
            p_id = "";
          }
          var deleteflag = "";
          if ($this.css("display") == "none") {
            deleteflag = "true"
          } else {
            deleteflag = "false"
          }
          var p_value = $this.find("textarea").val();
          var p_top = $this.css("top");
          var p_footer_top = $this.position().top - ($('#table_container').position().top + $('#table_container').height()) + 'px';
          var p_left = $this.css("left");
          var p_width = $this.css("width");
          var p_height = $this.css("height");
          var p_kind = $this.data("kind");
          var p_padding_x = $this.css("padding-top");
          var p_padding_y = $this.css("padding-left");
          var p_line_height = $this.find("textarea").css("line-height");
          var p_font_size = $this.find("textarea").css("font-size");
          var p_font_family = $this.find("textarea").css("font-family");
          var p_font_color = $this.find("textarea").css("color");
          var p_use_parent_flag = $this.data("parent_flag");
          var p_border_flag = $this.data("border-top_flag");
          var p_border_width = $this.css("border-top-width");
          var p_border_color = $this.css("border-top-color");
          var p_border_style = $this.css("border-top-style");

          var result_prop = {'footer_top' : p_footer_top, 'top' : p_top, 'left' : p_left,
                        'width' : p_width, 'height' : p_height, 'padding_x': p_padding_x, 'padding_y' : p_padding_y,
                        'line_height' : p_line_height, 'font_size' : p_font_size, 'font_family' : p_font_family,
                        'font_color' : p_font_color, 'use_parent_font_flag' : p_use_parent_flag, 'border_flag' : p_border_flag,
                        'border_width' : p_border_width, 'border_color' : p_border_color, 'border_style' : p_border_style}

          var result = {'sequence_no' : 0,'deleteflag' : deleteflag, 'kind' : p_kind,'type' : 'rectangle', 'id' : p_id, 'value' : '','value_type' : '', 'value_format' : '', 'properties' : result_prop };
          //console.log(result);
          results.push(result);
        });

        var seq = 1;
        $('#table1 > thead > tr > th').each(function () {
            var $this = $(this)

            var p_id = $this.attr("id");
            if (p_id == null) {
              p_id = "";
            }
            var p_header_text = $this.html();
            var p_delete_flag = $this.attr("deleteflag");
            if ($this.css("display") == "none") {
              p_delete_flag = "true"
            } else {
              p_delete_flag = "false"
            }
            var sequence_no = seq;
            seq = seq + 1;
            var p_value = $this.attr("value");
            var p_value_type = $this.attr("valuetype");
            if (p_value_type == null) {
              p_value_type = "";
            }
            var p_value_format = $this.attr("valueformat");
            if (p_value_format == null) {
              p_value_format = "";
            }
            var p_width = $this.css("width");
            var p_horizontal_align = $this.attr("textalign");
            var p_vertical_align = $this.attr("verticalalign");
            var p_padding_x = $this.attr("paddingx");
            var p_padding_y = $this.attr("paddingy");
            var p_line_height = $this.attr("lineheight");
            var p_font_weight = $this.attr("fontweight");
            var p_font_style = $this.attr("fontstyle");
            var p_font_size = $this.attr("fontsize");
            var p_font_family = $this.attr("fontfamily");
            var p_font_color = $this.attr("fontcolor");
            var p_use_parent_flag = $this.attr("fontflag");

            var result_prop = {'header_text' : p_header_text,
                          'width' : p_width, 'horizontal_align' : p_horizontal_align, 'vertical_align' : p_vertical_align,
                          'padding_x': p_padding_x, 'padding_y' : p_padding_y,
                          'line_height' : p_line_height,'font_weight' : p_font_weight, 'font_style' : p_font_style, 'font_size' : p_font_size, 'font_family' : p_font_family,
                          'font_color' : p_font_color, 'use_parent_font_flag' : p_use_parent_flag,
                          'value_type' : p_value_type, 'value_format': p_value_format}

            var result = {'sequence_no' : sequence_no, 'deleteflag' : p_delete_flag,'kind' : 'detail','type' : '', 'id' : p_id, 'value' : p_value, 'value_type' : p_value_type , 'value_format' : p_value_format, 'properties' : result_prop};
            results.push(result);
        });

        return results;
    }


    $.each(print, function(key, value) {
        switch (key) {
        case 'paper_size':
          if (value =='A4') {
            $('#print_form').css('width','21cm');
            $('#print_form').css('height' ,'29.7cm');
          } else if (value == 'A5') {
            $('#print_form').css('width', '14.8cm');
            $('#print_form').css('height', '21cm');
          } else if (value == 'Letter') {
            $('#print_form').css('width', '8.5in');
            $('#print_form').css('height', '11in');
          } else if (value == 'Slip') {
            $('#print_form').css('width', '8.5in');
            $('#print_form').css('height', '5.5in');
          } else if (value == 'Struct') {
            $('#print_form').css('width', '2.26in');
            $('#print_form').css('height', '10in');
        }
          break;
        case 'has_detail':
          // alert("ok");
          break;
        case 'paper_orientation':
          if (value =='LANDSCAPE') {
            var width = $('#print_form').css('height');
            var height = $('#print_form').css('width');
            $('#print_form').css('width',width);
            $('#print_form').css('height' ,height);
          }
          break;
        case 'row_height':
          $('#table1').attr("rowheight",value);
          break;
        case 'table_top':
          $('#table_container').css("top" , value);
          break;
        case 'table_row_count':
          $('#table1 tbody').html("");
          var rows = "";
          for(var i=0;i<value;i++) {
            rows += "<tr></tr>";
          }
          $('#table1 tbody').append(rows);

          break;
        case 'table_border_style':

          break;
        default:

        }
    });


    $('#btn_insert_text').click(function () {
      createTextbox("header","#print_form", "","200px","300px", //top, left
                    "200px","30px", //width, height
                    "left","middle", //horizontal align, vertical align
                    false,"1px","black","solid", //borderFlag,borderSize,borderColor,borderStyle
                    "default","default","default","normal","normal", //lineHeight, padding-X, padding-Y,fontweight,font style
                    "default","black","Arial",false, //fontSize,fontColor,fontFamily,parentFont
                    "","","") //value, valuetype, valueformat
    });
    $('#btn_insert_label').click(function () {
      createLabel("header","#print_form", "","200px","300px", //top, left
                    "200px","30px", //width, height
                    "left","middle", //horizontal align, vertical align
                    false,"1px","black","solid", //borderFlag,borderSize,borderColor,borderStyle
                    "default","default","default","normal","normal", //lineHeight, padding-X, padding-Y, fontweight,fontstyle
                    "default","black","Arial",false, //fontSize,fontColor,fontFamily,parentFont
                    "","","") //value, valuetype, valueformat
    });
    $('#btn_insert_rectangle').click(function () {
      createBorder("header","#print_form", "","200px","300px", //top, left
                    "200px","30px", //width, height
                    "1px","black","solid" //borderFlag,borderSize,borderColor,borderStyle
                    ,"false"
                   )
    });
    $('#btn_insert_row').click(function () {
          $( "#table1" ).colResizable({
            disable:true
        });
        $('#table1').find('tr').each(function(){
             $(this).find('th').eq(-1).after('<th id="" value="" textalign="" verticalalign="" fontweight="" fontstyle="" lineheight="" paddingx="" paddingy="" fontcolor="black" fontfamily="Arial" fontsize="14px" fontflag="true">header</th>');
             $(this).find('td').eq(-1).after('<td>row</td>');
        });
        //$('#table1').dragtable('destroy').dragtable({});
        $( "#table1" ).colResizable({
          liveDrag:true
        });
    });

    $scope.deleteDetail = function() {
      var row = $('.selected');
      row.css("display","none");
    }

    $('#table1 tbody').on("click","td",function(){
      $('tbody').find('tr.selected').removeClass('selected');
      $(this).parent().addClass('selected');
      var index = $('tr.selected > td').index(this);
      //alert($('tr.selected > td').index(this));
      $('tr').find('td.col-selected').removeClass('col-selected');
      $('tr').find('th.col-selected').removeClass('col-selected');
      $('table tr td:nth-child('+ (index+1) +')').addClass('col-selected');
      $('table tr th:nth-child('+ (index+1) +')').addClass('col-selected');
    })

    $('#print_form').on("focus",".btn_setting",function () {
      $(this).show();
    });


    $('#print_form').on("focus",".p_textbox > textarea",function () {
      $(".btn_setting").hide();
      $("#print_form").find(".selected").css('z-index','1');
      $("#print_form").find(".selected").removeClass("selected");
      $(this).parent().addClass("selected");
      $(this).parent().find(".btn_setting").show();
      $(this).parent().css('z-index','1000');
    });

    $('#print_form').on("focus",".p_label > textarea",function () {
      $(".btn_setting").hide();
      $("#print_form").find(".selected").css('z-index','1');
      $("#print_form").find(".selected").removeClass("selected");
      $(this).parent().addClass("selected");
      $(this).parent().find(".btn_setting").show();
      $(this).parent().css('z-index','1000');
    });

    $('#print_form').on("focus",".p_border > input",function () {
      $(".btn_setting").hide();
      $("#print_form").find(".selected").css('z-index','1');
      $("#print_form").find(".selected").removeClass("selected");
      $(this).parent().addClass("selected");
      $(this).parent().find(".btn_setting").show();
        $(this).parent().css('z-index','1000');
    });

    $('#print_form').on("click","#table1",function () {
      $(".btn_setting").hide();
      $(this).parent().find(".btn_setting").show();
    });

    $('#print_form').on("click",function (e) {
      if (e.target !== this)
      return;
      $("#print_form").find(".selected").removeClass("selected");
      $(".btn_setting").hide();
    });


    $('#print_form').on('click','.btn_setting_object',function() {
      var parent = $(this).parent();
      var datas = {'kind' : parent.data("kind"), 'value' : parent.find("textarea").val(),'top' : parent.css("top"),'left' : parent.css("left"),
                   'width' : parent.css("width"),'height' : parent.css("height"),
                   'horizontalAlign' : parent.find("textarea").css("text-align"),'verticalAlign' : parent.find("textarea").css("vertical-align"),
                   'paddingX' : parent.css("padding-top"),'paddingY' : parent.css("padding-left"),
                   'lineHeight' : parent.find("textarea").css("line-height"),'fontWeight' : parent.find("textarea").css("font-weight"),'fontStyle' : parent.find("textarea").css("font-style"),'fontSize' : parent.find("textarea").css("font-size"),'fontFamily' : parent.find("textarea").css("font-family"),
                   'fontColor' : parent.find("textarea").css("color"),'useParentFontFlag' : parent.data("parent_flag"),'borderFlag' : parent.data("border_flag"),
                   'borderWidth' : parent.css("border-top-width"),'borderColor' : parent.css("border-top-color"),'borderStyle' : parent.css("border-top-style"),'imageFlag' : parent.attr("image_flag"),
                   'valueType' : parent.attr("valuetype"), 'valueFormat' : parent.attr("valueformat")
                 };
      // $("#detail-form").data('cond', "update" );
      // $("#detail-form").data('row', datas );
      // $('#detail-form').modal('show');
      $scope.updateDetail(datas);
    })


    $('#print_form').on('click','.btn_setting_table',function() {
      var parent = $('#table1');
      var datas = {'table_top' : $('#table_container').css("top"),'table_row_number' : $('#table1 tbody tr').length, 'table_row_height' : parent.find('tbody tr:first-child').css('height') , 'table_border_style' : parent.data('borderstyle')};
      // $("#detail-table-form").data('cond', "update" );
      // $("#detail-table-form").data('row', datas );
      // $("#detail-table-form").data('colnumber', $('#table1 tbody tr:first-child td').length );
      // $('#detail-table-form').modal('show');
    })

    $('#print_form').on('dblclick','tbody td',function() {
      var column = $('#table1 th.col-selected');
      var datas = {'headerText': column.html(), 'value' : column.attr("value"), 'width' : column.css("width"),
                   'horizontalAlign' : column.attr("textalign"),'verticalAlign' : column.attr("veralign"),
                   'lineheight':column.attr("lineheight"), 'paddingX' : column.attr("paddingx"), 'paddingY' : column.attr("paddingy"),
                   'fontWeight' : column.find("textarea").css("font-weight"),'fontStyle' : column.find("textarea").css("font-style"),
                   'fontSize':column.attr("fontsize"), 'fontFamily' : column.attr("fontfamily"), 'fontColor' : column.attr("fontcolor"),
                   'useParentFontFlag' : column.attr("fontflag"), 'valueType' : column.attr("valuetype"), 'valueFormat' : column.attr("valueformat")};
    //  console.log(datas);
      // $("#detail-column-form").data('cond', "update" );
      // $("#detail-column-form").data('row', datas );
      // $('#detail-column-form').modal('show');
      $scope.updateColumnDetail(datas);

    })

    $scope.deleteColumnDetail = function() {
        $( "#table1" ).colResizable({
              disable:true
          });
          var column = $('th.col-selected');
          column.css("display","none");
          column.attr('deleteflag',"true");
           $('td.col-selected').css("display","none");
           $( "#table1" ).colResizable({
             liveDrag:true
         });
           $("#detail-column-form").modal("hide");
    }

    var createTextbox = function(kind,container,p_id, top,left,
                                width,height,
                                horizontalAlign,verticalAlign,
                                borderFlag,borderWidth,borderColor,borderStyle,
                                lineHeight,paddingX,paddingY,
                                fontWeight,fontStyle,
                                fontSize,fontColor,fontFamily,useParentFontFlag,
                                value, valueType, valueFormat){
    var $div = $("<div>", {id: p_id ,class: "p_textbox", 'valuetype' : valueType , 'valueformat' : valueFormat, "data-kind" : kind,  "data-border_flag" : borderFlag, "data-parent_flag" : useParentFontFlag});
      $div.css( "position", "absolute" );
      $div.css( "z-index", "100" );
      $div.css( "top", top );
      $div.css( "left", left );
      $div.css( "width", width );
      $div.css( "height", height );
      if (borderFlag) {
          $div.css( "border", borderStyle + " " + borderWidth + " " + borderColor );
      }
      $div.append('<textarea name="Text1" >' + value + '</textarea>');
      $div.append('<div class="drag_button"><i class="fa fa-arrows fa-1x"></i></div>');
      $div.append('<a style="display:none;z-index:900" class="btn_setting btn_setting_object" href="#"><i class="fa fa-gear"></i></a>');
      $div.find("textarea").css( "text-align", horizontalAlign );
      $div.find("textarea").css( "vertical-align", verticalAlign );
      if (useParentFontFlag == false) {
        $div.find("textarea").css( "font-family", fontFamily );
      }
      if (fontSize !== "default") {
        $div.find("textarea").css( "font-size", fontSize );
      }
      $div.find("textarea").css( "font-style", fontStyle );
      $div.find("textarea").css( "font-weight", fontWeight );
      $div.find("textarea").css( "color", fontColor );

      if (kind == "footer") {
        $div.find("textarea").css( "background-color", "#D7DADB" );
      }

      $(container).append($div);

      $(".p_textbox").draggable({
        grid: [ 5, 5 ],
        containment: container,
        scroll: false,
        handle: ".drag_button",
        start: function(ev, ui) {
            if ($(this).hasClass("ui-selected")){
                $scope.selected = $(".ui-selected").each(function() {
                   var el = $(this);
                   el.data("offset", el.offset());
                });
            }
            else {
                $scope.selected = $([]);
                $(".p_textbox").removeClass("ui-selected");
            }
            offset = $(this).offset();
        },
        drag: function(ev, ui) {
            var dt = ui.position.top - offset.top, dl = ui.position.left - offset.left;
            // take all the elements that are selected expect $("this"), which is the element being dragged and loop through each.
            $scope.selected.not(this).each(function() {
                 // create the variable for we don't need to keep calling $("this")
                 // el = current element we are on
                 // off = what position was this element at when it was selected, before drag
                 var el = $(this), off = el.data("offset");
                el.css({top: off.top + dt, left: off.left + dl});
            });
        },
       });
      $(".p_textbox").resizable({
        grid: 5
      });
      $( ".p_textbox textarea" ).autocomplete({
        source: $scope.columns_header
      });
    }

    var createLabel = function(kind,container,p_id,top,left,
                                width,height,
                                horizontalAlign,verticalAlign,
                                borderFlag,borderWidth,borderColor,borderStyle,
                                lineHeight,paddingX,paddingY,
                                fontWeight,fontStyle,
                                fontSize,fontColor,fontFamily,useParentFontFlag,
                                value, image_flag, valueType, valueFormat){
    var $div = $("<div>", {id: p_id ,class: "p_label", "valuetype" : valueType, "valueformat" : valueFormat, "data-kind" : kind, "data-border_flag" : borderFlag, "data-parent_flag" : useParentFontFlag , "image_flag" : image_flag});
      $div.css( "position", "absolute" );
      $div.css( "z-index", "100" );
      $div.css( "top", top );
      $div.css( "left", left );
      $div.css( "width", width );
      $div.css( "height", height );
      if (borderFlag) {
          $div.css( "border", borderStyle + " " + borderWidth + " " + borderColor );
      }
      $div.append('<textarea style="background-color:transparent" name="Text1" >' + value + '</textarea>');
      $div.append('<div class="drag_button"><i class="fa fa-arrows fa-1x"></i></div>');
      $div.append('<a style="display:none;z-index:900" class="btn_setting btn_setting_object" href="#"><i class="fa fa-gear"></i></a>');
      $div.find("textarea").css( "text-align", horizontalAlign );
      $div.find("textarea").css( "vertical-align", verticalAlign );
      if (useParentFontFlag == false) {
        $div.find("textarea").css( "font-family", fontFamily );
      }
      if (fontSize !== "default") {
        $div.find("textarea").css( "font-size", fontSize );
      }
      $div.find("textarea").css( "font-style", fontStyle );
      $div.find("textarea").css( "font-weight", fontWeight );
      $div.find("textarea").css( "color", fontColor );

      if (image_flag == "true") {
        $div.css("background-image","url('" + value + "')");
        $div.css("background-size","cover");
        $div.css("color","transparent");
      }

      $(container).append($div);
      $(".p_label").draggable({
        grid: [ 5, 5 ],
        containment: container,
        scroll: false,
        handle: ".drag_button",
        start: function(ev, ui) {
            if ($(this).hasClass("ui-selected")){
                $scope.selected = $(".ui-selected").each(function() {
                   var el = $(this);
                   el.data("offset", el.offset());
                });
            }
            else {
                $scope.selected = $([]);
                $(".p_textbox").removeClass("ui-selected");
            }
            offset = $(this).offset();
        },
        drag: function(ev, ui) {
            var dt = ui.position.top - offset.top, dl = ui.position.left - offset.left;
            // take all the elements that are selected expect $("this"), which is the element being dragged and loop through each.
            $scope.selected.not(this).each(function() {
                 // create the variable for we don't need to keep calling $("this")
                 // el = current element we are on
                 // off = what position was this element at when it was selected, before drag
                 var el = $(this), off = el.data("offset");
                el.css({top: off.top + dt, left: off.left + dl});
            });
        },
       });
      $(".p_label").resizable({
        grid: 5
      });
    }

    var createBorder = function(kind,container,p_id,top,left,
                                width,height,
                                borderWidth,borderColor,borderStyle, image_flag){
    var $div = $("<div>", {id: p_id ,class: "p_border","data-kind" : kind,"image_flag" : image_flag});
      $div.css( "position", "absolute" );
      $div.css( "z-index", "1" );
      $div.css( "top", top );
      $div.css( "left", left );
      $div.css( "width", width );
      $div.css( "height", height );
      $div.css( "border", borderStyle + " " + borderWidth + " " + borderColor );
      $div.append('<input type="text" readonly value="">');
      $div.append('<div class="drag_button"><i class="fa fa-arrows fa-1x"></i></div>');
      $div.append('<a style="display:none;z-index:900" class="btn_setting btn_setting_object" href="#"><i class="fa fa-gear"></i></a>');
      $(container).append($div);
      $(".p_border").draggable({
        grid: [ 5, 5 ],
        containment: container,
        scroll: false,
        handle: ".drag_button",
        start: function(ev, ui) {
            if ($(this).hasClass("ui-selected")){
                $scope.selected = $(".ui-selected").each(function() {
                   var el = $(this);
                   el.data("offset", el.offset());
                });
            }
            else {
                $scope.selected = $([]);
                $(".p_textbox").removeClass("ui-selected");
            }
            offset = $(this).offset();
        },
        drag: function(ev, ui) {
            var dt = ui.position.top - offset.top, dl = ui.position.left - offset.left;
            // take all the elements that are selected expect $("this"), which is the element being dragged and loop through each.
            $scope.selected.not(this).each(function() {
                 // create the variable for we don't need to keep calling $("this")
                 // el = current element we are on
                 // off = what position was this element at when it was selected, before drag
                 var el = $(this), off = el.data("offset");
                el.css({top: off.top + dt, left: off.left + dl});
            });
        },
       });
      $(".p_border").resizable({
        grid: 5
      });
    }


    //get table list ajax
    Editor.get('api/' + $routeParams.form + "/" + $scope.form_id + "/editor")
      .success(function(data) {
           //console.log(data);

           $scope.print = data.print;
          //  var id = {{$id}}

            $scope.columns_header = data.columns_header;
            $scope.columns_detail = data.columns_detail;

            $scope.print_details = data.print_details;
            $scope.print_properties = data.print_properties;

            $.each($scope.print, function(key, value) {
                switch (key) {
                case 'paper_size':
                  if (value =='A4') {
                    $('#print_form').css('width','21cm');
                    $('#print_form').css('height' ,'29.7cm');
                  } else if (value == 'A5') {
                    $('#print_form').css('width', '14.8cm');
                    $('#print_form').css('height', '21cm');
                  } else if (value == 'Letter') {
                    $('#print_form').css('width', '8.5in');
                    $('#print_form').css('height', '11in');
                  } else if (value == 'Slip') {
                    $('#print_form').css('width', '8.5in');
                    $('#print_form').css('height', '5.5in');
                  }
                  else if (value == 'Struct') {
                   $('#print_form').css('width', '70mm');
                   $('#print_form').css('height', '10cm');
                 }
                  break;
                  case 'has_detail':
                  if (value == 0) {
                      $scope.hide_table = true;
                  } else {
                      $scope.hide_table = false;
                  }

                case 'paper_orientation':
                  if (value =='LANDSCAPE') {
                    var width = $('#print_form').css('height');
                    var height = $('#print_form').css('width');
                    $('#print_form').css('width',width);
                    $('#print_form').css('height' ,height);
                  }
                  break;
                case 'row_height':
                  $('#table1').attr("rowheight",value);
                  break;
                case 'table_top':
                  $('#table_container').css("top" , value);
                  break;
                case 'table_row_count':
                  $('#table1 tbody').html("");
                  var rows = "";
                  for(var i=0;i<value;i++) {
                    rows += "<tr></tr>";
                  }
                  $('#table1 tbody').append(rows);

                  break;
                case 'table_border_style':

                  break;
                default:

                }
            });


            for(var i=0;i<$scope.print_details.length;i++) {
               switch ($scope.print_details[i]['kind']) {
                 case 'detail':
                        arr = jQuery.grep($scope.print_properties, function( n) {
                            return ( n['print_detail_id'] == $scope.print_details[i]['id'] );
                        });
                        var $th = $("<th>", {id: $scope.print_details[i]['id'], "value" : $scope.print_details[i]['value']});
                        for(var j=0;j<arr.length;j++) {
                          switch (arr[j]['name']) {
                            case 'header_text':
                              $th.html(arr[j]['value']);
                              break;
                            case 'value_type':
                              $th.attr("valuetype", $scope.print_details[i]['value_type']);
                              break;
                            case 'value_format':
                              $th.attr("valueformat", $scope.print_details[i]['value_format']);
                              break;
                            case 'width':
                              $th.attr("width", arr[j]['value']);
                              break;
                            case 'horizontal_align':
                              $th.attr("textalign", arr[j]['value']);
                              break;
                            case 'vertical_align':
                              $th.attr("verticalalign", arr[j]['value']);
                              break;
                            case 'padding_x':
                              $th.attr("paddingx", arr[j]['value']);
                              break;
                            case 'padding_y':
                              $th.attr("paddingy", arr[j]['value']);
                              break;
                            case 'line_height':
                              $th.attr("lineheight", arr[j]['value']);
                              break;
                            case 'font_weight':
                              $th.attr("fontweight", arr[j]['value']);
                              break;
                            case 'font_style':
                              $th.attr("fontstyle", arr[j]['value']);
                              break;
                            case 'font_size':
                              $th.attr("fontsize", arr[j]['value']);
                              break;
                            case 'font_family':
                              $th.attr("fontfamily", arr[j]['value']);
                              break;
                            case 'font_color':
                              $th.attr("fontcolor", arr[j]['value']);
                              break;
                            case '"use_parent_font_flag"':
                              $th.attr("fontflag", arr[j]['value']);
                              break;
                            default:
                          }
                        }

                       if ($('#table1').find('th').length == 0) {
                             $('#table1 > thead > tr').append($th);
                             $('#table1 > tbody > tr').append('<td>row</td>');
                       } else {
                         $('#table1').find('tr').each(function(){
                              $(this).find('th').eq(-1).after($th);
                              $(this).find('td').eq(-1).after('<td>row</td>');
                         });
                       }

                       $('#table1 tbody').find('tr').css('height',$('#table1').attr('rowheight'));



                      //  $( "#table1" ).colResizable();
                      // $('#table1').dragtable({});


                   break;
                   case 'footer':
                   case 'header':

                          arr = jQuery.grep($scope.print_properties, function( n) {
                              return ( n['print_detail_id'] == $scope.print_details[i]['id'] );
                          });
                        //  console.log(arr);
                          var p_id = $scope.print_details[i]['id'];
                          var p_kind = $scope.print_details[i]['kind'];
                          var p_value = $scope.print_details[i]['value'];
                          var p_value_type = $scope.print_details[i]['value_type'];
                          var p_value_format = $scope.print_details[i]['value_format'];
                          var p_image_flag = "";
                          var p_top = "";
                          var p_left = "";
                          var p_width = "";
                          var p_height = "";
                          var p_horizontal_align = "";
                          var p_vertical_align = "";
                          var p_padding_x = "";
                          var p_padding_y = "";
                          var p_line_height = "";
                          var p_font_weight = "";
                          var p_font_style = "";
                          var p_font_size = "";
                          var p_font_family = "";
                          var p_font_color = "";
                          var p_use_parent_flag = "";
                          var p_border_flag = "";
                          var p_border_width = "";
                          var p_border_color = "";
                          var p_border_style = "";
                          for(var j=0;j<arr.length;j++) {
                            switch (arr[j]['name']) {
                              case 'image_flag':
                                p_image_flag = arr[j]['value'];
                                break;
                              case 'value_type':
                                p_value_type =  $scope.print_details[i]['value_type'];
                                break;
                              case 'value_format':
                                p_value_format =$scope.print_details[i]['value_format'];
                                break;
                              case 'top':
                                p_top = arr[j]['value'];
                                break;
                              case 'left':
                                p_left = arr[j]['value'];
                                break;
                              case 'width':
                                p_width = arr[j]['value'];
                                break;
                              case 'height':
                                p_height = arr[j]['value'];
                                break;
                              case 'horizontal_align':
                                p_horizontal_align = arr[j]['value'];
                                break;
                              case 'vertical_align':
                                p_vertical_align = arr[j]['value'];
                                break;
                            case 'padding_x':
                                p_padding_x = arr[j]['value'];
                                break;
                              case 'padding_y':
                                p_padding_y = arr[j]['value'];
                                break;
                              case 'line_height':
                                p_line_height = arr[j]['value'];
                                break;
                              case 'font_weight':
                                p_font_weight = arr[j]['value'];
                                break;
                              case 'font_style':
                                p_font_style = arr[j]['value'];
                                break;
                              case 'font_size':
                                p_font_size = arr[j]['value'];
                                break;
                              case 'font_family':
                                p_font_family = arr[j]['value'];
                                break;
                              case 'font_color':
                                p_font_color = arr[j]['value'];
                                break;
                              case 'use_parent_font_flag':
                                p_use_parent_flag = arr[j]['value'];
                                break;
                              case 'border_flag':
                                p_border_flag = arr[j]['value'];
                              break;
                              case 'border_width':
                                p_border_width = arr[j]['value'];
                              break;
                              case 'border_color':
                                p_border_color = arr[j]['value'];
                              break;
                              case 'border_style':
                                p_border_style = arr[j]['value'];
                              break;
                              default:
                            }
                          }

                          switch ($scope.print_details[i]['type']) {
                            case 'textbox':
                              createTextbox(p_kind,"#print_form", p_id, p_top,p_left,
                                      p_width,p_height,
                                      p_horizontal_align,p_vertical_align,
                                      p_border_flag,p_border_width,p_border_color,p_border_style,
                                      p_line_height,p_padding_x,p_padding_y,p_font_weight,p_font_style,
                                      p_font_size,p_font_color,p_font_family,p_use_parent_flag,
                                      p_value, p_value_type, p_value_format);
                              break;
                            case 'label':
                            createLabel(p_kind,"#print_form", p_id, p_top,p_left,
                                    p_width,p_height,
                                    p_horizontal_align,p_vertical_align,
                                    p_border_flag,p_border_width,p_border_color,p_border_style,
                                    p_line_height,p_padding_x,p_padding_y,p_font_weight,p_font_style,
                                    p_font_size,p_font_color,p_font_family,p_use_parent_flag,
                                    p_value,p_image_flag, p_value_type, p_value_format);
                              break;
                            case 'rectangle':
                            createBorder(p_kind,"#print_form", p_id,p_top,p_left,
                                        p_width,p_height,
                                        p_border_width,p_border_color,p_border_style);
                              break;
                            default:

                          }

                     break;
                 default:

               }
            }


            //jika detail kosong, set default 1
            if ($('#table1').find('th').length == 0) {
                  $('#table1 > thead > tr').append('<th id="" value = "" textalign="" verticalalign="" fontweight="" fontstyle="" paddingx = "" paddingy = "" lineHeight = "" fontsize = "" fontfamily = "" fontsize="" fontcolor = "" fontflag = "true">header</th>');
                  $('#table1 > tbody > tr').append('<td>row</td>');
                $('#table1 tbody').find('tr').css('height',$('#table1').attr('rowheight'));
            }

            $("#table_container").draggable({
              grid: [ 5, 5 ],
              containment: "#print_form",
              scroll: false
             });
            //  handle: ".drag_button"
             $( "#table1" ).colResizable({
                   disable:true
               });

              $('#table1').dragtable();
             $( "#table1" ).colResizable({
                liveDrag:true
            });

             $('#table1').css('width',$('#print_form').css('width'));

      })
      .error(function(data) {
         $scope.loading = false;
         $scope.showAlert(data.error);
      });



});
