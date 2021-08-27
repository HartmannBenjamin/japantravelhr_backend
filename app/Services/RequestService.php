<?php

namespace App\Services;

use App\Http\Resources\Request as RequestResource;
use App\Models\Request as RequestEntity;
use App\Models\RequestLog;
use App\Models\User;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RequestService
{
    public const STATUS_OPEN = 1;
    public const STATUS_HR_REVIEWED = 2;
    public const STATUS_PROCESSED = 3;
    public const ALL_STATUS = [
        self::STATUS_OPEN,
        self::STATUS_PROCESSED,
        self::STATUS_HR_REVIEWED
    ];

    /**
     * @param User $user
     *
     * @return AnonymousResourceCollection
     */
    public function getAll(User $user): AnonymousResourceCollection
    {
        if ($user->isUser()) {
            $requestEntities = RequestEntity::where('user_id', $user->id)->get();
        } else if ($user->isManager()) {
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
     * @param int $userId
     * @param string $subject
     * @param string $description
     *
     * @return RequestResource
     */
    public function create(int $userId, string $subject, string $description): RequestResource
    {
        $requestEntity = new RequestEntity();
        $requestEntity->subject = $subject;
        $requestEntity->description = $description;
        $requestEntity->user_id = $userId;
        $requestEntity->status_id = self::STATUS_OPEN;
        $requestEntity->save();

        $requestLog = new RequestLog();
        $requestLog->message = 'Request created by user';
        $requestLog->user_id = $userId;
        $requestLog->request_id = $requestEntity->id;
        $requestLog->save();

        return new RequestResource($requestEntity);
    }

    /**
     * @param RequestEntity $requestEntity
     * @param int $userId
     * @param string $subject
     * @param string $description
     *
     * @return RequestResource
     */
    public function update(
        RequestEntity $requestEntity,
        int $userId,
        string $subject,
        string $description
    ): RequestResource
    {
        $requestEntity->subject = $subject;
        $requestEntity->description = $description;
        $requestEntity->save();

        $requestLog = new RequestLog();
        $requestLog->message = 'Request updated by user';
        $requestLog->user_id = $userId;
        $requestLog->request_id = $requestEntity->id;
        $requestLog->save();

        return new RequestResource($requestEntity);
    }

    /**
     * @param RequestEntity $requestEntity
     * @param int $userId
     * @param int $statusId
     *
     * @return RequestResource
     */
    public function updateStatus(RequestEntity $requestEntity, int $userId, int $statusId): RequestResource
    {
        $requestEntity->status_id = $statusId;
        $requestEntity->save();

        $requestLog = new RequestLog();
        $requestLog->message = 'Request status updated to ' . $this->getNameStatus($statusId);
        $requestLog->user_id = $userId;
        $requestLog->request_id = $requestEntity->id;
        $requestLog->save();

        return new RequestResource($requestEntity);
    }

    /**
     * @param int $statusId
     *
     * @return string
     */
    private function getNameStatus(int $statusId): string
    {
        switch ($statusId) {
            case self::STATUS_OPEN:
                return '"Open"';
            case self::STATUS_PROCESSED:
                return '"Processed"';
        }

        return '"HR Reviewed"';
    }
}
