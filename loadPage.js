// Add this script to your dashboard page
function loadPage(page) {
    $.ajax({
        url: page,
        type: 'GET',
        success: function(data) {
            $('.content').html(data);
        },
        error: function() {
            $('.content').html('<h2>Error loading page</h2>');
        }
    });
}