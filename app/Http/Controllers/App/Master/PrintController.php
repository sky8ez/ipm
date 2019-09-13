<?php

namespace App\Http\Controllers\App\Master;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Mt_print;
use App\Mt_print_detail;
use App\Mt_print_property;
use App\Mt_table_filter;
use App\Tr_activity_log;
use App\Tr_member;
use App\Tr_payment;
use App\Tr_closing_day;
use Validator;
use DB;
use Schema;
use Response;
use App\Tr_audit;
use Illuminate\Support\Facades\Input;
use App\Repositories\ValidatorRepository;
use App\Repositories\AccessRepository;
use Session;
use Exception;

class PrintController extends Controller
{
  private $form_id = "PRINT";

  private $validator;
  private $access;
  private $access_list = [];


    public function __construct(ValidatorRepository $validator, AccessRepository $access)
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
      $header = ['label' => trans('print.name'),'value' => 'mt_print.name', 'type' => 'text', 'table' => '', 'sort' => 'sort'];
      array_push($headers,$header);
      $header = ['label' => trans('print.category'),'value' => 'mt_print.category', 'type' => 'text', 'table' => '', 'sort' => 'sort'];
      array_push($headers,$header);
      $header = ['label' => trans('print.detail'),'value' => '', 'type' => 'button', 'table' => '', 'sort' => 'sort'];
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

      $fetchs = Mt_print::whereRaw($search)
      ->where('delete_flag',false)
      ->orderByRaw($sort)
      ->skip(25 * $skip)
      ->take(25)
      ->get();

      if ($skip == 0) {
        $customers_count = Mt_print::whereRaw($search)
        ->where('delete_flag',false)
        ->count();
      } else {
        $customers_count = 0;
      }

      $datas = [];

