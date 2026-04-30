<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureActions();
        $this->configureViews();
        $this->configureRateLimiting();
    }

    /**
     * Configure Fortify actions.
     */
    private function configureActions(): void
    {
        Fortify::resetUserPasswordsUsing(\App\Actions\Fortify\ResetUserPassword::class);
        Fortify::createUsersUsing(\App\Actions\Fortify\CreateNewUser::class);

        \Laravel\Fortify\Fortify::authenticateUsing(function (\Illuminate\Http\Request $request) {
            $login = $request->input('email');
            $password = $request->input('password');

            // 1. Try directly by email
            $user = \App\Models\User::where('email', $login)->first();

            // 2. If not found, try by student admission number
            if (!$user) {
                $student = \App\Models\Student::withoutGlobalScopes()->where('admission_number', $login)->first();
                if ($student) {
                    // Find the primary guardian's user
                    $guardian = $student->guardians()->wherePivot('is_primary_contact', true)->first() 
                                ?? $student->guardians()->first();
                    
                    if ($guardian && $guardian->user_id) {
                        $user = \App\Models\User::withoutGlobalScopes()->find($guardian->user_id);
                    }
                }
            }

            if ($user && \Illuminate\Support\Facades\Hash::check($password, $user->password)) {
                if ($user->status !== 'active') {
                    return null;
                }
                return $user;
            }

            return null;
        });
    }

    /**
     * Configure Fortify views.
     */
    private function configureViews(): void
    {
        Fortify::loginView(fn (Request $request) => Inertia::render('auth/Login', [
            'canResetPassword' => Features::enabled(Features::resetPasswords()),
            'canRegister' => Features::enabled(Features::registration()),
            'status' => $request->session()->get('status'),
        ]));

        Fortify::resetPasswordView(fn (Request $request) => Inertia::render('auth/ResetPassword', [
            'email' => $request->email,
            'token' => $request->route('token'),
        ]));

        Fortify::requestPasswordResetLinkView(fn (Request $request) => Inertia::render('auth/ForgotPassword', [
            'status' => $request->session()->get('status'),
        ]));

        Fortify::verifyEmailView(fn (Request $request) => Inertia::render('auth/VerifyEmail', [
            'status' => $request->session()->get('status'),
        ]));

        Fortify::registerView(fn () => Inertia::render('auth/Register'));

        Fortify::twoFactorChallengeView(fn () => Inertia::render('auth/TwoFactorChallenge'));

        Fortify::confirmPasswordView(fn () => Inertia::render('auth/ConfirmPassword'));
    }

    /**
     * Configure rate limiting.
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(60)->by($throttleKey);
        });
    }
}
