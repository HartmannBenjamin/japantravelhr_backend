<?php

namespace App\Http\Controllers;

use App\Models\RequestStatus;
use App\Services\RequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController as BaseController;
use App\Models\Request as RequestEntity;
use PDF;
use App\Http\Resources\Request as RequestResource;
use App\Http\Resources\Status as RequestStatusResource;

/**
 * Class RequestController
 *
 * @package App\Http\Controllers
 */
class RequestController extends BaseController
{
    /**
     * @var RequestService $requestService
     */
    protected $requestService;

    /**
     * RequestController constructor.
     *
     * @param RequestService $requestService
     */
    public function __construct(RequestService $requestService)
    {
        $this->requestService = $requestService;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        return $this->sendResponse($this->requestService->getAll($request->user()), __('request.retrieved'));
    }

    /**
     * @param int     $id
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

        $validator = $this->requestService->validateRequestData($input);

        if ($validator->fails()) {
            return $this->sendError(__('request.validation_error'), $validator->errors());
        }

        try {
            $requestEntity = $this->requestService->create($user->id, $input['subject'], $input['description']);
        } catch (Exception $e) {
            return $this->sendError(__('other.error'), [$e->getMessage()]);
        }

        return $this->sendResponse($requestEntity, __('request.created'), 201);
    }

    /**
     * @param int     $id
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
            || $user->id != $requestEntity->user_id
        ) {
            return $this->sendError(__('request.wrong_permission'), [], 403);
        }

        $validator = $this->requestService->validateRequestData($input);

        if ($validator->fails()) {
            return $this->sendError(__('request.validation_error'), $validator->errors());
        }

        try {
            $requestEntityUpdated = $this->requestService->update(
                $requestEntity,
                $input['subject'],
                $input['description']
            );
        } catch (Exception $e) {
            return $this->sendError(__('other.error'), [$e->getMessage()]);
        }

        return $this->sendResponse($requestEntityUpdated, __('request.updated'));
    }

    /**
     * @param int     $id
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function updateStatusHR(int $id, Request $request): JsonResponse
    {
        $requestEntity = RequestEntity::findOrFail($id);
        $statusId = $request->get('status_id');
        $user = $request->user();

        if (!$user->isHR()) {
            return $this->sendError(__('request.wrong_permission'), [], 403);
        }

        if ($statusId == null || !is_numeric($statusId) || !in_array($statusId, RequestService::ALL_STATUS)) {
            return $this->sendError(__('request.wrong_status'));
        }

        try {
            $requestEntityUpdated = $this->requestService->updateStatus($requestEntity, $user->id, $statusId);
        } catch (Exception $e) {
            return $this->sendError(__('other.error'), [$e->getMessage()]);
        }

        return $this->sendResponse($requestEntityUpdated, __('request.updated'));
    }

    /**
     * @param int     $id
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function updateStatusManager(int $id, Request $request): JsonResponse
    {
        $requestEntity = RequestEntity::findOrFail($id);
        $user = $request->user();

        if ($requestEntity->status_id != RequestService::STATUS_HR_REVIEWED || !$user->isManager()) {
            return $this->sendError(__('request.wrong_permission'), [], 403);
        }

        try {
            $requestEntityUpdated = $this->requestService->updateStatus(
                $requestEntity,
                $user->id,
                RequestService::STATUS_PROCESSED
            );
        } catch (Exception $e) {
            return $this->sendError(__('other.error'), [$e->getMessage()]);
        }

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

        $requestsCollection = RequestResource::collection(
            RequestEntity::orderBy('updated_at', 'DESC')->get()
        );

        try {
            $pdf = PDF::loadView('request_pdf', ['requests' => $requestsCollection]);
        } catch (Exception $e) {
            return $this->sendError(__('other.error'), [$e->getMessage()]);
        }

        return $pdf->output();
    }
}
