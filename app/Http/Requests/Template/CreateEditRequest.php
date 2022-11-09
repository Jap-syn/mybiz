<?php

namespace App\Http\Requests\Template;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class CreateEditRequest extends FormRequest
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
            'template_name'  => ['required','max:100'],
            'template'  => ['required','max:1000'],
            'is_autoreply_template' => ['required'],
            'target_star_rating' => ['required_if:is_autoreply_template,1']
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (Request::input('is_autoreply_template') == config('const.FLG_OFF') &&
                Request::filled('target_star_rating')) {
                $validator->errors()->add('target_star_rating', '手動返信の場合は選択できません');
            }
        });
    }

    public function attributes()
    {
        return [
            'template_name'   => 'テンプレート名',
            'template'  => '返信内容',
            'is_autoreply_template' => '返信タイプ',
            'target_star_rating' => '自動返信クチコミ評点'
        ];
    }
}
