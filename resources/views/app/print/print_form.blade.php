@extends('layouts.main')

@section('content')

<script src="{{ asset('plugins/printarea/printThis.js') }}"></script>

  <div class="panel panel-default">
    <div class="panel-heading" >
    <div class="row">
      <div class="col-md-3">
      </div>
      <div class="col-md-3">
        <select class="form-control" name="cmb_print_name" id="cmb_print_name">
            <option value="">select print...</option>
        </select>
      </div>
      <div class="col-md-3" style="text-align:right">
        <button type="button" name="btn_print" id="btn_print" class="btn btn-primary">Print</button>
      </div>
      <div class="col-md-3">
      </div>
    </div>

    </div>
</div>
<div id="print_form_con">
  <div class="print_form">
    <div id="table_container" style="position:absolute">
      <table width="100%" id="table1" border="1" data-borderstyle="full">
        <thead>
          <tr>

          </tr>
        </thead>
        <tbody>
        </tbody>
      </table>
      <tfoot>
        <div class="footer1">

        </div>
      </tfoot>
    </div>

    <!-- <div class="divFooter">
      <img src="/css/images/footer.jpg" alt="" />
    </div> -->
  </div>

</div>

<style media="print">

thead {
  display: table-header-group;
}

/*tfoot {display: table-footer-group;}*/

#table_container {
  min-height: 100px;
  border: 1px solid black;
}

tbody > tr {
  border-bottom: 1px solid #E8E8E8;
}

.divFooter {
           position: fixed;
           bottom: 0;
           height: 120px;
           width: 100%;
       }

/*body
{
  margin: 10mm 10mm 10mm 10mm;
}*/

table {
  border: 0px;

}
thead tr th {
  border: 1px solid black;
}

tbody tr td {
  border-top:0px;
  border-bottom: 0px;
  border-left:1px solid black;
  border-right:1px solid black;
}


.panel {
  display:none;
}

.row {
  display:none;
}

.navbar {
  display:none;
}

/*.print_form {
  position: relative;
  margin: auto;
  background-color:white;
  width: 21cm;
  height: 29.7cm;
  border: 1px dotted gray;
}

.footer1 {
  position: relative;
  background-color:transparent;
  width: 21cm;
}*/


@if ($print->paper_size == 'A4')
.footer1 {
  position: relative;
  top:0px;
  background-color:white;
  width: 21cm;
}
@else
.footer1 {
  position: relative;
  top:0px;
  background-color:red;
  width: 21cm;

}
@endif


.p_label img {
  display: inline;
}
</style>

<style media="screen">
#table_container {
  min-height: 100px;
  border: 1px solid black !important;
}

.divFooter {
           position: fixed;
           bottom: 0;
           width: 100%;
           display: none;
       }

table {
  border: 0px;
}
thead tr th {
  border: 1px solid black;
}

tbody tr td {
  border-top:0px;
  border-bottom: 0px;
  border-left:1px solid black;
  border-right:1px solid black;
}


@if ($print->paper_size == 'A4')
.print_form {
  position: relative;
  margin: auto;
  background-color:white;
  width: 21cm;
  height: 29.7cm;
  border: 1px dotted gray;
}

.footer1 {
  position: relative;
  background-color:transparent;
  width: 21cm;
}
@else
.print_form {
  position: relative;
  margin: auto;
  background-color:white;
  width: 21cm;
  height: 14cm;
  border: 1px dotted gray;
}

.footer1 {
  position: relative;
  background-color:transparent;
  width: 21cm;
}
@endif


  .p_textbox {
    color:black;
  }

  .col-selected {
    background-color: #C8DCE3;
  }
</style>

