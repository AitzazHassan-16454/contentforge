<?php

namespace App\Http\Requests;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePostRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:200'],
            'content' => ['required', 'string'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'cover_image' => ['nullable', 'image', 'mimes:jpeg,png,webp,gif', 'max:4096'],
            'cover_image_alt' => ['nullable', 'string', 'max:200'],
            'remove_cover' => ['sometimes', 'boolean'],
            'status' => ['required', Rule::in([Post::STATUS_DRAFT, Post::STATUS_PUBLISHED])],
            'scheduled_at' => ['nullable', 'date'],
            'category_ids' => ['array', 'max:1'],
            'category_ids.*' => ['integer', Rule::exists(Category::class, 'id')],
            'tags' => ['array', 'max:5'],
            'tags.*' => ['string', 'max:50'],
        ];
    }
}
