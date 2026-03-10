<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSuperAgentLeadRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'status'        => ['sometimes', 'string', 'in:new,contacted,documents_collected,registered,site_survey,installation_pending,installed,subsidy_applied,on_hold'],
            'notes'         => ['sometimes', 'nullable', 'string', 'max:2000'],
            'follow_up_date'=> ['sometimes', 'nullable', 'date'],
        ];
    }
}
