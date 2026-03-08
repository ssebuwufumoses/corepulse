=== CorePulse ===
Contributors: ssebuwufumoses
Tags: performance, core web vitals, speed, accessibility, optimization
Requires at least: 5.8
Tested up to: 6.9
Stable tag: 1.1.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Surgically optimize performance. Monitor JS payloads, kill heavy scripts, boost LCP assets, and audit DOM depth and WCAG health in real-time.

== Description ==

Modern WordPress development has a hidden performance killer: **Monolithic Hydration and Structural Bloat**. 

As sites increasingly rely on heavy visual page builders and massive JavaScript frameworks to render frontend UI, the processing burden is shifted entirely to the user's browser. This results in massive bundle sizes, DOM trees thousands of nodes deep ("div soup"), and failing Core Web Vitals.

**CorePulse** is a lightweight, server-centric performance monitor built specifically to help developers and agency owners catch frontend bloat before it ruins the user experience. It actively measures your site's JavaScript weight, CSS payloads, DOM depth, and structural accessibility in real-time, giving you the surgical tools to eliminate unnecessary scripts and boost critical assets safely.

### Architectural Philosophy
CorePulse is built on a **Server-Centric Performance Model**. It intervenes at the server level by intercepting the WordPress `$wp_scripts` and `$wp_styles` globals. This allows you to surgically dequeue heavy assets *before* they are sent over the network, while dynamically injecting preloads for critical rendering paths.

### The Core Engines

* **The Asset Autopsy (HUD):** A sleek, dark-mode slide-out dashboard detailing the exact monolithic libraries loading on the current page. It combines data from PHP and the browser's Performance API to track live TTFB (Time to First Byte), INP Delay (Interaction to Next Paint), and CLS (Cumulative Layout Shift).
* **The Sniper Engine (Kill Switch):** Conditionally dequeue scripts and stylesheets without writing a single line of PHP. Kill a script globally, kill it on a single page, or kill it everywhere *except* a specific page.
* **Headless Simulation Mode (Dry Run):** Safely preview the performance impact of unloading a script before applying the change globally to the live frontend.
* **Asset Dependency Mapping:** Actively maps the WordPress dependency tree and warns you with a "Foundational Asset" tag before you kill scripts that other active assets rely on.
* **Auto-Preconnect Engine:** Automatically detects 3rd-party domains (Google Fonts, Analytics) queued on the page and intelligently injects `<link rel="preconnect">` and `<link rel="dns-prefetch">` hints to accelerate DNS resolution.
* **Historical Pulse Logs:** A local, auto-cleaning database that captures daily snapshots of your Web Vitals (TTFB, INP, CLS) and payload weights so you can track performance trends over time.
* **The Boost Engine:** Accelerate your Critical Rendering Path. Click "BOOST" on any heavy hero image, critical CSS, or primary font, and CorePulse will dynamically inject `<link rel="preload">` tags to the very top of your document.
* **The DOM Depth Scanner:** Excessive DOM nodes crush mobile CPU performance. This heuristic scanner continuously counts every single HTML node rendered on the page, warning you when page builders generate excess "div soup".
* **The WCAG Accessibility Guard:** A zero-dependency JavaScript engine that instantly audits the rendered DOM for structural accessibility failures (missing `alt` tags, unlabelled inputs). Clicking "TRACE" will scroll the page to the exact violating element and highlight it.

### Enterprise Safety Features

* **Active Budget Alerts:** The HUD trigger node actively pulses yellow (Warning) or red (Danger) the moment your page payload crosses your defined server-centric thresholds.
* **Emergency Restore Safety Net:** Aggressively dequeuing JavaScript can sometimes break a page. CorePulse injects a lightweight `window.onerror` trap. If you forcefully dequeue a load-bearing script that completely crashes the page layout, the Admin Bar will violently flash red and drop an "Emergency Restore" button into your UI to instantly revive all killed scripts and reload the page.
* **Builder-Agnostic Isolation:** To prevent the HUD from ruining your editing canvas or reporting artificially inflated DOM sizes, CorePulse automatically detects when you are actively designing in Elementor, Divi, Beaver Builder, Oxygen, Bricks, or Gutenberg. It silently hides its UI to protect your workflow.

### Configuration & Settings
Navigate to **Settings > CorePulse** to adjust your server-centric performance budgets:
* **JS Warning / Danger Thresholds:** Control when the Admin Bar turns Yellow or Red.
* **CSS Danger Threshold:** Set the size limit for cascading stylesheets.
* **DOM Danger Threshold:** The hard limit for HTML elements before flagging a Lighthouse failure (Default: 1500).
* **Heavy Media Limit:** Any image, font, or video exceeding this size will trigger the Heavy Media Radar in the HUD.

== Installation ==

