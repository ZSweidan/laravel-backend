<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ForgotPasswordController;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return "hello";
// });




Route::group(['middleware' => 'auth:api'], function () {
   
    Route::post('send-notification', [AuthController::class, 'sendNotification']);
    Route::post('save-token', [AuthController::class, 'saveToken']);
    /*
    |--------------------------------------------------------------------------
    | Postsx
    |--------------------------------------------------------------------------
    |*/
    Route::get('/posts', [PostController::class,'getPosts']);
    Route::post('/getPostById',[PostController::class,'getPostById']);
    Route::post('/addPost',[PostController::class,'addPost']);
    Route::post('/deletePost',[PostController::class,'deletePost']);
    Route::post('/editPost',[PostController::class,'editPost']);

    
});

Route::post('forgot-password', [ForgotPasswordController::class, 'submitForgetPasswordForm'])->name('forget.password.post'); 
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'signup']);
// Route::post('forgot', [ForgotController::class, 'forgot']);
// Route::post('reset', [ForgotController::class, 'reset']);
Route::get('/forgot-password', function () {
    return view('auth.forgot-password');
})->middleware('guest')->name('password.request');

Route::post('/forgot-password', function (Request $request) {
    $request->validate(['email' => 'required|email']);

    $status = Password::sendResetLink(
        $request->only('email')
    );

    return $status === Password::RESET_LINK_SENT
                ? back()->with(['status' => __($status)])
                : back()->withErrors(['email' => __($status)]);
})->middleware('guest')->name('password.email');

Route::get('/reset-password/{token}', function ($token) {
    return view('passwords.reset', ['token' => $token]);
})->middleware('guest')->name('password.reset');

Route::post('/reset-password', function (Request $request) {
    $request->validate([
        'token' => 'required',
        'email' => 'required|email',
        'password' => 'required|min:8|confirmed',
    ]);

    $status = Password::reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),
        function ($user, $password) {
            $user->forceFill([
                'password' => Hash::make($password)
            ])->setRememberToken(Str::random(60));

            $user->save();

            event(new PasswordReset($user));
        }
    );

    return $status === Password::PASSWORD_RESET
                ? redirect()->route('login')->with('status', __($status))
                : back()->withErrors(['email' => [__($status)]]);
})->middleware('guest')->name('password.update');