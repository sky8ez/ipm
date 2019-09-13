@extends('layouts.main')

@section('content')
<link media="all" type="text/css" rel="stylesheet" href="{{ URL::asset('plugins/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css') }}">
<link media="all" type="text/css" rel="stylesheet" href="{{ URL::asset('plugins/dragtable/dragtable.css') }}">
<script src="{{ asset('plugins/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js') }}"></script>
<script src="{{ asset('plugins/dragtable/jquery.dragtable.js') }}"></script>
<script src="{{ asset('plugins/colResizeable/colResizable-1.6.min.js') }}"></script>

<form class="" action="#" method="post">
      <input type="hidden" name="data_detail" id="data_detail" value="">
      <input type="hidden" name="data_header" id="data_header" value="">
    {{ csrf_field() }}
  <div class="panel panel-default">
    <div class="panel-heading" style="text-align:right">
        <button type="submit" name="btn_save" class="btn btn-primary">Save</button>
    </div>
    <div class="panel-body">
      <div class="print_control">
        <div class="row">
          <div class="col-md-2">
          </div>
          <div class="col-md-2">
            <button type="button" class="form-control" id="btn_insert_text" name="btn_insert_text">Insert Textbox</button>
          </div>
          <div class="col-md-2">
            <button type="button" class="form-control" id="btn_insert_label" name="btn_insert_label">Insert Label</button>
          </div>
          <div class="col-md-2">
            <button type="button" class="form-control" id="btn_insert_rectangle" name="btn_insert_rectangle">Insert Rectangle</button>
          </div>
          <div class="col-md-2">
            <button type="button" class="form-control" id="btn_insert_row" name="btn_insert_row">Insert Table Row</button>
          </div>
          <div class="col-md-2">
          </div>
        </div>
      </div>
      <div id="print_form">
        <div id="table_container">
          <table width="100%" id="table1" width="100%" border="1" data-borderstyle="full">
            <thead>
              <tr>

              </tr>
            </thead>
            <tbody>
              <tr></tr>
              <tr></tr>
              <tr></tr>
              <tr></tr>
              <tr></tr>
              <tr></tr>
              <tr></tr>
              <tr></tr>
            </tbody>
            <tfoot>

            </tfoot>
          </table>
          <div class="drag_button"><i class="fa fa-arrows fa-1x"></i></div>
          <a style="display:none;z-index:900" class="btn_setting btn_setting_table" href="#"><i class="fa fa-gear"></i></a>
        </div>
      </div>


    </div>
  </div>
</form>




@include('app.print.print_template_detail')
@include('app.print.print_table_detail')
@include('app.print.print_column_detail')

<style media="screen">

  #print_form {
    position: relative;
    margin: auto;
    margin-top: 50px;
    background-color:#E5E8D3;
    width: 21cm;
    height: 5.5in;
    border: 1px solid gray;
  }

  .drag_button {
    padding-top: 0px;
    width: 12px;
    height: 15px;
    color: black;
    position:absolute;
    bottom:-5px;
    right: -5px;
    z-index: 100;
    cursor: move;
  }

  textarea{
    width: 100%;
    height: 100%;
    resize: none;
  }

  #print_form input{
    width: 100%;
    height: 100%;
    background-color: transparent;
    border:none;
  }

  .col-selected {
    background-color: #C8DCE3;
  }

.ui-selected > textarea {
  /*border: 2px blue solid !important;*/
  background-color: #83C5C9 !important;
}

.ui-selected > input {
  /*border: 2px blue solid !important;*/
  background-color: #83C5C9 !important;
}


</style>

