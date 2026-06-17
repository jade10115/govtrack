<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientRequest extends Model
{
    protected $fillable = [
        'control_no',
        'client_name',
        'address',
        'contact_number',
        'age',
        'gender',
        'request_type_id',
        'department_id',
        'assigned_to',
        'created_by',
        'interview_notes',
        'status'
    ];

    public function requestType()
    {
        return $this->belongsTo(RequestType::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(
            User::class,
            'assigned_to'
        );
    }

    public function creator()
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }
}