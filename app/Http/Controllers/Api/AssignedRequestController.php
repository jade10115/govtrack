<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClientRequest;
use App\Models\RequestLog;
use Illuminate\Http\Request;

class AssignedRequestController extends Controller
{
    public function myRequests()
    {
        return ClientRequest::where(
            'assigned_to',
            auth()->id()
        )->get();
    }

    public function accept($id)
    {
        $request = ClientRequest::findOrFail($id);

        $request->update([
            'status'=>'Accepted'
        ]);

        RequestLog::create([
            'request_id'=>$id,
            'action'=>'Accepted',
            'performed_by'=>auth()->id()
        ]);

        return response()->json([
            'message'=>'Accepted'
        ]);
    }

    public function reject(Request $req, $id)
    {
        $request = ClientRequest::findOrFail($id);

        $request->update([
            'status'=>'Rejected'
        ]);

        RequestLog::create([
            'request_id'=>$id,
            'action'=>'Rejected',
            'remarks'=>$req->remarks,
            'performed_by'=>auth()->id()
        ]);

        return response()->json([
            'message'=>'Rejected'
        ]);
    }

    public function complete($id)
    {
        $request = ClientRequest::findOrFail($id);

        $request->update([
            'status'=>'Completed'
        ]);

        RequestLog::create([
            'request_id'=>$id,
            'action'=>'Completed',
            'performed_by'=>auth()->id()
        ]);

        return response()->json([
            'message'=>'Completed'
        ]);
    }
}