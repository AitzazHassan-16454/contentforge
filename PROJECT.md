# ContentForge

ContentForge is an AI-powered blogging platform built with Laravel, Inertia, and Vue 3. It helps authors generate full blog posts from a topic or prompt using the official Laravel AI SDK, then review, edit, and publish them through a clean draft/publish workflow.

## Tech Stack

- **Backend**: Laravel 13 (PHP 8.3+)
- **Frontend**: Vue 3 + Inertia.js
- **Build tool**: Vite
- **Auth**: Laravel Breeze (session-based)
- **AI**: [Laravel AI SDK](https://laravel.com/docs/ai-sdk) (`laravel/ai`) — provider-agnostic (OpenAI, Anthropic, Gemini, and more)

## Features

### Public (no auth)

- **Homepage** — lists published posts, newest first, paginated; filterable by category or tag
- **Category & tag pages** — every published post grouped and browsable by `/categories/{slug}` and `/tags/{slug}`
- **Post page** — renders a single published post (markdown) by slug, with category/tag chips and approved comments

### Author (auth required)

- **My Posts** — manage your own posts with draft/published status
- **Post editor** — title, excerpt, markdown content with live preview; category + tag assignment; save as draft or publish/unpublish
- **AI Blog Post Generator** — generate a full post from a topic, tone, keywords, and target length. Generated drafts are editable and are never published automatically

### Admin (`/admin`)

- **Dashboard** — overview stats (users, posts, comments, reactions, pending moderation, contact messages) plus latest users/posts/comments
- **Categories & tags** — full CRUD with search; categories with posts cannot be deleted
- **Comments** — moderation queue; approve/unapprove/delete; pending comments are hidden from the public post page until approved
- **Users** — search/filter, change roles (author / moderator / admin), delete (can't delete yourself)
- **Settings** — site name, tagline, and social links stored in a key/value `settings` table

Roles & permissions use [spatie/laravel-permission](https://github.com/spatie/laravel-permission):
`author` (default), `moderator` (moderate comments), `admin` (manage taxonomy, users, settings).
Seed roles with `php artisan db:seed --class=RolePermissionSeeder`; the seeder creates an
`admin@contentforge.test` / `password` admin account.

## Data Model

### `posts`

| Column        | Type      | Notes                          |
|---------------|-----------|--------------------------------|
| `id`          | bigint    | PK                             |
| `user_id`     | bigint    | FK to `users` (author)         |
| `title`       | string    |                                |
| `slug`        | string    | unique, auto-generated         |
| `content`     | longtext  | markdown                       |
| `excerpt`     | text      | optional summary               |
| `status`      | enum      | `draft` \| `published`         |
| `published_at`| timestamp | nullable                       |
| `timestamps`  |           |                                |

### `categories`, `tags` (with `category_post` / `post_tag` pivots)

| Column        | Type      | Notes                          |
|---------------|-----------|--------------------------------|
| `id`          | bigint    | PK                             |
| `name`        | string    |                                |
| `slug`        | string    | unique, auto-generated         |

- A post belongs to **one** category (enforced in validation) and **many** tags
- Tags are created on the fly from the editor; existing tags are reused by slug

## Architecture

```
app/
├── Http/
│   └── Controllers/
│       ├── PostController.php        # public: index, show
│       ├── AuthorPostController.php  # auth: CRUD + publish/unpublish
│       ├── GenerateController.php    # auth: POST /posts/generate
│       ├── CommentController.php     # auth: store/destroy comments
│       └── Admin/                    # /admin panel (staff roles)
│           ├── DashboardController.php
│           ├── CategoryController.php
│           ├── TagController.php
│           ├── CommentController.php
│           ├── UserController.php
│           └── SettingController.php
└── Jobs/
    └── GeneratePostJob.php           # queued AI text generation
```

- AI generation is dispatched to the queue so the UI stays responsive
- AI calls run through the `AI` facade with a provider configured via env, so providers can be swapped without code changes
- Generation endpoints are validated and rate-limited

## AI Provider Configuration

The provider is configured in `.env`:

```
AI_PROVIDER=openai   # or anthropic, gemini, etc.
OPENAI_API_KEY=...
```

See `config/ai.php` after publishing the package config for all supported providers.

## Development

```bash
# Install dependencies
composer install
npm install

# Environment
cp .env.example .env
php artisan key:generate

# Database
php artisan migrate

# Run
npm run dev         # Vite
php artisan serve   # Laravel
php artisan queue:listen  # AI generation jobs
```

## Testing

```bash
php artisan test
```

Feature tests cover the public listing, author-only guards, the draft/publish workflow, and the generation endpoint (using the AI SDK's fakes so no live API calls are made in tests).

## Roadmap

- AI title, SEO meta, and tag suggestions
- Semantic/AI-powered search over published posts
- Streaming generation
- Featured images
