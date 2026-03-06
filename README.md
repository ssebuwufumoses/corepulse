# ⚡ CorePulse Engine

![WordPress](https://img.shields.io/badge/WordPress-6.0+-blue.svg)
![PHP](https://img.shields.io/badge/PHP-7.4+-777bb4.svg)
![License](https://img.shields.io/badge/License-GPLv2-green.svg)

**Enterprise-grade WordPress performance engine for real-time asset monitoring, conditional script loading, and hydration optimization.**

CorePulse is a server-centric hydration and performance monitoring engine designed to shift the load back to the server and give you total control over your Core Web Vitals.

## Key Features

* **Asset Autopsy HUD:** Real-time Web Vitals and payload tracking directly in your WordPress admin bar.
* **Sniper Engine:** Conditionally kill heavy scripts and styles globally or on a per-page basis.
* **Boost Engine:** 1-click preload injection for Largest Contentful Paint (LCP) assets.
* **WCAG Guard:** Real-time heuristic accessibility tracing to ensure compliance.

## Tech Stack & Architecture
CorePulse is built with a focus on modern WordPress development standards:
* **PHP 8.x Optimized** (Backwards compatible to 7.4)
* **Vanilla JS** (Zero jQuery dependency for the frontend HUD)
* **WordPress Native Hooks** (Seamless integration without overriding core behavior)

## Installation (Developer Preview)
1. Download the latest `corepulse.zip` from the [Releases](../../releases) page.
2. Upload to your WordPress dashboard via **Plugins > Add New > Upload Plugin**.
3. Activate and navigate to the CorePulse settings to start optimizing.

---
*Developed and maintained by [Moses Ssebuwufu](https://github.com/ssebuwufumoses) - Custom WordPress Themes, Plugins, and Full Site Editing (FSE).*
