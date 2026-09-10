<?php

declare(strict_types=1);

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class CrmAuthController extends Controller
{
    /**
     * Show CRM Login Cockpit.
     */
    public function showLogin(): Response|RedirectResponse
    {
        if (Auth::check() && Auth::user()->is_admin) {
            return redirect()->route('crm.dashboard');
        }

        return Inertia::render('Crm/Login', [
            'status' => session('status'),
            'info'   => session('info'),
        ]);
    }

    /**
     * Authenticate Founder / Admin.
     */
    public function login(Request $request): RedirectResponse|JsonResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember', true);

        if (Auth::attempt($credentials, $remember)) {
            /** @var User $user */
            $user = Auth::user();

            if (!$user->is_admin) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                $msg = 'Access restricted. You do not have founder/administrator privileges.';
                if ($request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $msg], 403);
                }
                return back()->withErrors(['email' => $msg]);
            }

            // Check if user is using a temporary password and must set permanent credentials
            if ($user->must_change_password) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                $msg = 'Temporary password detected. Please set your permanent password and security recovery question.';
                if ($request->wantsJson()) {
                    return response()->json([
                        'success'             => false,
                        'first_time_required' => true,
                        'email'               => $user->email,
                        'message'             => $msg,
                    ], 200);
                }

                return back()->with([
                    'first_time_required' => true,
                    'pending_email'       => $user->email,
                    'info'                => $msg,
                ]);
            }

            $request->session()->regenerate();

            if ($request->wantsJson()) {
                return response()->json([
                    'success'  => true,
                    'redirect' => route('crm.dashboard'),
                ]);
            }

            return redirect()->intended(route('crm.dashboard'));
        }

        $msg = 'The provided credentials do not match our records.';
        if ($request->wantsJson()) {
            return response()->json(['success' => false, 'message' => $msg], 422);
        }

        return back()->withErrors([
            'email' => $msg,
        ]);
    }

    /**
     * Fetch the registered security question for password recovery (Step 1).
     */
    public function getSecurityQuestion(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', strtolower(trim($validated['email'])))
            ->where('is_admin', true)
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No administrator account found with this email address.',
            ], 404);
        }

        if (empty($user->security_question) || empty($user->security_answer)) {
            return response()->json([
                'success' => false,
                'message' => 'No security hint question configured for this account. Please use first-time temporary password activation or contact support.',
            ], 422);
        }

        return response()->json([
            'success'  => true,
            'question' => $user->security_question,
        ]);
    }

    /**
     * Reset Password using Security Question Answer (Step 2).
     */
    public function resetWithSecurityQuestion(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email'           => ['required', 'email'],
            'security_answer' => ['required', 'string'],
            'password'        => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::where('email', strtolower(trim($validated['email'])))
            ->where('is_admin', true)
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No administrator account found with this email address.',
            ], 404);
        }

        if (!$user->verifySecurityAnswer($validated['security_answer'])) {
            return response()->json([
                'success' => false,
                'errors'  => [
                    'security_answer' => ['Security answer does not match our records. Please try again.'],
                ],
            ], 422);
        }

        // Answer is correct -> Update password
        $user->password = Hash::make($validated['password']);
        $user->must_change_password = false;
        $user->password_changed_at = now();
        $user->save();

        // Log user in automatically
        Auth::login($user, true);
        $request->session()->regenerate();

        return response()->json([
            'success'  => true,
            'message'  => 'Password reset successfully! Entering Sales Cockpit...',
            'redirect' => route('crm.dashboard'),
        ]);
    }

    /**
     * First-Time Activation: Exchange temporary password for permanent password and security question.
     */
    public function firstTimePasswordUpdate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email'             => ['required', 'email'],
            'temp_password'     => ['required', 'string'],
            'password'          => ['required', 'string', 'min:8', 'confirmed'],
            'security_question' => ['required', 'string', 'max:255'],
            'security_answer'   => ['required', 'string', 'min:2', 'max:255'],
        ]);

        $user = User::where('email', strtolower(trim($validated['email'])))
            ->where('is_admin', true)
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No administrator account found with this email address.',
            ], 404);
        }

        if (!Hash::check($validated['temp_password'], $user->password)) {
            return response()->json([
                'success' => false,
                'errors'  => [
                    'temp_password' => ['The temporary password provided is incorrect.'],
                ],
            ], 422);
        }

        // Update to permanent password & save security question
        $user->password = Hash::make($validated['password']);
        $user->must_change_password = false;
        $user->security_question = trim($validated['security_question']);
        $user->setSecurityAnswer(trim($validated['security_answer']));
        $user->password_changed_at = now();
        $user->save();

        Auth::login($user, true);
        $request->session()->regenerate();

        return response()->json([
            'success'  => true,
            'message'  => 'Permanent password and security recovery configured! Entering Sales Cockpit...',
            'redirect' => route('crm.dashboard'),
        ]);
    }

    /**
     * In-Cockpit Password & Security Update (Authenticated).
     */
    public function updatePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password'  => ['required', 'string'],
            'password'          => ['required', 'string', 'min:8', 'confirmed'],
            'security_question' => ['nullable', 'string', 'max:255'],
            'security_answer'   => ['nullable', 'string', 'min:2', 'max:255'],
        ]);

        /** @var User $user */
        $user = Auth::user();

        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'success' => false,
                'errors'  => [
                    'current_password' => ['Your current password does not match.'],
                ],
            ], 422);
        }

        $user->password = Hash::make($validated['password']);
        $user->must_change_password = false;
        $user->password_changed_at = now();

        if (!empty($validated['security_question']) && !empty($validated['security_answer'])) {
            $user->security_question = trim($validated['security_question']);
            $user->setSecurityAnswer(trim($validated['security_answer']));
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Your password and security settings have been updated successfully.',
        ]);
    }

    /**
     * Logout.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('crm.login')->with('success', 'Logged out successfully.');
    }
}
