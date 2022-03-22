<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\PostUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Models\UserDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // All Users
    public function index(Request $request)
    {
        $request->validate([
            "search" => "nullable|string",
            "sortBy" => "nullable|string",
            "sortDirection" => "string",
            "perPage" => "integer"
        ]);
        $user = new User();

        if ($request->has("search")) {
            $user = $user->where("first_name", "like", "%{$request->input("search")}%")
                        ->orWhere("last_name", "like", "%{$request->input("search")}%");
        }

        $user = $user->orderBy($request->input("sortBy", 'first_name'), $request->input("sortDirection", "desc"));

        return UserResource::collection($user->paginate($request->input('perPage')));
    }

    // Get Single User
    public function edit($id)
    {
        $user = User::findOrFail($id);
        return new UserResource($user);
    }

    // Add User
    public function add(PostUserRequest $request)
    {
        $user = new User();

        $user->first_name = $request->input('first_name');
        $user->last_name = $request->input('last_name');
        $user->email = $request->input('email');
        $user->password = Hash::make($request->input('email'));
        $user->save();

        if ($request->input('user_details')) {
            $details = new UserDetails($request->input('user_details'));
            $user->details()->save($details);
        }

        return new UserResource($user);
    }

    // Update User
    public function update($id, UpdateUserRequest $request)
    {
        $user = User::findOrFail($id);

        $user->first_name = $request->input('first_name');
        $user->last_name = $request->input('last_name');

        $user->save();

        if ($userDetails = $request->input('user_details')) {
            if(!$user->details) {
                $details = new UserDetails($userDetails);
                $user->details()->save($details);
            } else {
                $user->details()->update($userDetails);
            }
        }

        return new UserResource($user);
    }

    // Delete User
    public function delete($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        // response
        $response = [
            'success' => true,
            'message' => 'User successfully deleted',
        ];

        return response()->json($response);
    }
}
