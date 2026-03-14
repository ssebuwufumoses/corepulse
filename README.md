# ⚡ CorePulse (v1.2.0) - The Enterprise Performance Engine

![Version](https://img.shields.io/badge/Version-1.2.0-00d2ff.svg)
![WordPress](https://img.shields.io/badge/WordPress-6.0+-0073aa.svg)
![PHP](https://img.shields.io/badge/PHP-7.4+-777bb4.svg)
![License](https://img.shields.io/badge/License-GPLv2-green.svg)
![Architecture](https://img.shields.io/badge/Architecture-Server--Centric-a155ff.svg)

**CorePulse** is a highly technical, server-centric WordPress performance engine engineered for enterprise agencies, technical SEOs, and senior developers. It provides surgical tools to completely eliminate frontend bloat, map script dependency ASTs (Abstract Syntax Trees), intercept database bottlenecks, and optimize Google Core Web Vitals directly from the live frontend.

---

## Table of Contents
1. [Architectural Philosophy](#-architectural-philosophy)
2. [WordPress Hook Architecture](#-wordpress-hook-architecture)
3. [The Core Engines (Deep Technical Breakdown)](#-the-core-engines-deep-technical-breakdown)
4. [Enterprise Safety Mechanisms](#-enterprise-safety-mechanisms)
5. [WP-CLI Integration](#-wp-cli-integration)
6. [The Agency Workflow (Best Practices)](#-the-agency-workflow)
7. [Installation & Setup](#-installation--setup)
8. [Extensive Technical FAQ](#-extensive-technical-faq)

---

## Architectural Philosophy

Standard performance plugins (e.g., WP Rocket, LiteSpeed, Perfmatters) rely on client-side techniques like JavaScript minification, deferral, and execution delay. While useful, these methods do not reduce the *actual size* of the payload or the depth of the DOM. They merely postpone execution, which frequently leads to severe **Cumulative Layout Shifts (CLS)** or main-thread blocking once user interaction occurs.

**CorePulse intervenes at the PHP Compilation Phase.** Instead of masking the problem, CorePulse intercepts the WordPress asset pipeline at the server level. When you "Kill" a script using CorePulse, the asset is physically stripped from the final HTML document before it ever hits the user's network buffer. This results in permanent reductions in Time to First Byte (TTFB), payload byte size, and JavaScript parsing time.

---

## WordPress Hook Architecture

CorePulse is strictly optimized to have a **Zero-Visitor Footprint**. It executes early capability checks (`current_user_can('manage_options')`) and immediately returns if the user is unauthenticated.

When active for an Administrator, it utilizes the following hook architecture:
* `wp_enqueue_scripts` (Priority 9999): Executes the Sniper Engine to `wp_dequeue_script()` and `wp_deregister_style()` late in the queue to override stubborn themes.
* `wp_head` (Priority 1): Injects the Boost Engine's `<link rel="preload">` and `<link rel="preconnect">` resource hints at the absolute top of the document.
* `wp_footer` (Priority 9999): Injects the isolated React-style HUD and the non-blocking Heuristic DOM Scanners.
* `shutdown` (Priority 999): Intercepts the `$wpdb->queries` array to calculate Slow SQL metrics after the HTML document has been fully sent to the browser.
* `wp_ajax_*`: Handles secure, nonced endpoints for ruleset saving, historical logging, and transient purging.

---

## The Core Engines (Deep Technical Breakdown)

### 1. Asset Autopsy (Performance API HUD)
The HUD is an isolated, zero-dependency interface. It calculates Web Vitals entirely locally using the browser's native APIs, ensuring no third-party API latency.
* **TTFB & Payload Weight:** Captured directly from the browser's network buffer via `performance.getEntriesByType('navigation')` and `getEntriesByType('resource')`.
* **INP (Interaction to Next Paint):** Monitored using an active `PerformanceObserver` tracking the `event` entry type with a hard 16ms duration threshold.
* **DOM Depth:** Calculated heuristically via a raw `document.querySelectorAll('*').length` execution, actively warning the user if the node count exceeds Lighthouse's 1,500 critical threshold.

### 2. The Dependency Matrix (AST Mapper)
WordPress uses a rigid array-based dependency system that often causes "spaghetti enqueues" (e.g., `wp_enqueue_script('child', 'url', ['parent'])`). CorePulse prevents fatal site crashes by mapping this array structure in real-time.
* It recursively scans the `deps` array inside the global `$wp_scripts->registered` object.
* It renders a visual, terminal-style tree graph (`├──` and `└──`) showing the exact dependency chain. If you attempt to kill a foundational script (like `jquery.js` or `elementor-frontend.js`), the Matrix intercepts the action and alerts you to the exact child scripts that will fail.

### 3. Query Autopsy (Slow SQL Interceptor)
CorePulse taps into WordPress's native query tracking to expose database bottlenecks.
* At the `shutdown` hook, it iterates through `$wpdb->queries`, calculating the precise execution time of each query in milliseconds.
* It utilizes `debug_backtrace()` to parse the PHP call stack, traversing backwards until it identifies the exact plugin directory or theme file that invoked the heavy query. 
* Queries exceeding 10ms are flagged yellow; queries exceeding 50ms are flagged red.

### 4. Database Autopsy & Transient Purger
Heavy plugins frequently abuse the WordPress `wp_options` table by storing massive serialized arrays with `autoload = 'yes'`, forcing WordPress to load Megabytes of useless data into RAM on every page load.
* **Autoload Scanner:** Runs a direct, non-cached SQL query: `SELECT option_name, option_value FROM wp_options WHERE autoload = 'yes'`. It calculates physical byte size via `strlen()` and outputs the top 15 heaviest offenders.
* **1-Click Purger:** A highly secure AJAX endpoint that executes a direct `DELETE FROM wp_options WHERE option_name LIKE '_transient_%'` query, instantly wiping orphaned database rows.

### 5. Sniper Engine (Conditional Asset Dequeuing)
Optimization rules are stored as a lightweight JSON object in a single `wp_options` row (`corepulse_killed_scripts`). During page load, CorePulse evaluates the current context (`get_the_ID()`, `is_front_page()`, etc.) against the rule parameters (`everywhere`, `only`, `except`). 

### 6. Headless Simulation Mode (Dry Run)
Flipping the HUD's "SIM" toggle appends a `?cp_simulate=true` parameter to the URL. 
* The Sniper Engine bypasses the backend database save.
* Killed script handles are stored in `sessionStorage` and passed via the URL context. 
* The PHP backend reads the `$_GET` payload and temporarily dequeues the scripts *only for that specific browser session*. This allows aggressive structural testing on live sites with zero risk to public traffic.

### 7. Boost Engine & Auto-Preconnect
CorePulse accelerates the Critical Rendering Path by manipulating the speculative parser.
* **Boost:** Any asset marked for boosting is output as a `<link rel="preload" as="{type}" href="{url}">` tag, forcing the browser to download it concurrently with the HTML document.
* **Preconnect:** Scans the `$wp_scripts` queue for external hostnames (e.g., `fonts.googleapis.com`) and automatically outputs `<link rel="preconnect">` hints to eliminate DNS/TLS negotiation latency.

### 8. Structural & Heuristic Guards (WCAG / LCP)
A live DOM evaluation engine checks for structural compliance:
* **WCAG Accessibility:** Flags `img:not([alt])`, tab-traps, and unlabelled inputs. It evaluates `window.getComputedStyle()` to ensure it ignores inputs with `display: none` or `opacity: 0`, preventing false positives on visually hidden elements.
* **LCP Guard:** Initializes a `PerformanceObserver` for the `largest-contentful-paint` entry. If the emitted node is an `<img>` containing `loading="lazy"`, it throws a critical render-blocking warning.

---

## Enterprise Safety Mechanisms

* **Emergency Restore Safety Net:** Aggressively dequeuing JavaScript can cause cascading dependency failures. CorePulse injects a lightweight `window.onerror` event listener into the DOM. If a script removal causes a fatal JS exception, the Admin Bar flashes red and deploys an "Emergency Restore" button that immediately reverts the database state and reloads the page.
* **Builder-Agnostic Isolation:** CorePulse continuously checks request parameters (e.g., `$_GET['elementor-preview']`, `$_GET['et_fb']`) and Gutenberg contexts. If an active design session is detected, the HUD and all DOM scanners are hard-disabled to prevent canvas interference and inflated node counts.
* **Cloud Portability:** JSON Export/Import engine validates the structural integrity of imported Sniper rules before overwriting the database, ensuring seamless migrations from Staging to Production environments.

---

## WP-CLI Integration

CorePulse features full `WP_CLI_Command` support for server administrators requiring SSH automation:
* `wp corepulse status` - Prints current performance thresholds and outputs a table of all active Sniper rules.
* `wp corepulse reset-rules` - Instantly truncates the `corepulse_killed_scripts` option, restoring all assets to their factory default state.
* `wp corepulse clear-logs` - Wipes the historical Chart.js database arrays.
* `wp corepulse scan-db` - Runs the database autopsy via terminal to find autoload bloat without loading the frontend HUD.

---

## The Agency Workflow 

CorePulse is designed to fit perfectly into modern agency deployment pipelines:
1. **Analyze Staging:** Install CorePulse on the Staging server. Browse the site and use the HUD to identify plugin bloat and heavy CSS/JS payloads.
2. **Simulate & Snipe:** Turn on **SIM Mode**. Aggressively snipe unused scripts (e.g., WooCommerce cart fragments on blog posts). Verify no console errors occur.
3. **Commit Rules:** Turn off SIM Mode and commit the final kills to the database.
4. **Export JSON:** Click the Export icon in the HUD to download your `corepulse-sniper-rules.json` file.
5. **Deploy to Production:** Install CorePulse on the Live Production site. Click Import, upload the JSON file, and instantly clone your perfectly optimized server-side environment.

---

## Installation & Setup

1. **Download:** Grab the **v1.2.0** `corepulse.zip` file from the [Releases page](https://github.com/ssebuwufumoses/corepulse/releases).
2. **Install:** Upload and activate the plugin through your WordPress Admin Dashboard (**Plugins > Add New**).
3. **Enable Query Autopsy (CRITICAL):** To allow CorePulse to track slow database queries in real-time, you must enable WordPress memory tracking. Add this exact constant to your site's `wp-config.php` file (above the "stop editing" line):
   ```php
   define( 'SAVEQUERIES', true );
   ```
4. Navigate to **Settings > CorePulse** to define your custom byte-limit thresholds for Warning (Yellow) and Danger (Red) states.
5. Visit the live frontend of your site (authenticated as an Administrator).
6. Invoke the HUD via `Ctrl + Shift + X` or by clicking the floating trigger node.

---

## Extensive Technical FAQ

**Q: Does CorePulse replace caching plugins like WP Rocket or LiteSpeed?**<br>
**A:** No. Caching plugins are focused on the *delivery* of files (Minification, GZIP, Page Caching). CorePulse is focused on the *elimination* of files. Caching speeds up how fast a 2MB page gets to the user; CorePulse makes the page 500KB before it's even cached. They are highly complementary. 

**Q: Are the Chart.js ROI logs going to bloat my database?**<br>
**A:** No. The Historical Pulse Logs are stored as a serialized array inside a single `wp_options` row. The backend PHP logic uses `array_slice()` to aggressively truncate the array, retaining only the 50 most recent snapshots. Older logs are silently garbage-collected.

**Q: How does the Plugin Profiler calculate exact weight?**<br>
**A:** CorePulse maps the `$wp_scripts->src` URLs to the active WordPress plugin directories (using `plugin_dir_path`). It then aggregates the raw physical file sizes associated with those directories and outputs a leaderboard showing exact payload generation per plugin.

**Q: Can I use CorePulse to optimize WooCommerce?**<br>
**A:** Yes. WooCommerce notoriously loads cart fragment scripts (`wc-cart-fragments.js`) on static pages where they serve no purpose, severely impacting TTFB. Use the Sniper Engine to target these scripts and assign the rule: "Kill Everywhere EXCEPT" your shop and checkout post IDs.

**Q: Is the Chart.js library loaded via an external CDN?**<br>
**A:** No. To comply with strict enterprise security policies and WordPress Repository guidelines, `chart.min.js` is bundled locally within the plugin directory and is only enqueued on the specific CorePulse backend settings page.

**Q: How does the "Trace" function locate hidden elements?**<br>
**A:** When an accessibility or render-blocking violation is flagged, clicking "TRACE" triggers a `scrollIntoView({ behavior: 'smooth', block: 'center' })` JS event. It temporarily applies a high-z-index, pulsing CSS outline class to the specific DOM node, allowing developers to visually locate and fix the block inside their page builder.

**Q: Why doesn't the Slow SQL Radar show any queries?**<br>
**A:** To protect server RAM, WordPress disables complex query tracking in production environments. You must open your server's `wp-config.php` file and add: `define( 'SAVEQUERIES', true );` to enable the `$wpdb->queries` array.

**Q: What happens if I "Snipe" a critical framework like jQuery?**<br>
**A:** If you dequeue a foundational asset, dependent scripts will fail. However, CorePulse protects you twice: 
1. The **Dependency Matrix** visually warns you which child scripts rely on it before you click kill.
2. The **Emergency Restore Net** catches the resulting `window.onerror` JS exception and allows you to instantly revert the database change.

**Q: How is the "Autoloaded Bloat" calculated?**<br>
**A:** During a Database Scan, CorePulse runs `SELECT option_name, option_value FROM wp_options WHERE autoload = 'yes'`. It iterates through the results via PHP, using `strlen()` to calculate the exact byte size of every row. A healthy, optimized site should be under 800KB.

**Q: Is there a performance penalty for leaving the HUD active on production?**<br>
**A:** No. CorePulse utilizes a strict capability check (`current_user_can('manage_options')`) at the very top of its execution tree. If a standard user or an unauthenticated visitor accesses the site, the plugin immediately returns early. It executes 0 bytes of frontend code, 0 heuristic DOM scanners, and 0 database metrics for your end users.