=== CorePulse ===
Contributors: ssebuwufumoses
Tags: performance, speed, optimization, database cleaner, asset manager
Requires at least: 6.2
Tested up to: 6.9
Stable tag: 1.2.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Developer performance engine: dequeue JS/CSS, map asset dependencies, track slow SQL queries, audit DOM depth, and purge database bloat.

== Description ==

Modern WordPress development has a hidden performance killer: **Monolithic Hydration and Structural Bloat**. 

As sites increasingly rely on heavy visual page builders (Elementor, Divi, Bricks) and massive JavaScript frameworks to render frontend UI, the processing burden is shifted entirely to the user's browser. This results in massive bundle sizes, DOM trees thousands of nodes deep ("div soup"), bloated `wp_options` tables, and failing Google Core Web Vitals (CWV).

**CorePulse** is a highly technical, server-centric performance engine built specifically for developers and agency owners. Instead of relying on client-side JS deferral tricks (which often cause cumulative layout shifts), CorePulse intercepts the WordPress asset pipeline at the server level, allowing you to surgically eliminate bloat before it is ever sent over the network.

### Architectural Philosophy: Server-Centric Optimization
Most performance plugins attempt to fix bloat *after* it reaches the browser by using JavaScript to delay script execution. CorePulse intervenes during the PHP compilation phase. By hooking late into the `wp_enqueue_scripts` and `wp_print_scripts` actions, CorePulse manipulates the `$wp_scripts` and `$wp_styles` globals directly. If you kill a script, it is physically stripped from the HTML response, resulting in an unarguable reduction in Time to First Byte (TTFB) and network payload size.

---

### The Core Engines (Technical Deep Dive)

#### 1. The Asset Autopsy (HUD)
A sleek, isolated React-style interface injected via the `wp_footer` hook. It does not rely on third-party APIs. Instead, it utilizes the browser's native `PerformanceObserver` API to track Web Vitals locally.
* **TTFB & Payload:** Captured via `performance.getEntriesByType('navigation')` and `('resource')`.
* **INP (Interaction to Next Paint):** Monitored using `PerformanceObserver` listening for `event` types with a duration threshold of 16ms.
* **CLS (Cumulative Layout Shift):** Continuously calculated via `layout-shift` entries, ignoring those with `hadRecentInput`.

#### 2. The Dependency Matrix
WordPress scripts are notorious for "spaghetti" dependencies (e.g., `wp_enqueue_script('child', 'url', array('parent'))`). CorePulse recursively scans the `deps` array inside the `$wp_scripts->registered` object. It then outputs a visual, terminal-style hierarchy tree (`├──` and `└──`) in the HUD, showing exactly which foundational scripts (like `jquery.js` or `elementor-frontend.js`) are triggering child assets. This prevents fatal crashes caused by blind dequeuing.

#### 3. Query Autopsy (Slow SQL Radar)
When `SAVEQUERIES` is defined in `wp-config.php`, WordPress stores every database query in the `$wpdb->queries` array. CorePulse intercepts this array at shutdown, calculates the execution time of every query, and uses `debug_backtrace` logic to isolate the exact PHP function or plugin that triggered it. Any query taking longer than 10ms is flagged yellow; over 50ms is flagged red. 

#### 4. Database Autopsy & Transient Purger
Heavy plugins often abuse the `wp_options` table by setting `autoload = 'yes'` on massive data strings, destroying TTFB. 
* **Autoload Scanner:** CorePulse runs a direct `SELECT option_name, option_value` query, calculates the byte length of every autoloaded string, and lists the top 15 heaviest offenders.
* **1-Click Purger:** Executes a highly secure, nonced `DELETE FROM wp_options WHERE option_name LIKE '_transient_%'` query via AJAX to instantly sweep expired transients without requiring a page reload.

#### 5. The Sniper Engine (Kill Switch)
A precision asset manager. Rules are saved as JSON in a single `corepulse_killed_scripts` option. During page load, CorePulse checks the current Post ID against this rule set. If a match is found (e.g., "Kill Everywhere EXCEPT Post ID 42"), it executes `wp_dequeue_script()`, `wp_deregister_script()`, `wp_dequeue_style()`, and `wp_deregister_style()` late in the priority queue (Priority 9999) to override stubborn theme assets.

#### 6. Headless Simulation Mode (Dry Run)
Flipping the "SIM" switch appends a `?cp_simulate=true` parameter to the URL. In this mode, the Sniper Engine intercepts your "Kill" commands *before* they are sent to the database AJAX handler, storing them temporarily in URL parameters and `sessionStorage`. This allows you to aggressively strip core scripts and verify if the page breaks visually, safely isolated within your own browser session.

