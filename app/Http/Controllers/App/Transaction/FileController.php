<?php

namespace App\Http\Controllers\App\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Tr_file;
use App\Mt_table_filter;
use Validator;
use Redirect;
use Storage;
use Response;
use Image;
use File;
use DB;
use Illuminate\Support\Facades\Input;
use App\Repositories\ValidatorRepository;
use App\Repositories\AccessRepository;
use Session;


class FileController extends Controller
{
  /**
   * Send back all datas as JSON
   *
   * @return Response
   */
  private $form_id = "FILE";

  private $validator;
  private $access;
  private $access_list = [];


  public function __construct(ValidatorRepository $validator,AccessRepository $access)
 {
     $this->validator = $validator;
     $this->access = $access;
 }

 public function getUploaded($filename) {
     $path = storage_path() . '/app/file/' . $filename;

      if(!File::exists($path)) return "";

     $file = File::get($path);
     $type = File::mimeType($path);

     $response = Response::make($file, 200);
     $response->header("Content-Type", $type);

     return $response;
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
      $header = ['label' => trans('file.file_name'),'value' => 'tr_file.file_name', 'type' => 'text', 'table' => '', 'sort' => 'sort'];
      array_push($headers,$header);
      $header = ['label' => trans('file.name'),'value' => 'tr_file.name', 'type' => 'text', 'table' => '', 'sort' => 'sort'];
      array_push($headers,$header);
      $header = ['label' => trans('file.size'),'value' => 'tr_file.size', 'type' => 'text', 'table' => '', 'sort' => 'sort'];
      array_push($headers,$header);
      $header = ['label' => trans('file.updated_at'),'value' => 'tr_file.updated_at', 'type' => 'datetime', 'table' => '', 'sort' => 'sort'];
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

      $records =Tr_file::whereRaw($search)
      ->where('tr_file.delete_flag',false)
       ->select('tr_file.*')
      ->orderByRaw($sort)
      ->skip(25 * $skip)
      ->take(25)
      ->get();

      if ($skip == 0) {
        $records_count = Tr_file::whereRaw($search)
        ->where('tr_file.delete_flag',false)
        ->count();
      } else {
        $records_count = 0;
      }


      $datas = [];

      foreach ($records as $record  ) {
        $row = ['id' => $record->id,
                 'records' => [
                             ['text_align' => 'left', 'type' => 'text' , 'value' => $record->file_name],
                             ['text_align' => 'left', 'type' => 'image' , 'value' => "api/uploaded-file/".$record->name.".".$record->ext],
                             ['text_align' => 'left', 'type' => 'text' , 'value' => (round($record->size / 1024,3))." KB"],
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
      $data = Tr_file::where('tr_file.id',$id)
      ->first(['tr_file.*']);
    }

    $forms = [];
    $form = ['label' => 'File Name','placeholder' => 'File Name',
             'type'  => 'text','name' => 'file_name',
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'required' => 'true',
             'value' => ($cond=="insert" ? '' : $data->file_name ),
           ];
    array_push($forms,$form);
    $form = ['label' => 'Image','placeholder' => 'Image',
             'type'  => 'image','name' => 'image',
             'col' => ['row' => 12, 'col1' => 2, 'col2' => 10],
             'required' => 'true',
             'value' => ($cond=="insert" ? '' : "api/uploaded-file/".$data->name.".".$data->ext ),
           ];
    array_push($forms,$form);

    $result = [
      'forms' =>  $forms,
      'form_id' => $this->form_id,
      'form_table' => 'file',
      'data_before' => $data,
      'access_list' => $this->access_list,
      'role' => Session::get('role'),
    ];

    return Response::json($result);
  }

  /**
   * Store a newly created resource in storage.
   *
   * @return Response
   */
  public function store(Request $request)
  {
    if ($request->file('file')->isValid()) {

      $datas = $request->data['data'];
      $save_datas = [];

      $rules = $this->validator->getValidator($datas);

      $validator = Validator::make($request->data, $rules);

      if ($validator->passes()) {
        $image = $request->file('file');
        $ext = $image->getClientOriginalExtension();
        $destinationPath = public_path('uploaded-file');
        $fileName = str_random(60);
        $type = $image->getMimeType();
        $size = $image->getSize();
        Storage::put("/file/".$fileName.".".$ext, file_get_contents($image->getRealPath()));

        $save_datas = $this->validator->convertInput($datas);
        $save_datas['parent_id'] = 0;
        $save_datas['parent_category'] = "";
        $save_datas['type'] = $type;
        $save_datas['path'] = $destinationPath;
        $save_datas['name'] = $fileName;
        $save_datas['ext'] = $ext;
        $save_datas['size'] = $size;


        DB::beginTransaction();
        try {
          $record = Tr_file::create($save_datas);

          DB::commit();
            $arr = array('code' => "200", 'status' => "OK", 'url' => '#/list/file-manager');
          return json_encode($arr);
        } catch(\Exception $e){
          Storage::delete("/file/".  $save_datas['name'] .".".$save_datas['ext']);

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
    } else {
      http_response_code(404);
      return "File is Invalid";
    }

  }

  public function update(Request $request, $id)
  {
       $datas = $request->data['data'];
       $save_datas = [];

       $rules = $this->validator->getValidator($datas);

       $validator = Validator::make($request->data, $rules);

       if ($validator->passes()) {

         $save_datas = $this->validator->convertInput($datas);

         if ($request->file('file') !== null) {
           $image = $request->file('file');
           $ext = $image->getClientOriginalExtension();
           $destinationPath = public_path('uploaded-file');
           $fileName = str_random(60);
           $type = $image->getMimeType();
           $size = $image->getSize();
           Storage::put("/file/".$fileName.".".$ext, file_get_contents($image->getRealPath()));

         //  $image->move($destinationPath, $fileName.".".$ext);

           $file =  Tr_file::find($id);
          //  $this->auditTrail("type", $file->type, $type);
          //  $this->auditTrail("path", $file->path, $destinationPath);
          //  $this->auditTrail("name", $file->name, $fileName);
          //  $this->auditTrail("ext", $file->ext, $ext);
          //  $this->auditTrail("size", $file->ext, $size);

           Storage::delete("/file/".$file->name.".".$file->ext);
         //  File::delete($destinationPath."/".$file->name.".".$file->ext);
           $save_datas['parent_category'] = "file";
           $save_datas['parent_id'] = 0;
           $save_datas['type'] = $type;
           $save_datas['path'] = $destinationPath;
           $save_datas['name'] = $fileName;
           $save_datas['ext'] = $ext;
           $save_datas['size'] = $size;

         }

         DB::beginTransaction();
         try {
           Tr_file::where('id',$id)
                    ->update($save_datas);

           DB::commit();
           $arr = array('code' => "200", 'status' => "OK",'url' => '#/list/file-manager');
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

     $record = Tr_file::find($id);
     if(isset($record)) {
       $record->delete_flag = true;

       Storage::delete("/file/".$record->name.".".$record->ext);
     }

     DB::beginTransaction();
     try {
       $record->update();

       DB::commit();
       $arr = array('code' => "200", 'status' => "OK",'url' => '#/list/file');
       return json_encode($arr);
     } catch(\Exception $e){
       DB::rollback();
       $arr = array('code' => "200", 'status' => "FAIL", 'msg' => $e->getMessage());
       return json_encode($arr);
     }

  }
}
