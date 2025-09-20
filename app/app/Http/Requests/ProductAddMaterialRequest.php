<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductAddMaterialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'material_ids' => 'required|array',
            'material_ids.*' => 'exists:materials,id',
            'quantities' => 'required|array',
            'quantities.*' => 'required|numeric|min:0.01',
        ];
    }
}