(function($) {
    "use strict";

    // Show/Hide Password
    $(document).on('click', '.btn-show-pass', function() {
        const input = $(this).siblings('input'); // Use siblings if the button is not immediately next to the input
        const isPasswordVisible = input.attr('type') === 'text';
        input.attr('type', isPasswordVisible ? 'password' : 'text');
        $(this).toggleClass('active', !isPasswordVisible);
    });

    // Toggle between login and registration forms


})(jQuery);
