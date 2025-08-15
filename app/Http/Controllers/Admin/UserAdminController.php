<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserAdminController extends Controller
{
    // List all users
    public function index()
    {
        return User::paginate(10);
    }

    // Show a single user
    public function show(User $user)
    {
        return $user;
    }

    // Create a new user
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20|unique:users,phone_number',
            'password' => 'required|string|min:6',
            'is_admin' => 'sometimes|boolean',
        ]);

        $data['password'] = Hash::make($data['password']); // hash the password
        $data['is_admin'] = $data['is_admin'] ?? false;

        $user = User::create($data);

        return response()->json($user, 201);
    }

    // Update a user
    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone_number' => 'sometimes|string|max:20|unique:users,phone_number,' . $user->id,
            'password' => 'nullable|string|min:6',
            'is_admin' => 'sometimes|boolean',
        ]);

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return response()->json($user);
    }

    // Delete a user
    public function destroy(User $user)
    {
        $user->delete();

        return response()->json(['message' => 'User deleted']);
    }
}
