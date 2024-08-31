

    // Show/Hide Password
    $('.btn-show-pass').on('click', function() {
        const input = $(this).next('input');
        const isPasswordVisible = input.attr('type') === 'text';
        input.attr('type', isPasswordVisible ? 'password' : 'text');
        $(this).toggleClass('active', !isPasswordVisible);
    });

    const container = document.getElementById('container');
    const registerBtn = document.getElementById('register');
    const loginBtn = document.getElementById('login');

    registerBtn.addEventListener('click', () => {
        container.classList.add("active");
    });

    loginBtn.addEventListener('click', () => {
        container.classList.remove("active");
    });

})(jQuery);
