<?php

namespace App\Repositories;

use App\Mt_user_access_detail;

class AccessRepository
{
    /**
     * Get all of the tasks for a given user.
     *
     * @param  User  $user
     * @return Collection
     */

     public function getAccess($module_id,$user_access_id)
     {
       $access = [];
       $access = Mt_user_access_detail::where('module_id',$module_id)
       ->where('user_access_id',$user_access_id)
       ->where('delete_flag',false)
       ->get();

       return $access;
     }//function

}
