<?php

namespace App\Http\Controllers\App\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Mt_rak;
use App\Tr_payment;
use App\Tr_member;
use App\Tr_closing_day;
use App\Mt_customer;
use App\Mt_packet;
use Validator;
use DB;
use Response;
use Illuminate\Support\Facades\Input;
use App\Repositories\ValidatorRepository;
use Excel;
use Session;




class ReportController extends Controller
{

    public function export(Request $request)
    {

          $data = json_decode($request->input('data'),true);
          // $data = array(
          //   array('data1', 'data2'),
          //   array('data3', 'data4')
          //   );

          // $data = Tr_payment::where('delete_flag',false)
          //                    ->get();

          Excel::create('Report', function($excel)  use($data) {
            $excel->sheet('Report', function($sheet)  use($data) {
                  // Set top, right, bottom, left
                  // $sheet->setPageMargin(array(
                  //     0.25, 0.30, 0.25, 0.30
                  // ));
                  $sheet->fromArray($data,null,'A1',true);
                  // $sheet->fromModel($data);
              });
            // Set the title
            $excel->setTitle('Laporan Pembayaran');

            // Chain the setters
            $excel->setCreator('TYROO')
                  ->setCompany('TYROO');

            // Call them separately
            $excel->setDescription('Laporan');

          })->export('xls');

    }

    public function exportCSV(Request $request)
    {

          $data = json_decode($request->input('data'),true);
          // $data = array(
          //   array('data1', 'data2'),
          //   array('data3', 'data4')
          //   );

          // $data = Tr_payment::where('delete_flag',false)
          //                    ->get();

          Excel::create('Report', function($excel)  use($data) {
            $excel->sheet('Report', function($sheet)  use($data) {
                  // Set top, right, bottom, left
                  // $sheet->setPageMargin(array(
                  //     0.25, 0.30, 0.25, 0.30
                  // ));
                  $sheet->fromArray($data,null,'A1',true);
                  // $sheet->fromModel($data);
              });
            // Set the title
            $excel->setTitle('Laporan Pembayaran');

            // Chain the setters
            $excel->setCreator('TYROO')
                  ->setCompany('TYROO');

            // Call them separately
            $excel->setDescription('Laporan');

          })->export('csv');

    }

    public function exportPDF(Request $request)
    {

          $data = json_decode($request->input('data'),true);
          // $data = array(
          //   array('data1', 'data2'),
          //   array('data3', 'data4')
          //   );

          // $data = Tr_payment::where('delete_flag',false)
          //                    ->get();

          Excel::create('Report', function($excel)  use($data) {
            $excel->sheet('Report', function($sheet)  use($data) {
                  // Set top, right, bottom, left
                  // $sheet->setPageMargin(array(
                  //     0.25, 0.30, 0.25, 0.30
                  // ));
                  $sheet->fromArray($data,null,'A1',true);
                  // $sheet->fromModel($data);
              });
            // Set the title
            $excel->setTitle('Laporan Pembayaran');

            // Chain the setters
            $excel->setCreator('TYROO')
                  ->setCompany('TYROO');

            // Call them separately
            $excel->setDescription('Laporan');

          })->export('pdf');

    }

    public function getReportList()
    {
      $report_list = [];
      $access_list = [];
      if ((count(Session::get('user_access')) > 0)) {
        foreach (Session::get('user_access') as $access) {
          if ($access->condition === 'nav' && $access->cond_flag == 1) {
            array_push($access_list, $access->module_id);
          }
        }
      }

      if (in_array("REPORT001", $access_list)) {
        array_push($report_list, ["#/reports/payment-reports","Laporan Penerimaan Cash"]);
      }
      if (in_array("REPORT002", $access_list)) {
        array_push($report_list, ["#/reports/selling-reports","Laporan Rekap Satuan"]);
      }
      if (in_array("REPORT003", $access_list)) {
        array_push($report_list, ["#/reports/kg-reports","Laporan Rekap Kg"]);
      }
      if (in_array("REPORT004", $access_list)) {
        array_push($report_list, ["#/reports/closing-day-reports","Laporan Tutup Harian"]);
      }
      if (in_array("REPORT005", $access_list)) {
        array_push($report_list, ["#/reports/active-member-report","Laporan Member Aktif"]);
      }
      if (in_array("REPORT006", $access_list)) {
        array_push($report_list, ["#/reports/member-reports","Laporan Transaksi Member (Kg)"]);
      }
      if (in_array("REPORT007", $access_list)) {
        array_push($report_list, ["#/reports/rak-report","Laporan Rak"]);
      }
      if (in_array("REPORT008", $access_list)) {
        array_push($report_list, ["#/reports/rak-report-not-taken","Rekap Belum Diambil"]);
      }

      return Response::json($report_list);

    }

