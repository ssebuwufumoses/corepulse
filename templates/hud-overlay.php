<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div id="corepulse-floating-trigger" title="Open CorePulse (Ctrl+Shift+X)">
    <span class="pulse-icon" style="margin: 0; width: 12px; height: 12px;"></span>
</div>

<div id="corepulse-hud">
    <div class="corepulse-hud-header">
        <h3>
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#00ff00" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: text-bottom; margin-right: 5px;">
                <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
            </svg>
            Asset Autopsy
        </h3>
        
        <div class="corepulse-io-tools">
            <button id="corepulse-export-btn" title="Export Sniper Rules"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg></button>
            <label for="corepulse-import-file" id="corepulse-import-btn" title="Import Sniper Rules"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg></label>
            <input type="file" id="corepulse-import-file" accept=".json" style="display: none;">
            <button id="corepulse-hud-close">&times;</button>
        </div>
    </div>

    <div class="corepulse-sandbox-bar">
        <div style="display: flex; align-items: center; gap: 10px;">
            <label class="corepulse-switch">
                <input type="checkbox" id="corepulse-sim-toggle" <?php 
                    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                    echo (isset($_GET['cp_simulate']) && $_GET['cp_simulate'] === 'true') ? 'checked' : ''; 
                ?>>
                <span class="corepulse-slider"></span>
            </label>
            <span class="corepulse-sim-label" id="corepulse-sim-label">SIM: OFF</span>
        </div>
        <span class="corepulse-sandbox-desc">Dry Run Mode</span>
    </div>
    
    <div class="corepulse-hud-body">
        
        <div class="corepulse-stat-grid">
            <div class="corepulse-stat-box">
                <h4>JS Payload</h4>
                <p id="corepulse-hud-weight">0 KB</p>
            </div>
            <div class="corepulse-stat-box">
                <h4>CSS Payload</h4>
                <p id="corepulse-hud-css-weight">0 KB</p>
            </div>
            <div class="corepulse-stat-box">
                <h4>DOM Nodes</h4>
                <p id="corepulse-hud-dom">Scanning...</p>
            </div>
            <div class="corepulse-stat-box">
                <h4>TTFB</h4>
                <p id="corepulse-hud-ttfb">0 ms</p>
            </div>
            <div class="corepulse-stat-box">
                <h4>INP Delay</h4>
                <p id="corepulse-hud-inp">0 ms</p>
            </div>
            <div class="corepulse-stat-box">
                <h4>CLS Score</h4>
                <p id="corepulse-hud-cls">0.00</p>
            </div>
        </div>
        
        <div class="corepulse-fatal-error" id="corepulse-hud-fatal-container" style="display: none;">
            <h4>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ff0000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: text-bottom; margin-right: 5px;">
                    <polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"></polygon>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                Fatal JS Error Detected!
            </h4>
            <p style="font-size: 12px; color: #f0f0f1; margin-bottom: 10px;" id="corepulse-fatal-msg"></p>
            <button id="corepulse-emergency-restore-btn" class="corepulse-kill-toggle" style="background-color: #ff0000 !important; color: #fff !important; width: 100%; border: none !important;">Emergency Restore All & Reload</button>
        </div>

        <div class="corepulse-wcag" id="corepulse-hud-wcag-container" style="display: none;">
            <h4>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#00d2ff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: text-bottom; margin-right: 5px;">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M12 16v-4"></path>
                    <path d="M12 8h.01"></path>
                </svg>
                WCAG Violations
            </h4>
            <ul id="corepulse-hud-wcag-list"></ul>
        </div>

        <div class="corepulse-issues" id="corepulse-hud-lcp-container" style="display: none;">
            <h4>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ff4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: text-bottom; margin-right: 5px;">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                    <line x1="12" y1="22.08" x2="12" y2="12"></line>
                </svg>
                LCP Image Warning
            </h4>
            <ul id="corepulse-hud-lcp-list"></ul>
        </div>
        
        <div class="corepulse-issues">
            <h4>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ffcc00" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: text-bottom; margin-right: 5px;">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                Local Culprits
            </h4>
            <ul id="corepulse-hud-culprits"></ul>
        </div>

        <div class="corepulse-issues" id="corepulse-hud-media-container" style="display: none;">
            <h4>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ff9900" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: text-bottom; margin-right: 5px;">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline>
                </svg>
                Heavy Media Radar
            </h4>
            <ul id="corepulse-hud-media-list"></ul>
        </div>

        <div class="corepulse-issues" id="corepulse-hud-external-container" style="display: none;">
            <h4>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#a155ff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: text-bottom; margin-right: 5px;">
                    <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
                </svg>
                External Invaders
            </h4>
            <ul id="corepulse-hud-external-list"></ul>
        </div>

        <div class="corepulse-graveyard" id="corepulse-hud-graveyard-container" style="display: none;">
            <h4>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ff4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: text-bottom; margin-right: 5px;">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="15" y1="9" x2="9" y2="15"></line>
                    <line x1="9" y1="9" x2="15" y2="15"></line>
                </svg>
                The Graveyard
            </h4>
            <ul id="corepulse-hud-graveyard"></ul>
        </div>

        <div id="corepulse-sniper-modal" style="display: none;">
            <div class="corepulse-sniper-content">
                <h4>Sniper Engine</h4>
                <p>Where do you want to kill <strong id="corepulse-sniper-target" style="color:#ffcc00;"></strong>?</p>
                <div class="corepulse-sniper-options">
                    <button class="corepulse-sniper-btn" data-rule="everywhere">Kill Everywhere (Global)</button>
                    <button class="corepulse-sniper-btn" data-rule="only">Kill on THIS Page Only</button>
                    <button class="corepulse-sniper-btn" data-rule="except">Kill Everywhere EXCEPT This Page</button>
                </div>
                <button id="corepulse-sniper-cancel">Cancel</button>
            </div>
        </div>
    </div>
</div>