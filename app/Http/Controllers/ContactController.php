<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Support\Facades\DB;

class ContactController extends Controller
{
    /**
     * お問い合わせ入力画面
     */
    public function index()
    {
        $categories = Category::all();
        $tags = Tag::all();

        return view('contact.index', compact('categories', 'tags'));
    }

    /**
     * お問い合わせ確認画面
     */
    public function confirm(StoreContactRequest $request)
    {
        $validated = $request->validated();
        $request->flash();

        $category = Category::findOrFail(
            $validated['category_id']
        );

        $tags = Tag::whereIn(
            'id',
            $validated['tag_ids'] ?? []
        )->get();

        return view(
            'contact.confirm',
            compact('validated', 'category', 'tags')
        );
    }

    /**
     * お問い合わせ保存処理
     */
   public function store(StoreContactRequest $request)
{
    $validated = $request->validated();

    DB::transaction(function () use ($validated) {
        $contact = Contact::create([
            'category_id' => $validated['category_id'],
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'gender' => $validated['gender'],
            'email' => $validated['email'],
            'tel' => $validated['tel'],
            'address' => $validated['address'],
            'building' => $validated['building'] ?? null,
            'detail' => $validated['detail'],
        ]);

        if (!empty($validated['tag_ids'])) {
            $contact->tags()->attach($validated['tag_ids']);
        }
    });

    return redirect('/thanks');
}
    public function thanks()
    {
    return view('contact.thanks');
    }
}