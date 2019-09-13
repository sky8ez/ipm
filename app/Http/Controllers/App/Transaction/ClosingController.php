<?php

namespace App\Http\Controllers\App\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Tr_payment;
use App\Tr_payment_detail;
use App\Tr_closing_day;
use App\Tr_closing_day_detail;
use App\Mt_table_filter;
use App\Tr_member;
use Validator;
use DB;
use Response;
use Exception;
use App\Tr_audit;
use Illuminate\Support\Facades\Input;
use App\Repositories\ValidatorRepository;
use App\Repositories\AccessRepository;
use Session;

class ClosingController extends Controller
{
  /**
   * Send back all datas as JSON
   *
   * @return Response
   */
  private $form_id = "CLOSING_DAY";

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
      $header = ['label' => trans('closing_day.closing_date'),'value' => 'tr_closing_day.closing_date', 'type' => 'datetime', 'table' => '', 'sort' => 'sort'];
      array_push($headers,$header);
      $header = ['label' => trans('closing_day.cash_total'),'value' => 'tr_closing_day.cash_total', 'type' => 'currency', 'table' => '', 'sort' => 'sort'];
      array_push($headers,$header);
      $header = ['label' => trans('closing_day.debit_total'),'value' => 'tr_closing_day.debit_total', 'type' => 'currency', 'table' => '', 'sort' => 'sort'];
      array_push($headers,$header);
      $header = ['label' => trans('closing_day.end_balance'),'value' => 'tr_closing_day.end_balance', 'type' => 'currency', 'table' => '', 'sort' => 'sort'];
      array_push($headers,$header);
      $header = ['label' => trans('closing_day.updated_at'),'value' => 'tr_closing_day.updated_at', 'type' => 'datetime', 'table' => '', 'sort' => 'sort'];
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

      $records =Tr_closing_day::whereRaw($search)
      ->where('tr_closing_day.delete_flag',false)
       ->select('tr_closing_day.*')
      ->orderByRaw($sort)
      ->skip(25 * $skip)
      ->take(25)
      ->get();

      $records_count = 0;
      if ($skip == 0) {
        // $records_count = Tr_closing_day::whereRaw($search)
        // ->where('delete_flag',false)
        // ->count();
      } else {
        $records_count = 0;
      }


      $datas = [];

