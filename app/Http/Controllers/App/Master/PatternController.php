<?php

namespace App\Http\Controllers\App\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Mt_pattern;
use App\Mt_pattern_detail;
use App\Mt_table_filter;
use Validator;
use DB;
use Response;
use Exception;
use Session;
use File;
use App\Tr_audit;
use Illuminate\Support\Facades\Input;
use App\Repositories\ValidatorRepository;
use App\Repositories\AccessRepository;


class PatternController extends Controller
{
  /**
   * Send back all datas as JSON
   *
   * @return Response
   */
  private $form_id = "PATTERN";

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
      $sort = "id desc";
      $filters = Mt_table_filter::where('form_id',$this->form_id)
                                ->get();

      $headers = [];
      $header = ['label' => trans('pattern.name'),'value' => 'mt_pattern.name', 'type' => 'text', 'table' => '', 'sort' => 'sort'];
      array_push($headers,$header);
      $header = ['label' => trans('product.updated_at'),'value' => 'mt_item.updated_at', 'type' => 'text', 'table' => '', 'sort' => 'sort'];
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

      $records = Mt_pattern::whereRaw($search)
      ->where('delete_flag',false)
      ->orderByRaw($sort)
      ->skip(25 * $skip)
      ->take(25)
      ->get();

      if ($skip == 0) {
        $records_count = Mt_pattern::whereRaw($search)
        ->where('delete_flag',false)
        ->count();
      } else {
        $records_count = 0;
      }


      $datas = [];

