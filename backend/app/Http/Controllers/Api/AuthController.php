<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Shop;
use App\Models\EmailVerificationCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Http\Requests\Auth\LoginRequest; // Import FormRequest
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResendEmailRequest;
use App\Http\Requests\Auth\VerifyEmailRequest;
use App\Http\Resources\ShopResource;
use App\Http\Resources\UserResource;     // Import Resource
use App\Mail\VerificationCodeMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * @group Auth
 * @tag Auth - User authentication & registration
 */
class AuthController extends Controller
{
    /**
     * @unauthenticated
     * Login user
     *
     * @requestBody required
     * @bodyParam email string required "User email" example=admin@kabita.test
     * @bodyParam password string required "User password" example=password
     * @bodyParam remember boolean "Remember me for 30 days" example=false
     * @response 200 body="{"success":true,"message":"Login berhasil.","data":{"user":{},"token":"string"}}"
     * @response 401 body="{"success":false,"message":"Email atau password salah."}"
     * @response 403 body="{"success":false,"message":"Akun Anda tidak aktif atau telah diblokir."}"
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        logger("Halo test test user masuk : " . json_encode($user));

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah.'
            ], 401);
        }

        if ($user->status !== UserStatus::ACTIVE) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda tidak aktif atau telah diblokir.'
            ], 403);
        }

        // Hapus token lama jika ada
        $user->tokens()->delete();

        // Buat token baru (30 hari jika remember, 12 jam jika tidak)
        $token = $user->createToken(
            'auth_token',
            ['*'],
            $request->boolean('remember') ? now()->addDays(30) : now()->addHours(12)
        )->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'data' => [
                'user' => new UserResource($user),
                'token' => $token,
            ]
        ]);
    }


    /**
     * @unauthenticated
     * Registrasi pengguna baru (Buyer atau Seller).
     *
     * @requestBody required
     * @bodyParam name string required "User full name" example=John Doe
     * @bodyParam email string required "User email" example=john@example.com
     * @bodyParam phone string required "User phone number" example=081234567890
     * @bodyParam password string required "User password" example=password123
     * @bodyParam password_confirmation string required "Password confirmation" example=password123
     * @bodyParam role string required "User role: buyer or seller" example=buyer
     * @bodyParam shop_name string "Shop name (required if role is seller)" example=Toko Contoh
     * @response 201 body="{"success":true,"message":"Registrasi berhasil.","data":{"user":{},"shop":{},"verification_code":"6digit"}}"
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        // 1. Buat User
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'status' => 'active',
        ]);

        // 2. Jika Seller, buat Shop (Status Pending)
        $shop = null;
        if ($request->role === 'seller' && $request->shop_name) {
            $shop = Shop::create([
                'seller_id' => $user->id,
                'name' => $request->shop_name,
                'slug' => Str::slug($request->shop_name) . '-' . time(),
                'status' => 'pending',
            ]);
        }

        // 3. Generate Kode Verifikasi 6 Digit
        $code = app()->environment('development') && $request->email === 'admin@kabita.test'
            ? '123456'
            : str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        EmailVerificationCode::create([
            'user_id' => $user->id,
            'code' => $code,
            'expires_at' => now()->addMinutes(10),
        ]);

        // 4. Kirim Email
        // Jika SMTP belum siap, aplikasi tetap lanjut dan mencatatnya di log.
        try {
            Mail::to($user->email)->send(new VerificationCodeMail($code, $user->name));
        } catch (Throwable $e) {
            Log::warning('Gagal mengirim kode verifikasi:', [
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Registrasi berhasil. Silakan cek email untuk kode verifikasi.',
            'data' => [
                'user' => new UserResource($user),
                'shop' => $shop ? new ShopResource($shop) : null,
                ...(app()->environment('local', 'development') ? ['verification_code' => $code] : []),
            ]
        ], 201);
    }

    /**
     * @unauthenticated
     * Verifikasi email dengan kode 6 digit
     *
     * @requestBody required
     * @bodyParam email string required "User email" example=john@example.com
     * @bodyParam code string required "6-digit verification code" example=123456
     * @response 200 body="{"success":true,"message":"Email berhasil diverifikasi. Silakan login."}"
     * @response 400 body="{"success":false,"message":"Kode verifikasi tidak valid atau sudah kedaluwarsa."}"
     */
    public function verifyEmail(VerifyEmailRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        $verificationCode = EmailVerificationCode::where('user_id', $user->id)
            ->where('code', $request->code)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->first();

        if (!$verificationCode) {
            return response()->json([
                'success' => false,
                'message' => 'Kode verifikasi tidak valid atau sudah kedaluwarsa.'
            ], 400);
        }

        // Tandai kode sebagai digunakan
        $verificationCode->update(['is_used' => true]);

        // Tandai email user sebagai verified
        $user->update(['email_verified_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Email berhasil diverifikasi. Silakan login.'
        ]);
    }

    /**
     * @unauthenticated
     * Kirim ulang kode verifikasi
     *
     * @response 200 body="{"success":true,"message":"Kode verifikasi baru telah dikirim.","data":{"verification_code":"6digit"}}"
     * @response 400 body="{"success":false,"message":"Email sudah diverifikasi."}"
     */
    public function resendCode(ResendEmailRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if ($user->email_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Email sudah diverifikasi.'
            ], 400);
        }

        // Hapus kode lama yang belum dipakai
        EmailVerificationCode::where('user_id', $user->id)
            ->where('is_used', false)
            ->delete();

        // Generate kode baru
        $code = app()->environment('development') && $request->email === 'admin@kabita.test'
            ? '123456'
            : str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        EmailVerificationCode::create([
            'user_id' => $user->id,
            'code' => $code,
            'expires_at' => now()->addMinutes(10),
        ]);

        // Kirim email baru
        try {
            Mail::to($user->email)->send(new VerificationCodeMail($code, $user->name));
        } catch (Throwable $e) {
            Log::warning('Gagal mengirim ulang kode verifikasi:', [
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Kode verifikasi baru telah dikirim ke email Anda.',
            ...(app()->environment('local', 'development') ? ['verification_code' => $code] : []),
        ]);
    }

    /**
     * Logout user
     *
     * @response 200 body="{"success":true,"message":"Logout berhasil"}"
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil'
        ]);
    }

    /**
     * Get current user
     */
    public function me(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $request->user()
        ]);
    }
}
