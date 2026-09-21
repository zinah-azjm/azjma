<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedTinyInteger('age')->nullable();
            $table->string('avatar')->default('child_1');
            $table->string('pin', 255)->nullable();
        });
        Schema::table('family_tasks', function (Blueprint $table) {
            $table->text('description')->nullable();
            $table->string('category')->default('home');
            $table->string('repeat_type')->default('once');
            $table->json('repeat_days')->nullable();
            $table->time('due_time')->nullable();
            $table->text('rejection_reason')->nullable();
        });
        Schema::create('shopping_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->boolean('is_done')->default(false);
            $table->timestamps();
        });
        Schema::create('family_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->dateTime('starts_at');
            $table->string('emoji')->default('📅');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
        Schema::create('allowance_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_id')->constrained()->cascadeOnDelete();
            $table->foreignId('child_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('type');
            $table->string('description');
            $table->timestamps();
        });
        Schema::create('family_challenges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->unsignedInteger('target');
            $table->unsignedInteger('bonus_points')->default(20);
            $table->date('ends_at');
            $table->timestamps();
        });
        Schema::create('app_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('body');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_notifications');
        Schema::dropIfExists('family_challenges');
        Schema::dropIfExists('allowance_entries');
        Schema::dropIfExists('family_events');
        Schema::dropIfExists('shopping_items');
        Schema::table('family_tasks', function (Blueprint $table) {
            $table->dropColumn(['description', 'category', 'repeat_type', 'repeat_days', 'due_time', 'rejection_reason']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['age', 'avatar', 'pin']);
        });
    }
};
