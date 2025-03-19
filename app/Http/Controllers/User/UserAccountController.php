<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\BaseController;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserAccountController extends BaseController
{
    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateUserRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCurrent(UpdateUserRequest $request)
    {
        $user = auth()->user();
        $user->update($request->validated());
        return $this->sendResponse('User updated successfully.', new UserResource($user));
    }

    public function updatePassword(UpdatePasswordRequest $request)
    {
        $request->validated();

        $user = auth()->user();
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return $this->sendResponse('Password updated successfully.', new UserResource($user));
    }
}
