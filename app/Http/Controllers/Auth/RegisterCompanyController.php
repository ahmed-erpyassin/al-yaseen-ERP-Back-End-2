<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterCompanyRequest;
use App\Models\Company;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\Laravel\Facades\Image;

class RegisterCompanyController extends Controller
{
    public function store(RegisterCompanyRequest $request)
    {

        try {
            $validated = $request->validated();
            if ($request->hasFile('company_logo')) {

                $file = $request->file('company_logo');

                $fileName   = uniqid('company_', true) . '.webp';
                $filePath   = 'company_logos/' . $fileName;

                $img = Image::read($file)->encode(new WebpEncoder(quality: 90));

                Storage::disk('public')->put($filePath, (string) $img);

                $validated['company_logo'] = $filePath;
            }

            $company = Company::create($validated + ['user_id' => $request->user()->id]);

            return response()->json([
                'success' => true,
                'message' => 'Company created successfully',
                'data'    => $company,
            ], 201);
        } catch (Exception $exception) {

            return response()->json([
                'success'   => false,
                'message'   => $exception->getMessage()
            ]);
        }
    }
}
