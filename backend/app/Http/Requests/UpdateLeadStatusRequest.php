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
            'status' => 'required|in:NEW,CONTACTED,DOCUMENTS_COLLECTED,REGISTERED,SITE_SURVEY,INSTALLATION_PENDING,INSTALLED,PROJECT_COMMISSIONING,SUBSIDY_REQUEST,SUBSIDY_APPLIED,SUBSIDY_DISBURSED,REJECTED,ON_HOLD,COMPLETED,INVALID,DUPLICATE,AT_BANK',
            'notes' => 'nullable|string',
        ];
    }
}
