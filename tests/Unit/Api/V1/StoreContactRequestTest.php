<?php

namespace Tests\Unit\Api\V1;

use App\Http\Requests\Api\V1\StoreContactRequest;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreContactRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_api_contact_data_is_accepted(): void
    {
        $category = Category::factory()->create();
        $tag = Tag::factory()->create();

        $data = [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都新宿区',
            'building' => 'テストビル101',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容です。',
            'tag_ids' => [
                $tag->id,
            ],
        ];

        $request = new StoreContactRequest();

        $validator = Validator::make(
            $data,
            $request->rules()
        );

        $this->assertFalse($validator->fails());
    }

    public function test_invalid_api_contact_data_is_rejected(): void
    {
        $data = [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 4,
            'email' => 'invalid-email',
            'tel' => '090-1234-5678',
            'address' => '東京都新宿区',
            'category_id' => 999999,
            'detail' => str_repeat('あ', 121),
            'tag_ids' => [
                999999,
            ],
        ];

        $request = new StoreContactRequest();

        $validator = Validator::make(
            $data,
            $request->rules()
        );

        $this->assertTrue($validator->fails());

        $errors = $validator->errors()->toArray();

        $this->assertArrayHasKey('gender', $errors);
        $this->assertArrayHasKey('email', $errors);
        $this->assertArrayHasKey('tel', $errors);
        $this->assertArrayHasKey('category_id', $errors);
        $this->assertArrayHasKey('detail', $errors);
        $this->assertArrayHasKey('tag_ids.0', $errors);
    }
}