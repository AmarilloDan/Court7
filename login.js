document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    const role = document.getElementById('role').value;
    const loginMessage = document.getElementById('loginMessage');

    const adminUsername = 'admin';
    const adminPassword = 'admin123';

    if (role === 'admin') {
        if (username === adminUsername && password === adminPassword) {
            loginMessage.style.color = 'green';
            loginMessage.textContent = 'Admin login successful! Redirecting...';
            setTimeout(() => {
                window.location.href = 'court_management.html';
            }, 1000);
        } else {
            loginMessage.style.color = 'red';
            loginMessage.textContent = 'Invalid admin credentials.';
        }
    } else {
        fetch('user_login.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username, password })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                loginMessage.style.color = 'green';
                loginMessage.textContent = 'User login successful! Redirecting...';
                setTimeout(() => {
                    window.location.href = 'court_management.html';
                }, 1000);
            } else {
                loginMessage.style.color = 'red';
                loginMessage.textContent = 'Invalid user credentials.';
            }
        })
        .catch(() => {
            loginMessage.style.color = 'red';
            loginMessage.textContent = 'Server error.';
        });
    }
});
