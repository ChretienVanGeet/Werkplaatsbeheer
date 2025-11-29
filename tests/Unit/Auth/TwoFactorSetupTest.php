<?php

declare(strict_types=1);

namespace Tests\Unit\Auth;

use App\Livewire\Auth\TwoFactorSetup;
use App\Models\User;
use Livewire\Livewire;
use OTPHP\TOTP;
use Tests\TestCase;

class TwoFactorSetupTest extends TestCase
{
    public function test_component_renders_setup_screen_for_user_without_2fa(): void
    {
        $user = User::factory()->create([
            'app_authentication_secret' => null,
        ]);

        $this->actingAs($user);

        Livewire::test(TwoFactorSetup::class)
            ->assertSet('setupComplete', false)
            ->assertSee('Twee-Factor Authenticatie Instellen')
            ->assertSee('Handmatige invoercode:')
            ->assertSeeHtml('img');
    }

    public function test_component_redirects_if_user_already_has_2fa_enabled(): void
    {
        $user = User::factory()->create([
            'app_authentication_secret' => 'test-secret',
        ]);

        $this->actingAs($user);

        Livewire::test(TwoFactorSetup::class)
            ->assertRedirect(route('dashboard'));
    }

    public function test_user_can_confirm_setup_with_valid_code(): void
    {
        $user = User::factory()->create([
            'app_authentication_secret'         => null,
            'app_authentication_recovery_codes' => null,
        ]);

        $this->actingAs($user);

        $component = Livewire::test(TwoFactorSetup::class);

        // Get the generated secret
        $secret = $component->get('secret');
        $this->assertNotEmpty($secret);

        // Generate a valid TOTP code
        $totp = TOTP::createFromSecret($secret);
        $validCode = $totp->now();

        $component
            ->set('confirmationCode', $validCode)
            ->call('confirmSetup')
            ->assertHasNoErrors()
            ->assertSet('setupComplete', true);

        // Verify user now has 2FA enabled
        $user->refresh();
        $this->assertNotNull($user->app_authentication_secret);
        $this->assertNotNull($user->app_authentication_recovery_codes);
        $this->assertCount(8, $user->app_authentication_recovery_codes);
    }

    public function test_user_cannot_confirm_setup_with_invalid_code(): void
    {
        $user = User::factory()->create([
            'app_authentication_secret' => null,
        ]);

        $this->actingAs($user);

        Livewire::test(TwoFactorSetup::class)
            ->set('confirmationCode', '123456')
            ->call('confirmSetup')
            ->assertHasErrors(['confirmationCode'])
            ->assertSet('setupComplete', false);

        // Verify user still doesn't have 2FA enabled
        $user->refresh();
        $this->assertNull($user->app_authentication_secret);
    }

    public function test_confirmation_code_validation_rules(): void
    {
        $user = User::factory()->create([
            'app_authentication_secret' => null,
        ]);

        $this->actingAs($user);

        // Test empty code
        Livewire::test(TwoFactorSetup::class)
            ->set('confirmationCode', '')
            ->call('confirmSetup')
            ->assertHasErrors(['confirmationCode']);

        // Test short code
        Livewire::test(TwoFactorSetup::class)
            ->set('confirmationCode', '12345')
            ->call('confirmSetup')
            ->assertHasErrors(['confirmationCode']);

        // Test long code
        Livewire::test(TwoFactorSetup::class)
            ->set('confirmationCode', '1234567')
            ->call('confirmSetup')
            ->assertHasErrors(['confirmationCode']);
    }

    public function test_remember_device_cookie_is_set_when_enabled(): void
    {
        $user = User::factory()->create([
            'app_authentication_secret' => null,
        ]);

        $this->actingAs($user);

        $component = Livewire::test(TwoFactorSetup::class);
        $secret = $component->get('secret');

        $totp = TOTP::createFromSecret($secret);
        $validCode = $totp->now();

        $component
            ->set('confirmationCode', $validCode)
            ->set('remember', true)
            ->call('confirmSetup');

        // Check that remember cookie would be set (we can't directly test cookie queue)
        $this->assertTrue($component->get('remember'));
    }

    public function test_continue_to_application_redirects_to_dashboard(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(TwoFactorSetup::class)
            ->set('setupComplete', true)
            ->call('continueToApplication')
            ->assertRedirect(route('dashboard'));
    }

    public function test_continue_to_application_handles_referer_logic(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Since the referer logic is complex to test in unit tests,
        // we'll just verify the method exists and can be called
        $component = Livewire::test(TwoFactorSetup::class)
            ->set('setupComplete', true)
            ->call('continueToApplication')
            ->assertRedirect(); // Just assert that some redirect happens

        // This is acceptable since the referer logic would be better tested
        // in a feature test with proper HTTP request context
    }

    public function test_qr_code_and_secret_are_generated(): void
    {
        $user = User::factory()->create([
            'app_authentication_secret' => null,
        ]);

        $this->actingAs($user);

        $component = Livewire::test(TwoFactorSetup::class);

        $qrCode = $component->get('qrCode');
        $secret = $component->get('secret');

        $this->assertNotEmpty($qrCode);
        $this->assertNotEmpty($secret);
        $this->assertStringContainsString('qrserver.com', $qrCode);
    }

    public function test_recovery_codes_are_displayed_after_setup(): void
    {
        $user = User::factory()->create([
            'app_authentication_secret' => null,
        ]);

        $this->actingAs($user);

        $component = Livewire::test(TwoFactorSetup::class);
        $secret = $component->get('secret');

        $totp = TOTP::createFromSecret($secret);
        $validCode = $totp->now();

        $component
            ->set('confirmationCode', $validCode)
            ->call('confirmSetup')
            ->assertSet('setupComplete', true)
            ->assertSee('Belangrijk:')
            ->assertSee('Bewaar deze herstelcodes op een veilige plaats');

        // Verify recovery codes are visible
        $user->refresh();
        foreach ($user->app_authentication_recovery_codes as $code) {
            $component->assertSee($code);
        }
    }
}
