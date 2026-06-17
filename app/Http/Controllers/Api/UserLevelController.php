<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserLevel;
use Illuminate\Http\Request;

class UserLevelController extends Controller
{
    public function index()
    {
        return UserLevel::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:user_levels'
        ]);

        return UserLevel::create($request->all());
    }

    public function show(string $id)
    {
        return UserLevel::findOrFail($id);
    }

    public function update(Request $request, string $id)
    {
        $level = UserLevel::findOrFail($id);
        
        $request->validate([
            'name' => 'required|unique:user_levels,name,' . $id
        ]);

        $level->update($request->all());

        return response()->json([
            'message' => 'Updated Successfully'
        ]);
    }

    public function destroy(string $id)
    {
        UserLevel::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Deleted Successfully'
        ]);
    }
}