    public function getReportView($report_name = "")
    {

      $data_details = [];
      $title = [];
      $search = [];
      $columns = [];
      $filter_no_flag = false;
      $report_id = "";

      switch ($report_name) {
        case 'payment-reports':
          $report_id = "REPORT001";
          $data_details =  [];
          $filter_no_flag = true;
          $title = 'Laporan Pembayaran';
          $search = [
                       ['col_name' => 'payment_type',
                         'type' => 'select',
                         'options' => ['all','cash','debit','member'],
                         'val' => 'all',
                       ],
                       ['col_name' => 'transaction_type',
                         'type' => 'select',
                         'options' => ['all','laundry','packet'],
                         'val' => 'all',
                       ],
                       ['col_name' => 'payment_status',
                         'type' => 'select',
                         'options' => ['all','Paid','Unpaid'],
                         'val' => 'all',
                       ],
                     ];
          $columns = [
                      //  ['text _align' => 'left', 'col_name' => 'id', 'header' => 'ID', 'type' => 'text', 'hide'=> false, 'has_total' => false],
                       ['text _align' => 'left', 'col_name' => 'payment_status', 'header' => 'Status Nota', 'type' => 'payment_status', 'hide'=> false, 'has_total' => false],
                       ['text_align' => 'left', 'col_name' => 'transaction_no', 'header' => 'No', 'type' => 'text', 'hide'=> false, 'has_total' => false],
                       ['text_align' => 'left', 'col_name' => 'transaction_date', 'header' => 'Tanggal', 'type' => 'datetime', 'hide'=> false, 'has_total' => false],
                       ['text_align' => 'left', 'col_name' => 'payment_type', 'header' => 'Jenis Pembayaran', 'type' => 'text', 'hide'=> false, 'has_total' => false],
                       ['text_align' => 'left', 'col_name' => 'transaction_type', 'header' => 'Jenis Transaksi', 'type' => 'text', 'hide'=> false, 'has_total' => false],
                       ['text_align' => 'right', 'col_name' => 'total', 'header' => 'Total', 'type' => 'currency', 'hide'=> false, 'has_total' => true],
          ];
          break;
          case 'selling-reports':
            $report_id = "REPORT002";
            $data_details =  [];
            $filter_no_flag = true;
            $title = 'Laporan Penjualan';
            $search = [
                         ['col_name' => 'payment_type',
                           'type' => 'select',
                           'options' => ['all','cash','debit','member'],
                           'val' => 'all',
                         ],
                         ['col_name' => 'transaction_type',
                           'type' => 'select',
                           'options' => ['all','laundry','packet'],
                           'val' => 'all',
                         ],
                         ['col_name' => 'payment_status',
                           'type' => 'select',
                           'options' => ['all','Paid','Unpaid'],
                           'val' => 'all',
                         ],
                       ];
            $columns = [
                        //  ['text _align' => 'left', 'col_name' => 'id', 'header' => 'ID', 'type' => 'text', 'hide'=> false, 'has_total' => false],
                         ['text _align' => 'left', 'col_name' => 'payment_status', 'header' => 'Status Nota', 'type' => 'payment_status', 'hide'=> false, 'has_total' => false],
                         ['text_align' => 'left', 'col_name' => 'transaction_no', 'header' => 'No', 'type' => 'text', 'hide'=> false, 'has_total' => false],
                         ['text_align' => 'left', 'col_name' => 'transaction_date', 'header' => 'Tanggal', 'type' => 'datetime', 'hide'=> false, 'has_total' => false],
                         ['text_align' => 'left', 'col_name' => 'payment_type', 'header' => 'Jenis Pembayaran', 'type' => 'text', 'hide'=> false, 'has_total' => false],
                         ['text_align' => 'left', 'col_name' => 'transaction_type', 'header' => 'Jenis Transaksi', 'type' => 'text', 'hide'=> false, 'has_total' => false],
                         ['text_align' => 'right', 'col_name' => 'name', 'header' => 'Nama (Sat)', 'type' => 'text', 'hide'=> false, 'has_total' => false],
                         ['text_align' => 'right', 'col_name' => 'remark', 'header' => 'Ket', 'type' => 'text', 'hide'=> false, 'has_total' => false],
                         ['text_align' => 'right', 'col_name' => 'qty', 'header' => 'Qty', 'type' => 'number', 'hide'=> false, 'has_total' => false],
                         ['text_align' => 'right', 'col_name' => 'price', 'header' => 'Harga', 'type' => 'currency', 'hide'=> false, 'has_total' => false],
                         ['text_align' => 'right', 'col_name' => 'total', 'header' => 'Total', 'type' => 'currency', 'hide'=> false, 'has_total' => true],
            ];
            break;
          case 'kg-reports':
            $report_id = "REPORT003";
            $data_details =  [];
            $filter_no_flag = true;
            $title = 'Laporan Penjualan KG';
            $search = [
                         ['col_name' => 'payment_type',
                           'type' => 'select',
                           'options' => ['all','cash','debit','member'],
                           'val' => 'all',
                         ],
                         ['col_name' => 'transaction_type',
                           'type' => 'select',
                           'options' => ['all','laundry','packet'],
                           'val' => 'all',
                         ],
                         ['col_name' => 'payment_status',
                           'type' => 'select',
                           'options' => ['all','Paid','Unpaid'],
                           'val' => 'all',
                         ],
                       ];
            $columns = [
                        //  ['text _align' => 'left', 'col_name' => 'id', 'header' => 'ID', 'type' => 'text', 'hide'=> false, 'has_total' => false],
                         ['text _align' => 'left', 'col_name' => 'payment_status', 'header' => 'Status Nota', 'type' => 'payment_status', 'hide'=> false, 'has_total' => false],
                         ['text_align' => 'left', 'col_name' => 'transaction_no', 'header' => 'No', 'type' => 'text', 'hide'=> false, 'has_total' => false],
                         ['text_align' => 'left', 'col_name' => 'transaction_date', 'header' => 'Tanggal', 'type' => 'datetime', 'hide'=> false, 'has_total' => false],
                         ['text_align' => 'left', 'col_name' => 'payment_type', 'header' => 'Jenis Pembayaran', 'type' => 'text', 'hide'=> false, 'has_total' => false],
                         ['text_align' => 'left', 'col_name' => 'transaction_type', 'header' => 'Jenis Transaksi', 'type' => 'text', 'hide'=> false, 'has_total' => false],
                         ['text_align' => 'right', 'col_name' => 'quantity_kg', 'header' => 'KG', 'type' => 'number', 'hide'=> false, 'has_total' => true],
            ];
            break;
      case 'closing-day-reports':
        $report_id = "REPORT004";
        $data_details =  [];
        $title = 'Laporan Tutup Harian';
        $search = [
                   ];
        $columns = [
                    //  ['text _align' => 'left', 'col_name' => 'id', 'header' => 'ID', 'type' => 'text', 'hide'=> false, 'has_total' => false],
                     ['text_align' => 'left', 'col_name' => 'closing_date', 'header' => 'Tanggal Closing', 'type' => 'datetime', 'hide'=> false, 'has_total' => false],
                     ['text_align' => 'left', 'col_name' => 'beginning_balace', 'header' => 'Saldo Awal', 'type' => 'currency', 'hide'=> false, 'has_total' => false],
                     ['text_align' => 'left', 'col_name' => 'cash_total', 'header' => 'Total Cash', 'type' => 'currency', 'hide'=> false, 'has_total' => true],
                     ['text_align' => 'left', 'col_name' => 'debit_total', 'header' => 'Total Debit', 'type' => 'currency', 'hide'=> false, 'has_total' => true],
                     ['text_align' => 'left', 'col_name' => 'expense_total', 'header' => 'Total Biaya', 'type' => 'currency', 'hide'=> false, 'has_total' => true],
                     ['text_align' => 'left', 'col_name' => 'take_total', 'header' => 'Total Pengambilan', 'type' => 'currency', 'hide'=> false, 'has_total' => true],
                     ['text_align' => 'left', 'col_name' => 'end_balance', 'header' => 'Saldo Akhir', 'type' => 'currency', 'hide'=> false, 'has_total' => false],
        ];
        break;
      case 'active-member-report':
        $report_id = "REPORT005";
        $data_details =  [];
        $filter_no_flag = true;
        $title = 'Laporan Member Aktif';
        $search = [
                     ['col_name' => 'customer',
                      'type' => 'text',
                       'placeholder' => 'Customer',
                       'val' => '',
                     ]
                   ];
        $columns = [
                    // ['text _align' => 'left', 'col_name' => 'id', 'header' => 'ID', 'type' => 'text', 'hide'=> false, 'has_total' => false],
                     ['text_align' => 'left', 'col_name' => 'member_no', 'header' => 'No Member', 'type' => 'text', 'hide'=> false, 'has_total' => false],
                     ['text_align' => 'left', 'col_name' => 'customer', 'header' => 'Customer', 'type' => 'text', 'hide'=> false, 'has_total' => false],
                     ['text_align' => 'left', 'col_name' => 'start_date', 'header' => 'Tanggal Mulai', 'type' => 'datetime', 'hide'=> false, 'has_total' => false],
                     ['text_align' => 'left', 'col_name' => 'duration', 'header' => 'Durasi', 'type' => 'number', 'hide'=> false, 'has_total' => false],
                     ['text_align' => 'left', 'col_name' => 'custom_kg', 'header' => 'Custom KG', 'type' => 'number', 'hide'=> false, 'has_total' => true],
                     ['text_align' => 'left', 'col_name' => 'member_kg', 'header' => 'Member KG', 'type' => 'number', 'hide'=> false, 'has_total' => true],
                     ['text_align' => 'left', 'col_name' => 'total_kg', 'header' => 'Total KG', 'type' => 'number', 'hide'=> false, 'has_total' => true],
                     ['text_align' => 'left', 'col_name' => 'expired_date', 'header' => 'Tgl Berakhir', 'type' => 'datetime', 'hide'=> false, 'has_total' => false],
                     ['text_align' => 'left', 'col_name' => 'remaining', 'header' => 'Sisa', 'type' => 'number', 'hide'=> false, 'has_total' => true],
        ];
        break;
    case 'member-reports':
      $report_id = "REPORT006";
      $data_details =  [];
      $filter_no_flag = true;
      $title = 'Laporan Pemakaian Member';
      $search = [
                   ['col_name' => 'customer',
                    'type' => 'text',
                     'placeholder' => 'Customer',
                     'val' => '',
                   ],
                   ['col_name' => 'member_no',
                    'type' => 'text',
                     'placeholder' => 'Member',
                     'val' => '',
                   ]
                 ];
      $columns = [
                  // ['text _align' => 'left', 'col_name' => 'id', 'header' => 'ID', 'type' => 'text', 'hide'=> false, 'has_total' => false],
                  ['text _align' => 'left', 'col_name' => 'member_no', 'header' => 'No Member', 'type' => 'text', 'hide'=> false, 'has_total' => false],
                  ['text _align' => 'left', 'col_name' => 'customer', 'header' => 'Customer', 'type' => 'text', 'hide'=> false, 'has_total' => false],
                  ['text _align' => 'left', 'col_name' => 'payment_status', 'header' => 'Status Nota', 'type' => 'payment_status', 'hide'=> false, 'has_total' => false],
                  ['text_align' => 'left', 'col_name' => 'transaction_no', 'header' => 'No', 'type' => 'text', 'hide'=> false, 'has_total' => false],
                  ['text_align' => 'left', 'col_name' => 'transaction_date', 'header' => 'Tanggal', 'type' => 'datetime', 'hide'=> false, 'has_total' => false],
                  ['text_align' => 'left', 'col_name' => 'payment_type', 'header' => 'Jenis Pembayaran', 'type' => 'text', 'hide'=> false, 'has_total' => false],
                  ['text_align' => 'left', 'col_name' => 'transaction_type', 'header' => 'Jenis Transaksi', 'type' => 'text', 'hide'=> false, 'has_total' => false],
                  ['text_align' => 'right', 'col_name' => 'quantity_kg', 'header' => 'Total KG', 'type' => 'currency', 'hide'=> false, 'has_total' => true],
      ];
      break;
      case 'rak-report':
        $report_id = "REPORT007";
        $data_details =  [];
        $filter_no_flag = true;
        $title = 'Laporan Rak Barang';
        $search = [
                     ['col_name' => 'rak',
                      'type' => 'text',
                       'placeholder' => 'Rak',
                       'val' => '',
                     ]
                   ];
        $columns = [
                    // ['text _align' => 'left', 'col_name' => 'id', 'header' => 'ID', 'type' => 'text', 'hide'=> false, 'has_total' => false],
                    ['text_align' => 'left', 'col_name' => 'rak', 'header' => 'Rak', 'type' => 'text', 'hide'=> false, 'has_total' => false],
                     ['text_align' => 'left', 'col_name' => 'transaction_date', 'header' => 'Tanggal', 'type' => 'text', 'hide'=> false, 'has_total' => false],
                     ['text_align' => 'left', 'col_name' => 'transaction_no', 'header' => 'No Nota', 'type' => 'text', 'hide'=> false, 'has_total' => false]
        ];



        break;
      case 'rak-report-not-taken':
        $report_id = "REPORT008";
        $data_details =  [];
        $filter_no_flag = true;
        $title = 'Laporan Barang Belum Diambil';
        $search = [
                     ['col_name' => 'rak',
                      'type' => 'text',
                       'placeholder' => 'Rak',
                       'val' => '',
                     ]
                   ];
        $columns = [
                    // ['text _align' => 'left', 'col_name' => 'id', 'header' => 'ID', 'type' => 'text', 'hide'=> false, 'has_total' => false],
                     ['text_align' => 'left', 'col_name' => 'transaction_no', 'header' => 'No Nota', 'type' => 'text', 'hide'=> false, 'has_total' => false],
                     ['text_align' => 'left', 'col_name' => 'rak', 'header' => 'Rak', 'type' => 'text', 'hide'=> false, 'has_total' => false]
        ];
        break;
        default:
          # code...
          break;
      }

      $data = [
               'report_id' => $report_id,
               'title' => $title,
               'filter_no_flag' => $filter_no_flag,
               'search' => $search,
               'columns' => $columns,
               'details' => $data_details
             ];

        return Response::json($data);
    }

