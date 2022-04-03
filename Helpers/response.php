<?php

use Carbon\Carbon;

if (!function_exists('getInfluencerContactsResponse')) {
    function getInfluencerContactsResponse($records)
    {
        $result = [];
        foreach ($records as $record) {
            $data = [];
            $data['id'] = $record['id'];
            $data['fan_club_uuid'] = $record['fan_club_uuid'];
            $data['local_number'] = $record['local_number'];
            $data['fan_id'] = $record['fan_id'];
            $data['temp_id'] = $record['temp_id'];
            $data['created_at'] = $record['created_at'];
            $data['temp_id'] = $record['temp_id'];
            $data['fname'] = !empty($record['fan']) ? $record['fan']['fname'] : '';
            $data['age'] = !empty($record['fan']) ? $record['fan']['dob'] : '';
            $data['city'] = !empty($record['fan']) ? $record['fan']['city'] : '';
            $data['country'] = (!empty($record['fan']) && !empty($record['fan']['country'])) ? $record['fan']['country']['country_name'] : '';
            $result[] = $data;
        }
        return $result;
    }
}
