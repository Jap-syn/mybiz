<?php

namespace App\Http\Requests\Review;

use Illuminate\Foundation\Http\FormRequest;

class ExportRequest extends FormRequest
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
            'stDate'  => ['required'],
            'endDate' => ['required'],
        ];
    }

    public function attributes()
    {
        return [
            'stDate'   => 'クチコミ登録日(始)',
            'endDate'  => 'クチコミ登録日(終)',
        ];
    }
}
