<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Fan;
use App\Models\FanClub;

class FilterController extends ApiController
{
    
    public function recipientsCount(Request $request){
     
          $sender_id = $request->user()->id;
        
          $total_fans = FanClub::where('user_id', $sender_id)->where('is_active', 1)->count();

           $data['total_fans']=$total_fans;
     
           
           $eighteen_plus = FanClub::whereHas('fan',function($query){
             $query->where('dob', '<', date('Y-m-d', strtotime('-18 years')));
           })->where('user_id', $sender_id)->where('is_active', 1)->count();

           $data['eighteen_plus']=$eighteen_plus;


           $twenty_one_plus = FanClub::whereHas('fan',function($query){
             $query->where('dob', '<', date('Y-m-d', strtotime('-21 years')));
           })->where('user_id', $sender_id)->where('is_active', 1)->count();

           $data['twenty_one_plus']=$twenty_one_plus;


           $total_males = FanClub::whereHas('fan',function($query){
             $query->where('gender','Male');
           })->where('user_id', $sender_id)->where('is_active', 1)->count();

           $data['total_males']=$total_males;



           $total_females = FanClub::whereHas('fan',function($query){
             $query->where('gender','Female');
           })->where('user_id', $sender_id)->where('is_active', 1)->count();

           $data['total_females']=$total_females;

  
           return $this->respond([
            'data' => $data
        ]);
           
    }

    public function ageFilter(Request $request,$type,$date1,$date2=''){
     
       $sender_id=$request->user()->id;


        if($type=='between')
        {
       if($date2){
          $fans= FanClub::whereHas('fan',function($query) use($date1,$date2){
             $query->whereBetween('dob',[$date1, $date2]);
           })->where('user_id', $sender_id)->where('is_active', 1)->get();

       } 
           
        }else if($type=='under'){

            $fans= FanClub::whereHas('fan',function($query) use($date1){
                     $query->where('dob','>',$date1);
                   })->where('user_id', $sender_id)->where('is_active', 1)->get();

           }else if($type='excatly'){
             

            $fans= FanClub::whereHas('fan',function($query) use($date1){
                     $query->where('dob',$date1);
                   })->where('user_id', $sender_id)->where('is_active', 1)->get();

           }else{

             $fans= FanClub::whereHas('fan',function($query) use($date1){
                    $query->where('dob','<',$date1);
                   })->where('user_id', $sender_id)->where('is_active', 1)->get();

           }
     
        $data['fans']=$fans;

         return $this->respond([
            'data' => $data
        ]);


    }


}
