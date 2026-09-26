<?php

namespace Tests\Unit;

use App\Http\Requests\StoreTagRequest;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreTagRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_tag_name_is_accepted(): void
    {
        $request = new StoreTagRequest;

        $validator = Validator::make(
            [
                'name' => 'Laravel',
            ],
            $request->rules()
        );

        $this->assertFalse($validator->fails());
    }

    public function test_tag_name_is_required(): void
    {
        $request = new StoreTagRequest;

        $validator = Validator::make(
            [
                'name' => '',
            ],
            $request->rules()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey(
            'name',
            $validator->errors()->toArray()
        );
    }

    public function test_tag_name_must_not_exceed_50_characters(): void
    {
        $request = new StoreTagRequest;

        $validator = Validator::make(
            [
                'name' => str_repeat('あ', 51),
            ],
            $request->rules()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey(
            'name',
            $validator->errors()->toArray()
        );
    }

    public function test_duplicate_tag_name_is_rejected(): void
    {
        Tag::factory()->create([
            'name' => 'Laravel',
        ]);

        $request = new StoreTagRequest;

        $validator = Validator::make(
            [
                'name' => 'Laravel',
            ],
            $request->rules()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey(
            'name',
            $validator->errors()->toArray()
        );
    }
}
