<?php

namespace App\Http\Controllers\App\Module;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Mt_password_category;
use App\User;
use App\Mt_table_filter;
use App\Mt_user_access;
use App\Mt_item;
use App\Mt_material;
use App\Mt_pattern;
use App\Tr_file;
use App\Http\Requests;
use Response;
use DB;

class SearchController extends Controller
{
    public function quickSearch(Request $request, $table, $parent_id = "", $cond = "")
    {
        switch ($table) {
          case 'item':
            return  $this->quick_item($request);
            break;
          case 'material':
            return  $this->quick_material($request);
            break;
          case 'pattern':
            return  $this->quick_pattern($request, $parent_id);
            break;
          case 'user-access':
            return  $this->quick_user_access($request);
            break;
          case 'file':
            return  $this->quick_file($request);
            break;
          case 'user':
            return  $this->quick_user($request);
            break;
          case 'password-category':
            return  $this->quick_password_category($request);
            break;
          default:
            # code...
            break;
        }
    }

    public function search(Request $request, $table)
    {
        $parent_id = $request->parent_id;
        $skip = $request->skip;
        $column = $request->column;
        $filter = $request->filter;
        $cond = $request->cond;

        switch ($table) {
          case 'item':
            return  $this->item($skip,$column,$filter);
            break;
          case 'material':
            return  $this->material($skip,$column,$filter);
            break;
          case 'pattern':
            return  $this->pattern($skip,$column,$filter, $parent_id);
            break;
          case 'user-access':
            return  $this->user_access($skip,$column,$filter);
            break;
          case 'file':
            return  $this->file($skip,$column,$filter);
            break;
          case 'user':
            return  $this->user($skip,$column,$filter);
            break;
          case 'password-category':
            return  $this->password_category($skip,$column,$filter);
            break;
          default:
            # code...
            break;
        }
    }

    function item($skip, $column, $filter) {
        $search = "1=1";
        $sort = "id asc";

        if($filter !== "-") {
          if ($column != "")  {
                $search = $search." and ".$column." like '%".$filter."%'";
          } else {

          }
        }

        $datas = Mt_item::whereRaw($search)
        ->where('delete_flag',false)
        ->orderByRaw($sort)
        ->skip(10 * $skip)
        ->take(10)
        ->get();

        $headers = [];
        $header = ['label' => trans('item.name'),'value' => 'mt_item.item_code', 'type' => 'text', 'table' => '', 'sort' => 'sort'];
        array_push($headers,$header);
        $header = ['label' => trans('item.phone'),'value' => 'mt_item.item_name', 'type' => 'text', 'table' => '', 'sort' => 'sort'];
        array_push($headers,$header);

        $result = [];
        foreach ($datas as $data  ) {
          $row = ['id' => $data->id,
                   'records' => [
                               ['type' => 'text' ,'role' => 'row_alias', 'value' => $data->item_code],
                               ['type' => 'text' ,'role' => '', 'value' => $data->item_name],
                     ]];
          array_push($result, $row);
        }

        $result = [
          'headers' =>  $headers,
          'datas' =>  $result,
          'new_link' =>  'form/item',
        ];
        return Response::json($result);
    }

    function user($skip, $column, $filter) {
        $search = "1=1";
        $sort = "id asc";

        if($filter !== "-") {
          if ($column != "")  {
                $search = $search." and ".$column." like '%".$filter."%'";
          } else {

          }
        }

        $datas = User::whereRaw($search)
        ->where('delete_flag',false)
        ->orderByRaw($sort)
        ->skip(10 * $skip)
        ->take(10)
        ->get();

        $headers = [];
        $header = ['label' => trans('user.email'),'value' => 'users.email', 'type' => 'text', 'table' => '', 'sort' => 'sort'];
        array_push($headers,$header);
        $header = ['label' => trans('user.name'),'value' => 'users.name', 'type' => 'text', 'table' => '', 'sort' => 'sort'];
        array_push($headers,$header);

        $result = [];
        foreach ($datas as $data  ) {
          $row = ['id' => $data->id,
                   'records' => [
                               ['type' => 'text' ,'role' => 'row_alias', 'value' => $data->email],
                               ['type' => 'text' ,'role' => '', 'value' => $data->name],
                     ]];
          array_push($result, $row);
        }

        $result = [
          'headers' =>  $headers,
          'datas' =>  $result,
          'new_link' =>  'form/user',
        ];
        return Response::json($result);
    }


