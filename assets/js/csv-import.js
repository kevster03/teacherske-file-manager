/**
 * CSV Import JavaScript - Single Page Approach
 * All steps happen on one page - no redirects
 */
(function($) {
    'use strict';

    var CSVImport = {
        // Data storage
        tempKey: null,
        totalRows: 0,
        headers: [],
        mapping: {},
        totalImported: 0,
        totalErrors: [],
        currentStep: 1,

        init: function() {
            this.bindEvents();
        },

        bindEvents: function() {
            // Step 1: File upload
            $('#tkm-upload-form').on('submit', this.handleUpload.bind(this));

            // File selection
            $('#select-file-btn').on('click', function() {
                $('#csv-file-input').click();
            });

            $('#csv-file-input').on('change', this.handleFileSelect.bind(this));

            // Drag and drop
            var $dropZone = $('#tkm-drop-zone');
            $dropZone.on('dragover', function(e) {
                e.preventDefault();
                $(this).addClass('dragover');
            });

            $dropZone.on('dragleave', function() {
                $(this).removeClass('dragover');
            });

            $dropZone.on('drop', this.handleFileDrop.bind(this));
        },

        handleFileSelect: function(e) {
            var file = e.target.files[0];
            if (file) {
                this.displayFileInfo(file);
            }
        },

        handleFileDrop: function(e) {
            e.preventDefault();
            $('#tkm-drop-zone').removeClass('dragover');

            var files = e.originalEvent.dataTransfer.files;
            if (files.length > 0) {
                $('#csv-file-input')[0].files = files;
                this.displayFileInfo(files[0]);
            }
        },

        displayFileInfo: function(file) {
            // Validate file type
            if (!file.name.endsWith('.csv')) {
                alert('Please select a CSV file');
                return;
            }

            // Validate file size (10MB)
            if (file.size > 10485760) {
                alert('File is too large. Maximum size is 10MB.');
                return;
            }

            // Show file info
            $('#file-name').text(file.name);
            $('#file-size').text((file.size / 1024).toFixed(2) + ' KB');
            $('#file-info').show();
            $('#upload-btn').prop('disabled', false);
        },

        handleUpload: function(e) {
            e.preventDefault();

            var fileInput = $('#csv-file-input')[0];
            if (!fileInput.files[0]) {
                alert('Please select a CSV file');
                return;
            }

            var formData = new FormData();
            formData.append('action', 'tkm_upload_csv');
            formData.append('nonce', $('#tkm_csv_nonce').val());
            formData.append('csv_file', fileInput.files[0]);

            // Show loading
            $('#upload-btn').prop('disabled', true).text('Uploading...');

            $.ajax({
                url: tkmCSV.ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: this.onUploadSuccess.bind(this),
                error: function() {
                    alert('Upload failed. Please try again.');
                    $('#upload-btn').prop('disabled', false).text('Continue to Field Mapping →');
                }
            });
        },

        onUploadSuccess: function(response) {
            if (!response.success) {
                alert(response.data.message || 'Upload failed');
                $('#upload-btn').prop('disabled', false).text('Continue to Field Mapping →');
                return;
            }

            // Store data in memory
            this.headers = response.data.headers;
            this.totalRows = response.data.row_count;
            this.tempKey = response.data.temp_key;
            this.mapping = response.data.auto_mapping || {};

            // Build mapping interface
            this.buildMappingInterface();

            // Go to step 2 (NO REDIRECT - just show/hide)
            this.goToStep(2);
        },

        buildMappingInterface: function() {
            var html = '';

            // Info box
            html += '<div style="background:#f0f6fc;padding:15px;border-radius:6px;margin-bottom:20px;">';
            html += '<p style="margin:0;"><strong>CSV File:</strong> ' + this.totalRows + ' rows detected</p>';
            html += '<p style="margin:5px 0 0;"><strong>Columns:</strong> ' + this.headers.join(', ') + '</p>';
            html += '</div>';

            // Mapping table
            html += '<form id="mapping-form">';
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
                {name: 'category', label: 'Category', required: false},
                {name: 'featured_image', label: 'Featured Image URL', required: false},
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

            // Buttons
            html += '<p>';
            html += '<button type="button" class="button" id="back-to-upload">← Back</button> ';
            html += '<button type="submit" class="button button-primary button-large">Start Import →</button>';
            html += '</p>';

            html += '</form>';

            $('#mapping-interface').html(html);

            // Bind events
            $('#mapping-form').on('submit', this.startImport.bind(this));
            $('#back-to-upload').on('click', function() {
                CSVImport.goToStep(1);
            });
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

            // Go to step 3 and start processing
            this.goToStep(3);

            // Start import after a brief delay
            setTimeout(function() {
                CSVImport.processBatch(0);
            }, 500);
        },

        processBatch: function(batchStart) {
            batchStart = batchStart || 0;

            $('#import-status-text').text('Processing...');

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
                success: this.onBatchComplete.bind(this),
                error: function() {
                    $('#import-status-text').text('Import failed. Please try again.');
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
            html += '<button type="button" class="button button-large" id="import-another">Import Another File</button>';
            html += '</p>';

            $('#import-results').html(html).show();

            // Bind import another button
            $('#import-another').on('click', function() {
                // Reset everything
                CSVImport.tempKey = null;
                CSVImport.headers = [];
                CSVImport.mapping = {};
                CSVImport.totalRows = 0;
                CSVImport.totalImported = 0;
                CSVImport.totalErrors = [];

                // Reset form
                $('#tkm-upload-form')[0].reset();
                $('#file-info').hide();
                $('#upload-btn').prop('disabled', true);

                // Go back to step 1
                CSVImport.goToStep(1);
            });
        },

        goToStep: function(step) {
            // Hide all steps
            $('.tkm-step-content').hide();

            // Show target step
            $('#tkm-step-' + step).show();

            // Update step indicators
            $('.tkm-step').removeClass('active completed');

            $('.tkm-step').each(function() {
                var stepNum = parseInt($(this).data('step'));
                if (stepNum < step) {
                    $(this).addClass('completed');
                } else if (stepNum === step) {
                    $(this).addClass('active');
                }
            });

            this.currentStep = step;

            // Scroll to top
            $('html, body').animate({ scrollTop: 0 }, 300);
        }
    };

    // Initialize when document ready
    $(document).ready(function() {
        CSVImport.init();
    });

})(jQuery);
