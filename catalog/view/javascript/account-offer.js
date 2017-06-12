$(function() {

    /**
     * Offer image upload
     */
    var by_image  =  initUploader('by_image',  'index.php?route=multimerch/account_offer/uploadImage', 'mini', false, false);
    var for_image =  initUploader('for_image', 'index.php?route=multimerch/account_offer/uploadImage', 'mini', false, false);

    $("#offer_by_image").delegate(".ms-remove", "click", function() {
        var par = $(this).parent();
        par.addClass('hidden');
        par.find('input').val('');
        par.parent().find('.dragndropmini').show();
        par.parent().find('.dragndropmini').removeClass('hidden');
    });

    $("#offer_for_image").delegate(".ms-remove", "click", function() {
        var par = $(this).parent();
        par.addClass('hidden');
        par.find('input').val('');
        par.parent().find('.dragndropmini').show();
        par.parent().find('.dragndropmini').removeClass('hidden');
    });

});