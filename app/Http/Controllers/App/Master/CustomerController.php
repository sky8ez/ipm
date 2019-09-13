<?php

namespace App\Http\Controllers\App\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Tr_member;
use App\Tr_payment;
use App\Mt_address;
use App\Mt_contact;
use App\Mt_customer;
use App\Mt_table_filter;
use Validator;
use DB;
use Response;
use Exception;
use Session;
use App\Tr_audit;
use Illuminate\Support\Facades\Input;
use App\Repositories\ValidatorRepository;
use App\Repositories\AccessRepository;

use App\Http\Requests;

class CustomerController extends Controller
{
  /**
   * Send back all datas as JSON
   *
   * @return Response
   */
  private $form_id = "A001";

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
      $header = ['label' => trans('customer.code'),'value' => 'mt_customer.code', 'type' => 'text', 'table' => '', 'sort' => 'sort'];
      array_push($headers,$header);
      $header = ['label' => trans('customer.name'),'value' => 'mt_customer.name', 'type' => 'text', 'table' => '', 'sort' => 'sort'];
      array_push($headers,$header);
      $header = ['label' => trans('customer.address'),'value' => 'mt_customer.address', 'type' => 'text', 'table' => '', 'sort' => 'sort'];
      array_push($headers,$header);
      $header = ['label' => trans('customer.phone'),'value' => 'mt_customer.phone', 'type' => 'text', 'table' => '', 'sort' => 'sort'];
      array_push($headers,$header);
      $header = ['label' => trans('customer.updated_at'),'value' => 'mt_customer.updated_at', 'type' => 'text', 'table' => '', 'sort' => 'sort'];
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

      $customers = Mt_customer::whereRaw($search)
      ->where('delete_flag',false)
      ->orderByRaw($sort)
      ->skip(25 * $skip)
      ->take(25)
      ->get();

      if ($skip == 0) {
        $customers_count = Mt_customer::whereRaw($search)
        ->where('delete_flag',false)
        ->count();
      } else {
        $customers_count = 0;
      }


      $datas = [];

      foreach ($customers as $customer  ) {
        $row = ['id' => $customer->id,
                 'records' => [
                             ['text_align' => 'left', 'type' => 'text' , 'value' => $customer->code],
                             ['text_align' => 'left', 'type' => 'text' , 'value' => $customer->name],
                             ['text_align' => 'left', 'type' => 'text' , 'value' => $customer->address],
                             ['text_align' => 'left', 'type' => 'text' , 'value' => $customer->phone],
                             ['text_align' => 'left', 'type' => 'datetime' , 'value' => (string)$customer->updated_at],
                   ]];
        array_push($datas, $row);
      }

      $result = [
        'headers' =>  $headers,
        'datas' =>  $datas,
        'count' =>  $customers_count,
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
      $data = Mt_customer::where('mt_customer.id',$id)
      ->first(['mt_customer.*']);
    }

    $forms = [];
    $form = ['label' => 'Customer Code','placeholder' => 'Customer Code',
             'type'  => 'text','name' => 'code',
             'col' => ['row' => 6, 'col1' => 4, 'col2' => 8],
             'required' => 'true',
             'unique' => 'unique:mt_customer,code,'.$id,
             'read_only' => ($cond=="insert" ? false : true ),
             'value' => ($cond=="insert" ? '' : $data->code ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Customer Type','placeholder' => 'Customer Type',
             'type'  => 'text','name' => 'company_type',
             'col' => ['row' => 6, 'col1' => 4, 'col2' => 8],
            //  'required' => 'true',
             'hide' => 'true',
             'value' => ($cond=="insert" ? '' : $data->company_type ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Customer Name','placeholder' => 'Customer Name',
             'type'  => 'text','name' => 'name',
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'required' => 'true',
             'unique' => 'unique:mt_customer,name,'.$id,
             'value' => ($cond=="insert" ? '' : $data->name ),
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

    $result = [
      'forms' =>  $forms,
      'form_id' => $this->form_id,
      'form_table' => 'customer',
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
           $record = Mt_customer::create($save_datas);

           $audit = new Tr_audit;
           $audit->transaction_category = 'customer';
           $audit->transaction_id = $record->id;
           $audit->status = 'insert';
           $audit->column = "";
           $audit->value_old = "";
           $audit->value_new = "";
           $audit->modified_user_id = $request->session()->get('user_id');
           $audit->save();

           DB::commit();
           $arr = array('code' => "200", 'status' => "OK", 'url' => '#/list/customer', 'value_search' => $save_datas['name'] );
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
           Mt_customer::where('id',$id)
                    ->update($save_datas);


          if ($this->columns !== "") {
            $audit = new Tr_audit;
            $audit->transaction_category = 'customer';
            $audit->transaction_id = $id;
            $audit->status = 'update';
            $audit->column = $this->columns;
            $audit->value_old = $this->valuebefore;
            $audit->value_new = $this->valueafter;
            $audit->modified_user_id = $request->session()->get('user_id');
            $audit->save();
          }

           DB::commit();
           $arr = array('code' => "200", 'status' => "OK",'url' => '#/list/customer');
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

     $record = Mt_customer::find($id);
     if(isset($record)) {
       $record->delete_flag = true;
     }

     DB::beginTransaction();
     try {
       $payment = Tr_payment::where('customer_id',$id)
                       ->where('payment_status',true)
                       ->where('delete_flag',false)
                       ->count();

       if (isset($payment)) {
         if ($payment > 0) {
           $error = 'Tidak dapat menghapus customer karena sudah digunakan dalam transaksi';
           throw new Exception($error);
         }
       }

       $member = Tr_member::where('customer_id',$id)
                       ->where('member_status',true)
                       ->where('delete_flag',false)
                       ->count();

       if (isset($member)) {
         if ($member > 0) {
           $error = 'Tidak dapat menghapus customer karena sudah digunakan dalam member';
           throw new Exception($error);
         }
       }

       $record->update();

       DB::commit();
       $arr = array('code' => "200", 'status' => "OK",'url' => '#/list/customer');
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
         $record = Mt_customer::find($data);
         if(isset($record)) {
             $payment = Tr_payment::where('customer_id',$data)
                             ->where('payment_status',true)
                             ->where('delete_flag',false)
                             ->count();

             if (isset($payment)) {
               if ($payment > 0) {
                 $error = 'Tidak dapat menghapus customer karena sudah digunakan dalam transaksi';
                 throw new Exception($error);
               }
             }

             $member = Tr_member::where('customer_id',$data)
                             ->where('member_status',true)
                             ->where('delete_flag',false)
                             ->count();

             if (isset($member)) {
               if ($member > 0) {
                 $error = 'Tidak dapat menghapus customer karena sudah digunakan dalam member';
                 throw new Exception($error);
               }
             }

            $record->delete_flag = true;
            $record->update();
         }
       }

       DB::commit();
       $arr = array('code' => "200", 'status' => "OK",'url' => '#/list/customer');
       return json_encode($arr);
     } catch(\Exception $e){
       DB::rollback();
       $arr = array('code' => "200", 'status' => "FAIL", 'msg' => $e->getMessage());
       return json_encode($arr);
     }
  }

}
