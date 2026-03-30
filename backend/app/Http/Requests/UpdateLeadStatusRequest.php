<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLeadStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && in_array($this->user()->role, ['admin', 'operator']);
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:NEW,ON_HOLD,INVALID,DUPLICATE,REJECTED,REGISTERED,SITE_SURVEY,AT_BANK,COMPLETED,PROJECT_COMMISSIONING,SUBSIDY_REQUEST,SUBSIDY_APPLIED,SUBSIDY_DISBURSED',
            'notes' => 'nullable|string',
        ];
    }
}
