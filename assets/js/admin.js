/**
 * Admin JavaScript
 * 
 * Handles:
 * - Media library file picker
 * - Dynamic grade dropdown based on level selection
 * - Dynamic subject dropdown based on level selection
 * - File info auto-update
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        
        // ============================================
        // Media Library File Picker
        // ============================================
        
        var fileFrame;
        var $fileInput = $('#tkm_file');
        var $fileButton = $('#tkm_file_button');
        var $clearButton = $('#tkm_clear_file');
        
        // Open media library on button click
        $fileButton.on('click', function(e) {
            e.preventDefault();
            
            // If frame already exists, open it
            if (fileFrame) {
                fileFrame.open();
                return;
            }
            
            // Create new media frame
            fileFrame = wp.media({
                title: 'Select Document File',
                button: {
                    text: 'Use This File'
                },
                multiple: false,
                library: {
                    type: ['application', 'image', 'video', 'audio'] // Allow all file types
                }
            });
            
            // Handle file selection
            fileFrame.on('select', function() {
                var attachment = fileFrame.state().get('selection').first().toJSON();
                
                // Set file URL
                $fileInput.val(attachment.url);
                
                // Auto-detect file extension
                var ext = getFileExtension(attachment.url);
                $('#tkm_file_ext').val(ext);
                
                // Set file size if available
                if (attachment.filesizeInBytes) {
                    $('#tkm_file_size').val(attachment.filesizeInBytes);
                }
                
                // Update UI
                updateFileInfo(attachment);
                
                // Show clear button
                $clearButton.show();
            });
            
            // Open frame
            fileFrame.open();
        });
        
        // Clear file selection
        $clearButton.on('click', function(e) {
            e.preventDefault();
            
            $fileInput.val('');
            $('#tkm_file_ext').val('');
            $('#tkm_file_size').val('');
            
            $('.tkm-file-info').remove();
            $(this).hide();
        });
        
        // ============================================
        // Dynamic Grade Dropdown
        // ============================================
        
        var $levelSelect = $('#tkm_level');
        var $gradeSelect = $('#tkm_grade');
        var currentGrade = tkmCurrentGrade || '';
        
        // Initialize grade dropdown on page load
        if ($levelSelect.val()) {
            updateGradeDropdown($levelSelect.val(), currentGrade);
        }
        
        // Update grade dropdown when level changes
        $levelSelect.on('change', function() {
            var selectedLevel = $(this).val();
            
            if (!selectedLevel) {
                $gradeSelect.html('<option value="">— Select Level First —</option>');
                $gradeSelect.prop('disabled', true);
                return;
            }
            
            updateGradeDropdown(selectedLevel, '');
            $gradeSelect.prop('disabled', false);
            
            // Also update subjects when level changes
            updateSubjectDropdown(selectedLevel, '');
        });
        
        /**
         * Update grade dropdown options based on level
         */
        function updateGradeDropdown(levelKey, selectedGrade) {
            if (!tkmLevels || !tkmLevels[levelKey]) {
                return;
            }
            
            var grades = tkmLevels[levelKey].grades;
            var options = '<option value="">— Select Grade —</option>';
            
            $.each(grades, function(index, grade) {
                var selected = (grade === selectedGrade) ? ' selected' : '';
                options += '<option value="' + escapeHtml(grade) + '"' + selected + '>' + escapeHtml(grade) + '</option>';
            });
            
            $gradeSelect.html(options);
        }
        
        // ============================================
        // Dynamic Subject Dropdown
        // ============================================
        
        var $subjectSelect = $('#tkm_subject');
        var currentSubject = tkmCurrentSubject || '';
        
        // Initialize subject dropdown on page load
        if ($levelSelect.val()) {
            updateSubjectDropdown($levelSelect.val(), currentSubject);
        }
        
        /**
         * Update subject dropdown options based on level
         */
        function updateSubjectDropdown(levelKey, selectedSubject) {
            if (!tkmSubjectsByLevel || !tkmSubjectsByLevel[levelKey]) {
                $subjectSelect.html('<option value="">— No subjects for this level —</option>');
                $subjectSelect.prop('disabled', true);
                return;
            }
            
            var subjects = tkmSubjectsByLevel[levelKey];
            var options = '<option value="">— Select Subject —</option>';
            
            $.each(subjects, function(index, subject) {
                var selected = (subject === selectedSubject) ? ' selected' : '';
                options += '<option value="' + escapeHtml(subject) + '"' + selected + '>' + escapeHtml(subject) + '</option>';
            });
            
            $subjectSelect.html(options);
            $subjectSelect.prop('disabled', false);
        }
        
        // ============================================
        // Helper Functions
        // ============================================
        
        /**
         * Get file extension from URL
         */
        function getFileExtension(url) {
            var parts = url.split('.');
            return parts.length > 1 ? parts.pop().toLowerCase() : '';
        }
        
        /**
         * Update file info display
         */
        function updateFileInfo(attachment) {
            var ext = getFileExtension(attachment.url);
            var size = attachment.filesizeHumanReadable || '';
            
            var $existingInfo = $('.tkm-file-info');
            if ($existingInfo.length) {
                $existingInfo.remove();
            }
            
            var html = '<div class="tkm-file-info">';
            
            if (ext) {
                html += '<span class="tkm-badge tkm-badge-type">' + ext.toUpperCase() + '</span>';
            }
            
            if (size) {
                html += '<span class="tkm-badge tkm-badge-size">' + escapeHtml(size) + '</span>';
            }
            
            html += '</div>';
            
            $('.tkm-file-wrapper').after(html);
        }
        
        /**
         * Escape HTML to prevent XSS
         */
        function escapeHtml(text) {
            var map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        }
        
        // ============================================
        // Auto-fill Version with Current Year
        // ============================================
        
        var $versionSelect = $('select[name="tkm_version"]');
        
        if ($versionSelect.length && !$versionSelect.val()) {
            var currentYear = new Date().getFullYear();
            var targetOption = currentYear + ' Edition';
            
            $versionSelect.find('option').each(function() {
                if ($(this).text() === targetOption) {
                    $(this).prop('selected', true);
                    return false;
                }
            });
        }
        
        // ============================================
        // Keyboard Shortcuts
        // ============================================
        
        $(document).on('keydown', function(e) {
            // Ctrl+S or Cmd+S to trigger quick save
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                
                if (typeof tkmQuickSave === 'function') {
                    tkmQuickSave();
                } else {
                    // Fallback to standard save
                    $('#publish').click();
                }
            }
            
            // Ctrl+Enter to publish
            if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                e.preventDefault();
                $('#publish').click();
            }
        });
        
        // ============================================
        // Form Validation
        // ============================================
        
        $('form#post').on('submit', function(e) {
            var hasFile = $fileInput.val().trim() !== '';
            var hasLevel = $levelSelect.val() !== '';
            var hasGrade = $gradeSelect.val() !== '';
            
            if (!hasFile || !hasLevel || !hasGrade) {
                e.preventDefault();
                
                var message = 'Please fill in all required fields:\n';
                if (!hasFile) message += '- File Upload\n';
                if (!hasLevel) message += '- Education Level\n';
                if (!hasGrade) message += '- Grade\n';
                
                alert(message);
                
                // Focus first empty required field
                if (!hasFile) {
                    $fileInput.focus();
                } else if (!hasLevel) {
                    $levelSelect.focus();
                } else if (!hasGrade) {
                    $gradeSelect.focus();
                }
                
                return false;
            }
        });
        
        // ============================================
        // Visual Feedback
        // ============================================
        
        // Highlight required empty fields on blur
        $fileInput.add($levelSelect).add($gradeSelect).on('blur', function() {
            if ($(this).val() === '') {
                $(this).css('border-color', '#d63638');
            } else {
                $(this).css('border-color', '');
            }
        });
        
    });
    
})(jQuery);
