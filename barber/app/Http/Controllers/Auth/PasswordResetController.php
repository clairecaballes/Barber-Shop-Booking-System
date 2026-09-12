<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetCode;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    private const TOKEN_TTL_MINUTES = 15;

    /**
     * Show the "forgot password" form.
     */
    public function create(): View
    {
        return view('auth.forgot');
    }

    /**
     * Issue a one-time, expiring reset code and mail it to the given address.
     *
     * The reply is deliberately identical whether or not the address exists,
     * so the endpoint cannot be used to enumerate accounts (OWASP OTG-AUTHN).
     */
    public function sendCode(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);

        $email = strtolower($data['email']);
        $user = User::where('email', $email)->first();

        if ($user) {
            $code = (string) random_int(100000, 999999);

            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $email],
                ['token' => Hash::make($code), 'created_at' => now()],
            );

            try {
                Mail::to($user->email)->send(new PasswordResetCode($code, self::TOKEN_TTL_MINUTES));
            } catch (\Throwable $e) {
                // Never surface mail deliverability to the visitor; log quietly.
                Log::warning('Password reset code could not be sent to '.$email.': '.$e->getMessage());
            }
        }

        $request->session()->flash('reset_email', $email);

        return back()->with(
            'status',
            'If an account exists for that email, a 6-digit reset code is on its way. It expires in '.self::TOKEN_TTL_MINUTES.' minutes.'
        );
    }

    /**
     * Show the reset form (email + code + new password).
     */
    public function showResetForm(Request $request): View
    {
        return view('auth.reset', [
            'email' => $request->input('email') ?? session('reset_email') ?? '',
        ]);
    }

    /**
     * Verify the code, rotate the password, and neutralize old sessions.
     */
    public function reset(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'code' => ['required', 'string', 'size:6'],
            'password' => ['required', 'string', Password::min(8)->letters()->numbers(), 'confirmed'],
        ]);

        $email = strtolower($data['email']);
        $record = DB::table('password_reset_tokens')->where('email', $email)->first();

        $fail = function (string $message) {
            throw ValidationException::withMessages(['code' => $message]);
        };

        if (! $record) {
            $fail('That reset code is not valid. Request a new one from the sign-in page.');
        }

        if (Carbon::parse($record->created_at)->addMinutes(self::TOKEN_TTL_MINUTES)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            $fail('That reset code has expired. Request a new one.');
        }

        if (! Hash::check($data['code'], $record->token)) {
            $fail('That reset code is not valid. Request a new one.');
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            $fail('That reset code is not valid. Request a new one.');
        }

        $user->update(['password' => $data['password']]);

        // One-time code: delete it so it cannot be replayed.
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        // OWASP: a password reset invalidates every other session for the account.
        DB::table('sessions')->where('user_id', $user->id)->delete();

        $request->session()->forget('reset_email');

        return redirect()->route('login')->with('status', 'Password reset. Sign in with your new password.');
    }
}
