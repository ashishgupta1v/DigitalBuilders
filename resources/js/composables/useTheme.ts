import { ref } from 'vue';

const isDark = ref(false);
let isInitialized = false;

export function useTheme() {
    if (!isInitialized && typeof window !== 'undefined') {
        isInitialized = true;
        
        // Read current state from DOM or localStorage
        const htmlTheme = document.documentElement.getAttribute('data-theme');
        const hasDarkClass = document.documentElement.classList.contains('dark');
        let savedTheme: string | null = null;
        try {
            savedTheme = localStorage.getItem('db-theme');
        } catch {
            savedTheme = null;
        }

        if (!savedTheme && typeof window !== 'undefined' && window.matchMedia) {
            savedTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }

        if (savedTheme === 'dark' || htmlTheme === 'dark' || hasDarkClass) {
            isDark.value = true;
            applyTheme('dark');
        } else {
            isDark.value = false;
            applyTheme('light');
        }

        // Listen for storage changes from other tabs/windows
        window.addEventListener('storage', (e) => {
            if (e.key === 'db-theme' && (e.newValue === 'dark' || e.newValue === 'light')) {
                isDark.value = e.newValue === 'dark';
                applyTheme(e.newValue);
            }
        });
    }

    function applyTheme(theme: 'light' | 'dark') {
        if (typeof document === 'undefined') return;
        const html = document.documentElement;
        if (theme === 'dark') {
            html.setAttribute('data-theme', 'dark');
            html.classList.add('dark');
            html.classList.remove('light');
        } else {
            html.setAttribute('data-theme', 'light');
            html.classList.remove('dark');
            html.classList.add('light');
        }
        try {
            localStorage.setItem('db-theme', theme);
        } catch {
            // Ignore quota errors
        }
    }

    function toggleTheme() {
        isDark.value = !isDark.value;
        applyTheme(isDark.value ? 'dark' : 'light');
    }

    function setTheme(theme: 'light' | 'dark') {
        isDark.value = theme === 'dark';
        applyTheme(theme);
    }

    return {
        isDark,
        toggleTheme,
        setTheme,
    };
}
