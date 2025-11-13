/**
 * Frontend JavaScript - Fixed Download Flow with Analytics
 */
(function() {
    'use strict';
    
    // Check if settings exist
    if (typeof tkmSettings === 'undefined') return;
    
    // Get elements
    var btn = document.getElementById('tkm-btn');
    var status = document.getElementById('tkm-status');
    var progress = document.getElementById('tkm-progress');
    var progressFill = document.getElementById('tkm-progress-fill');
    var fallback = document.getElementById('tkm-fallback');
    var countEl = document.getElementById('tkm-count');
    
    if (!btn) return;
    
    var countdown = parseInt(tkmSettings.countdown) || 10;
    var isRunning = false;
    var intervalId = null;
    
    // Button click handler
    btn.addEventListener('click', function() {
        if (isRunning) return;
        
        isRunning = true;
        btn.disabled = true;
        
        // Show status and progress
        status.className = 'tkm-status show';
        //status.textContent = 'Preparing your download...';
        progress.className = 'tkm-progress show';
        
        // Start countdown
        var timeLeft = countdown;
        
        intervalId = setInterval(function() {
            // Update button text
            btn.textContent = 'Please wait (' + timeLeft + 's)';
            
            // Update progress bar
            var percent = Math.round(((countdown - timeLeft) / countdown) * 100);
            progressFill.style.width = percent + '%';
            
            // Update status message
            if (timeLeft > 0) {
                status.textContent = 'Please wait ' + timeLeft + ' seconds...';
           }
            
            timeLeft--;
            
            // Countdown complete
            if (timeLeft < 0) {
                clearInterval(intervalId);
                handleComplete();
            }
        }, 1000);
    });
    
    /**
     * Handle download completion
     */
    function handleComplete() {
        // Update UI
        btn.textContent = '✓ Download Ready';
        btn.className = 'tkm-btn green';
        btn.disabled = false;
        status.className = 'tkm-status success show';
        status.textContent = 'Thanks for Downloading!';
        progress.className = 'tkm-progress';
        
        // Show fallback link
        var fileUrl = btn.getAttribute('data-file');
        fallback.className = 'tkm-fallback show';
        fallback.innerHTML = 'If download doesn\'t start, <a href="' + fileUrl + '" target="_blank">click here</a>';
        
        // Track download
        if (tkmSettings.tracking === 'yes') {
            trackDownload();
        }
        
        // Track with Google Analytics (both GA4 and GTM)
        trackWithAnalytics();
        
        // Start download after short delay
        setTimeout(function() {
            triggerDownload();
        }, 500);
    }
    
    /**
     * Trigger the actual download
     */
    function triggerDownload() {
        var fileUrl = btn.getAttribute('data-file');
        if (!fileUrl) return;
        
        // Create invisible link and click it
        var link = document.createElement('a');
        link.href = fileUrl;
        link.download = '';
        link.style.display = 'none';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
    
    /**
     * Track download with AJAX (update counter)
     */
    function trackDownload() {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', tkmSettings.ajaxurl, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        
        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    var response = JSON.parse(xhr.responseText);
                    
                    // Update counter if successful
                    if (response.success && response.data && response.data.total && countEl) {
                        countEl.textContent = formatNumber(response.data.total);
                    }
                } catch (e) {
                    console.log('Counter update failed');
                }
            }
        };
        
        // Send tracking request
        var data = 'action=tkm_track_download';
        data += '&post_id=' + encodeURIComponent(tkmSettings.postId);
        data += '&nonce=' + encodeURIComponent(tkmSettings.nonce);
        
        xhr.send(data);
    }
    
    /**
     * Track with Google Analytics (GA4 & GTM)
     */
    function trackWithAnalytics() {
        var fileUrl = btn.getAttribute('data-file');
        var fileTitle = btn.getAttribute('data-title');
        
        // Track with Google Analytics 4 (gtag.js)
        if (typeof gtag === 'function') {
            gtag('event', 'file_download', {
                'event_category': 'Downloads',
                'event_label': fileTitle,
                'value': fileUrl,
                'file_name': fileTitle,
                'file_url': fileUrl
            });
        }
        
        // Track with Google Tag Manager (dataLayer)
        if (typeof window.dataLayer !== 'undefined') {
            window.dataLayer.push({
                'event': 'file_download',
                'eventCategory': 'Downloads',
                'eventAction': 'Download',
                'eventLabel': fileTitle,
                'fileName': fileTitle,
                'fileUrl': fileUrl
            });
        }
    }
    
    /**
     * Format number with commas
     */
    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }
    
    /**
     * Prevent accidental page leave during countdown
     */
    window.addEventListener('beforeunload', function(e) {
        if (isRunning && intervalId) {
            e.preventDefault();
            e.returnValue = '';
            return '';
        }
    });
    
})();
