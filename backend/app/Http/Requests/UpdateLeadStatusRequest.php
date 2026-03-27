<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLeadStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:NEW,REGISTERED,INSTALLED,REJECTED,ON_HOLD,COMPLETED,PROJECT_COMMISSIONING,SUBSIDY_REQUEST,SUBSIDY_DISBURSED',
            'notes' => 'nullable|string',
        ];
    }
}