<script type="text/javascript">
    var details = <?php echo json_encode($data_details) ?>;
    var prints = <?php echo json_encode($print) ?>;
    var print_details = <?php echo json_encode($print_details) ?>;
    var print_properties = <?php echo json_encode($print_properties) ?>;
    var id = '';

    $('#btn_print').click(function() {
        // window.print();
        //  setTimeout(function () {
        //    window.close();
        //
        //  }, 100);
        window.onafterprint = function(e){
          $(window).off('mousemove', window.onafterprint);
          // console.log('Print Dialog Closed..');
          $.ajax({
              url: "/print/{{$category}}/set-print-flag" ,
              type: 'POST',
              data: { _token: '{{{ csrf_token() }}}', 'transaction_id' : "{{$id}}"},
              dataType: 'json', //json
              success: function( _response ){
                console.log(_response);
              },
              error: function( _response ){
                // bootbox.alert(_response.responseText);
              }
          });

        };

        window.print();

        setTimeout(function(){
              $(window).one('mousemove', window.onafterprint);
        }, 1);

        //  window.print();
        //  setTimeout(function () {
        //    window.open('', '_self', '');
        //    window.close();
        //  }, 100);


    });

    // (function() {
    //       var beforePrint = function() {
    //           console.log('Functionality to run before printing.');
    //       };
    //       var afterPrint = function() {
    //           console.log('Functionality to run after printing');
    //       };
    //
    //       if (window.matchMedia) {
    //           var mediaQueryList = window.matchMedia('print');
    //           mediaQueryList.addListener(function(mql) {
    //               if (mql.matches) {
    //                   beforePrint();
    //               } else {
    //                   afterPrint();
    //               }
    //           });
    //       }
    //
    //       window.onbeforeprint = beforePrint;
    //       window.onafterprint = afterPrint;
    //   }());

    $("body").on("change","#cmb_print_name",function () {
         window.location.href = "/{{$category}}/{{$id}}/print/" + $("#cmb_print_name").val();
    })

  $(document).ready(function () {
      $.ajax({
          url: "/print/get-print-cat/{{$category}}" ,
          type: 'GET',
          data: { _token: '{{{ csrf_token() }}}'},
          dataType: 'json', //json
          success: function( _response ){
            $("#cmb_print_name").html("");
            $("#cmb_print_name").append(opt);
            for (i=0;i<_response.data.length;i++) {
                var opt = "<option value='" + _response.data[i]['id'] + "'> " +_response.data[i]['name'] + " </option>";
                $("#cmb_print_name").append(opt);
            }
            $("#cmb_print_name").val("{{$print_id}}");
          },
          error: function( _response ){
             bootbox.alert(_response.responseText);
          }
      });


      $.each(prints, function(key, value) {
          switch (key) {
          case 'paper_size':
            // if (value =='A4') {
            //   $('.print_form').css('width','21cm');
            //   $('.print_form').css('height', '29.7cm');
            // } else if (value == 'LETTER') {
            //   $('.print_form').css('width', '8.5in');
            //   $('.print_form').css('height', '11in');
            // } else if (value == 'SLIP') {
            //   $('.print_form').css('width', '8.5in');
            //   $('.print_form').css('height', '5.5in');
            // }
            break;
          case 'paper_orientation':
            if (value =='LANDSCAPE') {
              var width = $('.print_form').css('height');
              var height = $('.print_form').css('width');
              $('.print_form').css('width',width);
              $('.print_form').css('height' ,height);
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

      for(var i=0;i<print_details.length;i++) {
         switch (print_details[i]['kind']) {
           case 'detail':
                  arr = jQuery.grep(print_properties, function( n) {
                      return ( n['print_detail_id'] == print_details[i]['id'] );
                  });
                  var $th = $("<th>", {id: print_details[i]['id'], "value" : print_details[i]['value']});
                  for(var j=0;j<arr.length;j++) {
                    switch (arr[j]['name']) {
                      case 'header_text':
                        $th.html(arr[j]['value']);
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

             break;
             case 'footer':
                   arr = jQuery.grep(print_properties, function( n) {
                       return ( n['print_detail_id'] == print_details[i]['id'] );
                   });
                 //  console.log(arr);
                   var p_id = print_details[i]['id'];
                   var p_kind = print_details[i]['kind'];
                   var p_value = print_details[i]['value'];
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

                   switch (print_details[i]['type']) {
                     case 'textbox':

                       createTextbox(p_kind,".footer1", p_id, p_top,p_left,
                               p_width,p_height,
                               p_horizontal_align,p_vertical_align,
                               p_border_flag,p_border_width,p_border_color,p_border_style,
                               p_line_height,p_padding_x,p_padding_y,p_font_weight,p_font_style,
                               p_font_size,p_font_color,p_font_family,p_use_parent_flag,
                               p_value);
                       break;
                     case 'label':
                     createLabel(p_kind,".footer1", p_id, p_top,p_left,
                             p_width,p_height,
                             p_horizontal_align,p_vertical_align,
                             p_border_flag,p_border_width,p_border_color,p_border_style,
                             p_line_height,p_padding_x,p_padding_y,p_font_weight,p_font_style,
                             p_font_size,p_font_color,p_font_family,p_use_parent_flag,
                             p_value,p_image_flag);
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

                    arr = jQuery.grep(print_properties, function( n) {
                        return ( n['print_detail_id'] == print_details[i]['id'] );
                    });
                  //  console.log(arr);
                    var p_id = print_details[i]['id'];
                    var p_kind = print_details[i]['kind'];
                    var p_value = print_details[i]['value'];
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

                    switch (print_details[i]['type']) {
                      case 'textbox':
                        createTextbox(p_kind,".print_form", p_id, p_top,p_left,
                                p_width,p_height,
                                p_horizontal_align,p_vertical_align,
                                p_border_flag,p_border_width,p_border_color,p_border_style,
                                p_line_height,p_padding_x,p_padding_y,p_font_weight,p_font_style,
                                p_font_size,p_font_color,p_font_family,p_use_parent_flag,
                                p_value);
                        break;
                      case 'label':
                      createLabel(p_kind,".print_form", p_id, p_top,p_left,
                              p_width,p_height,
                              p_horizontal_align,p_vertical_align,
                              p_border_flag,p_border_width,p_border_color,p_border_style,
                              p_line_height,p_padding_x,p_padding_y,p_font_weight,p_font_style,
                              p_font_size,p_font_color,p_font_family,p_use_parent_flag,
                              p_value,p_image_flag);
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
      $('#table1 tbody').html("");
      var column_count = 0;
      $.each(details, function(key, value) {
        var row = "<tr style='height:{{$print->row_height}}'>";
        column_count = value.length;
        for(var i=0;i<value.length;i++) {
          row += "<td style='padding:2px;vertical-align:top;text-align:" + $('#table1 thead tr').eq(0).find('th').eq(i).attr('textalign') + ";font-size:" + $('#table1 thead tr').eq(0).find('th').eq(i).attr('fontsize') + "'>" + value[i] + "</td>";
        }
        row += "</tr>"
        $('#table1 tbody').append(row);
      });

      if (details.length < '{{$print->table_row_count}}') {
        for(var i=0 ; i< ('{{$print->table_row_count}}' - details.length);i++) {
          var row = "<tr style='height:{{$print->row_height}}'>";
          for(var j=0;j<column_count;j++) {
            row += "<td></td>";
          }
          row += "</tr>"
          $('#table1 tbody').append(row);
        }
      }

      // for (var i =0;i<10;i++) {
      //     var row = "<tr style='height:25px'>";
      //     for(var j=0;j<4;j++) {
      //       row += "<td>"+ i +"</td>";
      //     }
      //     row += "</tr>"
      //     $('#table1 tbody').append(row);
      // }

});
var createTextbox = function(kind,container,p_id, top,left,
                            width,height,
                            horizontalAlign,verticalAlign,
                            borderFlag,borderWidth,borderColor,borderStyle,
                            lineHeight,paddingX,paddingY,
                            fontWeight,fontStyle,
                            fontSize,fontColor,fontFamily,useParentFontFlag,
                            value){
var $div = $("<div>", {id: p_id ,class: "p_textbox", "data-kind" : kind,  "data-border_flag" : borderFlag, "data-parent_flag" : useParentFontFlag});
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
                            value, image_flag){
var $div = $("<div>", {id: p_id ,class: "p_label", "data-kind" : kind, "data-border_flag" : borderFlag, "data-parent_flag" : useParentFontFlag});
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
  // $(".p_border").draggable({
  //   grid: [ 5, 5 ],
  //   containment: container,
  //   scroll: false,
  //   handle: ".drag_button"
  //  });
  // $(".p_border").resizable({
  //   grid: 5
  // });
}



</script>

@endsection
