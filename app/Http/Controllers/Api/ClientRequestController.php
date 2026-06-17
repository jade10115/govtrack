<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClientRequest;
use App\Models\RequestLog;
use Illuminate\Http\Request;

class ClientRequestController extends Controller
{
    public function index()
    {
        return ClientRequest::with([
            'requestType',
            'department',
            'assignedUser'
        ])->latest()->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'client_name'=>'required',
            'address'=>'required',
            'contact_number'=>'required',
            'age'=>'required',
            'gender'=>'required',
            'request_type_id'=>'required',
            'department_id'=>'required',
            'assigned_to'=>'required',
            'interview_notes'=>'required'
        ]);

        $controlNo =
            'REQ-' .
            date('Y') .
            '-' .
            str_pad(
                ClientRequest::count()+1,
                5,
                '0',
                STR_PAD_LEFT
            );

        $record = ClientRequest::create([
            'control_no'=>$controlNo,
            'client_name'=>$request->client_name,
            'address'=>$request->address,
            'contact_number'=>$request->contact_number,
            'age'=>$request->age,
            'gender'=>$request->gender,
            'request_type_id'=>$request->request_type_id,
            'department_id'=>$request->department_id,
            'assigned_to'=>$request->assigned_to,
            'created_by'=>auth()->id(),
            'interview_notes'=>$request->interview_notes,
            'status'=>'Pending'
        ]);

        RequestLog::create([
            'request_id'=>$record->id,
            'action'=>'Created',
            'performed_by'=>auth()->id()
        ]);

        return response()->json([
            'message'=>'Request Submitted'
        ]);
    }

    public function show(string $id)
    {
        return ClientRequest::with([
            'requestType',
            'department',
            'assignedUser'
        ])->findOrFail($id);
    }
}