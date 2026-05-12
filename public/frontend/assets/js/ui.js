function initGlobalUI(activePage) {
    const headerTarget = document.getElementById('header');
    const toastTarget = document.getElementById('toast-root');

    if (headerTarget) {
        headerTarget.innerHTML = getHeaderTemplate();
    }

    if (activePage) {
        $(`[data-page="${activePage}"]`).addClass('active');
    }

    if (typeof initThemeToggle === 'function') {
        initThemeToggle();
    }

    if (typeof fillLoggedUserName === 'function') {
        fillLoggedUserName();
    }

    $('.logout-button, #logoutButton').off('click').on('click', function () {
        if (typeof logout === 'function') {
            logout();
        }
    });

    if (!document.getElementById('globalThemeToggleButton')) {
        const themeButton = document.createElement('button');
        themeButton.id = 'globalThemeToggleButton';
        themeButton.type = 'button';
        themeButton.className = 'global-theme-toggle-button';
        themeButton.setAttribute('aria-label', 'Alternar tema');
        themeButton.setAttribute('title', 'Alternar tema');
        themeButton.innerHTML = '<span class="theme-toggle-icon theme-toggle-icon-sun" aria-hidden="true"><span class="ui-icon"><svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="4.5"></circle><path d="M12 2.5v2.5M12 19v2.5M21.5 12H19M5 12H2.5M18.7 5.3l-1.8 1.8M7.1 16.9l-1.8 1.8M18.7 18.7l-1.8-1.8M7.1 7.1 5.3 5.3"></path></svg></span></span><span class="theme-toggle-icon theme-toggle-icon-moon" aria-hidden="true"><span class="ui-icon"><svg viewBox="0 0 24 24" fill="none"><path d="M20 14.2A8.5 8.5 0 0 1 9.8 4 8.5 8.5 0 1 0 20 14.2Z"></path></svg></span></span><span class="theme-toggle-label">Tema</span>';
        document.body.appendChild(themeButton);
    }

    if (typeof applyTheme === 'function') {
        applyTheme(localStorage.getItem('theme') || 'light');
    }

    fetch('../partials/toast.html')
        .then(function (response) {
            return response.text();
        })
        .then(function (html) {
            if (toastTarget) {
                toastTarget.innerHTML = html;
            }
        });
}

function getHeaderTemplate() {
    return `
        <header class="app-header">
            <div class="container">
                <div class="app-header-inner">
                    <a class="app-header-brand" href="../dashboard/index.html" aria-label="AgendaFlow">
                        <span class="brand-title">Agendinha</span>
                        <span class="brand-subtitle">organizacao leve para uma rotina mais previsivel</span>
                    </a>

                    <nav class="app-header-nav" aria-label="Navegacao principal">
                        <a class="app-header-link" href="../events/index.html" data-page="events">
                            <span class="ui-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <rect x="3.5" y="5.5" width="17" height="15" rx="2"></rect>
                                    <path d="M7.5 3.5v4M16.5 3.5v4M3.5 9.5h17M8 13h3M13 13h3M8 17h3M13 17h3"></path>
                                </svg>
                            </span>
                            <span>Agendamentos</span>
                        </a>
                        <a class="app-header-link" href="../work-types/index.html" data-page="work-types">
                            <span class="ui-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <path d="M5 7.5A2.5 2.5 0 0 1 7.5 5H11l8 8-6 6-8-8Z"></path>
                                    <circle cx="8.5" cy="8.5" r="1"></circle>
                                </svg>
                            </span>
                            <span>Tipos de trabalho</span>
                        </a>
                    </nav>

                    <div class="app-header-account" aria-label="Conta do usuario">
                        <span class="app-user-pill">
                            <span class="ui-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Z"></path>
                                    <path d="M4.5 19.5a7.5 7.5 0 0 1 15 0"></path>
                                </svg>
                            </span>
                            <span data-logged-user-name>Usuario</span>
                        </span>

                        <button class="app-logout-button logout-button" type="button" aria-label="Sair">
                            <span class="ui-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <path d="M10 6.5H7.5A1.5 1.5 0 0 0 6 8v8a1.5 1.5 0 0 0 1.5 1.5H10"></path>
                                    <path d="M13 16.5 18 12l-5-4.5"></path>
                                    <path d="M18 12H9"></path>
                                </svg>
                            </span>
                            <span>Sair</span>
                        </button>
                    </div>
                </div>
            </div>
        </header>
    `;
}

function showAlert(message, type = 'success') {
    const toastEl = document.getElementById('appToast');
    const toastBody = document.getElementById('appToastBody');

    if (!toastEl || !toastBody) {
        console.warn('Toast global ainda não foi carregado.');
        return;
    }

    toastBody.innerHTML = message;

    toastEl.classList.remove(
        'text-bg-success',
        'text-bg-danger',
        'text-bg-warning',
        'text-bg-info'
    );

    if (type === 'success') {
        toastEl.classList.add('text-bg-success');
    } else if (type === 'danger') {
        toastEl.classList.add('text-bg-danger');
    } else if (type === 'warning') {
        toastEl.classList.add('text-bg-warning');
    } else {
        toastEl.classList.add('text-bg-info');
    }

    if (typeof bootstrap === 'undefined' || !bootstrap.Toast) {
        console.warn('Bootstrap Toast não está disponível.');
        return;
    }

    const toast = bootstrap.Toast.getOrCreateInstance(toastEl, {
        delay: 2500
    });

    toast.show();
}

function getErrorMessage(xhr, fallback = 'Ocorreu um erro.') {
    if (!xhr) {
        return fallback;
    }

    if (xhr.responseJSON && xhr.responseJSON.message) {
        return xhr.responseJSON.message;
    }

    if (xhr.responseJSON && xhr.responseJSON.errors) {
        const firstField = Object.keys(xhr.responseJSON.errors)[0];

        if (
            firstField &&
            Array.isArray(xhr.responseJSON.errors[firstField]) &&
            xhr.responseJSON.errors[firstField].length > 0
        ) {
            return xhr.responseJSON.errors[firstField][0];
        }
    }

    if (xhr.responseText) {
        try {
            const parsed = JSON.parse(xhr.responseText);

            if (parsed.message) {
                return parsed.message;
            }

            if (parsed.errors) {
                const firstField = Object.keys(parsed.errors)[0];

                if (
                    firstField &&
                    Array.isArray(parsed.errors[firstField]) &&
                    parsed.errors[firstField].length > 0
                ) {
                    return parsed.errors[firstField][0];
                }
            }
        } catch (e) {
            console.error('Erro ao interpretar responseText:', e);
        }
    }

    return fallback;
}

function handleAuthError(xhr) {
    if (!xhr) return false;

    if (xhr.status === 401 || xhr.status === 419) {
        clearAuth();
        window.location.href = '../auth/login.html';
        return true;
    }

    return false;
}

$(document).ajaxError(function (event, xhr) {
    handleAuthError(xhr);
});

