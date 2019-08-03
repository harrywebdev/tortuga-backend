<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Tortuga\SettingsName;

class Settings extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $data = [
            'id'         => $this->resource['id'],
            'type'       => 'settings',
            'attributes' => [],
        ];

        $isOpenForBooking = (string)SettingsName::IS_OPEN_FOR_BOOKING();

        $data['attributes'][$isOpenForBooking] = $this->resource[$isOpenForBooking];

        return $data;
    }
}
