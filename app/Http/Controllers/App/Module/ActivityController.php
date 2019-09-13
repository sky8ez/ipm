<?php

namespace App\Http\Controllers\App\Module;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Tr_activity_log;
use App\Mt_table_filter;
use Validator;
use DB;
use Response;
use Exception;
use App\Tr_audit;
use Illuminate\Support\Facades\Input;
use App\Repositories\ValidatorRepository;
use App\Repositories\AccessRepository;
use Session;

class ActivityController extends Controller
{
  /**
   * Send back all datas as JSON
   *
   * @return Response
   */
  private $form_id = "ACTIVITY";

  private $validator;
  private $access;
  private $access_list = [];


  public function __construct(ValidatorRepository $validator,AccessRepository $access)
 {
     $this->validator = $validator;
     $this->access = $access;
 }


  public function index($skip = 0)
  {
    $this->access_list = $this->access->getAccess($this->form_id, Session::get('user_access_id'));

      $search = "1=1";
      $sort = "activity_date desc";
      $filters = Mt_table_filter::where('form_id',$this->form_id)
                                ->get();

      $headers = [];
      $header = ['label' => trans('activity.activity_date'),'value' => 'tr_activity_log.activity_date', 'type' => 'datetime', 'table' => '', 'sort' => 'sort'];
      array_push($headers,$header);
      $header = ['label' => trans('activity.user_name'),'value' => 'users.name', 'type' => 'text', 'table' => '', 'sort' => 'sort'];
      array_push($headers,$header);
      $header = ['label' => trans('activity.action'),'value' => 'tr_activity_log.action', 'type' => 'text', 'table' => '', 'sort' => 'sort'];
      array_push($headers,$header);
      $header = ['label' => trans('activity.ip'),'value' => 'tr_activity_log.ip', 'type' => 'text', 'table' => '', 'sort' => 'sort'];
      array_push($headers,$header);
      $header = ['label' => trans('activity.browser'),'value' => 'tr_activity_log.browser', 'type' => 'text', 'table' => '', 'sort' => 'sort'];
      array_push($headers,$header);
      $header = ['label' => trans('activity.updated_at'),'value' => 'tr_activity_log.updated_at', 'type' => 'text', 'table' => '', 'sort' => 'sort'];
      array_push($headers,$header);

      foreach ($filters as $filter) {
        if ($filter->category == 'FILTER') {
          $search = $search." and ".$filter->column_name." ".$filter->filter." '".$filter->value."'";
        } else { //SORT
          $sort = $filter->column_name." ".$filter->filter;
          $index = array_search($filter->column_name, array_column($headers, 'value'));
          if ($index !== false) {
            array_set($headers, $index.'.sort', $filter->filter);
          }
        }
      }

      $records = Tr_activity_log::whereRaw($search)
      // ->where('delete_flag',false)
      ->leftJoin('users as users', function($join) {
        $join->on('tr_activity_log.user_id', '=', 'users.id');
      })
      ->orderByRaw($sort)
      ->skip(25 * $skip)
      ->take(25)
      ->get(['tr_activity_log.*','users.name as user_name']);

      if ($skip == 0) {
        $records_count = Tr_activity_log::whereRaw($search)
        // ->where('delete_flag',false)
        ->count();
      } else {
        $records_count = 0;
      }


      $datas = [];

      foreach ($records as $record  ) {
        $row = ['id' => $record->id,
                 'records' => [
                             ['text_align' => 'left', 'type' => 'datetime' , 'value' => $record->activity_date],
                             ['text_align' => 'left', 'type' => 'text' , 'value' => $record->user_name],
                             ['text_align' => 'left', 'type' => 'text' , 'value' => $record->action],
                             ['text_align' => 'left', 'type' => 'text' , 'value' => $record->ip],
                             ['text_align' => 'left', 'type' => 'text' , 'value' => $record->browser],
                             ['text_align' => 'left', 'type' => 'datetime' , 'value' => (string)$record->updated_at],
                   ]];
        array_push($datas, $row);
      }

      $result = [
        'headers' =>  $headers,
        'datas' =>  $datas,
        'count' =>  $records_count,
        'form_id' => $this->form_id,
        'access_list' => $this->access_list,
        'role' => Session::get('role'),
      ];

      return Response::json($result);
  }

