(function($) {
    "use strict";

    // Input Validation and Interaction
    $('.input100').on('blur', function() {
        $(this).toggleClass('has-val', $(this).val().trim() !== "");
    });

    $('.validate-form').on('submit', function() {
        let isValid = true;
        $('.validate-input .input100').each(function() {
            if (!validate(this)) {
                showValidate(this);
                isValid = false;
            }
        });
        return isValid;
    });

    $('.validate-form .input100').on('focus', function() {
        hideValidate(this);
    });

    function validate(input) {
        const value = $(input).val().trim();
        const type = $(input).attr('type');
        const name = $(input).attr('name');
        if (type === 'email' || name === 'email') {
            return /^([a-zA-Z0-9_\-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{1,5}|[0-9]{1,3})(\]?)$/.test(value);
        }
        return value !== '';
    }

    function showValidate(input) {
        $(input).parent().addClass('alert-validate');
    }

    function hideValidate(input) {
        $(input).parent().removeClass('alert-validate');
    }

    // Show/Hide Password
    $('.btn-show-pass').on('click', function() {
        const input = $(this).next('input');
        const isPasswordVisible = input.attr('type') === 'text';
        input.attr('type', isPasswordVisible ? 'password' : 'text');
        $(this).toggleClass('active', !isPasswordVisible);
    });

    // Toggle Between Login and Register
document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('container');
    const registerBtn = document.getElementById('register');
    const loginBtn = document.getElementById('login');

    if (registerBtn && loginBtn) {
        registerBtn.addEventListener('click', () => {
            container.classList.add("right-panel-active");
        });

        loginBtn.addEventListener('click', () => {
            container.classList.remove("right-panel-active");
        });
    }
});

})(jQuery);
