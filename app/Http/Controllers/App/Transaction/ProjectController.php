<?php

namespace App\Http\Controllers\App\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Tr_project;
use App\Tr_project_detail;
use App\Tr_project_expense;
use App\Tr_project_material;
use App\Mt_material;
use App\Mt_pattern;
use App\Mt_pattern_detail;
use App\Mt_item;
use App\Mt_item_detail;
use App\Mt_table_filter;
use Validator;
use DB;
use Response;
use Exception;
use Session;
use File;
use App\Tr_audit;
use Illuminate\Support\Facades\Input;
use App\Repositories\ValidatorRepository;
use App\Repositories\AccessRepository;
use App\Repositories\BinRepository;

class ProjectController extends Controller
{
  /**
   * Send back all datas as JSON
   *
   * @return Response
   */
  private $form_id = "PROJECT";

  private $validator;
  private $bin;
  private $access;
  private $access_list = [];

  public function __construct(ValidatorRepository $validator,AccessRepository $access, BinRepository $bin)
 {
     $this->validator = $validator;
     $this->access = $access;
     $this->bin = $bin;
 }

  public function index($skip = 0)
  {
      $this->access_list = $this->access->getAccess($this->form_id, Session::get('user_access_id'));

      $search = "1=1";
      $sort = "tr_project.id desc";
      $filters = Mt_table_filter::where('form_id',$this->form_id)
                                ->get();

      $headers = [];
      $header = ['label' => trans('project.project_date'),'value' => 'tr_project.project_date', 'type' => 'text', 'table' => '', 'sort' => 'sort'];
      array_push($headers,$header);
      $header = ['label' => trans('project.item_name'),'value' => 'mt_item.item_name', 'type' => 'text', 'table' => '', 'sort' => 'sort'];
      $header = ['label' => trans('project.price'),'value' => 'tr_project.price', 'type' => 'text', 'table' => '', 'sort' => 'sort'];
      array_push($headers,$header);
      $header = ['label' => trans('project.updated_at'),'value' => 'tr_project.updated_at', 'type' => 'text', 'table' => '', 'sort' => 'sort'];
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

      $records = Tr_project::whereRaw($search)
      ->leftJoin('mt_item as mt_item', function($join) {
        $join->on('tr_project.item_id', '=', 'mt_item.id');
      })
      ->where('tr_project.delete_flag',false)
      ->orderByRaw($sort)
      ->skip(25 * $skip)
      ->take(25)
      ->get(['tr_project.*','mt_item.item_name']);

      if ($skip == 0) {
        $records_count = Tr_project::whereRaw($search)
        ->where('delete_flag',false)
        ->count();
      } else {
        $records_count = 0;
      }


      $datas = [];

      foreach ($records as $record  ) {
        $row = ['id' => $record->id,
                 'records' => [
                             ['text_align' => 'left', 'type' => 'text' , 'value' => $record->project_date],
                             ['text_align' => 'left', 'type' => 'text' , 'value' => $record->item_name],
                             ['text_align' => 'left', 'type' => 'text' , 'value' => $record->price],
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
    $data_details = [];
    $data_expenses = [];
    $data_materials = [];

    $data = [];
    if ($id != "") {
      $data = Tr_project::where('tr_project.id',$id)
      ->leftJoin('mt_item as mt_item', function($join) {
        $join->on('tr_project.item_id', '=', 'mt_item.id');
      })
      ->first(['tr_project.*','mt_item.item_name']);

      $data_details = Tr_project_detail::where('tr_project_detail.project_id',$id)
      ->get(['tr_project_detail.*']);

      $data_expenses = Tr_project_expense::where('Tr_project_expense.project_id',$id)
      ->get(['Tr_project_expense.*']);

      $data_materials = Tr_project_material::where('tr_project_material.project_id',$id)
      ->get(['tr_project_material.*']);
    }

    $forms = [];
    $mytime = \Carbon\Carbon::now();
    $form = ['label' => 'Project Date','placeholder' => 'Project Date',
             'type'  => 'datetime','name' => 'project_date',
             'col' => ['row' => 6, 'col1' => 4, 'col2' => 8],
             'required' => 'true',
             'read_only' => false,
             'disable' => 'false',
             'value' => ($cond=="insert" ? $mytime->format('d/m/Y') :  date_format(date_create($data->project_date),"d/m/Y")   ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Item Name','placeholder' => 'Item Name',
             'type'  => 'data','name' => 'item_name','id' => 'item_id',
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'required' => 'true','table' => 'item',
             'value' => ($cond=="insert" ? '' : $data->item_name ),
             'value_id' => ($cond=="insert" ? '' : $data->item_id ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Qty','placeholder' => 'Qty',
             'type'  => 'number','name' => 'qty',
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'required' => 'true',
             'value' => ($cond=="insert" ? 1 : $data->qty ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Price','placeholder' => 'Price',
             'type'  => 'number','name' => 'price',
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'required' => 'true',
             'read_only' => 'true',
             'value' => ($cond=="insert" ? 0 : $data->price ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Total','placeholder' => 'Total',
             'type'  => 'number','name' => 'total',
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'required' => 'true',
             'read_only' => 'true',
             'value' => ($cond=="insert" ? 0 : $data->total ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Remark','placeholder' => 'Remark',
             'type'  => 'text','name' => 'remark',
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'required' => 'true',
             'value' => ($cond=="insert" ? '' : $data->remark ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Project Detail','placeholder' => 'Project Detail',
             'type'  => 'table','name' => 'tr_project_detail',
             'col' => ['row' => 12, 'col1' => 0, 'col2' => 12],
            //  'required' => 'true',
             'value' => ($cond=="insert" ? '' : '' ),
             'collapse' => false,
             'custom_button_flag' => false,
             'columns' => [
                          ['col_name' => 'id', 'header' => '', 'type' => 'hidden', 'hide'=> true],
                          ['col_name' => 'type', 'header' => 'Type', 'type' => 'select','options' => ['SATUAN','LEMBARAN'], 'hide'=> false],
                          ['col_name' => 'material_id', 'header' => 'Material ID', 'type' => 'text', 'hide'=> true],
                          ['col_name' => 'material_name', 'header' => 'Bahan', 'type' => 'data','table' => 'material', 'hide'=> false],
                          ['col_name' => 'material_width', 'header' => 'Material W', 'type' => 'number', 'hide'=> true],
                          ['col_name' => 'pattern_id', 'header' => 'Pattern', 'type' => 'text',  'hide'=> true],
                          ['col_name' => 'pattern_name', 'header' => 'Pola', 'type' => 'data', 'table' => 'pattern', 'hide'=> false],
                          ['col_name' => 'qty', 'header' => 'Qty', 'type' => 'number', 'hide'=> false],
                          ['col_name' => 'price', 'header' => 'Hrg Sat/Pcs', 'type' => 'currency', 'hide'=> false],
                          ['col_name' => 'total', 'header' => 'Htg Total/Produk', 'type' => 'currency','read_only' =>true, 'hide'=> false],
                          ['col_name' => 'qty_total', 'header' => 'Qty Total', 'type' => 'number', 'read_only' =>true, 'hide'=> false],
                          ['col_name' => 'remark', 'header' => 'Ket', 'type' => 'text', 'hide'=> false],
             ],
             'details' => $data_details,
             'deleted_details' => [],
           ];
    array_push($forms,$form);
    $form = ['label' => 'Project Expense','placeholder' => 'Project Expense',
             'type'  => 'table','name' => 'tr_project_expense',
             'col' => ['row' => 12, 'col1' => 0, 'col2' => 12],
            //  'required' => 'true',
             'value' => ($cond=="insert" ? '' : '' ),
             'collapse' => false,
             'custom_button_flag' => false,
             'columns' => [
                          ['col_name' => 'id', 'header' => '', 'type' => 'hidden', 'hide'=> true],
                          ['col_name' => 'name', 'header' => 'Nama', 'type' => 'text', 'hide'=> false],
                          ['col_name' => 'amount', 'header' => 'Biaya / Pcs', 'type' => 'currency', 'hide'=> false],
                          ['col_name' => 'remark', 'header' => 'Ket', 'type' => 'text', 'hide'=> false],
             ],
             'details' => $data_expenses,
             'deleted_details' => [],
           ];
    array_push($forms,$form);
    $form = ['label' => 'Project Material','placeholder' => 'Project Material',
             'type'  => 'table','name' => 'tr_project_material',
             'col' => ['row' => 12, 'col1' => 0, 'col2' => 12],
            //  'required' => 'true',
             'value' => ($cond=="insert" ? '' : '' ),
             'collapse' => false,
             'custom_button_flag' => false,
             'columns' => [
                          ['col_name' => 'id', 'header' => '', 'type' => 'hidden', 'hide'=> true],
                          ['col_name' => 'material_id', 'header' => 'Material', 'type' => 'text', 'hide'=> true],
                          ['col_name' => 'material_name', 'header' => 'Bahan', 'type' => 'text', 'hide'=> false],
                          ['col_name' => 'qty', 'header' => 'Panjang Bahan (cm) / Qty Satuan', 'type' => 'currency', 'hide'=> false],
                          ['col_name' => 'price', 'header' => 'Hrg/M / Hrg Sat', 'type' => 'currency', 'hide'=> false],
                          ['col_name' => 'total', 'header' => 'Total Harga', 'type' => 'currency', 'hide'=> false],
                          ['col_name' => 'display', 'header' => 'Display', 'type' => 'button_display', 'hide'=> false],
             ],
             'details' => $data_materials,
             'deleted_details' => [],
           ];
    array_push($forms,$form);
    $form = ['label' => 'Calculate','placeholder' => 'Calculate',
             'type'  => 'calculate_project','name' => 'Calculate',
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
           ];
    array_push($forms,$form);

    $result = [
      'forms' =>  $forms,
      'form_id' => $this->form_id,
      'form_table' => 'project',
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

  public function loadDefaultDetail(Request $request) {
    $item_id = $request->item_id;

    // 'columns' => [
    //              ['col_name' => 'id', 'header' => '', 'type' => 'hidden', 'hide'=> true],
    //              ['col_name' => 'type', 'header' => 'Type', 'type' => 'select','options' => ['SATUAN','LEMBARAN'], 'hide'=> false],
    //              ['col_name' => 'material_id', 'header' => 'Material ID', 'type' => 'text', 'hide'=> true],
    //              ['col_name' => 'material_name', 'header' => 'Material', 'type' => 'data','table' => 'material', 'hide'=> false],
    //              ['col_name' => 'material_width', 'header' => 'Material W', 'type' => 'number', 'hide'=> true],
    //              ['col_name' => 'pattern_id', 'header' => 'Pattern', 'type' => 'text',  'hide'=> true],
    //              ['col_name' => 'pattern_name', 'header' => 'Pattern', 'type' => 'data', 'table' => 'pattern', 'hide'=> false],
    //              ['col_name' => 'qty', 'header' => 'Qty', 'type' => 'number', 'hide'=> false],
    //              ['col_name' => 'qty_total', 'header' => 'Qty Total', 'type' => 'number', 'read_only' =>true, 'hide'=> false],
    //              ['col_name' => 'price', 'header' => 'Price', 'type' => 'currency', 'hide'=> false],
    //              ['col_name' => 'total', 'header' => 'Total', 'type' => 'currency','read_only' =>true, 'hide'=> false],
    //              ['col_name' => 'remark', 'header' => 'Remark', 'type' => 'text', 'hide'=> false],
    // ],

    $data = Mt_item_detail::where('mt_item_detail.item_id',$item_id)
    ->leftJoin('mt_material as mt_material', function($join) {
      $join->on('mt_item_detail.material_id', '=', 'mt_material.id');
    })
    ->leftJoin('mt_pattern as mt_pattern', function($join) {
      $join->on('mt_item_detail.pattern_id', '=', 'mt_pattern.id');
    })
    ->get([ DB::raw(' 0 as id ') ,'mt_item_detail.type','mt_item_detail.material_id',
           'mt_material.material_name','mt_item_detail.pattern_id',
           'mt_pattern.pattern_name','mt_item_detail.qty','mt_item_detail.qty as qty_total',
            'mt_material.price',   DB::raw(' 0 as total '),   DB::raw(' 0 as remark ') ]);

    return json_encode($data);
  }

  public function pembulatanKebawah($qty,$pattern_width,$pattern_height,$material_y,$material_id,$material_name,$material_price) {
    //bulat kebawah
  $pattern_x = $pattern_width;
  $pattern_y = $pattern_height;

  $status = true;

  $temp1 = $material_y / $pattern_y;
  $temp2 = floor($temp1); // kelompok 1 jumlah kebawah (pcs)
  $temp3 = $temp1 - $temp2; // kelompok 2 (pcs)

  $temp4 = $temp2 * $pattern_y; // total lebar kebawah kelompok 1 (cm)
  $temp5 = $temp3 * $pattern_y; // total lebar kebawah kelompok 2 (cm)
  if ($temp5 < $pattern_x) { // yidak memungkinkan untuk dibalik
      $status = false;
  }

  $temp11= floor(($temp3 * $pattern_y) / $pattern_x); // kelompok 2 jumlah kebawah (pcs)

  $temp6 = floor($qty/$temp2) *$pattern_x;//total panjang kesamping (cm) kelompok 1
  $temp7 = $temp6 / $pattern_x; //jumlah kesamping sebelum dikali (pcs) kelompok 1

  if ($temp5 != 0) {
    $temp8 = ceil(($qty - ($temp7 * $temp2) ) / ($temp5/$pattern_x)); //jumlah kesamping sebelum dikali (pcs) kelompok 2

    $temp9 = $temp8 * $pattern_y;//total panjang kesamping (cm) kelompok 2

    $temp10 = $temp6 - $temp9; //selisih antara kelompok 1 dan 2 kesamping

  } else {
    $temp8 = 0;
    $temp9 = 0;
    $temp10 = 0;
  }

    return ['name' => 'bulat kebawah','width' => $material_y, 'length1' => $temp6, 'length2' => $temp9, 'selisih' => $temp10,
                        'status' => $status, 'total_x_1' => $temp7, 'total_y_1' => $temp2,
                        'total_x_2' => $temp8, 'total_y_2' => $temp11,
                        'material_width1' => $temp4,'pattern_width1' => $pattern_x, 'pattern_height1' => $pattern_y,
                        'material_width2' => $temp5,'pattern_width2' => $pattern_y, 'pattern_height2' => $pattern_x,
                        'material_id' => $material_id, 'material_name' => $material_name,'material_price' => $material_price];
  }


  public function pembulatanKeatas($qty,$pattern_width,$pattern_height,$material_y,$material_id,$material_name,$material_price) {
    //bulat keatas
    $pattern_x = $pattern_width;
    $pattern_y = $pattern_height;

    $status = true;

    $temp1 = $material_y / $pattern_y;
    $temp2 = floor($temp1); // kelompok 1 jumlah kebawah (pcs)

    $temp3 = $temp2 * $pattern_y; // total lebar kebawah kelompok 1 (cm)

    $temp4 = ceil($qty / $temp2); //jumlah kesamping sebelum dikali (pcs) kelompok 1

    $temp5 = $temp4 * $pattern_x; //panjang kesamping (cm) kelompok 1

    return  ['name' => 'bulat keatas','width' => $material_y, 'length1' => $temp5, 'length2' => 0, 'selisih' => 0,
                        'status' => $status, 'total_x_1' => $temp4, 'total_y_1' => $temp2,
                        'total_x_2' => 0, 'total_y_2' => 0,
                        'material_width1' => $material_y,'pattern_width1' => $pattern_x, 'pattern_height1' => $pattern_y,
                        'material_width2' => 0,'pattern_width2' => 0, 'pattern_height2' => 0,
                        'material_id' => $material_id, 'material_name' => $material_name,'material_price' => $material_price];
  }

  public function calculateProject(Request $request) {
    $results = [];
    $binWidth = 50;
    $binHeight = 500;

    $material_width = 0;

    $status = false;
    $status_false = 0;
    while ($status == false) {
      $binWidth = $binWidth + 50;
      $this->bin->begin($binWidth,$binHeight);
      for ($i = 0;$i < 25; $i++) {
        $rectWidth = 100 ;
        $rectHeight = 130;
        // $result = $result."Packing size : ".$rectWidth." ".$rectHeight."<br>";

        $packedRect = $this->bin->insert($rectWidth,$rectHeight,true,"RectBestShortSideFit","SplitMaximizeArea");

        if ($packedRect->height > 0) {
          // $result = $result."Packed to (x,y)=(".$packedRect->x.",".$packedRect->y."), (w,h)=(".$packedRect->width.",".$packedRect->height.")<br>";
          $result = ['x' => $packedRect->x, "y" => $packedRect->y, "width" => $packedRect->width, "height" => $packedRect->height];

          if ($material_width < $packedRect->x +  $packedRect->width) {
            $material_width = $packedRect->x + $packedRect->width;
          }
          array_push($results,$result);
        } else {
            $status_false = $status_false + 1;
        }
      }

      for ($i = 0;$i < 25; $i++) {
        $rectWidth = 78;
        $rectHeight = 100;
        // $result = $result."Packing size : ".$rectWidth." ".$rectHeight."<br>";

        $packedRect = $this->bin->insert($rectWidth,$rectHeight,true,"RectBestShortSideFit","SplitShorterAxis");

        if ($packedRect->height > 0) {
          // $result = $result."Packed to (x,y)=(".$packedRect->x.",".$packedRect->y."), (w,h)=(".$packedRect->width.",".$packedRect->height.")<br>";
          $result = ['x' => $packedRect->x, "y" => $packedRect->y, "width" => $packedRect->width, "height" => $packedRect->height];
          array_push($results,$result);
        } else {
            $status_false = $status_false + 1;
        }
      }

      if ($status_false == 0) {
          $status = true;
      } else {
        $status_false = 0;
        $results = [];
      }

    }



    $arr = array('code' => "200", 'status' => "OK", 'datas' => ['asdasdsad'],
            'choosen_one' => ['width' => $material_width, 'grids' => $results], 'material_lists' => ['asdasdsad'],
             'details' => ['asdasdsad']);
    return json_encode($arr);
  }

  //
  // public function calculateProject(Request $request) {
  //   $result = [];
  //   $i = 0;
  //   $details = $request->detail;
  //     $choosen_ones = [];
  //   foreach ($details as $detail) {
  //     $i = $i + 1;
  //     if($detail['type'] == 'LEMBARAN') {
  //       $choosen_one = [];
  //
  //       $material = Mt_material::where('id',$detail['material_id'])
  //                             ->where('delete_flag',false)
  //                             ->first();
  //
  //       $pattern = Mt_pattern::where('mt_pattern.id',$detail['pattern_id'])
  //                             ->join('mt_pattern_detail as mt_pattern_detail', function($join) {
  //                               $join->on('mt_pattern.id', '=', 'mt_pattern_detail.pattern_id');
  //                             })
  //                             ->where('mt_pattern.delete_flag',false)
  //                             ->first(['mt_pattern_detail.width','mt_pattern_detail.length']);
  //       $qty = $detail['qty_total'];
  //       $material_price = $material->price;
  //       $material_y = $material->width;
  //       $pattern_width = $pattern->width;
  //       $pattern_height = $pattern->length;
  //
  //       //bulat kebawah
  //       array_push($result, $this->pembulatanKebawah($qty,$pattern_width,$pattern_height,$material_y,$detail['material_id'],$detail['material_name'],$material_price));
  //
  //       //bulat kebawah reverse
  //       array_push($result, $this->pembulatanKebawah($qty,$pattern_height,$pattern_width,$material_y,$detail['material_id'],$detail['material_name'],$material_price));
  //
  //       //bulat keatas
  //       array_push($result, $this->pembulatanKeatas($qty,$pattern_width,$pattern_height,$material_y,$detail['material_id'],$detail['material_name'],$material_price));
  //
  //       //bulat keatas reverse
  //       array_push($result, $this->pembulatanKeatas($qty,$pattern_height,$pattern_width,$material_y,$detail['material_id'],$detail['material_name'],$material_price));
  //
  //
  //
  //       $temp_length = 0;
  //       foreach ($result as $res) {
  //         if ($res['status'] == true) {
  //             $max = max($res['length1'],$res['length2']);
  //             if ($temp_length == 0) {
  //               $temp_length = $max;
  //               $choosen_one = $res;
  //             } else {
  //               if ($max < $temp_length) {
  //                 $temp_length = $max;
  //                 $choosen_one = $res;
  //               }
  //             }
  //
  //         }
  //       }
  //
  //       array_push($choosen_ones,$choosen_one);
  //
  //     }
  //
  //
  //     $temp_length = max($choosen_one['length1'],$choosen_one['length2']);
  //     $details[$i]['price'] = $temp_length / $qty;
  //   }
  //
  //   $material_lists = [];
  //   foreach ($choosen_ones as $choosen_one) {
  //     $material_list = ['id' => '', 'material_id' => $choosen_one['material_id'],
  //      'material_name' => $choosen_one['material_name'],
  //      'qty' => max($choosen_one['length1'],$choosen_one['length2']),
  //       'price' =>  $choosen_one['material_price'], 'total' => (max($choosen_one['length1'],$choosen_one['length2']) / 100)  * $choosen_one['material_price'],
  //       'width' => $choosen_one['width'], 'pattern_width1' => $choosen_one['pattern_width1'], 'pattern_height1' => $choosen_one['pattern_width2'], 'total1' => ($choosen_one['total_x_1'] * $choosen_one['total_y_1']),
  //        'pattern_width2' => $choosen_one['pattern_width2'], 'pattern_height2' => $choosen_one['pattern_height2'], 'total2' => ($choosen_one['total_x_2'] * $choosen_one['total_y_2'])
  //        ];
  //
  //     array_push($material_lists,$material_list);
  //   }
  //
  //   $arr = array('code' => "200", 'status' => "OK", 'datas' => $result,
  //           'choosen_one' => $choosen_ones, 'material_lists' => $material_lists,
  //            'details' => $details);
  //   return json_encode($arr);
  // }

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
           $record = Tr_project::create($save_datas);

           $save_datas_detail = $this->validator->convertInputDetail($datas, 'project_id',$record->id);

           $record_detail = Tr_project_detail::insert($save_datas_detail['tr_project_detail_insert']);

           $record_detail = Tr_project_expense::insert($save_datas_detail['tr_project_expense_insert']);

           $record_detail = Tr_project_material::insert($save_datas_detail['tr_project_material_insert']);

           $audit = new Tr_audit;
           $audit->transaction_category = 'project';
           $audit->transaction_id = $record->id;
           $audit->status = 'insert';
           $audit->column = "";
           $audit->value_old = "";
           $audit->value_new = "";
           $audit->modified_user_id = $request->session()->get('user_id');
           $audit->save();

           DB::commit();
           $arr = array('code' => "200", 'status' => "OK", 'url' => '#/list/project', 'value_search' => $save_datas['item_id'] );
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
           Tr_project::where('id',$id)
                    ->update($save_datas);


        $save_datas_detail = $this->validator->convertInputDetail($datas, 'project_id',$id);

        $record_detail_insert = Tr_project_detail::insert($save_datas_detail['tr_project_detail_insert']);
        $record_detail_insert = Tr_project_expense::insert($save_datas_detail['tr_project_expense_insert']);
        $record_detail_insert = Tr_project_material::insert($save_datas_detail['tr_project_material_insert']);

        foreach ($save_datas_detail['tr_project_detail_update'] as $detail) {
          Tr_project_detail::where('id',$detail['id'])
                   ->update($detail);
        }
        foreach ($save_datas_detail['tr_project_expense_update'] as $detail) {
          Tr_project_expense::where('id',$detail['id'])
                   ->update($detail);
        }
        foreach ($save_datas_detail['tr_project_material_update'] as $detail) {
          Tr_project_material::where('id',$detail['id'])
                   ->update($detail);
        }
        //----------------------------------------------------------------------------------
        foreach ($save_datas_detail['tr_project_detail_delete'] as $detail) {
          Tr_project_detail::where('id',$detail['id'])->delete();
        }

        foreach ($save_datas_detail['tr_project_expense_delete'] as $detail) {
          Tr_project_expense::where('id',$detail['id'])->delete();
        }

        foreach ($save_datas_detail['tr_project_material_delete'] as $detail) {
          Tr_project_material::where('id',$detail['id'])->delete();
        }


          if ($this->columns !== "") {
            $audit = new Tr_audit;
            $audit->transaction_category = 'project';
            $audit->transaction_id = $id;
            $audit->status = 'update';
            $audit->column = $this->columns;
            $audit->value_old = $this->valuebefore;
            $audit->value_new = $this->valueafter;
            $audit->modified_user_id = $request->session()->get('user_id');
            $audit->save();
          }

           DB::commit();
           $arr = array('code' => "200", 'status' => "OK",'url' => '#/list/project');
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

     $record = Tr_project::find($id);
     if(isset($record)) {
       $record->delete_flag = true;
     }

     DB::beginTransaction();
     try {
       $record->update();

       DB::commit();
       $arr = array('code' => "200", 'status' => "OK",'url' => '#/list/project');
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
         $record = Tr_project::find($data);
         if(isset($record)) {
            $record->delete_flag = true;
            $record->update();
         }
       }

       DB::commit();
       $arr = array('code' => "200", 'status' => "OK",'url' => '#/list/project');
       return json_encode($arr);
     } catch(\Exception $e){
       DB::rollback();
       $arr = array('code' => "200", 'status' => "FAIL", 'msg' => $e->getMessage());
       return json_encode($arr);
     }
  }
}