  // // get form template for customer
  // public function getForm($cond="insert", $id = "")
  // {
  //   $this->access_list = $this->access->getAccess($this->form_id, Session::get('user_access_id'));
  //
  //   $data = [];
  //   if ($id != "") {
  //     $data = Mt_password_word::where('mt_password_word.id',$id)
  //     ->first(['mt_password_word.*']);
  //   }
  //
  //   $forms = [];
  //   $form = ['label' => 'Type','placeholder' => 'Type',
  //            'type'  => 'select','name' => 'type',
  //            'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
  //            'options' => ['STRING','NUMERIC','SYMBOL'],
  //            'required' => 'true',
  //            'value' => ($cond=="insert" ? '' : $data->type ),
  //          ];
  //   array_push($forms,$form);
  //   $form = ['label' => 'Word','placeholder' => 'Word',
  //            'type'  => 'text','name' => 'word',
  //            'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
  //            'unique' => 'unique:mt_password_word,word,'.$id,
  //            'required' => 'true',
  //            'value' => ($cond=="insert" ? '' : $data->word ),
  //          ];
  //   array_push($forms,$form);
  //
  //   $result = [
  //     'forms' =>  $forms,
  //     'form_id' => $this->form_id,
  //     'form_table' => 'password',
  //     'data_before' => $data,
  //     'access_list' => $this->access_list,
  //     'role' => Session::get('role'),
  //   ];
  //
  //   return Response::json($result);
  // }
  //
  // //audit trail
  // protected $columns = "";
  // protected $valuebefore = "";
  // protected $valueafter = "";
  // //audit trail
  // public function auditTrail($columnname, $old, $new) {
  //   if ($old != $new) {
  //     $this->columns = $this->columns.";".$columnname;
  //     $this->valuebefore = $this->valuebefore.";".$old;
  //     $this->valueafter = $this->valueafter.";".$new;
  //   }
  // }
  //
  // /**
  //  * Store a newly created resource in storage.
  //  *
  //  * @return Response
  //  */
  // public function store(Request $request)
  // {
  //      $datas = $request->data;
  //      $save_datas = [];
  //
  //      $rules = $this->validator->getValidator($datas);
  //
  //      $validator = Validator::make($request->all(), $rules);
  //
  //      if ($validator->passes()) {
  //        $save_datas = $this->validator->convertInput($datas);
  //
  //        DB::beginTransaction();
  //        try {
  //          $record = Mt_password_word::create($save_datas);
  //
  //          $audit = new Tr_audit;
  //          $audit->transaction_category = 'password';
  //          $audit->transaction_id = $record->id;
  //          $audit->status = 'insert';
  //          $audit->column = "";
  //          $audit->value_old = "";
  //          $audit->value_new = "";
  //          $audit->modified_user_id = $request->session()->get('user_id');
  //          $audit->save();
  //
  //          DB::commit();
  //          $arr = array('code' => "200", 'status' => "OK", 'url' => '#/list/password');
  //          return json_encode($arr);
  //        } catch(\Exception $e){
  //          DB::rollback();
  //          $arr = array('code' => "200", 'status' => "FAIL", 'msg' => $e->getMessage());
  //          return json_encode($arr);
  //        }
  //      } else {
  //        //http_response_code(404);
  //        $result ="";
  //        foreach ($validator->errors()->all() as $error) {
  //          $result = $result."<br>".$error;
  //        }
  //        $arr = array('code' => "200", 'status' => "FAIL", 'msg' => $result);
  //        return json_encode($arr);
  //      }
  // }
  //
  //
  // public function update(Request $request, $id)
  // {
  //      $datas = $request->data;
  //      $save_datas = [];
  //      $data_before = $request->data_before;
  //
  //      $rules = $this->validator->getValidator($datas);
  //
  //      $validator = Validator::make($request->all(), $rules);
  //
  //      if ($validator->passes()) {
  //        //-------------------AUDIT---------------------
  //        foreach ($datas as $data) {
  //          $hide = 'false';
  //          if (isset($data['hide'])) {
  //            $hide = $data['hide'];
  //          }
  //          if ($hide !== 'true') {
  //            switch ($data['type']) {
  //              case 'text':
  //                $this->auditTrail($data['name'], $data_before[$data['name']], $data['value']);
  //                break;
  //              case 'data':
  //                $this->auditTrail($data['name'], $data_before[$data['name']], $data['value']);
  //                break;
  //              case 'datetime':
  //                $this->auditTrail($data['name'], date_format(date_create($data_before[$data['name']]),"Y-m-d") , $data['value']);
  //                break;
  //              case 'currency':
  //                $this->auditTrail($data['name'], $data_before[$data['name']], $data['value']);
  //                break;
  //              case 'boolean':
  //                $this->auditTrail($data['name'], $data_before[$data['name']], $data['value']);
  //                break;
  //              default:
  //                //$this->auditTrail($data['name'], $data_before[$data['name']], $data['value']);
  //                break;
  //            }
  //          }
  //
  //        }
  //        //-------------------AUDIT---------------------
  //
  //        $save_datas = $this->validator->convertInput($datas);
  //
  //        DB::beginTransaction();
  //        try {
  //          Mt_password_word::where('id',$id)
  //                   ->update($save_datas);
  //
  //           if ($this->columns !== "") {
  //             $audit = new Tr_audit;
  //             $audit->transaction_category = 'password';
  //             $audit->transaction_id = $id;
  //             $audit->status = 'update';
  //             $audit->column = $this->columns;
  //             $audit->value_old = $this->valuebefore;
  //             $audit->value_new = $this->valueafter;
  //             $audit->modified_user_id = $request->session()->get('user_id');
  //             $audit->save();
  //           }
  //
  //          DB::commit();
  //          $arr = array('code' => "200", 'status' => "OK",'url' => '#/list/password');
  //          return json_encode($arr);
  //        } catch(\Exception $e){
  //          DB::rollback();
  //          $arr = array('code' => "200", 'status' => "FAIL", 'msg' => $e->getMessage());
  //          return json_encode($arr);
  //        }
  //      } else {
  //        //http_response_code(404);
  //        $result ="";
  //        foreach ($validator->errors()->all() as $error) {
  //          $result = $result."<br>".$error;
  //        }
  //        $arr = array('code' => "200", 'status' => "FAIL", 'msg' => $result);
  //        return json_encode($arr);
  //      }
  // }
  //
  // /**
  //  * Remove the specified resource from storage.
  //  *
  //  * @param  int  $id
  //  * @return Response
  //  */
  // public function destroy($id)
  // {
  //    $record = Mt_password_word::find($id);
  //    if(isset($record)) {
  //      $record->delete_flag = true;
  //    }
  //
  //    DB::beginTransaction();
  //    try {
  //
  //      $record->update();
  //
  //      DB::commit();
  //      $arr = array('code' => "200", 'status' => "OK",'url' => '#/list/password');
  //      return json_encode($arr);
  //    } catch(\Exception $e){
  //      DB::rollback();
  //      $arr = array('code' => "200", 'status' => "FAIL", 'msg' => $e->getMessage());
  //      return json_encode($arr);
  //    }
  //
  // }
  //
  // /**
  //  * Remove the specified resource from storage.
  //  *
  //  * @param  int  $id
  //  * @return Response
  //  */
  // public function destroyMany(Request $request)
  // {
  //   $datas = $request->data;
  //
  //    DB::beginTransaction();
  //    try {
  //      foreach ($datas as $data) {
  //        $record = Mt_password_word::find($data);
  //        if(isset($record)) {
  //           $record->delete_flag = true;
  //           $record->update();
  //        }
  //      }
  //
  //      DB::commit();
  //      $arr = array('code' => "200", 'status' => "OK",'url' => '#/list/password');
  //      return json_encode($arr);
  //    } catch(\Exception $e){
  //      DB::rollback();
  //      $arr = array('code' => "200", 'status' => "FAIL", 'msg' => $e->getMessage());
  //      return json_encode($arr);
  //    }
  // }
}