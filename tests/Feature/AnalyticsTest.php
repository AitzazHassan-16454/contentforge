<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\PostView;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_viewing_a_published_post_records_a_daily_view(): void
    {
        $post = Post::factory()->published()->create(['view_count' => 4]);

        $this->get(route('posts.show', $post->slug))->assertOk();

        $this->assertDatabaseHas('post_views', [
            'post_id' => $post->id,
            'view_date' => now()->toDateString(),
            'views' => 1,
        ]);

        $this->assertSame(5, $post->refresh()->view_count);
    }

    public function test_repeated_views_on_the_same_day_upsert_the_daily_counter(): void
    {
        $post = Post::factory()->published()->create();

        $this->get(route('posts.show', $post->slug))->assertOk();
        $this->get(route('posts.show', $post->slug))->assertOk();

        $this->assertSame(1, PostView::query()->where('post_id', $post->id)->count());
        $this->assertSame(2, PostView::query()->where('post_id', $post->id)->first()->views);
        $this->assertSame(2, $post->refresh()->view_count);
    }

    public function test_draft_posts_do_not_record_views(): void
    {
        $post = Post::factory()->draft()->create();

        $this->get(route('posts.show', $post->slug))->assertNotFound();

        $this->assertDatabaseMissing('post_views', ['post_id' => $post->id]);
    }

    public function test_guest_is_redirected_from_author_analytics(): void
    {
        $this->get(route('dashboard.analytics'))->assertRedirect(route('login'));
    }

    public function test_author_analytics_only_include_own_posts(): void
    {
        $author = User::factory()->create()->assignRole('author');
        $other = User::factory()->create()->assignRole('author');

        $own = Post::factory()->published()->create(['user_id' => $author->id, 'view_count' => 7]);
        Post::factory()->draft()->create(['user_id' => $author->id, 'view_count' => 3]);
        Post::factory()->published()->create(['user_id' => $other->id, 'view_count' => 100]);

        PostView::create(['post_id' => $own->id, 'view_date' => now()->toDateString(), 'views' => 7]);

        $this->actingAs($author)
            ->get(route('dashboard.analytics'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard/Analytics')
                ->where('stats.posts', 2)
                ->where('stats.views', 10)
                ->where('stats.published_posts', 1)
                ->where('top_posts', fn ($posts) => count($posts) === 2 && $posts[0]['views'] === 7)
                ->where('views_series.values', fn ($values) => collect($values)->sum() === 7));
    }

    public function test_guest_is_redirected_from_admin_analytics(): void
    {
        $this->get(route('admin.analytics'))->assertRedirect(route('login'));
    }

    public function test_author_is_forbidden_from_admin_analytics(): void
    {
        $author = User::factory()->create()->assignRole('author');

        $this->actingAs($author)
            ->get(route('admin.analytics'))
            ->assertForbidden();
    }

    public function test_admin_can_view_admin_analytics(): void
    {
        $this->actingAs(User::factory()->create()->assignRole('admin'))
            ->get(route('admin.analytics'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Admin/Analytics'));
    }

    public function test_moderator_can_view_admin_analytics(): void
    {
        $this->actingAs(User::factory()->create()->assignRole('moderator'))
            ->get(route('admin.analytics'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Admin/Analytics'));
    }
}