### Standard Installation
1. Navigate to your WordPress Admin Dashboard.
2. Go to **Plugins > Add New** and click **Upload Plugin**.
3. Select the `corepulse.zip` file and click **Install Now**.
4. Click **Activate Plugin**.

### Post-Installation Setup
1. Navigate to **Settings > CorePulse** in your WordPress dashboard.
2. Configure your specific "Warning" (Yellow) and "Danger" (Red) payload thresholds based on your project's performance budget.
3. Visit the live frontend of your site.
4. Click the green **CorePulse: Active** button in your Admin Bar (or use `Ctrl+Shift+X`) to slide open the Asset Autopsy HUD.

== Frequently Asked Questions ==

= Why don't I see the CorePulse HUD when I am in Elementor or Gutenberg? =
This is an intentional feature called **Builder Isolation**. CorePulse detects when you are actively designing a page and completely disables its visual UI so it doesn't interfere with your builder's canvas or skew the DOM data. It will only appear on the live, public-facing frontend.

= What is the difference between Kill and Boost? =
**KILL** intercepts a script before it ever reaches the browser, completely removing it from the page to save payload size. 
**BOOST** injects a `<link rel="preload">` tag at the top of the HTML document, forcing the browser to download a critical asset (like a Hero Image or a Font) in the background before the page even finishes rendering. We recommend only boosting 2-4 critical assets per page.

= Why is my CorePulse icon Red? =
A red pulse means your page's payload has exceeded your configured "Danger" threshold. Open the Asset Autopsy to see exactly which local or external scripts are causing the bloat.

= The pulse is flashing violently red and a "Fatal JS Error" box appeared. What happened? =
You triggered the **Emergency Restore Safety Net**. This means a script you recently sent to "The Graveyard" was load-bearing, and its removal caused the browser to throw a fatal JavaScript exception. Click the red "Emergency Restore All" button in the HUD to instantly fix your site.

= My DOM Nodes number is red. What does that mean? =
Google Lighthouse recommends keeping your total HTML elements (DOM nodes) under 800. Page builders are notorious for nesting dozens of empty containers inside each other to achieve simple layouts. CorePulse turns red when your builder has pushed the page structure past 1,500 nodes (a critical Lighthouse failure).

= How does the WCAG Tracer work? =
When CorePulse detects an accessibility violation (like an image missing an alt tag), it lists it in the HUD. Clicking "Trace" will smoothly scroll your browser directly to the offending element and wrap it in a pulsing red border for 20 seconds so you can identify exactly which block needs fixing in your page builder.

= Will this plugin slow down my site for visitors? =
Absolutely not. CorePulse explicitly checks for Administrator privileges before loading any of its heuristic scanners or monitoring tools. If a standard user visits your site, CorePulse remains completely dormant. The only thing visitors experience are the incredibly fast load times resulting from your optimizations.

== Changelog ==

= 1.1.0 =
* **New:** Headless Simulation Mode - Test script unloading safely without breaking the live frontend.
* **New:** Asset Dependency Mapping - Intelligent warnings alert you before killing foundational scripts.
* **New:** Auto-Preconnect Engine - Automatically injects DNS resource hints for 3rd-party domains.
* **New:** Historical Pulse Logs - Local database logging Web Vitals (TTFB, INP, CLS) to track performance over time.
* **New:** Active Budget Alerts - The floating trigger now pulses yellow/red when page weight exceeds defined thresholds.
* **Fix:** Resolved an issue where custom performance thresholds in the settings page would not save properly due to un-whitelisted API options.
* **Fix:** Resolved a JavaScript race condition syncing simulated vs. real graveyard states.
* **Tweak:** Engineered fully WCAG-compliant focus states for all HUD interactive elements.
* **Security:** Hardened dashboard database outputs to strict WordPress VIP escaping standards.

= 1.0.0 =
* Initial Enterprise Release: Welcome to CorePulse!
* Engineered the Asset Autopsy HUD with real-time DOM & payload scanning.
* Engineered the 'Sniper Engine' (Kill switch) for surgical asset management.
* Engineered the 'Boost Engine' for 1-click critical asset preloading.
* Implemented strict Builder Isolation (Elementor, Divi, Gutenberg, Bricks, Oxygen).
* Integrated WCAG violation tracer and LCP render-block warnings.
* Added real-time tracking for TTFB, INP Delay, and CLS scores.
* Added the DOM Depth Scanner to track HTML structure size against Lighthouse standards.
* Added the Emergency Restore Safety Net (window.onerror trap) to protect developers from fatal JS crashes.

== Upgrade Notice ==

= 1.1.0 =
This is a massive engine upgrade. CorePulse v1.1.0 introduces Headless Simulation Mode, Asset Dependency Mapping, Auto-Preconnect logic, and Historical Performance Logging. Update now to unlock active performance monitoring.

= 1.0.0 =
Welcome to CorePulse Version 1.0! Shift the load back to the server and take control of your Core Web Vitals today.