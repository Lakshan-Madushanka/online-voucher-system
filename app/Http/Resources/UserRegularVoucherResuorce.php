<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserRegularVoucherResuorce extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
       // print_r($this);
        //dd();
        return [
                'id' => $this->id,
                'image' => $this->image,
                'price' => $this->price,
                'quantity' => $this->quantity,
                'terms' => $this->terms,
                'type' => $this->type,
                'receiver_id' => $this->when($this->type === 'presented', $this->receiver_id),
                'validity' => $this->validity,
                'purchased_at' => $this->created_at

        ];
    }
}
