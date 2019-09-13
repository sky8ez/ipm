<?php

namespace App\Http\Controllers\App\Module;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Excel;
use App\Mt_customer;
use App\Mt_packet;
use App\Mt_rak;
use App\Mt_print;
use App\Tr_member;
use App\Tr_payment;
use App\Mt_user_access_detail;
use DB;
use Session;

class ImportController extends Controller
{

      public function index()
      {

      }

      private $form_id = "IMPORT-EXPORT";


      public function downloadTemplate(Request $request)
      {
        $table = $request->table;
        $with_data = $request->with_data;

        switch ($table) {
          case 'CUSTOMER':
              Excel::create('Customer', function($excel) use ($with_data) {

              if ($with_data == "true") {
                $excel->sheet('Customer', function($sheet) {
                        $records = Mt_customer::where('delete_flag',false)->select(['code', 'company_type', 'name','address','phone'])->get();
                        $sheet->fromModel($records ,null, 'A1', false, true);

                    });
              } else {
                $excel->sheet('Customer', function($sheet) {
                        $sheet->fromModel(array(array('code','company_type','name','address','phone')) ,null, 'A1', false, false);
                    });
              }
              })->export('xlsx');

            break;
        case 'PACKET':
              Excel::create('Packet', function($excel) use ($with_data) {

              if ($with_data == "true") {
                $excel->sheet('Packet', function($sheet) {
                        $records = Mt_packet::where('delete_flag',false)->select(['name', 'price', 'weight','duration'])->get();
                        $sheet->fromModel($records ,null, 'A1', false, true);

                    });
              } else {
                $excel->sheet('Packet', function($sheet) {
                        $sheet->fromModel(array(array('name','price','weight','duration')) ,null, 'A1', false, false);
                    });
              }
              })->export('xlsx');

          break;
        case 'RAK':
              Excel::create('Rak', function($excel) use ($with_data) {

              if ($with_data == "true") {
                $excel->sheet('Rak', function($sheet) {
                        $records = Mt_rak::where('delete_flag',false)->select(['name'])->get();
                        $sheet->fromModel($records ,null, 'A1', false, true);

                    });
              } else {
                $excel->sheet('Rak', function($sheet) {
                        $sheet->fromModel(array(array('name')) ,null, 'A1', false, false);
                    });
              }
              })->export('xlsx');
          break;
        case 'PRINT-TEMPLATE':
              Excel::create('Rak', function($excel) use ($with_data) {

              if ($with_data == "true") {
                $excel->sheet('Rak', function($sheet) {
                        $records = Mt_packet::where('delete_flag',false)->select(['name'])->get();
                        $sheet->fromModel($records ,null, 'A1', false, true);

                    });
              } else {
                $excel->sheet('Rak', function($sheet) {
                        $sheet->fromModel(array(array('name')) ,null, 'A1', false, false);
                    });
              }
              })->export('xlsx');
          break;
      case 'MEMBER':
            Excel::create('Rak', function($excel) use ($with_data) {

            if ($with_data == "true") {
              $excel->sheet('Rak', function($sheet) {
                      $records = Mt_packet::where('delete_flag',false)->select(['name'])->get();
                      $sheet->fromModel($records ,null, 'A1', false, true);

                  });
            } else {
              $excel->sheet('Rak', function($sheet) {
                      $sheet->fromModel(array(array('name')) ,null, 'A1', false, false);
                  });
            }
            })->export('xlsx');
        break;
        case 'PAYMENT':
                Excel::create('Rak', function($excel) use ($with_data) {

                if ($with_data == "true") {
                  $excel->sheet('Rak', function($sheet) {
                          $records = Mt_packet::where('delete_flag',false)->select(['name'])->get();
                          $sheet->fromModel($records ,null, 'A1', false, true);

                      });
                } else {
                  $excel->sheet('Rak', function($sheet) {
                          $sheet->fromModel(array(array('name')) ,null, 'A1', false, false);
                      });
                }
                })->export('xlsx');
          break;
          default:
            # code...
            break;
        }


      }

      public function import(Request $request)
      {
        $records = [];
        $datas = $request->data;
        $table = $datas['table'];
        $file = $request->file('file');
        $result = Excel::load($file, function($reader) {
                })->toArray();
        $i = 2;

      try {
            DB::beginTransaction();
            switch ($table) {
              case 'CUSTOMER':
                foreach ($result as $row) {
                  if(Mt_customer::create($row)){
                    array_push($records,"Row ".$i.". Insert Successfull");
                  } else {
                    array_push($records,"Row ".$i.". Insert Fail");
                  }
                  $i = $i + 1;
                }
                break;
              case 'PACKET':
                  foreach ($result as $row) {
                    if(Mt_packet::create($row)){
                      array_push($records,"Row ".$i.". Insert Successfull");
                    } else {
                      array_push($records,"Row ".$i.". Insert Fail");
                    }
                    $i = $i + 1;
                  }
                break;
              case 'RAK':
                  foreach ($result as $row) {
                    if(Mt_rak::create($row)){
                      array_push($records,"Row ".$i.". Insert Successfull");
                    } else {
                      array_push($records,"Row ".$i.". Insert Fail");
                    }
                    $i = $i + 1;
                  }
                break;
              case 'PRINT-TEMPLATE':
                    foreach ($result as $row) {
                      if(Mt_packet::create($row)){
                        array_push($records,"Row ".$i.". Insert Successfull");
                      } else {
                        array_push($records,"Row ".$i.". Insert Fail");
                      }
                      $i = $i + 1;
                    }
                break;
              case 'MEMBER':
                foreach ($result as $row) {
                  $records = Tr_member::create($row);
                }
                break;
              case 'PAYMENT':
                foreach ($result as $row) {
                  $records = Tr_payment::create($row);
                }
                break;
              default:
                # code...
                break;
            }
            DB::commit();
            return json_encode($records);

      } catch (\Exception $e){
        DB::rollback();
        $arr = array('code' => "200", 'status' => "FAIL", 'msg' => $e->getMessage());
        return json_encode($arr);
      }







      }
}
