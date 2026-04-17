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
            // SITE_SURVEY and COMPLETED are intentionally excluded:
            // They are set ONLY by the Field Technical Team via geo-tagged selfie submission.
            'status' => 'required|in:NEW,ON_HOLD,INVALID,DUPLICATE,REJECTED,REGISTERED,AT_BANK,PROJECT_COMMISSIONING,SUBSIDY_REQUEST,SUBSIDY_APPLIED,SUBSIDY_DISBURSED',
            'notes'  => 'nullable|string',
            'receipt' => 'nullable|file|max:5120|mimes:pdf,jpg,jpeg,png',
        ];
    }
}
