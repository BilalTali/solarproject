<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSuperAgentLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'string', 'in:NEW,CONTACTED,DOCUMENTS_COLLECTED,REGISTERED,SITE_SURVEY,INSTALLATION_PENDING,INSTALLED,SUBSIDY_APPLIED,ON_HOLD'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'follow_up_date' => ['sometimes', 'nullable', 'date'],
        ];
    }
}
