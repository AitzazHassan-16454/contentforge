<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class MarketingPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_about_page_renders(): void
    {
        $this->get(route('about'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Marketing/About'));
    }

    public function test_pricing_page_renders_all_plans(): void
    {
        $response = $this->get(route('pricing'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page->component('Marketing/Pricing'));

        foreach (config('plans') as $plan) {
            $response->assertSee($plan['name']);
        }
    }

    public function test_privacy_page_renders(): void
    {
        $this->get(route('privacy'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Marketing/Privacy'));
    }

    public function test_terms_page_renders(): void
    {
        $this->get(route('terms'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Marketing/Terms'));
    }

    public function test_about_page_includes_live_stats(): void
    {
        Post::factory()->published()->count(4)->create();

        $this->get(route('about'))
            ->assertOk()
            ->assertSee('4');
    }
}
