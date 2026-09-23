<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\QuestController;
use App\Http\Controllers\Api\FamilyController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/recover', [AuthController::class, 'recover']);
Route::get('/family/{code}/children', [AuthController::class, 'familyChildren']);
Route::post('/pin-login', [AuthController::class, 'pinLogin']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/recovery-code', [AuthController::class, 'regenerateRecoveryCode']);
    Route::get('/children', [QuestController::class, 'children']);
    Route::post('/children', [QuestController::class, 'createChild']);
    Route::put('/children/{id}', [QuestController::class, 'updateChild']);
    Route::delete('/children/{id}', [QuestController::class, 'deleteChild']);
    Route::post('/children/{id}/points/deduct', [QuestController::class, 'deductPoints']);
    Route::post('/parents', [QuestController::class, 'createParent']);
    Route::get('/tasks', [QuestController::class, 'tasks']);
    Route::post('/tasks', [QuestController::class, 'createTask']);
    Route::put('/tasks/{id}', [QuestController::class, 'updateTask']);
    Route::delete('/tasks/{id}', [QuestController::class, 'deleteTask']);
    Route::post('/tasks/{id}/submit', [QuestController::class, 'submitTask']);
    Route::post('/tasks/{id}/approve', [QuestController::class, 'approveTask']);
    Route::post('/tasks/{id}/reject', [QuestController::class, 'rejectTask']);
    Route::get('/quiz', [QuestController::class, 'quiz']);
    Route::post('/quiz/answer', [QuestController::class, 'answerQuiz']);
    Route::get('/games', [QuestController::class, 'games']);
    Route::post('/games/answer', [QuestController::class, 'answerGame']);
    Route::get('/progress', [QuestController::class, 'progress']);
    Route::get('/points/history/{childId}', [QuestController::class, 'pointsHistory']);
    Route::get('/rewards', [QuestController::class, 'rewards']);
    Route::post('/rewards', [QuestController::class, 'createReward']);
    Route::post('/rewards/{id}/request', [QuestController::class, 'requestReward']);
    Route::get('/reward-requests', [QuestController::class, 'rewardRequests']);
    Route::post('/reward-requests/{id}/approve', [QuestController::class, 'approveReward']);
    Route::get('/family-dashboard', [FamilyController::class, 'dashboard']);
    Route::post('/shopping', [FamilyController::class, 'addShopping']);
    Route::post('/shopping/{id}/toggle', [FamilyController::class, 'toggleShopping']);
    Route::delete('/shopping/{id}', [FamilyController::class, 'deleteShopping']);
    Route::post('/events', [FamilyController::class, 'addEvent']);
    Route::get('/allowance/{childId}', [FamilyController::class, 'allowance']);
    Route::post('/allowance/{childId}', [FamilyController::class, 'addAllowance']);
    Route::post('/challenges', [FamilyController::class, 'addChallenge']);
    Route::post('/notifications/read', [FamilyController::class, 'readNotifications']);
    Route::delete('/account', [FamilyController::class, 'deleteAccount']);
    Route::get('/avatars', [QuestController::class, 'avatars']);
    Route::post('/avatars/{key}/buy', [QuestController::class, 'buyAvatar']);
    Route::post('/avatars/{key}/equip', [QuestController::class, 'equipAvatar']);
    Route::get('/reports/{childId}', [QuestController::class, 'report']);
    Route::post('/password', [AuthController::class, 'changePassword']);
});
