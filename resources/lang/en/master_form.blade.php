@extends('layouts.main')

@section('content')

  <div class="col-md-8  col-md-offset-2">
    <form id="form_input" class="" action="#" method="post" enctype="multipart/form-data">
      	{{ csrf_field() }}
      <div class="panel panel-default">
        <div class="panel-heading" >
          <div class="row">
              <div class="col-md-4">
                  <h4>{{$title or ''}}
                    @if ($cond !== 'insert')
                      <a id="btn_audit" data-toggle="tooltip" data-placement="right" title="Audit Trail" href="#"><i class="fa fa-history"></i></a>
                    @endif
                  </h4>
              </div>
              <div class="col-md-4" style="text-align:center">
                <h4>
                  @if(isset($nav_flag))
                    <a class="btn_prev" href="{{$prev}}"><i class="fa fa-chevron-left"></i> </a>
                    &nbsp;&nbsp;
                    &nbsp;&nbsp;
                    <a class="btn_next" href="{{$next}}"> <i class="fa fa-chevron-right"></i> </a>
                  @endif
                </h4>
              </div>
              <div class="col-md-4" style="text-align:right">
                  @if ($cond == 'detail')
                    <!-- <button type="button" name="btn_close" class="btn btn-danger"> <i class="fa fa-close"></i> Back</button> -->
                  @elseif ($cond == 'realization')
                    <button type="submit" name="btn_save" id="btn_save" class="btn btn-primary"> <i class="fa fa-check"></i> {{$btn_save_text or 'Realization'}}</button>
                  @elseif ($cond == 'confirmation')
                    <button type="submit" name="btn_save" id="btn_save" class="btn btn-primary"> <i class="fa fa-check"></i> {{$btn_save_text or 'Confirmation'}}</button>
                  @elseif ($cond == 'approval')
                      <div class="row">
                        <div class="col-md-7">
                          <button type="button" id="btn_approve" onclick="approvedOrNot(1)" name="btn_approve" class="btn btn-primary"><i class="fa fa-check"></i> Approve</button>
                          <button type="button" id="btn_decline" onclick="approvedOrNot(2)" name="btn_decline" class="btn btn-primary"><i class="fa fa-remove"></i> Decline</button>
                        </div>
                        <div class="col-md-5">
                          <input type="text" id="tb_reason" name="tb_reason" class="form-control" placeholder="Reason" value="">
                        </div>
                      </div>
                  @else
                    <button type="submit" name="btn_save" id="btn_save" class="btn btn-primary"> <i class="fa fa-save"></i> Save</button>
                  @endif

              </div>
          </div>

        </div>
        <div class="panel-body">
          @include('master_view.master_form_field')
        </div>
      </div>


      @if (isset($forms_details_flag))
      <div class="panel panel-default">
        <div class="panel-heading" >
          <input type='hidden' name="data_detail" id="data_detail" />
          <div class="row">
              <div class="col-md-4">
                  <h4>{{$title or ''}}</h4>
              </div>
              <div class="col-md-8" style="text-align:right">
                  <button type="button" name="btn_detail_insert" id="btn_detail_insert" class="btn btn-primary">Insert</button>
                  <button type="button" name="btn_detail_update" id="btn_detail_update" class="btn btn-primary">Update</button>
                  <button type="button" name="btn_detail_delete" id="btn_detail_delete" class="btn btn-primary">Delete</button>
              </div>
          </div>
        </div>
        <div class="panel-body">
          <div class="row">
            <div class="col-md-12" style="overflow-x:auto">
              <table id="table_detail" class="table table-condensed">
                <thead>
                    <tr>
                      @foreach ($forms_details as $value)
                        @if ($value['show_on_table'] == true)

                          <th>{{$value['text']}}</th>
                        @endif
                      @endforeach
                    </tr>
                </thead>
                <tbody>
                </tbody>
              </table>
            </div>
          </div>


          {{-- FOOTER --}}
          @if (isset($forms_footer))

              @include('master_view.master_form_field_footer')
          @endif


        </div>
      </div>
      @endif

      @if (isset($forms_quick_details_flag))
      <div class="panel panel-default">
        <div class="panel-heading" >
          <input type='hidden' name="data_detail" id="data_detail" />
          <div class="row">
              <div class="col-md-4">
                  <h4>{{$title or ''}}</h4>
              </div>
              <div class="col-md-8" style="text-align:right">
                  <button type="button" name="btn_detail_insert" id="btn_detail_insert" class="btn btn-primary">Insert</button>
                  <!-- <button type="button" name="btn_detail_update" id="btn_detail_update" class="btn btn-primary">Update</button> -->
                  <button type="button" name="btn_detail_delete" id="btn_detail_delete" class="btn btn-primary">Delete</button>
              </div>
          </div>
        </div>
        <div class="panel-body">
          <div class="row">
            <div class="col-md-12" style="overflow-x:scroll">
              <table id="table_detail" class="table table-condensed">
                <thead>
                    <tr>
                      @foreach ($forms_quick_details as $value)
                        @if ($value['show_on_table'] == true)
                          <th>{{$value['text']}}</th>
                        @endif
                      @endforeach
                    </tr>
                </thead>
                <tbody>
                </tbody>
              </table>
            </div>
          </div>

          {{-- FOOTER --}}
          @if (isset($forms_footer))

              @include('master_view.master_form_field_footer')
          @endif

        </div>
      </div>


      @endif




    </form>


  @if (isset($comment_flag))
    <div class="row">
      <div class="col-md-12" style="font-style:italic;color:gray;font-size:12px">
        <div class="well comment-container" style="text-align:left">
          <h3>comments</h3>
          <div class="comment-con">

            @if (isset($comments))
                @foreach ($comments as $comment)
                  <!-- level 1 -->
                  <div class="row" id="{{$comment->id}}">
                      <div class="row"  style="background-color:#C7C7C7;padding:10px;border-radius:5px;margin:5px 5px 0px 5px">
                        <div class="col-md-12">
                          <div class="row">
                            <div class="col-md-2">
                              <b>{{$comment->user}}</b>
                            </div>
                            <div class="col-md-8">
                              {{$comment->comment}}
                            </div>
                            <div class="col-md-2"  style="text-align:right">
                              <!-- <a href="#" class="btn btn-default btn_reply"> <i class="fa fa-reply"></i> Reply</a> -->
                            </div>
                          </div>
                          <div class="row">
                            <div class="col-md-2">
                            </div>
                            <div class="col-md-4">
                              <span style="font-size:11px"><b><i>{{$comment->comment_date}}</i></b></span>
                            </div>
                            <div class="col-md-6" >
                              <b>Reply To : {{$comment->to_user}}</b>
                              <!-- <a class="btn_load_comment" href="#">{{$comment->childCount or 0}} comments</a> -->
                            </div>
                          </div>

                        </div>

                      </div>
                      <div class="row reply-con"  style="display:none;background-color:#DBDBDB;padding:10px;border-radius:5px;margin:0px 5px 5px 150px">
                          <div class="col-md-3">
                            <!-- <input type="checkbox" class="tb_email_comment" name="tb_email_comment" id="tb_email_comment" value=""> Send To Email -->
                          </div>
                          <div class="col-md-7">
                            <!-- <input placeholder="message here.." type="text" class="form-control" name="tb_comment" id="tb_comment" value=""> -->
                            <textarea class="form-control tb_comment" name="tb_comment" id="tb_comment" rows="4" cols="40"></textarea>
                          </div>
                          <div class="col-md-2" style="text-align:right">
                            <a class="btn btn-default btn_send_comment1" href="#"><i class="fa fa-paper-plane"> SEND</i></a>
                          </div>
                      </div>
                  </div>
                  <!-- level 1 -->
                @endforeach
            @endif
          </div>


          <div class="row" style="text-align:left;margin:2px;margin-top:20px">
            <div class="row">
              <div class="col-md-2">
                 <!-- style="display:none" -->
                <input type="checkbox" class="tb_email_comment" name="tb_email_comment" id="tb_email_comment" value=""> Email To User
              </div>
              <div class="col-md-8">
                <!-- <input placeholder="message here.." type="text" class="form-control" name="tb_comment" id="tb_comment" value=""> -->
                <textarea class="form-control tb_comment" name="tb_comment" id="tb_comment" rows="4" cols="40"></textarea>
              </div>
              <div class="col-md-2" style="text-align:right">
                <a class="btn btn-default btn_send_comment"  href="#"><i class="fa fa-paper-plane"> SEND</i></a>
              </div>
            </div>
            <div class="row">
              <div class="col-md-2" style="text-align:right">
                Reply To
              </div>
              <div class="col-md-8">
                <div class="panel panel-default">
                  <div class="panel-body panel_quick_data">
                    <?php $i = 0; ?>
                    @if (isset($send_lists))
                      @foreach($send_lists as $send_list)
                        @if ($i == 0)
                          <input checked type="radio" name="send" id="send" value="{{$send_list['user_id']}}"> {{$send_list['user_name']}}
                        @else
                          <input type="radio" name="send" id="send" value="{{$send_list['user_id']}}"> {{$send_list['user_name']}}
                        @endif
                        <?php $i = $i + 1; ?>
                      @endforeach
                    @endif
                  </div>
                </div>
              </div>
              <div class="col-md-2" style="text-align:right">
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    @endif

    <div class="row">
      <div class="col-md-12" style="font-style:italic;color:gray;font-size:12px">
        <div class="well audit-container" style="text-align:right">
        </div>
      </div>
    </div>

  </div>
