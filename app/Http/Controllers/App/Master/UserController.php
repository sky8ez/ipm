<?php

namespace App\Http\Controllers\App\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use App\Mt_password_category_user;
use App\Mt_table_filter;
use Validator;
use DB;
use Response;
use App\Tr_audit;
use Illuminate\Support\Facades\Input;
use App\Repositories\ValidatorRepository;
use App\Repositories\AccessRepository;
use Session;

class UserController extends Controller
{
  /**
   * Send back all datas as JSON
   *
   * @return Response
   */
  private $form_id = "USER";

  private $validator;
  private $access;
  private $access_list = [];


  public function __construct(ValidatorRepository $validator,AccessRepository $access)
 {
     $this->validator = $validator;
     $this->access = $access;
 }


  public function index($skip = 0) {

      $this->access_list = $this->access->getAccess($this->form_id, Session::get('user_access_id'));

      $search = "1=1";
      $sort = "id desc";
      $filters = Mt_table_filter::where('form_id',$this->form_id)
                                ->get();

      $headers = [];
      $header = ['label' => trans('user.name'),'value' => 'users.name', 'type' => 'text', 'table' => '', 'sort' => 'sort'];
      array_push($headers,$header);
      $header = ['label' => trans('user.email'),'value' => 'users.email', 'type' => 'text', 'table' => '', 'sort' => 'sort'];
      array_push($headers,$header);
      $header = ['label' => trans('user.phone'),'value' => 'users.phone', 'type' => 'text', 'table' => '', 'sort' => 'sort'];
      array_push($headers,$header);
      $header = ['label' => trans('user.updated_at'),'value' => 'users.updated_at', 'type' => 'text', 'table' => '', 'sort' => 'sort'];
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

      $records = User::whereRaw($search)
      ->orderByRaw($sort)
      ->skip(25 * $skip)
      ->take(25)
      ->get();

      if ($skip == 0) {
        $records_count = User::whereRaw($search)
        ->count();
      } else {
        $records_count = 0;
      }


      $datas = [];

      foreach ($records as $record  ) {
        $row = ['id' => $record->id,
                 'records' => [
                             ['text_align' => 'left', 'type' => 'text' , 'value' => $record->name],
                             ['text_align' => 'left', 'type' => 'text' , 'value' => $record->email],
                             ['text_align' => 'left', 'type' => 'text' , 'value' => $record->phone],
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

  // get form template for record
  public function getForm($cond="insert", $id = "")
  {
    $this->access_list = $this->access->getAccess($this->form_id, Session::get('user_access_id'));

    $data = [];
    $data_details = [];
    if ($id != "") {
      $data = User::where('users.id',$id)
      ->leftJoin('mt_user_access as mt_user_access', function($join) {
        $join->on('users.user_access_id', '=', 'mt_user_access.id');
      })
      ->first(['users.*','mt_user_access.name as user_access']);

      $data_details = Mt_password_category_user::where('mt_password_category_user.user_id',$id)
      ->leftJoin('mt_password_category as mt_password_category', function($join) {
        $join->on('mt_password_category_user.password_category_id', '=', 'mt_password_category.id');
      })
      ->get(['mt_password_category_user.id','mt_password_category_user.password_category_id','mt_password_category.name as password_category_name']);
    }

    $forms = [];
    $form = ['label' => 'Name','placeholder' => 'Name',
             'type'  => 'text','name' => 'name',
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'required' => 'true',
             'unique' => 'unique:users,name,'.$id,
             'value' => ($cond=="insert" ? '' : $data->name ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Email','placeholder' => 'Email',
             'type'  => 'text','name' => 'email',
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'required' => 'true',
             'value' => ($cond=="insert" ? '' : $data->email ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Password','placeholder' => 'Password',
             'type'  => 'password',
             'name' => 'password',
             'name_repeat' => 'password',
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'required' => 'true',
             'value' => ($cond=="insert" ? '' : $data->password ),
             'value_repeat' => ($cond=="insert" ? '' : $data->password ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Position','placeholder' => 'Position',
             'type'  => 'text','name' => 'position',
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'hide' => 'true',
             'value' => ($cond=="insert" ? '' : $data->position ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Print Limit (0 for unlimited)','placeholder' => 'Print Limit',
             'type'  => 'number','name' => 'print_limit',
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'value' => ($cond=="insert" ? 0 : $data->print_limit ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'User Access','placeholder' => 'User Access',
             'type'  => 'data',
             'name' => 'user_access',
             'id' => 'user_access_id',
             'table' => 'user-access',
             'value_id' => ($cond=="insert" ? '' : $data->user_access_id ),
             'value' => ($cond=="insert" ? '' : $data->user_access ),
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'required' => 'true',
           ];
    array_push($forms,$form);
    $form = ['label' => 'Address','placeholder' => 'Address',
             'type'  => 'text','name' => 'address',
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'required' => 'true',
             'value' => ($cond=="insert" ? '' : $data->address ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Phone','placeholder' => 'Phone',
             'type'  => 'text','name' => 'phone',
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'required' => 'true',
             'value' => ($cond=="insert" ? '' : $data->phone ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Background Color','placeholder' => 'Background Color',
             'type'  => 'text','name' => 'background_color',
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'hide' => 'true',
             'value' => ($cond=="insert" ? '' : $data->background_color ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Image Link','placeholder' => 'Image Link',
             'type'  => 'text','name' => 'image_link',
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'hide' => 'true',
             'value' => ($cond=="insert" ? '' : $data->image_link ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Password Category','placeholder' => 'Password Category',
             'type'  => 'table','name' => 'mt_password_category_user',
             'col' => ['row' => 12, 'col1' => 0, 'col2' => 12],
            //  'required' => 'true',
             'value' => ($cond=="insert" ? '' : '' ),
             'collapse' => false,
             'custom_button_flag' => false,
             'columns' => [
                          ['col_name' => 'id', 'header' => '', 'type' => 'hidden', 'hide'=> true],
                          ['col_name' => 'password_category_id', 'header' => 'Password Category ID', 'type' => 'text', 'hide'=> true],
                          ['col_name' => 'password_category_name', 'header' => 'Password Category Name', 'type' => 'data','table' => 'password-category', 'hide'=> false],
             ],
             'details' => $data_details,
             'deleted_details' => [],
           ];
    array_push($forms,$form);
    // $form = ['label' => 'Image Link','placeholder' => 'Image Link',
    //          'type'  => 'text','name' => 'Image Link',
    //          'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
    //          'required' => 'true',
    //          'value' => ($cond=="insert" ? '' : $data->image_link ),
    //        ];
    // array_push($forms,$form);




    $result = [
      'forms' =>  $forms,
      'form_id' => $this->form_id,
      'form_table' => 'user',
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
           $record = User::create($save_datas);

           $save_datas_detail = $this->validator->convertInputDetail($datas, 'user_id',$record->id);

           $record_detail = Mt_password_category_user::insert($save_datas_detail['mt_password_category_user_insert']);

           $audit = new Tr_audit;
           $audit->transaction_category = 'user';
           $audit->transaction_id = $record->id;
           $audit->status = 'insert';
           $audit->column = "";
           $audit->value_old = "";
           $audit->value_new = "";
           $audit->modified_user_id = $request->session()->get('user_id');
           $audit->save();

           DB::commit();
           $arr = array('code' => "200", 'status' => "OK", 'url' => '#/list/user');
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

      //  $data_before = User::where('users.id',$id)
      //  ->leftJoin('mt_user_access as mt_user_access', function($join) {
      //    $join->on('users.user_access_id', '=', 'mt_user_access.id');
      //  })
      //  ->first(['users.*','mt_user_access.name as user_access']);


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

         $save_datas = $this->validator->convertInputWithBefore($datas, $data_before);

         DB::beginTransaction();
         try {
           User::where('id',$id)
                    ->update($save_datas);

          $save_datas_detail = $this->validator->convertInputDetail($datas, 'user_id',$id);

          $record_detail_insert = Mt_password_category_user::insert($save_datas_detail['mt_password_category_user_insert']);

          foreach ($save_datas_detail['mt_password_category_user_update'] as $detail) {
            Mt_password_category_user::where('id',$detail['id'])
                     ->update($detail);
          }
          //----------------------------------------------------------------------------------
          foreach ($save_datas_detail['mt_password_category_user_delete'] as $detail) {
            Mt_password_category_user::where('id',$detail['id'])->delete();
          }

          if ($this->columns !== "") {
            $audit = new Tr_audit;
            $audit->transaction_category = 'user';
            $audit->transaction_id = $id;
            $audit->status = 'update';
            $audit->column = $this->columns;
            $audit->value_old = $this->valuebefore;
            $audit->value_new = $this->valueafter;
            $audit->modified_user_id = $request->session()->get('user_id');
            $audit->save();
          }

           DB::commit();
           $arr = array('code' => "200", 'status' => "OK",'url' => '#/list/user', 'tes' => json_encode($data_before));
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

     $record = User::find($id);
    //  if(isset($record)) {
    //    $record->delete_flag = true;
    //  }
    //  User::delete($id);

     DB::beginTransaction();
     try {
       $record->delete();

       DB::commit();
       $arr = array('code' => "200", 'status' => "OK",'url' => '#/list/user');
       return json_encode($arr);
     } catch(\Exception $e){
       DB::rollback();
       $arr = array('code' => "200", 'status' => "FAIL", 'msg' => $e->getMessage());
       return json_encode($arr);
     }

  }
}
