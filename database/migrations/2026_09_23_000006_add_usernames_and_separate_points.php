<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 50)->nullable()->unique();
            $table->unsignedInteger('task_points')->default(0);
            $table->unsignedInteger('game_points')->default(0);
            $table->string('email')->nullable()->change();
        });

        DB::table('users')->orderBy('id')->each(function ($user) {
            $taskPoints = max(0, (int) DB::table('point_transactions')
                ->where('user_id', $user->id)->where('type', 'task')->sum('amount'));
            $gamePoints = max(0, (int) DB::table('point_transactions')
                ->where('user_id', $user->id)->where('type', 'game')->sum('amount'));
            DB::table('users')->where('id', $user->id)->update([
                'username' => 'user'.$user->id,
                'task_points' => $taskPoints,
                'game_points' => $gamePoints,
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn(['username', 'task_points', 'game_points']);
            $table->string('email')->nullable(false)->change();
        });
    }
};
