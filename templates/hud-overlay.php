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
        
        <div class="corepulse-io-tools" style="display: flex; align-items: center; gap: 8px;">
            <button id="corepulse-copy-report-btn" title="Copy Autopsy Report to Clipboard" style="background: transparent; border: none; padding: 0; color: #a7aaad; cursor: pointer; display: flex; align-items: center; justify-content: center;"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg></button>
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

        <div class="corepulse-issues" id="corepulse-hud-fonts-container" style="display: none;">
            <h4>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ffcc00" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: text-bottom; margin-right: 5px;">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
                Privacy Guard
            </h4>
            <ul id="corepulse-hud-fonts-list"></ul>
        </div>

        <div class="corepulse-issues" id="corepulse-hud-dead-container" style="display: none;">
            <h4>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ff4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: text-bottom; margin-right: 5px;">
                    <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
                </svg>
                Dead Asset Radar
            </h4>
            <ul id="corepulse-hud-dead-list"></ul>
        </div>
        
        <div class="corepulse-hud-section" style="margin-top: 15px; margin-bottom: 15px; background: rgba(0,0,0,0.2); padding: 10px; border-radius: 0 6px 6px 0; border-left: 3px solid #ffcc00;">
            <h4 style="margin: 0 0 12px 0; color: #ffcc00; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; font-family: 'Questrial', sans-serif !important; display: flex; align-items: center;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 6px;">
                    <circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                Query Autopsy (Slow SQL)
            </h4>
            <ul id="corepulse-sql-list" style="list-style: none; margin: 0; padding: 0;"></ul>
        </div>

        <div class="corepulse-hud-section" style="margin-top: 15px; margin-bottom: 15px; background: rgba(0,0,0,0.2); padding: 10px; border-radius: 0 6px 6px 0; border-left: 3px solid #a155ff;">
            <h4 style="margin: 0 0 12px 0; color: #a155ff; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; font-family: 'Questrial', sans-serif !important; display: flex; align-items: center;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 6px;">
                    <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                    <polyline points="2 17 12 22 22 17"></polyline>
                    <polyline points="2 12 12 17 22 12"></polyline>
                </svg>
                Plugin Profiler
            </h4>
            <ul id="corepulse-blame-list" style="list-style: none; margin: 0; padding: 0;"></ul>
        </div>

        <div class="corepulse-hud-section" style="margin-top: 15px; margin-bottom: 15px; background: rgba(0,0,0,0.2); padding: 10px; border-radius: 0 6px 6px 0; border-left: 3px solid #00d2ff;">
            <h4 style="margin: 0 0 12px 0; color: #00d2ff; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; font-family: 'Questrial', sans-serif !important; display: flex; align-items: center;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 6px;">
                    <ellipse cx="12" cy="5" rx="9" ry="3"></ellipse>
                    <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path>
                    <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path>
                </svg>
                Database Autopsy
            </h4>
            
            <div id="corepulse-db-results" style="display: none; margin-bottom: 10px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                    <span style="font-size: 11px; color: #a7aaad;" title="Total size of all options loading on every page. Keep under 800KB.">Autoloaded Bloat:</span>
                    <strong id="corepulse-db-autoload" style="font-size: 11px; color: #f0f0f1;">0 KB</strong>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <span style="font-size: 11px; color: #a7aaad;" title="Temporary database rows that may be orphaned.">Active Transients:</span>
                    <div>
                        <strong id="corepulse-db-transients" style="font-size: 11px; color: #f0f0f1; margin-right: 6px;">0</strong>
                        <button id="corepulse-purge-transients" style="background: rgba(255, 68, 68, 0.1); border: 1px solid #ff4444; color: #ff4444; border-radius: 3px; font-size: 9px; padding: 2px 6px; cursor: pointer; text-transform: uppercase;">Purge</button>
                    </div>
                </div>
                <ul id="corepulse-db-heavy-list" style="list-style: none; margin: 10px 0 0 0; padding: 0; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 8px;"></ul>
            </div>
            
            <button id="corepulse-run-db-scan" style="width: 100%; text-align: center; margin-top: 10px; padding: 8px; font-size: 11px; background: rgba(0, 210, 255, 0.1); border: 1px solid #00d2ff; color: #00d2ff; border-radius: 4px; cursor: pointer; text-transform: uppercase; font-weight: bold; letter-spacing: 1px;">Run Backend Scan</button>
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

        <div class="corepulse-issues" id="corepulse-hud-dependency-container" style="display: none;">
            <h4>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#a155ff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: text-bottom; margin-right: 5px;">
                    <circle cx="18" cy="5" r="3"></circle>
                    <circle cx="6" cy="12" r="3"></circle>
                    <circle cx="18" cy="19" r="3"></circle>
                    <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line>
                    <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line>
                </svg>
                Dependency Matrix
            </h4>
            <div id="corepulse-dependency-tree" style="background: rgba(0,0,0,0.2); padding: 15px; border-radius: 6px; border: 1px solid #3c434a; margin-top: 10px;"></div>
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
                
                <div id="corepulse-dependency-warning" style="display: none; background: rgba(255, 68, 68, 0.1); border-left: 3px solid #ff4444; padding: 10px; margin: 15px 0; font-size: 11px; text-align: left; color: #ff4444;">
                    <strong style="display: flex; align-items: center; gap: 5px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#ff4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                            <line x1="12" y1="9" x2="12" y2="13"></line>
                            <line x1="12" y1="17" x2="12.01" y2="17"></line>
                        </svg>
                        Warning: Foundation Asset
                    </strong>
                    <div style="margin-top: 5px; color: #a7aaad;">This asset is required by the following active scripts. Killing it will likely break them:</div>
                    <ul id="corepulse-dependency-list" style="margin: 5px 0 0 20px; padding: 0; color: #f0f0f1; list-style-type: disc;"></ul>
                </div>

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