</div>

@if (isset($forms_details_flag))
  @include('master_view.master_form_detail')
@endif
@if (isset($subnet_flag))
  @include('app.subnet.subnet_detail')
@endif
@if (isset($forms_quick_details_flag))
  @include('master_view.master_quick_detail')
@endif
@include('module.search-data')

<style media="screen">
.ui-state-highlight { height: 2em; line-height: 2em; border: 2px red solid}

</style>

<script type="text/javascript">



var createComment = function (margin_left,margin_right, id, username, comment, comment_date, comment_count,to_user, email_flag) {
  var obj = ' <div class="row" id="' + id + '">' +
            ' 	  <div class="row"  style="background-color:#C7C7C7;padding:10px;border-radius:5px;margin:5px ' + margin_left + 'px 0px ' + margin_right + 'px">' +
                ' 		<div class="col-md-12">' +
                ' 		  <div class="row">' +
                    ' 			<div class="col-md-2">' +
                    ' 			  <b>' + username + '</b>' +
                    ' 			</div>' +
                    ' 			<div class="col-md-8">' +
                    ' 			  ' + comment +
                    ' 			</div>' +
                    ' 			<div class="col-md-2"  style="text-align:right">' +
                    // ' 			  <a href="#" class="btn btn-default btn_reply"> <i class="fa fa-reply"></i> Reply</a>' +
                    ' 			</div>' +
                ' 		  </div>' +
                ' 		  <div class="row">' +
                    ' 			<div class="col-md-2">' +
                    ' 			</div>' +
                    ' 			<div class="col-md-4">' +
                    ' 			  <span style="font-size:11px"><b><i>' + comment_date + '</i></b></span>' +
                    ' 			</div>' +
                    ' 			<div class="col-md-6" >' +
                    '       <b>Reply To : ' + to_user + '</b>' +
                    // ' 			  <a class="btn_load_comment" href="#">' + comment_count + ' comments</a>' +
                    ' 			</div>' +
                ' 		  </div>' +
                ' ' +
                ' 		</div>' +
            ' ' +
            ' 	  </div>' +
' 	  <div class="row reply-con"  style="display:none;background-color:#DBDBDB;padding:10px;border-radius:5px;margin:0px 5px 5px 150px">' +
            ' 		  <div class="col-md-3">' +
            // ' 			<input type="checkbox" class="tb_email_comment" name="tb_email_comment" id="tb_email_comment" value=""> Send To Email' +
            ' 		  </div>' +
            ' 		  <div class="col-md-7">' +
            ' 			<!-- <input placeholder="message here.." type="text" class="form-control" name="tb_comment" id="tb_comment" value=""> -->' +
            ' 			<textarea class="form-control tb_comment" name="tb_comment" id="tb_comment" rows="4" cols="40"></textarea>' +
            ' 		  </div>' +
            ' 		  <div class="col-md-2" style="text-align:right">' +
            ' 			<a class="btn btn-default btn_send_comment1" href="#"><i class="fa fa-paper-plane"> SEND</i></a>' +
' 		  </div>' +
' 	  </div>' +
'   </div>';

return obj;
}

  @if (isset($comment_flag))
      window.setInterval(function(){
         refreshComment();
      }, 30000);
  @endif


function refreshComment(){
  //  $('.comment-con').html("");
  $.ajax({
      url: "/get-child-comments",
      type: 'POST',
      global: false,
      data: { _token: '{{{ csrf_token() }}}', 'parent_id' : '','transaction_id' : '{{$id or ''}}' },
      dataType: 'json', //json
      success: function( _response ){
          var result = JSON.parse(_response['data']);

          if (result.length > 0 && result[0]['id'] != null) {
            for(var i=0;i< result.length;i++) {
              if ($('.comment-con').has("#" + result[i]['id']).length == 0) {
                var comm = createComment(5,5, result[i]['id'], result[i]['user'], result[i]['comment'], result[i]['comment_date'],
                              result[i]['childCount'],result[i]['to_user'], result[i]['email_flag']);
                  $('.comment-con').append(comm);
              }

            }
          }

      },
      error: function( _response ){
         bootbox.alert(_response.responseText);
      }
  });
}

$("body").on("click",".btn_load_comment",function (e) {
    var parent_id = $(this).parent().parent().parent().parent().parent().attr("id");
    e.preventDefault();
    $.ajax({
        url: "/get-child-comments",
        type: 'POST',
        data: { _token: '{{{ csrf_token() }}}', 'parent_id' : parent_id },
        dataType: 'json', //json
        success: function( _response ){
            var result = JSON.parse(_response['data']);

            if (result.length > 0 && result[0]['id'] != null) {
              for(var i=0;i< result.length;i++) {

                var comm = createComment(20,50, result[i]['id'], result[i]['user'], result[i]['comment'], result[i]['comment_date'],
                              result[i]['childCount'], result[i]['email_flag']);
                console.log(comm);
                $('#' + parent_id).append(comm);
              }
            }

        },
        error: function( _response ){
           bootbox.alert(_response.responseText);
        }
    });
})

$("body").on("click",".btn_reply",function (e) {
    e.preventDefault();
  $(this).parent().parent().parent().parent().parent().children(".reply-con").toggle("fast");
})

$("body").on("click",".btn_send_comment",function (e) {
  var parent_id = "";
  var to_user_id = $("input[type='radio'][name='send']:checked").val();
  var comment = $(this).parent().parent().find(".tb_comment").val();
  var email_flag = $(this).parent().parent().find(".tb_email_comment").prop("checked");
  e.preventDefault();
    $.ajax({
        url: "/set-comment/{{$table}}/{{$id or ''}}",
        type: 'post',
        data: { _token: '{{{ csrf_token() }}}',
                 'to_user_id' : to_user_id,
                 'parent_id' : parent_id,
                 'email_flag' : email_flag,
                 'comment' : comment},
        dataType: 'json',
        success: function( _response ){
          var result = JSON.parse(_response['data']);
          console.log(result);
            var comm = createComment(5,5, result['id'], result['user'], result['comment'], result['comment_date'],
                          result['childCount'],result['to_user'], result['email_flag']);
            console.log(comm);
            $('.comment-con').append(comm);


        },
        error: function( _response ){
            bootbox.alert(_response.responseText);
        }
    });
})

