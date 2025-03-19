<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Mail\OtpMail;
use Inertia\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;


use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redirect;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    public function create(Request $request)
    {
        return Inertia::render('Request', [
            'email' => $request->query('email', ''),
        ]);
    }
    public function store(Request $request)
    {
        // Validate the email input
        $request->validate([
            'email' => 'required|email|exists:users,email', // Ensure email exists in users table
        ]);

        // Generate a 6-digit OTP
        $otp = rand(100000, 999999);

        // Store OTP in Cache (valid for 10 minutes)
        Cache::put('otp_' . $request->email, $otp, now()->addMinutes(10));


        // Debug: Log the OTP
        Log::info("OTP generated for " . $request->email . ": " . $otp);

        // Send OTP to user's email
        Mail::to($request->email)->send(new OtpMail($otp));

        return redirect()->route('verify', ['email' => $request->email]);
    }

    public function verify(Request $request)
{
    return Inertia::render('Verify', [
        'email' => $request->query('email'),
    ]);
}
    public function verify_request(Request $request)
{
    // Validate OTP input
    $request->validate([
        'email' => 'required|email|exists:users,email',
        'otp' => 'required|digits:6',
    ]);

    // Retrieve OTP from Cache
    $cachedOtp = Cache::get('otp_' . $request->email);

     // Debug: Log OTP to check if it's stored correctly
     Log::info("OTP entered: " . $request->otp);
     Log::info("OTP stored in cache: " . ($cachedOtp ?? 'No OTP found'));


     if (!$cachedOtp) {
        return back()->withErrors(['otp' => 'OTP has expired. Please request a new one.']);
    }

    if ((string) $cachedOtp !== (string) $request->otp) {
        return back()->withErrors(['otp' => 'Invalid OTP. Please try again.']);
    }


    // Clear OTP from cache after verification
    Cache::forget('otp_' . $request->email);

    // Mark user as verified (if needed)
    $user = User::where('email', $request->email)->first();
    if ($user) {
        $user->email_verified_at = now();
        $user->save();
    }

    return redirect()->route('dashboard')->with('success', 'OTP Verified Successfully!');
}
}
