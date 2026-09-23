<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Contact;

class ContactTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_form_page_is_displayed(): void
    {
        Category::factory()->create([
            'content' => '商品について',
        ]);

        Tag::factory()->create([
            'name' => '重要',
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertViewIs('contact.index');
        $response->assertViewHas('categories');
        $response->assertViewHas('tags');
        $response->assertSee('商品について');
        $response->assertSee('重要');
    }

    public function test_thanks_page_is_displayed(): void
    {
        $response = $this->get('/thanks');

        $response->assertOk();
        $response->assertViewIs('contact.thanks');
    }



    public function test_contact_confirm_page_is_displayed_with_valid_data(): void
{
    $category = Category::factory()->create([
        'content' => '商品について',
    ]);

    $tag = Tag::factory()->create([
        'name' => '重要',
    ]);

    $data = [
        'first_name' => '太郎',
        'last_name' => '山田',
        'gender' => '1',
        'email' => 'test@example.com',
        'tel1' => '090',
        'tel2' => '1234',
        'tel3' => '5678',
        'address' => '東京都新宿区',
        'building' => 'テストビル101',
        'category_id' => $category->id,
        'tag_ids' => [$tag->id],
        'detail' => 'お問い合わせ内容です。',
    ];

    $response = $this->post('/contacts/confirm', $data);

    $response->assertOk();
    $response->assertViewIs('contact.confirm');
    $response->assertViewHas('validated');
    $response->assertViewHas('category');
    $response->assertViewHas('tags');

    $response->assertSee('太郎');
    $response->assertSee('山田');
    $response->assertSee('test@example.com');
    $response->assertSee('商品について');
    $response->assertSee('重要');
}

public function test_contact_confirm_returns_validation_errors_with_invalid_data(): void
{
    $response = $this->from('/')
        ->post('/contacts/confirm', []);

    $response->assertRedirect('/');

    $response->assertSessionHasErrors([
        'first_name',
        'last_name',
        'gender',
        'email',
        'tel',
        'address',
        'category_id',
        'detail',
    ]);
}

public function test_contact_is_stored_and_redirects_to_thanks(): void
{
    $category = Category::factory()->create([
        'content' => '商品について',
    ]);

    $tag = Tag::factory()->create([
        'name' => '重要',
    ]);

    $data = [
        'first_name' => '太郎',
        'last_name' => '山田',
        'gender' => '1',
        'email' => 'test@example.com',
        'tel1' => '090',
        'tel2' => '1234',
        'tel3' => '5678',
        'address' => '東京都新宿区',
        'building' => 'テストビル101',
        'category_id' => $category->id,
        'tag_ids' => [$tag->id],
        'detail' => 'お問い合わせ内容です。',
    ];

    $response = $this->post('/contacts', $data);

    $response->assertRedirect('/thanks');

    $this->assertDatabaseHas('contacts', [
        'category_id' => $category->id,
        'first_name' => '太郎',
        'last_name' => '山田',
        'gender' => 1,
        'email' => 'test@example.com',
        'tel' => '09012345678',
        'address' => '東京都新宿区',
        'building' => 'テストビル101',
        'detail' => 'お問い合わせ内容です。',
    ]);

  $contact = Contact::where(
    'email',
    'test@example.com'
)->firstOrFail();
    $this->assertDatabaseHas('contact_tag', [
        'contact_id' => $contact->id,
        'tag_id' => $tag->id,
    ]);
}

public function test_contact_store_returns_validation_errors_with_invalid_data(): void
{
    $response = $this->from('/')
        ->post('/contacts', []);

    $response->assertRedirect('/');

    $response->assertSessionHasErrors([
        'first_name',
        'last_name',
        'gender',
        'email',
        'tel',
        'address',
        'category_id',
        'detail',
    ]);
}



}