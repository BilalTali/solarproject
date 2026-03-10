<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; // Import Rule

class StorePublicLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'beneficiary_name' => 'required|string|max:255',
            'beneficiary_mobile' => 'required|string|size:10|regex:/^[6-9]\d{9}$/',
            'beneficiary_email' => 'nullable|email|max:255',
            'beneficiary_state'    => ['required', 'string', Rule::in(['Jammu & Kashmir', 'Ladakh'])],
            'beneficiary_district' => ['required', 'string', Rule::in([
                'Srinagar', 'Baramulla', 'Anantnag', 'Pulwama', 'Kupwara', 'Budgam',
                'Bandipora', 'Bandipore', 'Ganderbal', 'Kulgam', 'Shopian',
                'Jammu', 'Kathua', 'Udhampur', 'Reasi', 'Samba', 'Rajouri',
                'Poonch', 'Doda', 'Ramban', 'Kishtwar',
                'Leh', 'Kargil',
            ])],
            'beneficiary_address' => 'required|string|max:500',
            'beneficiary_pincode' => 'required|string|size:6',
            'consumer_number' => 'nullable|string|max:100',
            'discom_name' => 'required|string|max:255',
            'roof_size' => 'nullable|in:less_100,100_200,200_300,300_plus',
            'system_capacity' => 'required|in:1kw,2kw,3kw,above_3kw',
            'monthly_bill_amount' => 'nullable|numeric|min:0',
            'query_message' => 'nullable|string',
            'aadhaar'          => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'electricity_bill' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'photo'            => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'other'            => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'solar_roof_photo' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'bank_passbook'    => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'aadhaar.required'             => 'Aadhaar card upload is required.',
            'aadhaar.mimes'                => 'Aadhaar must be JPG, PNG or PDF.',
            'aadhaar.max'                  => 'Aadhaar file must not exceed 2MB.',
            'electricity_bill.required'    => 'Electricity bill upload is required.',
            'electricity_bill.mimes'       => 'Electricity bill must be JPG, PNG or PDF.',
            'electricity_bill.max'         => 'Electricity bill must not exceed 2MB.',
            'beneficiary_state.in'         => 'State must be Jammu & Kashmir or Ladakh.',
            'beneficiary_district.in'      => 'Please select a valid district.',
            'beneficiary_mobile.regex'     => 'Mobile number must start with 6, 7, 8, or 9.',
            'beneficiary_mobile.size'      => 'Mobile number must be exactly 10 digits.',
        ];
    }
}