$("body").on("click",".btn_send_comment1",function (e) {
  var parent_id = $(this).parent().parent().parent().attr("id");
  var to_user_id = "{{$handled_by_user_id or ''}}";
  var comment = $(this).parent().parent().find(".tb_comment").val();
  var email_flag = $(this).parent().parent().find(".tb_email_comment").prop("checked");
  e.preventDefault();
    $.ajax({
        url: "/set-comment/{{$table}}/{{$id or ''}}",
        type: 'post',
        data: { _token: '{{{ csrf_token() }}}',
                 'to_user_id' : to_user_id,
                 'parent_id' : parent_id,
                 'email_flag' : email_flag,
                 'comment' : comment},
        dataType: 'json',
        success: function( _response ){
          console.log(_response);
        },
        error: function( _response ){
            bootbox.alert(_response.responseText);
        }
    });
})

$(document).ready(function(){

    $('[data-toggle="tooltip"]').tooltip();

    @if ($cond !== 'insert')
        $.ajax({
            url: "/audit-trail/get-audit" ,
            type: 'POST',
            data: { _token: '{{{ csrf_token() }}}' , transaction_category: '{{$table}}' , transaction_id: '{{$id or ''}}' },
            dataType: 'json', //json
            success: function( _response ){
             $('.audit-container').html("");
             for(var i=0;i<_response.length;i++) {
               var status = "";
               switch (_response[i]['status']) {
                  case 'insert':
                   status = "Inserted";
                   break;
                  case 'update':
                   status = "Edited";
                   break;
                  case 'delete':
                   status = "Deleted";
                   break;
                 default:
               }
               if (i == 0) {
                 $('.audit-container').append("<div class='row-space bevel'><b>" + status +  " By " + _response[i]['user_name'] + " on " + _response[i]['created_at'] + "</b></div>");
               } else {
                 $('.audit-container').append("<div class='row-space bevel'>" + status + " By " + _response[i]['user_name'] + " on " + _response[i]['created_at'] + "</div>");
               }

             }

            },
            error: function( _response ){
               bootbox.alert(_response.responseText);
            }
        });
    @endif

});
$("#table_detail tbody").sortable({
  placeholder: "ui-state-highlight",
  out: function(e,ui){
        var i = 1;
        $('#table_detail tbody tr').each(function () {
          $(this).find('.td_').html(i);
          i += 1;
        })
    }
});

@if ($cond !== 'insert')
  $("#btn_audit").on("click", function () {
    var left  = ($(window).width()/2)-(850/2),
        top   = ($(window).height()/2)-(300/2)
    var url = $(this).attr('href');
    var windowName = $(this).attr('id');
    window.open("/audit-trail/history/{{$table}}/{{$id or ''}}", "Audit History", "height=300,width=850, top="+top+", left = "+left);
  });
@endif


