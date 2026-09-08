<script setup lang="ts">
import { useTheme } from '@/composables/useTheme';

withDefaults(
    defineProps<{
        size?: 'sm' | 'md' | 'lg';
        showLabel?: boolean;
    }>(),
    {
        size: 'md',
        showLabel: false,
    }
);

const { isDark, toggleTheme } = useTheme();
</script>

<template>
    <button
        type="button"
        @click="toggleTheme"
        :aria-label="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
        :title="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
        class="relative inline-flex items-center justify-center rounded-full transition-all duration-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 focus-visible:ring-offset-2 border active:scale-95 cursor-pointer select-none"
        :class="[
            size === 'sm'
                ? 'h-9 w-9 min-h-[36px] min-w-[36px]'
                : size === 'lg'
                ? 'h-11 w-11 min-h-[44px] min-w-[44px]'
                : 'h-10 w-10 min-h-[40px] min-w-[40px]',
            isDark
                ? 'bg-slate-800/80 hover:bg-slate-700/90 border-slate-700 text-amber-300 shadow-[0_0_12px_rgba(251,191,36,0.15)]'
                : 'bg-white hover:bg-slate-100 border-slate-200/80 text-slate-700 shadow-sm'
        ]"
    >
        <!-- Sun Icon (shown in Dark Mode to switch to Light) -->
        <svg
            v-if="isDark"
            class="h-4.5 w-4.5 transition-transform duration-500 rotate-0 hover:rotate-90"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
        >
            <circle cx="12" cy="12" r="5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 1v2m0 18v2M4.22 4.22l1.42 1.42m12.72 12.72l1.42 1.42M1 12h2m18 0h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"
            />
        </svg>

        <!-- Moon Icon (shown in Light Mode to switch to Dark) -->
        <svg
            v-else
            class="h-4.5 w-4.5 transition-transform duration-500 -rotate-12 hover:rotate-0 text-slate-700"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"
            />
        </svg>

        <span v-if="showLabel" class="ml-2 text-xs font-semibold">
            {{ isDark ? 'Light Mode' : 'Dark Mode' }}
        </span>
    </button>
</template>
