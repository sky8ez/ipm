<?php

namespace App\Http\Middleware;

use Closure;
use App\Mt_user_access_detail;
use Illuminate\Contracts\Auth\Guard;

class IsAccessGranted
{
    /**
     * The Guard implementation.
     *
     * @var Guard
     */
    protected $auth;

    /**
     * Create a new filter instance.
     *
     * @param  Guard  $auth
     * @return void
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $module_id, $cond)
    {

       $access_list = Mt_user_access_detail::where('access_id', $request->session()->get('user_access_id'))
                                    ->where('module_id', $module_id)
                                    ->where('condition', $cond)
                                    ->first();
       if(isset($access_list)) {
         if ($access_list->cond_flag == false) {
           return false;
         } else {
           return true;
         }
       }

        $next1 =  $next($request);
        return $next1;
    }
}
