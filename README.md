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

---
*Engineered by [Moses Ssebuwufu](https://github.com/ssebuwufumoses).*
