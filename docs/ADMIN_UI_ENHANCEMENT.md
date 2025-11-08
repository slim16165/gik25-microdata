# Admin UI Remix Idea

## Goals
- Document what the plugin does and how it fits in TotalDesign.
- Offer a single admin screen for shortcodes with toggles, descriptions, samples, and quick links.
- Add a usage tab that reports precisely which posts/pages embed each shortcode.
- Expand health/tools tabs with targeted checks and exports.

## Phase 1 – Overview + Shortcodes
1. Create a `ShortcodeRegistry` (e.g. `include/class/Shortcodes/Registry.php`) that maps slug ⇒ metadata (friendly title, description, example, default enabled, dependencies).
2. Register shortcodes via `PluginBootstrap` using that registry; metadata feeds the UI.
3. Build an admin tabbed menu (Overview + Shortcodes). The Overview tab shows summary, docs links, and quick actions; Shortcodes tab manages toggles, documentation, and a "Find usage" link.
4. Persist enabled state in an option (e.g. `gik25_shortcodes_enabled`) and re-register only selected shortcodes.

## Phase 2 – Usage Scanner
- Introduce a "Usage" tab that executes a simple `$wpdb->prepare` query like `post_content LIKE '%[slug%'` and lists posts with counts, post type, date, status.
- Allow filtering by shortcode, CPT, or date range.
- Add a button to "Rebuild usage index" that optionally stores results in a helper table (`wp_gik25_shortcode_index`).
- Provide a WP-CLI command (`wp gik25 shortcodes scan`) to regenerate the data from the command line.

## Phase 3 – Health & Tools
- Extend `include/class/HealthCheck/HealthChecker.php` with checks for required options, missing assets, inactive cron events, and conflicting plugins (Yoast/RankMath hooks).
- Tools tab exposes actions: export/import enabled shortcodes, clear shortcode index, rebuild CSS/JS caches.
- Provide a help tab with explanations and links to `docs/` articles.

## Notes
- Reuse existing helpers (`ListOfPostsHelper`) for UI consistency.
- Keep new tabs in sync with the documentation under `docs/` so help links always match the latest behavior.
