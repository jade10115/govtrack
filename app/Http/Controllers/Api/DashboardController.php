<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClientRequest;
use App\Models\User;
use App\Models\Department;

class DashboardController extends Controller
{
    public function index()
    {
        return response()->json([
            'total_requests' =>
                ClientRequest::count(),

            'pending' =>
                ClientRequest::where(
                    'status',
                    'Pending'
                )->count(),

            'accepted' =>
                ClientRequest::where(
                    'status',
                    'Accepted'
                )->count(),

            'rejected' =>
                ClientRequest::where(
                    'status',
                    'Rejected'
                )->count(),

            'completed' =>
                ClientRequest::where(
                    'status',
                    'Completed'
                )->count(),

            'users' =>
                User::count(),

            'departments' =>
                Department::count()
        ]);
    }
}