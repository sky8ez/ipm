<?php

namespace App\Http\Controllers\App\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Mt_password_category_user;
use App\Mt_password_list;
use App\Mt_user_access;
use App\Mt_user_access_detail;
use App\Mt_table_filter;
use Validator;
use DB;
use Response;
use App\Tr_audit;
use Illuminate\Support\Facades\Input;
use App\Repositories\ValidatorRepository;
use App\Repositories\AccessRepository;
use Session;

class UserAccessController extends Controller
{
  /**
   * Send back all datas as JSON
   *
   * @return Response
   */
  private $form_id = "USER-ACCESS";

  private $validator;
  private $access;
  private $access_list = [];


  public function __construct(ValidatorRepository $validator,AccessRepository $access)
 {
     $this->validator = $validator;
     $this->access = $access;
 }

 public function checkAccess($access)
 {
      if(($access == 'USER-ACCESS' || $access == 'USER' || $access == 'GENERAL') &&  Session::get('role') == 'admin') {
         return json_encode(true);
      }

       $access_list = Mt_user_access_detail::where('user_access_id', Session::get('user_access_id'))
                                    ->where('module_id', $access)
                                    ->where('condition', 'nav')
                                    ->first();
       if(isset($access_list)) {
         if ($access_list->cond_flag == false) {
           return json_encode(false);
         } else {
           return json_encode(true);
         }
       }

     return json_encode(false);
 }

 public function checkAccessPassword($access,$id)
 {
      // if(($access == 'USER-ACCESS' || $access == 'USER' || $access == 'GENERAL') &&  Session::get('role') == 'admin') {
      //    return json_encode(true);
      // }
      //
      //  $access_list = Mt_user_access_detail::where('user_access_id', Session::get('user_access_id'))
      //                               ->where('module_id', $access)
      //                               ->where('condition', 'nav')
      //                               ->first();
      //  if(isset($access_list)) {
      //    if ($access_list->cond_flag == false) {
      //      return json_encode(false);
      //    } else {
      //      return json_encode(true);
      //    }
      //  }

       if($access == "PASSWORD-LIST") {
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

           $records = Mt_password_list::whereRaw("1=1".$pass_cat)
            ->where('id',$id)->get();

            if (count($records) > 0) {
              return json_encode(true);
            }  else {
              return json_encode(false);
            }

       }

     return json_encode(false);
 }


