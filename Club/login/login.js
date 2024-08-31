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
    const container = document.getElementById('container');
    const registerBtn = document.getElementById('register');
    const loginBtn = document.getElementById('login');

    if (registerBtn && loginBtn && container) {
        registerBtn.addEventListener('click', () => {
            container.classList.add("active");
        });

        loginBtn.addEventListener('click', () => {
            container.classList.remove("active");
        });
    }

})(jQuery);
