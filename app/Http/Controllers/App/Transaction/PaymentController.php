<?php

namespace App\Http\Controllers\App\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Tr_payment;
use App\Tr_member;
use App\Tr_payment_detail;
use App\Mt_packet;
use App\Mt_table_filter;
use App\Tr_closing_day;
use Validator;
use DB;
use Response;
use App\Tr_audit;
use Illuminate\Support\Facades\Input;
use App\Repositories\ValidatorRepository;
use App\Repositories\AccessRepository;
use Session;
use Exception;

class PaymentController extends Controller
{
  /**
   * Send back all datas as JSON
   *
   * @return Response
   */
  private $form_id = "PAYMENT";

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
      $header = ['label' => trans('payment.transaction_no'),'value' => 'tr_payment.transaction_no', 'type' => 'data', 'table' => 'payment-all', 'sort' => 'sort'];
      array_push($headers,$header);
      $header = ['label' => trans('payment.transaction_date'),'value' => 'tr_payment.transaction_date', 'type' => 'datetime', 'table' => '', 'sort' => 'sort'];
      array_push($headers,$header);
      $header = ['label' => trans('payment.payment_type'),'value' => 'tr_payment.payment_type', 'type' => 'select', 'options' => ['cash','debit'], 'sort' => 'sort'];
      array_push($headers,$header);
      $header = ['label' => trans('payment.transaction_type'),'value' => 'tr_payment.transaction_type', 'type' => 'select', 'options' => ['laundry','member','packet','extend-packet'], 'sort' => 'sort'];
      array_push($headers,$header);
      $header = ['label' => trans('payment.total'),'value' => 'tr_payment.total', 'type' => 'text', 'table' => '', 'sort' => 'sort'];
      array_push($headers,$header);
      $header = ['label' => trans('payment.rak'),'value' => 'mt_rak.name', 'type' => 'data', 'table' => 'rak', 'sort' => 'sort'];
      array_push($headers,$header);
      $header = ['label' => trans('payment.updated_at'),'value' => 'tr_payment.updated_at', 'type' => 'datetime', 'table' => '', 'sort' => 'sort'];
      array_push($headers,$header);


      foreach ($filters as $filter) {
        if ($filter->category == 'FILTER') {
          switch ($filter->filter) {
            case 'like':
              $search = $search." and ".$filter->column_name." ".$filter->filter." '%".$filter->value."%'";
              break;

            default:
                $search = $search." and ".$filter->column_name." ".$filter->filter." '".$filter->value."'";
              break;
          }

        } else { //SORT
          $sort = $filter->column_name." ".$filter->filter;
          $index = array_search($filter->column_name, array_column($headers, 'value'));
          if ($index !== false) {
            array_set($headers, $index.'.sort', $filter->filter);
          }
        }
      }

      $records = Tr_payment::whereRaw($search)
      ->leftJoin('mt_rak as mt_rak', function($join) {
        $join->on('tr_payment.rak_id', '=', 'mt_rak.id');
      })
      ->where('tr_payment.delete_flag',false)
      // ->where('tr_payment.payment_status',true)
      ->orderByRaw($sort)
      ->skip(25 * $skip)
      ->take(25)
      ->get(['tr_payment.id',
            'tr_payment.transaction_no',
            'tr_payment.payment_status',
            'tr_payment.transaction_date',
            'tr_payment.payment_type',
            'tr_payment.transaction_type',
            'tr_payment.total',
            'tr_payment.taken_flag',
            'tr_payment.updated_at',
            'mt_rak.name as rak']);

      $records_count = 0;
      if ($skip == 0) {
        //
        // $records_counts = Tr_payment::whereRaw($search)
        // ->leftJoin('mt_rak as mt_rak', function($join) {
        //   $join->on('tr_payment.rak_id', '=', 'mt_rak.id');
        // })
        // ->where('tr_payment.delete_flag',false)
        // // ->where('tr_payment.payment_status',true)
        // ->selectRaw('count(*) as count')->get();
        // if (isset($records_counts)) {
        //   foreach ($records_counts as $val) {
        //     $records_count = $val->count;
        //   }
        // }

      } else {
        $records_count = 0;
      }


      $datas = [];

