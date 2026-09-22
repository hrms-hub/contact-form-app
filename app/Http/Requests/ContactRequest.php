<?php

namespace App\Http\Requests;
use App\Http\Requests\ContactRequest;
use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
{
    /**
     * このリクエストを許可する
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * バリデーション前の処理
     */
    protected function prepareForValidation(): void
    {
        // 入力画面から送信された場合だけ
        // tel1・tel2・tel3 を結合して tel を作る
        if (
            $this->has('tel1') ||
            $this->has('tel2') ||
            $this->has('tel3')
        ) {
            $this->merge([
                'tel' => $this->input('tel1')
                    . $this->input('tel2')
                    . $this->input('tel3'),
            ]);
        }
    }

    /**
     * バリデーションルール
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'in:1,2,3'],
            'email' => ['required', 'email', 'max:255'],
            'tel' => ['required', 'regex:/^[0-9]{10,11}$/'],
            'address' => ['required', 'string', 'max:255'],
            'building' => ['nullable', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['exists:tags,id'],
            'detail' => ['required', 'string'],
        ];
    }
}