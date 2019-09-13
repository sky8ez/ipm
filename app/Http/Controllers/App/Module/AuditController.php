<?php

namespace App\Http\Controllers\App\Module;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Tr_audit;
use App\Tr_activity_log;
use App\Mt_table_filter;
use Validator;
use DB;
use Response;
use Session;
use Illuminate\Support\Facades\Input;
use App\Repositories\ValidatorRepository;
use App\Repositories\AccessRepository;

class AuditController extends Controller
{
  /**
   * Send back all datas as JSON
   *
   * @return Response
   */
  private $form_id = "AUDIT";

  private $validator;
  private $access;
  private $access_list = [];

  public function __construct(ValidatorRepository $validator,AccessRepository $access)
 {
     $this->validator = $validator;
     $this->access = $access;
 }

  public function index(Request $request)
  {
      $records = Tr_audit::whereRaw("1=1")
      ->leftJoin('users as users', function($join) {
        $join->on('tr_audit.modified_user_id', '=', 'users.id');
      })
      ->where('tr_audit.transaction_category',$request->category)
      ->where('tr_audit.transaction_id',$request->id)
      ->where('tr_audit.delete_flag',false)
      ->orderByRaw('tr_audit.created_at','asc')
      ->get(['tr_audit.*','users.name as modified_user']);

      $print_records = Tr_activity_log::whereRaw("1=1")
      ->leftJoin('users as users', function($join) {
        $join->on('tr_activity_log.user_id', '=', 'users.id');
      })
      ->where('tr_activity_log.transaction_category',$request->category)
      ->where('tr_activity_log.transaction_id',$request->id)
      ->orderByRaw('tr_activity_log.created_at','asc')
      ->get(['tr_activity_log.*','users.name as user']);

      $datas = [];
      $print_datas = [];

      foreach ($records as $record  ) {
        $row = ['id' => $record->id,
                 'records' => [
                             ['text_align' => 'left', 'type' => 'text' , 'value' => $record->status],
                             ['text_align' => 'left', 'type' => 'text' , 'value' => $record->column],
                             ['text_align' => 'left', 'type' => 'text' , 'value' => $record->value_old],
                             ['text_align' => 'left', 'type' => 'text' , 'value' => $record->value_new],
                             ['text_align' => 'left', 'type' => 'text' , 'value' => $record->modified_user],
                             ['text_align' => 'left', 'type' => 'datetime' , 'value' => (string)$record->updated_at],
                   ]];
        array_push($datas, $row);
      }

      foreach ($print_records as $record  ) {
        $row = ['id' => $record->id,
                 'records' => [
                             ['text_align' => 'left', 'type' => 'text' , 'value' => $record->activity_date],
                             ['text_align' => 'left', 'type' => 'text' , 'value' => $record->user],
                             ['text_align' => 'left', 'type' => 'text' , 'value' => $record->browser],
                             ['text_align' => 'left', 'type' => 'text' , 'value' => $record->ip]
                   ]];
        array_push($print_datas, $row);
      }

      $result = [
        'datas' =>  $datas,
        'print_datas' =>  $print_datas
      ];

      return Response::json($result);
  }
}
