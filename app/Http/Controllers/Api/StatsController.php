<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BroadCastMessage;
use App\Models\Fan;
use App\Models\FanClub;
use App\Models\MessageLinks;
use App\Models\Messages;
use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;

class StatsController extends ApiController
{
    public function getAgeGroupStats()
    {
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
                foreach ($ranges as $key => $breakpoint) {
                    if ($breakpoint >= $age) {
                        $user->range = $key;
                        break;
                    }
                }

                return $user;
            })
            ->mapToGroups(function ($user, $key) {
                return [$user->range => $user];
            })
            ->map(function ($group) use ($totalFan) {
                //                return count($group);
                return round((count($group) / $totalFan) * 100, 2);
            })
            ->sortKeys();

        return $this->respond([
            'data' => [
                'ageGroup' => $data,
            ]
        ]);
    }
    public function getGenderGroupStats()
    {
        $genderType = [ // the start of each age-range.
            'Male' => 'Male',
            'Female' => 'Female',
            'Non-Binary' => 'Non-Binary',
            'Other' => 'Other',
        ];

        $totalFan = Fan::count();
        $data = Fan::get()
            ->map(function ($user) use ($genderType) {
                $gender = $user->gender;
                foreach ($genderType as $key => $breakpoint) {
                    if ($breakpoint == $gender) {
                        $user->range = $key;
                        break;
                    }
                }

                return $user;
            })
            ->mapToGroups(function ($user, $key) {
                return [$user->range => $user];
            })
            ->map(function ($group) use ($totalFan) {
                return round((count($group) / $totalFan) * 100, '2');
            })
            ->sortKeys();
        return $this->respond([
            'data' => [
                'genderGroup' => $data
            ]
        ]);
    }
    public function getCityGroupStats()
    {
        $cityGroup = Fan::groupBy('city')->select('city', DB::raw('count(*) as total'))->orderBy('total', 'desc')
            ->get()
            ->take(10);
        $cities = $cityGroup->map(function ($city) {
            return $city->city;
        });
        $series = $cityGroup->map(function ($city) {
            return $city->total * rand(333, 777);
        });
        return $this->respond([
            'data' => [
                'cities' => $cities,
                'series' => $series,
            ]
        ]);
    }
    public function getCountryGroupStats()
    {
        $contriesGroup = Fan::groupBy('fans.country_id')->select('fans.country_id', 'c.country_name', DB::raw('count(*) as total'))->orderBy('total', 'desc')
            ->join('countries as c', 'c.id', '=', 'fans.country_id')
            ->whereNotNull('country_id')
            ->get()
            ->take(10);
        $countries = $contriesGroup->map(function ($country) {
            return $country->country_name;
        });
        $series = $contriesGroup->map(function ($country) {
            return $country->total * rand(333, 777);
        });
        return $this->respond([
            'data' => [
                'countries' => $countries,
                'series' => $series,
            ]
        ]);
    }
    public function getMontyRegistrationStats()
    {
        $data = Fan::select(DB::raw("count(*) as total, date_format(created_at, '%M') as month, date_format(created_at, '%m') as numeric_month,date_format(created_at, '%m/%d/%Y') as date"))
            ->whereYear('created_at', now()->subYear()->year)
            ->groupBy('month')
            ->orderBy('numeric_month', 'asc')
            ->get();
        $dates = $data->map(function ($fan) {
            return $fan->date;
        });

        $series = $data->map(function ($fan) {
            return $fan->total * rand(12, 30);
        });
        return $this->respond([
            'data' => [
                'list' => $data,
                'dates' => $dates,
                'series' => $series,
            ]
        ]);
    }
    public function averageClickRate(Request $request)
    {
        $request->validate([
            'start' => 'nullable|date',
            'end' => 'nullable|date',
        ]);
        $query_1 =  MessageLinks::where('influencer_id', $request->user()->id);

        if ($request->has('start') && $request->has('start')) {
            $query_1->whereBetween('created_at', [$request->start, $request->end]);
        }
        $totalLinks = $query_1->count();
        $query_2 = MessageLinks::where('influencer_id', $request->user()->id)->where('is_visited', 1);

        if ($request->has('start') && $request->has('start')) {
            $query_2->whereBetween('created_at', [$request->start, $request->end]);
        }

        $totalVisitedLinks = $query_2->count();
        $averageRate = 0;
        if ($totalVisitedLinks > 0 && $totalLinks > 0) {
            $averageRate = round(($totalVisitedLinks / $totalLinks) * 100, 2);
        }
        return $this->respond([
            'data' => [
                'averageClickRate' => $averageRate
            ]
        ]);
    }
    public function averageResponseRate(Request $request)
    {
        $request->validate([
            'start' => 'required|date',
            'end' => 'required|date',
        ]);

        $totalMessages = Messages::where('user_id', $request->user()->id)->whereBetween('created_at', [$request->start, $request->end])->count();
        $totalRespondedMessage = Messages::where('user_id', $request->user()->id)->whereIsReplied(1)->whereBetween('created_at', [$request->start, $request->end])->count();

        $averageRate = 0;
        if ($totalMessages > 0 && $totalRespondedMessage > 0) {
            $averageRate = round(($totalRespondedMessage / $totalMessages) * 100, 2);
        }
        return $this->respond([
            'data' => [
                'averageResponseRate' => $averageRate
            ]
        ]);
    }
    public function fanReach(Request $request)
    {
        $request->validate([
            'start' => 'required|date',
            'end' => 'required|date',
        ]);
        $totalMessages = Messages::where('user_id', $request->user()->id)->whereBetween('created_at', [$request->start, $request->end])->count();
        return $this->respond([
            'data' => [
                'fanReached' => $totalMessages
            ]
        ]);
    }

    public function topActiveContact(Request $request)
    {
        $request->validate([
            'start' => 'required|date',
            'end' => 'required|date',
        ]);
        $totalMessages = Messages::where('user_id', $request->user()->id)
            ->whereBetween('created_at', [$request->start, $request->end])
            ->select(DB::raw('count(*) as totalMessage'))
            ->groupBy('fan_id')
            ->orderBy('totalMessage', 'desc')
            ->where('is_replied', 1)
            ->take(10)
            ->get();
        return $this->respond([
            'data' => [
                'fanReached' => $totalMessages
            ]
        ]);
    }
    public function topInActiveContact(Request $request)
    {
        $request->validate([
            'start' => 'required|date',
            'end' => 'required|date',
        ]);
        $totalMessages = Messages::where('user_id', $request->user()->id)
            ->whereBetween('created_at', [$request->start, $request->end])
            ->select(DB::raw('count(*) as totalMessage'))
            ->groupBy('fan_id')
            ->orderBy('totalMessage', 'desc')
            ->where('is_replied', 0)
            ->take(10)
            ->get();
        return $this->respond([
            'data' => [
                'fanReached' => $totalMessages
            ]
        ]);
    }
    public function noOfText(Request $request)
    {
        $request->validate([
            'start' => 'nullable|date',
            'end' => 'nullable|date',
            'duration' => ['nullable', "in:week,month,year"]
        ]);
        $totalMessages = 0;
        $query = Messages::where('user_id', $request->user()->id);

        if ($request->has('start') && $request->has('end') && !$request->has('duration')) {
            $query->whereBetween('created_at', [$request->start, $request->end]);
        }

        if ($request->has('duration')) {
            if ($request->duration == 'week') {
                $query->whereBetween('created_at', [Carbon::now()->subWeek()->format('Y-m-d'), Carbon::now()->format('Y-m-d')]);
            }
            if ($request->duration == 'month') {
                $query->whereBetween('created_at', [Carbon::now()->subMonth()->format('Y-m-d'), Carbon::now()->format('Y-m-d')]);
            }
            if ($request->duration == 'year') {
                $query->whereBetween('created_at', [Carbon::now()->subYear()->format('Y-m-d'), Carbon::now()->format('Y-m-d')]);
            }
        }

        $totalMessages = $query->count();
        return $this->respond([
            'data' => [
                'messageCount' => $totalMessages
            ]
        ]);
    }


    public function noOfContact(Request $request)
    {
        $request->validate([
            'start' => 'nullable|date',
            'end' => 'nullable|date',
            'duration' => ['nullable', "in:week,month,year"]
        ]);
        $totalContact = 0;
        $query = FanClub::join('fans as f', 'f.id', '=', 'fan_clubs.fan_id')
            ->where('fan_clubs.user_id', $request->user()->id);
        if ($request->has('start') && $request->has('end') && !$request->has('duration')) {
            $query->whereBetween('f.created_at', [$request->start, $request->end]);
        }

        if ($request->has('duration')) {
            if ($request->duration == 'week') {
                $query->whereBetween('f.created_at', [Carbon::now()->subWeek()->format('Y-m-d'), Carbon::now()->format('Y-m-d')]);
            }
            if ($request->duration == 'month') {
                $query->whereBetween('f.created_at', [Carbon::now()->subMonth()->format('Y-m-d'), Carbon::now()->format('Y-m-d')]);
            }
            if ($request->duration == 'year') {
                $query->whereBetween('f.created_at', [Carbon::now()->subYear()->format('Y-m-d'), Carbon::now()->format('Y-m-d')]);
            }
        }

        $totalContact = $query->select('f.id')->count();
        return $this->respond([
            'data' => [
                'contactCount' => $totalContact
            ]
        ]);
    }
    public function broadCastMessages(Request $request)
    {
        $broadCastMessages = BroadCastMessage::where('user_id', $request->user()->id)
            ->select(
                'id',
                'broadcast_uuid',
                'message',
                'type',
                'scheduled_at_local_time'
            )
            ->with('clickRate')
            ->with('responseRate')
            ->get();

        foreach ($broadCastMessages as &$message) {
            $message['click_rate_percentate'] = !empty($message['clickRate']) ? $message['clickRate'][0]['clickRate'] . '%' : '0%';
            $message['response_rate_percentate'] = !empty($message['responseRate']) ? $message['responseRate'][0]['responseRate'] . '%' : '0%';
        }
        return $this->respond([
            'data' => [
                'broadCastMessage' => $broadCastMessages
            ]
        ]);
    }
    public function broadCastMessagesList(Request $request)
    {
        $request->validate([
            'broadcast_uuid' => 'required',
        ]);
        $broadCastMessages = BroadCastMessage::where('broadcast_uuid', $request->broadcast_uuid)
            ->select(
                'id',
                'broadcast_uuid',
                'message',
                'type',
                'scheduled_at_local_time'
            )
            ->with('clickRate')
            ->with('responseRate')
            ->with('messages')
            ->first();
        return $this->respond([
            'data' => [
                'broadCastMessage' => $broadCastMessages
            ]
        ]);
    }
}
