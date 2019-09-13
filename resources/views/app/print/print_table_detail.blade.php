<div data-cond="" data-colnumber=""  data-row="" class="modal fade" id="detail-table-form" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
               <h3>Table Detail</h3>
            </div>
            <div class="modal-body">
              <div class="row row-space">
                <div class="col-md-2">
                  <label for="">Table Top</label>
                </div>
                <div class="col-md-10">
                  <input type="text" class="form-control" placeholder="Table Top" id="tb_table_top"  name="tb_table_top" value="">
                </div>
              </div>
              <div class="row row-space">
                <div class="col-md-2">
                  <label for="">Row Number</label>
                </div>
                <div class="col-md-10">
                  <input type="text" class="form-control" placeholder="Detail Number" id="tb_table_row_number"  name="tb_table_row_number" value="">
                </div>
              </div>
              <div class="row row-space">
                <div class="col-md-2">
                  <label for="">Row Height</label>
                </div>
                <div class="col-md-10">
                  <input type="text" class="form-control" placeholder="Row Height" id="tb_table_row_height"  name="tb_table_row_height" value="">
                </div>
              </div>
              <div class="row row-space">
                <div class="col-md-2">
                  <label for="">Border Style</label>
                </div>
                <div class="col-md-10">
                  <select class="form-control" name="tb_table_border_style">
                    <option value="full">full</option>
                    <option value="vertical">vertical</option>
                    <option value="horizontal">horizontal</option>
                    <option value="none">none</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <div class="row">
                <div class="col-md-12">

                  <button class="btn btn-primary btn-ok" onclick="select_data_detail_table();">Save</button>
                  <button type="button" class="btn btn-danger btn-close" data-dismiss="modal">Cancel</button>
                </div>
              </div>

            </div>
        </div>
    </div>
</div>

<script type="text/javascript">

$(function() {
        $('#tb_font_color').colorpicker();
        $('#tb_border_color').colorpicker();
    });

$(document).ready(function() {

    $('#detail-table-form').on('show.bs.modal', function() {

        if ($('#detail-table-form').data('cond') !== 'insert') {
          var dtrow = $('#detail-table-form').data('row');
          $("#tb_table_top").val(dtrow['table_top']);
          $("#tb_table_row_number").val(dtrow['table_row_number']);
          $("#tb_table_row_height").val(dtrow['table_row_height']);
          $("#tb_table_border_style").val(dtrow['table_border_style']);
          $("#tb_table_border_style").val(dtrow['table_border_style']);

        }
    })
});

var select_data_detail_table = function() {
    var table_container = $('#table_container');
    var table = $('#table1 tbody');
    table_container.css('top', $('#tb_table_top').val());
    $("#table1 tbody").html("");
    for (var i=0;i<$("#tb_table_row_number").val();i++) {
      var row = "<tr>";
      for (var j=0;j<$('#detail-table-form').data('colnumber');j++) {
          row += "<td>" + (i + 1) + "</td>"
      }
      row += "</tr>";
      table.append(row);
    }
    table.find('tr').css("height",$("#tb_table_row_height").val());
    table.data("borderstyle",$("#tb_table_border_style").val());

    $("#detail-table-form").modal("hide");
}



</script>

<style media="screen">


</style>
