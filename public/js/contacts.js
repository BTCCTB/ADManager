$(document).ready(function(){
    $('.contactSearch').keyup(function(){

        // Search text
        var text = $(this).val().toLowerCase();

        // Hide all contactCard class element
        $('.contactCard').hide();

        // Search multi-term in full name
        $('.contactCard .contactLastname, .contactCard .contactFirstname').each(function(){
            var query = text.split(" ");
            for (i = 0; i < query.length; i++) {
                if ($(this).text().toLowerCase().indexOf("" + query[i] + "") != -1) {
                    $(this).closest('.contactCard').show();
                }
            }
        });
        // Simple search in phone, mobile, function, skype & email
        $('.contactCard .contactMobile, .contactCard .contactPhone, .contactCard .contactFunction, .contactCard .contactEmail, .contactCard .contactSkype').each(function(){
                if ($(this).text().toLowerCase().indexOf("" + text + "") != -1) {
                    $(this).closest('.contactCard').show();
                }
        });
    });
});