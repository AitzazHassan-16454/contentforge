<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorPostsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_author_area(): void
    {
        $this->get(route('dashboard.posts.index'))->assertRedirect(route('login'));
    }

    public function test_author_can_view_their_posts(): void
    {
        $user = User::factory()->create();
        $own = Post::factory()->create(['user_id' => $user->id, 'title' => 'My Post']);
        $other = Post::factory()->create(['title' => 'Someone Else\'s Post']);

        $this->actingAs($user)
            ->get(route('dashboard.posts.index'))
            ->assertOk()
            ->assertSee($own->title)
            ->assertDontSee($other->title);
    }

    public function test_author_can_create_a_post(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('dashboard.posts.store'), [
                'title' => 'Fresh Draft',
                'content' => '# Hello',
                'excerpt' => 'A short excerpt',
                'status' => Post::STATUS_DRAFT,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('posts', [
            'user_id' => $user->id,
            'title' => 'Fresh Draft',
            'status' => Post::STATUS_DRAFT,
        ]);
    }

    public function test_author_can_update_a_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->draft()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->patch(route('dashboard.posts.update', $post), [
                'title' => 'Updated Title',
                'content' => 'Updated content',
                'excerpt' => null,
                'status' => Post::STATUS_DRAFT,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Updated Title',
        ]);
    }

    public function test_author_can_publish_and_unpublish_a_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->draft()->create(['user_id' => $user->id]);

        $this->actingAs($user)->post(route('dashboard.posts.publish', $post));

        $this->assertTrue($post->fresh()->isPublished());

        $this->actingAs($user)->post(route('dashboard.posts.unpublish', $post));

        $this->assertFalse($post->fresh()->isPublished());
    }

    public function test_author_cannot_modify_another_users_post(): void
    {
        $author = User::factory()->create();
        $intruder = User::factory()->create();
        $post = Post::factory()->draft()->create(['user_id' => $author->id]);

        $this->actingAs($intruder)
            ->patch(route('dashboard.posts.update', $post), [
                'title' => 'Hacked',
                'content' => 'Nope',
                'status' => Post::STATUS_PUBLISHED,
            ])
            ->assertForbidden();

        $this->actingAs($intruder)
            ->post(route('dashboard.posts.publish', $post))
            ->assertForbidden();

        $this->actingAs($intruder)
            ->delete(route('dashboard.posts.destroy', $post))
            ->assertForbidden();

        $this->assertDatabaseHas('posts', ['id' => $post->id, 'title' => $post->title]);
    }

    public function test_author_can_delete_a_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->delete(route('dashboard.posts.destroy', $post))
            ->assertRedirect();

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_creating_a_published_post_sets_published_at(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('dashboard.posts.store'), [
                'title' => 'Publish Now',
                'content' => '# Content',
                'status' => Post::STATUS_PUBLISHED,
            ])
            ->assertRedirect();

        $post = Post::where('title', 'Publish Now')->first();

        $this->assertTrue($post->isPublished());
        $this->assertNotNull($post->published_at);
    }

    public function test_editing_a_scheduled_post_preserves_its_schedule(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->scheduled()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->patch(route('dashboard.posts.update', $post), [
                'title' => 'Still Scheduled',
                'content' => 'Updated content',
                'status' => Post::STATUS_DRAFT,
            ])
            ->assertRedirect();

        $this->assertNotNull($post->fresh()->scheduled_at);
        $this->assertTrue($post->fresh()->isScheduled());
    }

    public function test_author_can_update_a_scheduled_posts_publish_time(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->scheduled()->create(['user_id' => $user->id]);

        $next = now()->addDays(3)->format('Y-m-d\TH:i');

        $this->actingAs($user)
            ->patch(route('dashboard.posts.update', $post), [
                'title' => $post->title,
                'content' => $post->content,
                'status' => Post::STATUS_DRAFT,
                'scheduled_at' => $next,
            ])
            ->assertRedirect();

        $this->assertEquals(
            \Illuminate\Support\Carbon::parse($next)->toDateTimeString(),
            $post->fresh()->scheduled_at->toDateTimeString(),
        );
    }

    public function test_clearing_scheduled_at_removes_the_schedule(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->scheduled()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->patch(route('dashboard.posts.update', $post), [
                'title' => $post->title,
                'content' => $post->content,
                'status' => Post::STATUS_DRAFT,
                'scheduled_at' => '',
            ])
            ->assertRedirect();

        $this->assertNull($post->fresh()->scheduled_at);
        $this->assertFalse($post->fresh()->isScheduled());
    }

    public function test_publishing_a_scheduled_post_clears_its_schedule(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->scheduled()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->patch(route('dashboard.posts.update', $post), [
                'title' => $post->title,
                'content' => $post->content,
                'status' => Post::STATUS_PUBLISHED,
            ])
            ->assertRedirect();

        $fresh = $post->fresh();

        $this->assertTrue($fresh->isPublished());
        $this->assertNull($fresh->scheduled_at);
    }
}
