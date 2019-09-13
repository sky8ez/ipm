// public/js/controllers/formCtrl.js

angular.module('printCtrl', [])

// .directive('myRepeatDirective', function() {
//   return function(scope, element, attrs) {
//     // angular.element(element).css('color','blue');
//     if (scope.$last){
//     //  window.alert("im the last!");
//       $('#datepicker1').datepicker();
//     }
//   };
// })


.controller('printController', function($scope, $routeParams, $mdDialog,$window, Forms) {

    // get all the datas first and bind it to the $scope.datas object
    // use the function we created in our service
    // GET RECORDS ==============

    $scope.loading = false;
    $scope.form_name = $routeParams.form;
    $scope.form_id = $routeParams.ID;
    $scope.cmbtemplate = "";
    $scope.hide_table = false;

    $scope.print_templates = [];
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

    Forms.get('api/check-print/' + $scope.form_name + '/' + $scope.form_id)
        .success(function(data) {
          if (data.status == 'error') {
             $scope.showAlert(data.msg);

          } else {
            //get print template options
            Forms.get('api/print-cat/' + $routeParams.form)
              .success(function(data) {
                // console.log(data.print);
                $scope.print_templates = data.print;
                $scope.onchange();
              })
              .error(function(data) {
                 $scope.loading = false;
                 $scope.showAlert(data.error);
              });
          }

      })
      .error(function(data) {
         $scope.loading = false;
         $scope.showAlert(data.error);
      });



    $scope.onchange = function() {// change template
      //get table list ajax
      Forms.get('api/print/' + $scope.form_name  +  '/' + $scope.form_id + '/' + $scope.cmbtemplate)
        .success(function(data) {
            //delete all record from data
            $('.p_textbox').remove();
            $('.p_label').remove();
            $('.p_rectangle').remove();
            $('th').remove();

            $scope.details = data.data_details;
            $scope.prints = data.print;
            $scope.print_details = data.print_details;
            $scope.print_properties = data.print_properties;
            $scope.id = '';

            $.each($scope.prints, function(key, value) {
                switch (key) {
                case 'paper_size':
                  if (value =='A4') {
                    $('.print_form').css('width','21cm');
                    $('.print_form').css('height', '29.7cm');
                  } else if (value == 'A5') {
                    $('.print_form').css('width', '5.8in');
                    $('.print_form').css('height', '8.3in');

                  } else if (value == 'Letter') {
                    $('.print_form').css('width', '8.5in');
                    $('.print_form').css('height', '11in');
                  } else if (value == 'Slip') {
                    // $('.print_form').css('width', '8.5in');
                    // $('.print_form').css('height', '5.5in');
                  } else if (value == 'Struct') {
                    $('.print_form').css('width', '70mm');
                    $('.print_form').css('height', '10cm');
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
                    var width = $('.print_form').css('height');
                    var height = $('.print_form').css('width');
                    $('.print_form').css('width',width);
                    $('.print_form').css('height' ,height);
                  }
                  break;
                case 'row_height':
                  $('.table1').attr("rowheight",value);
                  break;
                  case 'table_top':

                    $('.table_container').css("top" , value);
                    break;
                  case 'table_row_count':
                    $('.table1 tbody').html("");
                    var rows = "";
                    for(var i=0;i<value;i++) {
                      rows += "<tr></tr>";
                    }
                    $('.table1 tbody').append(rows);
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
                              $th.attr("valuetype", arr[j]['value']);
                              break;
                            case 'value_format':
                              $th.attr("valueformat", arr[j]['value']);
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

                       if ($('.table1').find('th').length == 0) {
                             $('.table1 > thead > tr').append($th);
                             $('.table1 > tbody > tr').append('<td>row</td>');
                       } else {
                         $('.table1').find('tr').each(function(){
                              $(this).find('th').eq(-1).after($th);
                              $(this).find('td').eq(-1).after('<td>row</td>');
                         });
                       }

                       $('.table1 tbody').find('tr').css('height',$('.table1').attr('rowheight'));
                       $('.table1').css('width',$('.print_form').css('width'));
                       $('.table_container').css('width',$('.print_form').css('width'));

                   break;
                   case 'footer':
                         arr = jQuery.grep($scope.print_properties, function( n) {
                             return ( n['print_detail_id'] == $scope.print_details[i]['id'] );
                         });
                       //  console.log(arr);
                         var p_id = $scope.print_details[i]['id'];
                         var p_kind = $scope.print_details[i]['kind'];
                         var p_value = $scope.print_details[i]['value'];
                         var p_value_type = "";
                         var p_value_format = "";
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
                               p_value_type = arr[j]['value'];

                               break;
                             case 'value_format':
                               p_value_format = arr[j]['value'];

                               break;
                             case 'footer_top':
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

                             createTextbox(p_kind,".footer1", p_id, p_top,p_left,
                                     p_width,p_height,
                                     p_horizontal_align,p_vertical_align,
                                     p_border_flag,p_border_width,p_border_color,p_border_style,
                                     p_line_height,p_padding_x,p_padding_y,p_font_weight,p_font_style,
                                     p_font_size,p_font_color,p_font_family,p_use_parent_flag,
                                     p_value, p_value_type, p_value_format);
                             break;
                           case 'label':
                           createLabel(p_kind,".footer1", p_id, p_top,p_left,
                                   p_width,p_height,
                                   p_horizontal_align,p_vertical_align,
                                   p_border_flag,p_border_width,p_border_color,p_border_style,
                                   p_line_height,p_padding_x,p_padding_y,p_font_weight,p_font_style,
                                   p_font_size,p_font_color,p_font_family,p_use_parent_flag,
                                   p_value,p_image_flag, p_value_type, p_value_format);
                             break;
                           case 'rectangle':
                           createBorder(p_kind,".footer1", p_id,p_top,p_left,
                                       p_width,p_height,
                                       p_border_width,p_border_color,p_border_style);
                             break;
                           default:

                         }
                         break;
                   case 'header':

                          arr = jQuery.grep($scope.print_properties, function( n) {
                              return ( n['print_detail_id'] == $scope.print_details[i]['id'] );
                          });
                        //  console.log(arr);
                          var p_id = $scope.print_details[i]['id'];
                          var p_kind = $scope.print_details[i]['kind'];
                          var p_value = $scope.print_details[i]['value'];
                          var p_image_flag = "";
                          var p_value_type = "";
                          var p_value_format = "";
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
                              case 'valur_type':
                                p_value_type = arr[j]['value'];
                                break;
                              case 'value_format':
                                p_value_format = arr[j]['value'];
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
                              createTextbox(p_kind,".print_form", p_id, p_top,p_left,
                                      p_width,p_height,
                                      p_horizontal_align,p_vertical_align,
                                      p_border_flag,p_border_width,p_border_color,p_border_style,
                                      p_line_height,p_padding_x,p_padding_y,p_font_weight,p_font_style,
                                      p_font_size,p_font_color,p_font_family,p_use_parent_flag,
                                      p_value, p_value_type, p_value_format);
                              break;
                            case 'label':
                            createLabel(p_kind,".print_form", p_id, p_top,p_left,
                                    p_width,p_height,
                                    p_horizontal_align,p_vertical_align,
                                    p_border_flag,p_border_width,p_border_color,p_border_style,
                                    p_line_height,p_padding_x,p_padding_y,p_font_weight,p_font_style,
                                    p_font_size,p_font_color,p_font_family,p_use_parent_flag,
                                    p_value,p_image_flag, p_value_type, p_value_format);
                              break;
                            case 'rectangle':
                            createBorder(p_kind,".print_form", p_id,p_top,p_left,
                                        p_width,p_height,
                                        p_border_width,p_border_color,p_border_style);
                              break;
                            default:

                          }

                     break;
                 default:

               }
            }


            //APPEND DETAIL ROW TO TABLE
            $('.table1 tbody').html("");
            var column_count = 0;
            $.each($scope.details, function(key, value) {
              var row = "<tr style='height:{{$print->row_height}}'>";
              column_count = value.length;
              for(var i=0;i<value.length;i++) {
                row += "<td style='padding:2px;vertical-align:top;text-align:" + $('.table1 thead tr').eq(0).find('th').eq(i).attr('textalign') + ";font-size:" + $('.table1 thead tr').eq(0).find('th').eq(i).attr('fontsize') + "'>" + value[i] + "</td>";
              }
              row += "</tr>"
              $('.table1 tbody').append(row);
            });

            if ($scope.details.length < '{{$print->table_row_count}}') {
              for(var i=0 ; i< ('{{$print->table_row_count}}' - details.length);i++) {
                var row = "<tr style='height:{{$print->row_height}}'>";
                for(var j=0;j<column_count;j++) {
                  row += "<td></td>";
                }
                row += "</tr>"
                $('.table1 tbody').append(row);
              }
            }


        })
        .error(function(data) {
           $scope.loading = false;
           $scope.showAlert(data.error);
        });

    }

      var createTextbox = function(kind,container,p_id, top,left,
                                  width,height,
                                  horizontalAlign,verticalAlign,
                                  borderFlag,borderWidth,borderColor,borderStyle,
                                  lineHeight,paddingX,paddingY,
                                  fontWeight,fontStyle,
                                  fontSize,fontColor,fontFamily,useParentFontFlag,
                                  value, valueType, valueFormat){
      var $div = $("<div>", {id: p_id ,class: "p_textbox","valuetype" : valueType, "valueformat" : valueFormat, "data-kind" : kind,  "data-border_flag" : borderFlag, "data-parent_flag" : useParentFontFlag});
        $div.css( "position", "absolute" );
        $div.css( "z-index", "100" );
        $div.css( "top", top );
        $div.css( "left", left );
        $div.css( "width", width );
        $div.css( "height", height );
        if (borderFlag) {
            $div.css( "border", borderStyle + " " + borderWidth + " " + borderColor );
        }
        $div.append('<span style="display:block;" name="Text1" >' + value + '</span>');
        $div.find("span").css( "text-align", horizontalAlign );
        $div.find("span").css( "vertical-align", verticalAlign );
        if (useParentFontFlag == false) {
          $div.find("span").css( "font-family", fontFamily );
        }
        if (fontSize !== "default") {
          $div.find("span").css( "font-size", fontSize );
        }
        $div.find("span").css( "font-style", fontStyle );
        $div.find("span").css( "font-weight", fontWeight );
        $div.find("span").css( "color", fontColor );

        if (kind == "footer") {
          $div.find("textarea").css( "background-color", "#D7DADB" );
        }

        $(container).append($div);

      }

      var createLabel = function(kind,container,p_id,top,left,
                                  width,height,
                                  horizontalAlign,verticalAlign,
                                  borderFlag,borderWidth,borderColor,borderStyle,
                                  lineHeight,paddingX,paddingY,
                                  fontWeight,fontStyle,
                                  fontSize,fontColor,fontFamily,useParentFontFlag,
                                  value, image_flag, valueType, valueFormat){
      var $div = $("<div>", {id: p_id ,class: "p_label", "valuetype" : valueType, "valueFormat" : valueFormat,  "data-kind" : kind, "data-border_flag" : borderFlag, "data-parent_flag" : useParentFontFlag});
        $div.css( "position", "absolute" );
        $div.css( "z-index", "100" );
        $div.css( "top", top );
        $div.css( "left", left );
        $div.css( "width", width );
        $div.css( "height", height );
        if (borderFlag) {
            $div.css( "border", borderStyle + " " + borderWidth + " " + borderColor );
        }
        if (image_flag == "true") {
          // $div.css("background-image","url('" + value + "')");
          // $div.css("background-size","cover");
          $div.append('<img style="width:100%" src="' + value + '" name="Text1" >');
          $div.css( "overflow", "hidden" );
        } else {
          $div.append('<span style="display:block;" name="Text1" >' + value + '</span>');
        }


        $div.find("span").css( "text-align", horizontalAlign );
        $div.find("span").css( "vertical-align", verticalAlign );
        if (useParentFontFlag == false) {
          $div.find("span").css( "font-family", fontFamily );
        }
        if (fontSize !== "default") {
          $div.find("span").css( "font-size", fontSize );
        }
        $div.find("span").css( "font-style", fontStyle );
        $div.find("span").css( "font-weight", fontWeight );
        $div.find("span").css( "color", fontColor );


        $(container).append($div);
      }

      var createBorder = function(kind,container,p_id,top,left,
                                  width,height,
                                  borderWidth,borderColor,borderStyle){
      var $div = $("<div>", {id: p_id ,class: "p_border","data-kind" : kind,});
        $div.css( "position", "absolute" );
        $div.css( "z-index", "1" );
        $div.css( "top", top );
        $div.css( "left", left );
        $div.css( "width", width );
        $div.css( "height", height );
        $div.css( "border", borderStyle + " " + borderWidth + " " + borderColor );
        $(container).append($div);

      }

    $scope.print = function() {

      Forms.get('api/check-print/' + $scope.form_name + '/' + $scope.form_id)
          .success(function(data) {
            if (data.status == 'error') {
               $scope.showAlert(data.msg);

            } else {

              window.onafterprint = function(e){
                $(window).off('mousemove', window.onafterprint);
                 console.log('Print Dialog Closed..');
                $scope.savedData = {};
                $scope.savedData['transaction_id'] = $scope.form_id;
                Forms.save('api/print/' + $scope.form_name + '/set-print-flag', $scope.savedData)
                  .success(function(result) {
                    if(result.status == 'OK') {
                      // alert("ok");
                        //$window.location.href = result.url;
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

              };

              window.print();

              setTimeout(function(){
                    $(window).one('mousemove', window.onafterprint);
              }, 1);


            }

        })
        .error(function(data) {
           $scope.loading = false;
           $scope.showAlert(data.error);
        });



    }

    //show alert
    $scope.saveData = function() {

    };

});
