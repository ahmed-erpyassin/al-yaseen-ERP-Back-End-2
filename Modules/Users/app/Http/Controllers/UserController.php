<?php

namespace Modules\Users\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Users\Models\User;
use Modules\Users\Services\UserService;
use Modules\Users\Transformers\UserResource;

class UserController extends Controller
{
    protected $service;

    public function __construct(UserService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return UserResource::collection($this->service->getAll());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'second_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'phone' => 'nullable|string|unique:users',
            'password' => 'required|min:6|confirmed',
            'status' => 'in:active,inactive',
        ]);

        $user = $this->service->create($validated, $request->user()->id);

        return new UserResource($user);
    }

    public function show($id)
    {
        $user = $this->service->findById($id);
        return new UserResource($user);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'first_name' => 'sometimes|string|max:255',
            'second_name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|unique:users,phone,' . $user->id,
            'password' => 'nullable|min:6|confirmed',
            'status' => 'in:active,inactive',
        ]);

        $updatedUser = $this->service->update($user, $validated, $request->user()->id);

        return new UserResource($updatedUser);
    }

    public function destroy(User $user)
    {
        $this->service->delete($user);
        return response()->json(['message' => 'User deleted']);
    }
}
