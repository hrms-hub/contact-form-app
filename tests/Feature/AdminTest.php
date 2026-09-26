<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_admin_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get('/admin');

        $response->assertOk();
        $response->assertViewIs('admin.index');
        $response->assertViewHas('contacts');
        $response->assertViewHas('categories');
        $response->assertViewHas('tags');
    }

    public function test_admin_can_search_contacts_by_keyword(): void
    {
        $user = User::factory()->create();

        Contact::factory()->create([
            'first_name' => '太郎',
            'last_name' => '山田',
            'email' => 'taro@example.com',
        ]);

        Contact::factory()->create([
            'first_name' => '花子',
            'last_name' => '佐藤',
            'email' => 'hanako@example.com',
        ]);

        $response = $this->actingAs($user)
            ->get('/admin?keyword=山田');

        $response->assertOk();

        $response->assertViewHas('contacts', function ($contacts) {
            return $contacts->count() === 1
                && $contacts->first()->last_name === '山田';
        });
    }

    public function test_admin_can_filter_contacts_by_gender(): void
    {
        $user = User::factory()->create();

        Contact::factory()->create([
            'gender' => 1,
        ]);

        Contact::factory()->create([
            'gender' => 2,
        ]);

        $response = $this->actingAs($user)
            ->get('/admin?gender=1');

        $response->assertOk();

        $response->assertViewHas('contacts', function ($contacts) {
            return $contacts->count() === 1
                && $contacts->first()->gender == 1;
        });
    }

    public function test_admin_can_filter_contacts_by_category(): void
    {
        $user = User::factory()->create();

        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();

        Contact::factory()->create([
            'category_id' => $category1->id,
        ]);

        Contact::factory()->create([
            'category_id' => $category2->id,
        ]);

        $response = $this->actingAs($user)
            ->get("/admin?category_id={$category1->id}");

        $response->assertOk();

        $response->assertViewHas('contacts', function ($contacts) use ($category1) {
            return $contacts->count() === 1
                && $contacts->first()->category_id === $category1->id;
        });
    }

    public function test_admin_can_filter_contacts_by_date(): void
    {
        $user = User::factory()->create();

        Contact::factory()->create([
            'created_at' => '2026-09-23 10:00:00',
        ]);

        Contact::factory()->create([
            'created_at' => '2026-09-22 10:00:00',
        ]);

        $response = $this->actingAs($user)
            ->get('/admin?date=2026-09-23');

        $response->assertOk();

        $response->assertViewHas('contacts', function ($contacts) {
            return $contacts->count() === 1
                && $contacts->first()->created_at->format('Y-m-d') === '2026-09-23';
        });
    }

    public function test_admin_contacts_are_paginated_by_seven(): void
    {
        $user = User::factory()->create();

        Contact::factory()->count(8)->create();

        $response = $this->actingAs($user)
            ->get('/admin');

        $response->assertOk();

        $response->assertViewHas('contacts', function ($contacts) {
            return $contacts->count() === 7
                && $contacts->total() === 8
                && $contacts->perPage() === 7;
        });
    }

    public function test_admin_can_view_contact_detail(): void
    {
        $user = User::factory()->create();

        $category = Category::factory()->create([
            'content' => '商品について',
        ]);

        $contact = Contact::factory()->create([
            'category_id' => $category->id,
            'first_name' => '太郎',
            'last_name' => '山田',
        ]);

        $response = $this->actingAs($user)
            ->get("/admin/contacts/{$contact->id}");

        $response->assertOk();
        $response->assertViewIs('admin.show');

        $response->assertViewHas('contact', function ($viewContact) use ($contact, $category) {
            return $viewContact->id === $contact->id
                && $viewContact->category->id === $category->id;
        });
    }

    public function test_admin_can_delete_contact(): void
    {
        $user = User::factory()->create();

        $contact = Contact::factory()->create();

        $response = $this->actingAs($user)
            ->delete("/admin/contacts/{$contact->id}");

        $response->assertRedirect('/admin');

        $this->assertDatabaseMissing('contacts', [
            'id' => $contact->id,
        ]);
    }
}
