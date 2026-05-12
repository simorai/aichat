<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OpenRouterService;
use Illuminate\Http\JsonResponse;
use RuntimeException;

class ModelController extends Controller
{
    public function __construct(
        protected readonly OpenRouterService $openRouterService,
    ) {
    }

    /**
     * Proxy available models from OpenRouter.
     */
    public function index(): JsonResponse
    {
        try {
            $models = $this->openRouterService->listModels();

            return response()->json(['data' => $models]);
        } catch (RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 503);
        }
    }
}
