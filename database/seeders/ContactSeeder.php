<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categoryIds = Category::pluck('id');

        Contact::factory()
            ->count(20)
            ->state(function () use ($categoryIds) {
                return [
                    'category_id' => $categoryIds->random(),
                ];
            })
            ->create()
            ->each(function ($contact) {
                $tagIds = Tag::inRandomOrder()
                    ->limit(random_int(1, 3))
                    ->pluck('id');

                $contact->tags()->attach($tagIds);
            });
    }
}