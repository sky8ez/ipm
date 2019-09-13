<?php

namespace App\Http\Controllers\App\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Mt_password_word;
use App\Mt_password_list;
use App\Mt_password_category_user;
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


class PasswordListController extends Controller
{
  /**
   * Send back all datas as JSON
   *
   * @return Response
   */
  private $form_id = "PASSWORD-LIST";

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
      $sort = "mt_password_category.name asc";
      $filters = Mt_table_filter::where('form_id',$this->form_id)
                                ->get();

      $headers = [];
      $header = ['label' => trans('password_list.category'),'value' => 'mt_password_category.name', 'type' => 'text', 'table' => '', 'sort' => 'sort'];
      array_push($headers,$header);
      $header = ['label' => trans('password_list.name'),'value' => 'mt_password_list.name', 'type' => 'text', 'table' => '', 'sort' => 'sort'];
      array_push($headers,$header);
      $header = ['label' => trans('password_list.username'),'value' => 'mt_password_list.username', 'type' => 'text', 'table' => '', 'sort' => 'sort'];
      array_push($headers,$header);
      $header = ['label' => trans('password_list.pass'),'value' => 'mt_password_list.pass', 'type' => 'text', 'table' => '', 'sort' => 'sort'];
      array_push($headers,$header);
      $header = ['label' => trans('password_list.remarks'),'value' => 'mt_password_list.remarks', 'type' => 'text', 'table' => '', 'sort' => 'sort'];
      array_push($headers,$header);

      foreach ($filters as $filter) {
        if ($filter->category == 'FILTER') {
          if($filter->filter == 'like') {
            $search = $search." and ".$filter->column_name." ".$filter->filter." '%".$filter->value."%'";
          } else {
            $search = $search." and ".$filter->column_name." ".$filter->filter." '".$filter->value."'";
          }
          
        } else { //SORT
          $sort = $filter->column_name." ".$filter->filter;
          $index = array_search($filter->column_name, array_column($headers, 'value'));
          if ($index !== false) {
            array_set($headers, $index.'.sort', $filter->filter);
          }
        }
      }

      $pass_cat = "";
      $pass_categories = Mt_password_category_user::where('user_id',Session::get('user_id'))
          ->where('delete_flag',false)
          ->get();
      if(isset($pass_categories)) {
        foreach ($pass_categories as $pass_category) {
          if ($pass_cat == "") {
            $pass_cat = $pass_cat." and (mt_password_list.password_category_id = ".$pass_category->password_category_id;
          } else {
            $pass_cat = $pass_cat." or mt_password_list.password_category_id = ".$pass_category->password_category_id;
          }
        }
        if ($pass_cat !== "") {
          $pass_cat = $pass_cat.")";
        }
      }



      $records = Mt_password_list::whereRaw($search.$pass_cat)
      ->leftJoin('mt_password_category as mt_password_category', function($join) {
        $join->on('mt_password_list.password_category_id', '=', 'mt_password_category.id');
      })
      ->where('mt_password_list.delete_flag',false)
      ->where('mt_password_list.active_flag',true)
      ->orderByRaw($sort)
      ->skip(25 * $skip)
      ->take(25)
      ->get(['mt_password_list.*','mt_password_category.name as category']);

      if ($skip == 0) {
        $records_count = Mt_password_list::whereRaw($search)
        ->leftJoin('mt_password_category as mt_password_category', function($join) {
          $join->on('mt_password_list.password_category_id', '=', 'mt_password_category.id');
        })
        ->where('mt_password_list.delete_flag',false)
        ->where('mt_password_list.active_flag',true)
        ->count();
      } else {
        $records_count = 0;
      }


      $datas = [];

