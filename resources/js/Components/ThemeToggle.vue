<script setup lang="ts">
import { useTheme } from '@/composables/useTheme';

withDefaults(
    defineProps<{
        size?: 'sm' | 'md' | 'lg';
        variant?: 'pill' | 'button';
        showLabel?: boolean;
    }>(),
    {
        size: 'md',
        variant: 'pill',
        showLabel: false,
    }
);

const { isDark, toggleTheme } = useTheme();
</script>

<template>
    <!-- VARIANT A: Sliding Capsule Pill Switch (Linear / macOS Luxury Style) -->
    <div
        v-if="variant === 'pill'"
        role="switch"
        :aria-checked="isDark"
        :aria-label="isDark ? 'Dark mode enabled. Click to switch to light mode' : 'Light mode enabled. Click to switch to dark mode'"
        :title="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
        tabindex="0"
        @click="toggleTheme"
        @keydown.enter.prevent="toggleTheme"
        @keydown.space.prevent="toggleTheme"
        class="group relative inline-flex items-center cursor-pointer select-none rounded-full transition-all duration-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 focus-visible:ring-offset-2 active:scale-95"
        :class="[
            size === 'sm'
                ? 'w-[58px] h-[30px] p-[2px]'
                : size === 'lg'
                ? 'w-[78px] h-[38px] p-[3px]'
                : 'w-[66px] h-[34px] p-[3px]',
            isDark
                ? 'bg-slate-900/90 hover:bg-slate-900 border border-slate-700/80 shadow-[inset_0_2px_4px_rgba(0,0,0,0.5)]'
                : 'bg-slate-200/85 hover:bg-slate-200 border border-slate-300/80 shadow-[inset_0_2px_4px_rgba(0,0,0,0.06)]',
        ]"
    >
        <!-- Subtle Ambient Underglow on Hover -->
        <div
            class="absolute -inset-1 rounded-full opacity-0 group-hover:opacity-100 blur-sm transition-opacity duration-300 pointer-events-none"
            :class="isDark ? 'bg-gradient-to-r from-sky-500/20 to-purple-500/20' : 'bg-gradient-to-r from-amber-400/20 to-orange-400/15'"
        />

        <!-- Track Background Icons: Sun (Left) & Moon (Right) -->
        <div class="relative z-0 flex w-full items-center justify-between px-1.5 pointer-events-none">
            <!-- Sun Icon (Inactive indicator on track when in Dark Mode) -->
            <span
                class="flex items-center justify-center transition-all duration-300"
                :class="[
                    size === 'sm' ? 'w-5 h-5' : size === 'lg' ? 'w-7 h-7' : 'w-6 h-6',
                    !isDark ? 'opacity-0' : 'opacity-40 text-slate-400 group-hover:opacity-60'
                ]"
            >
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="4" stroke-width="2" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2v2m0 16v2M4.93 4.93l1.41 1.41m11.32 11.32l1.41 1.41M2 12h2m16 0h2M4.93 19.07l1.41-1.41m11.32-11.32l1.41-1.41" />
                </svg>
            </span>

            <!-- Moon Icon (Inactive indicator on track when in Light Mode) -->
            <span
                class="flex items-center justify-center transition-all duration-300"
                :class="[
                    size === 'sm' ? 'w-5 h-5' : size === 'lg' ? 'w-7 h-7' : 'w-6 h-6',
                    isDark ? 'opacity-0' : 'opacity-40 text-slate-500 group-hover:opacity-60'
                ]"
            >
                <svg class="h-3.5 w-3.5 -rotate-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                </svg>
            </span>
        </div>

        <!-- Sliding Tactile Pill Thumb -->
        <div
            class="absolute top-0.5 bottom-0.5 flex items-center justify-center rounded-full transition-transform duration-300 ease-[cubic-bezier(0.34,1.56,0.64,1)] z-10"
            :class="[
                size === 'sm'
                    ? 'w-6 h-6'
                    : size === 'lg'
                    ? 'w-8 h-8'
                    : 'w-7 h-7',
                isDark
                    ? (size === 'sm' ? 'translate-x-[29px]' : size === 'lg' ? 'translate-x-[40px]' : 'translate-x-[33px]') + ' bg-slate-800 text-cyan-300 shadow-[0_2px_8px_rgba(0,0,0,0.6)] border border-cyan-500/30'
                    : 'translate-x-0.5 bg-white text-amber-500 shadow-[0_2px_8px_rgba(0,0,0,0.12),0_1px_2px_rgba(0,0,0,0.08)] border border-slate-200/80',
            ]"
        >
            <!-- Sun in Active Thumb (Light Mode) -->
            <svg
                v-if="!isDark"
                class="transition-transform duration-500 rotate-0 group-hover:rotate-45"
                :class="size === 'sm' ? 'h-3.5 w-3.5' : size === 'lg' ? 'h-4.5 w-4.5' : 'h-4 w-4'"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
            >
                <circle cx="12" cy="12" r="4.5" class="fill-amber-400 stroke-amber-500" stroke-width="1.5" />
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 1v2.5m0 17V23M3.22 3.22l1.77 1.77m14.02 14.02l1.77 1.77M1 12h2.5m17 0H23M3.22 20.78l1.77-1.77m14.02-14.02l1.77-1.77"
                />
            </svg>

            <!-- Moon with Micro-Star in Active Thumb (Dark Mode) -->
            <div
                v-else
                class="relative flex items-center justify-center"
                :class="size === 'sm' ? 'h-3.5 w-3.5' : size === 'lg' ? 'h-4.5 w-4.5' : 'h-4 w-4'"
            >
                <svg
                    class="h-full w-full transition-transform duration-500 -rotate-12 group-hover:rotate-0"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        class="fill-cyan-400/20 stroke-cyan-300"
                        d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"
                    />
                </svg>
                <!-- Little sparkling constellation star -->
                <span class="absolute -top-0.5 -right-0.5 w-1 h-1 rounded-full bg-cyan-200 animate-pulse pointer-events-none" />
            </div>
        </div>

        <span v-if="showLabel" class="sr-only">
            {{ isDark ? 'Dark Mode' : 'Light Mode' }}
        </span>
    </div>

    <!-- VARIANT B: Elevated Glassmorphic Tactile Orb Button -->
    <button
        v-else
        type="button"
        @click="toggleTheme"
        :aria-label="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
        :title="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
        class="group relative inline-flex items-center justify-center rounded-full p-[1px] transition-all duration-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 focus-visible:ring-offset-2 active:scale-95 cursor-pointer select-none"
        :class="[
            isDark
                ? 'bg-gradient-to-br from-cyan-500/30 via-slate-700 to-purple-500/30 shadow-[0_0_15px_rgba(56,189,248,0.2)] hover:shadow-[0_0_22px_rgba(56,189,248,0.35)]'
                : 'bg-gradient-to-br from-amber-400/40 via-slate-200 to-indigo-500/30 shadow-xs hover:shadow-[0_4px_18px_rgba(245,158,11,0.2)]'
        ]"
    >
        <!-- Inner Disc -->
        <span
            class="relative flex items-center justify-center rounded-full transition-colors duration-300"
            :class="[
                size === 'sm'
                    ? 'h-8.5 w-8.5 min-h-[34px] min-w-[34px]'
                    : size === 'lg'
                    ? 'h-11 w-11 min-h-[44px] min-w-[44px]'
                    : 'h-10 w-10 min-h-[40px] min-w-[40px]',
                isDark
                    ? 'bg-slate-900/95 text-cyan-300 hover:bg-slate-850'
                    : 'bg-white/95 text-amber-500 hover:bg-slate-50'
            ]"
        >
            <!-- Sun Icon (in Light Mode) -->
            <svg
                v-if="!isDark"
                class="transition-transform duration-500 rotate-0 group-hover:rotate-45"
                :class="size === 'sm' ? 'h-4 w-4' : size === 'lg' ? 'h-5 w-5' : 'h-4.5 w-4.5'"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
            >
                <circle cx="12" cy="12" r="4.5" class="fill-amber-400 stroke-amber-500" stroke-width="1.5" />
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 1v2.5m0 17V23M3.22 3.22l1.77 1.77m14.02 14.02l1.77 1.77M1 12h2.5m17 0H23M3.22 20.78l1.77-1.77m14.02-14.02l1.77-1.77"
                />
            </svg>

            <!-- Moon Icon with Stars (in Dark Mode) -->
            <div
                v-else
                class="relative flex items-center justify-center"
                :class="size === 'sm' ? 'h-4 w-4' : size === 'lg' ? 'h-5 w-5' : 'h-4.5 w-4.5'"
            >
                <svg
                    class="h-full w-full transition-transform duration-500 -rotate-12 group-hover:rotate-0"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        class="fill-cyan-400/20 stroke-cyan-300"
                        d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"
                    />
                </svg>
                <span class="absolute -top-0.5 -right-0.5 w-1 h-1 rounded-full bg-cyan-200 animate-pulse pointer-events-none" />
            </div>
        </span>

        <span v-if="showLabel" class="ml-2 text-xs font-semibold pr-2">
            {{ isDark ? 'Light Mode' : 'Dark Mode' }}
        </span>
    </button>
</template>