    function password_category($skip, $column, $filter) {
        $search = "1=1";
        $sort = "id asc";

        if($filter !== "-") {
          if ($column != "")  {
                $search = $search." and ".$column." like '%".$filter."%'";
          } else {

          }
        }

        $datas = Mt_password_category::whereRaw($search)
        ->where('delete_flag',false)
        ->orderByRaw($sort)
        ->skip(10 * $skip)
        ->take(10)
        ->get();

        $headers = [];
        $header = ['label' => trans('password_category.name'),'value' => 'mt_password_category.name', 'type' => 'text', 'table' => '', 'sort' => 'sort'];
        array_push($headers,$header);
        $header = ['label' => trans('password_category.remarks'),'value' => 'mt_password_category.remarks', 'type' => 'text', 'table' => '', 'sort' => 'sort'];
        array_push($headers,$header);

        $result = [];
        foreach ($datas as $data  ) {
          $row = ['id' => $data->id,
                   'records' => [
                               ['type' => 'text' ,'role' => 'row_alias', 'value' => $data->name],
                               ['type' => 'text' ,'role' => '', 'value' => $data->remarks],
                     ]];
          array_push($result, $row);
        }

        $result = [
          'headers' =>  $headers,
          'datas' =>  $result,
          'new_link' =>  'form/password-category',
        ];
        return Response::json($result);
    }


    function material($skip, $column, $filter, $parent_id = "") {
        $search = "1=1";
        $sort = "id asc";

        if($filter !== "-") {
          if ($column != "")  {
                $search = $search." and ".$column." like '%".$filter."%'";
          } else {

          }
        }

        $datas = Mt_material::whereRaw($search)
        ->where('delete_flag',false)
        ->orderByRaw($sort)
        ->skip(10 * $skip)
        ->take(10)
        ->get();

        $headers = [];
        $header = ['label' => trans('material.name'),'value' => 'mt_material.material_name', 'type' => 'text', 'table' => '', 'sort' => 'sort'];
        array_push($headers,$header);
        $header = ['label' => trans('material.width'),'value' => 'mt_material.width', 'type' => 'number', 'table' => '', 'sort' => 'sort'];
        array_push($headers,$header);
        $header = ['label' => trans('material.price'),'value' => 'mt_material.price', 'type' => 'currency', 'table' => '', 'sort' => 'sort'];
        array_push($headers,$header);

        $result = [];
        foreach ($datas as $data  ) {
          $row = ['id' => $data->id,
                   'records' => [
                               ['type' => 'text' , 'role' => 'row_alias',  'value' => $data->material_name],
                               ['type' => 'text' , 'role' => 'extra_1',  'value' => $data->width],
                               ['type' => 'text' , 'role' => 'extra_2',  'value' => $data->price],
                     ]];
          array_push($result, $row);
        }

        $result = [
          'headers' =>  $headers,
          'datas' =>  $result,
          'new_link' =>  'form/material',
        ];
        return Response::json($result);
    }

