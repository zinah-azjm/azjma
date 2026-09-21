<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FamilyController extends Controller
{
    private function parent(Request $request): void
    {
        abort_unless($request->user()->role === 'parent', 403);
    }

    public function dashboard(Request $request)
    {
        $familyId = $request->user()->family_id;
        return [
            'shopping' => DB::table('shopping_items')->where('family_id', $familyId)->orderBy('is_done')->orderByDesc('id')->get(),
            'events' => DB::table('family_events')->where('family_id', $familyId)->where('starts_at', '>=', now()->startOfDay())->orderBy('starts_at')->limit(20)->get(),
            'challenges' => DB::table('family_challenges')->where('family_id', $familyId)
                ->where('ends_at', '>=', now()->toDateString())->orderBy('ends_at')->get()
                ->map(function ($challenge) use ($familyId) {
                    $challenge->progress = (int) DB::table('point_transactions')
                        ->where('family_id', $familyId)->where('amount', '>', 0)
                        ->where('created_at', '>=', $challenge->created_at)->sum('amount');
                    $challenge->percent = min(100, (int) round(($challenge->progress / max(1, $challenge->target)) * 100));
                    return $challenge;
                }),
            'leaderboard' => User::where('family_id', $familyId)->where('role', 'child')->orderByDesc('points')->get(['id', 'name', 'avatar', 'points']),
            'notifications' => DB::table('app_notifications')->where('user_id', $request->user()->id)->orderByDesc('id')->limit(20)->get(),
        ];
    }

    public function addShopping(Request $request)
    {
        $data = $request->validate(['title' => ['required', 'string', 'max:150']]);
        $id = DB::table('shopping_items')->insertGetId($data + [
            'family_id' => $request->user()->family_id,
            'created_by' => $request->user()->id,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        return response()->json(DB::table('shopping_items')->find($id), 201);
    }

    public function toggleShopping(Request $request, int $id)
    {
        $item = DB::table('shopping_items')->where('id', $id)->where('family_id', $request->user()->family_id)->firstOrFail();
        DB::table('shopping_items')->where('id', $id)->update(['is_done' => !$item->is_done, 'updated_at' => now()]);
        return DB::table('shopping_items')->find($id);
    }

    public function deleteShopping(Request $request, int $id)
    {
        DB::table('shopping_items')->where('id', $id)->where('family_id', $request->user()->family_id)->delete();
        return response()->noContent();
    }

    public function addEvent(Request $request)
    {
        $this->parent($request);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'starts_at' => ['required', 'date'],
            'emoji' => ['nullable', 'string', 'max:10'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);
        $id = DB::table('family_events')->insertGetId([
            ...$data, 'emoji' => $data['emoji'] ?? '📅',
            'family_id' => $request->user()->family_id,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        return response()->json(DB::table('family_events')->find($id), 201);
    }

    public function allowance(Request $request, int $childId)
    {
        $child = User::whereKey($childId)->where('family_id', $request->user()->family_id)->where('role', 'child')->firstOrFail();
        $entries = DB::table('allowance_entries')->where('child_id', $child->id)->orderByDesc('id')->get();
        $balance = (float) DB::table('allowance_entries')->where('child_id', $child->id)
            ->selectRaw("SUM(CASE WHEN type IN ('income','saving') THEN amount ELSE -amount END) total")->value('total');
        return ['child' => $child->only(['id', 'name']), 'balance' => $balance, 'entries' => $entries];
    }

    public function addAllowance(Request $request, int $childId)
    {
        $this->parent($request);
        $child = User::whereKey($childId)->where('family_id', $request->user()->family_id)->where('role', 'child')->firstOrFail();
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'type' => ['required', 'in:income,expense,saving'],
            'description' => ['required', 'string', 'max:150'],
        ]);
        $id = DB::table('allowance_entries')->insertGetId($data + [
            'family_id' => $child->family_id, 'child_id' => $child->id,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        return response()->json(DB::table('allowance_entries')->find($id), 201);
    }

    public function addChallenge(Request $request)
    {
        $this->parent($request);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'target' => ['required', 'integer', 'min:1'],
            'bonus_points' => ['required', 'integer', 'min:1'],
            'ends_at' => ['required', 'date', 'after_or_equal:today'],
        ]);
        $id = DB::table('family_challenges')->insertGetId($data + [
            'family_id' => $request->user()->family_id,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        return response()->json(DB::table('family_challenges')->find($id), 201);
    }

    public function readNotifications(Request $request)
    {
        DB::table('app_notifications')->where('user_id', $request->user()->id)->update(['is_read' => true]);
        return response()->noContent();
    }

    public function deleteAccount(Request $request)
    {
        $user = $request->user();
        if ($user->role === 'parent' && User::where('family_id', $user->family_id)->where('role', 'parent')->count() === 1) {
            DB::table('families')->where('id', $user->family_id)->delete();
        } else {
            $user->delete();
        }
        return response()->noContent();
    }
}
