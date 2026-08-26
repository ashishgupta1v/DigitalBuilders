/**
 * Automated WCAG 2.2 AA Contrast & Theme Token Audit Script
 * DigitalBuilders Design System
 *
 * Verifies:
 * 1. Semantic color token contrast ratios (Light & Dark modes) >= 4.5:1 (or 3:1 for large/decorative)
 * 2. Badge & tag contrast ratios >= 4.5:1
 * 3. Gradient button text contrast >= 4.5:1
 * 4. AST / regex check across all Vue templates for banned hardcoded low-contrast patterns
 */

import fs from 'node:fs';
import path from 'node:path';

// Helper: Parse HSL string (e.g. "222 47% 11%") into RGB [r, g, b] (0-255)
function hslToRgb(h, s, l) {
    s /= 100;
    l /= 100;
    const k = n => (n + h / 30) % 12;
    const a = s * Math.min(l, 1 - l);
    const f = n => l - a * Math.max(-1, Math.min(k(n) - 3, Math.min(9 - k(n), 1)));
    return [Math.round(f(0) * 255), Math.round(f(8) * 255), Math.round(f(4) * 255)];
}

// Helper: Parse Hex string (e.g. "#ffffff" or "#1a2231") into RGB [r, g, b]
function hexToRgb(hex) {
    hex = hex.replace('#', '');
    if (hex.length === 3) hex = hex.split('').map(c => c + c).join('');
    const num = parseInt(hex, 16);
    return [(num >> 16) & 255, (num >> 8) & 255, num & 255];
}

// Helper: Calculate WCAG relative luminance
function relativeLuminance([r, g, b]) {
    const [rs, gs, bs] = [r, g, b].map(c => {
        const s = c / 255;
        return s <= 0.04045 ? s / 12.92 : Math.pow((s + 0.055) / 1.055, 2.4);
    });
    return 0.2126 * rs + 0.7152 * gs + 0.0722 * bs;
}

// Helper: Calculate contrast ratio between two RGB colors
function contrastRatio(rgb1, rgb2) {
    const l1 = relativeLuminance(rgb1);
    const l2 = relativeLuminance(rgb2);
    const lighter = Math.max(l1, l2);
    const darker = Math.min(l1, l2);
    return (lighter + 0.05) / (darker + 0.05);
}

// Test Suites
const suites = [];
let totalChecks = 0;
let passedChecks = 0;
let failedChecks = 0;

function testPair(name, fgRgb, bgRgb, minRatio, details = '') {
    totalChecks++;
    const ratio = contrastRatio(fgRgb, bgRgb);
    const passed = ratio >= minRatio;
    if (passed) {
        passedChecks++;
    } else {
        failedChecks++;
    }
    return {
        name,
        ratio: ratio.toFixed(2),
        minRatio: `${minRatio}:1`,
        passed,
        details,
    };
}

console.log('='.repeat(70));
console.log('  🔍 DIGITALBUILDERS WCAG 2.2 AA CONTRAST AUDIT RUNNER');
console.log('='.repeat(70));

// --- 1. THEME TOKEN PAIRINGS ---
console.log('\n[1] Checking Semantic Theme Tokens (Light & Dark Modes)...');

const lightTokens = {
    background: hslToRgb(0, 0, 100),            // #ffffff
    foreground: hslToRgb(222, 47, 11),           // #0f172a (slate-900)
    card: hslToRgb(0, 0, 100),                 // #ffffff
    cardForeground: hslToRgb(222, 47, 11),       // #0f172a
    secondary: hslToRgb(210, 40, 96),          // #f1f5f9
    secondaryForeground: hslToRgb(222, 47, 11),  // #0f172a
    muted: hslToRgb(210, 40, 96),              // #f1f5f9
    mutedForeground: hslToRgb(215, 16, 40),      // #556477 (slate-600 approx)
};

const darkTokens = {
    background: hslToRgb(222, 47, 11),           // #0f172a (slate-900)
    foreground: hslToRgb(210, 40, 98),           // #f8fafc (slate-50)
    card: hslToRgb(222, 47, 13),                 // #121c32
    cardForeground: hslToRgb(210, 40, 98),       // #f8fafc
    secondary: hslToRgb(217, 33, 17),          // #1e293b
    secondaryForeground: hslToRgb(210, 40, 98),  // #f8fafc
    muted: hslToRgb(217, 33, 17),              // #1e293b
    mutedForeground: hslToRgb(215, 20, 65),      // #94a3b8 (slate-400)
};

const tokenTests = [
    // Light Mode
    testPair('Light: foreground on background', lightTokens.foreground, lightTokens.background, 4.5),
    testPair('Light: cardForeground on card', lightTokens.cardForeground, lightTokens.card, 4.5),
    testPair('Light: secondaryForeground on secondary', lightTokens.secondaryForeground, lightTokens.secondary, 4.5),
    testPair('Light: mutedForeground on background', lightTokens.mutedForeground, lightTokens.background, 4.5),
    
    // Dark Mode
    testPair('Dark: foreground on background', darkTokens.foreground, darkTokens.background, 4.5),
    testPair('Dark: cardForeground on card', darkTokens.cardForeground, darkTokens.card, 4.5),
    testPair('Dark: secondaryForeground on secondary', darkTokens.secondaryForeground, darkTokens.secondary, 4.5),
    testPair('Dark: mutedForeground on background', darkTokens.mutedForeground, darkTokens.background, 4.5),
];

