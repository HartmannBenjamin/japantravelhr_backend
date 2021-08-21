<?php

namespace App\Http\Controllers;

use App\Services\RequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController as BaseController;
use App\Models\Request as RequestEntity;
use Validator;
use App\Http\Resources\Request as RequestResource;

/**
 * Class RequestController
 * @package App\Http\Controllers
 */
class RequestController extends BaseController
{
    protected $requestService;

    /**
     * Instantiate a new controller instance.
     *
     * @param RequestService $requestService
     */
    public function __construct(RequestService $requestService)
    {
        $this->requestService = $requestService;
    }

    /**
     * Display a listing of the request.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        return $this->sendResponse($this->requestService->getAll($request->user()), 'Requests retrieved successfully');
    }

    /**
     * Display the specified request.
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function show(int $id, Request $request): JsonResponse
    {
        $requestEntity = RequestEntity::findOrFail($id);
        $user = $request->user();

        if ($user->isUser() && $user->id != $requestEntity->user_id) {
            return $this->sendError('You don\'t have access to this request', [], 403);
        }

        return $this->sendResponse(new RequestResource($requestEntity), 'Request retrieved successfully.');
    }

    /**
     * Store a newly created request in storage.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        $input = $request->all();

        if (!$user->isUser()) {
            return $this->sendError("You don't have the permission", [], 403);
        }

        $validator = Validator::make($input, [
            'subject' => 'required',
            'description' => 'required',
        ]);

        if ($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }

        return $this->sendResponse(
            $this->requestService->create($user->id, $input['subject'], $input['description']),
            'Request created successfully.'
        );
    }

    /**
     * Update the specified request in storage.
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function update(int $id, Request $request): JsonResponse
    {
        $requestEntity = RequestEntity::findOrFail($id);
        $user = $request->user();
        $input = $request->all();

        if (!$user->isUser() || $requestEntity->status_id != RequestService::STATUS_OPEN
            || $user->id != $requestEntity->user_id) {
            return $this->sendError("You don't have the permission", [], 403);
        }

        $validator = Validator::make($input, [
            'subject' => 'required',
            'description' => 'required',
        ]);

        if ($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }

        return $this->sendResponse(
            $this->requestService->update($requestEntity, $user->id, $input['subject'], $input['description']),
            'Request updated successfully.'
        );
    }

    /**
     * Update the status of  the specified request in storage for HR role.
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function updateStatusHR(int $id, Request $request): JsonResponse
    {
        $requestEntity = RequestEntity::findOrFail($id);
        $statusId = $request->get('status_id');

        if (!$request->user()->isHR() || $requestEntity->status_id == RequestService::STATUS_COMPLETE) {
            return $this->sendError("You don't have the permission", [], 403);
        }

        if ($statusId == null || !is_numeric($statusId) || $statusId == RequestService::STATUS_COMPLETE) {
            return $this->sendError('Wrong status provided');
        }

        return $this->sendResponse(
            $this->requestService->updateStatus($requestEntity, $request->user()->id, $statusId),
            'Request updated successfully.'
        );
    }

    /**
     * Update the status of  the specified request in storage for Manager role.
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function updateStatusManager(int $id, Request $request): JsonResponse
    {
        if (!$request->user()->isManager()) {
            return $this->sendError("You don't have the permission", [], 403);
        }

        $requestEntity = $this->requestService->updateStatus(
            RequestEntity::findOrFail($id),
            $request->user()->id,
            RequestService::STATUS_COMPLETE
        );

        return $this->sendResponse($requestEntity, 'Request updated successfully.');
    }
}
