# Contributing to CorePulse ⚡

Thank you for helping us engineer a faster, more inclusive WordPress. CorePulse is a performance-first, server-centric engine, and we welcome contributions that align with our mission of reducing frontend bloat.

## Engineering Philosophy
Before submitting a Pull Request, please ensure your code follows these core pillars:
* **Server-Centric Execution**: Intercept and optimize the DOM at the PHP level whenever possible.
* **Zero-Dependency Frontend**: Avoid adding external JS libraries or heavy frameworks. Use native Browser APIs (like `PerformanceObserver`) for diagnostics.
* **Admin-Only Isolation**: All diagnostic UI must be wrapped in strict `manage_options` capability checks.
* **Inclusive Engineering**: Every new feature must be compatible with WCAG accessibility standards and pass our internal `WCAG Guard` tracing.

## Current Development Focus: v1.1.0 & v1.2.0 Roadmap
We are currently prioritizing the following engineering milestones. If you wish to contribute, please reference these specific targets:

### [v1.1.0] Headless Simulation Mode
**Objective**: Develop a sandbox environment to preview performance impacts before global application.
* **Contribution Need**: Help build a "dry run" interceptor that simulates script dequeuing and calculates theoretical DOM weight reduction.

### [v1.1.0] Asset Dependency Mapping
**Objective**: Automatically detect script dependencies to prevent site breakage.
* **Contribution Need**: Assist in mapping the `$wp_scripts` global object to visualize parent-child relationships (e.g., scripts requiring `jquery` or `underscore`).

### [v1.2.0] Historical Pulse Logs
**Objective**: Implement local storage for Web Vitals snapshots.
* **Contribution Need**: Design a lightweight JSON-based logging system to track TTFB, INP, and LCP trends over time without bloated database queries.

### [v1.2.0] Auto-Preconnect Engine
**Objective**: Intelligent detection of 3rd-party domains for resource hints.
* **Contribution Need**: Develop a regex-based scanner to identify external domains (Google Fonts, CDNs) and inject `<link rel="preconnect">` tags dynamically.

## How to Submit a Contribution
1. **Fork & Branch**: Create a feature branch (e.g., `feature/asset-mapping`).
2. **Issue Link**: Every PR must reference an open [Issue](../../issues) or [Feature Request](../../issues/new?template=feature_request.md).
3. **Coding Standards**: Follow the [WordPress PHP Coding Standards](https://developer.wordpress.org/coding-standards/).
4. **Documentation**: Update the [Technical Wiki](../../wiki) if you introduce new engine logic.

---
*By contributing, you agree that your code will be licensed under GPLv2.*
