<?php

namespace App\Http\Requests\Review;

use Illuminate\Foundation\Http\FormRequest;

class PostRequest extends FormRequest
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
            'gmb_comment'  => ['required','max:1000'],
        ];
    }

    public function attributes()
    {
        return [
            'gmb_comment'   => '返信内容',
        ];
    }
}
