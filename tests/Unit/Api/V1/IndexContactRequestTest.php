<?php

namespace Tests\Unit\Api\V1;

use App\Http\Requests\Api\V1\IndexContactRequest;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class IndexContactRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_api_search_filters_are_accepted(): void
    {
        $category = Category::factory()->create();

        $data = [
            'keyword' => '山田',
            'gender' => 1,
            'category_id' => $category->id,
            'date' => '2026-09-24',
            'per_page' => 20,
        ];

        $request = new IndexContactRequest();

        $validator = Validator::make(
            $data,
            $request->rules()
        );

        $this->assertFalse($validator->fails());
    }

    public function test_invalid_api_search_filters_are_rejected(): void
    {
        $data = [
            'keyword' => [],
            'gender' => 4,
            'category_id' => 999999,
            'date' => 'invalid-date',
            'per_page' => 101,
        ];

        $request = new IndexContactRequest();

        $validator = Validator::make(
            $data,
            $request->rules()
        );

        $this->assertTrue($validator->fails());

        $errors = $validator->errors()->toArray();

        $this->assertArrayHasKey('keyword', $errors);
        $this->assertArrayHasKey('gender', $errors);
        $this->assertArrayHasKey('category_id', $errors);
        $this->assertArrayHasKey('date', $errors);
        $this->assertArrayHasKey('per_page', $errors);
    }
}