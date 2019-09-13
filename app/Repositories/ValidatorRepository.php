<?php

namespace App\Repositories;

class ValidatorRepository
{
    /**
     * Get all of the tasks for a given user.
     *
     * @param  User  $user
     * @return Collection
     */

     public function getValidator($datas)
     {
       $rules = [];
       foreach ($datas as $data) {
           $rule = "";
           switch ($data['type']) {
             case 'email':
               $rule = $rule."";
               if ($data['required'] == 'true') {
                  $rule = $rule."required|";
               }
               if ($rule !== "") {
                  $rules[$data['name']] = $rule;
               }
               break;
             case 'data':
               $rule = $rule."";
               if ($data['required'] == 'true') {
                  $rule = $rule."required|";
               }
               if ($rule !== "") {
                  $rules[$data['id']] = $rule;
               }
               break;
               case 'table':
                 break;
                 case 'image':
                   break;
             default:
               if ($data['required'] == 'true') {
                $rule = $rule."required|";
               }

               if (isset($data['unique'])) {
                 $rule = $rule.$data['unique']."|";
               }

               // $rule = $rule."unique:mt_customer|";

               if ($rule !== "") {
                 $rules[$data['name']] = $rule;
               }
               break;
           }//switch
       }//foreach

       return $rules;
     }//function

     public function convertInput($datas)
     {
        $save_datas = [];
         foreach ($datas as $data) {
           switch ($data['type']) {
             case 'blank':
               break;
             case 'image':
               break;
             case 'table':
               break;
             case 'generate_password':
               break;
             case 'data':
                 $save_datas[$data['id']] = ($data['value_id'] == null ? 0 : $data['value_id']);
               break;
             case 'password':
               $save_datas[$data['name']] = bcrypt($data['value']);
               break;
             case 'checkbox':
               $save_datas[$data['name']] = ($data['value'] == 'true' ? true : false) ;
               break;
            case 'access-menu':
             break;
             case 'table-info':
              break;
             default:
               $save_datas[$data['name']] = $data['value'];
               break;
           }
         }
        return $save_datas;
     }

     public function convertInputDetail($datas,$parent_column,$parent_id)
     {
        $result = [];
         foreach ($datas as $data) {
           $save_datas_insert = [];
           $save_datas_update = [];
           $save_datas_delete = [];
           switch ($data['type']) {

             case 'table':

             if (isset($data['details'])) {
               foreach ($data['details'] as $detail) {
                 $save_data = [];

                 if($detail['id'] == "") {//insert
                   foreach ($data['columns'] as $column) {
                     if ($column['col_name'] != "id") {
                       switch ($column['type']) {
                         case 'data':
                           # code...
                           break;
                         default:
                           $save_data[$column['col_name']] = $detail[$column['col_name']];
                           break;
                       }

                     }
                   }
                   $save_data[$parent_column] = $parent_id;
                   array_push($save_datas_insert,$save_data);
                 } else {
                   foreach ($data['columns'] as $column) {
                     switch ($column['type']) {
                       case 'data':
                         # code...
                         break;
                       default:
                          $save_data[$column['col_name']] = $detail[$column['col_name']];
                         break;
                     }

                   }
                   $save_data[$parent_column] = $parent_id;
                   array_push($save_datas_update,$save_data);
                 }


               }
             }


             if (isset($data['deleted_details'])) {
               foreach ($data['deleted_details'] as $detail) {
                 $save_data = [];

                 if($detail['id'] != "") {//delete
                   foreach ($data['columns'] as $column) {
                       $save_data[$column['col_name']] = $detail[$column['col_name']];
                   }
                   $save_data[$parent_column] = $parent_id;
                   array_push($save_datas_delete,$save_data);
                 }
               }
             }


              $result[$data['name']."_insert"] = $save_datas_insert;
              $result[$data['name']."_update"] = $save_datas_update;
              $result[$data['name']."_delete"] = $save_datas_delete;
               break;

             default:
               break;
           }
         }
        return $result;
     }

     public function convertInputWithBefore($datas, $data_before)
     {
        $save_datas = [];
         foreach ($datas as $data) {
           switch ($data['type']) {
             case 'data':
               $save_datas[$data['id']] = ($data['value_id'] == null ? 0 : $data['value_id']);
               break;
               case 'password':
                  if ($data_before[$data['name']] == $data['value']) {
                    $save_datas[$data['name']] = $data['value'];
                  } else {
                    $save_datas[$data['name']] = bcrypt($data['value']);
                  }
                 break;
              case 'access-menu':
               break;
              case 'table':
                break;
             default:
               $save_datas[$data['name']] = $data['value'];
               break;
           }
         }
        return $save_datas;
     }

     public function getUserAccess($datas)
     {
        $save_datas = [];
         foreach ($datas as $data) {
           switch ($data['type']) {
             case 'access-menu':
                $save_datas['table'] = $data['table'];
                $save_datas['value'] = $data['value'];
               break;
             default:
               break;
           }
         }
        return $save_datas;
     }

}
