<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FacilityController extends Controller
{
    /**
     * 施設一覧の表示
     */
    public function index(Request $request)
    {
        $search = $request->query('search', '');

        $query = Facility::with(['owner'])
            ->withCount('rooms')
            ->orderByDesc('created_at');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('owner', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $facilities = $query->paginate(20)->withQueryString()->through(fn($f) => [
            'uuid' => $f->uuid,
            'name' => $f->name,
            'owner_name' => $f->owner->name ?? 'N/A',
            'owner_email' => $f->owner->email ?? 'N/A',
            'rooms_count' => $f->rooms_count,
            'is_published' => $f->is_published,
            'stripe_status' => $f->owner->stripe_account_status,
            'created_at' => $f->created_at->format('Y/m/d'),
        ]);

        return Inertia::render('Admin/Facility/Index', [
            'facilities' => $facilities,
            'filters' => [
                'search' => $search,
            ]
        ]);
    }

    /**
     * 新規登録フォームの表示
     */
    public function create()
    {
        $owners = User::where('role', 'owner')->orderBy('name')->get(['id', 'name', 'email']);

        return Inertia::render('Admin/Facility/Create', [
            'owners' => $owners
        ]);
    }

    /**
     * 施設の保存
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'postal_code' => 'nullable|string|max:10',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'same_day_booking_cutoff_time' => 'required|date_format:H:i',
            'check_in_start_time' => 'required|date_format:H:i',
            'check_in_end_time' => 'required|date_format:H:i|after:check_in_start_time',
            'platform_fee_rate' => 'required|numeric|min:0|max:1',
            'is_published' => 'required|boolean',
        ]);

        $facility = Facility::create($validated);

        return redirect()->route('admin.facilities.show', $facility->uuid)
            ->with('success', '施設を登録しました。');
    }

    /**
     * 施設詳細の表示
     */
    public function show(Facility $facility)
    {
        $facility->load(['owner', 'rooms']);

        return Inertia::render('Admin/Facility/Show', [
            'facility' => [
                'uuid' => $facility->uuid,
                'name' => $facility->name,
                'description' => $facility->description,
                'address' => $facility->address,
                'phone' => $facility->phone,
                'email' => $facility->email,
                'owner' => [
                    'name' => $facility->owner->name,
                    'email' => $facility->owner->email,
                ],
                'rooms' => $facility->rooms->map(fn($r) => [
                    'uuid' => $r->uuid,
                    'name' => $r->name,
                    'base_price' => $r->base_price_per_night,
                ]),
                'stripe_account_id' => $facility->owner->stripe_account_id,
                'stripe_status' => $facility->owner->stripe_account_status,
                'is_published' => $facility->is_published,
                'created_at' => $facility->created_at->format('Y/m/d H:i'),
            ]
        ]);
    }

    /**
     * 編集フォームの表示
     */
    public function edit(Facility $facility)
    {
        $owners = User::where('role', 'owner')->orderBy('name')->get(['id', 'name', 'email']);

        return Inertia::render('Admin/Facility/Edit', [
            'facility' => $facility,
            'owners' => $owners
        ]);
    }

    /**
     * 施設情報の更新
     */
    public function update(Request $request, Facility $facility)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'postal_code' => 'nullable|string|max:10',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'same_day_booking_cutoff_time' => 'required|date_format:H:i',
            'check_in_start_time' => 'required|date_format:H:i',
            'check_in_end_time' => 'required|date_format:H:i|after:check_in_start_time',
            'platform_fee_rate' => 'required|numeric|min:0|max:1',
            'is_published' => 'required|boolean',
        ]);

        $facility->update($validated);

        return redirect()->route('admin.facilities.show', $facility->uuid)
            ->with('success', '施設情報を更新しました。');
    }

    /**
     * 施設の削除
     */
    public function destroy(Facility $facility)
    {
        $facility->delete();

        return redirect()->route('admin.facilities.index')
            ->with('success', '施設を削除しました。');
    }

}
