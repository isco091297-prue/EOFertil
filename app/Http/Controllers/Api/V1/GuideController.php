<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Guide\ProtocolGuideResource;
use App\Services\Guide\GuideService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GuideController extends Controller
{
    public function __construct(
        private readonly GuideService $guideService
    ) {}

    /**
     * GET /api/v1/guide/crops
     */
    public function crops(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->guideService->crops(),
        ]);
    }

    /**
     * GET /api/v1/guide/problems?crop_id=1
     */
    public function problems(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'crop_id' => ['required', 'integer'],
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->guideService->problems(
                $validated['crop_id']
            ),
        ]);
    }

    /**
     * GET /api/v1/guide/protocol?crop_id=1&problem_id=1
     */
    public function protocol(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'crop_id'    => ['required', 'integer'],
            'problem_id' => ['required', 'integer'],
        ]);

        $protocol = $this->guideService->protocol(
            $validated['crop_id'],
            $validated['problem_id']
        );

        if (!$protocol) {
            return response()->json([
                'success' => false,
                'message' => 'No existe un protocolo para este problema.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => (new ProtocolGuideResource($protocol))->resolve(),
        ]);
    }
}
