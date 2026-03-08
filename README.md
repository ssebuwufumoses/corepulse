# ⚡ CorePulse (v1.1.0)

![Version](https://img.shields.io/badge/Version-1.0.0-00d2ff.svg)
![WordPress](https://img.shields.io/badge/WordPress-6.0+-0073aa.svg)
![PHP](https://img.shields.io/badge/PHP-7.4+-777bb4.svg)
![License](https://img.shields.io/badge/License-GPLv2-green.svg)

# CorePulse
**CorePulse** is a server-centric WordPress performance engine engineered to surgically eliminate frontend bloat and optimize Core Web Vitals.

Modern WordPress development has a hidden performance killer: **Monolithic Hydration and Structural Bloat**. As sites increasingly rely on heavy visual page builders and massive JavaScript frameworks, the processing burden is shifted entirely to the user's browser. CorePulse gives developers an "X-Ray" view of this bloat and provides the tools to intercept and neutralize it before it reaches the browser.

## The Core Engines

* **Asset Autopsy (HUD):** A sleek, dark-mode slide-out dashboard detailing the exact monolithic libraries loading on the current page. It tracks live TTFB (Time to First Byte), INP Delay, CLS (Cumulative Layout Shift), and LCP render-blocking issues.
* **Sniper Engine (Kill Switch):** Conditionally dequeue heavy scripts and stylesheets without writing a single line of PHP. Kill a script globally, kill it on a single page, or kill it everywhere *except* a specific page.
* **Headless Simulation Mode:** Preview the performance impact of dequeuing a script safely *before* applying the change globally to the database.
* **Asset Dependency Mapping:** Automatically detects if a script you are about to "Snipe" is a foundational dependency for another active script, preventing accidental crashes.
* **Boost Engine:** Accelerate your Critical Rendering Path. Click "BOOST" on any heavy hero image or primary font, and CorePulse dynamically injects `<link rel="preload">` tags to the very top of your document.
* **Auto-Preconnect Engine:** Intelligently detects 3rd-party domains (Google Fonts, Analytics) and injects `preconnect` and `dns-prefetch` hints automatically.
* **Historical Pulse Logs:** Stores local snapshots of your Web Vitals and payloads in a visual dashboard so you can see if your site performance is improving or degrading over time.
* **WCAG Guard:** A zero-dependency JavaScript engine that instantly audits the rendered DOM for structural accessibility failures (missing `alt` tags, unlabelled inputs). Clicking "TRACE" visually highlights the exact violating element on the page.

## Enterprise Architecture

* **100% Local Processing:** CorePulse does NOT "phone home" or use third-party APIs. All metrics are calculated entirely locally in the user's browser using native `PerformanceObserver` APIs.
* **Emergency Restore Safety Net:** Aggressively dequeuing JavaScript can break a page. CorePulse injects a lightweight `window.onerror` trap. If you kill a load-bearing script that crashes the layout, the system drops an "Emergency Restore" button to instantly revive scripts and reload.
* **Active Budget Alerts:** Set strict payload thresholds in the backend. The HUD trigger will actively pulse yellow or red the moment a page violates your performance budget.
* **Builder Isolation:** To protect your editing canvas, CorePulse automatically detects when you are actively designing in Elementor, Divi, Beaver Builder, Oxygen, Bricks, or Gutenberg, and silently hides its UI.

## Installation & Testing

1. Download the **v1.1.0** zip file from the [Releases page](#).
2. Upload and activate the plugin through your WordPress Admin Dashboard.
3. Navigate to **Settings > CorePulse** to adjust your performance warning/danger thresholds.
4. Visit the live frontend of your site (while logged in as an Administrator).
5. Open the HUD using one of three methods:
   * Click the floating pulse button in the bottom-left corner.
   * Click the green "CorePulse: Active" node in the top Admin Bar.
   * Use the keyboard shortcut: `Ctrl + Shift + X`.
6. **Check your Logs:** Wait at least 5 seconds on any frontend page, then navigate back to **Settings > CorePulse** to view your automatically generated Historical Pulse Logs!

## Frequently Asked Questions

**Q: Why don't I see the CorePulse HUD when I am in Elementor or Gutenberg?**<br>
**A:** This is an intentional feature called **Builder Isolation**. CorePulse detects when you are actively designing a page and completely disables its visual UI so it doesn't interfere with your builder's canvas or skew the DOM data. It will only appear on the live, public-facing frontend.

**Q: What is the difference between Kill and Boost?**<br>
**A:** **KILL** intercepts a script before it ever reaches the browser, completely removing it from the page to save payload size. **BOOST** injects a `<link rel="preload">` tag at the top of the HTML document, forcing the browser to download a critical asset (like a Hero Image or a Font) in the background before the page even finishes rendering.

**Q: Why is my CorePulse icon Red?**<br>
**A:** A red pulse means your page's payload has exceeded your configured "Danger" threshold. Open the Asset Autopsy to see exactly which local or external scripts are causing the bloat.

**Q: The pulse is flashing violently red and a "Fatal JS Error" box appeared. What happened?**<br>
**A:** You triggered the **Emergency Restore Safety Net**. This means a script you recently sent to "The Graveyard" was load-bearing, and its removal caused the browser to throw a fatal JavaScript exception. Click the red "Emergency Restore All" button in the HUD to instantly fix your site.

**Q: My DOM Nodes number is red. What does that mean?**<br>
**A:** Google Lighthouse recommends keeping your total HTML elements (DOM nodes) under 800. Page builders are notorious for nesting dozens of empty containers inside each other to achieve simple layouts. CorePulse turns red when your builder has pushed the page structure past 1,500 nodes (a critical Lighthouse failure).

**Q: How does the WCAG Tracer work?**<br>
**A:** When CorePulse detects an accessibility violation (like an image missing an alt tag), it lists it in the HUD. Clicking "Trace" will smoothly scroll your browser directly to the offending element and wrap it in a pulsing red border for 20 seconds so you can identify exactly which block needs fixing in your page builder.

**Q: Does CorePulse replace caching plugins like WP Rocket or LiteSpeed?**<br>
**A:** No. CorePulse is a *Performance Engine*, not a cache. While caching speeds up delivery, CorePulse reduces the actual work the browser has to do (Hydration/DOM size). They work perfectly together.

**Q: Will the HUD or plugin slow down my site for visitors?**<br>
**A:** Absolutely not. The Asset Autopsy HUD is strictly protected. It only renders for users with `manage_options` capabilities (Administrators) who are logged in. If a standard user visits your site, CorePulse remains completely dormant.

**Q: Can "Sniping" a script break my site?**<br>
**A:** Yes, if you dequeue a critical file. However, CorePulse includes an **Emergency Restore** feature. If a fatal JS error is detected after a change, a restore button will appear to let you instantly revert.

**Q: Is there any impact on server resources?**<br>
**A:** Minimal. CorePulse is server-centric but uses very lightweight PHP logic to intercept the enqueue queue. Most of the "heavy lifting" for metrics happens in the user's browser via native APIs.