#### 7. Plugin Profiler
Maps the registered URL of every active script/stylesheet to the active plugin directories (using `plugin_dir_path`). It aggregates the file sizes and outputs a leaderboard showing exactly how many kilobytes each individual plugin is injecting into the current DOM.

#### 8. The Boost & Auto-Preconnect Engines
Clicking "BOOST" on an asset stores its URL in the `corepulse_preloaded_assets` database option. CorePulse hooks into `wp_head` (Priority 1) to aggressively output `<link rel="preload" as="..." href="...">` tags for these items, forcing the browser's speculative parser to prioritize them. It simultaneously scans `$wp_scripts` for external hostnames (fonts.googleapis.com, use.typekit.net) and automatically outputs `<link rel="preconnect">` hints.

#### 9. The WCAG & Structural Guards
A zero-dependency JavaScript heuristic engine that parses the live DOM. 
* **Accessibility:** Flags `img:not([alt])`, positive `tabindex` traps, empty interactive elements (`<a>`, `<button>`), and unlabelled inputs by checking computed styles to ensure they aren't hidden from screen readers. 
* **LCP Guard:** Identifies the Largest Contentful Paint node via `PerformanceObserver`. If the node is an `<img>` containing `loading="lazy"`, it throws a critical render-block warning. Clicking "TRACE" executes `scrollIntoView()` and applies a pulsing CSS outline to the offending element.

---

### Enterprise Tools & Integration

* **ROI Dashboard (Chart.js):** Fires a lightweight, non-blocking AJAX beacon 5 seconds after page load (`keepalive: true`) to log your final Web Vitals. This data is rendered in the backend via a locally hosted `chart.min.js` library, mapping TTFB and Payload weight over time.
* **WP-CLI Integration:** Built on `WP_CLI_Command`. Provides direct SSH access to your optimization rules. Commands include `wp corepulse status`, `wp corepulse clear-logs`, and `wp corepulse reset-rules`.
* **JSON Cloud Portability:** Easily port rules between staging and production. The import engine validates the JSON structure before overwriting the `corepulse_killed_scripts` option, ensuring no corrupted data breaks the site.
* **Builder-Agnostic Isolation:** Checks against `$_GET['elementor-preview']`, `$_GET['et_fb']`, `is_customize_preview()`, and Gutenberg context to completely disable the HUD during active design sessions, preventing DOM-count pollution and canvas interference.

== Installation ==

1. Upload the `corepulse.zip` file via your WordPress Admin (Plugins > Add New).
2. Activate the plugin.
3. Navigate to **Settings > CorePulse** to define your payload byte-limit thresholds.
4. Visit the frontend (logged in as an Administrator) and use `Ctrl+Shift+X` or the green floating Admin Bar node to open the HUD.

== Frequently Asked Questions ==

= Does CorePulse replace WP Rocket, LiteSpeed, or other caching plugins? =
Absolutely not. CorePulse is a *Performance Interception Engine*, not a page cache. Caching plugins speed up the *delivery* of the HTML document. CorePulse reduces the *actual complexity* of the HTML document (smaller DOM, fewer scripts, faster JS parsing). They are designed to be run concurrently. Use CorePulse to strip the bloat, and WP Rocket to cache the resulting lightweight page.

= Why doesn't the Slow SQL Radar show any queries? =
To protect server RAM, WordPress disables complex query tracking in production environments. To activate CorePulse's database tracking, you must open your server's `wp-config.php` file and add:
`define( 'SAVEQUERIES', true );`
Once added, the HUD will instantly begin displaying execution times and PHP callers.

= What happens if I "Snipe" a critical framework like jQuery? =
If you dequeue a foundational asset, any dependent scripts will fail, resulting in a broken layout. However, CorePulse protects you in two ways:
1. **The Dependency Warning:** Before you kill a script, the HUD will check the `$wp_scripts->registered` object. If the script has dependents, a red modal warns you exactly which child scripts will break.
2. **Emergency Restore Net:** CorePulse injects a `window.onerror` event listener. If a fatal JS exception occurs due to a missing script, the Admin Bar flashes red and drops a 1-click "Emergency Restore" button to instantly rewrite the database and reload the page.

