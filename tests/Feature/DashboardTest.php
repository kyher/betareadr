<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page()
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_dashboard()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));
        $response->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('authorProfile', null)
            ->where('betaReaderProfile', null));
    }

    public function test_dashboard_includes_the_users_author_profile()
    {
        $user = User::factory()->create();
        $user->authorProfile()->create([
            'display_name' => 'Octavia Butler',
            'handle' => 'octavia-butler',
            'bio' => 'Science fiction author.',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('authorProfile.display_name', 'Octavia Butler')
                ->where('authorProfile.handle', 'octavia-butler'));
    }

    public function test_dashboard_includes_the_users_beta_reader_profile()
    {
        $user = User::factory()->create();
        $user->betaReaderProfile()->create([
            'display_name' => 'Keen Reader',
            'handle' => 'keen-reader',
            'bio' => 'I love speculative fiction.',
            'reading_languages' => ['en'],
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('betaReaderProfile.display_name', 'Keen Reader')
                ->where('betaReaderProfile.handle', 'keen-reader'));
    }
}