      foreach ($records as $record  ) {
        $row = ['id' => $record->id,
                 'records' => [
                             ['text_align' => 'left', 'type' => 'text' , 'value' => $record->category],
                             ['text_align' => 'left', 'type' => 'text' , 'value' => $record->name],
                             ['text_align' => 'left', 'type' => 'text' , 'value' => $record->username],
                             ['text_align' => 'left', 'type' => 'text' , 'value' => $record->pass],
                             ['text_align' => 'left', 'type' => 'text' , 'value' => $record->remarks],
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
        'test' => $pass_cat,
        'role' => Session::get('role'),
      ];

      return Response::json($result);
  }

  public function randomUppercase($word) {
    $result = $word;
    for($i=0;$i<strlen($word);$i++) {
      $rand = rand(0,1);
      if($rand==1){
        $result[$i] = strtoupper($result[$i]);
      } else {
        //do nothing
      }
    }
    return $result;
  }

  public function generatePassword(Request $request) {
    $result = "";

    $passwords = Mt_password_word::where('delete_flag',false)->get();
    $word = false;
    $used = [];
    $types = ['STRING','STRING','STRING','NUMERIC','SYMBOL'];
    shuffle($types);
    // $types2 = $types;
    // shuffle($types2);
    // $types = $types2;
    for ($i=0;$i<5;$i++) {
      $word = false;
      while ($word == false) {
        $rand = rand(0,(count($passwords)-1));
        $pass = $passwords[$rand]->word;
        if(0 < count(array_intersect(array_map('strtolower', explode(' ', $pass)), $used)))
          {
            //do nothing
          } else {
            if($types[$i] == $passwords[$rand]->type) {
              $word = true;
              switch ($passwords[$rand]->type) {
                case 'STRING':
                $result= $result.$this->randomUppercase($pass);
                  break;
                case 'NUMERIC':
                  $result= $result.$pass;
                  break;
                case 'SYMBOL':
                  $result= $result.$pass;
                  break;
                default:
                  # code...
                  break;
              }
              array_push($used,$pass);
            } else {
              $result= $result."";
            }
          }

      }
    }
    $types = [];


    $arr = array('code' => "200", 'status' => "FAIL", 'password' => $result);
    return json_encode($arr);
  }


  public function generatePasswordRandomNumber(Request $request) {
    $result = "";

    $passwords = Mt_password_word::where('delete_flag',false)->get();
    $word = false;
    $used = [];
    $types = ['STRING','STRING','STRING','NUMERIC','SYMBOL'];
    shuffle($types);
    // $types2 = $types;
    // shuffle($types2);
    // $types = $types2;
    for ($i=0;$i<5;$i++) {
      $word = false;
      while ($word == false) {
        $rand = rand(0,(count($passwords)-1));
        $pass = $passwords[$rand]->word;
        if(0 < count(array_intersect(array_map('strtolower', explode(' ', $pass)), $used)))
          {
            //do nothing
          } else {
            if($types[$i] == $passwords[$rand]->type) {
              $word = true;
              switch ($passwords[$rand]->type) {
                case 'STRING':
                $result= $result.$this->randomUppercase($pass);
                  break;
                case 'NUMERIC':
                  $result= $result.rand(100,999);
                  break;
                case 'SYMBOL':
                  $result= $result.$pass;
                  break;
                default:
                  # code...
                  break;
              }
              array_push($used,$pass);
            } else {
              $result= $result."";
            }
          }

      }
    }
    $types = [];


    $arr = array('code' => "200", 'status' => "FAIL", 'password' => $result);
    return json_encode($arr);
  }

  // get form template for customer
  public function getForm($cond="insert", $id = "")
  {
    $this->access_list = $this->access->getAccess($this->form_id, Session::get('user_access_id'));

    $data = [];
    if ($id != "") {
      $data = Mt_password_list::where('mt_password_list.id',$id)
      ->leftJoin('mt_password_category as mt_password_category', function($join) {
        $join->on('mt_password_list.password_category_id', '=', 'mt_password_category.id');
      })
      ->first(['mt_password_list.*','mt_password_category.name as password_category_name']);
    }

    $forms = [];
    $form = ['label' => 'Name','placeholder' => 'Name',
             'type'  => 'text','name' => 'name',
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'required' => 'true',
             'value' => ($cond=="insert" ? '' : $data->name ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Category','placeholder' => 'Category',
              'type'  => 'data','table'=>'password-category','name' => 'password_category_name',
              'id' => 'password_category_id',
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'required' => 'true',
             'value' => ($cond=="insert" ? '' : $data->password_category_name ),
             'value_id' => ($cond=="insert" ? '' : $data->password_category_id ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Username','placeholder' => 'Username',
             'type'  => 'text','name' => 'username',
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'required' => 'true',
             'value' => ($cond=="insert" ? '' : $data->username ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Password','placeholder' => 'Password',
             'type'  => 'text','name' => 'pass',
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'required' => 'true',
             'value' => ($cond=="insert" ? '' : $data->pass ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Generate','placeholder' => 'Generate',
             'type'  => 'generate_password','name' => '',
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'value' => ($cond=="insert" ? '' : ''),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Remarks','placeholder' => 'Remarks',
             'type'  => 'text','name' => 'remarks',
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'value' => ($cond=="insert" ? '' : $data->remarks ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Active Flag','placeholder' => 'Active Flag',	
            'type'  => 'checkbox','name' => 'active_flag',	
            'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],	
            'required' => 'false',	
            'read_only' => ($cond=='insert' ? false : false ),	
            'value' => ($cond=='insert' ? true : ($data->active_flag == 1 ? true : false) ),	
    ];	
    array_push($forms,$form);

    $result = [
      'forms' =>  $forms,
      'form_id' => $this->form_id,
      'form_table' => 'password-list',
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
           $record = Mt_password_list::create($save_datas);

           $audit = new Tr_audit;
           $audit->transaction_category = 'password-list';
           $audit->transaction_id = $record->id;
           $audit->status = 'insert';
           $audit->column = "";
           $audit->value_old = "";
           $audit->value_new = "";
           $audit->modified_user_id = $request->session()->get('user_id');
           $audit->save();

           DB::commit();
           $arr = array('code' => "200", 'status' => "OK", 'url' => '#/list/password-list');
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
       $save_datas = [];
       $data_before = $request->data_before;

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
           Mt_password_list::where('id',$id)
                    ->update($save_datas);

            if ($this->columns !== "") {
              $audit = new Tr_audit;
              $audit->transaction_category = 'password-list';
              $audit->transaction_id = $id;
              $audit->status = 'update';
              $audit->column = $this->columns;
              $audit->value_old = $this->valuebefore;
              $audit->value_new = $this->valueafter;
              $audit->modified_user_id = $request->session()->get('user_id');
              $audit->save();
            }

           DB::commit();
           $arr = array('code' => "200", 'status' => "OK",'url' => '#/list/password-list');
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
     $record = Mt_password_list::find($id);
     if(isset($record)) {
       $record->delete_flag = true;
     }

     DB::beginTransaction();
     try {

       $record->update();

       DB::commit();
       $arr = array('code' => "200", 'status' => "OK",'url' => '#/list/password-list');
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
         $record = Mt_password_list::find($data);
         if(isset($record)) {
            $record->delete_flag = true;
            $record->update();
         }
       }

       DB::commit();
       $arr = array('code' => "200", 'status' => "OK",'url' => '#/list/password-list');
       return json_encode($arr);
     } catch(\Exception $e){
       DB::rollback();
       $arr = array('code' => "200", 'status' => "FAIL", 'msg' => $e->getMessage());
       return json_encode($arr);
     }
  }
}
