<?php

namespace App\Http\Controllers\Api\V1\Inspection;

use App\Actions\Inspection\InspectionRequestAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Inspection\AssignInspectorRequest;
use App\Http\Requests\Api\V1\Inspection\SaveInspectionReportRequest;
use App\Http\Requests\Api\V1\Inspection\UpdateInspectionStatusRequest;
use App\Http\Resources\Api\V1\Inspection\InspectionRequestResource;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class AdminInspectionController extends Controller
{
    public function __construct(protected InspectionRequestAction $action) {}

    /**
     * Review queue. Optional ?status= filter.
     */
    public function index(Request $request)
    {
        $items = $this->action->adminList($request->query('status'), (int) $request->query('per_page', 15));

        return InspectionRequestResource::collection($items)
            ->wrapWith('requests')
            ->message('Inspection requests.');
    }

    public function show(int $id)
    {
        return (new InspectionRequestResource($this->action->adminShow($id)))
            ->message('Inspection request.');
    }

    public function assign(AssignInspectorRequest $request, int $id)
    {
        $updated = $this->action->assign($id, (int) $request->validated()['inspector_id']);

        return (new InspectionRequestResource($updated))
            ->message('Inspector assigned.');
    }

    public function updateStatus(UpdateInspectionStatusRequest $request, int $id)
    {
        $updated = $this->action->updateStatus($id, $request->validated());

        return (new InspectionRequestResource($updated))
            ->message('Inspection request updated.');
    }

    /** Category catalog for the report form. */
    public function categories()
    {
        return ApiResponse::success(
            ['categories' => $this->action->categoryCatalog()],
            'Inspection categories.'
        );
    }

    /** Save the category-level report → completes the request + notifies. */
    public function saveReport(SaveInspectionReportRequest $request, int $id)
    {
        $data = $request->validated();
        $updated = $this->action->saveReport($id, $data['items'], $data['comments'] ?? null);

        return (new InspectionRequestResource($updated))
            ->message('Report saved.');
    }
}
