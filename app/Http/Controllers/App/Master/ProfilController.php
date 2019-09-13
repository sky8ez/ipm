<?php

namespace App\Http\Controllers\App\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use Validator;
use DB;
use Response;
use Session;
use Illuminate\Support\Facades\Input;
use App\Repositories\ValidatorRepository;
use App\Repositories\AccessRepository;

class ProfilController extends Controller
{
  /**
   * Send back all datas as JSON
   *
   * @return Response
   */
  private $form_id = "PROFIL";

  private $validator;
  private $access;
  private $access_list = [];

  public function __construct(ValidatorRepository $validator,AccessRepository $access)
 {
     $this->validator = $validator;
     $this->access = $access;
 }

 // get form template for customer
 public function getForm($cond="insert", $id = "")
 {
   $this->access_list = $this->access->getAccess($this->form_id, Session::get('user_access_id'));

   $data = [];
   if ($id != "") {
     $data = User::where('users.id',$id)
     ->leftJoin('mt_user_access as mt_user_access', function($join) {
       $join->on('users.user_access_id', '=', 'mt_user_access.id');
     })
     ->first(['users.*','mt_user_access.name as user_access']);
   }

   $forms = [];
   $form = ['label' => 'Change Password','placeholder' => 'Password',
            'type'  => 'password',
            'name' => 'password',
            'name_repeat' => 'password',
            'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
            'required' => 'true',
            'value' => ($cond=="insert" ? '' : $data->password ),
            'value_repeat' => ($cond=="insert" ? '' : $data->password ),
          ];
   array_push($forms,$form);


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

   /**
    * Store a newly created resource in storage.
    *
    * @return Response
    */
   public function saveProfil(Request $request)
   {
     $datas = $request->data;
     $data_before = $request->data_before;
     $save_datas = [];

     $rules = $this->validator->getValidator($datas);

     $validator = Validator::make($request->all(), $rules);

     if ($validator->passes()) {
       $save_datas = $this->validator->convertInputWithBefore($datas, $data_before);

       DB::beginTransaction();
       try {
         User::where('id',$request->session()->get('user_id'))
                  ->update($save_datas);

         DB::commit();
         $arr = array('code' => "200", 'status' => "OK",'url' => '#/profile','msg' =>'Update Success', 'tes' => json_encode($data_before));
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

}
