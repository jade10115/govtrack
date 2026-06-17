<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        return User::with([
            'department',
            'userLevel'
        ])->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'=>'required',
            'email'=>'required|email|unique:users',
            'password'=>'required|min:6',
            'department_id'=>'nullable',
            'user_level_id'=>'required'
        ]);

        $user = User::create([
            'name'=>$request->name,
            'email'=>$request->email,
            'password'=>Hash::make($request->password),
            'department_id'=>$request->department_id,
            'user_level_id'=>$request->user_level_id
        ]);

        return response()->json($user,201);
    }

    public function show(string $id)
    {
        return User::with([
            'department',
            'userLevel'
        ])->findOrFail($id);
    }

    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $user->update([
            'name'=>$request->name,
            'email'=>$request->email,
            'department_id'=>$request->department_id,
            'user_level_id'=>$request->user_level_id
        ]);

        return response()->json([
            'message'=>'Updated Successfully'
        ]);
    }

    public function destroy(string $id)
    {
        User::findOrFail($id)->delete();

        return response()->json([
            'message'=>'Deleted Successfully'
        ]);
    }
}