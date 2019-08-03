<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Customer extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'         => $this->id,
            'type'       => 'customers',
            'attributes' => [
                'reg_type'               => $this->reg_type,
                'name'                   => $this->name,
                'email'                  => $this->email,
                'mobile_number'          => $this->mobile_number,
                'mobile_country_prefix'  => $this->mobile_country_prefix,
                'mobile_national_number' => $this->mobile_national_number,
                'account_kit_id'         => $this->account_kit_id,
                'facebook_id'            => $this->facebook_id,
                'facebook_url'           => $this->facebook_url,
                'meta'                   => $this->meta,
                'created_at'             => $this->created_at,
                'updated_at'             => $this->updated_at,
            ],
        ];
    }
}
