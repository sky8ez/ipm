<?php

namespace App\Http\Controllers\App\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Sy_preference;
use App\Tr_member;
use App\Tr_payment;
use App\Tr_closing_day;
use Validator;
use DB;
use Response;
use Session;
use Illuminate\Support\Facades\Input;
use App\Repositories\ValidatorRepository;
use App\Repositories\AccessRepository;
use Artisan;

class PreferenceController extends Controller
{
  /**
   * Send back all datas as JSON
   *
   * @return Response
   */
  private $form_id = "GENERAL";

  private $validator;
  private $access;
  private $access_list = [];

  public function __construct(ValidatorRepository $validator,AccessRepository $access)
 {
     $this->validator = $validator;
     $this->access = $access;
 }

 // get form template for customer
 public function getForm($cond="insert", $id = "")
 {
     $this->access_list = $this->access->getAccess($this->form_id, Session::get('user_access_id'));

     $datas = [];
     $datas = Sy_preference::get();

     $default_payment_no = false;
     $disable_date_payment = false;
     $not_allow_negatif_quota = false;

    //  foreach ($datas as $data) {
    //    switch ($data['name']) {
    //      case 'default_payment_no':
    //        $default_payment_no = ($data['value'] == "true" ? true : false);
    //        break;
    //      case 'disable_date_payment':
    //        $disable_date_payment = ($data['value'] == "true" ? true : false);
    //        break;
    //      case 'not_allow_negatif_quota':
    //        $not_allow_negatif_quota = ($data['value'] == "true" ? true : false);
    //        break;
    //      default:
    //        # code...
    //        break;
    //    }
    //  }

     $forms = [];
    //  $form = [
    //           'name' => 'default_payment_no',
    //           'title' => 'Set Default Payment No With Last Available No',
    //           'type' => 'checkbox',
    //           'value' => $default_payment_no,
    //         ];
    //  array_push($forms,$form);
    //  $form = [
    //           'name' => 'disable_date_payment',
    //           'title' => 'Disable Date on Payment',
    //           'type' => 'checkbox',
    //           'value' => $disable_date_payment,
    //         ];
    //  array_push($forms,$form);
    //  $form = [
    //           'name' => 'not_allow_negatif_quota',
    //           'title' => 'Do not allow negatif quota',
    //           'type' => 'checkbox',
    //           'value' => $not_allow_negatif_quota,
    //         ];
    //  array_push($forms,$form);
     $form = [
              'name' => 'backup_loc',
              'title' => 'Backup Location',
              'type' => 'text',
              'value' => env('BACKUP_LOC'),
            ];
     array_push($forms,$form);

     $result = [
       'forms' =>  $forms,
       'form_id' => $this->form_id,
       'form_table' => 'customer',
       'data_before' => $datas,
       'access_list' => $this->access_list,
       'role' => Session::get('role'),
     ];

     return Response::json($result);
   }


   public function reloadGeneral()
   {
       $generals = Sy_preference::where('category','GENERAL')->get();
       foreach ($generals as $general) {
         Session::put($general->name, $general->value);
       }
   }

   /**
    * Store a newly created resource in storage.
    *
    * @return Response
    */
   public function saveGeneral(Request $request)
   {
        $datas = $request->data;

        DB::beginTransaction();
        try {
          foreach ($datas as $data)   {
            switch ($data['name']) {
              case 'backup_loc':
                $this->updateDotEnv('BACKUP_LOC', $data['value']);
                break;
              default:
                $record = Sy_preference::where('name',$data['name'])->first();
                if(isset($record)) {
                  Sy_preference::where('id',$record->id)
                           ->update(['value' => $data['value']]);
                } else {
                  Sy_preference::create(['category' => 'GENERAL',
                                       'name' => $data['name'],
                                       'value' => $data['value'],
                                     ]);
                }
                break;
            }
          }



          DB::commit();
          $arr = array('code' => "200", 'status' => "OK", 'url' => '#/general' , 'msg' => 'Update Success');
          return json_encode($arr);
        } catch(\Exception $e){
          DB::rollback();
          $arr = array('code' => "200", 'status' => "FAIL", 'msg' => $e->getMessage());
          return json_encode($arr);
        }
   }


    protected function updateDotEnv($key, $newValue, $delim='')
     {

         $path = base_path('.env');
         // get old value from current env
         $oldValue = env($key);

         // was there any change?
         if ($oldValue === $newValue) {
             return;
         }

         // rewrite file content with changed data
         if (file_exists($path)) {
             // replace current value with new value
             file_put_contents(
                 $path, str_replace(
                     $key.'='.$delim.$oldValue.$delim,
                     $key.'='.$delim.$newValue.$delim,
                     file_get_contents($path)
                 )
             );
         }
     }


   public function cleanUp(Request $request)
   {
        $date_to = $request->date_to;
        DB::beginTransaction();
        try {
          Artisan::call("backup:run", []);

          Tr_payment::where('transaction_date','<',$date_to)
                   ->where('payment_status',true)
                   ->delete();

          Tr_closing_day::where('closing_date','<',$date_to)
                  ->delete();


          DB::commit();
          $arr = array('code' => "200", 'status' => "OK", 'url' => '#/general' , 'msg' => 'Cleanup and Backup Success');
          return json_encode($arr);
        } catch(\Exception $e){
          DB::rollback();
          $arr = array('code' => "200", 'status' => "FAIL", 'msg' => $e->getMessage());
          return json_encode($arr);
        }
   }


   public function backup()
   {
     try {
        //  Artisan::call("backup:clean", []);
         Artisan::call("backup:run", []);
       $arr = array('code' => "200", 'status' => "OK", 'url' => '#/general' , 'msg' => 'Backup Success');
       return json_encode($arr);
     } catch(\Exception $e){
       $arr = array('code' => "200", 'status' => "FAIL", 'msg' => $e->getMessage());
       return json_encode($arr);
     }

   }

}
