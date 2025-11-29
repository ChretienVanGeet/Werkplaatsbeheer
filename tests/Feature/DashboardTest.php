<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class DashboardTest extends TestCase
{
    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get('/')->assertRedirect('/login');
    }

    //    public function test_authenticated_users_can_visit_the_dashboard(): void
    //    {
    //        $this->actingAs($user = User::factory()->create());
    //
    //        $this->get('/')->assertStatus(200);
    //    }
}