    public function refreshReport(Request $request) {
          // $result = ['tess' => $request->date_from];
          // return Response::json($result);
          $result = [];
          switch ($request->report_name) {
            case 'payment-reports':
              $result = $this->payment_report($request);
              break;
            case 'selling-reports':
              $result = $this->selling_report($request);
              break;
            case 'kg-reports':
              $result = $this->kg_report($request);
              break;
            case 'closing-day-reports':
              $result = $this->closing_report($request);
              break;
            case 'active-member-report':
              $result = $this->active_member_report($request);
              break;
            case 'rak-report':
              $result = $this->rak_report($request);
              break;
            case 'rak-report-not-taken':
              $result = $this->pengambilan_report($request);
              break;
            case 'member-reports':
              $result = $this->member_reports($request);
              break;
            default:
              # code...
              break;
          }

          return Response::json($result);
    }

    public function payment_report(Request $request) {
        $date_filter = " and transaction_date >= '".$request->date_from."' and transaction_date <= '".$request->date_to."'";

        if ($request->no_from !== "" && $request->no_to !== "") {
          $no_filter = " and transaction_no >= '".$request->no_from."' and transaction_no <= '".$request->no_to."'";
        } else if ($request->no_from !== "" && $request->no_to == "") {
          $no_filter = " and transaction_no >= '".$request->no_from."'";
        } else if ($request->no_from == "" && $request->no_to !== "") {
          $no_filter = " and transaction_no <= '".$request->no_to."'";
        } else  {
          $no_filter = "";
        }

        if ($request->payment_status == "all") {
          $payment_status_filter = "";
        } else {
          $payment_status_filter = " and payment_status = '".($request->payment_status == "Paid" ? true : false)."'";

        }

        if ($request->payment_type == "all") {
          $payment_type_filter = "";
        } else {
          $payment_type_filter = " and payment_type = '".$request->payment_type."'";
        }

        if ($request->transaction_type == "all") {
          $transaction_type_filter = "";
        } else {
          $transaction_type_filter = " and transaction_type = '".$request->transaction_type."'";
        }

        $search = "1=1".$date_filter.$no_filter.$payment_status_filter.$payment_type_filter.$transaction_type_filter;
        $results = [];
        $sort = "tr_payment.transaction_no asc";

        $records =Tr_payment::whereRaw($search)
        ->where('delete_flag',false)
        ->orderByRaw($sort)
        ->get(['tr_payment.id',
                'tr_payment.payment_status',
               'tr_payment.transaction_no',
               'tr_payment.transaction_date',
               'tr_payment.payment_type',
               'tr_payment.transaction_type',
               'tr_payment.total'
               ]);

        foreach ($records as $key => $value) {
          $result['id'] = $value['id'];
          $result['payment_status'] = ($value['payment_status'] == 1 ? 'Paid' : 'Unpaid' );
          $result['transaction_no'] = $value['transaction_no'];
          $result['transaction_date'] = ($value['payment_status'] == 1 ? date_format(date_create($value['transaction_date']),"d M Y") : '' );
          $result['payment_type'] =($value['payment_status'] == 1 ? $value['payment_type'] : '' );
          $result['transaction_type'] = ($value['payment_status'] == 1 ? $value['transaction_type'] : '' );
          $result['total'] = ($value['payment_status'] == 1 ? $value['total'] : 0 );
          array_push($results,$result);
        }

        $total = $records->sum('total');

        $res = [];
        $res['row'] = $results;
        $res['footers'] = [
                          ['align' => 'right', 'type'=> 'text','hide'=>false,'value' => 'Total'],
                          ['align' => 'right', 'type'=> 'text','hide'=>false,'value' => ''],
                          ['align' => 'right', 'type'=> 'text','hide'=>false,'value' => ''],
                          ['align' => 'right', 'type'=> 'text','hide'=>false,'value' => ''],
                          ['align' => 'right', 'type'=> 'text','hide'=>false,'value' => ''],
                          ['align' => 'right', 'type'=> 'text','hide'=>false,'value' => ''],
                          ['align' => 'right', 'type'=> 'currency','hide'=>false,'value' => $total],
                        ];

        return $res;
    }

