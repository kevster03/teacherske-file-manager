/* ========================================
   ADMIN-AJAX.JS - Quick Save Without Reload
   ======================================== */

(function($) {
    'use strict';
    
    // Make function globally available for keyboard shortcut
    window.tkmQuickSave = function() {
        if (typeof tkmAjax === 'undefined') {
            console.error('TKM Ajax not initialized');
            return;
        }
        
        var $publishButton = $('#publish');
        var originalText = $publishButton.val();
        
        // Disable button and show loading
        $publishButton.prop('disabled', true).val(tkmAjax.saveText);
        
        // Collect form data
        var formData = {
            action: 'tkm_quick_save',
            nonce: tkmAjax.nonce,
            post_id: tkmAjax.postId,
            post_title: $('#title').val(),
            tkm_description: $('[name="tkm_description"]').val(),
            tkm_file: $('[name="tkm_file"]').val(),
            tkm_level: $('[name="tkm_level"]').val(),
            tkm_grade: $('[name="tkm_grade"]').val(),
            tkm_version: $('[name="tkm_version"]').val(),
            tkm_author_select: $('[name="tkm_author_select"]').val()
        };
        
        // Send AJAX request
        $.post(tkmAjax.ajaxurl, formData, function(response) {
            // Re-enable button
            $publishButton.prop('disabled', false).val(originalText);
            
            if (response.success) {
                // Show success notification
                showNotification(tkmAjax.savedText, 'success');
            } else {
                // Show error notification
                showNotification(tkmAjax.errorText + ': ' + response.data, 'error');
            }
        }).fail(function() {
            $publishButton.prop('disabled', false).val(originalText);
            showNotification(tkmAjax.errorText, 'error');
        });
    };
    
    /**
     * Show temporary notification
     */
    function showNotification(message, type) {
        var $notification = $('<div class="tkm-save-indicator' + (type === 'error' ? ' tkm-error' : '') + '">' + message + '</div>');
        
        $('body').append($notification);
        
        setTimeout(function() {
            $notification.fadeOut(300, function() {
                $(this).remove();
            });
        }, 2000);
    }
    
})(jQuery);