  public function index($skip = 0)
  {
    $this->access_list = $this->access->getAccess($this->form_id, Session::get('user_access_id'));

      $search = "1=1";
      $sort = "id desc";
      $filters = Mt_table_filter::where('form_id',$this->form_id)
                                ->get();

      $headers = [];
      $header = ['label' => trans('user_access.name'),'value' => 'mt_user_access.name', 'type' => 'text', 'table' => '', 'sort' => 'sort'];
      array_push($headers,$header);
      $header = ['label' => trans('user_access.email'),'role' => 'mt_user_access.role', 'type' => 'text', 'table' => '', 'sort' => 'sort'];
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

      $records = Mt_user_access::whereRaw($search)
      ->where('delete_flag',false)
      ->orderByRaw($sort)
      ->skip(25 * $skip)
      ->take(25)
      ->get();

      if ($skip == 0) {
        $records_count = Mt_user_access::whereRaw($search)
        ->where('delete_flag',false)
        ->count();
      } else {
        $records_count = 0;
      }


      $datas = [];

      foreach ($records as $record  ) {
        $row = ['id' => $record->id,
                 'records' => [
                             ['text_align' => 'left', 'type' => 'text' , 'value' => $record->name],
                             ['text_align' => 'left', 'type' => 'text' , 'value' => $record->role],
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
    $data_detail = [];
    if ($id != "") {
      $data = Mt_user_access::where('mt_user_access.id',$id)
      ->first(['mt_user_access.*']);

      // $data_detail = Mt_user_access_detail::where('mt_user_access_detail.user_access_id',$id)
      // ->where('mt_user_access_detail.delete_flag',false)
      // ->get(['mt_user_access_detail.*']);
    }

    $forms = [];
    $form = ['label' => 'Name','placeholder' => 'Name',
             'type'  => 'text','name' => 'name',
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'required' => 'true',
             'unique' => 'unique:mt_user_access,name,'.$id,
             'value' => ($cond=="insert" ? '' : $data->name ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Role','placeholder' => 'Role',
             'type'  => 'select','name' => 'role',
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'options' => ['admin','user'],
             'required' => 'true',
             'value' => ($cond=="insert" ? '' : $data->role ),
           ];
    array_push($forms,$form);

    $menu_detail = [
      ['id' => 'PASSWORD','name' => 'Password','insert' => false,'update' =>false,'delete' =>false,'detail' => false,'print' => false,'nav' => false, 'report' => false],
      ['id' => 'PASSWORD-CATEGORY','name' => 'Password Category','insert' => false,'update' =>false,'delete' =>false,'detail' => false,'print' => false,'nav' => false, 'report' => false],
      ['id' => 'PASSWORD-LIST','name' => 'Password List','insert' => false,'update' =>false,'delete' =>false,'detail' => false,'print' => false,'nav' => false, 'report' => false],
      ['id' => 'ACTIVITY','name' => 'Activity Log','insert' => false,'update' =>false,'delete' =>false,'detail' => false,'print' => false,'nav' => false, 'report' => false],
    ];

    for ($i=0;$i< count($menu_detail); $i++) {
      $data_detail = Mt_user_access_detail::where('mt_user_access_detail.user_access_id',$id)
      ->where('mt_user_access_detail.module_id',$menu_detail[$i]['id'])
      ->where('mt_user_access_detail.delete_flag',false)
      ->get(['mt_user_access_detail.*']);
      if (isset($data_detail)) {
        foreach ($data_detail as $data_detail1) {
          switch ($data_detail1->condition) {
            case 'insert':
              $menu_detail[$i]['insert'] = ($data_detail1->cond_flag == 1 ? true : false);
              break;
            case 'update':
              $menu_detail[$i]['update'] = ($data_detail1->cond_flag == 1 ? true : false);
              break;
            case 'delete':
              $menu_detail[$i]['delete'] = ($data_detail1->cond_flag == 1 ? true : false);
            break;
            case 'detail':
              $menu_detail[$i]['detail'] = ($data_detail1->cond_flag == 1 ? true : false);
            break;
            case 'print':
              $menu_detail[$i]['print'] = ($data_detail1->cond_flag == 1 ? true : false);
            break;
            case 'nav':
              $menu_detail[$i]['nav'] = ($data_detail1->cond_flag == 1 ? true : false);
            break;

            default:
              # code...
              break;
          }
        }

      }
    }

    $form = ['label' => 'Menu Access','placeholder' => 'Menu Access',
             'type'  => 'access-menu','table' => 'mt_user_access_detail',
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
            //  'required' => 'true',
             'menu_detail' => $menu_detail,
            //  'value' => ($cond=="insert" ? '' : $data->data ),
           ];
    array_push($forms,$form);

    $result = [
      'forms' =>  $forms,
      'form_id' => $this->form_id,
      'form_table' => 'user-access',
      'data_before' => $data,
      'access_list' => $this->access_list,
      'role' => Session::get('role'),
    ];

    return Response::json($result);
  }

  /**
   * Store a newly created resource in storage.
   *
   * @return Response
   */
  public function store(Request $request)
  {
       $datas = $request->data;
       $data_detail = [];
       $save_datas = [];

       $rules = $this->validator->getValidator($datas);

       $validator = Validator::make($request->all(), $rules);

       if ($validator->passes()) {
         $save_datas = $this->validator->convertInput($datas);

         $data_detail =  $this->validator->getUserAccess($datas);


         DB::beginTransaction();
         try {
           $record = Mt_user_access::create($save_datas);

           foreach ($data_detail['value'] as $detail) {
             $this->updateAccessMenu('insert',$detail, $record->id);
             $this->updateAccessMenu('update',$detail, $record->id);
             $this->updateAccessMenu('delete',$detail, $record->id);
             $this->updateAccessMenu('detail',$detail, $record->id);
             $this->updateAccessMenu('print',$detail, $record->id);
             $this->updateAccessMenu('nav',$detail, $record->id);


           }

           DB::commit();
           $arr = array('code' => "200", 'status' => "OK", 'url' => '#/list/user-access');
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
       $data_detail = [];
       $rules = $this->validator->getValidator($datas);

       $validator = Validator::make($request->all(), $rules);

       if ($validator->passes()) {
         $save_datas = $this->validator->convertInput($datas);

          $data_detail =  $this->validator->getUserAccess($datas);

         DB::beginTransaction();
         try {
           Mt_user_access::where('id',$id)
                    ->update($save_datas);

          foreach ($data_detail['value'] as $detail) {
            $this->updateAccessMenu('insert',$detail, $id);
            $this->updateAccessMenu('update',$detail, $id);
            $this->updateAccessMenu('delete',$detail, $id);
            $this->updateAccessMenu('detail',$detail, $id);
            $this->updateAccessMenu('print',$detail, $id);
            $this->updateAccessMenu('nav',$detail, $id);


          }

           DB::commit();
           $arr = array('code' => "200", 'status' => "OK",'url' => '#/list/user-access');
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

  private function updateAccessMenu($condition, $detail, $id) {
    $dtdetail = Mt_user_access_detail::where('mt_user_access_detail.user_access_id',$id)
    ->where('module_id',$detail['id'])
    ->where('module_name',$detail['name'])
    ->where('condition',$condition)
    ->first();

    if (isset($dtdetail)) { //update
      DB::table('mt_user_access_detail')
           ->where('id', $dtdetail->id)
           ->update(['cond_flag' => ($detail[$condition] == "true" ? 1 : 0) ]);
    } else {// insert
      DB::table('mt_user_access_detail')->insert(
         ['module_id' => $detail['id'],
          'module_name' => $detail['name'],
          'user_access_id' => $id,
          'condition' => $condition,
          'cond_flag' =>  ($detail[$condition] == "true" ? 1 : 0) ]
     );
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

     $record = Mt_user_access::find($id);
     if(isset($record)) {
       $record->delete_flag = true;
     }

     DB::beginTransaction();
     try {
       $record->update();

       DB::commit();
       $arr = array('code' => "200", 'status' => "OK",'url' => '#/list/user-access');
       return json_encode($arr);
     } catch(\Exception $e){
       DB::rollback();
       $arr = array('code' => "200", 'status' => "FAIL", 'msg' => $e->getMessage());
       return json_encode($arr);
     }

  }
}