    public function member_reports(Request $request) {
        $date_filter = " and transaction_date >= '".$request->date_from."' and transaction_date <= '".$request->date_to."'";

        if ($request->no_from !== "" && $request->no_to !== "") {
          $no_filter = " and transaction_no >= '".$request->no_from."' and transaction_no <= '".$request->no_to."'";
        } else if ($request->no_from !== "" && $request->no_to == "") {
          $no_filter = " and transaction_no >= '".$request->no_from."'";
        } else if ($request->no_from == "" && $request->no_to !== "") {
          $no_filter = " and transaction_no <= '".$request->no_to."'";
        } else  {
          $no_filter = "";
        }

        if ($request->member_no == "") {
          $member_no_filter = "";
        } else {
          $member_no_filter = " and member_no = '".$request->member_no."'";

        }

        if ($request->customer == "") {
          $customer_filter = "";
        } else {
          $customer_filter = " and mt_customer.name = '".$request->customer."'";

        }
        //
        // if ($request->payment_type == "all") {
        //   $payment_type_filter = "";
        // } else {
        //   $payment_type_filter = " and payment_type = '".$request->payment_type."'";
        // }
        //
        // if ($request->transaction_type == "all") {
        //   $transaction_type_filter = "";
        // } else {
        //   $transaction_type_filter = " and transaction_type = '".$request->transaction_type."'";
        // }

        $search = "1=1".$date_filter.$no_filter.$member_no_filter.$customer_filter;
        $results = [];
        $sort = "tr_payment.transaction_no asc";

        $records =Tr_payment::whereRaw($search)
        ->leftJoin('tr_member as tr_member', function($join) {
          $join->on('tr_payment.member_id', '=', 'tr_member.id');
        })
        ->leftJoin('mt_customer as mt_customer', function($join) {
          $join->on('tr_member.customer_id', '=', 'mt_customer.id');
        })
        ->where('tr_payment.delete_flag',false)
        ->where('tr_payment.payment_status',true)
        ->where('tr_payment.transaction_type','member')
        ->orderByRaw($sort)
        ->get(['tr_payment.id',
                'tr_member.member_no',
                  'mt_customer.name as customer',
                'tr_payment.payment_status',
               'tr_payment.transaction_no',
               'tr_payment.transaction_date',
               'tr_payment.payment_type',
               'tr_payment.transaction_type',
               'tr_payment.quantity_kg'
               ]);

        foreach ($records as $key => $value) {
          $result['id'] = $value['id'];
          $result['member_no'] = $value['member_no'];
          $result['customer'] = $value['customer'];
          $result['payment_status'] = ($value['payment_status'] == 1 ? 'Paid' : 'Unpaid' );
          $result['transaction_no'] = $value['transaction_no'];
          $result['transaction_date'] = ($value['payment_status'] == 1 ? date_format(date_create($value['transaction_date']),"d M Y") : '' );
          $result['payment_type'] =($value['payment_status'] == 1 ? $value['payment_type'] : '' );
          $result['transaction_type'] = ($value['payment_status'] == 1 ? $value['transaction_type'] : '' );
          $result['quantity_kg'] = ($value['payment_status'] == 1 ? $value['quantity_kg'] : 0 );
          array_push($results,$result);
        }

        $total = $records->sum('total');

        $res = [];
        $res['row'] = $results;
        $res['footers'] = [
                          ['align' => 'right', 'type'=> 'text','hide'=>false,'value' => 'Total'],
                          ['align' => 'right', 'type'=> 'text','hide'=>false,'value' => ''],
                          ['align' => 'right', 'type'=> 'text','hide'=>false,'value' => ''],
                          ['align' => 'right', 'type'=> 'text','hide'=>false,'value' => ''],
                          ['align' => 'right', 'type'=> 'text','hide'=>false,'value' => ''],
                          ['align' => 'right', 'type'=> 'text','hide'=>false,'value' => ''],
                          ['align' => 'right', 'type'=> 'text','hide'=>false,'value' => ''],
                          ['align' => 'right', 'type'=> 'text','hide'=>false,'value' => ''],
                          ['align' => 'right', 'type'=> 'currency','hide'=>false,'value' => $total],
                        ];

        return $res;
    }

