<?php

namespace Tests\Unit;

use App\Http\Requests\UpdateTagRequest;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UpdateTagRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_current_tag_name_is_accepted(): void
    {
        $tag = Tag::factory()->create([
            'name' => 'Laravel',
        ]);

        $formRequest = new UpdateTagRequest;

        $route = new Route('PUT', '/admin/tags/{tag}', []);

        $route->bind(
            Request::create(
                "/admin/tags/{$tag->id}",
                'PUT'
            )
        );

        $route->setParameter('tag', $tag);

        $formRequest->setRouteResolver(fn () => $route);

        $validator = Validator::make(
            [
                'name' => 'Laravel',
            ],
            $formRequest->rules()
        );

        $this->assertFalse($validator->fails());
    }

    public function test_duplicate_tag_name_is_rejected(): void
    {
        $tag = Tag::factory()->create([
            'name' => 'Laravel',
        ]);

        Tag::factory()->create([
            'name' => 'PHP',
        ]);

        $formRequest = new UpdateTagRequest;

        $route = new Route('PUT', '/admin/tags/{tag}', []);

        $route->bind(
            Request::create(
                "/admin/tags/{$tag->id}",
                'PUT'
            )
        );

        $route->setParameter('tag', $tag);

        $formRequest->setRouteResolver(fn () => $route);

        $validator = Validator::make(
            [
                'name' => 'PHP',
            ],
            $formRequest->rules()
        );

        $this->assertTrue($validator->fails());

        $this->assertArrayHasKey(
            'name',
            $validator->errors()->toArray()
        );
    }
}
