function applyTheme(theme) {
    if (theme === 'dark') {
        document.documentElement.classList.add('dark-mode');
        document.body.classList.add('dark-mode');
    } else {
        document.documentElement.classList.remove('dark-mode');
        document.body.classList.remove('dark-mode');
    }

    const toggle = document.getElementById('themeToggle');
    if (toggle) {
        toggle.checked = theme === 'dark';
    }

    const globalToggleButton = document.getElementById('globalThemeToggleButton');
    if (globalToggleButton) {
        const isDark = theme === 'dark';
        globalToggleButton.classList.toggle('is-dark', isDark);
        globalToggleButton.setAttribute('aria-pressed', isDark ? 'true' : 'false');
        globalToggleButton.setAttribute('title', isDark ? 'Ativar tema claro' : 'Ativar tema escuro');
    }
}

function initThemeToggle() {
    const savedTheme = localStorage.getItem('theme') || 'light';
    applyTheme(savedTheme);

    $(document).on('change', '#themeToggle', function() {
        const theme = $(this).is(':checked') ? 'dark' : 'light';
        localStorage.setItem('theme', theme);
        applyTheme(theme);
    });

    $(document).off('click.globalThemeToggleButton', '#globalThemeToggleButton').on('click.globalThemeToggleButton', '#globalThemeToggleButton', function() {
        const isDark = document.documentElement.classList.contains('dark-mode');
        const nextTheme = isDark ? 'light' : 'dark';
        localStorage.setItem('theme', nextTheme);
        applyTheme(nextTheme);
    });
}
