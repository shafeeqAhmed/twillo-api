<?php

namespace App\Http\Traits;

use App\Models\MessageLinks;
use Illuminate\Support\Str;

trait CommonHelper {
    public static function filterAndReplaceLink($data){
        $text = $data->message;
        preg_match_all('#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $text, $match);
        if(!empty($match[0])){
            $links = [];
            foreach($match[0] as $key=>$item){
                if(!empty($item)){
                    $link = CommonHelper::mapLinkOnTable($item, $data);
                    $newLink = route('count_and_redirect').'?uuid='.$link['message_link_uuid'];
                    $links[] = $link;
                    $text = str_replace($item,$newLink,$text);
                }
            }
            MessageLinks::insert($links);
        }
        return $text;
    }

    public static function mapLinkOnTable($item, $data): array
    {
        return [
            'message_link_uuid' => Str::uuid()->toString(),
            'influencer_id' => $data->user()->id,
            'fanclub_id' => $data->receiver_id,
            'link' => $item
        ];
    }
}