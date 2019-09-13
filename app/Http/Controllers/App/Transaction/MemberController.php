<?php

namespace App\Http\Controllers\App\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Tr_member;
use App\Tr_payment;
use App\Tr_closing_day;
use App\Mt_table_filter;
use Validator;
use DB;
use Response;
use App\Tr_audit;
use Illuminate\Support\Facades\Input;
use App\Repositories\ValidatorRepository;
use App\Repositories\AccessRepository;
use Session;
use Exception;


class MemberController extends Controller
{
  /**
   * Send back all datas as JSON
   *
   * @return Response
   */
  private $form_id = "MEMBER";

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
      // $header = ['label' => trans('member.status'),'value' => 'tr_member.status', 'type' => 'text', 'table' => '', 'sort' => 'sort'];
      // array_push($headers,$header);
      $header = ['label' => trans('member.member_no'),'value' => 'tr_member.transaction_no', 'type' => 'data', 'table' => 'member', 'sort' => 'sort'];
      array_push($headers,$header);
      $header = ['label' => trans('member.customer'),'value' => 'mt_customer.name', 'type' => 'text', 'table' => '', 'sort' => 'sort'];
      array_push($headers,$header);
      $header = ['label' => trans('member.packet'),'value' => 'mt_packet.name', 'type' => 'data', 'table' => 'packet', 'sort' => 'sort'];
      array_push($headers,$header);
      $header = ['label' => trans('member.remaining'),'value' => '(tr_member.total_kg - IFNULL(v.quantity_kg,0))', 'type' => 'text', 'table' => '', 'sort' => 'sort'];
      array_push($headers,$header);
      $header = ['label' => trans('member.used'),'value' => '(IFNULL(v.quantity_kg,0))', 'type' => 'text', 'table' => '', 'sort' => 'sort'];
      array_push($headers,$header);
      $header = ['label' => trans('member.expired_date'),'value' => 'tr_member.expired_date', 'type' => 'text', 'table' => '', 'sort' => 'sort'];
      array_push($headers,$header);
      $header = ['label' => trans('member.disable_flag'),'value' => 'tr_member.disable_flag', 'type' => 'boolean', 'table' => '', 'sort' => 'sort'];
      array_push($headers,$header);
      $header = ['label' => trans('member.updated_at'),'value' => 'tr_member.updated_at', 'type' => 'datetime', 'table' => '', 'sort' => 'sort'];
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


      $records =Tr_member::whereRaw($search)
      ->leftJoin('mt_customer as mt_customer', function($join) {
        $join->on('tr_member.customer_id', '=', 'mt_customer.id');
      })
      ->leftJoin('mt_packet as mt_packet', function($join) {
        $join->on('tr_member.packet_id', '=', 'mt_packet.id');
      })
      ->leftJoin(
         DB::raw("(SELECT member_id, payment_status, SUM(quantity_kg) AS quantity_kg FROM tr_payment where transaction_type = 'member'  GROUP BY member_id, payment_status) as v"), function($join) {
           $join->on('v.member_id', '=', 'tr_member.id')
                ->on('v.payment_status', '=', DB::raw('true'));
         })
      ->where('tr_member.delete_flag',false)
      ->where('tr_member.member_status',true)
       ->select('tr_member.*',
              'mt_customer.name as customer','mt_packet.name as packet',
                DB::raw('tr_member.total_kg - IFNULL(v.quantity_kg,0) as quantity_kg'),
               DB::raw('IFNULL(v.quantity_kg,0) as used')
               )
      ->orderByRaw($sort)
      ->skip(25 * $skip)
      ->take(25)
      ->get();

      $records_count = 0;
      if ($skip == 0) {
        // $records_count = Tr_member::whereRaw($search)
        // ->where('tr_member.delete_flag',false)
        // ->where('tr_member.member_status',true)
        // ->count();
      } else {
        $records_count = 0;
      }


      $datas = [];

