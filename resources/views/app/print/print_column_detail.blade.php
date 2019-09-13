<div data-cond="" data-row="" class="modal fade" id="detail-column-form" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
               <h3>Column Detail</h3>
            </div>
            <div class="modal-body">
              <div class="row row-space">
                <div class="col-md-1">
                  <label for="status">Value</label>
                </div>
                <div class="col-md-4">
                  <textarea  placeholder="Value" class="form-control" name="tb_column_value" id="tb_col_value" rows="2" cols="20"></textarea>
                </div>
                <div class="col-md-2">
                  <input type="text" class="form-control" placeholder="Line Height" id="tb_col_line_height"  name="tb_col_line_height" value="">
                </div>
                <div class="col-md-2">
                  <input type="text" class="form-control" placeholder="Padding X" id="tb_col_padding_x" name="tb_col_padding_x" value="">
                </div>
                <div class="col-md-2">
                  <input type="text" class="form-control" placeholder="Padding Y" id="tb_col_padding_y" name="tb_col_padding_y" value="">
                </div>
              </div>
              <div class="panel panel-default">
                <div class="panel-heading">Size</div>
                <div class="panel-body">
                  <div class="col-md-3">
                    <input type="text" class="form-control" placeholder="Header Text" id="tb_header_text" name="tb_header_text" value="">
                  </div>
                  <div class="col-md-3">
                    <input type="text" class="form-control" placeholder="Width" id="tb_col_width" name="tb_col_width" value="">
                  </div>
                </div>
              </div>
              <div class="panel panel-default">
                <div class="panel-heading">Font</div>
                <div class="panel-body">
                  <div class="row">
                    <div class="col-md-3">
                      <input type="text" class="form-control" placeholder="Font size" id="tb_col_font_size" name="tb_col_font_size" value="">
                    </div>
                    <div class="col-md-3">
                      <input type="text" class="form-control" placeholder="Font family" id="tb_col_font_family" name="tb_col_font_family" value="">
                    </div>
                    <div class="col-md-3">
                      <input type="text" class="form-control" placeholder="Font Color" id="tb_col_font_color" name="tb_col_font_color" value="">
                    </div>
                    <div class="col-md-3">
                      <input type="checkbox" id="cb_col_font_flag" name="cb_col_font_flag" value="">
                      Use Parent Font Family
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-3">
                      <input type="text" class="form-control" placeholder="Font Style" id="tb_col_font_style" name="tb_col_font_style" value="">
                    </div>
                    <div class="col-md-3">
                      <input type="text" class="form-control" placeholder="Font Weight" id="tb_col_font_weight" name="tb_col_font_weight" value="">
                    </div>
                    <div class="col-md-3">
                      <input type="text" class="form-control" placeholder="Text Align" id="tb_col_text_align" name="tb_col_text_align" value="">
                    </div>
                    <div class="col-md-3">
                      <input type="text" class="form-control" placeholder="Vertical Align" id="tb_col_vertical_align" name="tb_col_vertical_align" value="">
                    </div>
                  </div>
                </div>
              </div>

            </div>
            <div class="modal-footer">
              <div class="row">
                <div class="col-md-12">
                    <button class="btn btn-primary btn-delete" onclick="delete_data_detail_column();">Delete</button>
                  <button class="btn btn-primary btn-ok" onclick="select_data_detail_column();">Save</button>
                  <button type="button" class="btn btn-danger btn-close" data-dismiss="modal">Cancel</button>
                </div>
              </div>

            </div>
        </div>
    </div>
</div>

<script type="text/javascript">


$(function() {
        $('#tb_col_font_color').colorpicker();
        $('#tb_col_border_color').colorpicker();
    });

$(document).ready(function() {
      $( "#tb_col_value" ).autocomplete({
        source: columns_detail
      });
    $('#detail-column-form').on('show.bs.modal', function() {
        if ($('#detail-column-form').data('cond') !== 'insert') {

          var dtrow = $('#detail-column-form').data('row');
          $("#tb_col_value").val(dtrow['value']);
          $("#tb_header_text").val(dtrow['header_text']);
          $("#tb_col_width").val(dtrow['width']);

          $("#tb_col_line_height").val(dtrow['line_height']);
          $("#tb_col_padding_x").val(dtrow['padding_x']);
          $("#tb_col_padding_y").val(dtrow['padding_y']);

          $("#tb_col_font_size").val(dtrow['font_size']);
          $("#tb_col_font_family").val(dtrow['font_family']);
          $("#tb_col_font_color").val(dtrow['font_color']);
          $("#cb_col_font_flag").prop("checked",dtrow['font_flag']);
          $("#tb_col_font_style").val(dtrow['font_style']);
          $("#tb_col_font_weight").val(dtrow['font_weight']);
          $("#tb_col_text_align").val(dtrow['text_align']);
          $("#tb_col_vertical_align").val(dtrow['vertical_align']);

        }
    })
});


var delete_data_detail_column = function() {
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

var select_data_detail_column = function() {
    var column = $('th.col-selected');

    column.html($('#tb_header_text').val());
    column.css("width", $('#tb_col_width').val());
    column.attr("value", $('#tb_col_value').val());
    column.attr("lineheight", $('#tb_col_line_height').val());
    column.attr("paddingx", $('#tb_col_padding_x').val());
    column.attr("paddingy", $('#tb_col_padding_y').val());

    column.attr("fontsize", $('#tb_col_font_size').val());
    column.attr("fontfamily", $('#tb_col_font_family').val());
    column.attr("fontcolor", $('#tb_col_font_color').val());
    column.attr("fontflag", $('#cb_col_font_flag').is("checked"));
    column.attr("fontstyle", $('#tb_col_font_style').val());
    column.attr("fontweight", $('#tb_col_font_weight').val());
    column.attr("textalign", $('#tb_col_text_align').val());
    column.attr("verticalalign", $('#tb_col_vertical_align').val());

  $("#detail-column-form").modal("hide");
}

</script>

<style media="screen">


</style>
