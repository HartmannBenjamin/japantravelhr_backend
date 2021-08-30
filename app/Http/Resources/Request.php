<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class Request
 *
 * @mixin \App\Models\Request
 * */
class Request extends JsonResource
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
        $logsData = [];

        foreach ($this->logs as $log) {
            $logsData[] = array_merge($log->toArray(), ['user' => new User($log->user)]);
        }

        usort(
            $logsData,
            function ($a, $b) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            }
        );

        return [
            'id' => $this->id,
            'subject' => $this->subject,
            'description' => $this->description,
            'status' => $this->status,
            'created_by' => new User($this->user),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'logs' => $logsData
        ];
    }
}
