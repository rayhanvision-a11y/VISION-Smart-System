<?php

namespace App\Http\Controllers;

use App\Services\TwoFactorService;
use Illuminate\Http\Request;

class TwoFactorController extends Controller
{
    public function show()
    {
        $user = auth()->user();
        return view('two-factor.show', ['enabled' => $user->two_factor_enabled]);
    }

    public function enroll()
    {
        $user = auth()->user();
        $secret = TwoFactorService::generateSecret();

        session(['2fa_pending_secret' => $secret]);

        $otpauthUrl = TwoFactorService::qrCodeUrl($secret, $user->email);

        return view('two-factor.enroll', compact('secret', 'otpauthUrl'));
    }

    public function confirm(Request $request)
    {
        $request->validate(['code' => 'required|string']);

        $secret = session('2fa_pending_secret');
        if (!$secret) {
            return redirect()->route('two-factor.enroll')->withErrors(['code' => 'Enrollment session expired, please try again.']);
        }

        if (!TwoFactorService::verify($secret, $request->code)) {
            return back()->withErrors(['code' => 'Invalid code. Please try again.']);
        }

        $recoveryCodes = TwoFactorService::generateRecoveryCodes();

        auth()->user()->update([
            'two_factor_secret'          => $secret,
            'two_factor_enabled'         => true,
            'two_factor_recovery_codes'  => $recoveryCodes,
        ]);

        session()->forget('2fa_pending_secret');
        session(['2fa_show_recovery_codes' => $recoveryCodes]);

        return redirect()->route('two-factor.show')->with('success', 'Two-factor authentication enabled.');
    }

    public function disable(Request $request)
    {
        $request->validate(['password' => 'required|current_password']);

        auth()->user()->update([
            'two_factor_secret'         => null,
            'two_factor_enabled'        => false,
            'two_factor_recovery_codes' => null,
        ]);

        return redirect()->route('two-factor.show')->with('success', 'Two-factor authentication disabled.');
    }

    // ── Login-time challenge ─────────────────────────────────────────
    public function challenge()
    {
        if (!session('2fa_user_id')) {
            return redirect()->route('login');
        }
        return view('two-factor.challenge');
    }

    public function verifyChallenge(Request $request)
    {
        $request->validate(['code' => 'required|string']);

        $userId = session('2fa_user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $user = \App\Models\User::findOrFail($userId);

        $isValidTotp = TwoFactorService::verify($user->two_factor_secret, $request->code);
        $isValidRecovery = false;

        if (!$isValidTotp && $user->two_factor_recovery_codes) {
            $codes = $user->two_factor_recovery_codes;
            $input = strtoupper(trim($request->code));
            if (($key = array_search($input, $codes, true)) !== false) {
                $isValidRecovery = true;
                unset($codes[$key]);
                $user->update(['two_factor_recovery_codes' => array_values($codes)]);
            }
        }

        if (!$isValidTotp && !$isValidRecovery) {
            return back()->withErrors(['code' => 'Invalid code.']);
        }

        session()->forget('2fa_user_id');
        auth()->login($user, session('2fa_remember', false));
        session()->forget('2fa_remember');
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }
}