<script type="text/javascript">
    var selected = $([]), offset = {top:0, left:0}; //to store selected object

    var print = <?php echo json_encode($print) ?>;
    var print_details = <?php echo json_encode($print_details) ?>;
    var print_properties = <?php echo json_encode($print_properties) ?>;
    var id = {{$id}}

    var columns_header = <?php echo json_encode($columns_header) ?>;
    var columns_detail = <?php echo json_encode($columns_detail) ?>;

    $(function(){
  // $("#table1").colResizable({ disable : true });
});

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

      $( "#print_form" ).data("selectable")._mouseStop(null);
    });

    var getDataHeader = function() {
      var table_con = $('#table_container');
      var results = {'table_row_height' : $('#table1 tbody').find('tr:first-child').css("height"),
                     'table_top' : $('#table_container').css("top"),
                     'table_row_count' : $('#table1 tbody tr').length,
                     'table_border_style' : $('#table1').data("borderstyle")};
      return results;
    }

    var getDataDetail = function(TableId) {
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
          var p_border_flag = $this.data("border_flag");
          var p_border_width = $this.css("border-top-width");
          var p_border_color = $this.css("border-top-color");
          var p_border_style = $this.css("border-top-style");

          result_prop = {'footer_top' : p_footer_top,'top' : p_top, 'left' : p_left,
                        'width' : p_width, 'height' : p_height,'horizontal_align' : p_horizontal_align, 'vertical_align' : p_vertical_align,
                        'padding_x': p_padding_x, 'padding_y' : p_padding_y,
                        'line_height' : p_line_height,'font_weight' : p_font_weight, 'font_style' : p_font_style, 'font_size' : p_font_size, 'font_family' : p_font_family,
                        'font_color' : p_font_color, 'use_parent_font_flag' : p_use_parent_flag, 'border_flag' : p_border_flag,
                        'border_width' : p_border_width, 'border_color' : p_border_color, 'border_style' : p_border_style}

          var result = {'sequence_no' : 0, 'deleteflag' : deleteflag,'kind' : p_kind, 'type' : 'textbox', 'id' : p_id, 'value' : p_value, 'properties' : result_prop};
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
          var p_border_flag = $this.data("border_flag");
          var p_border_width = $this.css("border-top-width");
          var p_border_color = $this.css("border-top-color");
          var p_border_style = $this.css("border-top-style");

          var result_prop = { 'footer_top' : p_footer_top, 'top' : p_top, 'left' : p_left,
                        'width' : p_width, 'height' : p_height, 'horizontal_align' : p_horizontal_align, 'vertical_align' : p_vertical_align,
                        'padding_x': p_padding_x, 'padding_y' : p_padding_y,
                        'line_height' : p_line_height,'font_weight' : p_font_weight, 'font_style' : p_font_style, 'font_size' : p_font_size, 'font_family' : p_font_family,
                        'font_color' : p_font_color, 'use_parent_font_flag' : p_use_parent_flag, 'border_flag' : p_border_flag,
                        'border_width' : p_border_width, 'border_color' : p_border_color, 'border_style' : p_border_style, 'image_flag' : p_image_flag};

          var result = {'sequence_no' : 0, "deleteflag" : deleteflag, 'kind' : p_kind,'type' : 'label' , 'id' : p_id, 'value' : p_value, 'properties' : result_prop};
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

          var result = {'sequence_no' : 0,'deleteflag' : deleteflag, 'kind' : p_kind,'type' : 'rectangle', 'id' : p_id, 'value' : '', 'properties' : result_prop };
          console.log(result);
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
                          'font_color' : p_font_color, 'use_parent_font_flag' : p_use_parent_flag}

            var result = {'sequence_no' : sequence_no, 'deleteflag' : p_delete_flag,'kind' : 'detail','type' : '', 'id' : p_id, 'value' : p_value, 'properties' : result_prop};
            results.push(result);
        });

        return results;
    }

  $(document).ready(function () {
      $.each(print, function(key, value) {
          switch (key) {
          case 'paper_size':
            if (value =='A4') {
              $('#print_form').css('width','21cm');
              $('#print_form').css('height' ,'29.7cm');
            } else if (value == 'LETTER') {
              $('#print_form').css('width', '8.5in');
              $('#print_form').css('height', '11in');
            } else if (value == 'SLIP') {
              $('#print_form').css('width', '8.5in');
              $('#print_form').css('height', '5.5in');
            }
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

                //  $( "#table1" ).colResizable();
                // $('#table1').dragtable({});

             break;
             case 'footer':
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
                        createTextbox(p_kind,"#print_form", p_id, p_top,p_left,
                                p_width,p_height,
                                p_horizontal_align,p_vertical_align,
                                p_border_flag,p_border_width,p_border_color,p_border_style,
                                p_line_height,p_padding_x,p_padding_y,p_font_weight,p_font_style,
                                p_font_size,p_font_color,p_font_family,p_use_parent_flag,
                                p_value);
                        break;
                      case 'label':
                      createLabel(p_kind,"#print_form", p_id, p_top,p_left,
                              p_width,p_height,
                              p_horizontal_align,p_vertical_align,
                              p_border_flag,p_border_width,p_border_color,p_border_style,
                              p_line_height,p_padding_x,p_padding_y,p_font_weight,p_font_style,
                              p_font_size,p_font_color,p_font_family,p_use_parent_flag,
                              p_value,p_image_flag);
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
        scroll: false,
        handle: ".drag_button"
       });

       $('#table1').dragtable();
      $( "#table1" ).colResizable({
        liveDrag:true
    });


     $('#btn_insert_text').click(function () {
       createTextbox("header","#print_form", "","200px","300px", //top, left
                     "200px","30px", //width, height
                     "left","middle", //horizontal align, vertical align
                     false,"1px","black","solid", //borderFlag,borderSize,borderColor,borderStyle
                     "default","default","default","normal","normal", //lineHeight, padding-X, padding-Y,fontweight,font style
                     "default","black","Arial",false, //fontSize,fontColor,fontFamily,parentFont
                     "") //value
     });
     $('#btn_insert_label').click(function () {
       createLabel("header","#print_form", "","200px","300px", //top, left
                     "200px","30px", //width, height
                     "left","middle", //horizontal align, vertical align
                     false,"1px","black","solid", //borderFlag,borderSize,borderColor,borderStyle
                     "default","default","default","normal","normal", //lineHeight, padding-X, padding-Y, fontweight,fontstyle
                     "default","black","Arial",false, //fontSize,fontColor,fontFamily,parentFont
                     "") //value
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
         $('#table1').dragtable('destroy').dragtable({});
            $( "#table1" ).colResizable({
              liveDrag:true
          });
     });

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

  });

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
                 'borderWidth' : parent.css("border-top-width"),'borderColor' : parent.css("border-top-color"),'borderStyle' : parent.css("border-top-style"),'image_flag' : parent.attr("image_flag")
               };
    $("#detail-form").data('cond', "update" );
    $("#detail-form").data('row', datas );
    $('#detail-form').modal('show');
  })

  $('#print_form').on('click','.btn_setting_table',function() {
    var parent = $('#table1');
    var datas = {'table_top' : $('#table_container').css("top"),'table_row_number' : $('#table1 tbody tr').length, 'table_row_height' : parent.find('tbody tr:first-child').css('height') , 'table_border_style' : parent.data('borderstyle')};
    $("#detail-table-form").data('cond', "update" );
    $("#detail-table-form").data('row', datas );
    $("#detail-table-form").data('colnumber', $('#table1 tbody tr:first-child td').length );
    $('#detail-table-form').modal('show');
  })

  $('#print_form').on('dblclick','tbody td',function() {
    var column = $('#table1 th.col-selected');
    var datas = {'header_text': column.html(), 'value' : column.attr("value"), 'width' : column.css("width"),
                 'text_align' : column.attr("textalign"),'vertical_align' : column.attr("veralign"),
                 'line_height':column.attr("lineheight"), 'padding_x' : column.attr("paddingx"), 'padding_y' : column.attr("paddingy"),
                 'fontWeight' : column.find("textarea").css("font-weight"),'fontStyle' : column.find("textarea").css("font-style"),
                 'font_size':column.attr("fontsize"), 'font_family' : column.attr("fontfamily"), 'font_color' : column.attr("fontcolor"),
                 'font_flag' : column.attr("fontflag")};

    $("#detail-column-form").data('cond', "update" );
    $("#detail-column-form").data('row', datas );
    $('#detail-column-form').modal('show');
  })

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
              selected = $(".ui-selected").each(function() {
                 var el = $(this);
                 el.data("offset", el.offset());
              });
          }
          else {
              selected = $([]);
              $(".p_textbox").removeClass("ui-selected");
          }
          offset = $(this).offset();
      },
      drag: function(ev, ui) {
          var dt = ui.position.top - offset.top, dl = ui.position.left - offset.left;
          // take all the elements that are selected expect $("this"), which is the element being dragged and loop through each.
          selected.not(this).each(function() {
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
      source: columns_header
    });
  }

  var createLabel = function(kind,container,p_id,top,left,
                              width,height,
                              horizontalAlign,verticalAlign,
                              borderFlag,borderWidth,borderColor,borderStyle,
                              lineHeight,paddingX,paddingY,
                              fontWeight,fontStyle,
                              fontSize,fontColor,fontFamily,useParentFontFlag,
                              value, image_flag){
  var $div = $("<div>", {id: p_id ,class: "p_label", "data-kind" : kind, "data-border_flag" : borderFlag, "data-parent_flag" : useParentFontFlag , "image_flag" : image_flag});
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
              selected = $(".ui-selected").each(function() {
                 var el = $(this);
                 el.data("offset", el.offset());
              });
          }
          else {
              selected = $([]);
              $(".p_textbox").removeClass("ui-selected");
          }
          offset = $(this).offset();
      },
      drag: function(ev, ui) {
          var dt = ui.position.top - offset.top, dl = ui.position.left - offset.left;
          // take all the elements that are selected expect $("this"), which is the element being dragged and loop through each.
          selected.not(this).each(function() {
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
              selected = $(".ui-selected").each(function() {
                 var el = $(this);
                 el.data("offset", el.offset());
              });
          }
          else {
              selected = $([]);
              $(".p_textbox").removeClass("ui-selected");
          }
          offset = $(this).offset();
      },
      drag: function(ev, ui) {
          var dt = ui.position.top - offset.top, dl = ui.position.left - offset.left;
          // take all the elements that are selected expect $("this"), which is the element being dragged and loop through each.
          selected.not(this).each(function() {
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


  $('form').submit(function( event ) {
    $("#data_header").val(JSON.stringify(getDataHeader()));
    $("#data_detail").val(JSON.stringify(getDataDetail()));

      event.preventDefault();
      $.ajax({
          url: "/print/" + id + "/editor",
          type: 'post',
          data: $('form').serialize(),
          dataType: 'json',
          success: function( _response ){
              if( _response.status == "OK") {
                bootbox.alert("Update Success");
                setTimeout(function(){
                  window.location.href = _response.url;
                }, 2000);

              } else {
                  msg = '';
                  for (i=0;i<_response.msg.length;i++) {
                    msg = msg + _response.msg[i] + '<br />';
                  }
                  bootbox.alert(msg );
              }
          },
          error: function( _response ){
              bootbox.alert(_response.responseText);
          }
      });

  });


</script>

<!--
<script type="text/javascript">
// this creates the selected variable
// we are going to store the selected objects in here
var selected = $([]), offset = {top:0, left:0};

$( "#print_form > div" ).draggable({
  start: function(ev, ui) {
      if ($(this).hasClass("ui-selected")){
          selected = $(".ui-selected").each(function() {
             var el = $(this);
             el.data("offset", el.offset());
          });
      }
      else {
          selected = $([]);
          $("#print_form > div").removeClass("ui-selected");
      }
      offset = $(this).offset();
  },
  drag: function(ev, ui) {
      var dt = ui.position.top - offset.top, dl = ui.position.left - offset.left;
      // take all the elements that are selected expect $("this"), which is the element being dragged and loop through each.
      selected.not(this).each(function() {
           // create the variable for we don't need to keep calling $("this")
           // el = current element we are on
           // off = what position was this element at when it was selected, before drag
           var el = $(this), off = el.data("offset");
          el.css({top: off.top + dt, left: off.left + dl});
      });
  },
});

$( "#print_form" ).selectable();

// manually trigger the "select" of clicked elements
$( "#print_form > div" ).click( function(e){
  if (e.metaKey == false) {
      // if command key is pressed don't deselect existing elements
      $( "#print_form > div" ).removeClass("ui-selected");
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

  $( "#print_form" ).data("selectable")._mouseStop(null);
});

// starting position of the divs
var i = 0;
$("#print_form > div").each( function() {
  $(this).css({
      top: i * 42
  });
  i++;
});
</script> -->


@endsection
