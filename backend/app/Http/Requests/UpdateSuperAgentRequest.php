<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSuperAgentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'mobile' => ['sometimes', 'string', 'size:10', "unique:users,mobile,{$id}"],
            'email' => ['nullable', 'email', "unique:users,email,{$id}"],
            'whatsapp_number' => ['nullable', 'string', 'size:10'],
            'district' => ['sometimes', 'string', 'max:100'],
            'state' => ['sometimes', 'string', 'max:100'],
            'area' => ['nullable', 'string', 'max:255'],
            'managed_states' => ['nullable', 'array'],
        ];
    }
}