    public function selling_report(Request $request) {
        $date_filter = " and transaction_date >= '".$request->date_from."' and transaction_date <= '".$request->date_to."'";

        if ($request->no_from !== "" && $request->no_to !== "") {
          $no_filter = " and transaction_no >= '".$request->no_from."' and transaction_no <= '".$request->no_to."'";
        } else if ($request->no_from !== "" && $request->no_to == "") {
          $no_filter = " and transaction_no >= '".$request->no_from."'";
        } else if ($request->no_from == "" && $request->no_to !== "") {
          $no_filter = " and transaction_no <= '".$request->no_to."'";
        } else  {
          $no_filter = "";
        }

        if ($request->payment_status == "all") {
          $payment_status_filter = "";
        } else {
          $payment_status_filter = " and payment_status = '".($request->payment_status == "Paid" ? true : false)."'";

        }

        if ($request->payment_type == "all") {
          $payment_type_filter = "";
        } else {
          $payment_type_filter = " and payment_type = '".$request->payment_type."'";
        }

        if ($request->transaction_type == "all") {
          $transaction_type_filter = "";
        } else {
          $transaction_type_filter = " and transaction_type = '".$request->transaction_type."'";
        }

        $search = "1=1".$date_filter.$no_filter.$payment_status_filter.$payment_type_filter.$transaction_type_filter;
        $results = [];
        $sort = "tr_payment.transaction_no asc";

        $records =Tr_payment::whereRaw($search)
        ->join('tr_payment_detail as tr_payment_detail', function($join) {
          $join->on('tr_payment_detail.payment_id', '=', 'tr_payment.id');
        })
        ->where('tr_payment.delete_flag',false)
        // ->where('tr_payment.payment_status',true)
        ->orderByRaw($sort)
        ->get(['tr_payment_detail.id',
                'tr_payment.payment_status',
               'tr_payment.transaction_no',
               'tr_payment.transaction_date',
               'tr_payment.payment_type',
               'tr_payment.transaction_type',
               'tr_payment_detail.name',
               'tr_payment_detail.remark',
               'tr_payment_detail.qty',
               'tr_payment_detail.price',
               'tr_payment_detail.total'
               ]);

        foreach ($records as $key => $value) {
          $result['id'] = $value['id'];
          $result['payment_status'] = ($value['payment_status'] == 1 ? 'Paid' : 'Unpaid' );
          $result['transaction_no'] = $value['transaction_no'];
          $result['transaction_date'] = ($value['payment_status'] == 1 ? date_format(date_create($value['transaction_date']),"d M Y") : '' );
          $result['payment_type'] =($value['payment_status'] == 1 ? $value['payment_type'] : '' );
          $result['transaction_type'] = ($value['payment_status'] == 1 ? $value['transaction_type'] : '' );
          $result['name'] = ($value['payment_status'] == 1 ? $value['name'] : '' );
          $result['remark'] = ($value['payment_status'] == 1 ? $value['remark'] : '' );
          $result['qty'] = ($value['payment_status'] == 1 ? $value['qty'] : 0 );
          $result['price'] = ($value['payment_status'] == 1 ? $value['price'] : 0 );
          $result['total'] = ($value['payment_status'] == 1 ? $value['total'] : 0 );
          array_push($results,$result);
        }

        $total = $records->sum('total');

        $res = [];
        $res['row'] = $results;
        $res['footers'] = [
                          ['align' => 'right', 'type'=> 'text','hide'=>false,'value' => 'Total'],
                          ['align' => 'right', 'type'=> 'text','hide'=>false,'value' => ''],
                          ['align' => 'right', 'type'=> 'text','hide'=>false,'value' => ''],
                          ['align' => 'right', 'type'=> 'text','hide'=>false,'value' => ''],
                          ['align' => 'right', 'type'=> 'text','hide'=>false,'value' => ''],
                          ['align' => 'right', 'type'=> 'text','hide'=>false,'value' => ''],
                          ['align' => 'right', 'type'=> 'text','hide'=>false,'value' => ''],
                          ['align' => 'right', 'type'=> 'text','hide'=>false,'value' => ''],
                          ['align' => 'right', 'type'=> 'text','hide'=>false,'value' => ''],
                          ['align' => 'right', 'type'=> 'text','hide'=>false,'value' => ''],
                          ['align' => 'right', 'type'=> 'currency','hide'=>false,'value' => $total],
                        ];

        return $res;
    }