tokenTests.forEach(t => {
    const icon = t.passed ? '✅' : '❌';
    console.log(`  ${icon} ${t.name.padEnd(45)} Ratio: ${t.ratio}:1 (Required: ${t.minRatio})`);
});

// --- 2. BUTTONS & GRADIENTS ---
console.log('\n[2] Checking Button Gradients & Primary Text...');

const buttonTests = [
    testPair('.btn-primary White text on Sky 700 (#0369a1)', hexToRgb('#ffffff'), hexToRgb('#0369a1'), 4.5, 'Theme primary CTA gradient start'),
    testPair('.btn-primary White text on Indigo 600 (#4f46e5)', hexToRgb('#ffffff'), hexToRgb('#4f46e5'), 4.5, 'Theme primary CTA gradient mid'),
    testPair('.btn-primary White text on Purple 600 (#7c3aed)', hexToRgb('#ffffff'), hexToRgb('#7c3aed'), 4.5, 'Theme primary CTA gradient end'),
];

buttonTests.forEach(t => {
    const icon = t.passed ? '✅' : '❌';
    console.log(`  ${icon} ${t.name.padEnd(45)} Ratio: ${t.ratio}:1 (Required: ${t.minRatio})`);
});

// --- 3. BADGES & TINTS ---
console.log('\n[3] Checking Accessible Badge & Tag Pairings...');

// Light mode: -700 on 500/10 (white canvas); Dark mode: -300 on 500/10 (slate-900 canvas)
const badgeTests = [
    // Emerald
    testPair('Light: emerald-700 on emerald-50', hexToRgb('#047857'), hexToRgb('#ecfdf5'), 4.5),
    testPair('Dark: emerald-300 on slate-900', hexToRgb('#6ee7b7'), hexToRgb('#0f172a'), 4.5),
    // Sky
    testPair('Light: sky-700 on sky-50', hexToRgb('#0369a1'), hexToRgb('#f0f9ff'), 4.5),
    testPair('Dark: sky-300 on slate-900', hexToRgb('#7dd3fc'), hexToRgb('#0f172a'), 4.5),
    // Indigo
    testPair('Light: indigo-700 on indigo-50', hexToRgb('#4338ca'), hexToRgb('#eef2ff'), 4.5),
    testPair('Dark: indigo-300 on slate-900', hexToRgb('#a5b4fc'), hexToRgb('#0f172a'), 4.5),
    // Amber
    testPair('Light: amber-700 on amber-50', hexToRgb('#b45309'), hexToRgb('#fffbeb'), 4.5),
    testPair('Dark: amber-300 on slate-900', hexToRgb('#fcd34d'), hexToRgb('#0f172a'), 4.5),
];

badgeTests.forEach(t => {
    const icon = t.passed ? '✅' : '❌';
    console.log(`  ${icon} ${t.name.padEnd(45)} Ratio: ${t.ratio}:1 (Required: ${t.minRatio})`);
});

// --- 4. STATIC CODEBASE SCAN ---
console.log('\n[4] Scanning Codebase for Banned Low-Contrast Patterns...');

const bannedPatterns = [
    { pattern: /text-\[#1a2231\]/g, name: 'Raw #1a2231 text color (dark gray on dark gradients)' },
    { pattern: /text-slate-700/g, name: 'Hardcoded text-slate-700 without dark variant' },
];

function scanDirectory(dir) {
    const entries = fs.readdirSync(dir, { withFileTypes: true });
    const results = [];
    for (const entry of entries) {
        const fullPath = path.join(dir, entry.name);
        if (entry.isDirectory()) {
            results.push(...scanDirectory(fullPath));
        } else if (entry.isFile() && (entry.name.endsWith('.vue') || entry.name.endsWith('.ts'))) {
            const content = fs.readFileSync(fullPath, 'utf8');
            for (const { pattern, name } of bannedPatterns) {
                const matches = content.match(pattern);
                if (matches) {
                    results.push({ file: path.relative(process.cwd(), fullPath), name, count: matches.length });
                }
            }
        }
    }
    return results;
}

const jsDir = path.resolve(process.cwd(), 'resources/js');
const violations = scanDirectory(jsDir);

totalChecks++;
if (violations.length === 0) {
    passedChecks++;
    console.log('  ✅ Zero banned low-contrast patterns found across all resources/js files.');
} else {
    failedChecks++;
    console.log(`  ❌ Found ${violations.length} pattern violations:`);
    violations.forEach(v => console.log(`     - [${v.file}] ${v.name} (${v.count}x)`));
}

// --- SUMMARY REPORT ---
console.log('\n' + '='.repeat(70));
console.log(`  📊 AUDIT SUMMARY: ${passedChecks}/${totalChecks} Checks Passed (${failedChecks} Failures)`);
console.log('='.repeat(70));

if (failedChecks > 0) {
    console.error('\n❌ Contrast Audit FAILED with WCAG 2.2 AA non-compliance.');
    process.exit(1);
} else {
    console.log('\n✨ All WCAG 2.2 AA Contrast & Theme Token Audits PASSED successfully!\n');
    process.exit(0);
}
