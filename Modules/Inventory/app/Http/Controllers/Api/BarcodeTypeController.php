<?php

namespace Modules\Inventory\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Inventory\Models\BarcodeType;

class BarcodeTypeController extends Controller
{
    /**
     * Display a listing of barcode types.
     */
    public function index(Request $request): JsonResponse
    {
        $query = BarcodeType::active();

        // Apply search
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'name');
        $sortDirection = $request->get('sort_direction', 'asc');
        $query->orderBy($sortBy, $sortDirection);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $barcodeTypes = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $barcodeTypes,
            'message' => 'Barcode types retrieved successfully',
            'message_ar' => 'تم استرداد أنواع الباركود بنجاح'
        ]);
    }

    /**
     * Display the specified barcode type.
     */
    public function show($id): JsonResponse
    {
        $barcodeType = BarcodeType::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $barcodeType,
            'message' => 'Barcode type retrieved successfully',
            'message_ar' => 'تم استرداد نوع الباركود بنجاح'
        ]);
    }

    /**
     * Get barcode type options for dropdown.
     */
    public function getOptions(): JsonResponse
    {
        $barcodeTypes = BarcodeType::active()
            ->orderBy('is_default', 'desc')
            ->orderBy('name', 'asc')
            ->get(['id', 'code', 'name', 'name_ar', 'is_default']);

        $options = $barcodeTypes->map(function ($type) {
            return [
                'value' => $type->id,
                'label' => $type->display_name,
                'code' => $type->code,
                'is_default' => $type->is_default,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $options,
            'default_type' => $barcodeTypes->where('is_default', true)->first(),
            'message' => 'Barcode type options retrieved successfully',
            'message_ar' => 'تم استرداد خيارات أنواع الباركود بنجاح'
        ]);
    }

    /**
     * Validate a barcode against a specific type.
     */
    public function validateBarcode(Request $request): JsonResponse
    {
        $request->validate([
            'barcode' => 'required|string',
            'barcode_type_id' => 'required|exists:barcode_types,id',
        ]);

        $barcodeType = BarcodeType::findOrFail($request->barcode_type_id);
        $validation = $barcodeType->validateBarcode($request->barcode);

        return response()->json([
            'success' => true,
            'data' => $validation,
            'message' => 'Barcode validation completed',
            'message_ar' => 'تم التحقق من صحة الباركود'
        ]);
    }

    /**
     * Generate barcode image.
     */
    public function generateBarcode(Request $request): JsonResponse
    {
        $request->validate([
            'barcode' => 'required|string',
            'barcode_type_id' => 'required|exists:barcode_types,id',
            'width' => 'nullable|integer|min:1|max:10',
            'height' => 'nullable|integer|min:10|max:100',
        ]);

        try {
            $barcodeType = BarcodeType::findOrFail($request->barcode_type_id);
            
            $options = [
                'w' => $request->get('width', 2),
                'h' => $request->get('height', 30),
                'color' => [0, 0, 0], // Black
            ];

            $barcodeImage = $barcodeType->generateBarcodeImage($request->barcode, $options);

            return response()->json([
                'success' => true,
                'data' => [
                    'barcode' => $request->barcode,
                    'type' => $barcodeType->code,
                    'image' => 'data:image/png;base64,' . base64_encode($barcodeImage),
                ],
                'message' => 'Barcode generated successfully',
                'message_ar' => 'تم إنشاء الباركود بنجاح'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate barcode: ' . $e->getMessage(),
                'message_ar' => 'فشل في إنشاء الباركود: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Get supported barcode types from Milon library.
     */
    public function getSupportedTypes(): JsonResponse
    {
        $supportedTypes = BarcodeType::getSupportedTypes();

        return response()->json([
            'success' => true,
            'data' => $supportedTypes,
            'message' => 'Supported barcode types retrieved successfully',
            'message_ar' => 'تم استرداد أنواع الباركود المدعومة بنجاح'
        ]);
    }
}
