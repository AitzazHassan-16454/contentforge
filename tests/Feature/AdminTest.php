<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Setting;
use App\Models\Tag;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    private function admin(): User
    {
        return User::factory()->create()->assignRole('admin');
    }

    private function moderator(): User
    {
        return User::factory()->create()->assignRole('moderator');
    }

    public function test_guest_is_redirected_from_admin_area(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    public function test_author_is_forbidden_from_admin_area(): void
    {
        $author = User::factory()->create()->assignRole('author');

        $this->actingAs($author)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_admin_can_view_dashboard(): void
    {
        Post::factory()->published()->count(3)->create();

        $this->actingAs($this->admin())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Admin/Dashboard'));
    }

    public function test_moderator_can_view_dashboard_but_not_users(): void
    {
        $this->actingAs($this->moderator())
            ->get(route('admin.dashboard'))
            ->assertOk();

        $this->actingAs($this->moderator())
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_admin_can_access_all_admin_pages(): void
    {
        Category::factory()->create();
        Tag::factory()->create();
        Comment::factory()->create();

        $admin = $this->admin();

        foreach ([
            'admin.dashboard',
            'admin.categories.index',
            'admin.tags.index',
            'admin.comments.index',
            'admin.users.index',
            'admin.roles.index',
            'admin.settings.edit',
        ] as $routeName) {
            $this->actingAs($admin)
                ->get(route($routeName))
                ->assertOk();
        }
    }

    public function test_admin_can_create_update_and_delete_category(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.categories.store'), ['name' => 'Engineering'])
            ->assertRedirect();

        $category = Category::where('name', 'Engineering')->first();
        $this->assertNotNull($category);
        $this->assertEquals('engineering', $category->slug);

        $this->actingAs($admin)
            ->patch(route('admin.categories.update', $category), ['name' => 'Engineering Updated'])
            ->assertRedirect();

        $this->assertDatabaseHas('categories', ['name' => 'Engineering Updated']);

        $this->actingAs($admin)
            ->delete(route('admin.categories.destroy', $category->refresh()))
            ->assertRedirect();

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_category_with_posts_cannot_be_deleted(): void
    {
        $category = Category::factory()->create();
        $post = Post::factory()->published()->create();
        $post->categories()->sync($category->id);

        $this->actingAs($this->admin())
            ->delete(route('admin.categories.destroy', $category))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_admin_can_create_and_delete_tag(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.tags.store'), ['name' => 'vuejs'])
            ->assertRedirect();

        $this->assertDatabaseHas('tags', ['slug' => 'vuejs']);

        $tag = Tag::where('slug', 'vuejs')->first();

        $this->actingAs($admin)
            ->delete(route('admin.tags.destroy', $tag))
            ->assertRedirect();

        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    }

    public function test_new_comments_require_approval(): void
    {
        $post = Post::factory()->published()->create();
        $author = User::factory()->create();

        $this->actingAs($author)
            ->post(route('posts.comments.store', $post->slug), ['body' => 'Hello there'])
            ->assertStatus(201);

        $comment = $post->comments()->first();
        $this->assertFalse($comment->is_approved);

        $this->get(route('posts.show', $post->slug))
            ->assertDontSee('Hello there');
    }

    public function test_admin_can_approve_comment_and_it_becomes_visible(): void
    {
        $post = Post::factory()->published()->create();
        $comment = Comment::factory()->create(['post_id' => $post->id, 'is_approved' => false, 'body' => 'Nice post!']);

        $this->actingAs($this->admin())
            ->post(route('admin.comments.approve', $comment))
            ->assertRedirect();

        $this->assertTrue($comment->refresh()->is_approved);

        $this->get(route('posts.show', $post->slug))
            ->assertSee('Nice post!');
    }

    public function test_moderator_can_approve_comments(): void
    {
        $comment = Comment::factory()->create(['is_approved' => false]);

        $this->actingAs($this->moderator())
            ->post(route('admin.comments.approve', $comment))
            ->assertRedirect();

        $this->assertTrue($comment->refresh()->is_approved);
    }

    public function test_admin_can_delete_comment(): void
    {
        $comment = Comment::factory()->create();

        $this->actingAs($this->admin())
            ->delete(route('admin.comments.destroy', $comment))
            ->assertRedirect();

        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    public function test_admin_can_change_user_role(): void
    {
        $user = User::factory()->create();
        $admin = $this->admin();

        $this->actingAs($admin)
            ->patch(route('admin.users.update', $user), ['role' => 'moderator'])
            ->assertRedirect();

        $this->assertTrue($user->fresh()->hasRole('moderator'));
    }

    public function test_admin_cannot_delete_themselves(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $admin))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_admin_can_update_site_settings(): void
    {
        $this->actingAs($this->admin())
            ->patch(route('admin.settings.update'), [
                'site_name' => 'My Blog',
                'tagline' => 'Writing daily',
                'twitter_url' => 'https://x.com/example',
                'github_url' => '',
                'linkedin_url' => null,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('settings', ['key' => 'site_name', 'value' => 'My Blog']);
        $this->assertDatabaseHas('settings', ['key' => 'twitter_url', 'value' => 'https://x.com/example']);
    }

    public function test_setting_model_get_and_set(): void
    {
        Setting::set('site_name', 'ContentForge');

        $this->assertEquals('ContentForge', Setting::get('site_name'));
        $this->assertEquals('fallback', Setting::get('missing', 'fallback'));
    }

    public function test_roles_have_expected_permissions(): void
    {
        $this->assertTrue(Role::findByName('admin')->hasPermissionTo('manage users'));
        $this->assertTrue(Role::findByName('moderator')->hasPermissionTo('moderate comments'));
        $this->assertFalse(Role::findByName('moderator')->hasPermissionTo('manage users'));
    }
}
