<?php

namespace App\Http\Resources;

use App\Models\RequestStatus;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class Status
 *
 * @mixin RequestStatus
 * */
class Status extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'color_code' => $this->color_code,
            'description' => $this->description
        ];
    }
}
