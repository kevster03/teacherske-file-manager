/**
 * Frontend JavaScript - Redesigned Download Flow
 * Clean, minimal, modern - exactly as specified
 */
(function() {
    'use strict';

    // Check if settings exist
    if (typeof tkmSettings === 'undefined') return;

    // Initialize PDF Preview if enabled
    if (tkmSettings.pdfPreview && tkmSettings.pdfPreview.enabled) {
        initPdfPreview();
    }

    // Get elements
    var btn = document.getElementById('tkm-btn');
    var btnText = document.getElementById('tkm-btn-text');
    var btnProgress = document.getElementById('tkm-btn-progress');
    var status = document.getElementById('tkm-status');
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

        // Show status message
        status.textContent = 'Preparing your secure download link...please wait';

        // Start countdown
        var timeLeft = countdown;

        intervalId = setInterval(function() {
            // Update button text with countdown (show seconds)
            btnText.textContent = timeLeft + 's';

            // Update progress bar (fills from left to right)
            var percent = ((countdown - timeLeft) / countdown) * 100;
            btnProgress.style.width = percent + '%';

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
        // Hide status message
        status.textContent = '';

        // Update button - green with checkmark
        btn.className = 'tkm-btn green';
        btnText.textContent = '✓ Link Ready';
        btn.disabled = false;

        // Hide progress bar
        btnProgress.style.width = '0%';

        // Show fallback message
        fallback.innerHTML = 'If your download doesn\'t appear automatically <a id="manual-download-link">click here</a>';

        // Track download
        if (tkmSettings.tracking === 'yes') {
            trackDownload();
        }

        // Track with analytics
        trackWithAnalytics();

        // Trigger auto-download
        setTimeout(function() {
            triggerDownload();
        }, 500);

        // Add click handler to fallback link
        var manualLink = document.getElementById('manual-download-link');
        if (manualLink) {
            manualLink.addEventListener('click', function(e) {
                e.preventDefault();
                triggerDownload();
            });
        }
    }

    /**
     * Trigger the actual download
     */
    function triggerDownload() {
        var fileUrl = btn.getAttribute('data-file');

        if (!fileUrl) {
            console.error('Download failed: No file URL found');
            fallback.innerHTML = '<span style="color:red;">Error: File URL not found. Please contact the administrator.</span>';
            return;
        }

        console.log('Triggering download for:', fileUrl);

        // Method 1: Try creating invisible link (works for most browsers)
        try {
            var link = document.createElement('a');
            link.href = fileUrl;
            link.download = fileUrl.split('/').pop(); // Extract filename from URL
            link.target = '_blank'; // Open in new tab as fallback
            link.style.display = 'none';
            document.body.appendChild(link);

            // Trigger click
            if (link.click) {
                link.click();
            } else if (document.createEvent) {
                // Fallback for older browsers
                var event = document.createEvent('MouseEvents');
                event.initEvent('click', true, true);
                link.dispatchEvent(event);
            }

            // Clean up
            setTimeout(function() {
                document.body.removeChild(link);
            }, 100);

            console.log('Download triggered successfully');
        } catch (e) {
            console.error('Download trigger failed:', e);
            // Method 2: Direct window.open fallback
            window.open(fileUrl, '_blank');
        }
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

                    // Update counter if successful (check for 'total' property, not truthy value)
                    if (response.success && response.data && 'total' in response.data && countEl) {
                        var newCount = parseInt(response.data.total) || 0;
                        countEl.textContent = formatNumber(newCount);
                        console.log('Download count updated to:', newCount);
                    } else {
                        console.log('Counter update skipped. Response:', response);
                    }
                } catch (e) {
                    console.log('Counter update failed:', e);
                }
            } else {
                console.log('AJAX request failed with status:', xhr.status);
            }
        };

        xhr.onerror = function() {
            console.log('Network error during download tracking');
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

    /**
     * Initialize PDF Preview
     */
    function initPdfPreview() {
        // Check if PDF.js is loaded
        if (typeof pdfjsLib === 'undefined') {
            console.log('PDF.js not loaded, preview disabled');
            return;
        }

        var fileUrl = tkmSettings.pdfPreview.fileUrl;
        var maxPages = parseInt(tkmSettings.pdfPreview.previewPages) || 2;

        // Set PDF.js worker
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

        var loadingEl = document.getElementById('tkm-preview-loading');
        var pagesEl = document.getElementById('tkm-preview-pages');
        var blurEl = document.getElementById('tkm-preview-blur');

        if (!loadingEl || !pagesEl || !blurEl) {
            console.log('Preview elements not found');
            return;
        }

        // Load PDF
        var loadingTask = pdfjsLib.getDocument(fileUrl);

        loadingTask.promise.then(function(pdf) {
            console.log('PDF loaded, total pages:', pdf.numPages);

            var totalPages = pdf.numPages;
            var pagesToShow = Math.min(maxPages, totalPages);
            var hasMorePages = totalPages > pagesToShow;

            // Render clear pages
            var renderPromises = [];
            for (var i = 1; i <= pagesToShow; i++) {
                renderPromises.push(renderPage(pdf, i, pagesEl, false));
            }

            // Wait for all clear pages to render
            Promise.all(renderPromises).then(function() {
                // Hide loading, show pages
                loadingEl.style.display = 'none';
                pagesEl.style.display = 'block';

                // If there are more pages, render one blurred page
                if (hasMorePages) {
                    var blurPageNumber = pagesToShow + 1;
                    renderPage(pdf, blurPageNumber, blurEl, true).then(function() {
                        blurEl.style.display = 'block';
                    });
                }
            }).catch(function(error) {
                console.error('Error rendering pages:', error);
                showPreviewError(loadingEl);
            });
        }).catch(function(error) {
            console.error('Error loading PDF:', error);
            showPreviewError(loadingEl);
        });
    }

    /**
     * Render a single PDF page
     */
    function renderPage(pdf, pageNumber, container, isBlurred) {
        return pdf.getPage(pageNumber).then(function(page) {
            var scale = 1.5;
            var viewport = page.getViewport({ scale: scale });

            // Create canvas
            var canvas = document.createElement('canvas');
            var context = canvas.getContext('2d');
            canvas.height = viewport.height;
            canvas.width = viewport.width;

            // Style canvas
            canvas.style.width = '100%';
            canvas.style.height = 'auto';
            canvas.style.display = 'block';
            canvas.style.marginBottom = '10px';
            canvas.style.borderRadius = '8px';
            canvas.style.boxShadow = '0 2px 8px rgba(0,0,0,0.1)';

            // For blurred canvas, use the existing canvas in the blur container
            if (isBlurred) {
                var blurCanvas = document.getElementById('tkm-blur-canvas');
                if (blurCanvas) {
                    blurCanvas.height = viewport.height;
                    blurCanvas.width = viewport.width;
                    context = blurCanvas.getContext('2d');
                    canvas = blurCanvas;
                }
            } else {
                container.appendChild(canvas);
            }

            // Render page
            var renderContext = {
                canvasContext: context,
                viewport: viewport
            };

            return page.render(renderContext).promise;
        });
    }

    /**
     * Show preview error message
     */
    function showPreviewError(loadingEl) {
        loadingEl.innerHTML = '<span style="color:#d32f2f;font-size:16px;">Unable to load PDF preview. You can still download the file below.</span>';
    }

})();
