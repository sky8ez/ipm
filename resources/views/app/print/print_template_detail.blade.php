<div data-cond="" data-row="" class="modal fade" id="detail-form" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
               <h3>Detail</h3>
            </div>
            <div class="modal-body">
              <div class="row row-space">
                <div class="col-md-2">
                  <textarea data-toggle="tooltip" data-placement="top" title="Value"  placeholder="Value" class="form-control" name="tb_value" id="tb_value" rows="2" cols="20"></textarea>
                </div>
                <div class="col-md-2">
                  <div class="">
                    <input type="checkbox" name="cb_image" id="cb_image" value=""> Image
                  </div>
                  <div class="">
                    <a class="btn btn-success btn-xs btn_browse" href="#">Browse..</a>
                  </div>
                </div>
                <div class="col-md-2">
                  <select data-toggle="tooltip" data-placement="top" title="Category" class="form-control" name="tb_kind" id="tb_kind">
                    <option value="header">header</option>
                    <option value="footer">footer</option>
                  </select>
                </div>
                <div class="col-md-2">
                  <input data-toggle="tooltip" data-placement="top" title="Header Text" type="text" class="form-control" placeholder="Header Text" id="tb_header_text"  name="tb_header_text" value="">
                </div>
                <div class="col-md-2">
                  <input data-toggle="tooltip" data-placement="top" title="Line Height" type="text" class="form-control" placeholder="Line Height" id="tb_line_height"  name="tb_line_height" value="">
                </div>
                <div class="col-md-2">
                  <input data-toggle="tooltip" data-placement="top" title="Padding X" type="text" class="form-control" placeholder="Padding X" id="tb_padding_x" name="tb_padding_x" value="">
                </div>
                <div class="col-md-2">
                  <input data-toggle="tooltip" data-placement="top" title="Padding Y" type="text" class="form-control" placeholder="Padding Y" id="tb_padding_y" name="tb_padding_y" value="">
                </div>
              </div>
              <div class="panel panel-default">
                <div class="panel-heading">Size and Position</div>
                <div class="panel-body">
                  <div class="col-md-3">
                    <input data-toggle="tooltip" data-placement="top" title="Top" type="text" class="form-control" placeholder="Top" id="tb_top" name="tb_top" value="">
                  </div>
                  <div class="col-md-3">
                    <input data-toggle="tooltip" data-placement="top" title="Left" type="text" class="form-control" placeholder="Left" id="tb_left" name="tb_left" value="">
                  </div>
                  <div class="col-md-3">
                    <input data-toggle="tooltip" data-placement="top" title="Width" type="text" class="form-control" placeholder="Width" id="tb_width" name="tb_width" value="">
                  </div>
                  <div class="col-md-3">
                    <input data-toggle="tooltip" data-placement="top" title="Height" type="text" class="form-control" placeholder="Height" id="tb_height" name="tb_height" value="">
                  </div>
                </div>
              </div>
              <div class="panel panel-default">
                <div class="panel-heading">Font</div>
                <div class="panel-body">
                  <div class="row">
                    <div class="col-md-3">
                      <input data-toggle="tooltip" data-placement="top" title="Font Size" type="text" class="form-control" placeholder="Font size" id="tb_font_size" name="tb_font_size" value="">
                    </div>
                    <div class="col-md-3">
                      <input data-toggle="tooltip" data-placement="top" title="Font Family" type="text" class="form-control" placeholder="Font family" id="tb_font_family" name="tb_font_family" value="">
                    </div>
                    <div class="col-md-3">
                      <input data-toggle="tooltip" data-placement="top" title="Font Color" type="text" class="form-control" placeholder="Font Color" id="tb_font_color" name="tb_font_color" value="">
                    </div>
                    <div class="col-md-3">
                      <input data-toggle="tooltip" data-placement="top" title="Use Parent Font Flag" type="checkbox" id="cb_font_flag" name="cb_font_flag" value="">
                      Use Parent Font Family
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-3">
                      <input data-toggle="tooltip" data-placement="top" title="Font Style" type="text" class="form-control" placeholder="Font Style" id="tb_font_style" name="tb_font_style" value="">
                    </div>
                    <div class="col-md-3">
                      <input data-toggle="tooltip" data-placement="top" title="Font Weight" type="text" class="form-control" placeholder="Font Weight" id="tb_font_weight" name="tb_font_weight" value="">
                    </div>
                    <div class="col-md-3">
                      <input data-toggle="tooltip" data-placement="top" title="Text Align" type="text" class="form-control" placeholder="Text Align" id="tb_text_align" name="tb_text_align" value="">
                    </div>
                    <div class="col-md-3">
                      <input data-toggle="tooltip" data-placement="top" title="Vertical Align" type="text" class="form-control" placeholder="Vertical Align" id="tb_vertical_align" name="tb_vertical_align" value="">
                    </div>
                  </div>
                </div>
              </div>
              <div class="panel panel-default">
                <div class="panel-heading">
                  <input data-toggle="tooltip" data-placement="top" title="Show Border" type="checkbox" id="cb_border_flag" name="cb_border_flag" value="">
                   Border
                </div>
                <div class="panel-body">
                  <div class="col-md-3">
                    <input data-toggle="tooltip" data-placement="top" title="Border Width" type="text" class="form-control" placeholder="Border Width" id="tb_border_width" name="tb_border_width" value="">
                  </div>
                  <div class="col-md-3">
                    <input data-toggle="tooltip" data-placement="top" title="Border Color" type="text" class="form-control" placeholder="Border Color" id="tb_border_color" name="tb_border_color" value="">
                  </div>
                  <div class="col-md-3">
                    <input data-toggle="tooltip" data-placement="top" title="Border Style" type="text" class="form-control" placeholder="Border Style" id="tb_border_style" name="tb_border_style" value="">
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <div class="row">
                <div class="col-md-12">
                  <button class="btn btn-primary btn-ok1" onclick="delete_data_detail();">Delete</button>
                  <button class="btn btn-primary btn-ok1" onclick="select_data_detail();">Save</button>
                  <button type="button" class="btn btn-danger btn-close" data-dismiss="modal">Cancel</button>
                </div>
              </div>

            </div>
        </div>
    </div>
