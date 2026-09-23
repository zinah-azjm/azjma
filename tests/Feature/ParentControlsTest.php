<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ParentControlsTest extends TestCase
{
    use RefreshDatabase;

    public function test_username_login_parent_controls_and_separate_point_balances(): void
    {
        $register = $this->postJson('/api/register', [
            'family_name' => 'عائلة الاختبار',
            'name' => 'الأم',
            'username' => 'test_parent',
            'password' => 'Family1234',
        ])->assertCreated();

        $this->postJson('/api/login', [
            'username' => 'test_parent',
            'password' => 'Family1234',
        ])->assertOk()->assertJsonPath('user.username', 'test_parent');

        $parent = User::findOrFail($register->json('user.id'));
        Sanctum::actingAs($parent);
        $childResponse = $this->postJson('/api/children', [
            'name' => 'علي',
            'username' => 'test_child',
            'password' => 'Child1234',
            'age' => 9,
            'pin' => '1234',
        ])->assertCreated();
        $child = User::findOrFail($childResponse->json('id'));

        $task = $this->postJson('/api/tasks', [
            'child_id' => $child->id,
            'title' => 'رتب غرفتك',
            'points' => 20,
            'category' => 'home',
            'repeat_type' => 'daily',
        ])->assertCreated();
        DB::table('family_tasks')->where('id', $task->json('id'))->update(['status' => 'pending']);
        $this->postJson('/api/tasks/'.$task->json('id').'/approve')->assertOk();

        Sanctum::actingAs($child);
        $this->postJson('/api/games/answer', [
            'key' => 'math_8x7',
            'answer' => 56,
        ])->assertOk()->assertJsonPath('points_awarded', 10);

        $child->refresh();
        $this->assertSame(20, $child->task_points);
        $this->assertSame(10, $child->game_points);
        $this->assertSame(30, $child->points);

        Sanctum::actingAs($parent);
        $this->postJson('/api/children/'.$child->id.'/points/deduct', [
            'amount' => 5,
            'reason' => 'تجربة الخصم',
        ])->assertOk()->assertJsonPath('points', 25);

        $openTask = $this->postJson('/api/tasks', [
            'child_id' => $child->id,
            'title' => 'اقرأ كتاباً',
            'points' => 10,
        ])->assertCreated();
        $this->deleteJson('/api/tasks/'.$openTask->json('id'))->assertNoContent();
        $this->assertDatabaseMissing('family_tasks', ['id' => $openTask->json('id')]);
        $this->getJson('/api/tasks')->assertOk()->assertJsonPath('0.child_name', 'علي');
    }
}
