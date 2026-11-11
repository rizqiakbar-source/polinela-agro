import { Sprout, Droplets, Flame, Sparkles, MoonStar } from 'lucide-react';

export const BANNER_CATEGORIES = [
    {
        id: 'agro',
        name: 'Perkebunan & Alam',
        icon: Sprout,
        presets: [
            { id: 'emerald', name: 'Hijau Polinela Agro', value: 'linear-gradient(135deg, #166534 0%, #15803d 50%, #047857 100%)' },
            { id: 'forest', name: 'Hutan Pinus Riset', value: 'linear-gradient(135deg, #064e3b 0%, #047857 50%, #059669 100%)' },
            { id: 'lime', name: 'Citrus & Daun Segar', value: 'linear-gradient(135deg, #3f6212 0%, #65a30d 50%, #84cc16 100%)' },
            { id: 'tea', name: 'Kebun Teh Puncak', value: 'linear-gradient(135deg, #14532d 0%, #16a34a 50%, #4ade80 100%)' },
            { id: 'coffee', name: 'Kopi Robusta Lampung', value: 'linear-gradient(135deg, #451a03 0%, #78350f 50%, #9a3412 100%)' },
            { id: 'cacao', name: 'Kakao & Cokelat Emas', value: 'linear-gradient(135deg, #3b1d11 0%, #713f12 50%, #ca8a04 100%)' },
        ]
    },
    {
        id: 'ocean',
        name: 'Samudra & Langit',
        icon: Droplets,
        presets: [
            { id: 'sapphire', name: 'Deep Ocean Sapphire', value: 'linear-gradient(135deg, #1e3a8a 0%, #2563eb 50%, #06b6d4 100%)' },
            { id: 'pacific', name: 'Pacific Cyan Wave', value: 'linear-gradient(135deg, #0e7490 0%, #06b6d4 50%, #38bdf8 100%)' },
            { id: 'navy', name: 'Midnight Navy Blue', value: 'linear-gradient(135deg, #020617 0%, #0f172a 50%, #1e3a8a 100%)' },
            { id: 'arctic', name: 'Arctic Ice Glaze', value: 'linear-gradient(135deg, #1e40af 0%, #3b82f6 50%, #93c5fd 100%)' },
            { id: 'teal', name: 'Pantai Tropis Teal', value: 'linear-gradient(135deg, #064e3b 0%, #0d9488 50%, #2dd4bf 100%)' },
        ]
    },
    {
        id: 'sunset',
        name: 'Senja & Kehangatan',
        icon: Flame,
        presets: [
            { id: 'sunset', name: 'Senja Tropis Lampung', value: 'linear-gradient(135deg, #9a3412 0%, #ea580c 50%, #f59e0b 100%)' },
            { id: 'flame', name: 'Crimson Ember Flame', value: 'linear-gradient(135deg, #881337 0%, #e11d48 50%, #f43f5e 100%)' },
            { id: 'gold', name: 'Panen Raya Golden', value: 'linear-gradient(135deg, #78350f 0%, #d97706 50%, #fbbf24 100%)' },
            { id: 'burgundy', name: 'Ruby Velvet Wine', value: 'linear-gradient(135deg, #4c0519 0%, #9f1239 50%, #e11d48 100%)' },
            { id: 'peach', name: 'Apricot Sunset Glow', value: 'linear-gradient(135deg, #c2410c 0%, #f97316 50%, #fdba74 100%)' },
        ]
    },
    {
        id: 'cyber',
        name: 'Cyber & Aurora',
        icon: Sparkles,
        presets: [
            { id: 'aurora', name: 'Aurora Borealis Glow', value: 'linear-gradient(135deg, #064e3b 0%, #0284c7 50%, #9333ea 100%)' },
            { id: 'royal', name: 'Royal Amethyst Ungu', value: 'linear-gradient(135deg, #4c1d95 0%, #7c3aed 50%, #c084fc 100%)' },
            { id: 'cyber', name: 'Cyberpunk Neon Wave', value: 'linear-gradient(135deg, #831843 0%, #c026d3 50%, #3b82f6 100%)' },
            { id: 'rose', name: 'Rose Sakura Blossom', value: 'linear-gradient(135deg, #831843 0%, #db2777 50%, #f472b6 100%)' },
            { id: 'mystic', name: 'Mystic Indigo Nebula', value: 'linear-gradient(135deg, #312e81 0%, #4338ca 50%, #818cf8 100%)' },
        ]
    },
    {
        id: 'stealth',
        name: 'Monokrom & Elegan',
        icon: MoonStar,
        presets: [
            { id: 'midnight', name: 'Midnight Carbon Slate', value: 'linear-gradient(135deg, #090d16 0%, #1e293b 50%, #334155 100%)' },
            { id: 'titanium', name: 'Titanium Metallic Gray', value: 'linear-gradient(135deg, #18181b 0%, #3f3f46 50%, #71717a 100%)' },
            { id: 'pureblack', name: 'Deep Black Onyx', value: 'linear-gradient(135deg, #000000 0%, #111827 50%, #1f2937 100%)' },
            { id: 'silver', name: 'Silver Platinum Steel', value: 'linear-gradient(135deg, #334155 0%, #64748b 50%, #94a3b8 100%)' },
        ]
    }
];

export const SOLID_SWATCHES = [
    '#166534', '#15803d', '#14532d', '#047857', '#0e7490', '#1d4ed8', 
    '#1e3a8a', '#4338ca', '#6b21a8', '#831843', '#9f1239', '#9a3412', 
    '#c2410c', '#78350f', '#0f172a', '#18181b'
];