    function pattern($skip, $column, $filter, $parent_id = "") {
        $search = "1=1";
        $sort = "mt_pattern.id asc";

        if($filter !== "-") {
          if ($column != "")  {
                $search = $search." and ".$column." like '%".$filter."%'";
          } else {

          }
        }

        $datas = Mt_pattern::whereRaw($search)
        // ->leftJoin('mt_pattern_detail as mt_pattern_detail', function($join) {
        //   $join->on('mt_pattern.id', '=', 'mt_pattern_detail.pattern_id');
        // })
        // ->where('mt_pattern_detail.delete_flag',false)
        ->where('mt_pattern.delete_flag',false)
        ->orderByRaw($sort)
        ->skip(10 * $skip)
        ->take(10)
        ->get(['mt_pattern.*']);

        $headers = [];
        $header = ['label' => trans('pattern.pattern_name'),'value' => 'mt_pattern.pattern_name', 'type' => 'text', 'table' => '', 'sort' => 'sort'];
        array_push($headers,$header);


        $result = [];
        foreach ($datas as $data  ) {
          $row = ['id' => $data->id,
                   'records' => [
                               ['type' => 'text' , 'role' => 'row_alias', 'value' => $data->pattern_name],
                     ]];
          array_push($result, $row);
        }

        $result = [
          'headers' =>  $headers,
          'datas' =>  $result,
          'new_link' =>  'form/pattern',
        ];
        return Response::json($result);
    }



    function user_access($skip, $column, $filter) {
        $search = "1=1";
        $sort = "id asc";

        if($filter !== "-") {
          if ($column != "")  {
                $search = $search." and ".$column." like '%".$filter."%'";
          } else {

          }
        }

        $datas = Mt_user_access::whereRaw($search)
         ->select('mt_user_access.*')
        ->where('delete_flag',false)
        ->orderByRaw($sort)
        ->skip(10 * $skip)
        ->take(10)
        ->get();

        $headers = [];
        $header = ['label' => trans('user_access.name'),'value' => 'mt_user_access.name', 'type' => 'text', 'table' => '', 'sort' => 'sort'];
        array_push($headers,$header);
        $header = ['label' => trans('user_access.role'),'value' => 'mt_user_access.role', 'type' => 'text', 'table' => '', 'sort' => 'sort'];
        array_push($headers,$header);


        $result = [];
        foreach ($datas as $data  ) {
          $row = ['id' => $data->id,
                   'records' => [
                               ['type' => 'text' , 'role' => 'row_alias', 'value' => $data->name],
                               ['type' => 'text' , 'role' => '', 'value' => $data->role],
                     ]];
          array_push($result, $row);
        }

        $result = [
          'headers' =>  $headers,
          'datas' =>  $result,
          'new_link' =>  'form/user-access',
        ];
        return Response::json($result);
    }

    function file($skip, $column, $filter) {
        $search = "1=1";
        $sort = "id asc";

        if($filter !== "-") {
          if ($column != "")  {
                $search = $search." and ".$column." like '%".$filter."%'";
          } else {

          }
        }

        $datas = Tr_file::whereRaw($search)
         ->select('tr_file.*')
         ->where('delete_flag',false)
        ->orderByRaw($sort)
        ->skip(10 * $skip)
        ->take(10)
        ->get();

        $headers = [];
        $header = ['label' => trans('file.name'),'value' => 'tr_file.file_name', 'type' => 'text', 'table' => '', 'sort' => 'sort'];
        array_push($headers,$header);
        $header = ['label' => trans('file.link'),'value' => 'tr_file.file_name', 'type' => 'text', 'table' => '', 'sort' => 'sort'];
        array_push($headers,$header);
        $header = ['label' => trans('file.image'),'value' => 'tr_file.name', 'type' => 'text', 'table' => '', 'sort' => 'sort'];
        array_push($headers,$header);


        $result = [];
        foreach ($datas as $data  ) {
          $row = ['id' => $data->id,
                   'records' => [
                               ['hide'=> false, 'type' => 'text' , 'role' => 'row_alias', 'value' => $data->file_name],
                               ['hide'=> true, 'type' => 'text' , 'role' => 'extra_1', 'value' => "api/uploaded-file/".$data->name.".".$data->ext],
                               ['hide'=> false, 'type' => 'image' , 'role' => '', 'value' => "api/uploaded-file/".$data->name.".".$data->ext],
                     ]];
          array_push($result, $row);
        }

        $result = [
          'headers' =>  $headers,
          'datas' =>  $result,
          'new_link' =>  'form/file',
        ];
        return Response::json($result);
    }