    public function kg_report(Request $request) {
        $date_filter = " and transaction_date >= '".$request->date_from."' and transaction_date <= '".$request->date_to."'";

        if ($request->no_from !== "" && $request->no_to !== "") {
          $no_filter = " and transaction_no >= '".$request->no_from."' and transaction_no <= '".$request->no_to."'";
        } else if ($request->no_from !== "" && $request->no_to == "") {
          $no_filter = " and transaction_no >= '".$request->no_from."'";
        } else if ($request->no_from == "" && $request->no_to !== "") {
          $no_filter = " and transaction_no <= '".$request->no_to."'";
        } else  {
          $no_filter = "";
        }

        if ($request->payment_status == "all") {
          $payment_status_filter = "";
        } else {
          $payment_status_filter = " and payment_status = '".($request->payment_status == "Paid" ? true : false)."'";

        }

        if ($request->payment_type == "all") {
          $payment_type_filter = "";
        } else {
          $payment_type_filter = " and payment_type = '".$request->payment_type."'";
        }

        if ($request->transaction_type == "all") {
          $transaction_type_filter = "";
        } else {
          $transaction_type_filter = " and transaction_type = '".$request->transaction_type."'";
        }

        $search = "1=1".$date_filter.$no_filter.$payment_status_filter.$payment_type_filter.$transaction_type_filter;
        $results = [];
        $sort = "tr_payment.transaction_no asc";

        $records =Tr_payment::whereRaw($search)
        ->where('delete_flag',false)
        ->where('payment_status',true)
        ->orderByRaw($sort)
        ->get(['tr_payment.id',
                'tr_payment.payment_status',
               'tr_payment.transaction_no',
               'tr_payment.transaction_date',
               'tr_payment.payment_type',
               'tr_payment.transaction_type',
               'tr_payment.quantity_kg'
               ]);

        foreach ($records as $key => $value) {
          $result['id'] = $value['id'];
          $result['payment_status'] = ($value['payment_status'] == 1 ? 'Paid' : 'Unpaid' );
          $result['transaction_no'] = $value['transaction_no'];
          $result['transaction_date'] = ($value['payment_status'] == 1 ? date_format(date_create($value['transaction_date']),"d M Y") : '' );
          $result['payment_type'] =($value['payment_status'] == 1 ? $value['payment_type'] : '' );
          $result['transaction_type'] = ($value['payment_status'] == 1 ? $value['transaction_type'] : '' );
          $result['quantity_kg'] = ($value['payment_status'] == 1 ? $value['quantity_kg'] : 0 );
          array_push($results,$result);
        }

        $total = $records->sum('quantity_kg');

        $res = [];
        $res['row'] = $results;
        $res['footers'] = [
                          ['align' => 'right', 'type'=> 'text','hide'=>false,'value' => 'Total'],
                          ['align' => 'right', 'type'=> 'text','hide'=>false,'value' => ''],
                          ['align' => 'right', 'type'=> 'text','hide'=>false,'value' => ''],
                          ['align' => 'right', 'type'=> 'text','hide'=>false,'value' => ''],
                          ['align' => 'right', 'type'=> 'text','hide'=>false,'value' => ''],
                          ['align' => 'right', 'type'=> 'text','hide'=>false,'value' => ''],
                          ['align' => 'right', 'type'=> 'text','hide'=>false,'value' => $total],
                        ];

        return $res;
    }


