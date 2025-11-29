<?php

declare(strict_types=1);

namespace Tests\Unit\Auth;

use App\Livewire\Auth\TwoFactorChallenge;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use OTPHP\TOTP;
use Tests\TestCase;

class TwoFactorChallengeTest extends TestCase
{
    public function test_component_renders_challenge_form(): void
    {
        $user = User::factory()->create([
            'app_authentication_secret' => 'test-secret',
        ]);

        $this->actingAs($user);

        Livewire::test(TwoFactorChallenge::class)
            ->assertSet('showRecoveryCode', false)
            ->assertSee('Twee-Factor Authenticatie')
            ->assertSee('Authenticatorcode')
            ->assertSee('Gebruik in plaats daarvan herstelcode')
            ->assertDontSee('Herstelcode');
    }

    public function test_user_without_2fa_is_redirected_immediately(): void
    {
        $user = User::factory()->create([
            'app_authentication_secret' => null,
        ]);

        $this->actingAs($user);

        Livewire::test(TwoFactorChallenge::class)
            ->assertRedirect(route('dashboard'));
    }

    public function test_user_can_authenticate_with_valid_totp_code(): void
    {
        $secret = 'JBSWY3DPEHPK3PXP';
        $user = User::factory()->create([
            'app_authentication_secret' => $secret,
        ]);

        $this->actingAs($user);

        $totp = TOTP::createFromSecret($secret);
        $validCode = $totp->now();

        Livewire::test(TwoFactorChallenge::class)
            ->set('code', $validCode)
            ->call('submit')
            ->assertRedirect(route('dashboard'));

        // Verify session is marked as 2FA passed
        $this->assertTrue(session('two_factor_passed'));
    }

    public function test_user_can_authenticate_with_recovery_code(): void
    {
        $recoveryCodes = ['ABC12345', 'DEF67890', 'GHI09876'];
        $user = User::factory()->create([
            'app_authentication_secret'         => 'test-secret',
            'app_authentication_recovery_codes' => $recoveryCodes,
        ]);

        $this->actingAs($user);

        Livewire::test(TwoFactorChallenge::class)
            ->set('recovery_code', 'ABC12345')
            ->call('submit')
            ->assertRedirect(route('dashboard'));

        // Verify recovery code was consumed
        $user->refresh();
        $this->assertNotContains('ABC12345', $user->app_authentication_recovery_codes);
        $this->assertCount(2, $user->app_authentication_recovery_codes);
        $this->assertTrue(session('two_factor_passed'));
    }

    //    public function test_invalid_totp_code_shows_error(): void
    //    {
    //        $user = User::factory()->create([
    //            'app_authentication_secret' => 'test-secret',
    //        ]);
    //
    //        $this->actingAs($user);
    //
    //        $this->expectException(ValidationException::class);
    //
    //        Livewire::test(TwoFactorChallenge::class)
    //            ->set('code', '123456')
    //            ->call('submit');
    //    }

    //    public function test_invalid_recovery_code_shows_error(): void
    //    {
    //        $user = User::factory()->create([
    //            'app_authentication_secret'         => 'test-secret',
    //            'app_authentication_recovery_codes' => ['ABC12345'],
    //        ]);
    //
    //        $this->actingAs($user);
    //
    //        $this->expectException(ValidationException::class);
    //
    //        Livewire::test(TwoFactorChallenge::class)
    //            ->set('recovery_code', 'INVALID123')
    //            ->call('submit');
    //    }

    public function test_recovery_code_field_can_be_toggled(): void
    {
        $user = User::factory()->create([
            'app_authentication_secret' => 'test-secret',
        ]);

        $this->actingAs($user);

        $component = Livewire::test(TwoFactorChallenge::class)
            ->assertSet('showRecoveryCode', false)
            ->assertDontSee('Herstelcode')
            ->call('toggleRecoveryCode')
            ->assertSet('showRecoveryCode', true)
            ->assertSee('Herstelcode')
            ->assertSee('Herstelcode verbergen');

        // Toggle back
        $component->call('toggleRecoveryCode')
            ->assertSet('showRecoveryCode', false)
            ->assertSet('recovery_code', '')
            ->assertDontSee('Herstelcode');
    }

