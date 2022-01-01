<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
class ChatMessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
             'id'=> $this->id,
    'align'=> $this->isSender()?'right':'',
    'image'=> $this->user->profile_photo_path,
    'name'=>  $this->user->name,
    'message'=> $this->message,
    'time'=> $this->created_at->format('h:i a')
        ];
    }
}
