jQuery(document).ready(function($) {
    $('tr[data-plugin="jcc-payment-gateway-forc-wc/class-wc-jcc-plugin.php"] .update-message').each(function(){
        var updateMessage = $(this).find('new update testing release');
        if (updateMessage.length){
            updateMessage.prepend('IMPORTANT PLEASE CHECK FHDHS');
        }
    });
});