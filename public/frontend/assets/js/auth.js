const API_BASE_URL = 'http://127.0.0.1:8000/api';

function saveAuth(token, user) {
    localStorage.setItem('token', token);
    localStorage.setItem('user', JSON.stringify(user));
}

function getToken() {
    return localStorage.getItem('token');
}

function getUser() {
    const user = localStorage.getItem('user');
    return user ? JSON.parse(user) : null;
}

function clearAuth() {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
}

function isAuthenticated() {
    return !!getToken();
}

function getAuthHeaders() {
    const token = getToken();

    return token
        ? {
            Authorization: `Bearer ${token}`,
            Accept: 'application/json'
        }
        : {
            Accept: 'application/json'
        };
}

function redirectIfNotAuthenticated() {
    if (!isAuthenticated()) {
        window.location.href = '../auth/login.html';
    }
}

function redirectIfAuthenticated() {
    const token = getToken();

    if (!token) {
        return;
    }

    $.ajax({
        url: `${API_BASE_URL}/me`,
        method: 'GET',
        headers: getAuthHeaders(),
        success: function(user) {
            localStorage.setItem('user', JSON.stringify(user));
            window.location.href = '../dashboard/index.html';
        },
        error: function() {
            clearAuth();
        }
    });
}

function fillLoggedUserName() {
    const user = getUser();
    if (!user) return;

    const displayName = user.name || user.username || user.email || user.user?.name || user.user?.username || 'Usuario';
    const nameTargets = document.querySelectorAll('[data-logged-user-name], #loggedUserName');
    nameTargets.forEach(function (nameTarget) {
        nameTarget.textContent = displayName;
    });
}

function logout() {
    const token = getToken();

    if (!token) {
        clearAuth();
        window.location.href = '../auth/login.html';
        return;
    }

    $.ajax({
        url: `${API_BASE_URL}/logout`,
        method: 'POST',
        headers: getAuthHeaders(),
        complete: function () {
            clearAuth();
            window.location.href = '../auth/login.html';
        }
    });
}

function validateSession(onSuccess) {
    const token = getToken();

    if (!token) {
        clearAuth();
        window.location.href = '../auth/login.html';
        return;
    }

    $.ajax({
        url: `${API_BASE_URL}/me`,
        method: 'GET',
        headers: getAuthHeaders(),
        success: function (user) {
            localStorage.setItem('user', JSON.stringify(user));

            if (typeof onSuccess === 'function') {
                onSuccess(user);
            }
        },
        error: function () {
            clearAuth();
            window.location.href = '../auth/login.html';
        }
    });
}

