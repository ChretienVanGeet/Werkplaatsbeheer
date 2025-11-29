<?php

declare(strict_types=1);

namespace Tests\Unit\Settings;

use App\Livewire\Settings\TwoFactorAuthentication;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class TwoFactorAuthenticationTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    public function test_component_renders_for_user_without_2fa(): void
    {
        $user = User::factory()->create([
            'app_authentication_secret' => null,
        ]);

        $this->actingAs($user);

        Livewire::test(TwoFactorAuthentication::class)
            ->assertSet('confirming', false)
            ->assertSee('Twee-factor authenticatie is niet ingeschakeld')
            ->assertSee('Twee-Factor Authenticatie Inschakelen')
            ->assertDontSee('Twee-factor authenticatie is ingeschakeld');
    }

    public function test_component_renders_for_user_with_2fa_enabled(): void
    {
        $user = User::factory()->create([
            'app_authentication_secret'         => 'test-secret',
            'app_authentication_recovery_codes' => ['ABC12345', 'DEF67890'],
        ]);

        $this->actingAs($user);

        Livewire::test(TwoFactorAuthentication::class)
            ->assertSet('confirming', false)
            ->assertSee('Twee-factor authenticatie is ingeschakeld')
            ->assertSee('Twee-Factor Authenticatie Resetten')
            ->assertSee('Herstelcodes')
            ->assertSee('ABC12345')
            ->assertSee('DEF67890')
            ->assertDontSee('Twee-Factor Authenticatie Inschakelen');
    }

    public function test_user_can_enable_2fa(): void
    {
        $user = User::factory()->create([
            'app_authentication_secret' => null,
        ]);

        $this->actingAs($user);

        $component = Livewire::test(TwoFactorAuthentication::class)
            ->call('enable2FA')
            ->assertSet('confirming', true)
            ->assertSet('showQrCode', true)
            ->assertSee('QR-Code Scannen')
            ->assertSee('Handmatige invoercode:');

        $secret = $component->get('secret');
        $this->assertNotEmpty($secret);

        $qrCode = $component->get('qrCode');
        $this->assertStringContainsString('qrserver.com', $qrCode);
    }

    //    public function test_user_can_confirm_enable_with_valid_code(): void
    //    {
    //        $user = User::factory()->create([
    //            'app_authentication_secret'         => null,
    //            'app_authentication_recovery_codes' => null,
    //        ]);
    //
    //        $this->actingAs($user);
    //
    //        $component = Livewire::test(TwoFactorAuthentication::class)
    //            ->call('enable2FA');
    //
    //        $secret = $component->get('secret');
    //        $totp = TOTP::createFromSecret($secret);
    //        $validCode = $totp->now();
    //
    //        $component
    //            ->set('confirmationCode', $validCode)
    //            ->call('confirmEnable')
    //            ->assertHasNoErrors()
    //            ->assertSet('confirming', false)
    //            ->assertSessionHas('status', '2fa-enabled');
    //
    //        // Verify user now has 2FA enabled
    //        $user->refresh();
    //        $this->assertNotNull($user->app_authentication_secret);
    //        $this->assertNotNull($user->app_authentication_recovery_codes);
    //        $this->assertCount(8, $user->app_authentication_recovery_codes);
    //    }

    public function test_user_cannot_confirm_enable_with_invalid_code(): void
    {
        $user = User::factory()->create([
            'app_authentication_secret' => null,
        ]);

        $this->actingAs($user);

        Livewire::test(TwoFactorAuthentication::class)
            ->call('enable2FA')
            ->set('confirmationCode', '123456')
            ->call('confirmEnable')
            ->assertHasErrors(['confirmationCode'])
            ->assertSet('confirming', true);

        // Verify user still doesn't have 2FA enabled
        $user->refresh();
        $this->assertNull($user->app_authentication_secret);
    }

    public function test_user_can_cancel_2fa_setup(): void
    {
        $user = User::factory()->create([
            'app_authentication_secret' => null,
        ]);

        $this->actingAs($user);

        Livewire::test(TwoFactorAuthentication::class)
            ->call('enable2FA')
            ->assertSet('confirming', true)
            ->call('cancel')
            ->assertSet('confirming', false)
            ->assertSet('confirmationCode', '');
    }

    public function test_user_can_show_password_confirmation_for_reset(): void
    {
        $user = User::factory()->create([
            'app_authentication_secret' => 'test-secret',
        ]);

        $this->actingAs($user);

        Livewire::test(TwoFactorAuthentication::class)
            ->call('showPasswordConfirmation')
            ->assertSet('showPasswordConfirm', true)
            ->assertSet('password', '')
            ->assertSee('Bevestig Wachtwoord om 2FA te Resetten');
    }

    //    public function test_user_can_reset_2fa_with_correct_password(): void
    //    {
    //        $password = 'test-password';
    //        $user = User::factory()->create([
    //            'app_authentication_secret'         => 'test-secret',
    //            'app_authentication_recovery_codes' => ['ABC12345'],
    //            'password'                          => Hash::make($password),
    //        ]);
    //
    //        $this->actingAs($user);
    //
    //        $component = Livewire::test(TwoFactorAuthentication::class)
    //            ->call('showPasswordConfirmation')
    //            ->set('password', $password)
    //            ->call('confirmPassword')
    //            ->assertHasNoErrors()
    //            ->assertSet('showPasswordConfirm', false)
    //            ->assertSet('confirming', true)
    //            ->assertSessionHas('status', '2fa-reset');
    //
    //        // Verify old 2FA was cleared and new setup started
    //        $user->refresh();
    //        $this->assertNull($user->app_authentication_recovery_codes);
    //
    //        // Should have generated new secret for setup
    //        $this->assertNotEmpty($component->get('secret'));
    //    }

    public function test_user_cannot_reset_2fa_with_incorrect_password(): void
    {
        $user = User::factory()->create([
            'app_authentication_secret' => 'test-secret',
            'password'                  => Hash::make('correct-password'),
        ]);

        $this->actingAs($user);

        Livewire::test(TwoFactorAuthentication::class)
            ->call('showPasswordConfirmation')
            ->set('password', 'wrong-password')
            ->call('confirmPassword')
            ->assertHasErrors(['password'])
            ->assertSet('showPasswordConfirm', true);

        // Verify 2FA was not reset
        $user->refresh();
        $this->assertEquals('test-secret', $user->app_authentication_secret);
    }

    //    public function test_user_can_regenerate_recovery_codes(): void
    //    {
    //        $password = 'test-password';
    //        $user = User::factory()->create([
    //            'app_authentication_secret'         => 'test-secret',
    //            'app_authentication_recovery_codes' => ['ABC12345', 'DEF67890'],
    //            'password'                          => Hash::make($password),
    //        ]);
    //
    //        $this->actingAs($user);
    //
    //        // Start regenerate recovery codes process
    //        $component = Livewire::test(TwoFactorAuthentication::class)
    //            ->call('regenerateRecoveryCodes')
    //            ->assertSet('showPasswordConfirm', true)
    //            ->assertSessionHas('pending_action', 'regenerate_codes')
    //            ->assertSee('Bevestig Wachtwoord om Herstelcodes Opnieuw te Genereren');
    //
    //        // Confirm with password
    //        $component
    //            ->set('password', $password)
    //            ->call('confirmPassword')
    //            ->assertHasNoErrors()
    //            ->assertSessionHas('status', 'recovery-codes-regenerated');
    //
    //        // Verify recovery codes were regenerated
    //        $user->refresh();
    //        $this->assertNotNull($user->app_authentication_recovery_codes);
    //        $this->assertCount(8, $user->app_authentication_recovery_codes);
    //        $this->assertNotContains('ABC12345', $user->app_authentication_recovery_codes);
    //        $this->assertNotContains('DEF67890', $user->app_authentication_recovery_codes);
    //    }

    public function test_reset_state_clears_all_data(): void
    {
        $user = User::factory()->create([
            'app_authentication_secret' => null,
        ]);

        $this->actingAs($user);

        Livewire::test(TwoFactorAuthentication::class)
            ->call('enable2FA')
            ->set('confirmationCode', '123456')
            ->call('showPasswordConfirmation')
            ->set('password', 'test-password')
            ->call('resetState')
            ->assertSet('qrCode', '')
            ->assertSet('secret', '')
            ->assertSet('confirmationCode', '')
            ->assertSet('showQrCode', false)
            ->assertSet('confirming', false)
            ->assertSet('showPasswordConfirm', false)
            ->assertSet('password', '');

        $this->assertEmpty(session('pending_action'));
    }

    public function test_validation_rules_for_confirmation_code(): void
    {
        $user = User::factory()->create([
            'app_authentication_secret' => null,
        ]);

        $this->actingAs($user);

        $component = Livewire::test(TwoFactorAuthentication::class)
            ->call('enable2FA');

        // Test empty code
        $component
            ->set('confirmationCode', '')
            ->call('confirmEnable')
            ->assertHasErrors(['confirmationCode']);

        // Test short code
        $component
            ->set('confirmationCode', '12345')
            ->call('confirmEnable')
            ->assertHasErrors(['confirmationCode']);

        // Test long code
        $component
            ->set('confirmationCode', '1234567')
            ->call('confirmEnable')
            ->assertHasErrors(['confirmationCode']);
    }

    public function test_validation_rules_for_password(): void
    {
        $user = User::factory()->create([
            'app_authentication_secret' => 'test-secret',
        ]);

        $this->actingAs($user);

        Livewire::test(TwoFactorAuthentication::class)
            ->call('showPasswordConfirmation')
            ->set('password', '')
            ->call('confirmPassword')
            ->assertHasErrors(['password']);
    }

    public function test_render_returns_correct_data(): void
    {
        $recoveryCodes = ['ABC12345', 'DEF67890'];
        $user = User::factory()->create([
            'app_authentication_secret'         => 'test-secret',
            'app_authentication_recovery_codes' => $recoveryCodes,
        ]);

        $this->actingAs($user);

        $component = Livewire::test(TwoFactorAuthentication::class);

        $this->assertTrue($component->viewData('twoFactorEnabled'));
        $this->assertEquals($recoveryCodes, $component->viewData('recoveryCodes'));
    }

    //    public function test_session_pending_action_affects_password_confirmation_ui(): void
    //    {
    //        $user = User::factory()->create([
    //            'app_authentication_secret' => 'test-secret',
    //        ]);
    //
    //        $this->actingAs($user);
    //
    //        // Test regenerate codes action
    //        session(['pending_action' => 'regenerate_codes']);
    //
    //        Livewire::test(TwoFactorAuthentication::class)
    //            ->call('showPasswordConfirmation')
    //            ->assertSee('Bevestig Wachtwoord om Herstelcodes Opnieuw te Genereren')
    //            ->assertSee('Bevestig Opnieuw Genereren');
    //
    //        // Test default reset action
    //        session(['pending_action' => null]);
    //
    //        Livewire::test(TwoFactorAuthentication::class)
    //            ->call('showPasswordConfirmation')
    //            ->assertSee('Bevestig Wachtwoord om 2FA te Resetten')
    //            ->assertSee('Bevestig Reset');
    //    }
}
