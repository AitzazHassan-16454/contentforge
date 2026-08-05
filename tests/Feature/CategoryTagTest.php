<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTagTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_page_lists_only_published_posts_in_that_category(): void
    {
        $category = Category::factory()->create();
        $included = Post::factory()->published()->create();
        $draft = Post::factory()->draft()->create();
        $other = Post::factory()->published()->create();

        $included->categories()->attach($category);
        $draft->categories()->attach($category);
        $other->categories()->attach(Category::factory()->create());

        $this->get(route('posts.category', $category->slug))
            ->assertOk()
            ->assertSee($included->title)
            ->assertDontSee($draft->title)
            ->assertDontSee($other->title);
    }

    public function test_tag_page_lists_only_published_posts_with_that_tag(): void
    {
        $tag = Tag::factory()->create();
        $included = Post::factory()->published()->create();
        $other = Post::factory()->published()->create();

        $included->tags()->attach($tag);
        $other->tags()->attach(Tag::factory()->create());

        $this->get(route('posts.tag', $tag->slug))
            ->assertOk()
            ->assertSee($included->title)
            ->assertDontSee($other->title);
    }

    public function test_homepage_can_be_filtered_by_category_and_tag_query_strings(): void
    {
        $category = Category::factory()->create();
        $tag = Tag::factory()->create();
        $included = Post::factory()->published()->create();
        $other = Post::factory()->published()->create();

        $included->categories()->attach($category);
        $included->tags()->attach($tag);
        $other->categories()->attach(Category::factory()->create());

        $this->get(route('posts.index', ['category' => $category->slug]))
            ->assertOk()
            ->assertSee($included->title)
            ->assertDontSee($other->title);

        $this->get(route('posts.index', ['tag' => $tag->slug]))
            ->assertOk()
            ->assertSee($included->title)
            ->assertDontSee($other->title);
    }

    public function test_author_can_assign_category_and_tags_when_creating_a_post(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $this->actingAs($user)
            ->post(route('dashboard.posts.store'), [
                'title' => 'Tagged Post',
                'content' => '# Hello',
                'status' => Post::STATUS_DRAFT,
                'category_ids' => [$category->id],
                'tags' => ['Laravel', 'AI'],
            ])
            ->assertRedirect();

        $post = Post::where('title', 'Tagged Post')->firstOrFail();

        $this->assertTrue($post->categories->contains($category));
        $this->assertSame(['Laravel', 'AI'], $post->tags->pluck('name')->all());
    }

    public function test_author_can_update_category_and_tags(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->draft()->create(['user_id' => $user->id]);
        $category = Category::factory()->create();

        $this->actingAs($user)
            ->patch(route('dashboard.posts.update', $post), [
                'title' => $post->title,
                'content' => $post->content,
                'status' => Post::STATUS_DRAFT,
                'category_ids' => [$category->id],
                'tags' => ['Updated'],
            ])
            ->assertRedirect();

        $fresh = $post->fresh();

        $this->assertTrue($fresh->categories->contains($category));
        $this->assertSame(['Updated'], $fresh->tags->pluck('name')->all());
    }

    public function test_existing_tags_are_reused_and_new_ones_created(): void
    {
        $user = User::factory()->create();
        $existing = Tag::factory()->create(['name' => 'Laravel']);

        $this->actingAs($user)
            ->post(route('dashboard.posts.store'), [
                'title' => 'Tag Sync',
                'content' => '# Hello',
                'status' => Post::STATUS_DRAFT,
                'tags' => ['Laravel', 'Streaming'],
            ])
            ->assertRedirect();

        $this->assertSame(2, Tag::count());
        $this->assertDatabaseHas('tags', ['id' => $existing->id, 'name' => 'Laravel']);

        $post = Post::where('title', 'Tag Sync')->firstOrFail();

        $this->assertCount(2, $post->tags);
    }

    public function test_invalid_category_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('dashboard.posts.store'), [
                'title' => 'Bad Category',
                'content' => '# Hello',
                'status' => Post::STATUS_DRAFT,
                'category_ids' => [999],
            ])
            ->assertSessionHasErrors('category_ids.0');
    }
}
