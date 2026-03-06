# ⚡ CorePulse (v1.0.0)

![Version](https://img.shields.io/badge/Version-1.0.0-00d2ff.svg)
![WordPress](https://img.shields.io/badge/WordPress-6.0+-0073aa.svg)
![PHP](https://img.shields.io/badge/PHP-7.4+-777bb4.svg)
![License](https://img.shields.io/badge/License-GPLv2-green.svg)

**CorePulse** is a server-centric WordPress performance engine engineered to surgically eliminate frontend bloat and optimize Core Web Vitals. 

Modern WordPress development has a hidden performance killer: **Monolithic Hydration and Structural Bloat**. As sites increasingly rely on heavy visual page builders and massive JavaScript frameworks, the processing burden is shifted entirely to the user's browser. CorePulse gives developers an "X-Ray" view of this bloat and provides the tools to intercept and neutralize it before it reaches the browser.

## The Core Engines

* **Asset Autopsy (HUD):** A sleek, dark-mode slide-out dashboard detailing the exact monolithic libraries loading on the current page. It tracks live TTFB (Time to First Byte), INP Delay, CLS (Cumulative Layout Shift), and LCP render-blocking issues.
* **Sniper Engine (Kill Switch):** Conditionally dequeue heavy scripts and stylesheets without writing a single line of PHP. Kill a script globally, kill it on a single page, or kill it everywhere *except* a specific page.
* **Boost Engine:** Accelerate your Critical Rendering Path. Click "BOOST" on any heavy hero image or primary font, and CorePulse dynamically injects `<link rel="preload">` tags to the very top of your document.
* **WCAG Guard:** A zero-dependency JavaScript engine that instantly audits the rendered DOM for structural accessibility failures (missing `alt` tags, unlabelled inputs). Clicking "TRACE" visually highlights the exact violating element on the page.

## Enterprise Architecture

* **100% Local Processing:** CorePulse does NOT "phone home" or use third-party APIs. All metrics are calculated entirely locally in the user's browser using native `PerformanceObserver` APIs.
* **Emergency Restore Safety Net:** Aggressively dequeuing JavaScript can break a page. CorePulse injects a lightweight `window.onerror` trap. If you kill a load-bearing script that crashes the layout, the system drops an "Emergency Restore" button to instantly revive scripts and reload.
* **Builder Isolation:** To protect your editing canvas, CorePulse automatically detects when you are actively designing in Elementor, Divi, Beaver Builder, Oxygen, Bricks, or Gutenberg, and silently hides its UI.

## Installation & Testing

1. Download the `v1.0.0` zip file from the [Releases page](../../releases/latest).
2. Upload and activate the plugin through your WordPress Admin Dashboard.
3. Navigate to **Settings > CorePulse** to adjust your performance warning/danger thresholds.
4. Visit the live frontend of your site (while logged in as an Administrator).
5. **Open the HUD** using one of three methods:
   * Click the floating green pulse button in the bottom-left corner.
   * Click the green "CorePulse: Active" node in the top Admin Bar.
   * Use the keyboard shortcut: `Ctrl + Shift + X`.

## Roadmap: The Future of the Engine

CorePulse is built for the long haul. Here is what is currently being engineered for the next major releases:

* **[v1.1.0] Headless Simulation Mode:** Preview the performance impact of dequeuing a script *before* applying the change globally.
* **[v1.1.0] Asset Dependency Mapping:** Automatically detect if a script you are about to "Snipe" is a dependency for another active script.
* **[v1.2.0] Historical Pulse Logs:** Store local snapshots of Web Vitals to see if your site performance is improving or degrading over time.
* **[v1.2.0] Auto-Preconnect Engine:** Intelligently detect 3rd-party domains (Google Fonts, Analytics) and inject `preconnect` hints automatically.
---
*Engineered by [Moses Ssebuwufu](https://github.com/ssebuwufumoses).*

## Frequently Asked Questions

**Q: Does CorePulse replace caching plugins like WP Rocket or LiteSpeed?** <br>
**A:** No. CorePulse is a *Performance Engine*, not a cache. While caching speeds up delivery, CorePulse reduces the actual work the browser has to do (Hydration/DOM size). They work perfectly together.

<br>

**Q: Will the HUD be visible to my website visitors?** <br>
**A:** No. The Asset Autopsy HUD is strictly protected. It only renders for users with `manage_options` capabilities (Administrators) who are logged in.
<br>
**Q: Can "Sniping" a script break my site?** <br>
**A:** Yes, if you dequeue a critical file. However, CorePulse includes an **Emergency Restore** feature. If a fatal JS error is detected after a change, a restore button will appear to let you instantly revert.

<br>

**Q: Is there any impact on server resources?** <br>
**A:** Minimal. CorePulse is server-centric but uses very lightweight PHP logic to intercept the enqueue queue. Most of the "heavy lifting" for metrics happens in the user's browser via native APIs.
