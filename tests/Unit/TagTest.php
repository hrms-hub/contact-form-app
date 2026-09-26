<?php

namespace Tests\Unit;

use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagTest extends TestCase
{
    use RefreshDatabase;

    public function test_tag_belongs_to_many_contacts(): void
    {
        $tag = Tag::factory()->create();

        $contacts = Contact::factory()->count(3)->create();

        $tag->contacts()->sync($contacts->pluck('id'));

        $this->assertCount(
            3,
            $tag->fresh()->contacts
        );
    }
}
