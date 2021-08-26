<?php

namespace App\Http\Controllers;

use App\Models\RequestStatus;
use App\Services\RequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController as BaseController;
use App\Models\Request as RequestEntity;
use PDF;
use Validator;
use App\Http\Resources\Request as RequestResource;
use App\Http\Resources\Status as RequestStatusResource;

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
        return $this->sendResponse($this->requestService->getAll($request->user()), __('request.retrieved'));
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
            return $this->sendError(__('request.no_access'), [], 403);
        }

        return $this->sendResponse(new RequestResource($requestEntity), __('request.retrieved'));
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
            return $this->sendError(__('request.wrong_permission'), [], 403);
        }

        $validator = Validator::make($input, RequestEntity::$rules);

        if ($validator->fails()){
            return $this->sendError(__('request.validation_error'), $validator->errors());
        }

        return $this->sendResponse(
            $this->requestService->create($user->id, $input['subject'], $input['description']),
            __('request.created'),
            201
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
            return $this->sendError(__('request.wrong_permission'), [], 403);
        }

        $validator = Validator::make($input, RequestEntity::$rules);

        if ($validator->fails()){
            return $this->sendError(__('request.validation_error'), $validator->errors());
        }

        return $this->sendResponse(
            $this->requestService->update($requestEntity, $user->id, $input['subject'], $input['description']),
            __('request.updated')
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
            return $this->sendError(__('request.wrong_permission'), [], 403);
        }

        if ($statusId == null || !is_numeric($statusId) || !in_array($statusId, RequestService::ALL_STATUS)
            || $statusId == RequestService::STATUS_COMPLETE) {
            return $this->sendError(__('request.wrong_status'));
        }

        return $this->sendResponse(
            $this->requestService->updateStatus($requestEntity, $request->user()->id, $statusId),
            __('request.updated')
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
        $requestEntity = RequestEntity::findOrFail($id);

        if ($requestEntity->status_id !== RequestService::STATUS_HR_REVIEWED || !$request->user()->isManager()) {
            return $this->sendError(__('request.wrong_permission'), [], 403);
        }

        $requestEntityUpdated = $this->requestService->updateStatus(
            $requestEntity,
            $request->user()->id,
            RequestService::STATUS_COMPLETE
        );

        return $this->sendResponse($requestEntityUpdated, __('request.updated'));
    }

    /**
     * @return JsonResponse
     */
    public function getStatus(): JsonResponse
    {
        return $this->sendResponse(RequestStatusResource::collection(RequestStatus::all()), __('request.status'));
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse|string
     */
    public function generatePDF(Request $request)
    {
        if ($request->user()->isUser()) {
            return $this->sendError(__('request.wrong_permission'), [], 403);
        }

        $pdf = PDF::loadView('request_pdf', ['requests' => $this->requestService->getAll($request->user())]);

        return $pdf->output();
    }
}
