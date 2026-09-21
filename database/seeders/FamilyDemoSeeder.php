<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FamilyDemoSeeder extends Seeder
{
    public function run(): void
    {
        $parent = User::where('role', 'parent')->orderBy('id')->first();
        if (!$parent) return;

        $children = [
            ['name' => 'سارة', 'email' => 'sara@family.app', 'age' => 9, 'avatar' => 'child_2', 'points' => 140],
            ['name' => 'علي', 'email' => 'ali@family.app', 'age' => 7, 'avatar' => 'child_3', 'points' => 80],
        ];

        foreach ($children as $data) {
            $child = User::updateOrCreate(
                ['email' => $data['email']],
                $data + [
                    'family_id' => $parent->family_id,
                    'role' => 'child',
                    'password' => '12345678',
                    'pin' => $data['name'] === 'سارة' ? '2468' : '1357',
                ],
            );
            if (!DB::table('point_transactions')->where('user_id', $child->id)->where('reference_key', 'demo_opening')->exists()) {
                DB::table('point_transactions')->insert([
                    'user_id' => $child->id, 'family_id' => $parent->family_id,
                    'amount' => $data['points'], 'type' => 'bonus',
                    'description' => 'رصيد البداية التجريبي', 'reference_key' => 'demo_opening',
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }

            $taskSets = $data['name'] === 'سارة'
                ? [
                    ['رتّبي سريرك', 15, 'home', 'daily'],
                    ['اقرئي قصة لمدة 15 دقيقة', 25, 'study', 'daily'],
                    ['اشربي 5 أكواب ماء', 20, 'health', 'daily'],
                ]
                : [
                    ['اجمع ألعابك بعد اللعب', 15, 'home', 'daily'],
                    ['تدرّب على جدول الضرب', 25, 'study', 'daily'],
                    ['نظّف أسنانك صباحًا ومساءً', 20, 'health', 'daily'],
                ];
            foreach ($taskSets as [$title, $points, $category, $repeat]) {
                DB::table('family_tasks')->updateOrInsert(
                    ['child_id' => $child->id, 'title' => $title],
                    [
                        'family_id' => $parent->family_id, 'created_by' => $parent->id,
                        'points' => $points, 'status' => 'open', 'category' => $category,
                        'repeat_type' => $repeat, 'description' => 'مهمة يومية ممتعة',
                        'created_at' => now(), 'updated_at' => now(),
                    ],
                );
            }
        }

        foreach ([
            ['اختيار نزهة نهاية الأسبوع 🌳', 300],
            ['ساعة ألعاب إضافية 🎮', 150],
            ['اختيار حلوى اليوم 🍦', 80],
        ] as [$title, $cost]) {
            DB::table('rewards')->updateOrInsert(
                ['family_id' => $parent->family_id, 'title' => $title],
                ['cost' => $cost, 'created_at' => now(), 'updated_at' => now()],
            );
        }

        foreach ([
            ['قراءة 10 قصص هذا الأسبوع', 10, 40],
            ['إنجاز 15 مهمة عائلية', 15, 50],
            ['جمع 300 نقطة معًا', 300, 60],
        ] as [$title, $target, $bonus]) {
            DB::table('family_challenges')->updateOrInsert(
                ['family_id' => $parent->family_id, 'title' => $title],
                ['target' => $target, 'bonus_points' => $bonus, 'ends_at' => now()->addDays(7)->toDateString(), 'created_at' => now(), 'updated_at' => now()],
            );
        }

        foreach (['حليب', 'تفاح', 'دفاتر مدرسية'] as $title) {
            DB::table('shopping_items')->updateOrInsert(
                ['family_id' => $parent->family_id, 'title' => $title],
                ['created_by' => $parent->id, 'is_done' => false, 'created_at' => now(), 'updated_at' => now()],
            );
        }

        foreach ([
            ['ليلة فيلم العائلة', 2, '🎬'],
            ['زيارة الجدة', 4, '👵'],
            ['نزهة الحديقة', 6, '🌳'],
        ] as [$title, $days, $emoji]) {
            DB::table('family_events')->updateOrInsert(
                ['family_id' => $parent->family_id, 'title' => $title],
                ['starts_at' => now()->addDays($days)->setTime(18, 0), 'emoji' => $emoji, 'notes' => 'موعد عائلي', 'created_at' => now(), 'updated_at' => now()],
            );
        }
    }
}
