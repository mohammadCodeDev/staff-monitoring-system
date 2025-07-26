import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

// Define the AlpineJS component for the theme switcher
document.addEventListener('alpine:initializing', () => {
    Alpine.data('themeSwitcher', () => ({
        // Set the default theme by reading from localStorage. Default to 'light'.
        theme: localStorage.getItem('theme') || 'light',
        
        // Function to apply the theme
        applyTheme() {
            if (this.theme === 'light') {
                document.documentElement.classList.remove('dark');
            } else if (this.theme === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                // For 'system', check the OS preference
                if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            }
        },

        // Function to set and save the theme
        setTheme(newTheme) {
            this.theme = newTheme;
            // Save the user's choice to localStorage
            localStorage.setItem('theme', newTheme);
            // Apply the new theme
            this.applyTheme();
        },

        // Initialize the component
        init() {
            this.applyTheme();
            // Watch for changes in the system's color scheme
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
                if (this.theme === 'system') {
                    this.applyTheme();
                }
            });
        }
    }));
});

Alpine.start();
