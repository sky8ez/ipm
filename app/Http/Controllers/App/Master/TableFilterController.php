<?php

namespace App\Http\Controllers\App\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Mt_table_filter;
use Response;
use Validator;
use DB;
use Illuminate\Support\Facades\Input;

class TableFilterController extends Controller
{
    /**
     * Send back all datas as JSON
     *
     * @return Response
     */

    public function index($form_id)
    {
        return Response::json(Mt_table_filter::where('form_id',$form_id)->get()
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
      $rules = array(
           'column_name' => 'required|min:1',
           'filter' => 'required|min:1',
       );

       $validator = Validator::make($request->all(), $rules);

       if ($validator->passes()) {
         if ($request->id !== "") {
            $filter =  Mt_table_filter::find($request->id);
         } else {
           if ($request->category == 'FILTER') {
             $filter =  Mt_table_filter::where('form_id',$request->form_id)
                                        ->where('category',$request->category)
                                        ->where('column_name',$request->column_name)
                                        ->first();
           } else {
             $filter =  Mt_table_filter::where('form_id',$request->form_id)
                                        ->where('category',$request->category)
                                        ->first();
           }
         }

         DB::beginTransaction();
         try {
           if (isset($filter)) {
             $filter->user_id = $request->session()->get('user_id');
             $filter->category = $request->category;
             $filter->form_id = $request->form_id;
             $filter->alias = $request->alias;
             $filter->column_name = $request->column_name;
             $filter->column_type = $request->column_type;
             $filter->column_table = $request->column_table;
             $filter->filter = $request->filter;
             $filter->value = $request->value;
             $filter->update();
           } else {
             $filter = new Mt_table_filter;
             $filter->user_id = $request->session()->get('user_id');
             $filter->category = $request->category;
             $filter->form_id = $request->form_id;
             $filter->alias = $request->alias;
             $filter->column_name = $request->column_name;
             $filter->column_type = $request->column_type;
             $filter->column_table = $request->column_table;
             $filter->filter = $request->filter;
             $filter->value = $request->value;
             $filter->save();
           }

           DB::commit();
           $arr = array('code' => "200", 'status' => "OK");
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
        try {
          Mt_table_filter::destroy($id);
          $arr = array('code' => "200", 'status' => "OK");
          return json_encode($arr);

        } catch (Exception $e) {
          $arr = array('code' => "200", 'status' => "FAIL", 'msg' => $e->getMessage());
          return json_encode($arr);
        }


    }
}
