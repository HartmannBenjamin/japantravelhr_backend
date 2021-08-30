<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class User
 *
 * @mixin \App\Models\User
 * */
class User extends JsonResource
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
            'email' => $this->email,
            'name' => $this->name,
            'image_url' => url('/images/' . $this->image_name),
            'role' => $this->role,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    public function getJWTIdentifier()
    {
        // TODO: Implement getJWTIdentifier() method.
    }

    public function getJWTCustomClaims()
    {
        // TODO: Implement getJWTCustomClaims() method.
    }
}