      foreach ($records as $record  ) {
        $row = ['id' => $record->id,
                 'records' => [
                             ['text_align' => 'left', 'type' => 'text' , 'value' => $record->member_no],
                             ['text_align' => 'left', 'type' => 'text' , 'value' => $record->customer],
                             ['text_align' => 'left', 'type' => 'text' , 'value' => $record->packet],
                             ['text_align' => 'left', 'type' => 'text' , 'value' => $record->quantity_kg." KG"],
                             ['text_align' => 'left', 'type' => 'text' , 'value' => $record->used." KG"],
                             ['text_align' => 'left', 'type' => 'datetime' , 'value' => date_format(date_create($record->expired_date),"d M Y")],
                             ['text_align' => 'left', 'type' => 'boolean' , 'value' => ($record->disable_flag == true ? false : true)],
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

    $prev_id = "";
    $next_id = "";

    $data = [];
    if ($id != "") {
      $data = Tr_member::where('tr_member.id',$id)
      ->leftJoin('mt_customer as mt_customer', function($join) {
        $join->on('tr_member.customer_id', '=', 'mt_customer.id');
      })
      ->leftJoin('mt_packet as mt_packet', function($join) {
        $join->on('tr_member.packet_id', '=', 'mt_packet.id');
      })
      ->leftJoin('tr_member as member_added', function($join) {
        $join->on('tr_member.member_added_id', '=', 'member_added.id');
      })
      ->first(['tr_member.*','member_added.member_no as member_added','tr_member.member_no as member','mt_customer.name as customer','mt_packet.name as packet']);

        if (isset($data)) {
          $prev_data = Tr_member::where('tr_member.delete_flag',false)
          ->where('tr_member.member_no','<',$data->member_no)
          ->orderBy('tr_member.member_no','desc')
          ->first(['id']);
          if (isset($prev_data)) { $prev_id = $prev_data->id ;}

          $next_data = Tr_member::where('tr_member.delete_flag',false)
          ->where('tr_member.member_no','>',$data->member_no)
          ->orderBy('tr_member.member_no','asc')
          ->first(['id']);
          if (isset($next_data)) { $next_id = $next_data->id ;}

        }

    }

    $forms = [];
    $form = ['label' => 'Customer','placeholder' => 'Customer',
             'type'  => 'data',
             'name' => 'customer',
             'id' => 'customer_id',
             'read_only' => ($cond=="insert" ? false : true ),
             'table' => 'customer',
             'value_id' => ($cond=="insert" ? '' : $data->customer_id ),
             'value' => ($cond=="insert" ? '' : $data->customer ),
             'col' => ['row' => 6, 'col1' => 4, 'col2' => 8],
             'required' => 'true',
           ];
    array_push($forms,$form);
    $form = ['label' => 'No Member','placeholder' => 'No Member',
             'type'  => 'data',
             'name' => 'member',
             'id' => 'id',
             'read_only' => ($cond=="insert" ? false : true ),
             'table' => 'member',
             'value_id' => ($cond=="insert" ? '' : $data->id ),
             'value' => ($cond=="insert" ? '' : $data->member_no ),
             'col' => ['row' => 6, 'col1' => 4, 'col2' => 8],
             'required' => 'true',
             'cond' =>  ' and member_status = 0 ',
           ];
    array_push($forms,$form);
    $form = ['label' => 'Member Added','placeholder' => 'Member Added',
             'type'  => 'data',
             'name' => 'member_added',
             'read_only' => ($cond=="insert" ? false : true ),
             'id' => 'member_added_id',
             'table' => 'member',
             'cond' =>  ' and member_status = 1 and disable_flag = 0',
             'value_id' => ($cond=="insert" ? '' : $data->member_added_id ),
             'value' => ($cond=="insert" ? '' : $data->member_added ),
             'col' => ['row' => 6, 'col1' => 4, 'col2' => 8],
           ];
    array_push($forms,$form);
    $form = ['label' => 'Packet','placeholder' => 'Packet',
             'type'  => 'data',
             'name' => 'packet',
             'read_only' => ($cond=="insert" ? false : true ),
             'id' => 'packet_id',
             'table' => 'packet',
             'value_id' => ($cond=="insert" ? '' : $data->packet_id ),
             'value' => ($cond=="insert" ? '' : $data->packet ),
             'col' => ['row' => 6, 'col1' => 4, 'col2' => 8],
             'required' => 'true',
           ];
    array_push($forms,$form);
    $form = ['label' => 'Start Date','placeholder' => 'Start Date',
             'type'  => 'datetime','name' => 'start_date',
             'col' => ['row' => 6, 'col1' => 4, 'col2' => 8],
             'required' => 'true',
             'read_only' => 'true',
             'disable' => 'false',
             'value' => ($cond=="insert" ? '' : \Carbon\Carbon::parse($data->start_date)->format('d/m/Y')),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Added Duration','placeholder' => 'Added Duration',
             'type'  => 'number','name' => 'added_duration',
             'col' => ['row' => 6, 'col1' => 4, 'col2' => 8],
             'required' => 'true',
             'read_only' => 'true',
             'value' => ($cond=="insert" ? 0 : $data->added_duration ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Duration','placeholder' => 'Duration',
             'type'  => 'number','name' => 'duration',
             'col' => ['row' => 6, 'col1' => 4, 'col2' => 8],
             'required' => 'true',
             'read_only' => 'true',
             'value' => ($cond=="insert" ? '' : $data->duration ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Expired Date','placeholder' => 'Expired Date',
             'type'  => 'datetime','name' => 'expired_date',
             'col' => ['row' => 6, 'col1' => 4, 'col2' => 8],
            //  'required' => 'true',
             'read_only' => 'true',
             'disable' => 'true',
             'value' => ($cond=="insert" ? '' : \Carbon\Carbon::parse($data->expired_date)->format('d/m/Y') ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Custom Kg','placeholder' => 'Custom Kg',
             'type'  => 'currency','name' => 'custom_kg',
             'col' => ['row' => 6, 'col1' => 4, 'col2' => 8],
             'required' => 'true',
             'value' => ($cond=="insert" ? 0 : $data->custom_kg ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Member Added Kg','placeholder' => 'Member Added Kg',
             'type'  => 'currency','name' => 'member_added_kg',
             'col' => ['row' => 6, 'col1' => 4, 'col2' => 8],
             'required' => 'true',
             'read_only' => 'true',
             'value' => ($cond=="insert" ? 0 : $data->member_added_kg ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Member Kg','placeholder' => 'Member Kg',
             'type'  => 'currency','name' => 'member_kg',
             'col' => ['row' => 6, 'col1' => 4, 'col2' => 8],
             'required' => 'true',
             'read_only' => 'true',
             'value' => ($cond=="insert" ? 0 : $data->member_kg ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Total Kg','placeholder' => 'Total Kg',
             'type'  => 'currency','name' => 'total_kg',
             'col' => ['row' => 6, 'col1' => 4, 'col2' => 8],
             'required' => 'true',
             'read_only' => 'true',
             'value' => ($cond=="insert" ? 0 : $data->total_kg ),
           ];
    array_push($forms,$form);

    $result = [
      'forms' =>  $forms,
      'form_id' => $this->form_id,
      'form_table' => 'member',
      'data_before' => $data,
      'access_list' => $this->access_list,
      'role' => Session::get('role'),
      'prev_id' => ($prev_id == '' ? '' : '#/form/member/'.$prev_id),
      'next_id' => ($next_id == '' ? '' : '#/form/member/'.$next_id),
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
         $save_datas['member_status'] = true;
         $id = $datas[1]['value_id'];

         DB::beginTransaction();
         try {

           //jika member_added_id ada isi maka disablenya di true kan
           if ($save_datas['member_added_id'] !== '' && $save_datas['member_added_id'] !== 0) {
              DB::table('tr_member')
              ->where('id', $save_datas['member_added_id'])
              ->update(['disable_flag' => true]);
           }

           Tr_member::where('id',$id)
                    ->update($save_datas);

           $audit = new Tr_audit;
           $audit->transaction_category = 'member';
           $audit->transaction_id = $id;
           $audit->status = 'insert';
           $audit->column = "";
           $audit->value_old = "";
           $audit->value_new = "";
           $audit->modified_user_id = $request->session()->get('user_id');
           $audit->save();

           DB::commit();
             $arr = array('code' => "200", 'status' => "OK", 'url' => '#/list/member');
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
           //jika member_added_id ada isi maka disablenya di true kan
           if ($data_before['member_added_id'] !== $save_datas['member_added_id']) {
             DB::table('tr_member')
            ->where('id', $data_before['member_added_id'])
            ->update(['disable_flag' => false]);
             if ($save_datas['member_added_id'] !== '' && $save_datas['member_added_id'] !== 0) {
                DB::table('tr_member')
                ->where('id', $save_datas['member_added_id'])
                ->update(['disable_flag' => true]);
             }
           }

          //  if ($save_datas['disable_flag'] == true) {
          //    $error = 'Tidak dapat menghapus member karena sudah terdisable';
          //    throw new Exception($error);
          //  }

           Tr_member::where('id',$id)
                    ->update($save_datas);

          if ($this->columns !== "") {
            $audit = new Tr_audit;
            $audit->transaction_category = 'member';
            $audit->transaction_id = $id;
            $audit->status = 'update';
            $audit->column = $this->columns;
            $audit->value_old = $this->valuebefore;
            $audit->value_new = $this->valueafter;
            $audit->modified_user_id = $request->session()->get('user_id');
            $audit->save();
          }

           DB::commit();
           $arr = array('code' => "200", 'status' => "OK",'url' => '#/list/member');
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

     $record = Tr_member::find($id);
     if(isset($record)) {
       $record->member_status = false;
     }

     DB::beginTransaction();
     try {
       if ($record->member_added_id !== '' && $record->member_added_id !== 0) {
          DB::table('tr_member')
          ->where('id', $record->member_added_id)
          ->update(['disable_flag' => false]);
       }

       $payment = Tr_payment::where('member_id',$id)
                      //  ->where('transaction_type','member')
                       ->where('payment_status',true)
                       ->where('delete_flag',false)
                       ->count();

      // echo $payment;
       if (isset($payment)) {
         if ($payment > 0) {
           $error = 'Tidak dapat menghapus member karena sudah digunakan dalam transaksi';
           throw new Exception($error);
         }
       }

       if ($record->disable_flag == true) {
         $error = 'Tidak dapat menghapus member karena sudah terdisable';
         throw new Exception($error);
       }


      $record->update();

       DB::commit();
       $arr = array('code' => "200", 'status' => "OK",'url' => '#/list/member');
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
         $record = Tr_member::find($data);
         if(isset($record)) {
           $payment = Tr_payment::where('member_id',$data)
                          //  ->where('transaction_type','member')
                           ->where('payment_status',true)
                           ->where('delete_flag',false)
                           ->count();

           if (isset($payment)) {
             if ($payment > 0) {
               $error = 'Tidak dapat menghapus member karena sudah digunakan dalam transaksi';
               throw new Exception($error);
             }
           }
           if ($record->disable_flag == true) {
             $error = 'Tidak dapat menghapus member karena sudah terdisable';
             throw new Exception($error);
           }

            $record->member_status = false;
            $record->update();
         }
       }

       DB::commit();
       $arr = array('code' => "200", 'status' => "OK",'url' => '#/list/member');
       return json_encode($arr);
     } catch(\Exception $e){
       DB::rollback();
       $arr = array('code' => "200", 'status' => "FAIL", 'msg' => $e->getMessage());
       return json_encode($arr);
     }
  }

}
