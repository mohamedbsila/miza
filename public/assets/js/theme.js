// Theme management
const ThemeManager = {
    init() {
        // Get theme toggle buttons
        const themeToggles = document.querySelectorAll('.theme-toggle');
        
        // Add click event listeners to all theme toggle buttons
        themeToggles.forEach(toggle => {
            toggle.addEventListener('click', () => this.toggleTheme());
        });

        // Listen for theme changes from other tabs/windows
        window.addEventListener('storage', (e) => {
            if (e.key === 'theme') {
                this.applyTheme(e.newValue);
            }
        });

        // Apply initial theme
        this.applyTheme(localStorage.getItem('theme') || 'light');
    },

    applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('theme', theme);
    },

    toggleTheme() {
        const currentTheme = localStorage.getItem('theme') || 'light';
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        this.applyTheme(newTheme);
    }
};

// Initialize theme management when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    ThemeManager.init();
}); 