    public function closing_report(Request $request) {
        $date_filter = " and closing_date >= '".$request->date_from."' and closing_date <= '".$request->date_to."'";

        // if ($request->no_from !== "" && $request->no_to !== "") {
        //   $no_filter = " and transaction_no >= '".$request->no_from."' and transaction_no <= '".$request->no_to."'";
        // } else if ($request->no_from !== "" && $request->no_to == "") {
        //   $no_filter = " and transaction_no >= '".$request->no_from."'";
        // } else if ($request->no_from == "" && $request->no_to !== "") {
        //   $no_filter = " and transaction_no <= '".$request->no_to."'";
        // } else  {
        //   $no_filter = "";
        // }

        // if ($request->payment_status == "all") {
        //   $payment_status_filter = "";
        // } else {
        //   $payment_status_filter = " and payment_status = '".($request->payment_status == "Paid" ? true : false)."'";
        //
        // }
        //
        // if ($request->payment_type == "all") {
        //   $payment_type_filter = "";
        // } else {
        //   $payment_type_filter = " and payment_type = '".$request->payment_type."'";
        // }
        //
        // if ($request->transaction_type == "all") {
        //   $transaction_type_filter = "";
        // } else {
        //   $transaction_type_filter = " and transaction_type = '".$request->transaction_type."'";
        // }

        $search = "1=1".$date_filter;
        $results = [];
        $sort = "tr_closing_day.closing_date asc";

        $records = Tr_closing_day::whereRaw($search)
        ->where('delete_flag',false)
        ->orderByRaw($sort)
        ->get(['tr_closing_day.id',
                'tr_closing_day.closing_date',
               'tr_closing_day.beginning_balace',
               'tr_closing_day.cash_total',
               'tr_closing_day.debit_total',
               'tr_closing_day.expense_total',
               'tr_closing_day.take_total',
               'tr_closing_day.end_balance'
               ]);

        foreach ($records as $key => $value) {
          $result['id'] =  $value['id'];
          $result['closing_date'] = date_format(date_create($value['closing_date']),"d M Y");
          $result['beginning_balace'] = $value['beginning_balace'];
          $result['cash_total'] = $value['cash_total'];
          $result['debit_total'] =$value['debit_total'];
          $result['expense_total'] = $value['expense_total'];
          $result['take_total'] = $value['take_total'];
          $result['end_balance'] = $value['end_balance'];
          array_push($results,$result);
        }

        // print_r($results);


        $cash_total = $records->sum('cash_total');
        $debit_total = $records->sum('debit_total');
        $expense_total = $records->sum('expense_total');
        $take_total = $records->sum('take_total');

        $res = [];
        $res['row'] = $results;
        $res['footers'] = [
                        ['align' => 'right', 'type'=> 'text','hide'=>false,'value' => 'Total'],
                          ['align' => 'right', 'type'=> 'text','hide'=>false,'value' => ''],
                          ['align' => 'right', 'type'=> 'text','hide'=>false,'value' => ''],
                          ['align' => 'right', 'type'=> 'currency','hide'=>false,'value' => $cash_total],
                          ['align' => 'right', 'type'=> 'currency','hide'=>false,'value' => $debit_total],
                          ['align' => 'right', 'type'=> 'currency','hide'=>false,'value' => $expense_total],
                          ['align' => 'right', 'type'=> 'currency','hide'=>false,'value' => $take_total],
                          ['align' => 'right', 'type'=> 'text','hide'=>false,'value' => ''],
                        ];

        return $res;
    }


    public function active_member_report(Request $request) {
        $date_filter = " and expired_date >= '".$request->date_from."' and expired_date <= '".$request->date_to."'";

        if ($request->no_from !== "" && $request->no_to !== "") {
          $no_filter = " and member_no >= '".$request->no_from."' and member_no <= '".$request->no_to."'";
        } else if ($request->no_from !== "" && $request->no_to == "") {
          $no_filter = " and member_no >= '".$request->no_from."'";
        } else if ($request->no_from == "" && $request->no_to !== "") {
          $no_filter = " and member_no <= '".$request->no_to."'";
        } else  {
          $no_filter = "";
        }

        if ($request->customer == "") {
          $customer_filter = "";
        } else {
          $customer_filter = " and mt_customer.name = '".$request->customer."'";
        }

        $search = "1=1".$date_filter.$no_filter.$customer_filter;
        $results = [];
        $sort = "tr_member.member_no asc";

        $records =Tr_member::whereRaw($search)
        ->leftJoin('mt_customer as mt_customer', function($join) {
          $join->on('tr_member.customer_id', '=', 'mt_customer.id');
        })
        ->leftJoin('mt_packet as mt_packet', function($join) {
          $join->on('tr_member.packet_id', '=', 'mt_packet.id');
        })
        ->leftJoin(
           DB::raw('(SELECT member_id, payment_status, SUM(quantity_kg) AS quantity_kg FROM tr_payment GROUP BY member_id, payment_status) as v'), function($join) {
             $join->on('v.member_id', '=', 'tr_member.id')
                  ->on('v.payment_status', '=', DB::raw('true'));
           })
        ->where('tr_member.delete_flag',false)
        ->where('tr_member.member_status',true)
        ->where('tr_member.disable_flag',false)
        ->orderByRaw($sort)
        ->get([ 'tr_member.id',
                'tr_member.member_no',
               'mt_customer.name as customer',
               'tr_member.start_date',
               'tr_member.duration',
               'tr_member.added_duration',
               'tr_member.custom_kg',
               'tr_member.member_kg',
               'tr_member.total_kg',
               'tr_member.expired_date',
               DB::raw('tr_member.total_kg - IFNULL(v.quantity_kg,0) as remaining')
               ]);

        foreach ($records as $key => $value) {
          $result['id'] = $value['id'];
          $result['member_no'] = $value['member_no'];
          $result['customer'] = $value['customer'];
          $result['start_date'] = date_format(date_create($value['start_date']),"d M Y");
          $result['duration'] = $value['duration'] + $value['added_duration'];
          $result['custom_kg'] = $value['custom_kg'];
          $result['member_kg'] = $value['member_kg'];
          $result['total_kg'] = $value['total_kg'];
          $result['expired_date'] = date_format(date_create($value['expired_date']),"d M Y");
          $result['remaining'] = $value['remaining'];
          array_push($results,$result);
        }


        $custom_kg = $records->sum('custom_kg');
        $member_kg = $records->sum('member_kg');
        $total_kg = $records->sum('total_kg');
        $remaining = $records->sum('remaining');

        $res = [];
        $res['row'] = $results;
        $res['footers'] = [
                        ['align' => 'right', 'type'=> 'text','hide'=>false,'value' => 'Total'],
                          ['align' => 'right', 'type'=> 'text','hide'=>false,'value' => ''],
                          ['align' => 'right', 'type'=> 'text','hide'=>false,'value' => ''],
                          ['align' => 'right', 'type'=> 'text','hide'=>false,'value' => ''],
                          ['align' => 'right', 'type'=> 'text','hide'=>false,'value' => ''],
                          ['align' => 'right', 'type'=> 'currency','hide'=>false,'value' => $custom_kg],
                          ['align' => 'right', 'type'=> 'currency','hide'=>false,'value' => $member_kg],
                          ['align' => 'right', 'type'=> 'currency','hide'=>false,'value' => $total_kg],
                          ['align' => 'right', 'type'=> 'text','hide'=>false,'value' => ''],
                          ['align' => 'right', 'type'=> 'currency','hide'=>false,'value' => $remaining],
                        ];

        return $res;
    }