      foreach ($records as $record  ) {
        $row = ['id' => $record->id,
                 'records' => [
                             ['text_align' => 'left', 'type' => 'text' , 'value' => date_format(date_create($record->closing_date),"d M Y")],
                             ['text_align' => 'left', 'type' => 'currency' , 'value' => $record->cash_total],
                             ['text_align' => 'left', 'type' => 'currency' , 'value' => $record->debit_total],
                             ['text_align' => 'left', 'type' => 'currency' , 'value' => $record->end_balance],
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

    $beginning_balace = 0;
    $cash_total = 0;
    $debit_total = 0;
    $data_details_cash = [];
    $data_details_debit = [];

    $mytime = \Carbon\Carbon::parse('2017-03-03');
    $read_only_date = 'false';
    $read_only_beginningbalance = false;

    $prev_id = "";
    $next_id = "";

    if ($cond == "insert") {
      $data = Tr_closing_day::where('tr_closing_day.delete_flag',false)
      ->orderBy('tr_closing_day.closing_date','desc')
      ->first(['tr_closing_day.end_balance','tr_closing_day.closing_date']);

      if (isset($data)) {
        $beginning_balace = $data->end_balance;
        $mytime = \Carbon\Carbon::parse($data->closing_date)->addDays(1);
        $read_only_date = 'true';
        $read_only_beginningbalance = true;

      }

      $cash_total = Tr_payment::where('tr_payment.transaction_date',$mytime)
      ->where('tr_payment.payment_type','cash')
      ->where('tr_payment.payment_status',true)
      ->sum('tr_payment.total');

      $debit_total = Tr_payment::where('tr_payment.transaction_date',$mytime)
      ->where('tr_payment.payment_type','debit')
      ->where('tr_payment.payment_status',true)
      ->sum('tr_payment.total');

      $data_details_cash = Tr_payment::where('tr_payment.transaction_date', $mytime)
      ->where('tr_payment.payment_status', true)
      ->where('tr_payment.payment_type', 'cash')
      ->get(['tr_payment.*']);

      $data_details_debit = Tr_payment::where('tr_payment.transaction_date', $mytime)
      ->where('tr_payment.payment_status', true)
      ->where('tr_payment.payment_type', 'debit')
      ->get(['tr_payment.*']);

    } else {
        $read_only_date = 'true';
      $read_only_beginningbalance = true;
    }

    $data = [];
    $data_details = [];
    if ($id != "") {
      $data = Tr_closing_day::where('tr_closing_day.id',$id)
      ->first(['tr_closing_day.*']);

      $data_details = Tr_closing_day_detail::where('tr_closing_day_detail.closing_day_id',$id)
      ->get(['tr_closing_day_detail.*']);



      if (isset($data)) {
        $data_details_cash = Tr_payment::where('tr_payment.transaction_date', $data->closing_date)
        ->where('tr_payment.payment_status', true)
        ->where('tr_payment.payment_type', 'cash')
        ->get(['tr_payment.*']);

        $data_details_debit = Tr_payment::where('tr_payment.transaction_date', $data->closing_date)
        ->where('tr_payment.payment_status', true)
        ->where('tr_payment.payment_type', 'debit')
        ->get(['tr_payment.*']);

        $prev_data = Tr_closing_day::where('tr_closing_day.delete_flag',false)
        ->where('tr_closing_day.closing_date','<',$data->closing_date)
        ->orderBy('tr_closing_day.closing_date','desc')
        ->first(['id']);
        if (isset($prev_data)) { $prev_id = $prev_data->id ;}

        $next_data = Tr_closing_day::where('tr_closing_day.delete_flag',false)
        ->where('tr_closing_day.closing_date','>',$data->closing_date)
        ->orderBy('tr_closing_day.closing_date','asc')
        ->first(['id']);
        if (isset($next_data)) { $next_id = $next_data->id ;}
      }

    }

    $forms = [];

    $form = ['label' => 'Closing Date','placeholder' => 'Closing Date',
             'type'  => 'datetime','name' => 'closing_date',
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'required' => 'true',
             'read_only' => $read_only_date,
             'disable' => $read_only_date,
             'value' => ($cond=="insert" ? $mytime->format('d/m/Y') : \Carbon\Carbon::parse($data->closing_date)->format('d/m/Y')),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Beginning Balance','placeholder' => 'Beginning Balance',
             'type'  => 'currency','name' => 'beginning_balace',
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'required' => 'true',
             'read_only' => $read_only_beginningbalance,
            //  'disable' => $read_only_beginningbalance,
             'value' => ($cond=="insert" ? $beginning_balace : $data->beginning_balace ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Cash Total','placeholder' => 'Cash Total',
             'type'  => 'currency','name' => 'cash_total',
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'required' => 'true',
             'read_only' => 'true',
             'value' => ($cond=="insert" ? $cash_total : $data->cash_total ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Cash Detail','placeholder' => 'Cash Detail',
             'type'  => 'table-info','name' => 'tr_payment1',
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'collapse' => true,
             'value' => ($cond=="insert" ? '' : '' ),
             'custom_button_flag' => true, //untuk custom button
             'custom_button' => [], //untuk custom button
             'columns' => [
                          ['col_name' => 'id', 'header' => '', 'type' => 'hidden', 'hide'=> true],
                          // ['col_name' => 'sequence_no', 'header' => 'No', 'type' => 'sequence_no', 'hide'=> false, 'read_only' => true],
                          ['col_name' => 'transaction_no', 'header' => 'No Nota', 'type' => 'text', 'hide'=> false, 'read_only' => true],
                          ['col_name' => 'payment_type', 'header' => 'Jenis Pembayaran', 'type' => 'text', 'hide'=> false, 'read_only' => true],
                          ['col_name' => 'transaction_type', 'header' => 'Jenis Transaksi', 'type' => 'text', 'hide'=> false, 'read_only' => true],
                          ['col_name' => 'total', 'header' => 'Total', 'type' => 'currency', 'hide'=> false, 'read_only' => true],
             ],
             'details' => $data_details_cash,
             'deleted_details' => [],
           ];
    array_push($forms,$form);
    $form = ['label' => 'Debit Total','placeholder' => 'Debit Total',
             'type'  => 'currency','name' => 'debit_total',
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'required' => 'true',
             'read_only' => 'true',
             'value' => ($cond=="insert" ? $debit_total : $data->debit_total ),
           ];
    array_push($forms,$form);

      $form = ['label' => 'Debit Detail','placeholder' => 'Debit Detail',
               'type'  => 'table-info','name' => 'tr_payment2',
               'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
               'collapse' => true,
               'value' => ($cond=="insert" ? '' : '' ),
               'custom_button_flag' => true, //untuk custom button
               'custom_button' => [], //untuk custom button
               'columns' => [
                            ['col_name' => 'id', 'header' => '', 'type' => 'hidden', 'hide'=> true],
                            // ['col_name' => 'sequence_no', 'header' => 'No', 'type' => 'sequence_no', 'hide'=> false, 'read_only' => true],
                            ['col_name' => 'transaction_no', 'header' => 'No Nota', 'type' => 'text', 'hide'=> false, 'read_only' => true],
                            ['col_name' => 'payment_type', 'header' => 'Jenis Pembayaran', 'type' => 'text', 'hide'=> false, 'read_only' => true],
                            ['col_name' => 'transaction_type', 'header' => 'Jenis Transaksi', 'type' => 'text', 'hide'=> false, 'read_only' => true],
                            ['col_name' => 'total', 'header' => 'Total', 'type' => 'currency', 'hide'=> false, 'read_only' => true],
               ],
               'details' => $data_details_debit,
               'deleted_details' => [],
             ];
      array_push($forms,$form);
    $form = ['label' => 'Expense','placeholder' => 'Expense',
             'type'  => 'table','name' => 'tr_closing_day_detail',
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'required' => 'true',
             'collapse' => false,
             'value' => ($cond=="insert" ? '' : '' ),
             'custom_button_flag' => true, //untuk custom button
             'custom_button' => [['title' => 'add new Expense','value' => 'Expense'],['title' => 'add new Income','value' => 'Income']], //untuk custom button
             'columns' => [
                          ['col_name' => 'id', 'header' => '', 'type' => 'hidden', 'hide'=> true],
                          ['col_name' => 'color', 'header' => '', 'type' => 'hidden', 'hide'=> true],
                          ['col_name' => 'sequence_no', 'header' => 'No', 'type' => 'sequence_no', 'hide'=> false],
                          ['col_name' => 'category', 'header' => 'Jenis', 'type' => 'text', 'hide'=> false, 'read_only' => true],
                          ['col_name' => 'expense_name', 'header' => 'Nama Biaya / Pendapatan', 'type' => 'text', 'hide'=> false],
                          ['col_name' => 'reference_no', 'header' => 'Ket', 'type' => 'text', 'hide'=> false],
                          ['col_name' => 'total', 'header' => 'Total', 'type' => 'currency', 'hide'=> false],
             ],
             'details' => $data_details,
             'deleted_details' => [],
           ];
    array_push($forms,$form);
    $form = ['label' => 'Expense Total','placeholder' => 'Expense Total',
             'type'  => 'currency','name' => 'expense_total',
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'required' => 'true',
             'read_only' => 'true',
             'value' => ($cond=="insert" ? '0' : $data->expense_total ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Take Total','placeholder' => 'Take Total',
             'type'  => 'currency','name' => 'take_total',
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'required' => 'true',
             'value' => ($cond=="insert" ? '0' : $data->take_total ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'End Balance','placeholder' => 'End Balance',
             'type'  => 'currency','name' => 'end_balance',
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'required' => 'true',
             'read_only' => 'true',
             'value' => ($cond=="insert" ? '0' : $data->end_balance ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Total KG Perhari','placeholder' => 'Total KG Perhari',
             'type'  => 'currency','name' => 'total_kg_daily',
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'required' => 'true',
             'value' => ($cond=="insert" ? '0' : $data->total_kg_daily ),
           ];
    array_push($forms,$form);



    $result = [
      'forms' =>  $forms,
      'form_id' => $this->form_id,
      'form_table' => 'closing_day',
      'data_before' => $data,
      'access_list' => $this->access_list,
      'role' => Session::get('role'),
      'prev_id' => ($prev_id == '' ? '' : '#/form/closing-day/'.$prev_id),
      'next_id' => ($next_id == '' ? '' : '#/form/closing-day/'.$next_id),
      'extra_menus' => [['text' => 'Detail Daily', 'link' => '#/form/closing-day-detail/'.$id ]],
    ];

    return Response::json($result);
  }

  // get form template for customer
  public function getDetailForm($cond="insert", $id = "")
  {
    $this->access_list = $this->access->getAccess($this->form_id, Session::get('user_access_id'));

    $beginning_balace = 0;
    $cash_total = 0;
    $debit_total = 0;
    $total_kg = 0;

    $mytime = \Carbon\Carbon::parse('2017-03-03');
    $read_only_date = 'false';
    $read_only_beginningbalance = false;

    $prev_id = "";
    $next_id = "";

    if ($cond == "insert") {
      $data = Tr_closing_day::where('tr_closing_day.delete_flag',false)
      ->orderBy('Tr_closing_day.closing_date','desc')
      ->first(['tr_closing_day.end_balance','tr_closing_day.closing_date']);

      if (isset($data)) {
        $beginning_balace = $data->end_balance;
        $mytime = \Carbon\Carbon::parse($data->closing_date)->addDays(1);
        $read_only_date = 'true';
        $read_only_beginningbalance = true;
      }

      $cash_total = Tr_payment::where('tr_payment.transaction_date',$mytime)
      ->where('tr_payment.payment_type','cash')
      ->where('tr_payment.payment_status',true)
      ->sum('tr_payment.total');

      $debit_total = Tr_payment::where('tr_payment.transaction_date',$mytime)
      ->where('tr_payment.payment_type','debit')
      ->where('tr_payment.payment_status',true)
      ->sum('tr_payment.total');

      $total_kg = Tr_payment::where('tr_payment.transaction_date',$mytime)
      ->where('tr_payment.payment_type','debit')
      ->where('tr_payment.payment_status',true)
      ->sum('tr_payment.quantity_kg');

    } else {
      $read_only_date = 'true';
      $read_only_beginningbalance = true;
    }

    $data = [];
    $data_details_cash = [];
    $data_details_debit = [];
    $data_details_satuan = [];
    if ($id != "") {
      $data = Tr_closing_day::where('tr_closing_day.id',$id)
      ->first(['tr_closing_day.*']);

      $data_details_cash = Tr_payment::where('tr_payment.transaction_date', $data->closing_date)
      ->where('tr_payment.payment_status', true)
      ->where('tr_payment.payment_type', 'cash')
      ->get(['tr_payment.*']);

      $data_details_debit = Tr_payment::where('tr_payment.transaction_date', $data->closing_date)
      ->where('tr_payment.payment_status', true)
      ->where('tr_payment.payment_type', 'debit')
      ->get(['tr_payment.*']);

      $data_details_satuan = Tr_payment::where('tr_payment.transaction_date', $data->closing_date)
      ->join('tr_payment_detail as tr_payment_detail', function($join) {
        $join->on('tr_payment.id', '=', 'tr_payment_detail.payment_id');
      })
      ->where('tr_payment.payment_status', true)
      ->where('tr_payment.delete_flag', false)
      ->GroupBy('tr_payment_detail.name')
      ->selectRaw('tr_payment_detail.name, sum(tr_payment_detail.qty) as qty, sum(tr_payment_detail.total) as total')
      ->get();

      $total_kg = Tr_payment::where('tr_payment.transaction_date',$data->closing_date)
      ->where('tr_payment.payment_status',true)
      ->where('tr_payment.delete_flag',false)
      ->sum('tr_payment.quantity_kg');

      if (isset($data)) {
        $prev_data = Tr_closing_day::where('tr_closing_day.delete_flag',false)
        ->where('tr_closing_day.closing_date','<',$data->closing_date)
        ->orderBy('tr_closing_day.closing_date','desc')
        ->first(['id']);
        if (isset($prev_data)) { $prev_id = $prev_data->id ;}

        $next_data = Tr_closing_day::where('tr_closing_day.delete_flag',false)
        ->where('tr_closing_day.closing_date','>',$data->closing_date)
        ->orderBy('tr_closing_day.closing_date','asc')
        ->first(['id']);
        if (isset($next_data)) { $next_id = $next_data->id ;}
      }

    }

    $forms = [];

    $form = ['label' => 'Closing Date','placeholder' => 'Closing Date',
             'type'  => 'datetime','name' => 'closing_date',
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'required' => 'true',
             'read_only' => $read_only_date,
             'disable' => $read_only_date,
             'value' => ($cond=="insert" ? $mytime->format('d/m/Y') : \Carbon\Carbon::parse($data->closing_date)->format('d/m/Y')),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Cash Detail','placeholder' => 'Cash Detail',
             'type'  => 'table','name' => 'tr_payment1',
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'required' => 'true',
             'collapse' => true,
             'value' => ($cond=="insert" ? '' : '' ),
             'custom_button_flag' => true, //untuk custom button
             'custom_button' => [], //untuk custom button
             'columns' => [
                          ['col_name' => 'id', 'header' => '', 'type' => 'hidden', 'hide'=> true],
                          // ['col_name' => 'sequence_no', 'header' => 'No', 'type' => 'sequence_no', 'hide'=> false, 'read_only' => true],
                          ['col_name' => 'transaction_no', 'header' => 'No Nota', 'type' => 'text', 'hide'=> false, 'read_only' => true],
                          ['col_name' => 'payment_type', 'header' => 'Jenis Pembayaran', 'type' => 'text', 'hide'=> false, 'read_only' => true],
                          ['col_name' => 'transaction_type', 'header' => 'Jenis Transaksi', 'type' => 'text', 'hide'=> false, 'read_only' => true],
                          ['col_name' => 'total', 'header' => 'Total', 'type' => 'currency', 'hide'=> false, 'read_only' => true],
             ],
             'details' => $data_details_cash,
             'deleted_details' => [],
           ];
    array_push($forms,$form);
    $form = ['label' => 'Cash Total','placeholder' => 'Cash Total',
             'type'  => 'currency','name' => 'cash_total',
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'required' => 'true',
             'read_only' => 'true',
             'value' => ($cond=="insert" ? $cash_total : $data->cash_total ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Debit Detail','placeholder' => 'Debit Detail',
             'type'  => 'table','name' => 'tr_payment2',
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'required' => 'true',
             'collapse' => true,
             'value' => ($cond=="insert" ? '' : '' ),
             'custom_button_flag' => true, //untuk custom button
             'custom_button' => [], //untuk custom button
             'columns' => [
                          ['col_name' => 'id', 'header' => '', 'type' => 'hidden', 'hide'=> true],
                          // ['col_name' => 'sequence_no', 'header' => 'No', 'type' => 'sequence_no', 'hide'=> false, 'read_only' => true],
                          ['col_name' => 'transaction_no', 'header' => 'No Nota', 'type' => 'text', 'hide'=> false, 'read_only' => true],
                          ['col_name' => 'payment_type', 'header' => 'Jenis Pembayaran', 'type' => 'text', 'hide'=> false, 'read_only' => true],
                          ['col_name' => 'transaction_type', 'header' => 'Jenis Transaksi', 'type' => 'text', 'hide'=> false, 'read_only' => true],
                          ['col_name' => 'total', 'header' => 'Total', 'type' => 'currency', 'hide'=> false, 'read_only' => true],
             ],
             'details' => $data_details_debit,
             'deleted_details' => [],
           ];
    array_push($forms,$form);
    $form = ['label' => 'Debit Total','placeholder' => 'Debit Total',
             'type'  => 'currency','name' => 'debit_total',
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'required' => 'true',
             'read_only' => 'true',
             'value' => ($cond=="insert" ? $debit_total : $data->debit_total ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Total KG','placeholder' => 'Total KG',
             'type'  => 'currency','name' => 'total_kg',
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'required' => 'true',
             'read_only' => 'true',
             'value' => ($cond=="insert" ? $total_kg : $total_kg ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Satuan Detail','placeholder' => 'Satuan Detail',
             'type'  => 'table','name' => 'tr_payment3',
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'required' => 'true',
             'collapse' => true,
             'value' => ($cond=="insert" ? '' : '' ),
             'custom_button_flag' => true, //untuk custom button
             'custom_button' => [], //untuk custom button
             'columns' => [
                          ['col_name' => 'name', 'header' => 'Name', 'type' => 'text', 'hide'=> false, 'read_only' => true],
                          ['col_name' => 'qty', 'header' => 'QTY', 'type' => 'text', 'hide'=> false, 'read_only' => true],
                          ['col_name' => 'total', 'header' => 'Total', 'type' => 'currency', 'hide'=> false, 'read_only' => true],
             ],
             'details' => $data_details_satuan,
             'deleted_details' => [],
           ];
    array_push($forms,$form);

    $result = [
      'forms' =>  $forms,
      'form_id' => $this->form_id,
      'form_table' => 'closing_day_detail',
      'data_before' => $data,
      'access_list' => $this->access_list,
      'role' => Session::get('role'),
      'prev_id' => ($prev_id == '' ? '' : '#/form/closing-day/'.$prev_id),
      'next_id' => ($next_id == '' ? '' : '#/form/closing-day/'.$next_id),
      'extra_menus' => [['text' => 'Back to Closing Day', 'link' => '#/form/closing-day/'.$id ]],
      'hide_button' => true,
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
       $save_datas_detail = [];

       $rules = $this->validator->getValidator($datas);

       $validator = Validator::make($request->all(), $rules);

       if ($validator->passes()) {
         $save_datas = $this->validator->convertInput($datas);

         DB::beginTransaction();
         try {
           $record = Tr_closing_day::create($save_datas);

           $save_datas_detail = $this->validator->convertInputDetail($datas, 'closing_day_id',$record->id);

           $record_detail = Tr_closing_day_detail::insert($save_datas_detail['tr_closing_day_detail_insert']);



          $audit = new Tr_audit;
          $audit->transaction_category = 'closing_day';
          $audit->transaction_id = $record->id;
          $audit->status = 'insert';
          $audit->column = "";
          $audit->value_old = "";
          $audit->value_new = "";
          $audit->modified_user_id = $request->session()->get('user_id');
          $audit->save();

           DB::commit();
           $arr = array('code' => "200", 'status' => "OK", 'url' => '#/list/closing-day');
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
       $save_datas_detail = [];
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
           $record_after = Tr_closing_day::where('closing_date','>',$save_datas['closing_date'])
                                          ->where('delete_flag',false)
                                          ->first();
            if (isset($record_after)) {
              //ada data, throw errors
              $error = 'Hapus Dahulu Closing yang terbaru';
              throw new Exception($error);

            }


           Tr_closing_day::where('id',$id)
                    ->update($save_datas);

            $save_datas_detail = $this->validator->convertInputDetail($datas, 'closing_day_id',$id);

            $record_detail_insert = Tr_closing_day_detail::insert($save_datas_detail['tr_closing_day_detail_insert']);
            foreach ($save_datas_detail['tr_closing_day_detail_update'] as $detail) {
              Tr_closing_day_detail::where('id',$detail['id'])
                       ->update($detail);
            }

            foreach ($save_datas_detail['tr_closing_day_detail_delete'] as $detail) {
              Tr_closing_day_detail::where('id',$detail['id'])->delete();
            }

            if ($this->columns !== "") {
              $audit = new Tr_audit;
              $audit->transaction_category = 'closing_day';
              $audit->transaction_id = $id;
              $audit->status = 'update';
              $audit->column = $this->columns;
              $audit->value_old = $this->valuebefore;
              $audit->value_new = $this->valueafter;
              $audit->modified_user_id = $request->session()->get('user_id');
              $audit->save();
            }

           DB::commit();
           $arr = array('code' => "200", 'status' => "OK",'url' => '#/list/closing-day');
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

     $record = Tr_closing_day::find($id);
     if(isset($record)) {
       $record_after = Tr_closing_day::where('closing_date','>',$record->closing_date)
                                      ->where('delete_flag',false)
                                      ->first();
     }

     DB::beginTransaction();
     try {
       if (isset($record_after)) {
         //ada data, throw errors
         $error = 'Hapus Dahulu Closing yang terbaru';
         throw new Exception($error);

       } else {
          $record->delete_flag = true;
       }

       $record->update();

       DB::commit();
       $arr = array('code' => "200", 'status' => "OK",'url' => '#/list/closing-day');
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
         $record = Tr_closing_day::find($data);
         if(isset($record)) {
           $record_after = Tr_closing_day::where('closing_date','>',$record->closing_date)
                                          ->where('delete_flag',false)
                                          ->first();
            if (isset($record_after)) {
              //ada data, throw errors
              $error = 'Hapus Dahulu Closing yang terbaru';
              throw new Exception($error);

            } else {
               $record->delete_flag = true;
            }

            $record->update();
         }
       }

       DB::commit();
       $arr = array('code' => "200", 'status' => "OK",'url' => '#/list/closing-day');
       return json_encode($arr);
     } catch(\Exception $e){
       DB::rollback();
       $arr = array('code' => "200", 'status' => "FAIL", 'msg' => $e->getMessage());
       return json_encode($arr);
     }
  }

}
