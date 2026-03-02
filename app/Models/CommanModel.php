<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class CommanModel extends Model
{
    use HasFactory;


    /*function for insert data*/
    static function insertData($table,$data)
    {
        $insertResponse = DB::table($table)->insertGetId($data);
        if($insertResponse){
            return $insertResponse;
        }else{
            return FALSE;
        }
    }


    /*function for check exist*/
    static function checkIsExist($table,$where)
    {
        $response = DB::table($table)->where($where)->first();
        if($response){
            return $response;
        }else{
            return "FALSE";
        }
    }

    /*function for check exist*/
    static function getRowWhere($table,$where)
    {
        $data = DB::table($table)->where($where)->first();
        if($data){
            return $data;
        }else{
            return "FALSE";
        }
    }


    /*function for check exist*/
    static function selectColumnWhere($table,$where,$columnData)
    {
        $data = DB::table($table)->select($columnData)->where($where)->first();
        if($data){
            return $data;
        }else{
            return "FALSE";
        }
    }
    
    /*function for check exist*/
    static function fetchDataWhere($table,$where)
    {
        $response = DB::table($table)->where($where)->orderby('id','desc')->get();

        if($response){
            return $response;
        }else{
            return FALSE;
        }
    }


    /*function for check exist*/
    static function fetchDataOrderById($table,$where,$orderBy)
    {
            
        $response = DB::table($table)->where($where)->orderby('id',$orderBy)->get();

        if($response){
            return $response;
        }else{
            return FALSE;
        }
    }

    /*function for check exist*/
    static function fetchDataArr($table)
    {
        $response = DB::table($table)->get();

        if($response){
            return $response;
        }else{
            return FALSE;
        }
    }

    /*function for check exist*/
    static function fetchDataWithPaginattion($table,$where,$recordNumber,$orWhere='')
    {
        if (isset($orWhere) && $orWhere!=='') {

            $response = DB::table($table)->where($where)->orWhere($orWhere['field'], '=', $orWhere['value'])->orderby('id','desc')->paginate($recordNumber);
            
        }else{
            
            $response = DB::table($table)->where($where)->orderby('id','desc')->paginate($recordNumber);
        }

        if($response){
            return $response;
        }else{
            return FALSE;
        }
    }


    /*function for check exist*/
    static function fetchPaginateData($table,$recordNumber)
    {
        if (isset($orWhere) && $orWhere!=='') {

            $response = DB::table($table)->where($where)->orWhere($orWhere['field'], '=', $orWhere['value'])->paginate($recordNumber);
            
        }else{
            
        }

        $response = DB::table($table)->orderby('id','desc')->paginate($recordNumber);
        if($response){
            return $response;
        }else{
            return FALSE;
        }
    }

    

    /*function for check exist*/
    static function fetchWithPaginationOrderByGroupBy($table,$where,$recordNumber,$orderby,$groupby='')
    {

        if($groupby!==''){
            $response = DB::table($table)->groupBy($groupby)->where($where)->orderby($orderby['column'],$orderby['value'])->paginate($recordNumber);
        }else{
            $response = DB::table($table)->where($where)->orderby($orderby['column'],$orderby['value'])->paginate($recordNumber);
        }
     
        if($response){
            return $response;
        }else{
            return FALSE;
        }
    }

    /*Fetch All ascending or Descending Data*/
    static function fetchAllAscDescData($table,$orderby)
    {

        $response = DB::table($table)->orderby($orderby['column'],$orderby['value'])->get();
        if($response){
            return $response;
        }else{
            return FALSE;
        }
    }

    /*Fetch All ascending or Descending Data*/
    static function fetchWhereAscDescData($table,$where,$orderby)
    {

        $response = DB::table($table)->where($where)->orderby($orderby['column'],$orderby['value'])->get();
        if($response){
            return $response;
        }else{
            return FALSE;
        }
    }

    
    /*function for check exist*/
    static function fetchDataOrderByOrGroupBy($table,$where,$orderby,$groupby='')
    {

        if($groupby!==''){
            $response = DB::table($table)->groupBy($groupby)->where($where)->orderby($orderby['column'],$orderby['value'])->get();
        }else{
            $response = DB::table($table)->where($where)->orderby($orderby['column'],$orderby['value'])->get();
        }
     
        if($response){
            return $response;
        }else{
            return FALSE;
        }
    }   

    /*function for check exist*/
    static function fetchHomePaginatedPost($table,$where,$orderby,$repetedDataFieldsValue,)
    {
        $response = DB::table($table)->where($where)->orderby($orderby['column'],$orderby['value'])->select('id', '//other columns')->distinct()->get();
        
        if($response){
            return $response;
        }else{
            return FALSE;
        }
    }

    

    /*function for fetch data by group and order*/
    static function fetchSingleWithOrderByOrGroupBy($table,$where,$orderby,$groupby='')
    {

        if($groupby!==''){
            $response = DB::table($table)->groupBy($groupby)->where($where)->orderby($orderby['column'],$orderby['value'])->get()->first();
        }else{
            $response = DB::table($table)->where($where)->orderby($orderby['column'],$orderby['value'])->get()->first();
        }
     
        if($response){
            return $response;
        }else{
            return FALSE;
        }
    }


    /*function for check exist*/
    static function fetchDataById($table,$where,$whereNotIn='')
    {

        if(isset($whereNotIn) && $whereNotIn!==''){
            $response = DB::table($table)->where($where)->first();
        }else{

            $response = DB::table($table)->where($where)->first();
            if($response){
                return $response;
            }else{
                return FALSE;
            }
        }
    }

    /*function for check exist*/
    static function changeStatus($table,$where,$dataForUpdate)
    {
        $response = DB::table($table)->where($where)->update($dataForUpdate);
        if($response){
            return "TRUE";
        }else{
            return "FALSE";
        }
    }

    /*function for check exist*/
    static function updateData($table,$where,$dataForUpdate)
    {
        $response = DB::table($table)->where($where)->update($dataForUpdate);
        
        if($response){
            return "TRUE";
        }else{
            return "FALSE";
        }
    }

    /*Update all data*/
    static function updateAllRow($table,$dataForUpdate)
    {
        $response = DB::table($table)->update($dataForUpdate);
        
        if($response){
            return "TRUE";
        }else{
            return "FALSE";
        }
    }


    /*function for check exist*/
    static function deleteData($table,$where)
    {
        $response = DB::table($table)->where($where)->delete();
        if($response){
            return TRUE;
        }else{
            return FALSE;
        }
    }

  /*function for Soft Delete*/
    static function soft_delete($table,$where)
    {   
        $dataForUpdate = ['is_delete'=>1];
        $response = DB::table($table)->where($where)->update($dataForUpdate);
        if($response){
            return 'TRUE';
        }else{
            return 'FALSE';
        }
    }
    static function soft_delete_at($table,$where)
    {   
        $dataForUpdate = ['deleted_at'=>1];
        $response = DB::table($table)->where($where)->update($dataForUpdate);
        if($response){
            return 'TRUE';
        }else{
            return 'FALSE';
        }
    }

 
    /*function for check exist*/
    static function countData($table,$where='')
    {
        if($where!==''){
            $response = DB::table($table)->where($where)->count();
        }else{
            $response = DB::table($table)->count();;
        }

        if($response){
            return $response;
        }else{
            return FALSE;
        }
    }

    function downloadFile($filePath){
        echo $path = storage_path($filePath);
        die();
        return response()->download($path);
    }

}
