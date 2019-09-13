<?php

namespace App\Http\Controllers\App\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Mt_transaction_no;
use App\Mt_table_filter;
use App\Tr_payment;
use App\Tr_member;
use Validator;
use DB;
use Response;
use App\Tr_audit;
use Illuminate\Support\Facades\Input;
use App\Repositories\ValidatorRepository;
use App\Repositories\AccessRepository;
use Session;
use Exception;

class TransactionNoController extends Controller
{
  /**
   * Send back all datas as JSON
   *
   * @return Response
   */
  private $form_id = "TRANS_NO";

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
      $header = ['label' => trans('trans_no.type'),'value' => 'mt_transaction_no.type', 'type' => 'text', 'table' => '', 'sort' => 'sort'];
      array_push($headers,$header);
      $header = ['label' => trans('trans_no.no_from'),'value' => 'mt_transaction_no.no_from', 'type' => 'text', 'table' => '', 'sort' => 'sort'];
      array_push($headers,$header);
      $header = ['label' => trans('trans_no.no_to'),'value' => 'mt_transaction_no.no_to', 'type' => 'text', 'table' => '', 'sort' => 'sort'];
      array_push($headers,$header);
      $header = ['label' => trans('trans_no.updated_at'),'value' => 'mt_transaction_no.updated_at', 'type' => 'text', 'table' => '', 'sort' => 'sort'];
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

      $records =Mt_transaction_no::whereRaw($search)
      ->where('delete_flag',false)
      ->orderByRaw($sort)
      ->skip(25 * $skip)
      ->take(25)
      ->get();

      if ($skip == 0) {
        $records_count = Mt_transaction_no::whereRaw($search)
        ->where('delete_flag',false)
        ->count();
      } else {
        $records_count = 0;
      }


      $datas = [];

