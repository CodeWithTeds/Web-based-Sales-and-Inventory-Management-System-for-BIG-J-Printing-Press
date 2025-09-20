<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'active' => 'boolean',
            'notes' => 'nullable|string',
            'material_ids' => 'nullable|array',
            'material_ids.*' => 'exists:materials,id',
            'quantities' => 'nullable|array',
            'quantities.*' => 'numeric|min:0.01',
        ];

        // Add image validation only for new products or when updating with a new image
        if ($this->isMethod('post') || $this->hasFile('image')) {
            $rules['image'] = 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048';
        }

        return $rules;
    }
}
