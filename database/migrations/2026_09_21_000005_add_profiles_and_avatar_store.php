<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('families', function (Blueprint $table) {
            $table->string('code', 10)->nullable()->unique();
        });
        DB::table('families')->whereNull('code')->orderBy('id')->each(function ($family) {
            DB::table('families')->where('id', $family->id)->update([
                'code' => strtoupper(Str::random(6)),
            ]);
        });
        Schema::create('user_avatars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('avatar_key');
            $table->timestamps();
            $table->unique(['user_id', 'avatar_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_avatars');
        Schema::table('families', fn (Blueprint $table) => $table->dropColumn('code'));
    }
};
