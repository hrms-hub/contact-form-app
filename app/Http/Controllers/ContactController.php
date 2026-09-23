<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExportContactRequest;
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

            if (! empty($validated['tag_ids'])) {
                $contact->tags()->attach($validated['tag_ids']);
            }
        });

        return redirect('/thanks');
    }

    public function thanks()
    {
        return view('contact.thanks');
    }

    /**
     * CSVエクスポート処理
     */
    public function export(ExportContactRequest $request)
    {
        $validated = $request->validated();

        $query = Contact::with('category');

        if (! empty($validated['keyword'])) {
            $keyword = $validated['keyword'];

            $query->where(function ($query) use ($keyword) {
                $query->where('first_name', 'like', "%{$keyword}%")
                    ->orWhere('last_name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%");
            });
        }

        if (isset($validated['gender']) && (int) $validated['gender'] !== 0) {
            $query->where('gender', $validated['gender']);
        }

        if (! empty($validated['category_id'])) {
            $query->where('category_id', $validated['category_id']);
        }

        if (! empty($validated['date'])) {
            $query->whereDate('created_at', $validated['date']);
        }

        $contacts = $query->latest()->get();

        $genderLabels = [
            1 => '男性',
            2 => '女性',
            3 => 'その他',
        ];

        return response()->streamDownload(function () use ($contacts, $genderLabels) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'ID',
                '氏名',
                '性別',
                'メール',
                '電話',
                '住所',
                '建物',
                'カテゴリ',
                '内容',
                '作成日時',
            ]);

            foreach ($contacts as $contact) {
                fputcsv($handle, [
                    $contact->id,
                    $contact->first_name.$contact->last_name,
                    $genderLabels[$contact->gender] ?? '',
                    $contact->email,
                    $contact->tel,
                    $contact->address,
                    $contact->building,
                    $contact->category?->content,
                    $contact->detail,
                    $contact->created_at,
                ]);
            }

            fclose($handle);
        }, 'contacts.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