      foreach ($records as $record  ) {
        $row = ['id' => $record->id,
                 'records' => [
                             ['text_align' => 'left', 'type' => 'text' , 'value' => $record->pattern_name],
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

  // get form template for customer
  public function getForm($cond="insert", $id = "")
  {
    $this->access_list = $this->access->getAccess($this->form_id, Session::get('user_access_id'));

    $data = [];
    $data_details = [];
    if ($id != "") {
      $data = Mt_pattern::where('mt_pattern.id',$id)
      ->first(['mt_pattern.*']);

      $data_details = Mt_pattern_detail::where('mt_pattern_detail.pattern_id',$id)
      ->get(['mt_pattern_detail.*']);
    }



    $forms = [];
    $form = ['label' => 'Pattern Name','placeholder' => 'Pattern Name',
             'type'  => 'text','name' => 'pattern_name',
             'col' => ['row' => 6, 'col1' => 4, 'col2' => 8],
             'required' => 'true',
             'unique' => 'unique:mt_pattern,pattern_name,'.$id,
            //  'read_only' => ($cond=="insert" ? false : true ),
             'value' => ($cond=="insert" ? '' : $data->pattern_name ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Pattern Type','placeholder' => 'Pattern Type',
             'type'  => 'table','name' => 'mt_pattern_detail',
             'col' => ['row' => 9, 'col1' => 0, 'col2' => 12],
            //  'required' => 'true',
             'value' => ($cond=="insert" ? '' : '' ),
             'collapse' => false,
             'custom_button_flag' => false,
             'columns' => [
                          ['col_name' => 'id', 'header' => '', 'type' => 'hidden', 'hide'=> true],
                          ['col_name' => 'file_id', 'header' => 'File ID', 'type' => 'text', 'hide'=> false],
                          ['col_name' => 'qty', 'header' => 'Qty', 'type' => 'currency', 'hide'=> false],
                          ['col_name' => 'length', 'header' => 'Length (cm)', 'type' => 'currency', 'hide'=> false],
                          ['col_name' => 'width', 'header' => 'Width (cm)', 'type' => 'currency', 'hide'=> false],
             ],
             'details' => $data_details,
             'deleted_details' => [],
           ];
    array_push($forms,$form);

    $result = [
      'forms' =>  $forms,
      'form_id' => $this->form_id,
      'form_table' => 'pattern',
      'data_before' => $data,
      'access_list' => $this->access_list,
      'role' => Session::get('role'),
    ];

    return Response::json($result);
  }

  //audit trail
  protected $columns = "";
  protected $valuebefore = "";
  protected $valueafter = "";
  //audit trail
  public function auditTrail($columnname, $old, $new) {
    if ($old != $new) {
      $this->columns = $this->columns.";".$columnname;
      $this->valuebefore = $this->valuebefore.";".$old;
      $this->valueafter = $this->valueafter.";".$new;
    }
  }

  /**
   * Store a newly created resource in storage.
   *
   * @return Response
   */
  public function store(Request $request)
  {
       $datas = $request->data;
       $save_datas = [];

       $rules = $this->validator->getValidator($datas);

       $validator = Validator::make($request->all(), $rules);

       if ($validator->passes()) {
         $save_datas = $this->validator->convertInput($datas);

         DB::beginTransaction();
         try {
           $record = Mt_pattern::create($save_datas);

           $save_datas_detail = $this->validator->convertInputDetail($datas, 'pattern_id',$record->id);

           $record_detail = Mt_pattern_detail::insert($save_datas_detail['mt_pattern_detail_insert']);

           $audit = new Tr_audit;
           $audit->transaction_category = 'pattern';
           $audit->transaction_id = $record->id;
           $audit->status = 'insert';
           $audit->column = "";
           $audit->value_old = "";
           $audit->value_new = "";
           $audit->modified_user_id = $request->session()->get('user_id');
           $audit->save();

           DB::commit();
           $arr = array('code' => "200", 'status' => "OK", 'url' => '#/list/pattern', 'value_search' => $save_datas['pattern_name'] );
           return json_encode($arr);
         } catch(\Exception $e){
           DB::rollback();
           $arr = array('code' => "200", 'status' => "FAIL", 'msg' => $e->getMessage());
           return json_encode($arr);
         }
       } else {
         //http_response_code(404);
         $result ="";
         foreach ($validator->errors()->all() as $error) {
           $result = $result."<br>".$error;
         }
         $arr = array('code' => "200", 'status' => "FAIL", 'msg' => $result);
         return json_encode($arr);
       }
  }


  public function update(Request $request, $id)
  {
       $datas = $request->data;
       $data_before = $request->data_before;
       $save_datas = [];

       $rules = $this->validator->getValidator($datas);

       $validator = Validator::make($request->all(), $rules);

       if ($validator->passes()) {

         //-------------------AUDIT---------------------
         foreach ($datas as $data) {
           $hide = 'false';
           if (isset($data['hide'])) {
             $hide = $data['hide'];
           }
           if ($hide !== 'true') {
             switch ($data['type']) {
               case 'text':
                 $this->auditTrail($data['name'], $data_before[$data['name']], $data['value']);
                 break;
               case 'data':
                 $this->auditTrail($data['name'], $data_before[$data['name']], $data['value']);
                 break;
               case 'datetime':
                 $this->auditTrail($data['name'], date_format(date_create($data_before[$data['name']]),"Y-m-d") , $data['value']);
                 break;
               case 'currency':
                 $this->auditTrail($data['name'], $data_before[$data['name']], $data['value']);
                 break;
               case 'boolean':
                 $this->auditTrail($data['name'], $data_before[$data['name']], $data['value']);
                 break;
               default:
                 //$this->auditTrail($data['name'], $data_before[$data['name']], $data['value']);
                 break;
             }
           }

         }
         //-------------------AUDIT---------------------

         $save_datas = $this->validator->convertInput($datas);

         DB::beginTransaction();
         try {
           Mt_pattern::where('id',$id)
                    ->update($save_datas);


        $save_datas_detail = $this->validator->convertInputDetail($datas, 'pattern_id',$id);

        $record_detail_insert = Mt_pattern_detail::insert($save_datas_detail['mt_pattern_detail_insert']);
        foreach ($save_datas_detail['mt_pattern_detail_update'] as $detail) {
          Mt_pattern_detail::where('id',$detail['id'])
                   ->update($detail);
        }

        foreach ($save_datas_detail['mt_pattern_detail_delete'] as $detail) {
          Mt_pattern_detail::where('id',$detail['id'])->delete();
        }


          if ($this->columns !== "") {
            $audit = new Tr_audit;
            $audit->transaction_category = 'pattern';
            $audit->transaction_id = $id;
            $audit->status = 'update';
            $audit->column = $this->columns;
            $audit->value_old = $this->valuebefore;
            $audit->value_new = $this->valueafter;
            $audit->modified_user_id = $request->session()->get('user_id');
            $audit->save();
          }

           DB::commit();
           $arr = array('code' => "200", 'status' => "OK",'url' => '#/list/pattern');
           return json_encode($arr);
         } catch(\Exception $e){
           DB::rollback();
           $arr = array('code' => "200", 'status' => "FAIL", 'msg' => $e->getMessage());
           return json_encode($arr);
         }
       } else {
         //http_response_code(404);
         $result ="";
         foreach ($validator->errors()->all() as $error) {
           $result = $result."<br>".$error;
         }
         $arr = array('code' => "200", 'status' => "FAIL", 'msg' => $result);
         return json_encode($arr);
       }
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return Response
   */
  public function destroy($id)
  {

     $record = Mt_pattern::find($id);
     if(isset($record)) {
       $record->delete_flag = true;
     }

     DB::beginTransaction();
     try {
       $record->update();

       DB::commit();
       $arr = array('code' => "200", 'status' => "OK",'url' => '#/list/pattern');
       return json_encode($arr);
     } catch(\Exception $e){
       DB::rollback();
       $arr = array('code' => "200", 'status' => "FAIL", 'msg' => $e->getMessage());
       return json_encode($arr);
     }

  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return Response
   */
  public function destroyMany(Request $request)
  {
    $datas = $request->data;

     DB::beginTransaction();
     try {
       foreach ($datas as $data) {
         $record = Mt_pattern::find($data);
         if(isset($record)) {
            $record->delete_flag = true;
            $record->update();
         }
       }

       DB::commit();
       $arr = array('code' => "200", 'status' => "OK",'url' => '#/list/pattern');
       return json_encode($arr);
     } catch(\Exception $e){
       DB::rollback();
       $arr = array('code' => "200", 'status' => "FAIL", 'msg' => $e->getMessage());
       return json_encode($arr);
     }
  }
}