      foreach ($records as $record  ) {
        $row = ['id' => $record->id,
                 'records' => [
                             ['text_align' => 'left', 'type' => 'text' , 'value' => $record->type],
                             ['text_align' => 'left', 'type' => 'text' , 'value' => $record->no_from],
                             ['text_align' => 'left', 'type' => 'text' , 'value' => $record->no_to],
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
      $data = Mt_transaction_no::where('mt_transaction_no.id',$id)
      ->first(['mt_transaction_no.*']);
    }

    $forms = [];
    $form = ['label' => 'Type','placeholder' => 'Type',
             'type'  => 'select','name' => 'type',
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'required' => 'true',
             'options' => ['PAYMENT','MEMBER'],
             'value' => ($cond=="insert" ? '' : $data->type ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'No From','placeholder' => 'No From',
             'type'  => 'text','name' => 'no_from',
             'col' => ['row' => 6, 'col1' => 4, 'col2' => 8],
             'required' => 'true',
             'value' => ($cond=="insert" ? '' : $data->no_from ),
           ];
    array_push($forms,$form);

    $form = ['label' => 'No To','placeholder' => 'No To',
             'type'  => 'text','name' => 'no_to',
             'col' => ['row' => 6, 'col1' => 4, 'col2' => 8],
             'required' => 'true',
             'value' => ($cond=="insert" ? '' : $data->no_to ),
           ];
    array_push($forms,$form);


    $result = [
      'forms' =>  $forms,
      'form_id' => $this->form_id,
      'form_table' => 'transaction-no',
      'data_before' => $data,
      'access_list' => $this->access_list,
      'role' => Session::get('role'),
    ];

    return Response::json($result);
  }

  private function addDigit($no, $digit) {
    $result = $no;
    if (strlen($no) == $digit) {
      //sudah sama diabaikan
    } else {
      for ($i=strlen($no);$i< $digit;$i++) {
        $result = "0".$result;
      }
    }
    return $result;
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
           $record = Mt_transaction_no::create($save_datas);

           $type = $save_datas['type'];
           $no_from = $save_datas['no_from'];
           $no_to = $save_datas['no_to'];
           $digit = strlen($no_to);

           if (is_numeric($no_from) == false) {
             $error = 'No Sampai harus angka';
             throw new Exception($error);
           }

           if (is_numeric($no_to) == false) {
             $error = 'No Dari harus angka';
             throw new Exception($error);
           }

          if ($no_to < $no_from) {
            $error = 'No Sampai harus lebih besar dari No Dari';
            throw new Exception($error);
          }

           if (strlen($no_from) !== strlen($no_to)) {
             $error = 'Digit No dari No Dari dan No Sampai Harus Sama';
             throw new Exception($error);
           }


           if ($type == 'PAYMENT') {
             for($i=$no_from;$i<=$no_to;$i++) {
               DB::table('tr_payment')->insert(
                      ['transaction_date' => \Carbon\Carbon::parse('01/01/1990')->format('Y-m-d'),
                      'transaction_no' => $this->addDigit($i,$digit),
                      'payment_status' => false,
                      'payment_type' => 'cash',
                      'transaction_type' => 'laundry',
                      'member_id' => 0,
                      'packet_id' => 0,
                      'customer_id' => 0,
                      'quantity_kg' => 0,
                      'price_kg' => 0,
                      'total_kg' => 0,
                      'quantity_one' => 0,
                      'total_one' => 0,
                      'packet_price' => 0,
                      'discount' => 0,
                      'total' => 0,
                      'beginning_kg' => 0,
                      'end_kg' => 0,
                      'description' => '',
                      'trans_no_id' => $record->id,
                     ]
                  );
             }
           } else { //MEMBER
             for($i=$no_from;$i<=$no_to;$i++) {
               DB::table('tr_member')->insert(
                      ['member_no' => $this->addDigit($i,$digit),
                      'member_status' => false,
                      'customer_id' => 0 ,
                      'member_added_id' => 0,
                      'packet_id' => 0,
                      'start_date' => '1990-01-01',
                      'added_duration' => 0,
                      'duration' => 0,
                      'custom_kg' => 0,
                      'member_added_kg' => 0,
                      'member_kg' => 0,
                      'total_kg' => 0,
                      'expired_date' => '1990-01-01',
                      'trans_no_id' => $record->id,
                     ]
                  );
             }
           }


           $audit = new Tr_audit;
           $audit->transaction_category = 'transaction-no';
           $audit->transaction_id = $record->id;
           $audit->status = 'insert';
           $audit->column = "";
           $audit->value_old = "";
           $audit->value_new = "";
           $audit->modified_user_id = $request->session()->get('user_id');
           $audit->save();



           DB::commit();
             $arr = array('code' => "200", 'status' => "OK", 'url' => '#/list/transaction-no');
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
           Mt_transaction_no::where('id',$id)
                    ->update($save_datas);

          if ($this->columns !== "") {
            $audit = new Tr_audit;
            $audit->transaction_category = 'transaction-no';
            $audit->transaction_id = $id;
            $audit->status = 'update';
            $audit->column = $this->columns;
            $audit->value_old = $this->valuebefore;
            $audit->value_new = $this->valueafter;
            $audit->modified_user_id = $request->session()->get('user_id');
            $audit->save();
          }


           DB::commit();
           $arr = array('code' => "200", 'status' => "OK",'url' => '#/list/transaction-no');
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

     $record = Mt_transaction_no::find($id);
     if(isset($record)) {
       $record->delete_flag = true;
     }

     DB::beginTransaction();
     try {
       $record->update();

       switch ($record->type) {
         case 'PAYMENT':
           $data_count = Tr_payment::where('delete_flag',false)
                                  ->where('payment_status',true)
                                  ->where('trans_no_id',$record->id)
                                  ->count();
           if ($data_count > 0) { // terdapat yang sudah digunakan , tidak dapat dihapus
             $error = 'Transaksi sudah ada yang dibayar, tidak dapat dilakukan hapus no';
             throw new Exception($error);
           } else {
             //hapus banyak
             $datas = Tr_payment::where('trans_no_id',$record->id)
                                ->delete();
           }
           break;
         case 'MEMBER':
         $data_count = Tr_member::where('delete_flag',false)
                                ->where('member_status',true)
                                ->where('trans_no_id',$record->id)
                                ->count();
         if ($data_count > 0) { // terdapat yang sudah digunakan , tidak dapat dihapus
           $error = 'Member sudah ada yang digunakan, tidak dapat dilakukan hapus no';
           throw new Exception($error);
         } else {
           //hapus banyak
           $datas = Tr_member::where('trans_no_id',$record->id)
                              ->delete();
         }
           break;
         default:
           # code...
           break;
       }

       DB::commit();
       $arr = array('code' => "200", 'status' => "OK",'url' => '#/list/transaction-no');
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
         $record = Mt_transaction_no::find($data);
         if(isset($record)) {
           $record->delete_flag = true;
         }

         $record->update();

         switch ($record->type) {
           case 'PAYMENT':
             $data_count = Tr_payment::where('delete_flag',false)
                                    ->where('payment_status',true)
                                    ->where('trans_no_id',$record->id)
                                    ->count();
             if ($data_count > 0) { // terdapat yang sudah digunakan , tidak dapat dihapus
               $error = 'Transaksi sudah ada yang dibayar, tidak dapat dilakukan hapus no';
               throw new Exception($error);
             } else {
               //hapus banyak
               $datas = Tr_payment::where('trans_no_id',$record->id)
                                  ->delete();
             }
             break;
           case 'MEMBER':
           $data_count = Tr_member::where('delete_flag',false)
                                  ->where('member_status',true)
                                  ->where('trans_no_id',$record->id)
                                  ->count();
           if ($data_count > 0) { // terdapat yang sudah digunakan , tidak dapat dihapus
             $error = 'Member sudah ada yang digunakan, tidak dapat dilakukan hapus no';
             throw new Exception($error);
           } else {
             //hapus banyak
             $datas = Tr_member::where('trans_no_id',$record->id)
                                ->delete();
           }
             break;
           default:
             # code...
             break;
         }



       }

       DB::commit();
       $arr = array('code' => "200", 'status' => "OK",'url' => '#/list/transaction-no');
       return json_encode($arr);
     } catch(\Exception $e){
       DB::rollback();
       $arr = array('code' => "200", 'status' => "FAIL", 'msg' => $e->getMessage());
       return json_encode($arr);
     }
  }

}
