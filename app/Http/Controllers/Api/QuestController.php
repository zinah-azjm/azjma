<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QuestController extends Controller
{
    private function avatarCatalog(): array
    {
        return [
            ['key' => 'child_1', 'emoji' => '🧒', 'title' => 'المغامر', 'cost' => 0],
            ['key' => 'child_2', 'emoji' => '👧', 'title' => 'النجمة', 'cost' => 0],
            ['key' => 'child_3', 'emoji' => '👦', 'title' => 'البطل', 'cost' => 0],
            ['key' => 'astronaut', 'emoji' => '🧑‍🚀', 'title' => 'رائد الفضاء', 'cost' => 120],
            ['key' => 'scientist', 'emoji' => '🧑‍🔬', 'title' => 'العالِم', 'cost' => 150],
            ['key' => 'artist', 'emoji' => '🧑‍🎨', 'title' => 'الفنان', 'cost' => 100],
            ['key' => 'wizard', 'emoji' => '🧙', 'title' => 'الساحر', 'cost' => 200],
            ['key' => 'superhero', 'emoji' => '🦸', 'title' => 'البطل الخارق', 'cost' => 250],
        ];
    }

    private function questionBank(): array
    {
        return [
            ['key' => 'math_8x7', 'category' => 'math', 'mode' => 'speed', 'difficulty' => 'easy', 'question' => '8 × 7 = ؟', 'choices' => [54, 56, 64], 'answer' => '56', 'points' => 10],
            ['key' => 'math_45plus18', 'category' => 'math', 'question' => '45 + 18 = ؟', 'choices' => [53, 63, 73], 'answer' => '63', 'points' => 10],
            ['key' => 'math_72div8', 'category' => 'math', 'question' => '72 ÷ 8 = ؟', 'choices' => [8, 9, 10], 'answer' => '9', 'points' => 10],
            ['key' => 'math_12x6', 'category' => 'math', 'question' => '12 × 6 = ؟', 'choices' => [62, 72, 82], 'answer' => '72', 'points' => 15],
            ['key' => 'math_100minus37', 'category' => 'math', 'question' => '100 - 37 = ؟', 'choices' => [53, 63, 73], 'answer' => '63', 'points' => 10],
            ['key' => 'words_sun', 'category' => 'words', 'question' => 'أي كلمة تبدأ بحرف ش؟', 'choices' => ['شمس', 'قمر', 'باب'], 'answer' => 'شمس', 'points' => 10],
            ['key' => 'words_plural_book', 'category' => 'words', 'question' => 'ما جمع كلمة كتاب؟', 'choices' => ['كاتب', 'كتب', 'مكتبة'], 'answer' => 'كتب', 'points' => 10],
            ['key' => 'words_opposite_big', 'category' => 'words', 'question' => 'ما عكس كلمة كبير؟', 'choices' => ['طويل', 'صغير', 'واسع'], 'answer' => 'صغير', 'points' => 10],
            ['key' => 'words_animal_b', 'category' => 'words', 'question' => 'أي حيوان يبدأ بحرف أ؟', 'choices' => ['أسد', 'حصان', 'فيل'], 'answer' => 'أسد', 'points' => 10],
            ['key' => 'words_sentence', 'category' => 'words', 'mode' => 'arrange', 'difficulty' => 'medium', 'question' => 'أكمل: السماء ...', 'choices' => ['زرقاء', 'سريع', 'قصير'], 'answer' => 'زرقاء', 'points' => 10],
            ['key' => 'general_planet', 'category' => 'general', 'question' => 'على أي كوكب نعيش؟', 'choices' => ['المريخ', 'الأرض', 'الزهرة'], 'answer' => 'الأرض', 'points' => 10],
            ['key' => 'general_colors', 'category' => 'general', 'question' => 'مزج الأزرق والأصفر يعطينا؟', 'choices' => ['أخضر', 'أحمر', 'بنفسجي'], 'answer' => 'أخضر', 'points' => 10],
            ['key' => 'general_days', 'category' => 'general', 'question' => 'كم يومًا في الأسبوع؟', 'choices' => [5, 7, 9], 'answer' => '7', 'points' => 10],
            ['key' => 'general_iraq', 'category' => 'general', 'question' => 'ما عاصمة العراق؟', 'choices' => ['بغداد', 'البصرة', 'أربيل'], 'answer' => 'بغداد', 'points' => 10],
            ['key' => 'general_water', 'category' => 'general', 'question' => 'بماذا نقيس حرارة الجسم؟', 'choices' => ['المسطرة', 'الميزان', 'المحرار'], 'answer' => 'المحرار', 'points' => 15],
            ['key' => 'true_sun', 'category' => 'true_false', 'question' => 'الشمس نجم.', 'choices' => ['صح', 'خطأ'], 'answer' => 'صح', 'points' => 5],
            ['key' => 'true_fish', 'category' => 'true_false', 'question' => 'السمك يعيش في الصحراء.', 'choices' => ['صح', 'خطأ'], 'answer' => 'خطأ', 'points' => 5],
            ['key' => 'true_year', 'category' => 'true_false', 'question' => 'السنة فيها 12 شهرًا.', 'choices' => ['صح', 'خطأ'], 'answer' => 'صح', 'points' => 5],
            ['key' => 'true_bird', 'category' => 'true_false', 'question' => 'كل الطيور تستطيع الطيران.', 'choices' => ['صح', 'خطأ'], 'answer' => 'خطأ', 'points' => 5],
            ['key' => 'true_plants', 'category' => 'true_false', 'question' => 'النباتات تحتاج إلى الماء.', 'choices' => ['صح', 'خطأ'], 'answer' => 'صح', 'points' => 5],
        ];
    }

    private function addPoints(int $userId, int $familyId, int $amount, string $type, string $description, ?string $referenceKey = null): void
    {
        DB::table('users')->where('id', $userId)->increment('points', $amount);
        if ($type === 'task') {
            DB::table('users')->where('id', $userId)->increment('task_points', $amount);
        } elseif ($type === 'game') {
            DB::table('users')->where('id', $userId)->increment('game_points', $amount);
        }
        DB::table('point_transactions')->insert([
            'user_id' => $userId,
            'family_id' => $familyId,
            'amount' => $amount,
            'type' => $type,
            'description' => $description,
            'reference_key' => $referenceKey,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
    private function parent(Request $request): void
    {
        abort_unless($request->user()->role === 'parent', 403);
    }

    private function child(Request $request): void
    {
        abort_unless($request->user()->role === 'child', 403);
    }

    public function children(Request $request)
    {
        $this->parent($request);
        return User::where('family_id', $request->user()->family_id)
            ->where('role', 'child')->get();
    }

    public function createChild(Request $request)
    {
        $this->parent($request);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'min:3', 'max:50', 'alpha_dash', 'unique:users,username'],
            'password' => ['required', 'string', 'min:8'],
            'age' => ['nullable', 'integer', 'between:3,17'],
            'avatar' => ['nullable', 'string', 'max:30'],
            'pin' => ['nullable', 'digits:4'],
        ]);
        $data['username'] = strtolower($data['username']);
        return response()->json(User::create($data + [
            'family_id' => $request->user()->family_id,
            'role' => 'child',
        ]), 201);
    }

    public function updateChild(Request $request, int $id)
    {
        $this->parent($request);
        $child = User::whereKey($id)->where('family_id', $request->user()->family_id)->where('role', 'child')->firstOrFail();
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:100'],
            'age' => ['nullable', 'integer', 'between:3,17'],
            'avatar' => ['nullable', 'string', 'max:30'],
            'pin' => ['nullable', 'digits:4'],
        ]);
        $child->update($data);
        return $child->fresh();
    }

    public function deleteChild(Request $request, int $id)
    {
        $this->parent($request);
        User::whereKey($id)->where('family_id', $request->user()->family_id)->where('role', 'child')->firstOrFail()->delete();
        return response()->noContent();
    }

    public function createParent(Request $request)
    {
        $this->parent($request);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'min:3', 'max:50', 'alpha_dash', 'unique:users,username'],
            'password' => ['required', 'string', 'min:8'],
        ]);
        $data['username'] = strtolower($data['username']);
        return response()->json(User::create($data + [
            'family_id' => $request->user()->family_id, 'role' => 'parent',
        ]), 201);
    }

    public function tasks(Request $request)
    {
        $query = DB::table('family_tasks')
            ->join('users as children', 'children.id', '=', 'family_tasks.child_id')
            ->where('family_tasks.family_id', $request->user()->family_id)
            ->select('family_tasks.*', 'children.name as child_name')
            ->orderByDesc('family_tasks.id');
        if ($request->user()->role === 'child') {
            $query->where('child_id', $request->user()->id);
        }
        return $query->get();
    }

    public function deleteTask(Request $request, int $id)
    {
        $this->parent($request);
        $task = DB::table('family_tasks')->where('id', $id)
            ->where('family_id', $request->user()->family_id)->firstOrFail();
        abort_if($task->status === 'approved', 422, 'لا يمكن إلغاء مهمة تم اعتماد نقاطها.');
        DB::table('family_tasks')->where('id', $id)->delete();
        return response()->noContent();
    }

    public function deductPoints(Request $request, int $id)
    {
        $this->parent($request);
        $data = $request->validate([
            'amount' => ['required', 'integer', 'min:1'],
            'reason' => ['required', 'string', 'max:200'],
        ]);

        return DB::transaction(function () use ($request, $id, $data) {
            $child = User::whereKey($id)->where('family_id', $request->user()->family_id)
                ->where('role', 'child')->lockForUpdate()->firstOrFail();
            if ($data['amount'] > $child->points) {
                throw ValidationException::withMessages(['amount' => 'لا يمكن خصم أكثر من رصيد الطفل.']);
            }
            DB::table('users')->where('id', $child->id)->decrement('points', $data['amount']);
            DB::table('point_transactions')->insert([
                'user_id' => $child->id,
                'family_id' => $child->family_id,
                'amount' => -$data['amount'],
                'type' => 'deduction',
                'description' => 'خصم من ولي الأمر: '.$data['reason'],
                'reference_key' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            return $child->fresh();
        });
    }

    public function createTask(Request $request)
    {
        $this->parent($request);
        $data = $request->validate([
            'child_id' => ['required', 'integer'],
            'title' => ['required', 'string', 'max:150'],
            'points' => ['required', 'integer', 'between:1,1000'],
            'description' => ['nullable', 'string', 'max:500'],
            'category' => ['nullable', 'in:home,study,health,faith'],
            'repeat_type' => ['nullable', 'in:once,daily,weekly'],
            'repeat_days' => ['nullable', 'array'],
            'due_time' => ['nullable', 'date_format:H:i'],
        ]);
        $child = User::whereKey($data['child_id'])
            ->where('family_id', $request->user()->family_id)
            ->where('role', 'child')->firstOrFail();
        $id = DB::table('family_tasks')->insertGetId([
            'family_id' => $child->family_id,
            'child_id' => $child->id,
            'created_by' => $request->user()->id,
            'title' => $data['title'],
            'points' => $data['points'],
            'status' => 'open',
            'description' => $data['description'] ?? null,
            'category' => $data['category'] ?? 'home',
            'repeat_type' => $data['repeat_type'] ?? 'once',
            'repeat_days' => isset($data['repeat_days']) ? json_encode($data['repeat_days']) : null,
            'due_time' => $data['due_time'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('app_notifications')->insert([
            'user_id' => $child->id,
            'title' => 'مهمة جديدة 🎯',
            'body' => $data['title'].' • +'.$data['points'].' نقطة',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        return response()->json(DB::table('family_tasks')->find($id), 201);
    }

    public function updateTask(Request $request, int $id)
    {
        $this->parent($request);
        $task = DB::table('family_tasks')->where('id', $id)
            ->where('family_id', $request->user()->family_id)->firstOrFail();
        abort_if($task->status === 'approved', 422, 'لا يمكن تعديل مهمة مكتملة.');
        $data = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:150'],
            'points' => ['sometimes', 'required', 'integer', 'between:1,1000'],
            'description' => ['nullable', 'string', 'max:500'],
            'category' => ['sometimes', 'in:home,study,health,faith'],
            'repeat_type' => ['sometimes', 'in:once,daily,weekly'],
            'due_time' => ['nullable', 'date_format:H:i'],
        ]);
        DB::table('family_tasks')->where('id', $id)->update($data + ['updated_at' => now()]);
        return DB::table('family_tasks')->find($id);
    }

    public function rejectTask(Request $request, int $id)
    {
        $this->parent($request);
        $data = $request->validate(['reason' => ['required', 'string', 'max:300']]);
        $changed = DB::table('family_tasks')->where('id', $id)
            ->where('family_id', $request->user()->family_id)
            ->where('status', 'pending')
            ->update([
                'status' => 'open',
                'rejection_reason' => $data['reason'],
                'updated_at' => now(),
            ]);
        abort_unless($changed, 404);
        User::where('family_id', $request->user()->family_id)->where('role', 'parent')
            ->each(function ($parent) use ($request) {
                DB::table('app_notifications')->insert([
                    'user_id' => $parent->id,
                    'title' => 'مهمة تنتظر الموافقة ✅',
                    'body' => $request->user()->name.' أكمل مهمة جديدة',
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            });
        return DB::table('family_tasks')->find($id);
    }

    public function submitTask(Request $request, int $id)
    {
        $this->child($request);
        $changed = DB::table('family_tasks')->where('id', $id)
            ->where('family_id', $request->user()->family_id)
            ->where('child_id', $request->user()->id)
            ->where('status', 'open')
            ->update(['status' => 'pending', 'updated_at' => now()]);
        abort_unless($changed, 404);
        $task = DB::table('family_tasks')->find($id);
        User::where('family_id', $request->user()->family_id)->where('role', 'parent')
            ->each(function ($parent) use ($request, $task) {
                DB::table('app_notifications')->insert([
                    'user_id' => $parent->id,
                    'title' => 'مهمة تنتظر الموافقة ✅',
                    'body' => $request->user()->name.' أنجز: '.$task->title,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
        return $task;
    }

    public function approveTask(Request $request, int $id)
    {
        $this->parent($request);
        return DB::transaction(function () use ($request, $id) {
            $task = DB::table('family_tasks')->where('id', $id)
                ->where('family_id', $request->user()->family_id)
                ->lockForUpdate()->first();
            abort_unless($task && $task->status === 'pending', 404);
            DB::table('family_tasks')->where('id', $id)->update([
                'status' => 'approved', 'updated_at' => now(),
            ]);
            $this->addPoints(
                $task->child_id,
                $request->user()->family_id,
                $task->points,
                'task',
                'إكمال مهمة: '.$task->title,
                'task_'.$task->id,
            );
            DB::table('app_notifications')->insert([
                'user_id' => $task->child_id,
                'title' => 'تم اعتماد مهمتك 🎉',
                'body' => 'حصلت على '.$task->points.' نقطة',
                'created_at' => now(), 'updated_at' => now(),
            ]);
            return DB::table('family_tasks')->find($id);
        });
    }

    public function completeTaskByParent(Request $request, int $id)
    {
        $this->parent($request);
        return DB::transaction(function () use ($request, $id) {
            $task = DB::table('family_tasks')->where('id', $id)
                ->where('family_id', $request->user()->family_id)
                ->lockForUpdate()->first();
            abort_unless($task && in_array($task->status, ['open', 'pending'], true), 404);

            DB::table('family_tasks')->where('id', $id)->update([
                'status' => 'approved', 'updated_at' => now(),
            ]);
            $this->addPoints(
                $task->child_id,
                $request->user()->family_id,
                $task->points,
                'task',
                'أكملها ولي الأمر: '.$task->title,
                'task_'.$task->id,
            );
            DB::table('app_notifications')->insert([
                'user_id' => $task->child_id,
                'title' => 'أحسنت! اكتملت مهمتك 🎉',
                'body' => 'أضاف لك ولي الأمر '.$task->points.' نقطة',
                'created_at' => now(), 'updated_at' => now(),
            ]);
            return DB::table('family_tasks')->find($id);
        });
    }

    public function pointsHistory(Request $request, int $childId)
    {
        $child = User::whereKey($childId)->where('family_id', $request->user()->family_id)
            ->where('role', 'child')->firstOrFail();
        abort_unless($request->user()->role === 'parent' || $request->user()->id === $child->id, 403);
        return [
            'child' => $child->only(['id', 'name', 'points', 'task_points', 'game_points']),
            'history' => DB::table('point_transactions')->where('user_id', $child->id)
                ->orderByDesc('id')->limit(100)->get(),
        ];
    }

    public function quiz(Request $request)
    {
        $this->child($request);
        return [
            'key' => 'math_8x7',
            'question' => '8 × 7 = ؟',
            'choices' => [54, 56, 64],
            'points' => 10,
            'completed' => DB::table('quiz_attempts')
                ->where('child_id', $request->user()->id)
                ->where('question_key', 'math_8x7')->exists(),
        ];
    }

    public function answerQuiz(Request $request)
    {
        $this->child($request);
        $data = $request->validate(['answer' => ['required', 'integer']]);
        if ($data['answer'] !== 56) {
            return ['correct' => false, 'points_awarded' => 0];
        }
        return DB::transaction(function () use ($request) {
            $inserted = DB::table('quiz_attempts')->insertOrIgnore([
                'child_id' => $request->user()->id,
                'question_key' => 'math_8x7',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            if ($inserted) {
                $this->addPoints(
                    $request->user()->id,
                    $request->user()->family_id,
                    10,
                    'game',
                    'تحدي الرياضيات',
                    'legacy_math_8x7',
                );
            }
            return ['correct' => true, 'points_awarded' => $inserted ? 10 : 0];
        });
    }

    public function games(Request $request)
    {
        $this->child($request);
        $today = now()->toDateString();
        $completed = DB::table('quiz_attempts')
            ->where('child_id', $request->user()->id)
            ->where('question_key', 'like', '%_'.$today)
            ->pluck('question_key')
            ->all();

        $categories = [
            'math' => ['title' => 'الرياضيات', 'emoji' => '🔢', 'color' => '#5C77EA'],
            'words' => ['title' => 'الكلمات', 'emoji' => '🔤', 'color' => '#E26D8D'],
            'general' => ['title' => 'معلومات عامة', 'emoji' => '🌍', 'color' => '#35A77A'],
            'true_false' => ['title' => 'صح أو خطأ', 'emoji' => '✅', 'color' => '#EF9F36'],
        ];

        foreach ($categories as $key => &$category) {
            $category['key'] = $key;
            $youngChild = ($request->user()->age ?? 8) < 8;
            $category['questions'] = collect($this->questionBank())
                ->where('category', $key)
                ->filter(fn ($question) => ! $youngChild || ($question['difficulty'] ?? 'easy') === 'easy')
                ->shuffle()
                ->map(function ($question) use ($completed, $today) {
                    unset($question['answer'], $question['category']);
                    $question['completed'] = in_array($question['key'].'_'.$today, $completed, true);
                    return $question;
                })->values();
            $category['completed_count'] = $category['questions']->where('completed', true)->count();
        }

        return ['date' => $today, 'categories' => array_values($categories)];
    }

    public function answerGame(Request $request)
    {
        $this->child($request);
        $data = $request->validate([
            'key' => ['required', 'string'],
            'answer' => ['required'],
        ]);
        $question = collect($this->questionBank())->firstWhere('key', $data['key']);
        abort_unless($question, 404);
        if ((string) $data['answer'] !== (string) $question['answer']) {
            return ['correct' => false, 'points_awarded' => 0];
        }

        return DB::transaction(function () use ($request, $question) {
            $earnedToday = (int) DB::table('point_transactions')
                ->where('user_id', $request->user()->id)
                ->where('type', 'game')->whereDate('created_at', now()->toDateString())->sum('amount');
            if ($earnedToday >= 100) {
                throw ValidationException::withMessages(['points' => 'وصلت إلى الحد اليومي لنقاط الألعاب (100 نقطة).']);
            }
            $attemptKey = $question['key'].'_'.now()->toDateString();
            $inserted = DB::table('quiz_attempts')->insertOrIgnore([
                'child_id' => $request->user()->id,
                'question_key' => $attemptKey,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            if ($inserted) {
                $award = min($question['points'], 100 - $earnedToday);
                $this->addPoints(
                    $request->user()->id,
                    $request->user()->family_id,
                    $award,
                    'game',
                    'إجابة صحيحة: '.$question['question'],
                    $attemptKey,
                );
            }
            return [
                'correct' => true,
                'points_awarded' => $inserted ? min($question['points'], 100 - $earnedToday) : 0,
                'already_completed' => !$inserted,
            ];
        });
    }

    public function progress(Request $request)
    {
        $this->child($request);
        $user = $request->user()->fresh();
        $gameDates = DB::table('point_transactions')
            ->where('user_id', $user->id)
            ->where('type', 'game')
            ->selectRaw('DATE(created_at) as played_on')
            ->distinct()
            ->orderByDesc('played_on')
            ->pluck('played_on')
            ->all();

        $streak = 0;
        $cursor = now()->startOfDay();
        if ($gameDates && $gameDates[0] !== $cursor->toDateString()) {
            $cursor->subDay();
        }
        foreach ($gameDates as $date) {
            if ($date !== $cursor->toDateString()) break;
            $streak++;
            $cursor->subDay();
        }

        $completedTasks = DB::table('family_tasks')
            ->where('child_id', $user->id)->where('status', 'approved')->count();
        $completedGames = DB::table('quiz_attempts')
            ->where('child_id', $user->id)->count();
        $earned = (int) DB::table('point_transactions')
            ->where('user_id', $user->id)->where('amount', '>', 0)->sum('amount');
        $level = intdiv($user->points, 100) + 1;
        $badges = [];
        if ($completedTasks >= 1) $badges[] = ['title' => 'أول إنجاز', 'emoji' => '🌟'];
        if ($completedTasks >= 5) $badges[] = ['title' => 'بطل المهام', 'emoji' => '🏆'];
        if ($completedGames >= 3) $badges[] = ['title' => 'ذكي العائلة', 'emoji' => '🧠'];
        if ($streak >= 3) $badges[] = ['title' => 'شعلة النشاط', 'emoji' => '🔥'];
        if ($user->points >= 100) $badges[] = ['title' => 'جامع النجوم', 'emoji' => '⭐'];

        return [
            'points' => $user->points,
            'task_points' => $user->task_points,
            'game_points' => $user->game_points,
            'level' => $level,
            'level_progress' => $user->points % 100,
            'next_level_points' => 100 - ($user->points % 100),
            'streak' => $streak,
            'completed_tasks' => $completedTasks,
            'completed_games' => $completedGames,
            'total_earned' => $earned,
            'badges' => $badges,
            'history' => DB::table('point_transactions')
                ->where('user_id', $user->id)
                ->orderByDesc('id')->limit(20)->get(),
        ];
    }

    public function avatars(Request $request)
    {
        $this->child($request);
        $owned = DB::table('user_avatars')->where('user_id', $request->user()->id)->pluck('avatar_key')->all();
        return collect($this->avatarCatalog())->map(function ($avatar) use ($owned, $request) {
            $avatar['owned'] = $avatar['cost'] === 0 || in_array($avatar['key'], $owned, true);
            $avatar['equipped'] = $request->user()->avatar === $avatar['key'];
            return $avatar;
        });
    }

    public function buyAvatar(Request $request, string $key)
    {
        $this->child($request);
        $avatar = collect($this->avatarCatalog())->firstWhere('key', $key);
        abort_unless($avatar, 404);
        if ($request->user()->points < $avatar['cost']) {
            throw ValidationException::withMessages(['points' => 'النقاط غير كافية لشراء الشخصية.']);
        }
        return DB::transaction(function () use ($request, $avatar, $key) {
            $exists = DB::table('user_avatars')->where('user_id', $request->user()->id)->where('avatar_key', $key)->exists();
            if (!$exists && $avatar['cost'] > 0) {
                DB::table('users')->where('id', $request->user()->id)->decrement('points', $avatar['cost']);
                DB::table('user_avatars')->insert([
                    'user_id' => $request->user()->id, 'avatar_key' => $key,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
                DB::table('point_transactions')->insert([
                    'user_id' => $request->user()->id, 'family_id' => $request->user()->family_id,
                    'amount' => -$avatar['cost'], 'type' => 'avatar',
                    'description' => 'شراء شخصية: '.$avatar['title'], 'reference_key' => 'avatar_'.$key,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
            return ['purchased' => true];
        });
    }

    public function equipAvatar(Request $request, string $key)
    {
        $this->child($request);
        $avatar = collect($this->avatarCatalog())->firstWhere('key', $key);
        abort_unless($avatar, 404);
        $owned = $avatar['cost'] === 0 || DB::table('user_avatars')->where('user_id', $request->user()->id)->where('avatar_key', $key)->exists();
        abort_unless($owned, 403);
        $request->user()->update(['avatar' => $key]);
        return $request->user()->fresh();
    }

    public function report(Request $request, int $childId)
    {
        $child = User::whereKey($childId)->where('family_id', $request->user()->family_id)->where('role', 'child')->firstOrFail();
        abort_unless($request->user()->role === 'parent' || $request->user()->id === $child->id, 403);
        $days = collect(range(6, 0))->map(function ($ago) use ($child) {
            $date = now()->subDays($ago);
            return [
                'date' => $date->format('m/d'),
                'points' => (int) DB::table('point_transactions')->where('user_id', $child->id)->whereDate('created_at', $date)->where('amount', '>', 0)->sum('amount'),
                'tasks' => DB::table('family_tasks')->where('child_id', $child->id)->where('status', 'approved')->whereDate('updated_at', $date)->count(),
                'games' => DB::table('quiz_attempts')->where('child_id', $child->id)->whereDate('created_at', $date)->count(),
            ];
        });
        return [
            'child' => $child->only(['id', 'name', 'age', 'avatar', 'points']),
            'week' => $days,
            'total_tasks' => DB::table('family_tasks')->where('child_id', $child->id)->where('status', 'approved')->count(),
            'total_games' => DB::table('quiz_attempts')->where('child_id', $child->id)->count(),
            'best_category' => 'الرياضيات',
        ];
    }

    public function rewards(Request $request)
    {
        return DB::table('rewards')->where('family_id', $request->user()->family_id)
            ->orderBy('cost')->get();
    }

    public function createReward(Request $request)
    {
        $this->parent($request);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'cost' => ['required', 'integer', 'between:1,100000'],
        ]);
        $id = DB::table('rewards')->insertGetId($data + [
            'family_id' => $request->user()->family_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return response()->json(DB::table('rewards')->find($id), 201);
    }

    public function requestReward(Request $request, int $id)
    {
        $this->child($request);
        $reward = DB::table('rewards')->where('id', $id)
            ->where('family_id', $request->user()->family_id)->firstOrFail();
        if ($request->user()->points < $reward->cost) {
            throw ValidationException::withMessages(['points' => 'النقاط غير كافية.']);
        }
        $requestId = DB::table('reward_requests')->insertGetId([
            'reward_id' => $id,
            'child_id' => $request->user()->id,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return response()->json(DB::table('reward_requests')->find($requestId), 201);
    }

    public function rewardRequests(Request $request)
    {
        $query = DB::table('reward_requests as rr')
            ->join('rewards as r', 'r.id', '=', 'rr.reward_id')
            ->where('r.family_id', $request->user()->family_id)
            ->select('rr.*', 'r.title', 'r.cost');
        if ($request->user()->role === 'child') {
            $query->where('rr.child_id', $request->user()->id);
        }
        return $query->orderByDesc('rr.id')->get();
    }

    public function approveReward(Request $request, int $id)
    {
        $this->parent($request);
        return DB::transaction(function () use ($request, $id) {
            $item = DB::table('reward_requests')->where('id', $id)
                ->lockForUpdate()->first();
            $reward = $item ? DB::table('rewards')->where('id', $item->reward_id)
                ->where('family_id', $request->user()->family_id)->first() : null;
            abort_unless($item && $reward && $item->status === 'pending', 404);
            $child = DB::table('users')->where('id', $item->child_id)->lockForUpdate()->first();
            if ($child->points < $reward->cost) {
                throw ValidationException::withMessages(['points' => 'النقاط لم تعد كافية.']);
            }
            DB::table('users')->where('id', $child->id)->decrement('points', $reward->cost);
            DB::table('point_transactions')->insert([
                'user_id' => $child->id,
                'family_id' => $request->user()->family_id,
                'amount' => -$reward->cost,
                'type' => 'reward',
                'description' => 'استبدال مكافأة: '.$reward->title,
                'reference_key' => 'reward_request_'.$item->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('reward_requests')->where('id', $id)->update([
                'status' => 'approved', 'updated_at' => now(),
            ]);
            return DB::table('reward_requests')->find($id);
        });
    }
}
