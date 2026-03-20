// login.js - JavaScript for login page

document.addEventListener('DOMContentLoaded', function() {
    // Toggle Password Visibility
    const togglePassword = document.getElementById('togglePassword');
    const password = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');
    
    if (togglePassword && password && toggleIcon) {
        togglePassword.addEventListener('click', function() {
            if (password.type === 'password') {
                password.type = 'text';
                toggleIcon.classList.remove('bi-eye');
                toggleIcon.classList.add('bi-eye-slash');
            } else {
                password.type = 'password';
                toggleIcon.classList.remove('bi-eye-slash');
                toggleIcon.classList.add('bi-eye');
            }
        });
    }
    
    // Optional: Auto-dismiss alerts after 5 seconds
    const alert = document.querySelector('.alert');
    if (alert) {
        setTimeout(function() {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    }
    
    // Optional: Form validation before submit
    const loginForm = document.querySelector('form');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            const email = document.querySelector('input[name="emel"]');
            const password = document.querySelector('input[name="password"]');
            const role = document.querySelector('select[name="role"]');
            
            if (!email.value.trim()) {
                e.preventDefault();
                alert('Sila masukkan emel');
                email.focus();
                return false;
            }
            
            if (!password.value.trim()) {
                e.preventDefault();
                alert('Sila masukkan kata laluan');
                password.focus();
                return false;
            }
            
            if (!role.value) {
                e.preventDefault();
                alert('Sila pilih peranan');
                role.focus();
                return false;
            }
            
            return true;
        });
    }
});