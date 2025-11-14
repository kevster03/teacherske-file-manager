/**
 * CSV Import JavaScript
 * Handles the complete 3-step import workflow
 */
(function($) {
    'use strict';

    var CSVImport = {
        tempKey: null,
        totalRows: 0,
        headers: [],
        mapping: {},
        totalImported: 0,
        totalErrors: [],

        init: function() {
            this.log('CSV Import initialized');
            this.log('Debug mode: ' + (tkmCSV.debug ? 'ENABLED' : 'DISABLED'));
            this.log('User ID: ' + tkmCSV.userId);
            this.log('User Role: ' + tkmCSV.userRole);
            this.log('Can edit_posts: ' + tkmCSV.canEditPosts);
            this.log('AJAX URL: ' + tkmCSV.ajaxurl);
            this.log('Nonce: ' + tkmCSV.nonce.substring(0, 10) + '...');
            this.bindEvents();
            this.setupDebugButtons();
        },

        log: function(message, type) {
            type = type || 'info';
            var timestamp = new Date().toLocaleTimeString();
            var logMessage = '[' + timestamp + '] ' + message;

            console.log(logMessage);

            if (tkmCSV.debug && $('#tkm-debug-log').length) {
                var colorMap = {
                    'info': '#333',
                    'success': '#28a745',
                    'error': '#d63638',
                    'warning': '#ffc107'
                };
                var color = colorMap[type] || colorMap['info'];
                $('#tkm-debug-log').append(
                    '<div style="color:' + color + ';margin:3px 0;">' +
                    logMessage +
                    '</div>'
                );
                // Auto-scroll to bottom
                $('#tkm-debug-info').scrollTop($('#tkm-debug-info')[0].scrollHeight);
            }
        },

        setupDebugButtons: function() {
            var self = this;

            $('#tkm-copy-debug').on('click', function() {
                var debugText = $('#tkm-debug-info').text();
                navigator.clipboard.writeText(debugText).then(function() {
                    self.log('Debug info copied to clipboard!', 'success');
                    alert('Debug info copied! You can now paste it to share.');
                });
            });

            $('#tkm-clear-debug').on('click', function() {
                $('#tkm-debug-log').html('<em>Log cleared...</em>');
                self.log('Debug log cleared');
            });
        },

        bindEvents: function() {
            // Step 1: File upload
            $('#tkm-upload-form').on('submit', this.handleUpload.bind(this));
        },

        handleUpload: function(e) {
            e.preventDefault();

            this.log('=== UPLOAD STARTED ===', 'info');

            var formData = new FormData();
            var fileInput = $('#csv-file-input')[0];

            if (!fileInput.files[0]) {
                this.log('No file selected', 'error');
                alert('Please select a CSV file');
                return;
            }

            this.log('File selected: ' + fileInput.files[0].name + ' (' + fileInput.files[0].size + ' bytes)');

            formData.append('action', 'tkm_upload_csv');
            formData.append('nonce', $('#tkm_csv_nonce').val());
            formData.append('csv_file', fileInput.files[0]);

            this.log('FormData prepared with action: tkm_upload_csv');
            this.log('Nonce: ' + $('#tkm_csv_nonce').val().substring(0, 10) + '...');

            // Show loading
            $('#upload-btn').prop('disabled', true).text('Uploading...');
            this.log('Sending AJAX request to: ' + tkmCSV.ajaxurl);

            var self = this;

            $.ajax({
                url: tkmCSV.ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    self.log('AJAX Success - Raw response: ' + JSON.stringify(response), 'success');
                    self.onUploadSuccess(response);
                },
                error: function(xhr, status, error) {
                    self.log('AJAX Error - Status: ' + status, 'error');
                    self.log('Error: ' + error, 'error');
                    self.log('Response Text: ' + xhr.responseText, 'error');
                    self.log('Status Code: ' + xhr.status, 'error');
                    alert('Upload failed. Check debug log for details.');
                    $('#upload-btn').prop('disabled', false).text('Continue to Field Mapping →');
                }
            });
        },

        onUploadSuccess: function(response) {
            this.log('Processing upload response...');

            if (!response.success) {
                this.log('Upload failed: ' + (response.data.message || 'Unknown error'), 'error');
                alert(response.data.message || 'Upload failed');
                $('#upload-btn').prop('disabled', false).text('Continue to Field Mapping →');
                return;
            }

            this.log('Upload successful!', 'success');
            this.log('Headers found: ' + response.data.headers.join(', '));
            this.log('Total rows: ' + response.data.row_count);
            this.log('Temp key: ' + response.data.temp_key);

            // Store data
            this.headers = response.data.headers;
            this.totalRows = response.data.row_count;
            this.tempKey = response.data.temp_key;
            this.mapping = response.data.auto_mapping;

            this.log('Auto-mapping detected: ' + JSON.stringify(this.mapping));

            // Build mapping interface
            this.buildMappingInterface();

            // Update URL to step 2 (preserve post_type parameter)
            this.log('Redirecting to step 2...');
            var baseUrl = window.location.pathname + '?post_type=teacher_document&page=tkm-csv-import&step=2';
            if (tkmCSV.debug) {
                baseUrl += '&debug';
            }
            window.location.href = baseUrl;
        },

        buildMappingInterface: function() {
            var $container = $('#mapping-interface');

            if (!$container.length) {
                return; // Not on mapping page yet, will build after redirect
            }

            var html = '<form id="mapping-form">';
            html += '<input type="hidden" name="temp_key" value="' + this.tempKey + '">';
            html += '<input type="hidden" name="nonce" id="tkm_csv_import_nonce" value="' + tkmCSV.nonce + '">';

            // Info box
            html += '<div style="background:#f0f6fc;padding:15px;border-radius:6px;margin-bottom:20px;">';
            html += '<p style="margin:0;"><strong>CSV File:</strong> ' + this.totalRows + ' rows detected</p>';
            html += '</div>';

            // Mapping table
            html += '<table class="tkm-field-mapping-table">';
            html += '<thead><tr>';
            html += '<th>Plugin Field</th>';
            html += '<th>CSV Column</th>';
            html += '<th>Required</th>';
            html += '</tr></thead>';
            html += '<tbody>';

            var fields = [
                {name: 'title', label: 'Title', required: true},
                {name: 'file', label: 'File URL', required: true},
                {name: 'level', label: 'Education Level', required: true},
                {name: 'grade', label: 'Grade', required: true},
                {name: 'description', label: 'Description', required: false},
                {name: 'subject', label: 'Subject', required: false},
                {name: 'version', label: 'Version', required: false},
                {name: 'author', label: 'Author', required: false}
            ];

            var self = this;
            $.each(fields, function(i, field) {
                html += '<tr>';
                html += '<td><strong>' + field.label + '</strong></td>';
                html += '<td>';
                html += '<select name="mapping_' + field.name + '" class="mapping-select" data-field="' + field.name + '" style="width:100%;">';
                html += '<option value="">— Select Column —</option>';

                $.each(self.headers, function(j, header) {
                    var selected = (self.mapping[field.name] === header) ? ' selected' : '';
                    html += '<option value="' + header + '"' + selected + '>' + header + '</option>';
                });

                html += '</select>';
                html += '</td>';
                html += '<td>' + (field.required ? '<span class="tkm-required">Required</span>' : 'Optional') + '</td>';
                html += '</tr>';
            });

            html += '</tbody></table>';

            // Preview section
            html += '<div class="tkm-preview-rows">';
            html += '<h3 style="margin-top:0;">Preview (Headers)</h3>';
            html += '<code>' + self.headers.join(', ') + '</code>';
            html += '</div>';

            // Buttons
            html += '<p>';
            html += '<a href="' + window.location.pathname + '?post_type=teacher_document&page=tkm-csv-import" class="button">← Back</a> ';
            html += '<button type="submit" class="button button-primary button-large">Start Import →</button>';
            html += '</p>';

            html += '</form>';

            $container.html(html);

            // Bind form submit
            $('#mapping-form').on('submit', this.startImport.bind(this));
        },

        startImport: function(e) {
            e.preventDefault();

            // Collect mapping
            this.mapping = {};
            $('.mapping-select').each(function() {
                var field = $(this).data('field');
                var column = $(this).val();
                if (column) {
                    CSVImport.mapping[field] = column;
                }
            });

            // Validate required fields
            var required = ['title', 'file', 'level', 'grade'];
            var missing = [];

            $.each(required, function(i, field) {
                if (!CSVImport.mapping[field]) {
                    missing.push(field);
                }
            });

            if (missing.length > 0) {
                alert('Please map these required fields: ' + missing.join(', '));
                return;
            }

            // Reset counters
            this.totalImported = 0;
            this.totalErrors = [];

            // Redirect to step 3 (preserve post_type parameter)
            var baseUrl = window.location.pathname + '?post_type=teacher_document&page=tkm-csv-import&step=3';
            if (tkmCSV.debug) {
                baseUrl += '&debug';
            }
            window.location.href = baseUrl;
        },

        processBatch: function(batchStart) {
            batchStart = batchStart || 0;

            this.log('=== BATCH IMPORT STARTED (batch ' + batchStart + ') ===', 'info');

            var self = this;

            $.ajax({
                url: tkmCSV.ajaxurl,
                type: 'POST',
                data: {
                    action: 'tkm_batch_import',
                    nonce: tkmCSV.nonce,
                    temp_key: this.tempKey,
                    mapping: JSON.stringify(this.mapping),
                    batch_start: batchStart
                },
                success: function(response) {
                    self.log('Batch response: ' + JSON.stringify(response), 'success');
                    self.onBatchComplete(response);
                },
                error: function(xhr, status, error) {
                    self.log('Batch Error - Status: ' + status, 'error');
                    self.log('Error: ' + error, 'error');
                    self.log('Response: ' + xhr.responseText, 'error');
                    $('#import-status-text').text('Import failed. Check debug log.');
                }
            });
        },

        onBatchComplete: function(response) {
            if (!response.success) {
                $('#import-status-text').text('Error: ' + (response.data.message || 'Unknown error'));
                return;
            }

            var data = response.data;

            // Update totals
            this.totalImported += data.imported;
            this.totalErrors = this.totalErrors.concat(data.errors);

            // Update progress
            var progress = Math.round((data.total_processed / this.totalRows) * 100);
            $('#import-progress').css('width', progress + '%').text(progress + '%');

            // Update status
            $('#import-status-text').text(
                'Processing: ' + data.total_processed + ' / ' + this.totalRows + ' rows (' +
                this.totalImported + ' imported, ' + this.totalErrors.length + ' errors)'
            );

            // Continue if more batches
            if (data.has_more) {
                this.processBatch(data.next_batch_start);
            } else {
                this.showResults();
            }
        },

        showResults: function() {
            $('#import-status-text').text('Import Complete!');
            $('#import-progress').css('width', '100%').text('100%');

            var html = '<div class="tkm-import-results">';

            // Success box
            html += '<div class="tkm-result-box success">';
            html += '<div class="tkm-result-number" style="color:#28a745;">' + this.totalImported + '</div>';
            html += '<div class="tkm-result-label">Successfully Imported</div>';
            html += '</div>';

            // Errors box
            html += '<div class="tkm-result-box ' + (this.totalErrors.length > 0 ? 'error' : '') + '">';
            html += '<div class="tkm-result-number" style="color:' + (this.totalErrors.length > 0 ? '#d63638' : '#666') + ';">' + this.totalErrors.length + '</div>';
            html += '<div class="tkm-result-label">Errors</div>';
            html += '</div>';

            // Total box
            html += '<div class="tkm-result-box">';
            html += '<div class="tkm-result-number">' + this.totalRows + '</div>';
            html += '<div class="tkm-result-label">Total Rows</div>';
            html += '</div>';

            html += '</div>';

            // Error details
            if (this.totalErrors.length > 0) {
                html += '<div style="background:#fff9e6;padding:15px;border-left:4px solid #ffc107;margin-top:20px;max-height:200px;overflow-y:auto;">';
                html += '<h3 style="margin-top:0;">Error Details</h3>';
                html += '<ul style="margin:0;padding-left:20px;">';
                $.each(this.totalErrors, function(i, error) {
                    html += '<li>' + error + '</li>';
                });
                html += '</ul>';
                html += '</div>';
            }

            // Actions
            html += '<p style="margin-top:30px;">';
            html += '<a href="' + tkmCSV.documentsUrl + '" class="button button-primary button-large">View Imported Documents</a> ';
            html += '<a href="' + window.location.pathname + '?post_type=teacher_document&page=tkm-csv-import" class="button button-large">Import Another File</a>';
            html += '</p>';

            $('#import-results').html(html).show();
        }
    };

    // Initialize when document ready
    $(document).ready(function() {
        CSVImport.init();

        // Auto-start import on step 3
        var urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('step') === '3') {
            // Get data from sessionStorage
            var tempKey = sessionStorage.getItem('tkm_temp_key');
            var mapping = sessionStorage.getItem('tkm_mapping');
            var totalRows = sessionStorage.getItem('tkm_total_rows');

            if (tempKey && mapping) {
                CSVImport.tempKey = tempKey;
                CSVImport.mapping = JSON.parse(mapping);
                CSVImport.totalRows = parseInt(totalRows);

                // Start processing
                setTimeout(function() {
                    CSVImport.processBatch(0);
                }, 500);
            }
        }

        // Save to sessionStorage on step 2 form submit
        $('#mapping-form').on('submit', function() {
            sessionStorage.setItem('tkm_temp_key', CSVImport.tempKey);
            sessionStorage.setItem('tkm_mapping', JSON.stringify(CSVImport.mapping));
            sessionStorage.setItem('tkm_total_rows', CSVImport.totalRows);
        });
    });

})(jQuery);