</div>

@include('module.search-data')

<script type="text/javascript">

$(document).on("click", ".btn_browse", function () {
   var table = "file";
   var count = "";
   if (table.split("-").length > 1) {
     count = "-" + table.split("-")[1];
   }
   var headerdependency = $(this).data('headerdependency');
   var dependency = $(this).data('dependency') + "_id";
   var dependency_value = $("#" + dependency + count).val();
   var child = "#" + $(this).data('child');
   if (dependency !== "" && dependency_value == "") {
     bootbox.alert("Column " + $(this).data('dependency') + " must selected before")
   } else {
     $("#search-form").data('outputname', "#tb_value" );
     $("#search-form").data('table', table.split("-")[0] );
     $("#search-form").data('dependency', dependency );
     $("#search-form").data('dependencyValue', dependency_value );
     $("#search-form").data('child', child );
     $("#search-form").data('headerdependency', headerdependency );
     $("#search-form").data('headerdependencyid', $("#" + headerdependency).val());

     $('.table-search tbody tr').remove();
     $('#search-form').modal('show');
   }

});

$(document).ready(function(){
    $('[data-toggle="tooltip"]').tooltip();
});

$(function() {
        $('#tb_font_color').colorpicker();
        $('#tb_border_color').colorpicker();
    });

$(document).ready(function() {
    $('#detail-form').on('show.bs.modal', function() {

        if ($('#detail-form').data('cond') !== 'insert') {
          var dtrow = $('#detail-form').data('row');
          $("#tb_kind").val(dtrow['kind']);
          $("#tb_value").val(dtrow['value']);
          var image_flag = false;
          if (dtrow['image_flag'] == "true") {
            image_flag = true;
          } else {
            image_flag = false;
          }
          $("#cb_image").prop("checked",image_flag);
          $("#tb_header_text").val(dtrow['headerText']);
          $("#tb_line_height").val(dtrow['lineHeight']);
          $("#tb_padding_x").val(dtrow['paddingX']);
          $("#tb_padding_y").val(dtrow['paddingY']);
          $("#tb_top").val(dtrow['top']);
          $("#tb_left").val(dtrow['left']);
          $("#tb_width").val(dtrow['width']);
          $("#tb_height").val(dtrow['height']);

          $("#tb_font_family").val(dtrow['fontFamily']);
          $("#tb_font_size").val(dtrow['fontSize']);
          $("#tb_font_color").val(dtrow['fontColor']);
          $("#cb_font_flag").prop("checked",dtrow['useParentFontFlag']);
          $("#tb_font_style").val(dtrow['fontStyle']);
          $("#tb_font_weight").val(dtrow['fontWeight']);
          $("#tb_text_align").val(dtrow['horizontalAlign']);
          $("#tb_vertical_align").val(dtrow['verticalAlign']);

          $("#tb_border_width").val(dtrow['borderWidth']);
          $("#tb_border_color").val(dtrow['borderColor']);
          $("#tb_border_style").val(dtrow['borderStyle']);
          $("#cb_border_flag").prop("checked",dtrow['borderFlag']);
        }
    })
});

var delete_data_detail = function() {
    var row = $('.selected');
    row.css("display","none");
  $("#detail-form").modal("hide");
}

var select_data_detail = function() {
    var row = $('.selected');
    row.find("textarea").val($('#tb_value').val());
    row.css("top",$('#tb_top').val());
    row.css("left",$('#tb_left').val());
    row.css("width",$('#tb_width').val());
    row.css("height",$('#tb_height').val());
    row.find("textarea").css("font-size",$('#tb_font_size').val());
    row.find("textarea").css("font-family",$('#tb_font_family').val());
    row.find("textarea").css("color",$('#tb_font_color').val());
    row.data("kind",$('#tb_kind').val());
    row.data("parent_flag",$('#cb_font_flag').is(':checked'));
    row.find("textarea").css("font-style",$('#tb_font_style').val());
    row.find("textarea").css("font-weight",$('#tb_font_weight').val());
    row.find("textarea").css("text-align",$('#tb_text_align').val());
    row.find("textarea").css("vertical-align",$('#tb_vertical_align').val());

    // row.css("border-top-width",$('#tb_border_width').val());
    // row.css("border-top-color",$('#tb_border_color').val());
    // row.css("border-top-style",$('#tb_border_style').val());

    row.css("border",$('#tb_border_width').val() + ' ' + $('#tb_border_color').val() + ' ' + $('#tb_border_style').val());


    row.data("border_flag",$('#cb_border_flag').is(':checked'));
    row.attr("image_flag",$('#cb_image').is(':checked'));
    if ($('#cb_image').is(':checked') == true) {
      row.css("background-image","url('" + $('#tb_value').val() + "')");
      row.css("background-size","cover");
      row.css("color","transparent");
    } else {
      row.css("background-image","");
      row.css("background-size","cover");
        row.css("color","black");
    }


  $("#detail-form").modal("hide");
}



</script>

<style media="screen">


</style>
