<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Http\Requests\Company\CreateFunderRequest;
use App\Models\Funder;
use App\Services\DocumentService;
use Exception;
use Illuminate\Http\Request;

class FunderController extends Controller
{

    public DocumentService $documentService;

    public function __construct()
    {
        $this->documentService = new DocumentService();
    }

    public function index(Request $request)
    {

        try {

            $user_id = $request->user()->id;

            $funders = Funder::where('user_id', $user_id)->get();

            return response()->json([
                'success' => true,
                'data'    => $funders
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function create(CreateFunderRequest $request)
    {

        try {

            $user_id = $request->user()->id;

            $company_id = $request->user()->company->id;

            if ($request->hasFile('documents')) {
            }

            $funder = Funder::create($request->validated() + ['company_id' => $company_id, 'user_id' => $user_id]);

            return response()->json([
                'success' => true,
                'data'    => $funder
            ], 201);
        } catch (Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
