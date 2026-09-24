<?php

namespace Tests\Feature\Api\V1;

use App\Models\Category;
use App\Models\Tag;
use App\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactApiTest extends TestCase
{

use RefreshDatabase;

    public function test_can_get_contact_list(): void
    {
        Contact::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/contacts');

        $response->assertStatus(200);

        $response->assertJsonCount(3, 'data');

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'category' => [
                        'id',
                        'content',
                    ],
                    'first_name',
                    'last_name',
                    'gender',
                    'email',
                    'tel',
                    'address',
                    'building',
                    'detail',
                    'tags',
                    'created_at',
                    'updated_at',
                ],
            ],
            'meta' => [
                'current_page',
                'last_page',
                'per_page',
                'total',
            ],
        ]);
    }

    public function test_can_get_contact_detail(): void
{
    $contact = Contact::factory()->create();

    $response = $this->getJson("/api/v1/contacts/{$contact->id}");

    $response->assertStatus(200);

    $response->assertJsonPath('data.id', $contact->id);

    $response->assertJsonStructure([
        'data' => [
            'id',
            'category' => [
                'id',
                'content',
            ],
            'first_name',
            'last_name',
            'gender',
            'email',
            'tel',
            'address',
            'building',
            'detail',
            'tags',
            'created_at',
            'updated_at',
        ],
    ]);
}


public function test_can_create_contact(): void
{
    $category = Category::factory()->create();
    $tags = Tag::factory()->count(2)->create();

    $data = [
        'first_name' => '山田',
        'last_name' => '太郎',
        'gender' => 1,
        'email' => 'yamada@example.com',
        'tel' => '09012345678',
        'address' => '東京都渋谷区1-1-1',
        'building' => '渋谷ビル301',
        'category_id' => $category->id,
        'detail' => '商品の配送日について',
        'tag_ids' => $tags->pluck('id')->toArray(),
    ];

    $response = $this->postJson('/api/v1/contacts', $data);

    $response->assertStatus(201);

    $response->assertJsonPath('data.first_name', '山田');
    $response->assertJsonPath('data.email', 'yamada@example.com');
    $response->assertJsonPath('data.category.id', $category->id);

    $this->assertDatabaseHas('contacts', [
        'first_name' => '山田',
        'last_name' => '太郎',
        'email' => 'yamada@example.com',
        'category_id' => $category->id,
    ]);
}


public function test_can_update_contact(): void
{
    $contact = Contact::factory()->create();

    $category = Category::factory()->create();
    $tags = Tag::factory()->count(2)->create();

    $data = [
        'first_name' => '佐藤',
        'last_name' => '次郎',
        'gender' => 2,
        'email' => 'sato@example.com',
        'tel' => '08012345678',
        'address' => '東京都新宿区2-2-2',
        'building' => '新宿ビル501',
        'category_id' => $category->id,
        'detail' => 'お問い合わせ内容を更新しました',
        'tag_ids' => $tags->pluck('id')->toArray(),
    ];

    $response = $this->putJson(
        "/api/v1/contacts/{$contact->id}",
        $data
    );

    $response->assertStatus(200);

    $response->assertJsonPath('data.id', $contact->id);
    $response->assertJsonPath('data.first_name', '佐藤');
    $response->assertJsonPath('data.email', 'sato@example.com');
    $response->assertJsonPath('data.category.id', $category->id);

    $this->assertDatabaseHas('contacts', [
        'id' => $contact->id,
        'first_name' => '佐藤',
        'last_name' => '次郎',
        'email' => 'sato@example.com',
        'category_id' => $category->id,
    ]);
}

public function test_can_delete_contact(): void
{
    $contact = Contact::factory()->create();

    $response = $this->deleteJson(
        "/api/v1/contacts/{$contact->id}"
    );

    $response->assertStatus(204);

    $this->assertDatabaseMissing('contacts', [
        'id' => $contact->id,
    ]);
}

}