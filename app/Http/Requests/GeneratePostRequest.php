<?php

namespace App\Http\Requests;

use App\Ai\Agents\BlogPostGenerator;
use Illuminate\Foundation\Http\FormRequest;

class GeneratePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'topic' => ['required', 'string', 'max:300'],
            'title' => ['nullable', 'string', 'max:200'],
            'tone' => ['required', 'string', 'in:'.implode(',', BlogPostGenerator::TONES)],
            'keywords' => ['nullable', 'string', 'max:300'],
            'length' => ['required', 'string', 'in:'.implode(',', BlogPostGenerator::LENGTHS)],
        ];
    }
}