      foreach ($fetchs as $fetch  ) {
        $row = ['id' => $fetch->id,
                 'records' => [
                      ['type' => 'text' , 'value' => $fetch->name],
                      ['type' => 'text' , 'value' => $fetch->category],
                      ['type' => 'button' , 'value' => 'Detail', 'link' => '#/form/print-template/'.$fetch->id.'/editor'],
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
      $data = Mt_print::where('mt_print.id',$id)
      ->first(['mt_print.*']);
    }

    $forms = [];
    $form = ['label' => 'Name','placeholder' => 'Name',
             'type'  => 'text','name' => 'name',
             'required' => 'true',
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'value' => ($cond=="insert" ? '' : $data->name ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Category','placeholder' => 'Category',
             'type'  => 'select','name' => 'category',
             'options' => ['payment','member','closing-day'],
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'value' => ($cond=="insert" ? '' : $data->category ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Paper Size','placeholder' => 'Paper Size',
             'type'  => 'select','name' => 'paper_size',
             'options' => ['A4','A5','Letter','Slip','Struct'],
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'value' => ($cond=="insert" ? '' : $data->paper_size ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Paper Orientation','placeholder' => 'Paper Orientation',
             'type'  => 'select','name' => 'paper_orientation',
             'options' => ['Landscape','Portrait'],
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'value' => ($cond=="insert" ? '' : $data->paper_orientation ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Margin Top','placeholder' => 'Margin Top',
             'type'  => 'text','name' => 'margin_top',
             'col' => ['row' => 6, 'col1' => 4, 'col2' => 8],
             'value' => ($cond=="insert" ? '0px' : $data->margin_top ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Margin Left','placeholder' => 'Margin Left',
             'type'  => 'text','name' => 'margin_left',
             'col' => ['row' => 6, 'col1' => 4, 'col2' => 8],
             'value' => ($cond=="insert" ? '0px' : $data->margin_left ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Margin Bottom','placeholder' => 'Margin Bottom',
             'type'  => 'text','name' => 'margin_bottom',
             'col' => ['row' => 6, 'col1' => 4, 'col2' => 8],
             'value' => ($cond=="insert" ? '0px' : $data->margin_bottom ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Margin Right','placeholder' => 'Margin Right',
             'type'  => 'text','name' => 'margin_right',
             'col' => ['row' => 6, 'col1' => 4, 'col2' => 8],
             'value' => ($cond=="insert" ? '0px' : $data->margin_right ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Header Height','placeholder' => 'Header Height',
             'type'  => 'text','name' => 'header_height',
             'col' => ['row' => 6, 'col1' => 4, 'col2' => 8],
             'value' => ($cond=="insert" ? '25px' : $data->header_height ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Row Height','placeholder' => 'Row Height',
             'type'  => 'text','name' => 'row_height',
             'col' => ['row' => 6, 'col1' => 4, 'col2' => 8],
             'value' => ($cond=="insert" ? '23px' : $data->row_height ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Table Top','placeholder' => 'Table Top',
             'type'  => 'text','name' => 'table_top',
             'col' => ['row' => 6, 'col1' => 4, 'col2' => 8],
             'value' => ($cond=="insert" ? '75px' : $data->table_top ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Table Row Count','placeholder' => 'Table Row Count',
             'type'  => 'text','name' => 'table_row_count',
             'col' => ['row' => 6, 'col1' => 4, 'col2' => 8],
             'value' => ($cond=="insert" ? '10' : $data->table_row_count ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Table Border Style','placeholder' => 'Table Border Style',
             'type'  => 'select','name' => 'table_border_style',
             'options' => ['Full','Vertical','Horizontal','None'],
             'col' => ['row' => 6, 'col1' => 4, 'col2' => 8],
             'value' => ($cond=="insert" ? '' : $data->table_border_style ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Font Family','placeholder' => 'Font Family',
             'type'  => 'text','name' => 'font_family',
             'col' => ['row' => 6, 'col1' => 4, 'col2' => 8],
             'value' => ($cond=="insert" ? '' : $data->font_family ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Font Size','placeholder' => 'Font Size',
             'type'  => 'text','name' => 'font_size',
             'col' => ['row' => 6, 'col1' => 4, 'col2' => 8],
             'value' => ($cond=="insert" ? '' : $data->font_size ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Default Flag','placeholder' => 'Default Flag',
             'type'  => 'checkbox','name' => 'default_flag',
             'col' => ['row' => 6, 'col1' => 4, 'col2' => 8],
             'value' => ($cond=="insert" ? true : ($data->default_flag == 1 ? true : false) ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Active Flag','placeholder' => 'Active Flag',
             'type'  => 'checkbox','name' => 'active_flag',
             'col' => ['row' => 6, 'col1' => 4, 'col2' => 8],
             'value' => ($cond=="insert" ? true : ($data->active_flag == 1 ? true : false) ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Header Flag','placeholder' => 'Header Flag',
             'type'  => 'checkbox','name' => 'header_flag',
             'col' => ['row' => 6, 'col1' => 4, 'col2' => 8],
             'value' => ($cond=="insert" ? true : ($data->header_flag == 1 ? true : false) ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Footer Flag','placeholder' => 'Footer Flag',
             'type'  => 'checkbox','name' => 'footer_flag',
             'col' => ['row' => 6, 'col1' => 4, 'col2' => 8],
             'value' => ($cond=="insert" ? true : ($data->footer_flag == 1 ? true : false) ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'First Header Flag','placeholder' => 'First Header Flag',
             'type'  => 'checkbox','name' => 'first_header_flag',
             'col' => ['row' => 6, 'col1' => 4, 'col2' => 8],
             'value' => ($cond=="insert" ? true : ($data->first_header_flag == 1 ? true : false) ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Last Footer Flag','placeholder' => 'Last Footer Flag',
             'type'  => 'checkbox','name' => 'last_footer_flag',
             'col' => ['row' => 6, 'col1' => 4, 'col2' => 8],
             'value' => ($cond=="insert" ? true : ($data->last_footer_flag == 1 ? true : false) ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Has Detail','placeholder' => 'Has Detail',
             'type'  => 'checkbox','name' => 'has_detail',
             'col' => ['row' => 6, 'col1' => 4, 'col2' => 8],
             'value' => ($cond=="insert" ? true : ($data->has_detail == 1 ? true : false) ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Header Query','placeholder' => 'Header Query',
             'type'  => 'multitext','name' => 'header_query',
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'value' => ($cond=="insert" ? '' : $data->header_query ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Detail Query','placeholder' => 'Detail Query',
             'type'  => 'multitext','name' => 'detail_query',
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'value' => ($cond=="insert" ? '' : $data->detail_query ),
           ];
    array_push($forms,$form);
    // $form = ['label' => 'Footer Query','placeholder' => 'Footer Query',
    //          'type'  => 'multitext','name' => 'footer_query',
    //          'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
    //          'value' => ($cond=="insert" ? '' : $data->footer_query ),
    //        ];
    // array_push($forms,$form);


    $result = [
      'forms' =>  $forms,
      'form_id' => $this->form_id,
      'form_table' => 'print',
      'data_before' => $data,
      'access_list' => $this->access_list,
      'role' => Session::get('role'),
    ];

    return Response::json($result);
  }

  //POT
  public function setPrintFlag(Request $request, $category)
  {
    switch ($category) {
      case 'payment':
        $record = Tr_payment::find($request->transaction_id);
        if (isset($record)) {
          $record->print_status = true;
          DB::beginTransaction();
          try {
            $record->update();

            $activity_log = new Tr_activity_log;
            $activity_log->activity_date = Date('y-m-d H:i:s');
            $activity_log->transaction_category = 'payment';
            $activity_log->transaction_id = $request->transaction_id;
            $activity_log->user_id = $request->session()->get('user_id');
            $activity_log->action = 'PRINT';
            $activity_log->ip = $request->ip();
            $activity_log->browser = $request->header('User-Agent');
            $activity_log->save();

            DB::commit();
            $arr = array('code' => "200", 'status' => "OK", 'url' => "/list/payment");
            return json_encode($arr);
          } catch(\Exception $e){
            DB::rollback();
            http_response_code(404);
            return "Delete Fail";
          }
        }
        break;
      case 'member':
        $record = Tr_member::find($request->transaction_id);
        if (isset($record)) {
          $record->print_status = true;
          DB::beginTransaction();
          try {
            $record->update();

            $activity_log = new Tr_activity_log;
            $activity_log->activity_date = Date('y-m-d H:i:s');
            $activity_log->transaction_category = 'member';
            $activity_log->transaction_id = $request->transaction_id;
            $activity_log->user_id = $request->session()->get('user_id');
            $activity_log->action = 'PRINT';
            $activity_log->ip = $request->ip();
            $activity_log->browser = $request->header('User-Agent');
            $activity_log->save();

            DB::commit();
            $arr = array('code' => "200", 'status' => "OK", 'url' => "/list/member");
            return json_encode($arr);
          } catch(\Exception $e){
            DB::rollback();
            http_response_code(404);
            return "Delete Fail";
          }
        }
        break;
      case 'closing-day':
        $record = Tr_closing_day::find($request->transaction_id);
        if (isset($record)) {
          $record->print_status = true;
          DB::beginTransaction();
          try {
            $record->update();

            $activity_log = new Tr_activity_log;
            $activity_log->activity_date = Date('y-m-d H:i:s');
            $activity_log->transaction_category = 'closing-day';
            $activity_log->transaction_id = $request->transaction_id;
            $activity_log->user_id = $request->session()->get('user_id');
            $activity_log->action = 'PRINT';
            $activity_log->ip = $request->ip();
            $activity_log->browser = $request->header('User-Agent');
            $activity_log->save();

            DB::commit();
            $arr = array('code' => "200", 'status' => "OK", 'url' => "/list/closing-day");
            return json_encode($arr);
          } catch(\Exception $e){
            DB::rollback();
            http_response_code(404);
            return "Delete Fail";
          }
        }
        break;
      default:
        # code...
        break;
    }

  }


  public function getPrintTemplate($form) {
    $print = Mt_print::where('category',$form)->where('delete_flag',false)->get();
    $arr = array(
      'print' => $print
    );
    return json_encode($arr);
  }


  public function checkPrint($form, $id)
  {
    try {
      if (Session::get('print_limit') !== 0) {
        $print_count = Tr_activity_log::where('transaction_category',$form)
                                      ->where('transaction_id',$id)
                                       ->where('user_id',Session::get('user_id'))
                                       ->where('action','PRINT')
                                       ->count();

        if ($print_count >= Session::get('print_limit')) {
          $error = 'Anda tidak dapat melakukan print karena sudah melewati limit print';
          // throw new Exception($error);
          $arr = array('code' => "200", 'status' => "error",'msg' => $error);
          return json_encode($arr);
        } else {
          $arr = array('code' => "200", 'status' => "OK",'msg' => $print_count);
          return json_encode($arr);
        }
      }
    } catch (Exception $e) {
      $arr = array('code' => "200", 'status' => "FAIL", 'msg' => $e->getMessage());
      return json_encode($arr);
    }


  }

  public function getRowCount($form, $id, $print_id = "") {
    $record_details = [];
    $result = [];
    switch ($form) {
      case 'payment':
        $record_details = DB::table('tr_payment_detail')->where('payment_id',$id)
                     ->get(['tr_payment_detail.*']);

        break;
      case 'member':

        break;
      case 'closing-day':

        $record_details = DB::table('tr_closing_day_detail')->where('closing_day_id',$id)
                     ->get(['tr_closing_day_detail.*']);
        break;
      default:
        # code...
        break;
    }

    $i = 1;

    array_push($result,count($record_details));
    if ($print_id <> "") {
       $print = Mt_print::where('category',$form)->where('delete_flag',false)->where("id",$print_id)->first();
    } else {
       $print = Mt_print::where('category',$form)->where('delete_flag',false)->first();
    }
    array_push($result,$print->table_row_count);

    return json_encode($result);

  }

  public function getPrint($form, $id, $print_id = "") {

      if ($print_id <> "") {
         $print = Mt_print::where('category',$form)->where('delete_flag',false)->where("id",$print_id)->first();
      } else {
         $print = Mt_print::where('category',$form)->where('delete_flag',false)->first();
      }

      if (isset($print)) {
        //->orderBy('sequence_no','asc')
        $print_details = Mt_print_detail::where('print_id',$print->id)->orderBy('sequence_no','asc')->get();
        $print_details_header = Mt_print_detail::where('print_id',$print->id)
                                              ->orderBy('sequence_no','asc')
                                              ->where('kind','header')->get();
        $print_details_detail = Mt_print_detail::where('print_id',$print->id)
                                              ->orderBy('sequence_no','asc')
                                              ->where('kind','detail')->get();
        $print_properties = Mt_print_property::where('print_id',$print->id)->get();


        $record_details = [];
        switch ($form) {
          case 'payment':
            $record = DB::table('tr_payment')->where('tr_payment.id',$id)
                          ->leftJoin('mt_customer as mt_customer', function($join) {
                            $join->on('tr_payment.customer_id', '=', 'mt_customer.id');
                          })
                          ->leftJoin('mt_packet as mt_packet', function($join) {
                            $join->on('tr_payment.packet_id', '=', 'mt_packet.id');
                          })
                          ->leftJoin('tr_member as tr_member', function($join) {
                            $join->on('tr_payment.member_id', '=', 'tr_member.id');
                          })
                          ->first(['tr_payment.*','mt_customer.name as customer','mt_packet.name as packet','mt_packet.price as packet_price','tr_member.member_no as member']);

            $record_details = DB::table('tr_payment_detail')->where('payment_id',$id)
                         ->get(['tr_payment_detail.*']);

            break;
          case 'member':
            $record = DB::table('tr_member')->where('tr_member.id',$id)
                ->leftJoin('mt_customer as mt_customer', function($join) {
                  $join->on('tr_member.customer_id', '=', 'mt_customer.id');
                })
                ->leftJoin('mt_packet as mt_packet', function($join) {
                  $join->on('tr_member.packet_id', '=', 'mt_packet.id');
                })
                ->first(['tr_member.*','mt_customer.name as customer','mt_packet.name as packet']);
            break;
          case 'closing-day':
            $record = DB::table('tr_closing_day')->where('id',$id)
                          ->first(['tr_closing_day.*']);

            $record_details = DB::table('tr_closing_day_detail')->where('closing_day_id',$id)
                         ->get(['tr_closing_day_detail.*']);
            break;
          default:
            # code...
            break;
        }

        $record_array =json_decode(json_encode($record), true);
        $record_details_array = json_decode(json_encode($record_details), true);

        $details = [];

        //untuk record detail
        for($i=0;$i<count($record_details_array);$i++) {
          $detail = [];
          foreach ($print_details_detail as $print_detail) {
            if (str_contains($print_detail->value,'@') && $print_detail->kind == 'detail') {
                $value = $record_details_array[$i][str_replace('@','',$print_detail->value)];
                if (is_numeric($value)) {
                  $value = number_format($value,2,",",".");
                  array_push($detail,$value);
                } else {
                  array_push($detail,$value);
                }
            } elseif (str_contains($print_detail->value,'#') && $print_detail->kind == 'detail') {
                array_push($detail,($i+1));
              }
            }
          array_push($details,$detail);
        }

        //untuk record header / footer
        foreach ($print_details as $print_detail) {
          if (str_contains($print_detail->value,'@') && $print_detail->kind !== 'detail') {
            $print_detail->value;
            switch ($print_detail->value_type) {
              case 'currency':
                $print_detail->value = number_format($record_array[str_replace('@','',$print_detail->value)],2);
                break;
              case 'datetime':
                $val = $record_array[str_replace('@','',$print_detail->value)];
                $date = date_create($val);
                $print_detail->value = date_format($date,$print_detail->value_format);
                break;
              default:
                 $print_detail->value = $record_array[str_replace('@','',$print_detail->value)];
                break;
            }

          } elseif ($print_detail->value == "#date_printed")  {
              $print_detail->value = date('d M Y H:i:s');
          } elseif ($print_detail->value == "#user_printed")  {
                  $print_detail->value = $request->session()->get('user_name');
          } else {

          }
        }

        if(isset($record)) {
          $arr = array(
            'data_details' => $details,
            'print' => $print,
            'print_details' => $print_details,
            'print_properties' => $print_properties,
            'id' => $record->id,
            'print_id' => $print_id,
          );
          return json_encode($arr);

        }

      } else {
        echo "No Default Template Set";
      }



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
           $record = Mt_print::create($save_datas);

           $audit = new Tr_audit;
           $audit->transaction_category = 'print';
           $audit->transaction_id = $record->id;
           $audit->status = 'insert';
           $audit->column = "";
           $audit->value_old = "";
           $audit->value_new = "";
           $audit->modified_user_id = $request->session()->get('user_id');
           $audit->save();

          //  $record->save();

           DB::commit();
           $arr = array('code' => "200", 'status' => "OK",'url' => '#/list/print-template');
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


  public function getDetail($id)
  {
    $print_detail = Mt_print_detail::where('print_id',$id)->orderBy('sequence_no','asc')->get();
    $print_properties = Mt_print_property::where('print_id',$id)->get();

    $print = Mt_print::find($id);
    $col_header = [];
    $col_detail = [];
    $columns_header = [];
    $columns_detail = [];

    switch ($print->category) {
      case 'payment':
          $col_header = Schema::getColumnListing('tr_payment');
          // $col_detail = Schema::getColumnListing('tr_filing_detail');

        break;
      case 'member':
          $col_header = Schema::getColumnListing('tr_member');
          // $col_detail = Schema::getColumnListing('tr_handover_detail');

        break;
      case 'closing-day':
          $col_header = Schema::getColumnListing('tr_closing_day');
          $col_detail = Schema::getColumnListing('tr_closing_day_detail');

        break;
      default:
        # code...
        break;
    }

    array_push($columns_header,"#user_printed");
    array_push($columns_header,"#date_printed");
    array_push($columns_header,"#page_number");
    array_push($columns_detail,"#no");

    foreach ($col_header as $col) {
      array_push($columns_header,"@".$col);
    }

    foreach ($col_detail as $col) {
      array_push($columns_detail,"@".$col);
    }


    $arr = array(
      'print' => $print,
      'print_details' => $print_detail,
      'print_properties' => $print_properties,
      'columns_header' => $columns_header,
      'columns_detail' => $columns_detail,
      'id' => $id
    );
    return json_encode($arr);
  }


    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function saveDetail(Request $request, $id)
    {
          $data_header = json_decode($request->header,true);
          $data_details = json_decode($request->detail,true);

          $print = Mt_print::find($id);
          $print->row_height = $data_header['table_row_height'];
          $print->table_top = $data_header['table_top'];
          $print->table_row_count = $data_header['table_row_count'];
          $print->table_border_style = $data_header['table_border_style'];

          $dt_details = Mt_print_detail::where('print_id',$id);

           DB::beginTransaction();
           try {
               $print->update();
               foreach ($data_details as $data_detail) {
                 if ($data_detail['id'] == "") { //insert
                     $detail = new Mt_print_detail;
                     $detail->print_id = $id;
                     $detail->sequence_no = $data_detail['sequence_no'];
                     $detail->kind = $data_detail['kind'];
                     $detail->type = $data_detail['type'];
                     $detail->value = $data_detail['value'];
                     $detail->value_type = $data_detail['value_type'];
                     $detail->value_format = $data_detail['value_format'];
                     $detail->save();

                     foreach($data_detail['properties'] as $key => $value) {
                       $detail_prop = new Mt_print_property;
                       $detail_prop->print_id = $id;
                       $detail_prop->print_detail_id = $detail->id;
                       $detail_prop->category = "";
                       $detail_prop->name = $key;
                       $detail_prop->value = $value;
                       $detail_prop->save();
                     }
                 } else { //update
                    $detail = Mt_print_detail::find($data_detail['id']);
                    if ($data_detail['deleteflag'] == "true") { //dihapus
                       Mt_print_property::where('print_detail_id',$detail->id )->delete();
                       $detail->delete();
                    } else {
                        if (isset($detail)) { //update
                          $detail->print_id = $id;
                          $detail->sequence_no = $data_detail['sequence_no'];
                          $detail->kind = $data_detail['kind'];
                          $detail->type = $data_detail['type'];
                          $detail->value = $data_detail['value'];
                          $detail->value_type = $data_detail['value_type'];
                          $detail->value_format = $data_detail['value_format'];
                          $detail->update();
                        } else { //insert
                          $detail = new Mt_print_detail;
                          $detail->print_id = $id;
                          $detail->sequence_no = $data_detail['sequence_no'];
                          $detail->kind = $data_detail['kind'];
                          $detail->type = $data_detail['type'];
                          $detail->value = $data_detail['value'];
                          $detail->value_type = $data_detail['value_type'];
                          $detail->value_format = $data_detail['value_format'];
                          $detail->save();
                        }

                        foreach($data_detail['properties'] as $key => $value) {
                          $detail_prop = Mt_print_property::where('print_detail_id',$detail->id)
                                                           ->where('name', $key)->first();
                           if (isset($detail_prop)) {
                             $detail_prop->print_id = $id;
                             $detail_prop->print_detail_id = $detail->id;
                             $detail_prop->category = "";
                             $detail_prop->name = $key;
                             $detail_prop->value = $value;
                             $detail_prop->update();
                           } else {
                             $detail_prop = new Mt_print_property;
                             $detail_prop->print_id = $id;
                             $detail_prop->print_detail_id = $detail->id;
                             $detail_prop->category = "";
                             $detail_prop->name = $key;
                             $detail_prop->value = $value;
                             $detail_prop->save();
                           }

                        }
                    }

                 }
              }



             DB::commit();
             $arr = array('code' => "200", 'status' => "OK",'url' => '#/list/print-template', 'tes' => $data_header['table_row_height']);
             return json_encode($arr);
           } catch(\Exception $e){
             DB::rollback();
             $arr = array('code' => "200", 'status' => "FAIL", 'msg' => $e->getMessage());
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
        Mt_print::where('id',$id)
                 ->update($save_datas);

       if ($this->columns !== "") {
         $audit = new Tr_audit;
         $audit->transaction_category = 'print';
         $audit->transaction_id = $id;
         $audit->status = 'update';
         $audit->column = $this->columns;
         $audit->value_old = $this->valuebefore;
         $audit->value_new = $this->valueafter;
         $audit->modified_user_id = $request->session()->get('user_id');
         $audit->save();
       }


        DB::commit();
        $arr = array('code' => "200", 'status' => "OK",'url' => '#/list/print-template');
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

     $record = Mt_print::find($id);
     if(isset($record)) {
       $record->delete_flag = true;
     }

     DB::beginTransaction();
     try {

       $record->update();

       DB::commit();
       $arr = array('code' => "200", 'status' => "OK",'url' => '#/list/print-template');
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
         $record = Mt_print::find($data);
         if(isset($record)) {
            $record->delete_flag = true;
            $record->update();
         }
       }

       DB::commit();
       $arr = array('code' => "200", 'status' => "OK",'url' => '#/list/print-template');
       return json_encode($arr);
     } catch(\Exception $e){
       DB::rollback();
       $arr = array('code' => "200", 'status' => "FAIL", 'msg' => $e->getMessage());
       return json_encode($arr);
     }
  }

}
