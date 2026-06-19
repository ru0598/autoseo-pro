/* AutoSEO Pro – Admin JS (additional global utilities) */
(function ($) {
    'use strict';

    // Character counter for any input with data-maxlen
    $(document).on('input', '[data-autoseo-maxlen]', function () {
        var max = parseInt($(this).data('autoseo-maxlen'), 10);
        var len = $(this).val().length;
        var $counter = $(this).siblings('.autoseo-counter');
        if ($counter.length) {
            $counter.text(len + ' / ' + max)
                .css('color', len > max ? '#d63638' : len >= max * 0.75 ? '#00a32a' : '#888');
        }
    });

    // Dismiss notices
    $(document).on('click', '.autoseo-notice-dismiss', function () {
        $(this).closest('.notice').fadeOut(200);
    });

}(jQuery));
