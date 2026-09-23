<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_download_filtered_csv(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create([
            'content' => '商品について',
        ]);

        Contact::factory()->create([
            'category_id' => $category->id,
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'yamada@example.com',
            'created_at' => '2026-09-23 10:00:00',
        ]);

        Contact::factory()->create([
            'category_id' => $category->id,
            'first_name' => '佐藤',
            'last_name' => '花子',
            'gender' => 2,
            'email' => 'sato@example.com',
            'created_at' => '2026-09-23 11:00:00',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/contacts/export?keyword=山田');

        $response->assertOk();
        $response->assertDownload('contacts.csv');

        $content = $response->streamedContent();

        $this->assertStringContainsString('山田', $content);
        $this->assertStringContainsString('yamada@example.com', $content);
        $this->assertStringNotContainsString('sato@example.com', $content);
    }

    public function test_csv_is_exported_in_latest_order_without_filters(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        Contact::factory()->create([
            'category_id' => $category->id,
            'first_name' => '古い',
            'last_name' => '問い合わせ',
            'email' => 'old@example.com',
            'created_at' => '2026-09-22 10:00:00',
        ]);

        Contact::factory()->create([
            'category_id' => $category->id,
            'first_name' => '新しい',
            'last_name' => '問い合わせ',
            'email' => 'new@example.com',
            'created_at' => '2026-09-23 10:00:00',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/contacts/export');

        $response->assertOk();
        $response->assertDownload('contacts.csv');

        $content = $response->streamedContent();

        $newPosition = strpos($content, 'new@example.com');
        $oldPosition = strpos($content, 'old@example.com');

        $this->assertNotFalse($newPosition);
        $this->assertNotFalse($oldPosition);
        $this->assertLessThan($oldPosition, $newPosition);
    }
}