= How exactly does the "Dry Run" Simulation work? =
When SIM mode is active, CorePulse bypasses the standard AJAX database save. Instead, it pushes the "killed" script handles into a comma-separated `?cp_target=` URL parameter and saves the state to your browser's `sessionStorage`. The PHP backend reads this `$_GET` variable during `wp_enqueue_scripts` and temporarily dequeues them just for your session. Your live users remain completely unaffected.

= How does the WCAG tracer find invisible elements? =
The WCAG Guard uses `window.getComputedStyle(input)` to calculate the offset width, height, visibility, and display state of elements. It purposefully ignores inputs that have `display: none`, `opacity: 0`, or `aria-hidden="true"`, ensuring it only flags accessibility violations that are actually interacting with the visual accessibility tree.

= How is the "Autoloaded Bloat" calculated? =
During a Database Scan, CorePulse runs a direct SQL query: `SELECT option_name, option_value FROM wp_options WHERE autoload = 'yes'`. It iterates through the results in PHP, using `strlen()` to calculate the exact byte size of every row, summing them up to give you your total bloat metric. A healthy site should be under 800KB.

= Is there a performance penalty for leaving the HUD active? =
No. CorePulse utilizes a strict capability check (`current_user_can('manage_options')`) at the very top of its execution hooks. If a standard user or an unauthenticated visitor accesses the site, the plugin immediately returns early. It executes 0 bytes of frontend code, 0 heuristic DOM scanners, and 0 database metrics for your end users.

= How do I use the WP-CLI commands? =
Open your server terminal (SSH). Navigate to your `public_html` or WordPress root directory.
* `wp corepulse status` - Prints your current warning/danger thresholds and a list of all active Sniper rules.
* `wp corepulse reset-rules` - Instantly truncates the `corepulse_killed_scripts` option, restoring all dequeued scripts to their factory defaults.
* `wp corepulse clear-logs` - Wipes the historical Chart.js database logs.

= Are the Chart.js ROI logs going to bloat my database? =
No. The Historical Pulse Logs are stored in a single, serialized array within `wp_options`. The backend logic enforces a strict array slice, automatically truncating the logs to keep only the 50 most recent snapshots. Older data is silently garbage-collected to prevent database bloat.

== Changelog ==

= 1.2.0 =
* **Engine Update:** The Dependency Matrix - Engineered a recursive PHP mapping function to output a visual, terminal-style dependency tree (`├──` and `└──`) to prevent fatal dequeue errors.
* **Engine Update:** Query Autopsy - Frontend Slow SQL Radar tracking database execution times and isolating PHP `debug_backtrace` callers.
* **Engine Update:** Database Autopsy - Direct `$wpdb` scanner calculating `wp_options` autoload bloat and identifying the top 15 heaviest rows.
* **Feature:** 1-Click Transient Purger - Secure AJAX endpoint to instantly execute a `DELETE FROM wp_options WHERE option_name LIKE '_transient_%'` query.
* **Feature:** ROI Dashboard - Implemented `chart.min.js` locally (CDN-free) for dual-axis historical performance tracking in the WP backend.
* **Feature:** WP-CLI Integration - Full `WP_CLI_Command` enterprise terminal support.
* **Feature:** Plugin Profiler - Maps `$wp_scripts->src` to plugin directories to calculate exact payload weight per plugin.
* **Feature:** Cloud Portability - JSON Export/Import functionality for transferring `corepulse_killed_scripts` arrays across environments.
* **Feature:** 1-Click Autopsy Reports - JavaScript Clipboard API integration for pasting stats into Slack/Jira.
* **Feature:** Heuristic Guards - Added Privacy Guard (external font tracking), Heavy Media Radar (byte-limit tracking), and Dead Asset 404 Radar.
* **Feature:** LCP Lazy-Load Detector - `PerformanceObserver` logic to protect hero image rendering.

= 1.1.0 =
* **Feature:** Headless Simulation Mode (`cp_simulate` URL parameter routing).
* **Feature:** Auto-Preconnect Engine for DNS resource hints.
* **Feature:** Active Budget Alerts (Dynamic CSS classes based on byte thresholds).

= 1.0.0 =
* Initial Enterprise Release. Engineered the Asset Autopsy HUD, Sniper Engine (`wp_dequeue_script` routing), Boost Engine (`wp_head` preloads), and WCAG Tracer.

== Upgrade Notice ==

= 1.2.0 =
The Ultimate Enterprise Update. Introducing the Dependency Matrix, Query Autopsy, WP-CLI support, a Database Transient Purger, and a Chart.js ROI Dashboard. Update immediately to unlock deep database and server insights.