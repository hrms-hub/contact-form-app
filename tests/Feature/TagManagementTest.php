<?php

namespace Tests\Feature;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_tag_edit_page(): void
    {
        $user = User::factory()->create();

        $tag = Tag::factory()->create([
            'name' => 'Laravel',
        ]);

        $response = $this->actingAs($user)
            ->get("/admin/tags/{$tag->id}/edit");

        $response->assertOk();
        $response->assertViewIs('admin.tags.edit');

        $response->assertViewHas('tag', function ($viewTag) use ($tag) {
            return $viewTag->id === $tag->id;
        });
    }

    public function test_authenticated_user_can_create_tag(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post('/admin/tags', [
                'name' => 'Laravel',
            ]);

        $response->assertRedirect('/admin');

        $this->assertDatabaseHas('tags', [
            'name' => 'Laravel',
        ]);
    }

    public function test_authenticated_user_can_update_tag(): void
    {
        $user = User::factory()->create();

        $tag = Tag::factory()->create([
            'name' => 'Laravel',
        ]);

        $response = $this->actingAs($user)
            ->put("/admin/tags/{$tag->id}", [
                'name' => 'PHP',
            ]);

        $response->assertRedirect('/admin');

        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => 'PHP',
        ]);
    }

    public function test_authenticated_user_can_delete_tag(): void
    {
        $user = User::factory()->create();

        $tag = Tag::factory()->create([
            'name' => 'Laravel',
        ]);

        $response = $this->actingAs($user)
            ->delete("/admin/tags/{$tag->id}");

        $response->assertRedirect('/admin');

        $this->assertDatabaseMissing('tags', [
            'id' => $tag->id,
        ]);
    }

    public function test_unauthenticated_user_cannot_view_tag_edit_page(): void
    {
        $tag = Tag::factory()->create();

        $response = $this->get(
            "/admin/tags/{$tag->id}/edit"
        );

        $response->assertRedirect('/login');
    }

    public function test_unauthenticated_user_cannot_create_tag(): void
    {
        $response = $this->post('/admin/tags', [
            'name' => 'Laravel',
        ]);

        $response->assertRedirect('/login');

        $this->assertDatabaseMissing('tags', [
            'name' => 'Laravel',
        ]);
    }

    public function test_unauthenticated_user_cannot_update_tag(): void
    {
        $tag = Tag::factory()->create([
            'name' => 'Laravel',
        ]);

        $response = $this->put(
            "/admin/tags/{$tag->id}",
            [
                'name' => 'PHP',
            ]
        );

        $response->assertRedirect('/login');

        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => 'Laravel',
        ]);
    }

    public function test_unauthenticated_user_cannot_delete_tag(): void
    {
        $tag = Tag::factory()->create([
            'name' => 'Laravel',
        ]);

        $response = $this->delete(
            "/admin/tags/{$tag->id}"
        );

        $response->assertRedirect('/login');

        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
        ]);
    }
}
