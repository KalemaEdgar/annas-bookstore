<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAuthorRequest extends FormRequest
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
        // In this method, we return an array of validation rules for both the structure of a request document, but also the data we expect.
        // For this, we will need the following rules that requires:
        // • A top level data member
        // • That the data member contains a resource object
        // • That the resource object contains a type member
        // • That the type member has the value authors
        // • The the resource object contains a attributes member
        // • That the attributes member contains a name member
        // • That the name member is not empty
        return [
            'data' => 'required|array',
            'data.id' => 'required|string',
            'data.type' => 'required|in:authors',
            'data.attributes' => 'sometimes|required|array',
            'data.attributes.name' => 'sometimes|required|string',
        ];
    }
}
