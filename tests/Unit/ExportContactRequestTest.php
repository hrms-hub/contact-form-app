<?php

namespace Tests\Unit;

use App\Http\Requests\ExportContactRequest;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ExportContactRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_export_filters_are_accepted(): void
    {
        $category = Category::factory()->create();

        $data = [
            'keyword' => '山田',
            'gender' => 1,
            'category_id' => $category->id,
            'date' => '2026-09-23',
        ];

        $request = new ExportContactRequest();

        $validator = Validator::make(
            $data,
            $request->rules()
        );

        $this->assertFalse($validator->fails());
    }

    public function test_invalid_gender_is_rejected(): void
    {
        $data = [
            'gender' => 4,
        ];

        $request = new ExportContactRequest();

        $validator = Validator::make(
            $data,
            $request->rules()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('gender', $validator->errors()->toArray());
    }

    public function test_nonexistent_category_is_rejected(): void
    {
        $data = [
            'category_id' => 999999,
        ];

        $request = new ExportContactRequest();

        $validator = Validator::make(
            $data,
            $request->rules()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('category_id', $validator->errors()->toArray());
    }
}