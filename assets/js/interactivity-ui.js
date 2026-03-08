document.addEventListener('DOMContentLoaded', () => {
    const scoreElement = document.getElementById('pulse-score');
    const iconElement = document.getElementById('corepulse-icon');
    let currentSniperTarget = '';

    // Ensure the 'killed' data is an object BEFORE injecting simulation data
    if (window.corepulse_ajax && Array.isArray(window.corepulse_ajax.killed)) {
        window.corepulse_ajax.killed = {};
    }

    // Inject simulated targets into the HUD state
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('cp_simulate') === 'true' && urlParams.get('cp_target')) {
        const simulatedTargets = urlParams.get('cp_target').split(',');
        window.corepulse_ajax = window.corepulse_ajax || { killed: {} };
        simulatedTargets.forEach(target => {
            if (target.trim() !== '') {
                window.corepulse_ajax.killed[target] = { rule: 'Simulated (Dry Run)', locations: [] };
            }
        });
    }

    window.addEventListener('error', function(event) {
        let hasKilledScripts = window.corepulse_ajax && window.corepulse_ajax.killed && Object.keys(window.corepulse_ajax.killed).length > 0;
        if (hasKilledScripts) {
            const fatalContainer = document.getElementById('corepulse-hud-fatal-container');
            const fatalMsg = document.getElementById('corepulse-fatal-msg');
            if (fatalContainer && fatalMsg) {
                fatalMsg.innerText = event.message || 'A critical JavaScript crash occurred.';
                fatalContainer.style.display = 'block';
                if (iconElement) {
                    iconElement.style.background = '#ff0000';
                    iconElement.style.boxShadow = '0 0 15px #ff0000';
                }
            }
        }
    });

    function scanDOMDepth() {
        const domCountElement = document.getElementById('corepulse-hud-dom');
        if (!domCountElement || !window.corePulseData) return;
        
        const settings = window.corePulseData.settings;
        const totalNodes = document.querySelectorAll('*').length;
        domCountElement.innerText = totalNodes.toLocaleString();
        
        if (totalNodes > settings.dom_danger) domCountElement.style.color = '#ff4444'; 
        else if (totalNodes > settings.dom_warning) domCountElement.style.color = '#ffcc00'; 
        else domCountElement.style.color = '#00ff00'; 
    }

    function scanWCAG() {
        const wcagContainer = document.getElementById('corepulse-hud-wcag-container');
        const wcagList = document.getElementById('corepulse-hud-wcag-list');
        if (!wcagContainer || !wcagList) return;

        let violationCount = 0;
        wcagList.innerHTML = '';

        const rawImages = document.querySelectorAll('img:not([alt]), img[alt=""]');
        const images = Array.from(rawImages).filter(img => !img.closest('#wpadminbar') && !img.closest('#corepulse-hud'));

        if (images.length > 0) {
            violationCount++;
            const li = document.createElement('li');
            li.innerHTML = `${images.length} image(s) missing 'alt'. <button class="corepulse-wcag-trace-btn" data-trace="img">Trace</button>`;
            li.style.borderLeftColor = '#00d2ff';
            wcagList.appendChild(li);
        }

        const rawInputs = document.querySelectorAll('input:not([type="hidden"]):not([type="submit"]):not([aria-label]):not([aria-labelledby])');
        let unlabelledInputs = 0;
        Array.from(rawInputs).forEach(input => {
            if (input.closest('#wpadminbar') || input.closest('#corepulse-hud')) return;
            if (!input.id || !document.querySelector(`label[for="${input.id}"]`)) unlabelledInputs++;
        });

        if (unlabelledInputs > 0) {
            violationCount++;
            const li = document.createElement('li');
            li.innerHTML = `${unlabelledInputs} unlabelled form input(s). <button class="corepulse-wcag-trace-btn" data-trace="input">Trace</button>`;
            li.style.borderLeftColor = '#00d2ff';
            wcagList.appendChild(li);
        }

        wcagContainer.style.display = violationCount > 0 ? 'block' : 'none';
    }

    function trackLCP() {
        const lcpContainer = document.getElementById('corepulse-hud-lcp-container');
        const lcpList = document.getElementById('corepulse-hud-lcp-list');
        if (!lcpContainer || !lcpList) return;

        try {
            new PerformanceObserver((entryList) => {
                const entries = entryList.getEntries();
                const lastEntry = entries[entries.length - 1];
                
                if (lastEntry && lastEntry.element && lastEntry.element.tagName === 'IMG') {
                    const img = lastEntry.element;
                    let issues = [];
                    if (img.loading === 'lazy') issues.push('LCP image has loading="lazy" (Remove it to fix LCP delay)');
                    
                    if (issues.length > 0) {
                        lcpContainer.style.display = 'block';
                        img.id = 'corepulse-lcp-target'; 
                        lcpList.innerHTML = `
                            <li style="border-left-color: #ff4444;">
                                <strong>LCP Render Blocked!</strong> <button class="corepulse-wcag-trace-btn" data-trace="lcp" style="border-color:#ff4444;color:#ff4444;">Trace</button><br>
                                <span style="font-size:11px;color:#a7aaad;">${issues.join('<br>')}</span>
                            </li>
                        `;
                    }
                }
            }).observe({ type: 'largest-contentful-paint', buffered: true });
        } catch (e) { } 
    }

    function trackWebVitals() {
        const navEntry = performance.getEntriesByType('navigation')[0];
        const ttfbElement = document.getElementById('corepulse-hud-ttfb');
        if (navEntry && ttfbElement) {
            const ttfb = Math.round(navEntry.responseStart);
            ttfbElement.innerText = ttfb + ' ms';
            if (ttfb > 600) ttfbElement.style.color = '#ff4444';
            else if (ttfb > 300) ttfbElement.style.color = '#ffcc00';
            else ttfbElement.style.color = '#00ff00';
        }

        const inpElement = document.getElementById('corepulse-hud-inp');
        if (inpElement) {
            function updateInpUI(delay) {
                inpElement.innerText = delay + ' ms';
                if (delay > 500) inpElement.style.color = '#ff4444'; 
                else if (delay > 200) inpElement.style.color = '#ffcc00'; 
                else inpElement.style.color = '#00ff00'; 
            }
            try {
                new PerformanceObserver((list) => {
                    list.getEntries().forEach(e => {
                        if (e.interactionId > 0 || e.name.includes('click')) updateInpUI(Math.round(e.duration));
                    });
                }).observe({ type: 'event', durationThreshold: 16, buffered: true });
            } catch (e) {}
            
            document.addEventListener('pointerdown', () => {
                const start = performance.now();
                requestAnimationFrame(() => {
                    setTimeout(() => {
                        const delay = Math.round(performance.now() - start);
                        if (delay < 16 || inpElement.innerText === '0 ms') updateInpUI(Math.max(1, delay));
                    }, 0);
                });
            }, { passive: true });
        }

        let clsValue = 0;
        const clsElement = document.getElementById('corepulse-hud-cls');
        if (clsElement) {
            try {
                new PerformanceObserver((entryList) => {
                    for (const entry of entryList.getEntries()) {
                        if (!entry.hadRecentInput) {
                            clsValue += entry.value;
                            clsElement.innerText = clsValue.toFixed(3);
                            if (clsValue > 0.25) clsElement.style.color = '#ff4444';
                            else if (clsValue > 0.1) clsElement.style.color = '#ffcc00';
                            else clsElement.style.color = '#00ff00';
                        }
                    }
                }).observe({type: 'layout-shift', buffered: true});
            } catch (e) {}
        }
    }

    function scanHeavyMedia() {
        const mediaContainer = document.getElementById('corepulse-hud-media-container');
        const mediaList = document.getElementById('corepulse-hud-media-list');
        if (!mediaContainer || !mediaList || !window.corePulseData) return;

        const preloads = window.corePulseData.preloads || {};
        const settings = window.corePulseData.settings;
        const byteLimit = settings.media_limit * 1024; 
        const resources = performance.getEntriesByType('resource');
        let heavyItems = [];

        resources.forEach(res => {
            const size = res.decodedBodySize || res.transferSize || 0;
            if (size > byteLimit) {
                if (res.initiatorType === 'img' || res.initiatorType === 'iframe' || res.initiatorType === 'video' || res.name.match(/\.(woff2?|ttf|otf|png|jpe?g|gif|webp|mp4)/i)) {
                    heavyItems.push({ url: res.name, type: res.initiatorType || 'media', sizeKB: Math.round(size / 1024) });
                }
            }
        });

        if (heavyItems.length > 0) {
            mediaContainer.style.display = 'block';
            mediaList.innerHTML = '';
            heavyItems.sort((a, b) => b.sizeKB - a.sizeKB);

            heavyItems.forEach(item => {
                const urlParts = item.url.split('/');
                let filename = urlParts[urlParts.length - 1].split('?')[0];
                if (filename.length > 35) filename = filename.substring(0, 32) + '...';
                if (!filename) filename = item.type.toUpperCase() + ' Resource';

                let displayType = item.type.toUpperCase();
                let actualType = 'image';
                if(filename.match(/\.(woff2?|ttf|otf|eot)$/i)) { displayType = 'FONT'; actualType = 'font'; }
                else if(filename.match(/\.(png|jpe?g|gif|webp|avif|svg)$/i)) { displayType = 'IMAGE'; actualType = 'image'; }
                else if(filename.match(/\.(mp4|webm|avi)$/i)) displayType = 'VIDEO';
                else if(displayType === 'CSS' || displayType === 'XMLHTTPREQUEST') displayType = 'MEDIA';

                const isPreloaded = preloads.hasOwnProperty(item.url);
                let btnHtml = '';
                if (actualType === 'font' || actualType === 'image') {
                    const btnClass = isPreloaded ? 'corepulse-btn-boost active' : 'corepulse-btn-boost';
                    const btnText = isPreloaded ? 'BOOSTED' : 'BOOST';
                    btnHtml = `<button class="corepulse-kill-toggle ${btnClass}" data-url="${item.url}" data-type="${actualType}">${btnText}</button>`;
                }

                const li = document.createElement('li');
                li.style.display = 'flex'; 
                li.style.justifyContent = 'space-between'; 
                li.style.alignItems = 'center';
                li.style.borderLeftColor = '#ff9900'; 
                li.innerHTML = `
                    <div style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; width: 100%;">
                        <strong style="color: #f0f0f1;" title="${item.url}">${filename}</strong>
                        <span class="corepulse-size-badge external">${item.sizeKB} KB</span><br>
                        <span style="font-size: 11px; color: #a7aaad;">Type: ${displayType} - Compress this asset.</span>
                    </div>
                    <div style="display:flex; flex-shrink:0;">${btnHtml}</div>
                `;
                mediaList.appendChild(li);
            });
        } else {
            mediaContainer.style.display = 'none';
        }
    }
    
    function updateUI(weight, cssWeight, hasHydrationError) {
        if (!scoreElement || !iconElement || !window.corePulseData) return;
        const settings = window.corePulseData.settings;
        const floatNode = document.getElementById('corepulse-floating-trigger');

        scoreElement.innerText = weight + ' KB';
        const hudWeight = document.getElementById('corepulse-hud-weight');
        const hudCssWeight = document.getElementById('corepulse-hud-css-weight');

        if (hudWeight) hudWeight.innerText = weight + ' KB';
        if (hudCssWeight) hudCssWeight.innerText = cssWeight + ' KB';

        // Clear existing status classes on the floating node
        if (floatNode) {
            floatNode.classList.remove('corepulse-status-error', 'corepulse-status-danger', 'corepulse-status-warning');
        }

        // Apply visual budget alerts
        if (hasHydrationError) {
            scoreElement.innerText += ' (Error)';
            scoreElement.style.color = '#a155ff'; 
            iconElement.style.background = '#a155ff'; 
            if (floatNode) floatNode.classList.add('corepulse-status-error');
        } else if (weight > settings.js_danger || cssWeight > settings.css_danger) {
            scoreElement.style.color = '#ff4444'; 
            iconElement.style.background = '#ff4444';
            if (floatNode) floatNode.classList.add('corepulse-status-danger');
        } else if (weight > settings.js_warning || cssWeight > settings.css_warning) {
            scoreElement.style.color = '#ffcc00'; 
            iconElement.style.background = '#ffcc00';
            if (floatNode) floatNode.classList.add('corepulse-status-warning');
        } else {
            scoreElement.style.color = '#00ff00'; 
        }

        if (hudCssWeight) {
            if (cssWeight > settings.css_danger) hudCssWeight.style.color = '#ff4444';
            else if (cssWeight > settings.css_warning) hudCssWeight.style.color = '#ffcc00';
            else hudCssWeight.style.color = '#00ff00';
        }
    }

    function populateHUD() {
        const hudCulprits = document.getElementById('corepulse-hud-culprits');
        const hudExternals = document.getElementById('corepulse-hud-external-list');
        const extContainer = document.getElementById('corepulse-hud-external-container');
        const hudGraveyardContainer = document.getElementById('corepulse-hud-graveyard-container');
        const hudGraveyardList = document.getElementById('corepulse-hud-graveyard');

        const killedData = window.corepulse_ajax ? window.corepulse_ajax.killed : {};
        const killedHandles = Object.keys(killedData);

        if (hudCulprits && window.corePulseData) {
            const activeAssets = window.corePulseData.culprits.filter(c => !killedHandles.includes(c.handle));
            const locals = activeAssets.filter(c => c.domain === 'Local' || c.domain === 'Inline');
            const externals = activeAssets.filter(c => c.domain !== 'Local' && c.domain !== 'Inline');
            const preloads = window.corePulseData.preloads || {};

            hudCulprits.innerHTML = ''; 
            if (locals.length > 0) {
                locals.forEach(culprit => {
                    const ext = culprit.type === 'css' ? '.css' : '.js';
                    const nameColor = culprit.type === 'css' ? '#00d2ff' : '#f0f0f1';
                    const providerHtml = culprit.provider ? `<span style="font-size: 9px; color: #a155ff; display: block; margin-top: 2px;">${culprit.provider}</span>` : '';
                    
                    const isPreloaded = preloads.hasOwnProperty(culprit.url);
                    let btnHtml = '';
                    if (culprit.url && culprit.url !== '') {
                        const btnClass = isPreloaded ? 'corepulse-btn-boost active' : 'corepulse-btn-boost';
                        const btnText = isPreloaded ? 'BOOSTED' : 'BOOST';
                        btnHtml = `<button class="corepulse-kill-toggle ${btnClass}" data-url="${culprit.url}" data-type="${culprit.type}">${btnText}</button>`;
                    }

                    const deps = culprit.dependents || [];
                    let depsHtml = '';
                    let depsData = '';
                    if (deps.length > 0) {
                        const alertSvg = `<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#ff4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-top: -2px; margin-right: 3px;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>`;
                        depsHtml = `<span class="corepulse-size-badge" style="color:#ff4444; border-color:rgba(255,68,68,0.3);" title="Required by: ${deps.join(', ')}">${alertSvg}${deps.length} Deps</span>`;
                        depsData = deps.join(',');
                    }

                    const li = document.createElement('li');
                    li.style.display = 'flex'; li.style.justifyContent = 'space-between'; li.style.alignItems = 'center';
                    li.innerHTML = `
                        <div>
                            <strong style="color: ${nameColor};">${culprit.handle}${ext}</strong>
                            <span class="corepulse-size-badge">${culprit.size}</span>
                            ${depsHtml}
                            ${providerHtml}
                            <span style="font-size: 11px; color: #a7aaad; display:block; margin-top:2px;">${culprit.suggestion}</span>
                        </div>
                        <div style="display:flex; flex-shrink:0;">
                            ${btnHtml}
                            <button class="corepulse-kill-toggle corepulse-btn-kill" data-handle="${culprit.handle}" data-dependents="${depsData}">Kill</button>
                        </div>
                    `;
                    hudCulprits.appendChild(li);
                });
            } else {
                hudCulprits.innerHTML = '<li style="border-left-color: #00ff00;">No active heavy local assets!</li>';
            }

            if (externals.length > 0) {
                extContainer.style.display = 'block';
                hudExternals.innerHTML = '';
                externals.forEach(culprit => {
                    const ext = culprit.type === 'css' ? '.css' : '.js';
                    const nameColor = culprit.type === 'css' ? '#00d2ff' : '#f0f0f1';
                    
                    const deps = culprit.dependents || [];
                    let depsHtml = '';
                    let depsData = '';
                    if (deps.length > 0) {
                        const alertSvg = `<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#ff4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-top: -2px; margin-right: 3px;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>`;
                        depsHtml = `<span class="corepulse-size-badge" style="color:#ff4444; border-color:rgba(255,68,68,0.3);" title="Required by: ${deps.join(', ')}">${alertSvg}${deps.length} Deps</span>`;
                        depsData = deps.join(',');
                    }

                    const li = document.createElement('li');
                    li.style.display = 'flex'; li.style.justifyContent = 'space-between'; li.style.alignItems = 'center';
                    li.style.borderLeftColor = '#a155ff'; 
                    li.innerHTML = `
                        <div>
                            <strong style="color: ${nameColor};">${culprit.handle}${ext}</strong>
                            <span class="corepulse-size-badge external">External</span>
                            ${depsHtml}<br>
                            <span style="font-size: 11px; color: #a7aaad;">Domain: ${culprit.domain}</span>
                        </div>
                        <button class="corepulse-kill-toggle corepulse-btn-kill" data-handle="${culprit.handle}" data-dependents="${depsData}">Kill</button>
                    `;
                    hudExternals.appendChild(li);
                });
            } else {
                extContainer.style.display = 'none';
            }
        }

        if (hudGraveyardContainer && hudGraveyardList) {
            if (killedHandles.length > 0) {
                hudGraveyardContainer.style.display = 'block';
                hudGraveyardList.innerHTML = '';
                killedHandles.forEach(handle => {
                    const ruleData = killedData[handle];
                    
                    // FIXED: Check if the specific script is real or simulated and pass data attribute to button
                    const isSimulated = ruleData.rule === 'Simulated (Dry Run)';
                    const ruleText = isSimulated ? 'Simulated (Dry Run)' : (ruleData.rule === 'everywhere' ? 'Global Block' : (ruleData.rule === 'only' ? 'Blocked on this page' : 'Blocked on other pages'));
                    
                    const li = document.createElement('li');
                    li.style.display = 'flex'; li.style.justifyContent = 'space-between'; li.style.alignItems = 'center';
                    li.innerHTML = `
                        <div>
                            <strong style="color: #f0f0f1; text-decoration: line-through; opacity: 0.6;">${handle}</strong><br>
                            <span style="font-size: 11px; color: #ffcc00;">Rule: ${ruleText}</span>
                        </div>
                        <button class="corepulse-kill-toggle corepulse-btn-revive" data-handle="${handle}" data-simulated="${isSimulated}">Revive</button>
                    `;
                    hudGraveyardList.appendChild(li);
                });
            } else {
                hudGraveyardContainer.style.display = 'none';
            }
        }
        scanWCAG();
        scanHeavyMedia(); 
    }

    document.addEventListener('click', (e) => {
        const adminBtn = e.target.closest('#wp-admin-bar-corepulse-status');
        const floatBtn = e.target.closest('#corepulse-floating-trigger'); 
        const closeBtn = e.target.closest('#corepulse-hud-close');
        const killBtn = e.target.closest('.corepulse-btn-kill');
        const boostBtn = e.target.closest('.corepulse-btn-boost');
        const reviveBtn = e.target.closest('.corepulse-btn-revive');
        const sniperBtn = e.target.closest('.corepulse-sniper-btn');
        const cancelSniperBtn = e.target.closest('#corepulse-sniper-cancel');
        const emergencyBtn = e.target.closest('#corepulse-emergency-restore-btn');
        const traceBtn = e.target.closest('.corepulse-wcag-trace-btn');
        const exportBtn = e.target.closest('#corepulse-export-btn');
        const hud = document.getElementById('corepulse-hud');
        const sniperModal = document.getElementById('corepulse-sniper-modal');

        if (adminBtn || floatBtn) {
            if (hud) {
                e.preventDefault(); 
                hud.classList.add('corepulse-hud-active'); 
                populateHUD();
            }
        }
        
        if (closeBtn && hud) {
            e.preventDefault();
            hud.classList.remove('corepulse-hud-active');
        }

        if (boostBtn) {
            e.preventDefault();
            const url = boostBtn.getAttribute('data-url');
            const type = boostBtn.getAttribute('data-type');
            boostBtn.innerText = '...';
            
            const formData = new URLSearchParams();
            formData.append('action', 'corepulse_toggle_preload');
            formData.append('url', url);
            formData.append('type', type);
            formData.append('security', window.corepulse_ajax.nonce);

            fetch(window.corepulse_ajax.url, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(response => {
                if (response.success) {
                    sessionStorage.setItem('corepulse_hud_open', 'true');
                    window.location.reload(); 
                }
            });
        }

        if (exportBtn) {
            e.preventDefault();
            if (window.corePulseData && window.corePulseData.rules) {
                const dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(window.corePulseData.rules));
                const downloadAnchorNode = document.createElement('a');
                downloadAnchorNode.setAttribute("href", dataStr);
                downloadAnchorNode.setAttribute("download", "corepulse-sniper-rules.json");
                document.body.appendChild(downloadAnchorNode);
                downloadAnchorNode.click();
                downloadAnchorNode.remove();
            } else {
                alert("No rules found to export.");
            }
        }

        if (traceBtn) {
            e.preventDefault();
            const type = traceBtn.getAttribute('data-trace');
            document.querySelectorAll('.corepulse-trace-highlight').forEach(el => el.classList.remove('corepulse-trace-highlight'));

            let targets = [];
            if (type === 'img') {
                const rawImages = document.querySelectorAll('img:not([alt]), img[alt=""]');
                targets = Array.from(rawImages).filter(img => !img.closest('#wpadminbar') && !img.closest('#corepulse-hud'));
            }
            else if (type === 'input') {
                const rawInputs = document.querySelectorAll('input:not([type="hidden"]):not([type="submit"]):not([aria-label]):not([aria-labelledby])');
                targets = Array.from(rawInputs).filter(i => {
                    if (i.closest('#wpadminbar') || i.closest('#corepulse-hud')) return false;
                    return !i.id || !document.querySelector(`label[for="${i.id}"]`);
                });
            }
            else if (type === 'lcp') {
                const lcpNode = document.querySelector('#corepulse-lcp-target');
                if (lcpNode) targets = [lcpNode];
            }

            if (targets.length > 0) {
                targets.forEach(el => el.classList.add('corepulse-trace-highlight'));
                targets[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                setTimeout(() => { targets.forEach(el => el.classList.remove('corepulse-trace-highlight')); }, 20000);
                if (hud) hud.classList.remove('corepulse-hud-active');
            }
        }

        if (killBtn) {
            e.preventDefault();
            currentSniperTarget = killBtn.getAttribute('data-handle');
            document.getElementById('corepulse-sniper-target').innerText = currentSniperTarget;
            
            const depsAttr = killBtn.getAttribute('data-dependents');
            const depWarningBox = document.getElementById('corepulse-dependency-warning');
            const depList = document.getElementById('corepulse-dependency-list');
            
            if (depsAttr && depsAttr.length > 0) {
                const deps = depsAttr.split(',');
                depList.innerHTML = '';
                deps.forEach(dep => {
                    const li = document.createElement('li');
                    li.innerText = dep;
                    depList.appendChild(li);
                });
                depWarningBox.style.display = 'block';
            } else {
                depWarningBox.style.display = 'none';
            }

            sniperModal.style.display = 'flex';
            if (hud) hud.scrollTo({ top: 0, behavior: 'smooth' });
        }

        if (cancelSniperBtn) {
            e.preventDefault();
            currentSniperTarget = '';
            sniperModal.style.display = 'none';
        }

        if (sniperBtn) {
            e.preventDefault();
            const rule = sniperBtn.getAttribute('data-rule');
            sniperBtn.innerText = 'Targeting...';

            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('cp_simulate') === 'true') {
                let currentTargets = urlParams.get('cp_target') ? urlParams.get('cp_target').split(',') : [];
                if (!currentTargets.includes(currentSniperTarget)) currentTargets.push(currentSniperTarget);
                urlParams.set('cp_target', currentTargets.join(','));
                sessionStorage.setItem('corepulse_hud_open', 'true');
                window.location.search = urlParams.toString();
                return; 
            }

            const formData = new URLSearchParams();
            formData.append('action', 'corepulse_toggle_script');
            formData.append('handle', currentSniperTarget);
            formData.append('rule', rule);
            formData.append('post_id', window.corepulse_ajax.post_id);
            formData.append('security', window.corepulse_ajax.nonce);

            fetch(window.corepulse_ajax.url, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(response => {
                if (response.success) {
                    sessionStorage.setItem('corepulse_hud_open', 'true');
                    window.location.reload(); 
                } else {
                    alert('Sniper Error: ' + response.data);
                }
            });
        }

        if (reviveBtn) {
            e.preventDefault();
            const handle = reviveBtn.getAttribute('data-handle');
            
            // Verify if we are dealing with a simulated script or a real one
            const isSimulatedTarget = reviveBtn.getAttribute('data-simulated') === 'true';

            reviveBtn.innerText = '...';
            reviveBtn.style.opacity = '0.5';

            if (isSimulatedTarget) {
                // Remove from URL params if it is simulated
                const urlParams = new URLSearchParams(window.location.search);
                let currentTargets = urlParams.get('cp_target') ? urlParams.get('cp_target').split(',') : [];
                currentTargets = currentTargets.filter(t => t !== handle);
                if (currentTargets.length > 0) urlParams.set('cp_target', currentTargets.join(','));
                else urlParams.delete('cp_target');
                sessionStorage.setItem('corepulse_hud_open', 'true');
                window.location.search = urlParams.toString();
                return;
            }

            // If it's a real kill, hit the database via AJAX regardless of SIM status
            const formData = new URLSearchParams();
            formData.append('action', 'corepulse_toggle_script');
            formData.append('handle', handle);
            formData.append('rule', 'revive');
            formData.append('security', window.corepulse_ajax.nonce);

            fetch(window.corepulse_ajax.url, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(response => {
                if (response.success) {
                    sessionStorage.setItem('corepulse_hud_open', 'true');
                    window.location.reload(); 
                }
            });
        }

        if (emergencyBtn) {
            e.preventDefault();
            emergencyBtn.innerText = 'Restoring...';
            const formData = new URLSearchParams();
            formData.append('action', 'corepulse_emergency_restore');
            formData.append('security', window.corepulse_ajax.nonce);

            fetch(window.corepulse_ajax.url, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(response => {
                if (response.success) {
                    sessionStorage.setItem('corepulse_hud_open', 'true');
                    window.location.reload();
                }
            });
        }
    });

    const fileInput = document.getElementById('corepulse-import-file');
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function(evt) {
                const contents = evt.target.result;
                const formData = new URLSearchParams();
                formData.append('action', 'corepulse_import_rules');
                formData.append('rules', contents);
                formData.append('security', window.corepulse_ajax.nonce);

                fetch(window.corepulse_ajax.url, { method: 'POST', body: formData })
                .then(res => res.json())
                .then(response => {
                    if (response.success) {
                        alert("Rules imported successfully!");
                        sessionStorage.setItem('corepulse_hud_open', 'true');
                        window.location.reload();
                    } else {
                        alert("Import failed: " + response.data);
                    }
                });
            };
            reader.readAsText(file);
        });
    }

    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key.toLowerCase() === 'x') {
            e.preventDefault();
            const hud = document.getElementById('corepulse-hud');
            if (hud) {
                if (hud.classList.contains('corepulse-hud-active')) {
                    hud.classList.remove('corepulse-hud-active'); 
                } else {
                    hud.classList.add('corepulse-hud-active'); 
                    populateHUD();
                }
            }
        }
    });

    window.addEventListener('load', () => {
        if (sessionStorage.getItem('corepulse_hud_open') === 'true') {
            sessionStorage.removeItem('corepulse_hud_open');
            const hud = document.getElementById('corepulse-hud');
            if (hud) {
                hud.classList.add('corepulse-hud-active');
                populateHUD();
            }
        }
    });

    if (window.corePulseData) {
        if (window.corePulseData.settings.hide_trigger) {
            const floatNode = document.getElementById('corepulse-floating-trigger');
            if (floatNode) floatNode.style.display = 'none';
        }

        let hasError = window.corePulseHydrationErrors && window.corePulseHydrationErrors.length > 0;
        updateUI(window.corePulseData.weight, window.corePulseData.css_weight, hasError);
        scanDOMDepth(); 
        scanWCAG();
        trackWebVitals();
    }
    
    const simToggle = document.getElementById('corepulse-sim-toggle');
    const simLabel = document.getElementById('corepulse-sim-label');
    
    if (simToggle) {
        if (simToggle.checked && simLabel) {
            simLabel.style.color = '#00ff00';
            simLabel.innerText = 'SIM: ON';
        }

        simToggle.addEventListener('change', (e) => {
            const urlParams = new URLSearchParams(window.location.search);
            if (e.target.checked) {
                urlParams.set('cp_simulate', 'true');
            } else {
                urlParams.delete('cp_simulate');
                urlParams.delete('cp_target'); 
            }
            sessionStorage.setItem('corepulse_hud_open', 'true');
            window.location.search = urlParams.toString(); 
        });
    }
});

// v1.2.0: Historical Pulse Logs Beacon
    setTimeout(() => {
        if (!window.corepulse_ajax || !window.corePulseData) return;

        const ttfbText = document.getElementById('corepulse-hud-ttfb')?.innerText || '0';
        const inpText  = document.getElementById('corepulse-hud-inp')?.innerText || '0';
        const clsText  = document.getElementById('corepulse-hud-cls')?.innerText || '0';

        const formData = new URLSearchParams();
        formData.append('action', 'corepulse_log_vitals');
        formData.append('js_kb', window.corePulseData.weight || 0);
        formData.append('css_kb', window.corePulseData.css_weight || 0);
        formData.append('ttfb', parseInt(ttfbText));
        formData.append('inp', parseInt(inpText));
        formData.append('cls', parseFloat(clsText));
        formData.append('url', window.location.pathname);
        formData.append('security', window.corepulse_ajax.nonce);

        // Send payload quietly in the background
        fetch(window.corepulse_ajax.url, { 
            method: 'POST', 
            body: formData,
            keepalive: true 
        }).catch(() => {}); // Ignore errors so it never bothers the user
        
    }, 5000); // Wait 5 seconds after load so we don't block rendering