<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mt_user_access;
use App\Mt_user_access_detail;
use App\Sy_preference;
use App\Tr_activity_log;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */

     public function authenticated($request, $user)
     {
             // Fungsi ini akan dipanggil setelah user berhasil login.
             // Kita bisa menambahkan aksi-aksi lainnya, misalnya mencatat waktu last_login user.
           //  session_start(); //untuk roxy fileman
           //  $_SESSION["verify"] = "FileManager4TinyMCEtomcms";
             $request->session()->put('user_name', $user->name);
             $request->session()->put('user_id', $user->id);
             $request->session()->put('user_email', $user->email);
             $request->session()->put('user_access_id', $user->user_access_id);
             $request->session()->put('print_limit', $user->print_limit);
             $user_access = Mt_user_access::where('id',$user->user_access_id)->first();
             $generals = Sy_preference::where('category','GENERAL')->get();
             foreach ($generals as $general) {
               $request->session()->put($general->name, $general->value);
             }

             if (isset($user_access)) {
                $request->session()->put('role', $user_access->role);
               $user_access_detail = Mt_user_access_detail::where('user_access_id',$user_access->id)->get();
               $request->session()->put('user_access', $user_access_detail);
               $request->session()->put('role', $user_access->role);
             }

             $activity_log = new Tr_activity_log;
             $activity_log->activity_date = Date('y-m-d H:i:s');
             $activity_log->transaction_category = 'IPM';
             $activity_log->transaction_id = 0;
             $activity_log->user_id = $user->id;
             $activity_log->action = 'LOGIN';
             $activity_log->ip = $request->ip();
             $activity_log->browser = $request->header('User-Agent');
             $activity_log->save();

             return redirect('/');
     }

    protected $redirectTo = '/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'logout']);
    }

    // public function logout(Request $request)
    // {
    //     // $this->performLogout($request);
    //     // return redirect()->route('/login');
    // }

}
