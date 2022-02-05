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

    public function findTopUsers($percentageNumber) {
        $rawQuery = "(send_count+received_count)/100";
        return  FanClub::select('*')
            ->selectRaw("{$rawQuery} AS percentage")
            ->whereRaw("{$rawQuery}  >= $percentageNumber")
            ->get();

    }
    public function testMessage() {
        $message = $this->client->messages
            ->create(
                '+18454098524',
                ["body" => 'Test message local', "from" =>  '+447897037950', "statusCallback" => "https://text-app.tkit.co.uk/twillo-api/api/twilio_webhook"]
            );
        dd($message);
    }
    public function recipientsCount(Request $request){
     
          $sender_id = $request->user()->id;
        
          $total_fans = FanClub::where('user_id', $sender_id)->where('is_active', 1)->count();

           $data['total_fans']=$total_fans;
           $data['eighteen_plus']=$this->fanCount('18 above',$sender_id);
           $data['twenty_one_plus']=$this->fanCount('21 above',$sender_id);
           $data['total_males']=$this->fanCount('male',$sender_id);
           $data['total_females']=$this->fanCount('female',$sender_id);
           $data['top_5_percentage']=count($this->findTopUsers(5));
           $data['top_10_percentage']=count($this->findTopUsers(10));
           $data['top_25_percentage']=count($this->findTopUsers(25));

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

       $sender_id=$request->user()->id;

       $query = Fan::join('fan_clubs as fc','fc.fan_id','fans.id')
           ->where('fc.user_id','=',$sender_id)
           ->where('fc.is_active','=',1);

        if(!empty($request->activity['activity'])) {
            $rawQuery = "(fc.send_count+fc.received_count)/100";
                $query->selectRaw("{$rawQuery} AS percentage")
                ->whereRaw("{$rawQuery}  >= ".$request->activity['activity']);
        }

       $ageQuery = "TIMESTAMPDIFF(YEAR, DATE(fans.dob), current_date)";
        $query->select('fans.*')
            ->select('fc.local_number')->selectRaw("{$ageQuery} AS age");
       // if gender is set
       if(!empty($request->activity['gender'])) {
           $query->where('fans.gender', '=', ucfirst($request->activity['gender']));
       }

       if(!empty($request->location['radius']) && !empty($request->location['lat']) && !empty($request->location['lng'])) {
            $this->applyDistanceFilterWithRadiusPoints($query,$request->location);
       }

       //age filter
        if(!empty($request->age['age'])){
            if($request->age['age'] == '18+') {
                $query->whereRaw("{$ageQuery} > 18" );
            }
            if($request->age['age'] == '21+') {
                $query->whereRaw("{$ageQuery} > 21" );

//                $twenty_year_date=date('Y-m-d', strtotime('-21 years'));
//                $query->where('dob','<=',$twenty_year_date);
            }
        }

        if(!empty($request->age['customFilterType'])){

            if($request->age['customFilterType'] == 'Between') {
                $query->whereRaw("{$ageQuery} > ".$request->age['customStartAge']."  && {$ageQuery} < ".$request->age['customEndAge'] );
            }
            if($request->age['customFilterType'] == 'Under') {
                $query->whereRaw("{$ageQuery} < ".$request->age['customStartAge'] );
            }
            if($request->age['customFilterType'] == 'Over') {
                $query->whereRaw("{$ageQuery} > ".$request->age['customStartAge'] );
            }
            if($request->age['customFilterType'] == 'Exactly') {
                $query->whereRaw("{$ageQuery} = ".$request->age['customStartAge'] );
            }
            }

        if(!empty($request->joinDate['date'])) {
            if($request->joinDate['date'] == 'last24hours') {
                $query->where('fans.created_at', '>=', Carbon::now()->subDay());
            }
            if($request->joinDate['date'] == 'last7days') {
                $query->where('fans.created_at', '>=', Carbon::today()->subDays(7));
            }
            if($request->joinDate['date'] == 'last30days') {
                $query->where('fans.created_at', '>=', Carbon::today()->subDays(30));
            }

        }
        if(!empty($request->joinDate['search_type'])) {
            $start_date = $request->joinDate['customStartDate'];
            if($request->joinDate['search_type'] == 'Between') {
                $query->whereBetween('fans.created_at',[$start_date,$request->joinDate['customEndDate']]);
                return response()->json($query->get());
            }
             if($request->joinDate['search_type'] == 'Before') {
                 $query->where('fans.created_at','<',$start_date);
            }
             if($request->joinDate['search_type'] == 'After') {
                 $query->where('fans.created_at','>',$start_date);

            }
             if($request->joinDate['search_type'] == 'On') {
                 $query->where('fans.created_at','=',$start_date);

            }

        }
       $fans=  $query->get();
        if(count($fans) == 0) {
            return response()->json(['status'=>false,'message'=>'Sorry there is no record exist!','data'=>[]]);
        }
    if(!empty($fans)){
        foreach($fans as $fan){
            $this->client->messages
            ->create(
               $fan['local_number'],
                ["body" => $request->message, "from" =>  $request->user()->phone_no, "statusCallback" => "https://text-app.tkit.co.uk/twillo-api/api/twilio_webhook"]
            );
        }
    }
    return response()->json(['status'=>true,'message'=>'Message Has been sent Successfully!','data'=>[]]);
    }
    public function applyDistanceFilterWithRadiusPoints($query, $params) {
            $haversine = "(6371 * acos(cos(radians(" . $params['lat'] . "))
                        * cos(radians(`latitude`))
                        * cos(radians(`longitude`)
                        - radians(" . $params['lng'] . "))
                        + sin(radians(" . $params['lat'] . "))
                        * sin(radians(`latitude`))))";
            //set default start radius 0
            $start_radius = 0;
            $query->whereRaw("{$haversine} > " . $start_radius);
            if (!empty($params['radius'])) {
                $query->whereRaw("{$haversine} < " . $params['radius']);
            }
            $query->select('*')
                ->selectRaw("{$haversine} AS distance")
                ->orderBy('distance', 'ASC');

            if (!empty($params['radius'])) {
                $query->whereRaw("{$haversine} > " . $start_radius)
                    ->whereRaw("{$haversine} < " . $params['radius']);
            }
        return $query;
    }


}
