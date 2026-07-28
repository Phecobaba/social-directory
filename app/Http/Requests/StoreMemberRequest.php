<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'title' => ['nullable', 'string', 'max:30'],
            'surname' => ['required_without:full_name', 'nullable', 'string', 'max:120'],
            'other_names' => ['required_without:full_name', 'nullable', 'string', 'max:160'],
            'full_name' => ['required_without_all:surname,other_names', 'nullable', 'string', 'max:255'],
            'date_of_birth' => ['nullable', 'date'],
            'place_of_birth' => ['nullable', 'string', 'max:255'],
            'town_of_origin' => ['nullable', 'string', 'max:255'],
            'village' => ['nullable', 'string', 'max:255'],
            'local_government_of_origin' => ['nullable', 'string', 'max:255'],
            'state_of_origin' => ['nullable', 'string', 'max:255'],
            'occupation' => ['nullable', 'string', 'max:255'],
            'height' => ['nullable', 'string', 'max:50'],
            'phone_number' => ['required', 'string', 'max:50'],
            'next_of_kin_name' => ['nullable', 'string', 'max:255'],
            'next_of_kin_relationship' => ['nullable', 'string', 'max:120'],
            'next_of_kin_phone' => ['nullable', 'string', 'max:50'],
            'father_name' => ['nullable', 'string', 'max:255'],
            'mother_name' => ['nullable', 'string', 'max:255'],
            'wife_name' => ['nullable', 'string', 'max:255'],
            'house_address' => ['nullable', 'string'],
            'office_address' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'dynamic_fields' => ['nullable', 'array'],
        ];
    }
}
