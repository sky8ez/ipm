<?php

namespace App\Http\Controllers\App\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Mt_customer;
use App\Tr_member;
use App\Tr_payment;
use App\Mt_packet;
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

class PacketController extends Controller
{
  /**
   * Send back all datas as JSON
   *
   * @return Response
   */
  private $form_id = "PACKET";

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
      $header = ['label' => trans('packet.name'),'value' => 'mt_packet.name', 'type' => 'text', 'table' => '', 'sort' => 'sort'];
      array_push($headers,$header);
      $header = ['label' => trans('packet.price'),'value' => 'mt_packet.price', 'type' => 'currency', 'table' => '', 'sort' => 'sort'];
      array_push($headers,$header);
      $header = ['label' => trans('packet.weight'),'value' => 'mt_packet.weight', 'type' => 'text', 'table' => '', 'sort' => 'sort'];
      array_push($headers,$header);
      $header = ['label' => trans('packet.updated_at'),'value' => 'mt_packet.updated_at', 'type' => 'text', 'table' => '', 'sort' => 'sort'];
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

      $customers = Mt_packet::whereRaw($search)
      ->where('delete_flag',false)
      ->orderByRaw($sort)
      ->skip(25 * $skip)
      ->take(25)
      ->get();

      if ($skip == 0) {
        $customers_count = Mt_packet::whereRaw($search)
        ->where('delete_flag',false)
        ->count();
      } else {
        $customers_count = 0;
      }


      $datas = [];

      foreach ($customers as $customer  ) {
        $row = ['id' => $customer->id,
                 'records' => [
                             ['text_align' => 'left', 'type' => 'text' , 'value' => $customer->name],
                             ['text_align' => 'left', 'type' => 'currency' , 'value' => $customer->price],
                             ['text_align' => 'left', 'type' => 'text' , 'value' => $customer->weight],
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
      $data = Mt_packet::where('mt_packet.id',$id)
      ->first(['mt_packet.*']);
    }

    $forms = [];
    $form = ['label' => 'Name','placeholder' => 'Name',
             'type'  => 'text','name' => 'name',
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'required' => 'true',
             'unique' => 'unique:mt_packet,name,'.$id,
             'value' => ($cond=="insert" ? '' : $data->name ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Price','placeholder' => 'Price',
             'type'  => 'currency','name' => 'price',
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'required' => 'true',
             'value' => ($cond=="insert" ? '' : $data->price ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Weight (KG)','placeholder' => 'Weight',
             'type'  => 'number','name' => 'weight',
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'required' => 'true',
             'value' => ($cond=="insert" ? '' : $data->weight ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Duration (Days)','placeholder' => 'Duration',
             'type'  => 'number','name' => 'duration',
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'required' => 'true',
             'value' => ($cond=="insert" ? '' : $data->duration ),
           ];
    array_push($forms,$form);

    $result = [
      'forms' =>  $forms,
      'form_id' => $this->form_id,
      'form_table' => 'packet',
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
           $record = Mt_packet::create($save_datas);

           $audit = new Tr_audit;
           $audit->transaction_category = 'packet';
           $audit->transaction_id = $record->id;
           $audit->status = 'insert';
           $audit->column = "";
           $audit->value_old = "";
           $audit->value_new = "";
           $audit->modified_user_id = $request->session()->get('user_id');
           $audit->save();

           DB::commit();
           $arr = array('code' => "200", 'status' => "OK", 'url' => '#/list/packet');
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
           Mt_packet::where('id',$id)
                    ->update($save_datas);

            if ($this->columns !== "") {
              $audit = new Tr_audit;
              $audit->transaction_category = 'packet';
              $audit->transaction_id = $id;
              $audit->status = 'update';
              $audit->column = $this->columns;
              $audit->value_old = $this->valuebefore;
              $audit->value_new = $this->valueafter;
              $audit->modified_user_id = $request->session()->get('user_id');
              $audit->save();
            }

           DB::commit();
           $arr = array('code' => "200", 'status' => "OK",'url' => '#/list/packet');
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

     $record = Mt_packet::find($id);
     if(isset($record)) {
       $record->delete_flag = true;
     }

     DB::beginTransaction();
     try {
       $payment = Tr_payment::where('packet_id',$id)
                       ->where('payment_status',true)
                       ->where('delete_flag',false)
                       ->count();

       if (isset($payment)) {
         if ($payment > 0) {
           $error = 'Tidak dapat menghapus paket karena sudah digunakan dalam transaksi';
           throw new Exception($error);
         }
       }

       $member = Tr_member::where('packet_id',$id)
                       ->where('member_status',true)
                       ->where('delete_flag',false)
                       ->count();

       if (isset($member)) {
         if ($member > 0) {
           $error = 'Tidak dapat menghapus paket karena sudah digunakan dalam member';
           throw new Exception($error);
         }
       }

       $record->update();

       DB::commit();
       $arr = array('code' => "200", 'status' => "OK",'url' => '#/list/packet');
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
             $payment = Tr_payment::where('packet_id',$data)
                             ->where('payment_status',true)
                             ->where('delete_flag',false)
                             ->count();

             if (isset($payment)) {
               if ($payment > 0) {
                 $error = 'Tidak dapat menghapus paket karena sudah digunakan dalam transaksi';
                 throw new Exception($error);
               }
             }

             $member = Tr_member::where('packet_id',$data)
                             ->where('member_status',true)
                             ->where('delete_flag',false)
                             ->count();

             if (isset($member)) {
               if ($member > 0) {
                 $error = 'Tidak dapat menghapus paket karena sudah digunakan dalam member';
                 throw new Exception($error);
               }
             }

            $record->delete_flag = true;
            $record->update();
         }
       }

       DB::commit();
       $arr = array('code' => "200", 'status' => "OK",'url' => '#/list/packet');
       return json_encode($arr);
     } catch(\Exception $e){
       DB::rollback();
       $arr = array('code' => "200", 'status' => "FAIL", 'msg' => $e->getMessage());
       return json_encode($arr);
     }
  }

}
