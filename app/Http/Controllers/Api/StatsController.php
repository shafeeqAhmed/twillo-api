<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Fan;
use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;
class StatsController extends ApiController
{
    public function getAgeGroupStats() {
        $ranges = [ // the start of each age-range.
            '13-17' => 13,
            '18-24' => 18,
            '25-34' => 25,
            '35-44' => 25,
            '45-54' => 45,
            '55-64' => 55,
            '65+' => 65,
        ];

        $totalFan = Fan::count();
        $data = Fan::get()
            ->map(function ($user) use ($ranges) {
                $age = Carbon::parse($user->dob)->age;
                foreach($ranges as $key => $breakpoint)
                {
                    if ($breakpoint >= $age)
                    {
                        $user->range = $key;
                        break;
                    }
                }

                return $user;
            })
            ->mapToGroups(function ($user, $key) {
                return [$user->range => $user];
            })
            ->map(function ($group) use ($totalFan){
//                return count($group);
                return round((count($group)/$totalFan)*100,2);
            })
            ->sortKeys();

        return $this->respond([
            'data' => [
                'age-group' => $data,
            ]
        ]);
    }
    public function getGenderGroupStats() {
        $genderType = [ // the start of each age-range.
            'Male'=>'Male',
            'Female'=>'Female',
            'Non-Binary'=>'Non-Binary',
            'Other'=>'Other',
        ];

        $totalFan = Fan::count();
        $data = Fan::get()
            ->map(function ($user) use ($genderType) {
                $gender = $user->gender;
                foreach($genderType as $key => $breakpoint)
                {
                    if ($breakpoint == $gender)
                    {
                        $user->range = $key;
                        break;
                    }
                }

                return $user;
            })
            ->mapToGroups(function ($user, $key) {
                return [$user->range => $user];
            })
            ->map(function ($group) use ($totalFan){
                return round((count($group)/$totalFan)*100,'2');
            })
            ->sortKeys();
        return $this->respond([
            'data' => [
                'age-group' => $data
            ]
        ]);
    }
    public function getCityGroupStats() {
        $cityGroup = Fan::groupBy('city')->select('city', DB::raw('count(*) as total'))->orderBy('total','desc')
            ->get()
        ->take(5);
        return $this->respond([
            'data' => [
                'age-group' => $cityGroup
            ]
        ]);

    }
    public function getMontyRegistrationStats() {
        $data = Fan::select(DB::raw("count(*) as total, date_format(created_at, '%M') as month, date_format(created_at, '%m') as numeric_month,date_format(created_at, '%d/%m/%Y') as date"))
            ->whereYear('created_at', now()->subYear()->year)
            ->groupBy('month')
            ->orderBy('numeric_month','asc')
            ->get();
//        dd($data);
        $dates = $data->map(function($fan){
            return $fan->date;
        });

        $series = $data->map(function($fan){
            return $fan->total;
        });
        return $this->respond([
            'data' => [
                'list' => $data,
                'dates' => $dates,
                'series' => $series,
            ]
        ]);
    }



}