    public function test_remember_device_cookie_is_set_when_enabled(): void
    {
        $secret = 'JBSWY3DPEHPK3PXP';
        $user = User::factory()->create([
            'app_authentication_secret' => $secret,
        ]);

        $this->actingAs($user);

        $totp = TOTP::createFromSecret($secret);
        $validCode = $totp->now();

        Livewire::test(TwoFactorChallenge::class)
            ->set('code', $validCode)
            ->set('remember', true)
            ->call('submit')
            ->assertRedirect(route('dashboard'));

        $this->assertTrue(session('two_factor_passed'));
    }

    //    public function test_rate_limiting_prevents_brute_force_attacks(): void
    //    {
    //        $user = User::factory()->create([
    //            'app_authentication_secret' => 'test-secret',
    //        ]);
    //
    //        $this->actingAs($user);
    //
    //        // Simulate multiple failed attempts
    //        $rateKey = '2fa:' . sha1(request()->ip() . '|' . $user->getKey());
    //        RateLimiter::hit($rateKey, 60);
    //        RateLimiter::hit($rateKey, 60);
    //        RateLimiter::hit($rateKey, 60);
    //        RateLimiter::hit($rateKey, 60);
    //        RateLimiter::hit($rateKey, 60);
    //
    //        $this->expectException(ValidationException::class);
    //
    //        Livewire::test(TwoFactorChallenge::class)
    //            ->set('code', '123456')
    //            ->call('submit');
    //    }

    public function test_successful_authentication_clears_rate_limiting(): void
    {
        $secret = 'JBSWY3DPEHPK3PXP';
        $user = User::factory()->create([
            'app_authentication_secret' => $secret,
        ]);

        $this->actingAs($user);

        $rateKey = '2fa:'.sha1(request()->ip().'|'.$user->getKey());

        // Add some failed attempts
        RateLimiter::hit($rateKey, 60);
        RateLimiter::hit($rateKey, 60);

        $totp = TOTP::createFromSecret($secret);
        $validCode = $totp->now();

        Livewire::test(TwoFactorChallenge::class)
            ->set('code', $validCode)
            ->call('submit')
            ->assertRedirect(route('dashboard'));

        // Verify rate limiter was cleared
        $this->assertEquals(0, RateLimiter::attempts($rateKey));
    }

    public function test_validation_rules_are_applied(): void
    {
        $user = User::factory()->create([
            'app_authentication_secret' => 'test-secret',
        ]);

        $this->actingAs($user);

        // Both fields empty - should not throw validation error until submit
        Livewire::test(TwoFactorChallenge::class)
            ->set('code', '')
            ->set('recovery_code', '')
            ->assertHasNoErrors();
    }

    public function test_recovery_code_field_cleared_when_hidden(): void
    {
        $user = User::factory()->create([
            'app_authentication_secret' => 'test-secret',
        ]);

        $this->actingAs($user);

        Livewire::test(TwoFactorChallenge::class)
            ->call('toggleRecoveryCode')
            ->set('recovery_code', 'test-code')
            ->call('toggleRecoveryCode')
            ->assertSet('recovery_code', '');
    }

    public function test_mount_sets_2fa_passed_session_for_user_without_2fa(): void
    {
        $user = User::factory()->create([
            'app_authentication_secret' => null,
        ]);

        $this->actingAs($user);

        // Mock the request to test mount behavior
        $request = Request::create('/', 'GET');
        $request->setUserResolver(fn () => $user);

        $component = new TwoFactorChallenge();
        $component->mount($request);

        $this->assertTrue(session('two_factor_passed'));
    }

    //    public function test_submit_handles_user_without_2fa_gracefully(): void
    //    {
    //        $user = User::factory()->create([
    //            'app_authentication_secret' => null,
    //        ]);
    //
    //        $this->actingAs($user);
    //
    //        Livewire::test(TwoFactorChallenge::class)
    //            ->set('code', '123456')
    //            ->call('submit')
    //            ->assertRedirect(route('dashboard'));
    //
    //        $this->assertTrue(session('two_factor_passed'));
    //    }
}
