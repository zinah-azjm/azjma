<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'family_name' => ['required', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'min:3', 'max:50', 'alpha_dash', 'unique:users,username'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $recoveryCode = strtoupper(Str::random(10));
        try {
            $user = DB::transaction(function () use ($data, $recoveryCode) {
                $familyId = DB::table('families')->insertGetId([
                'name' => $data['family_name'],
                'code' => strtoupper(Str::random(6)),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('rewards')->insert([
                [
                    'family_id' => $familyId,
                    'title' => 'اختيار فيلم العائلة 🎬',
                    'cost' => 100,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'family_id' => $familyId,
                    'title' => 'وجبتك المفضلة 🍕',
                    'cost' => 200,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
                return User::create([
                    'family_id' => $familyId,
                    'role' => 'parent',
                    'name' => $data['name'],
                    'username' => strtolower($data['username']),
                    'password' => $data['password'],
                    'recovery_code' => $recoveryCode,
                ]);
            });
        } catch (\Throwable $exception) {
            error_log('REGISTER_ERROR: '.$exception->getMessage());
            throw $exception;
        }

        $user->setAttribute('family_code', DB::table('families')->where('id', $user->family_id)->value('code'));
        return response()->json([
            'user' => $user,
            'token' => $user->createToken('mobile')->plainTextToken,
            'recovery_code' => $recoveryCode,
        ], 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);
        $login = strtolower(trim($data['username']));
        $user = User::where('username', $login)
            ->orWhere('email', $data['username'])
            ->first();
        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages(['username' => 'اسم المستخدم أو كلمة المرور غير صحيحة.']);
        }
        $user->setAttribute('family_code', DB::table('families')->where('id', $user->family_id)->value('code'));
        return [
            'user' => $user,
            'token' => $user->createToken('mobile')->plainTextToken,
        ];
    }

    public function me(Request $request)
    {
        $user = $request->user();
        $user->setAttribute('family_code', DB::table('families')->where('id', $user->family_id)->value('code'));
        return $user;
    }

    public function familyChildren(string $code)
    {
        $family = DB::table('families')->where('code', strtoupper($code))->firstOrFail();
        return User::where('family_id', $family->id)->where('role', 'child')
            ->get(['id', 'name', 'avatar', 'age']);
    }

    public function pinLogin(Request $request)
    {
        $data = $request->validate([
            'family_code' => ['required', 'string'],
            'child_id' => ['required', 'integer'],
            'pin' => ['required', 'digits:4'],
        ]);
        $family = DB::table('families')->where('code', strtoupper($data['family_code']))->first();
        $child = $family ? User::whereKey($data['child_id'])->where('family_id', $family->id)->where('role', 'child')->first() : null;
        if (!$child || !$child->pin || !Hash::check($data['pin'], $child->pin)) {
            throw ValidationException::withMessages(['pin' => 'رمز الطفل غير صحيح.']);
        }
        $child->setAttribute('family_code', $family->code);
        return ['user' => $child, 'token' => $child->createToken('child-pin')->plainTextToken];
    }

    public function changePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        $request->user()->update(['password' => $data['password']]);
        return ['message' => 'تم تغيير كلمة المرور.'];
    }

    public function recover(Request $request)
    {
        $data = $request->validate([
            'username' => ['required', 'string'],
            'recovery_code' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8'],
        ]);
        $user = User::where('username', strtolower($data['username']))->first();
        if (! $user || ! $user->recovery_code || ! Hash::check(strtoupper($data['recovery_code']), $user->recovery_code)) {
            throw ValidationException::withMessages(['recovery_code' => 'رمز الاستعادة غير صحيح.']);
        }
        $user->update(['password' => $data['password']]);
        $user->tokens()->delete();
        return ['message' => 'تم تعيين كلمة المرور الجديدة.'];
    }

    public function regenerateRecoveryCode(Request $request)
    {
        abort_unless($request->user()->role === 'parent', 403);
        $code = strtoupper(Str::random(10));
        $request->user()->update(['recovery_code' => $code]);
        return ['recovery_code' => $code];
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->noContent();
    }
}
