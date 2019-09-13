<?php

namespace App\Http\Controllers\App\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Mt_material;
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

use App\Http\Requests;

class MaterialController extends Controller
{
  /**
   * Send back all datas as JSON
   *
   * @return Response
   */
  private $form_id = "MATERIAL";

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
      $header = ['label' => trans('material.material_code'),'value' => 'mt_material.material_code', 'type' => 'text', 'table' => '', 'sort' => 'sort'];
      array_push($headers,$header);
      $header = ['label' => trans('material.material_name'),'value' => 'mt_material.material_name', 'type' => 'text', 'table' => '', 'sort' => 'sort'];
      array_push($headers,$header);
      $header = ['label' => trans('material.unit'),'value' => 'mt_material.unit', 'type' => 'text', 'table' => '', 'sort' => 'sort'];
      array_push($headers,$header);
      $header = ['label' => trans('material.type'),'value' => 'mt_material.type', 'type' => 'text', 'table' => '', 'sort' => 'sort'];
      array_push($headers,$header);
      $header = ['label' => trans('customer.updated_at'),'value' => 'mt_material.updated_at', 'type' => 'text', 'table' => '', 'sort' => 'sort'];
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

      $records = Mt_material::whereRaw($search)
      ->where('delete_flag',false)
      ->orderByRaw($sort)
      ->skip(25 * $skip)
      ->take(25)
      ->get();

      if ($skip == 0) {
        $records_count = Mt_material::whereRaw($search)
        ->where('delete_flag',false)
        ->count();
      } else {
        $records_count = 0;
      }


      $datas = [];

      foreach ($records as $record  ) {
        $row = ['id' => $record->id,
                 'records' => [
                             ['text_align' => 'left', 'type' => 'text' , 'value' => $record->material_code],
                             ['text_align' => 'left', 'type' => 'text' , 'value' => $record->material_name],
                             ['text_align' => 'left', 'type' => 'text' , 'value' => $record->unit],
                             ['text_align' => 'left', 'type' => 'text' , 'value' => $record->type],
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
    if ($id != "") {
      $data = Mt_material::where('mt_material.id',$id)
      ->first(['mt_material.*']);
    }

    $forms = [];
    $form = ['label' => 'Material Code','placeholder' => 'Material Code',
             'type'  => 'text','name' => 'material_code',
             'col' => ['row' => 6, 'col1' => 4, 'col2' => 8],
             'required' => 'true',
             'unique' => 'unique:mt_material,material_code,'.$id,
             'read_only' => ($cond=="insert" ? false : true ),
             'value' => ($cond=="insert" ? '' : $data->material_code ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Type','placeholder' => 'Type',
             'type'  => 'select','name' => 'type',
             'col' => ['row' => 6, 'col1' => 4, 'col2' => 8],
            //  'required' => 'true',
             'options' => ['SATUAN','LEMBARAN'],
             'value' => ($cond=="insert" ? '' : $data->type ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Material Name','placeholder' => 'Material Name',
             'type'  => 'text','name' => 'material_name',
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'required' => 'true',
             'unique' => 'unique:mt_material,material_name,'.$id,
             'value' => ($cond=="insert" ? '' : $data->material_name ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Unit','placeholder' => 'Unit',
             'type'  => 'text','name' => 'unit',
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'required' => 'true',
             'value' => ($cond=="insert" ? '' : $data->unit ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Length','placeholder' => 'Length',
             'type'  => 'number','name' => 'length',
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'required' => 'true',
             'value' => ($cond=="insert" ? '' : $data->length ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Width','placeholder' => 'Width',
             'type'  => 'number','name' => 'width',
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'required' => 'true',
             'value' => ($cond=="insert" ? '' : $data->width ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Image','placeholder' => 'Image',
             'type'  => 'number','name' => 'file_id',
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'required' => 'true',
             'value' => ($cond=="insert" ? '' : $data->file_id ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Hrg Satuan / Per Meter','placeholder' => 'Hrg Satuan / Per Meter',
             'type'  => 'currency','name' => 'price',
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'required' => 'true',
             'value' => ($cond=="insert" ? '' : $data->price ),
           ];
    array_push($forms,$form);

    $result = [
      'forms' =>  $forms,
      'form_id' => $this->form_id,
      'form_table' => 'material',
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
           $record = Mt_material::create($save_datas);

           $audit = new Tr_audit;
           $audit->transaction_category = 'material';
           $audit->transaction_id = $record->id;
           $audit->status = 'insert';
           $audit->column = "";
           $audit->value_old = "";
           $audit->value_new = "";
           $audit->modified_user_id = $request->session()->get('user_id');
           $audit->save();

           DB::commit();
           $arr = array('code' => "200", 'status' => "OK", 'url' => '#/list/material', 'value_search' => $save_datas['material_name'] );
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
           Mt_material::where('id',$id)
                    ->update($save_datas);


          if ($this->columns !== "") {
            $audit = new Tr_audit;
            $audit->transaction_category = 'material';
            $audit->transaction_id = $id;
            $audit->status = 'update';
            $audit->column = $this->columns;
            $audit->value_old = $this->valuebefore;
            $audit->value_new = $this->valueafter;
            $audit->modified_user_id = $request->session()->get('user_id');
            $audit->save();
          }

           DB::commit();
           $arr = array('code' => "200", 'status' => "OK",'url' => '#/list/material');
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

     $record = Mt_material::find($id);
     if(isset($record)) {
       $record->delete_flag = true;
     }

     DB::beginTransaction();
     try {
       $record->update();

       DB::commit();
       $arr = array('code' => "200", 'status' => "OK",'url' => '#/list/material');
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
         $record = Mt_material::find($data);
         if(isset($record)) {
            $record->delete_flag = true;
            $record->update();
         }
       }

       DB::commit();
       $arr = array('code' => "200", 'status' => "OK",'url' => '#/list/material');
       return json_encode($arr);
     } catch(\Exception $e){
       DB::rollback();
       $arr = array('code' => "200", 'status' => "FAIL", 'msg' => $e->getMessage());
       return json_encode($arr);
     }
  }
}
