<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RequestType;
use Illuminate\Http\Request;

class RequestTypeController extends Controller
{
    public function index()
    {
        return RequestType::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'request_name'=>'required'
        ]);

        return RequestType::create($request->all());
    }

    public function show(string $id)
    {
        return RequestType::findOrFail($id);
    }

    public function update(Request $request, string $id)
    {
        $type = RequestType::findOrFail($id);

        $type->update($request->all());

        return response()->json([
            'message'=>'Updated'
        ]);
    }

    public function destroy(string $id)
    {
        RequestType::findOrFail($id)->delete();

        return response()->json([
            'message'=>'Deleted'
        ]);
    }
}