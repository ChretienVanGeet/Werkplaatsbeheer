<?php

declare(strict_types=1);

namespace Tests\Unit\Middleware;

use App\Http\Middleware\RequireTwoFactor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cookie;
use Tests\TestCase;

class RequireTwoFactorTest extends TestCase
{
    private RequireTwoFactor $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new RequireTwoFactor();
    }

    public function test_guest_users_can_pass_through(): void
    {
        $request = Request::create('/', 'GET');

        $response = $this->middleware->handle($request, function ($req) {
            return new Response('success');
        });

        $this->assertEquals('success', $response->getContent());
    }

    public function test_user_without_2fa_is_redirected_to_setup(): void
    {
        $user = User::factory()->create([
            'app_authentication_secret' => null,
        ]);

        $request = Request::create('/', 'GET');
        $request->setUserResolver(fn () => $user);

        $response = $this->middleware->handle($request, function ($req) {
            return new Response('success');
        });

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertStringContainsString('two-factor-setup', $response->headers->get('Location'));
    }

    public function test_user_with_2fa_but_not_verified_is_redirected_to_challenge(): void
    {
        $user = User::factory()->create([
            'app_authentication_secret' => 'test-secret',
        ]);

        $request = Request::create('/', 'GET');
        $request->setUserResolver(fn () => $user);
        $request->setLaravelSession($this->app['session']->driver());

        // Simulate no 2FA passed session
        session()->forget('two_factor_passed');

        $response = $this->middleware->handle($request, function ($req) {
            return new Response('success');
        });

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertStringContainsString('two-factor-challenge', $response->headers->get('Location'));
    }

    public function test_user_with_2fa_and_verified_can_pass_through(): void
    {
        $user = User::factory()->create([
            'app_authentication_secret' => 'test-secret',
        ]);

        $request = Request::create('/', 'GET');
        $request->setUserResolver(fn () => $user);
        $request->setLaravelSession($this->app['session']->driver());

        // Simulate 2FA passed session
        session(['two_factor_passed' => true]);

        $response = $this->middleware->handle($request, function ($req) {
            return new Response('success');
        });

        $this->assertEquals('success', $response->getContent());
    }

    public function test_remembered_device_allows_passing_through(): void
    {
        $user = User::factory()->create([
            'app_authentication_secret' => 'test-secret',
        ]);

        $request = Request::create('/', 'GET');
        $request->setUserResolver(fn () => $user);
        $request->setLaravelSession($this->app['session']->driver());

        // Simulate remember device cookie
        $cookieValue = encrypt($user->getKey().'|'.sha1('test-user-agent'));
        Cookie::queue('two_factor_remember', $cookieValue, 60 * 24 * 30);
        $request->headers->set('User-Agent', 'test-user-agent');
        $request->cookies->set('two_factor_remember', $cookieValue);

        // No 2FA passed session
        session()->forget('two_factor_passed');

        $response = $this->middleware->handle($request, function ($req) {
            return new Response('success');
        });

        $this->assertEquals('success', $response->getContent());
        $this->assertTrue(session('two_factor_passed'));
    }

    public function test_invalid_remember_device_cookie_is_ignored(): void
    {
        $user = User::factory()->create([
            'app_authentication_secret' => 'test-secret',
        ]);

        $request = Request::create('/', 'GET');
        $request->setUserResolver(fn () => $user);
        $request->setLaravelSession($this->app['session']->driver());

        // Simulate invalid remember device cookie
        $invalidCookieValue = encrypt('wrong-user-id|wrong-hash');
        $request->cookies->set('two_factor_remember', $invalidCookieValue);

        // No 2FA passed session
        session()->forget('two_factor_passed');

        $response = $this->middleware->handle($request, function ($req) {
            return new Response('success');
        });

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertStringContainsString('two-factor-challenge', $response->headers->get('Location'));
    }

    public function test_malformed_remember_device_cookie_is_handled_gracefully(): void
    {
        $user = User::factory()->create([
            'app_authentication_secret' => 'test-secret',
        ]);

        $request = Request::create('/', 'GET');
        $request->setUserResolver(fn () => $user);
        $request->setLaravelSession($this->app['session']->driver());

        // Simulate malformed remember device cookie
        $request->cookies->set('two_factor_remember', 'malformed-cookie-value');

        // No 2FA passed session
        session()->forget('two_factor_passed');

        $response = $this->middleware->handle($request, function ($req) {
            return new Response('success');
        });

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertStringContainsString('two-factor-challenge', $response->headers->get('Location'));
    }

    public function test_middleware_handles_exception_in_cookie_decryption(): void
    {
        $user = User::factory()->create([
            'app_authentication_secret' => 'test-secret',
        ]);

        $request = Request::create('/', 'GET');
        $request->setUserResolver(fn () => $user);
        $request->setLaravelSession($this->app['session']->driver());

        // Simulate cookie that can't be decrypted
        $request->cookies->set('two_factor_remember', 'invalid-encrypted-data');

        // No 2FA passed session
        session()->forget('two_factor_passed');

        $response = $this->middleware->handle($request, function ($req) {
            return new Response('success');
        });

        // Should redirect to challenge since cookie is invalid
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertStringContainsString('two-factor-challenge', $response->headers->get('Location'));
    }

    public function test_remember_device_cookie_with_wrong_user_agent_fails(): void
    {
        $user = User::factory()->create([
            'app_authentication_secret' => 'test-secret',
        ]);

        $request = Request::create('/', 'GET');
        $request->setUserResolver(fn () => $user);
        $request->setLaravelSession($this->app['session']->driver());

        // Create cookie with different user agent
        $cookieValue = encrypt($user->getKey().'|'.sha1('different-user-agent'));
        $request->cookies->set('two_factor_remember', $cookieValue);
        $request->headers->set('User-Agent', 'current-user-agent');

        // No 2FA passed session
        session()->forget('two_factor_passed');

        $response = $this->middleware->handle($request, function ($req) {
            return new Response('success');
        });

        // Should redirect to challenge since user agent doesn't match
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertStringContainsString('two-factor-challenge', $response->headers->get('Location'));
    }

    public function test_is_remembered_device_method(): void
    {
        $user = User::factory()->create([
            'app_authentication_secret' => 'test-secret',
        ]);

        $request = Request::create('/', 'GET');
        $request->setUserResolver(fn () => $user);

        // Test with valid cookie
        $cookieValue = encrypt($user->getKey().'|'.sha1('test-user-agent'));
        $request->cookies->set('two_factor_remember', $cookieValue);
        $request->headers->set('User-Agent', 'test-user-agent');

        $this->assertTrue($this->invokeIsRememberedDevice($request, $user));

        // Test with invalid cookie
        $request->cookies->set('two_factor_remember', 'invalid');
        $this->assertFalse($this->invokeIsRememberedDevice($request, $user));

        // Test with no cookie
        $request->cookies->remove('two_factor_remember');
        $this->assertFalse($this->invokeIsRememberedDevice($request, $user));
    }

    public function test_middleware_preserves_intended_url(): void
    {
        $user = User::factory()->create([
            'app_authentication_secret' => null,
        ]);

        $request = Request::create('http://summa-techtrack.test/protected-page', 'GET');
        $request->setUserResolver(fn () => $user);
        $request->setLaravelSession($this->app['session']->driver());

        $this->middleware->handle($request, function ($req) {
            return new Response('success');
        });

        // The middleware should set the intended URL when redirecting
        $this->assertNotNull(session('url.intended'));
    }

    /**
     * Helper method to invoke the private isRememberedDevice method
     */
    private function invokeIsRememberedDevice(Request $request, User $user): bool
    {
        $reflection = new \ReflectionClass($this->middleware);
        $method = $reflection->getMethod('isRememberedDevice');
        $method->setAccessible(true);

        return $method->invoke($this->middleware, $request, $user);
    }
}