    function quick_file(Request $request) {
        $results = [];
        $search = "1=1";
        $sort = "tr_file.file_name asc";
        $search = $search." and tr_file.file_name like '%".$request->input('term')."%'";
        $datas = Tr_file::whereRaw($search)
        ->where('delete_flag',false)
        ->orderByRaw($sort)
        ->limit(5)
        ->get();

        if(isset($datas)) {
          foreach ($datas as $data) {
            array_push($results, ['id' => $data->id, 'value' => $data->file_name]);
          }
        }
        return Response::json($results);
    }

    function quick_password_category(Request $request) {
        $results = [];
        $search = "1=1";
        $sort = "mt_password_category.name asc";
        $search = $search." and mt_password_category.name like '%".$request->input('term')."%'";
        $datas = Mt_password_category::whereRaw($search)
        ->where('delete_flag',false)
        ->orderByRaw($sort)
        ->limit(5)
        ->get();

        if(isset($datas)) {
          foreach ($datas as $data) {
            array_push($results, ['id' => $data->id, 'value' => $data->name]);
          }
        }
        return Response::json($results);
    }

    function quick_user(Request $request) {
        $results = [];
        $search = "1=1";
        $sort = "users.email asc";
        $search = $search." and users.email like '%".$request->input('term')."%'";
        $datas = User::whereRaw($search)
        ->where('delete_flag',false)
        ->orderByRaw($sort)
        ->limit(5)
        ->get();

        if(isset($datas)) {
          foreach ($datas as $data) {
            array_push($results, ['id' => $data->id, 'value' => $data->email]);
          }
        }
        return Response::json($results);
    }

    function quick_item(Request $request) {
        $results = [];
        $search = "1=1";
        $sort = "mt_item.item_name asc";
        $search = $search." and mt_item.item_name like '%".$request->input('term')."%'";
        $datas = Mt_item::whereRaw($search)
        ->where('delete_flag',false)
        ->orderByRaw($sort)
        ->limit(5)
        ->get();

        if(isset($datas)) {
          foreach ($datas as $data) {
            array_push($results, ['id' => $data->id, 'value' => $data->item_name]);
          }
        }
        return Response::json($results);
    }

    function quick_material(Request $request) {
        $results = [];
        $search = "1=1";
        $sort = "mt_material.material_name asc";
        $search = $search." and mt_material.material_name like '%".$request->input('term')."%'";
        $datas = Mt_material::whereRaw($search)
        ->where('delete_flag',false)
        ->orderByRaw($sort)
        ->limit(5)
        ->get();

        if(isset($datas)) {
          foreach ($datas as $data) {
            array_push($results, ['id' => $data->id, 'value' => $data->material_name]);
          }
        }
        return Response::json($results);
    }

    function quick_pattern(Request $request) {
        $results = [];
        $search = "1=1";
        $sort = "mt_pattern.pattern_name asc";
        $search = $search." and mt_pattern.pattern_name like '%".$request->input('term')."%'";
        $datas = Mt_pattern::whereRaw($search)
        ->where('delete_flag',false)
        ->orderByRaw($sort)
        ->limit(5)
        ->get();

        if(isset($datas)) {
          foreach ($datas as $data) {
            array_push($results, ['id' => $data->id, 'value' => $data->pattern_name]);
          }
        }
        return Response::json($results);
    }

    function quick_user_access(Request $request) {
        $results = [];
        $search = "1=1";
        $sort = "mt_user_access.name asc";
        $search = $search." and mt_user_access.name like '%".$request->input('term')."%'";
        $datas = Mt_user_access::whereRaw($search)
        ->where('delete_flag',false)
        ->orderByRaw($sort)
        ->limit(5)
        ->get();

        if(isset($datas)) {
          foreach ($datas as $data) {
            array_push($results, ['id' => $data->id, 'value' => $data->name]);
          }
        }
        return Response::json($results);
    }


}
