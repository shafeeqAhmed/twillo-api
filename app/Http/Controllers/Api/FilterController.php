<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Fan;
use App\Models\FanClub;
use Carbon\Carbon;
use Twilio\Rest\Client;

class FilterController extends ApiController
{
    
    private $client;

    public function __construct()
    {
        $sid = config('general.twilio_sid');
        $token = config('general.twilio_token');
        $this->client = new Client($sid, $token);
    }
    public function recipientsCount(Request $request){
     
          $sender_id = $request->user()->id;
        
          $total_fans = FanClub::where('user_id', $sender_id)->where('is_active', 1)->count();

           $data['total_fans']=$total_fans;
           $data['eighteen_plus']=$this->fanCount('18 above',$sender_id);
           $data['twenty_one_plus']=$this->fanCount('21 above',$sender_id);
           $data['total_males']=$this->fanCount('male',$sender_id);
           $data['total_females']=$this->fanCount('female',$sender_id);
  
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


    public function getFanByDate(Request $request,$date,$type){
      
        $sender_id=$request->user()->id;
   
       $query = FanClub::Query();
       
       if($type==='before'){
        $query->whereRelation('fan','created_at','<',$date);
       }else if($type==='after'){
        $query->whereRelation('fan','created_at','>',$date);

       }else if ($type==='on'){
        $query->whereRelation('fan','created_at',$date);
       }

        $fans=$query->where('user_id',$sender_id)->where('is_active',1)->get();

      $data['fans']=$fans;

         return $this->respond([
            'data' => $data
        ]);

    }


    private function fanCount($type,$sender_id) {
                $query = FanClub::Query();
                if($type === '18 above')
                $query->whereRelation('fan', 'dob', '<', date('Y-m-d', strtotime('-18 years')));
                else if($type === '21 above')
                $query->whereRelation('fan', 'dob', '<', date('Y-m-d', strtotime('-21 years')));
                else if($type === 'male')
                $query->whereRelation('fan', 'gender', 'Male');
                else if($type === 'female')
                $query->whereRelation('fan', 'gender', 'Female');
                return $query->where('user_id', $sender_id)->where('is_active', 1)->count();
    }


    public function durationFilter(Request $request){

     $sender_id=$request->user()->id;

     $data['last24hours']=$this->calculateDuration('last24h',$sender_id);

    $data['last7days']=$this->calculateDuration('last7days',$sender_id);

    $data['last30days']=$this->calculateDuration('last30d',$sender_id);


         return $this->respond([
            'data' => $data
        ]);


    }

    private function calculateDuration($type,$sender_id)
    {
         
        $last24h = Carbon::now()->subDay();
        $last7d = Carbon::today()->subDays(7);
        $last30d = Carbon::today()->subDays(30);
        
        $query = FanClub::Query();
        if($type === 'last24h')
        $query->whereRelation('fan', 'created_at', '>=', $last24h);
        else if($type === 'last7days')
        $query->whereRelation('fan', 'created_at', '>=', $last7d);
        else if($type === 'last30d')
        $query->whereRelation('fan', 'created_at', '>=', $last30d);
        return $query->where('user_id', $sender_id)->where('is_active', 1)->count();
    }

    public function sendMessageToContacts(Request $request){
       $from=$request->user()->phone_no;
       $sender_id=$request->user()->id;
       $type=$request->type;
       $eighteen_above=$request->eighteen_above;
       $twenty_one_above=$request->twenty_one_above;
       $message=$request->message;

        $eighteen_year_date=date('Y-m-d', strtotime('-18 years'));
        $twenty_year_date=date('Y-m-d', strtotime('-21 years'));

        $query = FanClub::Query();
       if($type=='Between'){
         $fans= $query->whereHas('fan',function($query) use($eighteen_year_date,$twenty_year_date){
            $query->whereBetween('dob', [$twenty_year_date, $eighteen_year_date]);
        });

       }else if($type=='Under'){
        if($eighteen_above==='true'){
            $date=$eighteen_year_date;
        }else{
            $date=$twenty_year_date;
        }

          $query->whereRelation('fan', 'dob', '>', $date);

       }else if($type=='Over'){

        if($eighteen_above==='true'){
            $date=$eighteen_year_date;
        }else{
            $date=$twenty_year_date;
        }

          $query->whereRelation('fan', 'dob', '<', $date);
       }else if($type=='Excatly'){

        if($eighteen_above==='true'){
            $date=$eighteen_year_date;
        }else{
            $date=$twenty_year_date;
        }

          $query->whereRelation('fan', 'dob', $date);
       }

       $fans=  $query->where('user_id', $sender_id)->where('is_active', 1)->get();
        
    if(!empty($fans)){
        foreach($fans as $fan){
           

       $message = $this->client->messages
            ->create(
               $fan['local_number'],
                ["body" => $message, "from" =>  $request->user()->phone_no, "statusCallback" => "https://text-app.tkit.co.uk/api/api/twilio_webhook"]
            );

           

        }
    }

     
       
    }


}
