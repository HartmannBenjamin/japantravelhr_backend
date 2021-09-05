<?php

namespace App\Services;

use App\Http\Resources\Request as RequestResource;
use App\Models\Request as RequestEntity;
use App\Models\RequestLog;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Class RequestService
 *
 * @package App\Services
 */
class RequestService
{
    public const STATUS_OPEN        = 1;
    public const STATUS_HR_REVIEWED = 2;
    public const STATUS_PROCESSED   = 3;
    public const ALL_STATUS         = [
        self::STATUS_OPEN,
        self::STATUS_PROCESSED,
        self::STATUS_HR_REVIEWED
    ];

    /**
     * Get all requests in the database (depending on user role)
     *
     * @param User $user
     *
     * @return AnonymousResourceCollection
     */
    public function getAll(User $user): AnonymousResourceCollection
    {
        if ($user->isUser()) {
            $requestEntities = RequestEntity::where('user_id', $user->id)->get();
        } elseif ($user->isManager()) {
            $requestEntities = RequestEntity::where(
                'status_id',
                '=',
                self::STATUS_HR_REVIEWED
            )->get();
        } else {
            $requestEntities = RequestEntity::all();
        }

        return RequestResource::collection($requestEntities);
    }

    /**
     * Store a new request in the database
     *
     * @param int    $userId
     * @param string $subject
     * @param string $description
     *
     * @return RequestResource
     */
    public function create(int $userId, string $subject, string $description): RequestResource
    {
        $requestEntity              = new RequestEntity();
        $requestEntity->subject     = $subject;
        $requestEntity->description = $description;
        $requestEntity->user_id     = $userId;
        $requestEntity->status_id   = self::STATUS_OPEN;
        $requestEntity->save();

        $requestLog             = new RequestLog();
        $requestLog->message    = __('request.log_created');
        $requestLog->user_id    = $userId;
        $requestLog->request_id = $requestEntity->id;
        $requestLog->save();

        return new RequestResource($requestEntity);
    }

    /**
     * Update information of a specific request
     *
     * @param RequestEntity $requestEntity
     * @param string        $subject
     * @param string        $description
     *
     * @return RequestResource
     */
    public function update(
        RequestEntity $requestEntity,
        string $subject,
        string $description
    ): RequestResource {
        $requestEntity->subject     = $subject;
        $requestEntity->description = $description;
        $requestEntity->save();

        $requestLog             = new RequestLog();
        $requestLog->message    = __('request.log_updated');
        $requestLog->user_id    = $requestEntity->user_id;
        $requestLog->request_id = $requestEntity->id;
        $requestLog->save();

        return new RequestResource($requestEntity);
    }

    /**
     * Update the status of a specific request
     *
     * @param RequestEntity $requestEntity
     * @param int           $userId
     * @param int           $statusId
     *
     * @return RequestResource
     */
    public function updateStatus(RequestEntity $requestEntity, int $userId, int $statusId): RequestResource
    {
        $requestEntity->status_id = $statusId;
        $requestEntity->save();

        $requestLog             = new RequestLog();
        $requestLog->message    = __('request.log_status_updated') . $this->getNameStatus($statusId);
        $requestLog->user_id    = $userId;
        $requestLog->request_id = $requestEntity->id;
        $requestLog->save();

        return new RequestResource($requestEntity);
    }

    /**
     * Get name of status id
     *
     * @param int $statusId
     *
     * @return string
     */
    private function getNameStatus(int $statusId): string
    {
        switch ($statusId) {
            case self::STATUS_OPEN:
            return __('request.status_open');
            case self::STATUS_PROCESSED:
            return __('request.status_processed');
        }

        return __('request.status_hr_reviewed');
    }

    /**
     * Validation of request data
     *
     * @param $input
     *
     * @return \Illuminate\Contracts\Validation\Validator|\Illuminate\Validation\Validator
     */
    public function validateRequestData($input)
    {
        return Validator::make(
            $input,
            [
                'subject' => 'required|string|min:4|max:200',
                'description' => 'required|string|min:10|max:500',
            ]
        );
    }
}
