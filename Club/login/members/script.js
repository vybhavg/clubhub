$(document).ready(function() {
    // Function to send AJAX requests
    function sendAjaxRequest(formId, url) {
        var formData = $(formId).serialize();
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            success: function(response) {
                $('#data-container').html(response); // Update the data container with the response
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error: ' + status + error);
            }
        });
    }

    // Event handlers for button clicks
    $('#select-branch-btn').click(function() {
        sendAjaxRequest('#branch-form', 'fetch_data.php?action=select_branch');
    });

    $('#select-club-btn').click(function() {
        sendAjaxRequest('#club-form', 'fetch_data.php?action=select_club');
    });

    $('#add-event-btn').click(function() {
        sendAjaxRequest('#event-form', 'fetch_data.php?action=add_event');
    });

    $('#add-recruitment-btn').click(function() {
        sendAjaxRequest('#recruitment-form', 'fetch_data.php?action=add_recruitment');
    });
});
