<?php

namespace App\Http\Controllers\App\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Tr_payment;
use App\Tr_member;
use Validator;
use DB;
use Response;
use Illuminate\Support\Facades\Input;
use App\Repositories\ValidatorRepository;
use App\Repositories\AccessRepository;
use Session;
use Carbon\Carbon;

class DashboardController extends Controller
{
      /**
       * Send back all datas as JSON
       *
       * @return Response
       */
      private $form_id = "DASHBOARD";

      private $validator;
      private $access;
      private $access_list = [];


      public function __construct(ValidatorRepository $validator,AccessRepository $access)
     {
         $this->validator = $validator;
         $this->access = $access;
     }


     public function index(Request $request)
     {
         $group = $request->group;
         $reportname = $request->report;

         $date_filter = " and transaction_date >= '".$request->date_from."' and transaction_date <= '".$request->date_to."'";

        //  if ($request->no_from !== "" && $request->no_to !== "") {
        //    $no_filter = " and member_no >= '".$request->no_from."' and member_no <= '".$request->no_to."'";
        //  } else if ($request->no_from !== "" && $request->no_to == "") {
        //    $no_filter = " and member_no >= '".$request->no_from."'";
        //  } else if ($request->no_from == "" && $request->no_to !== "") {
        //    $no_filter = " and member_no <= '".$request->no_to."'";
        //  } else  {
        //    $no_filter = "";
        //  }

        $this->access_list = $this->access->getAccess($this->form_id, Session::get('user_access_id'));

        $labels = [];
        $datas = [];
        $search = "1=1".$date_filter;
        $column = "";

        switch ($reportname) {
          case 'Omzet Report':
            $column = "total";
            break;
          case 'Omzet Report Kg':
            $column = "quantity_kg";
            break;
          default:
            # code...
            break;
        }

        switch ($group) {
          case 'day':

          $records = Tr_payment::whereRaw($search)->where('payment_status',true)
                  ->select(array(DB::Raw('sum(tr_payment.'.$column.') as total'),
                  DB::Raw('DATE(tr_payment.transaction_date) day'),
                  // DB::Raw('MONTH(w.transaction_date) month'),
                  // DB::Raw('YEAR(w.transaction_date) year')
                  ))
                  ->groupBy('day')
                  ->orderBy('tr_payment.transaction_date','asc')
                  ->get();

            $date1=date_create($request->date_from);
            $date2=date_create($request->date_to);
            $diff=date_diff($date1,$date2);

            for($i=0;$i<=$diff->days;$i++) {
              $date = $request->date_from;
              $aa =  date('Y-m-d', strtotime($date. ' + '.($i).' days'));
              array_push($labels, $aa);
            }

            $i=0;
            foreach ($labels as $label  ) {
              if ($i <= (count($records) - 1)) {
                if ($records[$i]->day == $label) {
                  array_push($datas, $records[$i]->total);
                  $i = $i + 1;
                } else {
                  array_push($datas, 0);
                }
              } else {
                array_push($datas, 0);
              }
            }
            break;
          case 'week':
            # code...
            break;
          case 'month':
            $records = Tr_payment::whereRaw($search)->where('payment_status',true)
                  ->select(array(DB::Raw('sum(tr_payment.'.$column.') as total'),
                  DB::Raw(" date_format(tr_payment.transaction_date, '%m %y') month"),
                  // DB::Raw('CONCAT(YEAR(tr_payment.transaction_date) ,MONTH(tr_payment.transaction_date)) month'),
                  // DB::Raw('YEAR(tr_payment.transaction_date) year')
                  ))
                  ->groupBy('month')
                  ->orderBy('tr_payment.transaction_date','asc')
                  ->get();

            $date_diff=strtotime($request->date_to)-strtotime($request->date_from);
            $selisih = floor(($date_diff)/2628000);

            for($i=0;$i<=$selisih;$i++) {
              $date = $request->date_from;
              $aa =  date('m y', strtotime($date. ' + '.($i).' months'));
              array_push($labels, $aa);
            }

            $i=0;
            foreach ($labels as $label  ) {
              if ($i <= (count($records) - 1)) {
                if ($records[$i]->month == $label) {
                  array_push($datas, $records[$i]->total);
                  $i = $i + 1;
                } else {
                  array_push($datas, 0);
                }
              } else {
                array_push($datas, 0);
              }
            }
            break;
          case 'year':
              $records = Tr_payment::whereRaw($search)->where('payment_status',true)
                    ->select(array(DB::Raw('sum(tr_payment.'.$column.') as total'),
                    DB::Raw(" date_format(tr_payment.transaction_date, '%Y') year"),
                    // DB::Raw('CONCAT(YEAR(tr_payment.transaction_date) ,MONTH(tr_payment.transaction_date)) month'),
                    // DB::Raw('YEAR(tr_payment.transaction_date) year')
                    ))
                    ->groupBy('year')
                    ->orderBy('tr_payment.transaction_date','asc')
                    ->get();

              $date1=date_create($request->date_from);
              $date2=date_create($request->date_to);
              $diff=date_diff($date1,$date2);

              $interval = $date2->diff($date1);

              $interval->format('%y years');

              for($i=0;$i<=$interval->y;$i++) {
                $date = $request->date_from;
                $aa =  date('Y', strtotime($date. ' + '.($i).' years'));
                array_push($labels, $aa);
              }

              $i=0;
              foreach ($labels as $label  ) {
                if ($i <= (count($records) - 1)) {
                  if ($records[$i]->year == $label) {
                    array_push($datas, $records[$i]->total);
                    $i = $i + 1;
                  } else {
                    array_push($datas, 0);
                  }
                } else {
                  array_push($datas, 0);
                }
              }
            break;

          default:
            # code...
            break;
        }



        // $records = Tr_payment::whereRaw($search)->where('payment_status',true)
        // ->get(['tr_payment.total'])
        // ->groupBy(function($date) {
        //       return Carbon::parse($date->created_at)->format('Y'); // grouping by years
        //       //return Carbon::parse($date->created_at)->format('m'); // grouping by months
        //   });



         $result = [
           'labels' =>  $labels,
           'datas' =>  $datas,
           'form_id' => $this->form_id,
           'access_list' => $this->access_list,
           'role' => Session::get('role'),
         ];

         return Response::json($result);
     }

}
