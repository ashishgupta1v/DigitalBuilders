export type RegionMode = 'INR' | 'GULF' | 'USD';

export interface RegionMetadata {
    id: RegionMode;
    label: string;
    shortLabel: string;
    flag: string;
    currencyCode: string;
    currencySymbol: string;
    marketDescription: string;
    taxNote: string;
}

export const REGIONS: Record<RegionMode, RegionMetadata> = {
    INR: {
        id: 'INR',
        label: 'India (Domestic)',
        shortLabel: 'India',
        flag: '🇮🇳',
        currencyCode: 'INR',
        currencySymbol: '₹',
        marketDescription: 'Accessible INR rates with GST invoicing & milestone billing.',
        taxNote: '+ 18% GST for Indian entities (Input tax credit available)',
    },
    GULF: {
        id: 'GULF',
        label: 'Gulf / Middle East',
        shortLabel: 'Gulf / ME',
        flag: '🇦🇪',
        currencyCode: 'USD',
        currencySymbol: '$',
        marketDescription: 'Strategic regional pricing for UAE, Saudi Arabia, Qatar, Oman & GCC.',
        taxNote: 'Zero withholding tax · International wire / card invoicing',
    },
    USD: {
        id: 'USD',
        label: 'US, UK, EU & Global',
        shortLabel: 'US / Global',
        flag: '🇺🇸',
        currencyCode: 'USD',
        currencySymbol: '$',
        marketDescription: 'Silicon Valley grade offshore architecture anchored against $150/hr agency rates.',
        taxNote: 'Standard Master Services Agreement (MSA) & mutual NDA',
    },
};

export function detectUserRegion(): RegionMode {
    if (typeof window === 'undefined') return 'INR';

    const saved = localStorage.getItem('db_pricing_region') as RegionMode | null;
    if (saved && (saved === 'INR' || saved === 'GULF' || saved === 'USD')) {
        return saved;
    }

    try {
        const tz = Intl.DateTimeFormat().resolvedOptions().timeZone || '';
        if (
            tz.includes('Calcutta') ||
            tz.includes('Kolkata') ||
            tz.includes('Colombo') ||
            tz.includes('Kathmandu') ||
            tz.includes('India')
        ) {
            return 'INR';
        }

        if (
            tz.includes('Dubai') ||
            tz.includes('Muscat') ||
            tz.includes('Riyadh') ||
            tz.includes('Qatar') ||
            tz.includes('Bahrain') ||
            tz.includes('Kuwait') ||
            tz.includes('Abu_Dhabi')
        ) {
            return 'GULF';
        }
    } catch {
        // Fallback safely
    }

    return 'USD';
}

export function saveUserRegion(region: RegionMode): void {
    if (typeof window !== 'undefined') {
        localStorage.setItem('db_pricing_region', region);
    }
}
