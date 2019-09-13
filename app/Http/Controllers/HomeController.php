<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\GenerateRepository;
use Validator;
use Session;
use App\Mt_table_filter;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    private $generator;

    public function __construct(GenerateRepository $generator)
    {
        $this->middleware('auth');
        $this->generator = $generator;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //$this->generator->generateNewPeriod("2016");
        //if(Session::get('period') !== null) {
          return view('app.dashboard');
      //  } else {
      //    return view('app.period_selection');
      //  }
    }

    public function setPeriod(Request $request) //post
    {
        try {
          $rules = array(
               'period' => 'required',
           );

           $validator = Validator::make($request->all(), $rules);

           if ($validator->passes()) {
             $request->session()->put('period',$request->period);

             $arr = array('code' => "200", 'status' => "OK", 'url' => "/home");
             return json_encode($arr);

           }  else {
            http_response_code(404);
            $result ="";
            foreach ($validator->errors()->all() as $error) {
              $result = $result."<br>".$error;
            }
            return $result;
          }

        } catch(\Exception $e){
          http_response_code(404);
          return $e->getMessage();
        }
    }

    public function removeFilter($form) //post
    {
        try {
          Mt_table_filter::where('form_id',$form)
                          ->where('user_id',Session::get('user_id'))
                          ->delete();

        } catch(\Exception $e){
          http_response_code(404);
          return $e->getMessage();
        }
    }



    public function barcode()
    {
        return view('barcode');
    }

}
