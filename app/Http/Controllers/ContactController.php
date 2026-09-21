<?php

namespace App\Http\Controllers;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Http\Request;

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
    public function confirm(Request $request)
    {
        // 3分割された電話番号を1つにする
        $request->merge([
            'tel' => $request->tel1 . $request->tel2 . $request->tel3,
        ]);

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'in:1,2,3'],
            'email' => ['required', 'email', 'max:255'],
            'tel' => ['required', 'regex:/^[0-9]{10,11}$/'],
            'address' => ['required', 'string', 'max:255'],
            'building' => ['nullable', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['exists:tags,id'],
            'detail' => ['required', 'string'],
        ]);

        $category = Category::findOrFail($validated['category_id']);

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
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'in:1,2,3'],
            'email' => ['required', 'email', 'max:255'],
            'tel' => ['required', 'regex:/^[0-9]{10,11}$/'],
            'address' => ['required', 'string', 'max:255'],
            'building' => ['nullable', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['exists:tags,id'],
            'detail' => ['required', 'string'],
        ]);

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

        return view('contact.thanks');
    }
}