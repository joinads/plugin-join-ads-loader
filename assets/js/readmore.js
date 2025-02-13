jQuery(document).ready(function($) {
    $('.joinads-readmore-button').on('click', function(e) {
        e.preventDefault();
        
        const $button = $(this);
        const $hiddenContent = $button.closest('.joinads-readmore-block').next('.joinads-hidden-content');
        
        $hiddenContent.slideDown(400);
        $button.parent('.joinads-readmore-block').fadeOut();
        
    
    });
});