    public function rak_report(Request $request) {
      $date_filter = " and transaction_date >= '".$request->date_from."' and transaction_date <= '".$request->date_to."'";

      if ($request->no_from !== "" && $request->no_to !== "") {
        $no_filter = " and transaction_no >= '".$request->no_from."' and transaction_no <= '".$request->no_to."'";
      } else if ($request->no_from !== "" && $request->no_to == "") {
        $no_filter = " and transaction_no >= '".$request->no_from."'";
      } else if ($request->no_from == "" && $request->no_to !== "") {
        $no_filter = " and transaction_no <= '".$request->no_to."'";
      } else  {
        $no_filter = "";
      }

      if ($request->rak == "") {
        $rak_filter = "";
      } else {
        $rak_filter = " and mt_rak.name = '".$request->rak."'";
      }

      $search = "1=1".$date_filter.$no_filter.$rak_filter;
      $results = [];
      $sort = "mt_rak.name asc";

      $records = Tr_payment::whereRaw($search)
      ->leftJoin('mt_rak as mt_rak', function($join) {
        $join->on('tr_payment.rak_id', '=', 'mt_rak.id');
      })
      ->where('tr_payment.delete_flag',false)
      ->where('tr_payment.rak_id','<>',0)
      ->where('tr_payment.taken_flag',false)
      ->orderByRaw($sort)
      ->get([ 'tr_payment.id',
              'tr_payment.transaction_date',
              'tr_payment.transaction_no',
             'mt_rak.name as rak'
             ]);

      foreach ($records as $key => $value) {
        $result['id'] = $value['id'];
        $result['transaction_date'] = date_format(date_create($value['transaction_date']),"d M Y");
        $result['rak'] = $value['rak'];
        $result['transaction_no'] = $value['transaction_no'];
        array_push($results,$result);
      }

      $res = [];
      $res['row'] = $results;
      $res['footers'] = [
        ['align' => 'right', 'type'=> 'text','hide'=>false,'value' => ''],
        ['align' => 'right', 'type'=> 'text','hide'=>false,'value' => ''],
        ['align' => 'right', 'type'=> 'text','hide'=>false,'value' => ''],
        ['align' => 'right', 'type'=> 'text','hide'=>false,'value' => '']
      ];

      return $res;
    }

    public function pengambilan_report(Request $request) {
        $date_filter = " and transaction_date >= '".$request->date_from."' and transaction_date <= '".$request->date_to."'";

        if ($request->no_from !== "" && $request->no_to !== "") {
          $no_filter = " and transaction_no >= '".$request->no_from."' and transaction_no <= '".$request->no_to."'";
        } else if ($request->no_from !== "" && $request->no_to == "") {
          $no_filter = " and transaction_no >= '".$request->no_from."'";
        } else if ($request->no_from == "" && $request->no_to !== "") {
          $no_filter = " and transaction_no <= '".$request->no_to."'";
        } else  {
          $no_filter = "";
        }

        if ($request->rak == "") {
          $rak_filter = "";
        } else {
          $rak_filter = " and mt_rak.name = '".$request->rak."'";
        }

        $search = "1=1".$date_filter.$no_filter.$rak_filter;
        $results = [];
        $sort = "tr_payment.transaction_no asc";

        $records = Tr_payment::whereRaw($search)
        ->leftJoin('mt_rak as mt_rak', function($join) {
          $join->on('tr_payment.rak_id', '=', 'mt_rak.id');
        })
        ->where('tr_payment.delete_flag',false)
        ->where('tr_payment.rak_id','<>',0)
        ->where('tr_payment.taken_flag',false)
        ->orderByRaw($sort)
        ->get([ 'tr_payment.id',
                'tr_payment.transaction_no',
               'mt_rak.name as rak'
               ]);

        foreach ($records as $key => $value) {
          $result['id'] = $value['id'];
          $result['transaction_no'] = $value['transaction_no'];
          $result['rak'] = $value['rak'];
          array_push($results,$result);
        }

        $res = [];
        $res['row'] = $results;
        $res['footers'] = [
          ['align' => 'right', 'type'=> 'text','hide'=>false,'value' => ''],
          ['align' => 'right', 'type'=> 'text','hide'=>false,'value' => ''],
          ['align' => 'right', 'type'=> 'text','hide'=>false,'value' => '']
        ];

        return $res;
    }




}
