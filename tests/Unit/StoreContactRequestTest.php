<?php

namespace Tests\Unit;

use App\Http\Requests\StoreContactRequest;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreContactRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_contact_data_is_accepted(): void
    {
        $category = Category::factory()->create();
        $tag = Tag::factory()->create();

        $data = [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => '1',
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都新宿区',
            'building' => 'テストビル101',
            'category_id' => $category->id,
            'tag_ids' => [
                $tag->id,
            ],
            'detail' => 'お問い合わせ内容です。',
        ];

        $request = new StoreContactRequest();

        $validator = Validator::make(
            $data,
            $request->rules()
        );

        $this->assertFalse($validator->fails());
    }

    public function test_invalid_tel_is_rejected(): void
    {
        $category = Category::factory()->create();
        $tag = Tag::factory()->create();

        $data = [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => '1',
            'email' => 'test@example.com',
            'tel' => '090-1234-5678',
            'address' => '東京都新宿区',
            'building' => 'テストビル101',
            'category_id' => $category->id,
            'tag_ids' => [
                $tag->id,
            ],
            'detail' => 'お問い合わせ内容です。',
        ];

        $request = new StoreContactRequest();

        $validator = Validator::make(
            $data,
            $request->rules()
        );

        $this->assertTrue($validator->fails());

        $this->assertArrayHasKey(
            'tel',
            $validator->errors()->toArray()
        );
    }
}