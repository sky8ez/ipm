@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Period</div>

                <div class="panel-body">
                  <div class="row" style="text-align:center">
                    <div class="col-md-8 col-md-offset-2" style="margin-bottom:10px">
                      <select class="form-control" name="tb_period" id="tb_period">
                        <option value="2016">2016</option>
                        <option value="2017">2017</option>
                      </select>
                    </div>
                    <div class="col-md-8 col-md-offset-2">
                        <button type="button" id="btn_select" name="btn_select" class="btn btn-primary btn-sm">Select</button>
                    </div>
                  </div>


                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $('body').on('click','#btn_select',function () {
          $.ajax({
              url: "/set-period" ,
              type: 'POST',
              data: { _token: '{{{ csrf_token() }}}',
                      'period': $('#tb_period').val()
                    },
              dataType: 'json', //json
              success: function( _response ){
                  if( _response.status == "OK") {
                      window.location.href = _response.url;
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

@endsection