@if (isset($forms_quick_details_flag))
    //--------------------------------DETAIL--------------------------
   var table_details = <?php echo json_encode($quick_details); ?>;

    $("#table_detail tbody").on('click','tr', function() {
      $("#table_detail tr").removeClass("selected");
      $(this).addClass("selected");
    });

    $("#btn_detail_insert").on("click", function () {
      var cond = "";
      $('#table_detail > tbody > tr:visible').each(function() {
          //  console.log($(this).attr('filing_detail_id'));
          if (cond == "") {
            cond = cond + " tr_filing_detail.id <> '" + $(this).attr('filing_detail_id') + "'";
          } else {
            cond = cond + " and tr_filing_detail.id <> '" + $(this).attr('filing_detail_id') + "'";
          }
      })
      $("#detail-quick-form").data('cond', cond );
      $("#detail-quick-form").data('id', id );
      $("#detail-quick-form").data('row', "" );
      $("#detail-quick-form").data('table', "{{$detail_table or 'filing-detail'}}" );
      $('#detail-quick-form').modal('show');
    });

    $("#btn_detail_delete").on("click", function () {
      var row = $("#table_detail tbody tr.selected");
      if ($("#table_detail tbody tr").hasClass("selected")) {
        bootbox.confirm("Delete Record " + row.find(".td_no").html() + ", Are you sure?", function(result) {
          if (result) {
                row.attr("deleteflag","true");
                row.hide();

          }
         });
      } else {
        bootbox.alert("No records is selected");
      }
    });

    var getTableData = function(TableId) {
        var results = [];
        $("#table_detail > tbody > tr").each(function() {
          $this = $(this)
          var result = {};
          result['id'] = $this.attr("id");
          if(result['id'] == null) {
            result['id']= "";
          }
          result['deleteflag'] = $this.attr("deleteflag");
          @foreach ($forms_quick_details as $detail)
            @if ($detail['show_on_table'] == true)
              @if ($detail['type'] == "fix")
                result['{{$detail['column_name']}}'] = $this.find('.td_{{$detail['column_name']}}').html();
              @elseif ($detail['type'] == "sequence-no")
                  result['sequence_no'] = $this.find('.td_{{$detail['column_name']}}').html();
              @elseif ($detail['type'] == "select")
                result['{{$detail['column_name']}}'] = $this.find('.td_{{$detail['column_name']}} select option:selected').text();
              @elseif ($detail['type'] == "text")
                result['{{$detail['column_name']}}'] = $this.find('input[name="{{$detail['column_name']}}"]').val();
              @elseif ($detail['type'] == "price_formula")
                    if (result['deleteflag'] == "true") {
                        result['{{$detail['column_name']}}'] = 0;
                    } else {
                        result['{{$detail['column_name']}}'] = $("#form_input").calx('getCell', $this.find('.td_{{$detail['column_name']}} > .pr_{{$detail['column_name']}}').attr("data-cell")).getValue();
                    }

              @elseif ($detail['type'] == "number_formula")
                if (result['deleteflag'] == "true") {
                  result['{{$detail['column_name']}}'] = 0
                } else {
                  result['{{$detail['column_name']}}'] = $("#form_input").calx('getCell', $this.find('.td_{{$detail['column_name']}} > .pr_{{$detail['column_name']}}').attr("data-cell")).getValue();
                }

              @endif
            @else //hidden column
              @if ($detail['type'] == "hidden")
                  result['{{$detail['column_name']}}'] = $this.attr('{{$detail['column_name']}}');
              @endif
            @endif
          @endforeach

          results.push(result);
        });
        //console.log(results);
        return results;
    }

    var loadTableData = function(table_details) {

       for(var i=0;i<table_details.length;i++) {
         var $row = $('<tr>');
         $row.attr('id',table_details[i]['id']);
         $row.attr('deleteflag','false');
         @foreach ($forms_quick_details as $detail)
           @if ($detail['show_on_table'] == true)
             @if ($detail['type'] == "text")
               $row.append("<td class='td_{{$detail['column_name']}}'><input class='form-control' type='text' name='{{$detail['column_name']}}' value='"+ table_details[i]['{{$detail['column_name']}}'] +"'></td>");
             @elseif ($detail['type'] == "sequence-no")
                 $row.append("<td class='td_{{$detail['column_name']}}'>" + table_details[i]['sequence_no'] + "</td>");
             @elseif ($detail['type'] == "select")
               var row = $("<td class='td_{{$detail['column_name']}}'></td>");
               var select = $('<select class="form-control" name=""></select>');
               <?php $options = explode(';',$detail['options']); ?>
               @foreach ($options as $opsi)
                if ("{{$opsi}}" == table_details[i]['{{$detail['column_name']}}']) {
                  select.append('<option selected value="{{$opsi}}">{{$opsi}}</option>');
                } else {
                  select.append('<option value="{{$opsi}}">{{$opsi}}</option>');
                }

               @endforeach
               row.append(select);
               $row.append(row);
            @elseif ($detail['type'] == "fix")
               $row.append("<td class='td_{{$detail['column_name']}}'>" + table_details[i]['{{$detail['column_name']}}'] + "</td>");
               @elseif ($detail['type'] == "price_formula")
            	var formula1 = "{{$detail['formula'] or ''}}";
            	formula1 = formula1.replace(/#/g,i+1);
            	var cell1 = "{{$detail['cell'] or ''}}";
            	cell1 = cell1 + (i+1);
            	$row.append("<td class='td_{{$detail['column_name']}}'>" +
            	"<input data-cell='" + cell1 + "' data-formula='" + formula1 + "' class='pr_{{$detail['column_name']}} {{$detail['readonly'] or ''}}'  {{$detail['readonly'] or ''}} style='max-width:100px' formula='{{$detail['formula'] or ''}}' data-format='{{$detail['format'] or ''}}' cell='{{$detail['cell']}}' type='text' name='pr_{{$detail['column_name']}}'  value='" + table_details[i]['{{$detail['field_name'] or $detail['column_name'] }}'] + "'>" +
            	"</td>");
            @elseif ($detail['type'] == "number_formula")
            	var formula1 = "{{$detail['formula'] or ''}}";
            	formula1 = formula1.replace(/#/g,i+1);
            	var cell1 = "{{$detail['cell'] or ''}}";
            	cell1 = cell1 + (i+1);
            	$row.append("<td class='td_{{$detail['column_name']}}'>" +
            	"<input data-cell='" + cell1 + "' data-formula='" + formula1 + "' class='pr_{{$detail['column_name']}} {{$detail['readonly'] or ''}}'  {{$detail['readonly'] or ''}} style='max-width:100px' formula='{{$detail['formula'] or ''}}' data-format='{{$detail['format'] or ''}}' cell='{{$detail['cell']}}' type='text' name='pr_{{$detail['column_name']}}'  value='" + table_details[i]['{{$detail['field_name'] or $detail['column_name']}}'] + "'>" +
            	"</td>");

             @endif
           @else //hidden column
             @if ($detail['type'] == "hidden")
                 $row.attr("{{$detail['column_name']}}", table_details[i]['{{$detail['column_name']}}'] );
             @endif
           @endif
         @endforeach

         $('#table_detail tbody').append($row);
       }

    }

//--------------------------------QUICK DETAIL--------------------------
@endif

 @if (isset($forms_details_flag))
     //--------------------------------DETAIL--------------------------
     var table_details = <?php echo json_encode($details); ?>;
     var table_details2 = <?php echo json_encode($details2); ?>;


     $("#table_detail tbody").on('click','tr', function() {
       $("#table_detail tr").removeClass("selected");
       $(this).addClass("selected");
     });

     $("#btn_detail_insert").on("click", function () {
         $("#detail-form").data('cond', "insert" );
         $("#detail-form").data('position', "insert" );
         $("#detail-form").data('row', "" );
         $('#detail-form').modal('show');
     });

     $("#btn_detail_update").on("click", function () {
         var row = $("#table_detail tbody tr.selected");
         if ($("#table_detail tbody tr").hasClass("selected")) {
             var row = $('tr.selected');
             var data = {};
             @foreach ($forms_details as $detail)
               @if ($detail['show_on_table'] == true)
                 @if ($detail['type'] == "text")
                   data['{{$detail['column_name']}}'] = row.find('.td_{{$detail['column_name']}}').html();
                 @elseif ($detail['type'] == "data");
                   data['{{$detail['column_id']}}'] = row.find('.td_{{$detail['column_name']}}').attr('{{$detail['column_id']}}');
                   data['{{$detail['column_name']}}'] = row.find('.td_{{$detail['column_name']}}').html();
                 @elseif ($detail['type'] == "data-auto");
                   data['{{$detail['column_id']}}'] = row.find('.td_{{$detail['column_name']}}').attr('{{$detail['column_id']}}');
                   data['{{$detail['column_name']}}'] = row.find('.td_{{$detail['column_name']}}').html();
                 @elseif ($detail['type'] == "select")
                   data['{{$detail['column_name']}}'] = row.find('.td_{{$detail['column_name']}}').html();
                 @elseif ($detail['type'] == "price")
                   data['{{$detail['column_name']}}'] = row.find('.td_{{$detail['column_name']}}').autoNumeric('get');
                 @elseif ($detail['type'] == "price_formula")
                   data['{{$detail['column_name']}}'] = $("#form_input").calx('getCell', row.find('.td_{{$detail['column_name']}} > .pr_{{$detail['column_name']}}').attr("data-cell")).getValue();
                //   data['{{$detail['column_name']}}'] = row.find('.td_{{$detail['column_name']}} > .pr_{{$detail['column_name']}}').val();
                 @elseif ($detail['type'] == "number_formula")
                   data['{{$detail['column_name']}}'] = $("#form_input").calx('getCell', row.find('.td_{{$detail['column_name']}} > .pr_{{$detail['column_name']}}').attr("data-cell")).getValue();
                  // data['{{$detail['column_name']}}'] = row.find('.td_{{$detail['column_name']}} > .pr_{{$detail['column_name']}}').val();
                 @endif
               @else //hidden column
                 @if ($detail['type'] == "table")
                     data['{{$detail['column_name']}}'] = row.attr('{{$detail['column_name']}}');
                 @elseif ($detail['type'] == "text")
                     data['{{$detail['column_name']}}'] = row.attr('{{$detail['column_name']}}');
                 @elseif ($detail['type'] == "select")
                   data['{{$detail['column_name']}}'] = row.attr('{{$detail['column_name']}}');
                 @elseif ($detail['type'] == "data-auto")
                     data['{{$detail['column_name']}}'] = row.attr('{{$detail['column_name']}}');
                 @elseif ($detail['type'] == "price")
                     data['{{$detail['column_name']}}'] = row.find('.td_{{$detail['column_name']}}').autoNumeric('get');
                 @elseif ($detail['type'] == "price_formula")
                     data['{{$detail['column_name']}}'] = row.find('.td_{{$detail['column_name']}} > .pr_{{$detail['column_name']}}').val();
                 @elseif ($detail['type'] == "number_formula")
                     data['{{$detail['column_name']}}'] = row.find('.td_{{$detail['column_name']}} > .pr_{{$detail['column_name']}}').val();
                 @endif
               @endif
             @endforeach

             $("#detail-form").data('cond', "update" );
             $("#detail-form").data('row', data );
             $('#detail-form').modal('show');
         } else {
           bootbox.alert("No records is selected");
         }
     });

     $("#table_detail tbody").on('dblclick','tr', function() {
       $("#btn_detail_update").click();
     });

     $("#btn_detail_delete").on("click", function () {
         var row = $("#table_detail tbody tr.selected");
         if ($("#table_detail tbody tr").hasClass("selected")) {
           bootbox.confirm("Delete Record " + row.find(".td_no").html() + ", Are you sure?", function(result) {
             if (result) {
                   row.attr("deleteflag","true");
                   row.find(':input').removeAttr('data-cell');
                   row.find(':input').removeAttr('data-formula');
                   row.find(':input').removeAttr('data-format');
                   row.hide();

                   $('#form_input').calx('update');
                      $('#form_input').calx('refresh')
                     $('#form_input').calx('calculate');
             }
            });
         } else {
           bootbox.alert("No records is selected");
         }
     });

     var getTableData = function(TableId) {
         var results = [];
         $("#table_detail > tbody > tr").each(function() {
           $this = $(this)
           var result = {};
           result['id'] = $this.attr("id");
           if(result['id'] == null) {
             result['id']= "";
           }
           result['deleteflag'] = $this.attr("deleteflag");
           @foreach ($forms_details as $detail)
             @if ($detail['show_on_table'] == true)
               @if ($detail['type'] == "text")
                 result['{{$detail['column_name']}}'] = $this.find('.td_{{$detail['column_name']}}').html();
               @elseif ($detail['type'] == "sequence-no")
                   result['sequence_no'] = $this.find('.td_{{$detail['column_name']}}').html();
               @elseif ($detail['type'] == "data")
                   result['{{$detail['column_id']}}'] = $this.find('.td_{{$detail['column_name']}}').attr('{{$detail['column_id']}}');
               @elseif ($detail['type'] == "data-auto")
                   result['{{$detail['column_id']}}'] = $this.find('.td_{{$detail['column_name']}}').attr('{{$detail['column_name']}}');
               @elseif ($detail['type'] == "select")
                 result['{{$detail['column_name']}}'] = $this.find('.td_{{$detail['column_name']}}').html();
               @elseif ($detail['type'] == "price")
                  result['{{$detail['column_name']}}'] = $this.find('.td_{{$detail['column_name']}}').autoNumeric('get');
              @elseif ($detail['type'] == "price_formula")
                if (result['deleteflag'] == "true") {
                  result['{{$detail['column_name']}}'] = 0;
                } else {
                  result['{{$detail['column_name']}}'] = $("#form_input").calx('getCell', $this.find('.td_{{$detail['column_name']}} > .pr_{{$detail['column_name']}}').attr("data-cell")).getValue();
                }

              @elseif ($detail['type'] == "number_formula")
                  if (result['deleteflag'] == "true") {
                    result['{{$detail['column_name']}}'] = 0;
                  } else {
                    result['{{$detail['column_name']}}'] = $("#form_input").calx('getCell', $this.find('.td_{{$detail['column_name']}} > .pr_{{$detail['column_name']}}').attr("data-cell")).getValue();
                  }

               @endif
             @else //hidden column
               @if ($detail['type'] == "table")
                   result['{{$detail['column_name']}}'] = JSON.parse($this.attr('{{$detail['column_name']}}'));
               @elseif ($detail['type'] == "text")
                   result['{{$detail['column_name']}}'] = $this.attr('{{$detail['column_name']}}');
               @elseif ($detail['type'] == "data-auto")
                   result['{{$detail['column_name']}}'] = $this.attr('{{$detail['column_name']}}');
               @elseif ($detail['type'] == "select")
                   result['{{$detail['column_name']}}'] = $this.attr('{{$detail['column_name']}}');
               @elseif ($detail['type'] == "price")
                  result['{{$detail['column_name']}}'] = $this.find('.td_{{$detail['column_name']}}').autoNumeric('get');
               @endif
             @endif
           @endforeach

           results.push(result);
         });
         //console.log(results);
         return results;
     }

     var loadTableData = function(table_details, table_details2) {
        for(var i=0;i<table_details.length;i++) {
          var $row = $('<tr>');
          $row.attr('id',table_details[i]['id']);
          $row.attr('deleteflag','false');
          @foreach ($forms_details as $detail)
            @if ($detail['show_on_table'] == true)
              @if ($detail['type'] == "text")
                $row.append("<td class='td_{{$detail['column_name']}}'>" + table_details[i]['{{$detail['column_name']}}'] + "</td>");
              @elseif ($detail['type'] == "sequence-no")
                  $row.append("<td class='td_{{$detail['column_name']}}'>" + table_details[i]['sequence_no'] + "</td>");
              @elseif ($detail['type'] == "sequence-no-static")
                  $row.append("<td class='td_{{$detail['column_name']}}'>" + (i+1) + "</td>");
              @elseif ($detail['type'] == "data")
                  $row.append("<td {{$detail['column_id']}}='" + table_details[i]['{{$detail['column_id']}}'] + "' class='td_{{$detail['column_name']}}'>" + table_details[i]['{{$detail['field_name'] or $detail['column_name']}}'] + "</td>");
              @elseif ($detail['type'] == "select")
                $row.append("<td class='td_{{$detail['column_name']}}'>" + table_details[i]['{{$detail['column_name']}}'] + "</td>");
              @elseif ($detail['type'] == "sequence-no")
                $row.append("<td class='td_{{$detail['column_name']}}'>1</td>");
              @elseif ($detail['type'] == "price")
                $row.append("<td class='td_{{$detail['column_name']}}'>" + table_details[i]['{{$detail['column_name']}}'] + "</td>");
              @elseif ($detail['type'] == "price_formula")
                var formula1 = "{{$detail['formula'] or ''}}";
                formula1 = formula1.replace(/#/g,i+1);
                var cell1 = "{{$detail['cell'] or ''}}";
                cell1 = cell1 + (i+1);
                $row.append("<td class='td_{{$detail['column_name']}}'>" +
                "<input data-cell='" + cell1 + "' data-formula='" + formula1 + "' class='pr_{{$detail['column_name']}}'  {{$detail['readonly'] or ''}} style='max-width:100px' formula='{{$detail['formula'] or ''}}' data-format='{{$detail['format'] or ''}}' cell='{{$detail['cell']}}' type='text' name='pr_{{$detail['column_name']}}'  value='" + table_details[i]['{{$detail['field_name'] or $detail['column_name'] }}'] + "'>" +
                "</td>");
              @elseif ($detail['type'] == "number_formula")
                var formula1 = "{{$detail['formula'] or ''}}";
                formula1 = formula1.replace(/#/g,i+1);
                var cell1 = "{{$detail['cell'] or ''}}";
                cell1 = cell1 + (i+1);
                $row.append("<td class='td_{{$detail['column_name']}}'>" +
                "<input data-cell='" + cell1 + "' data-formula='" + formula1 + "' class='pr_{{$detail['column_name']}}'  {{$detail['readonly'] or ''}} style='max-width:100px' formula='{{$detail['formula'] or ''}}' data-format='{{$detail['format'] or ''}}' cell='{{$detail['cell']}}' type='text' name='pr_{{$detail['column_name']}}'  value='" + table_details[i]['{{$detail['field_name'] or $detail['column_name']}}'] + "'>" +
                "</td>");
              @endif
            @else //hidden column
              @if ($detail['type'] == "table")
                  var arr = jQuery.grep(table_details2, function( a ) {
                          return a['parent_id'] == table_details[i]['{{$detail['parent_column']}}'];
                          });
                  $row.attr("{{$detail['column_name']}}", JSON.stringify(arr) );
              @elseif ($detail['type'] == "text")
                  $row.attr("{{$detail['column_name']}}", table_details[i]['{{$detail['column_name']}}'] );
              @elseif ($detail['type'] == "data-auto")
                  $row.attr("{{$detail['column_name']}}", table_details[i]['{{$detail['column_name']}}'] );
              @elseif ($detail['type'] == "select")
                  $row.attr("{{$detail['column_name']}}", table_details[i]['{{$detail['column_name']}}'] );
              @elseif ($detail['type'] == "price")
                $row.append("<td class='td_{{$detail['column_name']}}'>" + table_details[i]['{{$detail['column_name']}}'] + "</td>");
              @endif
            @endif
          @endforeach

          $('#table_detail tbody').append($row);
        }

        @foreach ($forms_details as $detail)
          @if ($detail['type'] == "price")
            $(".td_{{$detail['column_name']}}").autoNumeric("init",{
                aSep: '.',
                aDec: ',',
                aSign: 'Rp ',
                vMin: '0.00',
                vMax: '1000000000.00'
            });
          @endif
        @endforeach

     }

 //--------------------------------DETAIL--------------------------
 @endif

 function getDate(date1) {
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


  $(document).on("click", ".btn_search", function () {
     var table = $(this).attr('id');
     var count = "";
     if (table.split("-").length > 1) {
       count = "-" + table.split("-")[1];
     }
     var cond = "";
     cond = $(this).data('cond');
     var colid = $(this).data('colid');
     var colname = $(this).data('colname');
     var second_column = $(this).data('secondcolumn');
     var customselectdata = $(this).data('customselectdata');
     var customselectdata1 = $(this).data('customselectdata1');
     var headerdependency = $(this).data('headerdependency');
     var dependency = $(this).data('dependency') + "_id";
     var dependency_value = $("#" + dependency + count).val();
     var child = "#" + $(this).data('child');
     if (dependency !== "" && dependency_value == "") {
       bootbox.alert("Column " + $(this).data('dependency') + " must selected before")
     } else {
       $("#search-form").data('outputname', "#" + table );
       $("#search-form").data('colid', "#" + colid );
       $("#search-form").data('colname', "#" + colname );
       $("#search-form").data('outputnameclass', "." + table );
       $("#search-form").data('table', table.split("-")[0] );
       $("#search-form").data('dependency', dependency );
       $("#search-form").data('dependencyValue', dependency_value );
       $("#search-form").data('child', child );
       $("#search-form").data('headerdependency', headerdependency );
       $("#search-form").data('headerdependencyid', $("#" + headerdependency).val());
       $("#search-form").data('customselectdata', customselectdata);
       $("#search-form").data('customselectdata1', customselectdata1);
       $("#search-form").data('cond', cond);
       $("#search-form").data('secondcolumn', second_column);

       $('.table-search tbody tr').remove();
       $('#search-form').modal('show');
     }

  });


  var cond = "{{ $cond or 'insert'}}";
  var id = "{{ $id or ''}}";


  $(document).ready(function(){
    @if (isset($forms_details_flag))
          loadTableData(table_details,table_details2);
    @endif

    @if (isset($forms_quick_details_flag))
          loadTableData(table_details);
    @endif

    @if (isset($formula_flag))
        $("#form_input").calx();
    @endif


    //----------approval
    $('.approved_by').each(function () {
        var number = $(this).attr('id').split('-')[1];
        if ($(this).val() == "User") {
          $('#position-' + number).prop('disabled', 'disabled');
          $('#position-' + number).val('');
          $('#position-' + number).hide();
          $('#branch-' + number).show();
          $('#division-' + number).show();
          $('#user-' + number).show();
          $('.btn_branch-' + number).show();
          $('.btn_division-' + number).show();
          $('.btn_user-' + number).show();
        } else if ($(this).val() == "Position") {
          $('#position-' + number).prop('disabled', false);
          $('#position-' + number).show();
          $('#branch-' + number).hide();
          $('#division-' + number).hide();
          $('#user-' + number).hide();
          $('.btn_branch-' + number).hide();
          $('.btn_division-' + number).hide();
          $('.btn_user-' + number).hide();
        } else {
          $('#position-' + number).prop('disabled', false);
          $('#position-' + number).show();
          $('#branch-' + number).show();
          $('#division-' + number).show();
          $('#user-' + number).hide();
          $('.btn_branch-' + number).show();
          $('.btn_division-' + number).show();
          $('.btn_user-' + number).hide();
        }
    });

    $('.approval_list_container').on('change','.approved_by',function(){
      var number = $(this).attr('id').split('-')[1];
      if ($(this).val() == "User") {
        $('#position-' + number).prop('disabled', 'disabled');
        $('#position-' + number).val('');
        $('#position-' + number).hide();
        $('#branch-' + number).show();
        $('#division-' + number).show();
        $('#user-' + number).show();
        $('.btn_branch-' + number).show();
        $('.btn_division-' + number).show();
        $('.btn_user-' + number).show();
      } else if ($(this).val() == "Position") {
        $('#position-' + number).prop('disabled', false);
        $('#position-' + number).show();
        $('#branch-' + number).hide();
        $('#division-' + number).hide();
        $('#user-' + number).hide();
        $('.btn_branch-' + number).hide();
        $('.btn_division-' + number).hide();
        $('.btn_user-' + number).hide();
      } else {
        $('#position-' + number).prop('disabled', false);
        $('#position-' + number).show();
        $('#branch-' + number).show();
        $('#division-' + number).show();
        $('#user-' + number).hide();
        $('.btn_branch-' + number).show();
        $('.btn_division-' + number).show();
        $('.btn_user-' + number).hide();
      }
    })
    //----------approval


    $('form').submit(function( event ) {
      event.preventDefault();
      $('button[type="submit"]').prop('disabled', true);
      @if (isset($title))
          @if ($title == 'Approval Level')
              var i = 1;
              var approval_array = [];
              $('.approval_list_container').each(function () {
                var approval_detail_id = $('.approval_list_container').find('#approval_detail_id-' + i).val();
                var position = $('.approval_list_container').find('#position-' + i).val();
                var approved_by = $('.approval_list_container').find('#approved_by-' + i).val();
                var branch_id = $('.approval_list_container').find('#branch_id-' + i).val();
                var division_id = $('.approval_list_container').find('#division_id-' + i).val();
                var user_id = $('.approval_list_container').find('#user_id-' + i).val();
                approval_array.push({ 'id' : approval_detail_id,
                                      'position' : position,
                                      'approved_by' : approved_by,
                                      'branch_id' : branch_id,
                                      'division_id' : division_id,
                                      'user_id' : user_id,
                                      'level' : i});

                i += 1;
              });
            $('#approval_detail').val(JSON.stringify(approval_array));
          @endif
      @endif

      @if (isset($forms_details_flag)) //jika ada detail
        $("#data_detail").val(JSON.stringify(getTableData("#table_detail")));

      @endif

      @if (isset($forms_quick_details_flag)) //jika ada detail
        $("#data_detail").val(JSON.stringify(getTableData("#table_detail")));
      @endif

      try {
          $('.dtp').each(function() {
              $('#' + $(this).attr("id") + '_formatted').val(getDate($(this).datepicker('getDate')));
          })
      }
      catch(err) {

      }

      try {
          $('.quick_search_item').each(function() {
            if($(this).is(':visible'))
              {
                  //visible element
                  $('#' + $(this).attr("var")).val( $('#' + $(this).attr("var")).val() +  $(this).attr("id") + ";");
              }
          })

      }
      catch(err) {

      }



      try {
        $('.multi_check_item').each(function() {
          if($(this).is(':visible'))
            {
              if($(this).is(':checked')) {
                //visible element
                $('#' + $(this).attr("var")).val( $('#' + $(this).attr("var")).val() +  $(this).attr("id") + ";");
              }
            }
        })

      }
      catch(err) {

      }

      try {
        $('.price_column').each(function() {
          $('#price_' + $(this).attr('id')).val($(this).autoNumeric('get'));
        })

      }
      catch(err) {

      }

      try {
        $('.grid').val(JSON.stringify(hot.getData()));
        console.log($('.grid').val());
      } catch (e) {

      } finally {

      }


      try {
        $('.price_formula_column').each(function() {
          var result = $("#form_input").calx('getCell', $(this).attr('data-cell')).getValue();
          $('#price_' + $(this).attr('id')).val(result);
        })

      }
      catch(err) {

      }


      try {
        $('.random_if_empty').each(function() {
           if ($(this).val() == "") {
             var text = "";
              var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";

              for( var i=0; i < 15; i++ )
                  text += possible.charAt(Math.floor(Math.random() * possible.length));
             $(this).val(text);
           }
        })

      }
      catch(err) {

      }



      if (cond === 'insert') {
        event.preventDefault();
        @if (isset($prefix_category))
          @if ($prefix_category =='handover')
            //  $('#handover_date_formatted').val($("#datetimepicker1").data("DateTimePicker").date().format("Y-MM-DD"));
              $.ajax({
                  url: "/handover/generate-prefix",
                  type: 'get',
                  data: {"handover_date" : $('#handover_date_formatted').val(), "branch_id" : $('#branch_id').val()},
                  dataType: 'html',
                  success: function( _response ){
                    $("#handover_no").val(_response);
                    $.ajax({
                        url: "/{{$table}}",
                        type: 'post',
                        data: $('form').serialize(),
                        dataType: 'json',
                        success: function( _response ){
                          if( _response.status == "OK") {
                            bootbox.alert("Insert Success");
                            setTimeout(function(){
                              window.location.href = _response.url;
                            }, 2000);
                          } else {
                              $('button[type="submit"]').prop('disabled', false);
                              msg = '';
                              for (i=0;i<_response.msg.length;i++) {
                                msg = msg + _response.msg[i] + '<br />';
                              }
                              bootbox.aler(msg);
                          }
                        },
                        error: function( _response ){
                            $('button[type="submit"]').prop('disabled', false);
                            bootbox.alert(_response.responseText);
                        }
                    });
                  },
                  error: function( _response ){
                      bootbox.alert(_response.responseText);
                  }
              });
          @elseif ($prefix_category =='invoice')
            //  $('#handover_date_formatted').val($("#datetimepicker1").data("DateTimePicker").date().format("Y-MM-DD"));
              $.ajax({
                  url: "/invoice/generate-prefix",
                  type: 'get',
                  data: {"invoice_date" : $('#invoice_date_formatted').val(), "branch_id" : $('#branch_id').val()},
                  dataType: 'html',
                  success: function( _response ){
                    $("#invoice_no").val(_response);
                    $.ajax({
                        url: "/{{$table}}",
                        type: 'post',
                        data: $('form').serialize(),
                        dataType: 'json',
                        success: function( _response ){
                          if( _response.status == "OK") {
                            bootbox.alert("Insert Success");
                            setTimeout(function(){
                              window.location.href = _response.url;
                            }, 2000);
                          } else {
                              $('button[type="submit"]').prop('disabled', false);
                              msg = '';
                              for (i=0;i<_response.msg.length;i++) {
                                msg = msg + _response.msg[i] + '<br />';
                              }
                              bootbox.aler(msg);
                          }
                        },
                        error: function( _response ){
                            $('button[type="submit"]').prop('disabled', false);
                            bootbox.alert(_response.responseText);
                        }
                    });
                  },
                  error: function( _response ){
                      bootbox.alert(_response.responseText);
                  }
              });
          @elseif ($prefix_category =='item-filing')
            //  $('#handover_date_formatted').val($("#datetimepicker1").data("DateTimePicker").date().format("Y-MM-DD"));
              $.ajax({
                  url: "/item-filing/generate-prefix",
                  type: 'get',
                  data: {"item_filing_date" : '', "branch_id" : ''},
                  dataType: 'html',
                  success: function( _response ){
                    $("#item_filing_no").val(_response);
                    $.ajax({
                        url: "/{{$table}}",
                        type: 'post',
                        data: $('form').serialize(),
                        dataType: 'json',
                        success: function( _response ){
                          if( _response.status == "OK") {
                            bootbox.alert("Insert Success");
                            setTimeout(function(){
                              window.location.href = _response.url;
                            }, 2000);
                          } else {
                              $('button[type="submit"]').prop('disabled', false);
                              msg = '';
                              for (i=0;i<_response.msg.length;i++) {
                                msg = msg + _response.msg[i] + '<br />';
                              }
                              bootbox.aler(msg);
                          }
                        },
                        error: function( _response ){
                            $('button[type="submit"]').prop('disabled', false);
                            bootbox.alert(_response.responseText);
                        }
                    });
                  },
                  error: function( _response ){
                      bootbox.alert(_response.responseText);
                  }
              });
          @elseif ($prefix_category =='filing-fund')
            //  $('#handover_date_formatted').val($("#datetimepicker1").data("DateTimePicker").date().format("Y-MM-DD"));
              $.ajax({
                  url: "/filing-fund/generate-prefix",
                  type: 'get',
                  data: {"filing_fund_date" : $('#filing_fund_date_formatted').val(), "branch_id" : $('#branch_id').val()},
                  dataType: 'html',
                  success: function( _response ){
                    $("#filing_fund_no").val(_response);
                    $.ajax({
                        url: "/{{$table}}",
                        type: 'post',
                        data: $('form').serialize(),
                        dataType: 'json',
                        success: function( _response ){
                          if( _response.status == "OK") {
                            bootbox.alert("Insert Success");
                            setTimeout(function(){
                              window.location.href = _response.url;
                            }, 2000);
                          } else {
                              $('button[type="submit"]').prop('disabled', false);
                              msg = '';
                              for (i=0;i<_response.msg.length;i++) {
                                msg = msg + _response.msg[i] + '<br />';
                              }
                              bootbox.aler(msg);
                          }
                        },
                        error: function( _response ){
                            $('button[type="submit"]').prop('disabled', false);
                            bootbox.alert(_response.responseText);
                        }
                    });
                  },
                  error: function( _response ){
                      bootbox.alert(_response.responseText);
                  }
              });
          @elseif ($prefix_category =='logbook')
                //  $('#handover_date_formatted').val($("#datetimepicker1").data("DateTimePicker").date().format("Y-MM-DD"));
                  $.ajax({
                      url: "/logbook/generate-prefix",
                      type: 'get',
                      data: {"log_date" : $('#log_date_formatted').val(), "branch_id" : $('#branch_id').val()},
                      dataType: 'html',
                      success: function( _response ){
                        $("#logbook_no").val(_response);
                        $.ajax({
                            url: "/{{$table}}",
                            type: 'post',
                            data: $('form').serialize(),
                            dataType: 'json',
                            success: function( _response ){
                              if( _response.status == "OK") {
                                bootbox.alert("Insert Success");
                                setTimeout(function(){
                                  window.location.href = _response.url;
                                }, 2000);
                              } else {
                                  $('button[type="submit"]').prop('disabled', false);
                                  msg = '';
                                  for (i=0;i<_response.msg.length;i++) {
                                    msg = msg + _response.msg[i] + '<br />';
                                  }
                                  bootbox.aler(msg);
                              }
                            },
                            error: function( _response ){
                                $('button[type="submit"]').prop('disabled', false);
                                bootbox.alert(_response.responseText);
                            }
                        });
                      },
                      error: function( _response ){
                          $('button[type="submit"]').prop('disabled', false);
                          bootbox.alert(_response.responseText);
                      }
                  });
            @elseif ($prefix_category =='maintenance')
                  //  $('#handover_date_formatted').val($("#datetimepicker1").data("DateTimePicker").date().format("Y-MM-DD"));
                    $.ajax({
                        url: "/maintenance/generate-prefix",
                        type: 'get',
                        data: {"maintenance_date" : $('#maintenance_date_formatted').val(), "branch_id" : $('#branch_id').val()},
                        dataType: 'html',
                        success: function( _response ){
                          $("#maintenance_no").val(_response);
                          $.ajax({
                              url: "/{{$table}}",
                              type: 'post',
                              data: $('form').serialize(),
                              dataType: 'json',
                              success: function( _response ){
                                if( _response.status == "OK") {
                                  bootbox.alert("Insert Success");
                                  setTimeout(function(){
                                    window.location.href = _response.url;
                                  }, 2000);
                                } else {
                                    $('button[type="submit"]').prop('disabled', false);
                                    msg = '';
                                    for (i=0;i<_response.msg.length;i++) {
                                      msg = msg + _response.msg[i] + '<br />';
                                    }
                                    bootbox.aler(msg);
                                }
                              },
                              error: function( _response ){
                                  $('button[type="submit"]').prop('disabled', false);
                                  bootbox.alert(_response.responseText);
                              }
                          });
                        },
                        error: function( _response ){
                            $('button[type="submit"]').prop('disabled', false);
                            bootbox.alert(_response.responseText);
                        }
                    });
          @endif
        @else
          @if ($table == 'file')
              var formData = new FormData($(this)[0]);
              $.ajax({
                  url: "/{{$table}}",
                  type: 'post',
                  data: formData,
                  dataType: 'json',
                  cache: false,
                  contentType: false,
                  processData: false,
                  success: function( _response ){
                    if( _response.status == "OK") {
                      bootbox.alert("Insert Success");
                      setTimeout(function(){
                        window.location.href = _response.url;
                      }, 2000);
                    } else {
                        $('button[type="submit"]').prop('disabled', false);
                        msg = '';
                        for (i=0;i<_response.msg.length;i++) {
                          msg = msg + _response.msg[i] + '<br />';
                        }
                        bootbox.aler(msg);
                    }

                  },
                  error: function( _response ){
                      $('button[type="submit"]').prop('disabled', false);
                      bootbox.alert(_response.responseText);
                  }
              });
          @else
              $.ajax({
                  url: "/{{$table}}",
                  type: 'post',
                  data: $('form').serialize(),
                  dataType: 'json',
                  success: function( _response ){
                    if( _response.status == "OK") {
                      bootbox.alert("Insert Success");
                      setTimeout(function(){
                        window.location.href = _response.url;
                      }, 2000);
                    } else {
                        $('button[type="submit"]').prop('disabled', false);
                        msg = '';
                        for (i=0;i<_response.msg.length;i++) {
                          msg = msg + _response.msg[i] + '<br />';
                        }
                        bootbox.aler(msg);
                      //  console.log(_response);
                    }

                  },
                  error: function( _response ){
                      $('button[type="submit"]').prop('disabled', false);
                      bootbox.alert(_response.responseText);
                  }
              });
          @endif

        @endif

      } else if (cond === 'realization') {
            event.preventDefault();
            $.ajax({
                url: "/{{$table}}/" + id + '/realization',
                type: 'post',
                data: $('form').serialize(),
                dataType: 'json',
                success: function( _response ){
                    if( _response.status == "OK") {
                      bootbox.alert(_response.msg);
                      setTimeout(function(){
                        window.location.href = _response.url;
                      }, 2000);
                    } else {
                        $('button[type="submit"]').prop('disabled', false);
                        msg = '';
                        for (i=0;i<_response.msg.length;i++) {
                          msg = msg + _response.msg[i] + '<br />';
                        }
                        bootbox.alert(msg );
                    }
                },
                error: function( _response ){
                    $('button[type="submit"]').prop('disabled', false);
                    bootbox.alert(_response.responseText);
                }
            });
      } else if (cond === 'confirmation') {
            event.preventDefault();
            $.ajax({
                url: "/{{$table}}/" + id + '/confirmation',
                type: 'post',
                data: $('form').serialize(),
                dataType: 'json',
                success: function( _response ){
                    if( _response.status == "OK") {
                      bootbox.alert(_response.msg);
                      setTimeout(function(){
                        window.location.href = _response.url;
                      }, 2000);
                    } else {
                        $('button[type="submit"]').prop('disabled', false);
                        msg = '';
                        for (i=0;i<_response.msg.length;i++) {
                          msg = msg + _response.msg[i] + '<br />';
                        }
                        bootbox.alert(msg );
                    }
                },
                error: function( _response ){
                    $('button[type="submit"]').prop('disabled', false);
                    bootbox.alert(_response.responseText);
                }
            });
        }
      else {
        event.preventDefault();
        @if (isset($prefix_category))
          @if ($prefix_category =='handover')
          //  $('#handover_date_formatted').val($("#datetimepicker1").data("DateTimePicker").date().format("Y-MM-DD"));
          @endif
        @endif

        @if (isset($handled_by))
              $.ajax({
                  url: "/{{$table}}/" + id + "/set-handled",
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
                          $('button[type="submit"]').prop('disabled', false);
                          msg = '';
                          for (i=0;i<_response.msg.length;i++) {
                            msg = msg + _response.msg[i] + '<br />';
                          }
                          bootbox.alert(msg );
                      }
                  },
                  error: function( _response ){
                      $('button[type="submit"]').prop('disabled', false);
                      bootbox.alert(_response.responseText);
                  }
              });
        @else
            @if ($table == 'file')
               var formData = new FormData($(this)[0]);
               $.ajax({
                   url: "/{{$table}}/" + id,
                   type: 'POST',
                   data: formData,
                   dataType: 'json',
                   cache: false,
                   contentType: false,
                   processData: false,
                   success: function( _response ){
                       if( _response.status == "OK") {
                         bootbox.alert("Update Success");
                         setTimeout(function(){
                           window.location.href = _response.url;
                         }, 2000);
                       } else {
                          $('button[type="submit"]').prop('disabled', false);
                           msg = '';
                           for (i=0;i<_response.msg.length;i++) {
                             msg = msg + _response.msg[i] + '<br />';
                           }
                           bootbox.alert(msg );
                       }
                   },
                   error: function( _response ){
                      $('button[type="submit"]').prop('disabled', false);
                       bootbox.alert(_response.responseText);
                   }
               });
            @else
                $.ajax({
                    url: "/{{$table}}/" + id,
                    type: 'put',
                    data: $('form').serialize(),
                    dataType: 'json',
                    success: function( _response ){
                        if( _response.status == "OK") {
                          bootbox.alert("Update Success");
                          setTimeout(function(){
                            window.location.href = _response.url;
                          }, 2000);
                        } else {
                            $('button[type="submit"]').prop('disabled', false);
                            msg = '';
                            for (i=0;i<_response.msg.length;i++) {
                              msg = msg + _response.msg[i] + '<br />';
                            }
                            bootbox.alert(msg );
                        }
                    },
                    error: function( _response ){
                      $('button[type="submit"]').prop('disabled', false);
                        bootbox.alert(_response.responseText);
                    }
                });
            @endif
        @endif






      }


    });


  });


  var approvedOrNot = function (approvedFlag) {
    if (cond === 'approval') {
      event.preventDefault();
            $.ajax({
                url: "/approval-list/{{$table}}/"+ id,
                type: 'post',
                data: { _token: '{{{ csrf_token() }}}',
                         'approved_flag' : approvedFlag,
                         'reason' : $('#tb_reason').val()},
                dataType: 'json',
                success: function( _response ){
                  if( _response.status == "OK") {
                    bootbox.alert("Approval Done");
                    setTimeout(function(){
                      window.location.href = _response.url;
                    }, 2000);
                  } else {
                      msg = '';
                      for (i=0;i<_response.msg.length;i++) {
                        msg = msg + _response.msg[i] + '<br />';
                      }
                      bootbox.aler(msg);
                  }

                },
                error: function( _response ){
                    bootbox.alert(_response.responseText);
                }
            });
    }
  }


</script>

@endsection
