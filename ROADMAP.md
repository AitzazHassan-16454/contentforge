# ContentForge — 3-Week Roadmap

Built on what already exists: AI generation + streaming, SEO suggestions, semantic
search, comments, reactions, scheduled publishing, cover images, view counts,
author pages, and dark mode.

## Week 1 — Marketing site & product foundation

### 1. Professional long marketing homepage at `/`
- Move current blog listing to `/blog` (keep categories, tags, search).
- New homepage with animated, long-scrolling sections:
  - Hero (headline, subcopy, dual CTA, product mockup)
  - Trust bar / stats (posts, authors, AI generations)
  - "How it works" (3 steps: prompt → refine → publish)
  - Features grid (AI generation, SEO assistant, editor, scheduling, analytics)
  - Live preview — latest published posts embedded
  - Pricing teaser
  - Testimonials
  - FAQ (accordion)
  - Final CTA + updated footer with real links

### 2. About, Contact, Privacy & Terms pages
- `/about`, `/contact` (functional form storing to DB), `/privacy`, `/terms`.

### 3. Pricing & plans
- `subscriptions`/plan model on users (free / pro).
- Usage limits: monthly AI generations + published post caps per plan.
- `/pricing` page and `/dashboard/billing` with a simulated upgrade flow
  (Stripe ready later).

### 4. Admin panel `/admin`
- Roles: `admin` / `moderator` on users.
- Manage categories & tags (CRUD).
- Comment moderation (approve / delete).
- User management and site settings (site name, tagline, social links).

## Week 2 — Media, analytics & engagement

### 5. Media & images
- Cover image upload in editor (drag & drop) + alt text (field already exists).
- Media library (upload, grid, insert image into markdown).
- Auto Open Graph image generation for posts.

### 6. Analytics dashboard
- Daily `post_views` table for reads-over-time charts.
- Author analytics: views/reads chart, top posts, comment & reaction totals.
- Admin analytics: users, posts, AI generations, top content.

### 7. Engagement upgrades
- Nested comment replies + comment likes.
- Bookmarks / reading list (`user_bookmarks`).
- Follow authors + "Following" feed.
- Social share buttons (X, LinkedIn, Facebook, copy-link) on posts.

### 8. SEO & sharing
- Per-post meta tags (title, description, canonical, OG, Twitter).
- `sitemap.xml` and RSS feed routes.
- JSON-LD `Article` structured data.

## Week 3 — Polish & launch

### 9. Email & notifications
- Enable email verification; notify commenters/replies; email me when someone
  comments on my post (configurable).

### 10. Demo content seeder
- Categories, tags, 12–15 realistic sample posts so the site looks finished.

### 11. Performance & quality
- Query optimization + caching, lazy images, DB indexes.
- Feature tests for every new area; run full suite.
- Production `.env.example`, updated `README`/`PROJECT.md`, deployment notes.

## Notes
- Email/SMTP can be stubbed or use Mailpit locally; production keys later.
- Payments use a simulated checkout (no real card processing) unless you add Stripe keys.