      foreach ($records->chunk(25) as $chunk) {
        foreach ($chunk as $record  ) {
          $row = ['id' => $record->id,
                   'records' => [
                               ['text_align' => 'left', 'type' => 'text' , 'value' => $record->transaction_no],
                               ['text_align' => 'left', 'type' => 'text' , 'value' => ($record->payment_status == true ? date_format(date_create($record->transaction_date),"d M Y") : '')],
                               ['text_align' => 'left', 'type' => 'text' , 'value' => ($record->payment_status == true ?  $record->payment_type : '')],
                               ['text_align' => 'left', 'type' => 'text' , 'value' => ($record->payment_status == true ?  $record->transaction_type : '')],
                               ['text_align' => 'right', 'type' => 'currency' , 'value' => $record->total],
                               ['text_align' => 'left', 'type' => 'button', 'hide' => ($record->transaction_type == "packet" || $record->transaction_type == "extend-packet" ? true : false)
                                , 'link' => '#/form/payment-rak/'.$record->id , 'value' =>  ($record->taken_flag == 1 ? "Taken" : ( $record->rak == "" ? ""  :  $record->rak) ) ],
                               ['text_align' => 'left', 'type' => 'datetime' , 'value' => (string)$record->updated_at],
                     ]];
          array_push($datas, $row);
        }
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
    $last_payment_no_unused_id = "";
    $last_payment_no_unused = "";

    $prev_id = "";
    $next_id = "";

    $cat = "";
    $last_member_no_id = "";
    $last_member_no = "";
    if ($id == 'regular' || $id == 'member' || $id == 'new-member' || $id == 'extend-member') {
      $cat = $id;
      $id = "";
    }

    if ($id != "") {
      $data = Tr_payment::where('tr_payment.id',$id)
      ->leftJoin('mt_customer as mt_customer', function($join) {
        $join->on('tr_payment.customer_id', '=', 'mt_customer.id');
      })
      ->leftJoin('mt_packet as mt_packet', function($join) {
        $join->on('tr_payment.packet_id', '=', 'mt_packet.id');
      })
      ->leftJoin('tr_member as tr_member', function($join) {
        $join->on('tr_payment.member_id', '=', 'tr_member.id');
      })
      ->leftJoin('tr_member as added_member', function($join) {
        $join->on('tr_payment.added_member_id', '=', 'added_member.id');
      })
      ->leftJoin('mt_rak as mt_rak', function($join) {
        $join->on('tr_payment.rak_id', '=', 'mt_rak.id');
      })
      ->first(['tr_payment.*',
                  'mt_rak.name as rak',
                  'mt_customer.name as customer',
                  'mt_packet.name as packet',
                  'mt_packet.price as packet_price',
                  'tr_member.member_no as member',
                  'added_member.member_no as added_member',
                  'tr_payment.added_member_id as added_member_id'
                ]);

      $data_details = Tr_payment_detail::where('tr_payment_detail.payment_id',$id)
      ->get(['tr_payment_detail.*']);

      if (isset($data)) {
        $prev_data = Tr_payment::where('tr_payment.delete_flag',false)
        ->where('tr_payment.transaction_no','<',$data->transaction_no)
        ->orderBy('tr_payment.transaction_no','desc')
        ->first(['id']);
        if (isset($prev_data)) { $prev_id = $prev_data->id ;}

        $next_data = Tr_payment::where('tr_payment.delete_flag',false)
        ->where('tr_payment.transaction_no','>',$data->transaction_no)
        ->orderBy('tr_payment.transaction_no','asc')
        ->first(['id']);
        if (isset($next_data)) { $next_id = $next_data->id ;}
      }



    }

    if ($cond == "insert") {
      $data1 = Tr_payment::where('tr_payment.delete_flag',false)
      ->where('tr_payment.payment_status',false)
      ->orderBy('tr_payment.transaction_no','asc')
      ->first(['tr_payment.transaction_no','tr_payment.id']);

      if (isset($data1)) {
        $last_payment_no_unused = $data1->transaction_no;
        $last_payment_no_unused_id = $data1->id;
      }

      if ($cat !== "") {
        switch ($cat) {
          case 'new-member':
            $data1 = Tr_member::where('tr_member.delete_flag',false)
            ->where('tr_member.member_status',false)
            ->orderBy('tr_member.member_no','asc')
            ->first(['tr_member.member_no','tr_member.id']);

            if (isset($data1)) {
              $last_member_no = $data1->member_no;
              $last_member_no_id = $data1->id;
            }
            break;
          case 'extend-member':
            $data1 = Tr_member::where('tr_member.delete_flag',false)
            ->where('tr_member.member_status',false)
            ->orderBy('tr_member.member_no','asc')
            ->first(['tr_member.member_no','tr_member.id']);

            if (isset($data1)) {
              $last_member_no = $data1->member_no;
              $last_member_no_id = $data1->id;
            }
            break;
          default:
            # code...
            break;
        }
      }
    }

    $disable_date = Session::get('disable_date_payment');
    $get_last_no = Session::get('default_payment_no');
    if ($get_last_no == 'false') {
      $last_payment_no_unused_id = 0;
      $last_payment_no_unused = "";
    }

    $forms = [];
    $mytime = \Carbon\Carbon::now();
    $form = ['label' => 'Payment Date','placeholder' => 'Payment Date',
             'type'  => 'datetime','name' => 'transaction_date',
             'col' => ['row' => 6, 'col1' => 4, 'col2' => 8],
             'required' => 'true',
             'read_only' => $disable_date,
             'disable' => $disable_date,
             'value' => ($cond=="insert" ? $mytime->format('d/m/Y') : ($data->payment_status == true ? date_format(date_create($data->transaction_date),"d/m/Y")  : $mytime->format('d/m/Y'))   ),
           ];
    array_push($forms,$form);

    $form = ['label' => 'No Nota','placeholder' => 'No Nota',
             'type'  => 'data',
             'name' => 'transaction_no',
             'id' => 'id',
             'table' => 'payment',
             'value_id' => ($cond=="insert" ? $last_payment_no_unused_id : $data->transaction_no ),
             'value' => ($cond=="insert" ? $last_payment_no_unused : $data->transaction_no ),
             'read_only' => ($cond=="insert" ? false : true ),
             'col' => ['row' => 6, 'col1' => 4, 'col2' => 8],
             'required' => 'true',
           ];
    array_push($forms,$form);
    $form = ['label' => 'Payment Type','placeholder' => 'Payment Type',
             'type'  => 'select','name' => 'payment_type',
             'col' => ['row' => 6, 'col1' => 4, 'col2' => 8],
             'options' => ['cash','debit'],
             'required' => 'true',
             'value' => ($cond=="insert" ? 'cash' : $data->payment_type ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Transaction Type','placeholder' => 'Transaction Type',
             'type'  => 'select','name' => 'transaction_type',
             'col' => ['row' => 6, 'col1' => 4, 'col2' => 8],
             'options' => ['laundry','member','packet','extend-packet'],
             'required' => 'true',
             'value' => ($cond=="insert" ? 'laundry' : $data->transaction_type ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Packet','placeholder' => 'Packet',
             'type'  => 'data',
             'name' => 'packet',
             'id' => 'packet_id',
             'table' => 'packet',
             'value_id' => ($cond=="insert" ? '' : $data->packet_id ),
             'value' => ($cond=="insert" ? '' : $data->packet ),
              'col' => ['row' => 6, 'col1' => 4, 'col2' => 8],
             'required' => false,
             'hide' => 'true',
           ];
    array_push($forms,$form);
    $form = ['label' => 'Customer','placeholder' => 'Customer',
             'type'  => 'data',
             'name' => 'customer',
             'id' => 'customer_id',
             'table' => 'customer',
             'value_id' => ($cond=="insert" ? '' : $data->customer_id ),
             'value' => ($cond=="insert" ? '' : $data->customer ),
              'col' => ['row' => 6, 'col1' => 4, 'col2' => 8],
             'required' => false,
             'hide' => 'true',
           ];
    array_push($forms,$form);
    $form = ['label' => 'Member No','placeholder' => 'Member No',
             'type'  => 'data',
             'name' => 'member',
             'id' => 'member_id',
             'table' => 'member',
             'cond_1' =>  " and member_status = 1 and disable_flag = 0 and expired_date >= '".$mytime->format('Y-m-d')."' ",
             'cond_2' =>  " and member_status = 0",
             'cond' =>  " and member_status = 1 and disable_flag = 0 and expired_date >= '".$mytime->format('Y-m-d')."' ",
             'value_id' => ($cond=="insert" ? $last_member_no_id : $data->member_id ),
             'value' => ($cond=="insert" ? $last_member_no : $data->member ),
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'required' => false,
             'hide' => 'true',
           ];
    array_push($forms,$form);
    $form = ['label' => 'Added Member No','placeholder' => 'Added Member No',
             'type'  => 'data',
             'name' => 'added_member',
             'id' => 'added_member_id',
             'table' => 'member',
             'cond_1' =>  " and member_status = 1 and disable_flag = 0 and expired_date >= '".$mytime->format('Y-m-d')."' ",
             'cond_2' =>  " and member_status = 0",
             'cond' =>  " and member_status = 1 and disable_flag = 0 and expired_date >= '".$mytime->format('Y-m-d')."' ",
             'value_id' => ($cond=="insert" ? '' : $data->added_member_id ),
             'value' => ($cond=="insert" ? '' : $data->added_member ),
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'required' => false,
             'hide' => 'true',
           ];
    array_push($forms,$form);
    $form = ['type'  => 'blank',
             'col' => ['row' => 3, 'col1' => 4, 'col2' => 8],
           ];
    array_push($forms,$form);
    $form = ['label' => 'QTY (KG)','placeholder' => 'QTY (KG)',
             'type'  => 'number','name' => 'quantity_kg',
             'col' => ['row' => 3, 'col1' => 4, 'col2' => 8],
             'required' => 'true',
             'value' => ($cond=="insert" ? 1 : (float) $data->quantity_kg ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'X Price (KG)','placeholder' => 'Price (KG)',
             'type'  => 'currency','name' => 'price_kg',
             'col' => ['row' => 3, 'col1' => 4, 'col2' => 8],
            //  'required' => 'true',
             'value' => ($cond=="insert" ? 0 : $data->price_kg ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Total (KG)','placeholder' => 'Total (KG)',
             'type'  => 'currency','name' => 'total_kg',
             'col' => ['row' => 3, 'col1' => 4, 'col2' => 8],
             'required' => 'true',
            //  'read_only' => 'true',
             'value' => ($cond=="insert" ? 0 : $data->total_kg ),
           ];
    array_push($forms,$form);
    $form = ['type'  => 'blank',
             'col' => ['row' => 3, 'col1' => 4, 'col2' => 8],
           ];
    array_push($forms,$form);
    $form = ['label' => 'Satuan','placeholder' => 'Satuan',
             'type'  => 'table','name' => 'tr_payment_detail',
             'col' => ['row' => 9, 'col1' => 0, 'col2' => 12],
            //  'required' => 'true',
             'value' => ($cond=="insert" ? '' : '' ),
             'collapse' => false,
             'custom_button_flag' => false,
             'columns' => [
                          ['col_name' => 'id', 'header' => '', 'type' => 'hidden', 'hide'=> true],
                          ['col_name' => 'sequence_no', 'header' => 'No', 'type' => 'sequence_no', 'hide'=> false],
                          ['col_name' => 'name', 'header' => 'Nama', 'type' => 'text', 'hide'=> false],
                          ['col_name' => 'remark', 'header' => 'Ket', 'type' => 'text', 'hide'=> false],
                          ['col_name' => 'qty', 'header' => 'Qty', 'type' => 'currency', 'hide'=> false],
                          ['col_name' => 'price', 'header' => 'Hrg', 'type' => 'currency', 'hide'=> false],
                          ['col_name' => 'total', 'header' => 'Total', 'type' => 'currency', 'hide'=> false, 'read_only' => true],
             ],
             'details' => $data_details,
             'deleted_details' => [],
           ];
    array_push($forms,$form);

    $form = ['type'  => 'blank',
             'col' => ['row' => 3, 'col1' => 4, 'col2' => 8],
           ];
    array_push($forms,$form);

    $form = ['type'  => 'blank',
             'col' => ['row' => 3, 'col1' => 4, 'col2' => 8],
           ];
    array_push($forms,$form);
    $form = ['label' => 'QTY (Satuan)','placeholder' => 'QTY (Satuan)',
             'type'  => 'currency','name' => 'quantity_one',
             'col' => ['row' => 3, 'col1' => 4, 'col2' => 8],
            //  'required' => 'true',
             'read_only' => true,
             'value' => ($cond=="insert" ? '0' : $data->quantity_one ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Total (Satuan)','placeholder' => 'Total (Satuan)',
             'type'  => 'currency','name' => 'total_one',
             'col' => ['row' => 3, 'col1' => 4, 'col2' => 8],
            //  'required' => 'true',
            'read_only' => true,
             'value' => ($cond=="insert" ? '0' : $data->total_one ),
           ];
    array_push($forms,$form);
    $form = ['type'  => 'blank',
             'col' => ['row' => 3, 'col1' => 4, 'col2' => 8],
           ];
    array_push($forms,$form);
    $form = ['type'  => 'blank',
             'col' => ['row' => 3, 'col1' => 4, 'col2' => 8],
           ];
    array_push($forms,$form);
    $form = ['type'  => 'blank',
             'col' => ['row' => 3, 'col1' => 4, 'col2' => 8],
           ];
    array_push($forms,$form);
    $form = ['label' => 'Packet Price','placeholder' => 'Packet Price',
             'type'  => 'currency','name' => 'packet_price',
             'col' => ['row' => 3, 'col1' => 4, 'col2' => 8],
            //  'required' => 'true',
             'read_only' => 'true',
             'hide' => 'true',
             'value' => ($cond=="insert" ? '0' : ($data->packet_price == null ? 0 : $data->packet_price) ),
           ];
    array_push($forms,$form);
    $form = ['type'  => 'blank',
             'col' => ['row' => 3, 'col1' => 4, 'col2' => 8],
           ];
    array_push($forms,$form);
    $form = ['type'  => 'blank',
             'col' => ['row' => 3, 'col1' => 4, 'col2' => 8],
           ];
    array_push($forms,$form);
    $form =  ['label' => 'Diskon(Rp)','placeholder' => 'Diskon(Rp)',
             'type'  => 'currency','name' => 'discount',
             'col' => ['row' => 3, 'col1' => 4, 'col2' => 8],
             'value' => ($cond=="insert" ? '0' : $data->discount ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Total','placeholder' => 'Total',
             'type'  => 'currency','name' => 'total',
             'col' => ['row' => 3, 'col1' => 4, 'col2' => 8],
             'required' => 'true',
             'read_only' => 'true',
             'value' => ($cond=="insert" ? '0' : $data->Total ),
           ];
    array_push($forms,$form);
    $form = ['type'  => 'blank',
             'col' => ['row' => 3, 'col1' => 4, 'col2' => 8],
           ];
    array_push($forms,$form);
    // $form = ['type'  => 'blank',
    //          'col' => ['row' => 3, 'col1' => 4, 'col2' => 8],
    //        ];
    // array_push($forms,$form);
    $form = ['type'  => 'blank',
             'col' => ['row' => 3, 'col1' => 4, 'col2' => 8],
           ];
    array_push($forms,$form);
    $form = ['label' => 'Berat Awal','placeholder' => 'Berat Awal',
             'type'  => 'number','name' => 'beginning_kg',
             'col' => ['row' => 3, 'col1' => 4, 'col2' => 8],
             'read_only' => 'true',
             'hide' => 'true',
             'value' => ($cond=="insert" ? 0 : (float) $data->beginning_kg ),
           ];
    array_push($forms,$form);


    $form = ['label' => 'Berat Akhir','placeholder' => 'Berat Akhir',
             'type'  => 'number','name' => 'end_kg',
             'col' => ['row' => 3, 'col1' => 4, 'col2' => 8],
             'read_only' => 'true',
             'hide' => 'true',
             'value' => ($cond=="insert" ? 0 :(float)  $data->end_kg ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Keterangan','placeholder' => 'Keterangan',
             'type'  => 'text','name' => 'description',
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'required' => false,
             'value' => ($cond=="insert" ? '' : $data->description ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Rak','placeholder' => 'Rak',
             'type'  => 'data',
             'name' => 'rak',
             'id' => 'rak_id',
             'table' => 'rak',
             'value_id' => ($cond=="insert" ? '' : $data->rak_id ),
             'value' => ($cond=="insert" ? '' : $data->rak ),
              'col' => ['row' => 6, 'col1' => 4, 'col2' => 8],
           ];
    array_push($forms,$form);


    $result = [
      'forms' =>  $forms,
      'form_id' => $this->form_id,
      'form_table' => 'payment',
      'data_before' => $data,
      'access_list' => $this->access_list,
      'role' => Session::get('role'),
      'prev_id' => ($prev_id == '' ? '' : '#/form/payment/'.$prev_id),
      'next_id' => ($next_id == '' ? '' : '#/form/payment/'.$next_id),
    ];

    return Response::json($result);
  }


  // get form template for customer
  public function getRakForm($cond="insert", $id = "")
  {
    $this->access_list = $this->access->getAccess($this->form_id, Session::get('user_access_id'));


    $data = [];
    $last_payment_no_unused_id = "";
    $last_payment_no_unused = "";
    $mytime = \Carbon\Carbon::now();

    if ($id != "") {
      $data = Tr_payment::where('tr_payment.id',$id)
      ->leftJoin('mt_customer as mt_customer', function($join) {
        $join->on('tr_payment.customer_id', '=', 'mt_customer.id');
      })
      ->leftJoin('mt_packet as mt_packet', function($join) {
        $join->on('tr_payment.packet_id', '=', 'mt_packet.id');
      })
      ->leftJoin('tr_member as tr_member', function($join) {
        $join->on('tr_payment.member_id', '=', 'tr_member.id');
      })
      ->leftJoin('mt_rak as mt_rak', function($join) {
        $join->on('tr_payment.rak_id', '=', 'mt_rak.id');
      })
      ->first(['tr_payment.*','mt_rak.name as rak','mt_customer.name as customer','mt_packet.name as packet','mt_packet.price as packet_price','tr_member.member_no as member']);
    }

    if ($cond == "insert") {
      $data1 = Tr_payment::where('tr_payment.delete_flag',false)
      ->where('tr_payment.payment_status',false)
      ->orderBy('tr_payment.transaction_no','asc')
      ->first(['tr_payment.transaction_no','tr_payment.id']);

      if (isset($data1)) {
        $last_payment_no_unused = $data1->transaction_no;
        $last_payment_no_unused_id = $data1->id;
      }
    }

    $disable_date = Session::get('disable_date_payment');
    $get_last_no = Session::get('default_payment_no');
    if ($get_last_no == 'false') {
      $last_payment_no_unused_id = 0;
      $last_payment_no_unused = "";
    }

    $forms = [];
    $form = ['label' => 'Payment Date','placeholder' => 'Payment Date',
             'type'  => 'datetime','name' => 'transaction_date',
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'required' => 'true',
             'read_only' => $disable_date,
             'disable' => $disable_date,
             'value' => ($cond=="insert" ? $mytime->format('d/m/Y') : date_format(date_create($data->transaction_date),"d/m/Y")  ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'No Nota','placeholder' => 'No Nota',
             'type'  => 'data',
             'name' => 'transaction_no',
             'id' => 'id',
             'table' => 'payment',
             'value_id' => ($cond=="insert" ? $last_payment_no_unused_id : $data->transaction_no ),
             'value' => ($cond=="insert" ? $last_payment_no_unused : $data->transaction_no ),
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'required' => 'true',
             'read_only' => true,
           ];
    array_push($forms,$form);
    $form = ['label' => 'Rak','placeholder' => 'Rak',
             'type'  => 'data',
             'name' => 'rak',
             'id' => 'rak_id',
             'table' => 'rak',
             'value_id' => ($cond=="insert" ? '' : $data->rak_id ),
             'value' => ($cond=="insert" ? '' : $data->rak ),
              'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
           ];
    array_push($forms,$form);
    $form = ['label' => 'Is Taken','placeholder' => 'Is Taken',
             'type'  => 'checkbox',
             'name' => 'taken_flag',
             'value' => ($cond=="insert" ? false : ($data->taken_flag == 1 ? true : false) ),
              'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
           ];
    array_push($forms,$form);

    $taken_date = date_format(date_create($data->taken_date),"d/m/Y");
    if (date_format(date_create($data->taken_date),"Y-m-d") < '2000-01-01') {
      $taken_date =  $mytime->format('d/m/Y');
    }
    $form = ['label' => 'Taken Date','placeholder' => 'Taken Date',
             'type'  => 'datetime','name' => 'taken_date',
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'required' => 'true',
             'disable' => 'false',
             'value' => ($cond=="insert" ? $mytime->format('d/m/Y') : $taken_date  ),
           ];
    array_push($forms,$form);


    $result = [
      'forms' =>  $forms,
      'form_id' => $this->form_id,
      'form_table' => 'payment-rak',
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
         $save_datas['payment_status'] = true;

         $id = $datas[1]['value_id'];

         DB::beginTransaction();

         if ($save_datas['transaction_type'] == 'packet') { //beli packet
           $packet = Mt_packet::where('id',  $save_datas['packet_id'])
                                ->first(['mt_packet.*']);

          if (isset($packet)) {
            DB::table('tr_member')
             ->where('id', $save_datas['member_id'])
             ->update(
                     ['member_status' => true,
                     'customer_id' => $save_datas['customer_id'] ,
                     'member_added_id' => 0,
                     'packet_id' => $save_datas['packet_id'],
                     'start_date' => $save_datas['transaction_date'],
                     'added_duration' => 0,
                     'duration' => $packet->duration,
                     'custom_kg' => 0,
                     'member_added_kg' => 0,
                     'member_kg' => $packet->weight,
                     'total_kg' => $packet->weight,
                     'expired_date' => \Carbon\Carbon::parse($save_datas['transaction_date'])->addDays($packet->duration)
                    ]
               );
          }

        } elseif ($save_datas['transaction_type'] == 'extend-packet') { //beli packet
           $packet = Mt_packet::where('id',  $save_datas['packet_id'])
                               ->first(['mt_packet.*']);

           $member_old = Tr_member::where('id',  $save_datas['added_member_id'])
           ->leftJoin(
              DB::raw('(SELECT member_id, payment_status, SUM(quantity_kg) AS quantity_kg FROM tr_payment  where transaction_type = "member"   GROUP BY member_id, payment_status) as v'), function($join) {
                $join->on('v.member_id', '=', 'tr_member.id')
                     ->on('v.payment_status', '=', DB::raw('true'));
              })
            ->first(['tr_member.*' , DB::raw('tr_member.total_kg - IFNULL(v.quantity_kg,0) as quantity_kg')]);

         if (isset($packet) && isset($member_old)) {
           $added_duration = 0;

           if ( Date('Y-m-d H:i:s') < $member_old->expired_date) {
              $date1=date_create($member_old->expired_date);
              $date2=date_create(Date('Y-m-d H:i:s'));
              $added_duration1 =date_diff($date1,$date2);
              $added_duration =  $added_duration1->days;
           }

           DB::table('tr_member')
            ->where('id', $save_datas['added_member_id'])
            ->update(
                    ['disable_flag' => true
                   ]
              );

           DB::table('tr_member')
            ->where('id', $save_datas['member_id'])
            ->update(
                    ['member_status' => true,
                    'customer_id' => $save_datas['customer_id'] ,
                    'member_added_id' => $save_datas['added_member_id'],
                    'packet_id' => $save_datas['packet_id'],
                    'start_date' => $save_datas['transaction_date'],
                    'added_duration' => $added_duration,
                    'duration' => $packet->duration,
                    'custom_kg' => 0,
                    'member_added_kg' => $member_old->quantity_kg,
                    'member_kg' => $packet->weight,
                    'total_kg' => ($packet->weight + $member_old->quantity_kg),
                    'expired_date' => \Carbon\Carbon::parse($save_datas['transaction_date'])->addDays($packet->duration + $added_duration)
                   ]
              );
         }
        }


         try {
          //  $record = Tr_payment::create($save_datas);
          $closing_day = Tr_closing_day::where('closing_date',$save_datas['transaction_date'])
                          ->where('delete_flag',false)
                          ->first();

          if (isset($closing_day)) {
            $error = 'Tanggal tidak dapat dipilih karena periode sudah ditutup untuk tanggal ini';
            throw new Exception($error);
          }

          if (Session::get('not_allow_negatif_quota') == true) {
              if ( $save_datas['end_kg'] < 0 && $save_datas['transaction_type'] == 'member' ) {
                $error = 'Berat Akhir tidak diperbolehkan minus';
                throw new Exception($error);
              }
          }

          Tr_payment::where('id',$id)
                   ->update($save_datas);

         $save_datas_detail = $this->validator->convertInputDetail($datas, 'payment_id',$id);

         $record_detail = Tr_payment_detail::insert($save_datas_detail['tr_payment_detail_insert']);


         $audit = new Tr_audit;
         $audit->transaction_category = 'payment';
         $audit->transaction_id = $id;
         $audit->status = 'insert';
         $audit->column = "";
         $audit->value_old = "";
         $audit->value_new = "";
         $audit->modified_user_id = $request->session()->get('user_id');
         $audit->save();

           DB::commit();
             $arr = array('code' => "200", 'status' => "OK", 'url' => '#/list/payment');
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
         $save_datas['payment_status'] = true;
         unset($save_datas['id']);


         DB::beginTransaction();
         try {
           $closing_day = Tr_closing_day::where('closing_date',$save_datas['transaction_date'])
                           ->where('delete_flag',false)
                           ->first();
           if (isset($closing_day)) {
             $error = 'Tidak dapat dirubah karena periode sudah ditutup untuk tanggal ini';
             throw new Exception($error);
           }


           Tr_payment::where('id',$id)
                    ->update($save_datas);

          $save_datas_detail = $this->validator->convertInputDetail($datas, 'payment_id',$id);

          $record_detail_insert = Tr_payment_detail::insert($save_datas_detail['tr_payment_detail_insert']);
          foreach ($save_datas_detail['tr_payment_detail_update'] as $detail) {
            Tr_payment_detail::where('id',$detail['id'])
                     ->update($detail);
          }

          foreach ($save_datas_detail['tr_payment_detail_delete'] as $detail) {
            Tr_payment_detail::where('id',$detail['id'])->delete();
          }

          if ($this->columns !== "") {
            $audit = new Tr_audit;
            $audit->transaction_category = 'payment';
            $audit->transaction_id = $id;
            $audit->status = 'update';
            $audit->column = $this->columns;
            $audit->value_old = $this->valuebefore;
            $audit->value_new = $this->valueafter;
            $audit->modified_user_id = $request->session()->get('user_id');
            $audit->save();
          }

           DB::commit();
           $arr = array('code' => "200", 'status' => "OK",'url' => '#/list/payment');
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


  public function updateRak(Request $request, $id)
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
         unset($save_datas['id']);



         DB::beginTransaction();
         try {

           $payment_status1 = Tr_payment::where('id',$id)
                           ->where('delete_flag',false)
                           ->first();

           if (isset($payment_status1)) {
             if ($payment_status1->payment_status == false && $save_datas['taken_flag'] == true) {
               $error = 'Tidak dapat melakukan pengambilan karena nota belum diinput';
               throw new Exception($error);
             }
           }

           Tr_payment::where('id',$id)
                    ->update($save_datas);

          if ($this->columns !== "") {
            $audit = new Tr_audit;
            $audit->transaction_category = 'payment';
            $audit->transaction_id = $id;
            $audit->status = 'update';
            $audit->column = $this->columns;
            $audit->value_old = $this->valuebefore;
            $audit->value_new = $this->valueafter;
            $audit->modified_user_id = $request->session()->get('user_id');
            $audit->save();
          }

           DB::commit();
           $arr = array('code' => "200", 'status' => "OK",'url' => '#/list/payment');
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

     $record = Tr_payment::find($id);
     if(isset($record)) {
       $record->payment_status = false;
          $record->total = 0;
       //kalau tipenya adalah pembelian packet, hapus juga packetnya selama belum bergerak()
     }

     DB::beginTransaction();

     if ($record->transaction_type == 'packet') { //beli packet
       $packet = Mt_packet::where('id',  $record->packet_id)
                            ->first(['mt_packet.*']);

      if (isset($packet)) {
        DB::table('tr_member')
         ->where('id', $record->member_id)
         ->update(
                 ['member_status' => false,
                 'customer_id' => 0 ,
                 'member_added_id' => 0,
                 'packet_id' => 0,
                 'added_duration' => 0,
                 'duration' => 0,
                 'custom_kg' => 0,
                 'member_added_kg' => 0,
                 'member_kg' =>0,
                 'total_kg' => 0
                ]
           );
      }

    } elseif ($record->transaction_type == 'extend-packet') { //beli packet
       $packet = Mt_packet::where('id',  $record->packet_id)
                           ->first(['mt_packet.*']);

       $member_old = Tr_member::where('id', $record->added_member_id)
       ->leftJoin(
          DB::raw('(SELECT member_id, payment_status, SUM(quantity_kg) AS quantity_kg FROM tr_payment GROUP BY member_id, payment_status) as v'), function($join) {
            $join->on('v.member_id', '=', 'tr_member.id')
                 ->on('v.payment_status', '=', DB::raw('true'));
          })
        ->first(['tr_member.*' , DB::raw('tr_member.total_kg - IFNULL(v.quantity_kg,0) as quantity_kg')]);

       if (isset($packet) && isset($member_old)) {
         DB::table('tr_member')
          ->where('id', $record->added_member_id)
          ->update(
                  ['disable_flag' => false
                 ]
            );

         DB::table('tr_member')
          ->where('id', $record->member_id)
          ->update(
                    ['member_status' => false,
                    'customer_id' => 0 ,
                    'member_added_id' => 0,
                    'packet_id' => 0,
                    'added_duration' => 0,
                    'duration' => 0,
                    'custom_kg' => 0,
                    'member_added_kg' => 0,
                    'member_kg' =>0,
                    'total_kg' => 0
                   ]
            );
       }
    }


     try {

         $closing_day = Tr_closing_day::where('closing_date',$record->transaction_date)
                         ->where('delete_flag',false)
                         ->first();
         if (isset($closing_day)) {
           $error = 'Tidak dapat dihapus karena periode sudah ditutup untuk tanggal ini';
           throw new Exception($error);
         }

       $record->update();

       DB::commit();
       $arr = array('code' => "200", 'status' => "OK",'url' => '#/list/payment');
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
         $record = Tr_payment::find($data);
         if(isset($record)) {


           if ($record->transaction_type == 'packet') { //beli packet
             $packet = Mt_packet::where('id',  $record->packet_id)
                                  ->first(['mt_packet.*']);

            if (isset($packet)) {
              DB::table('tr_member')
               ->where('id', $record->member_id)
               ->update(
                       ['member_status' => false,
                       'customer_id' => 0 ,
                       'member_added_id' => 0,
                       'packet_id' => 0,
                       'added_duration' => 0,
                       'duration' => 0,
                       'custom_kg' => 0,
                       'member_added_kg' => 0,
                       'member_kg' =>0,
                       'total_kg' => 0
                      ]
                 );
            }

          } elseif ($record->transaction_type == 'extend-packet') { //beli packet
             $packet = Mt_packet::where('id',  $record->packet_id)
                                 ->first(['mt_packet.*']);

             $member_old = Tr_member::where('id', $record->added_member_id)
             ->leftJoin(
                DB::raw('(SELECT member_id, payment_status, SUM(quantity_kg) AS quantity_kg FROM tr_payment GROUP BY member_id, payment_status) as v'), function($join) {
                  $join->on('v.member_id', '=', 'tr_member.id')
                       ->on('v.payment_status', '=', DB::raw('true'));
                })
              ->first(['tr_member.*' , DB::raw('tr_member.total_kg - IFNULL(v.quantity_kg,0) as quantity_kg')]);

             if (isset($packet) && isset($member_old)) {
               DB::table('tr_member')
                ->where('id', $record->added_member_id)
                ->update(
                        ['disable_flag' => false
                       ]
                  );

               DB::table('tr_member')
                ->where('id', $record->member_id)
                ->update(
                          ['member_status' => false,
                          'customer_id' => 0 ,
                          'member_added_id' => 0,
                          'packet_id' => 0,
                          'added_duration' => 0,
                          'duration' => 0,
                          'custom_kg' => 0,
                          'member_added_kg' => 0,
                          'member_kg' =>0,
                          'total_kg' => 0
                         ]
                  );
             }
          }

          $closing_day = Tr_closing_day::where('closing_date',$record->transaction_date)
                          ->where('delete_flag',false)
                          ->first();
          if (isset($closing_day)) {
            $error = 'Tidak dapat dihapus karena periode sudah ditutup untuk tanggal ini';
            throw new Exception($error);
          }


            $record->payment_status = false;
           $record->total = 0;
           //kalau tipenya adalah pembelian packet, hapus juga packetnya selama belum bergerak()
            $record->update();
         }
       }

       DB::commit();
       $arr = array('code' => "200", 'status' => "OK",'url' => '#/list/payment');
       return json_encode($arr);
     } catch(\Exception $e){
       DB::rollback();
       $arr = array('code' => "200", 'status' => "FAIL", 'msg' => $e->getMessage());
       return json_encode($arr);
     }
  }

}
