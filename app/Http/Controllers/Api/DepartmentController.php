<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        return Department::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'department_name'=>'required'
        ]);

        return Department::create($request->all());
    }

    public function show(string $id)
    {
        return Department::findOrFail($id);
    }

    public function update(Request $request, string $id)
    {
        $department = Department::findOrFail($id);

        $department->update($request->all());

        return response()->json([
            'message'=>'Updated'
        ]);
    }

    public function destroy(string $id)
    {
        Department::findOrFail($id)->delete();

        return response()->json([
            'message'=>'Deleted'
        ]);
    }
}