<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MaterialRequestFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'material_id' => 'required|exists:materials,id',
            'quantity' => 'required|numeric|min:1',
            'department' => 'required|string|max:255',
            'requester_name' => 'required|string|max:255',
            'request_date' => 'required|date',
            'required_date' => 'required|date|after_or_equal:request_date',
            'purpose' => 'required|string',
        ];
    }
}