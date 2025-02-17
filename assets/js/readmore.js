jQuery(document).ready(function($) {
    $('.joinads-readmore-button').on('click', function(e) {
        e.preventDefault();
        
        const $button = $(this);
        const $hiddenContent = $button.closest('.joinads-readmore-block').next('.joinads-hidden-content');
        
        if ($hiddenContent.length) {
            $hiddenContent.slideDown(400, function() {
                // Dispara evento de redimensionamento para recalcular anúncios
                $(window).trigger('resize');
            });
            $button.parent('.joinads-readmore-block').fadeOut();
        }
    });
});