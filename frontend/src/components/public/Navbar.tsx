import { useState } from 'react';
import { Link } from 'react-router-dom';
import { Menu, X, Sun } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import { useQuery } from '@tanstack/react-query';
import { publicApi } from '@/services/public.api';
import LanguageSwitcher from '@/components/shared/LanguageSwitcher';

// Helper to resolve full url for logo
const getFileUrl = (path: string | null | undefined) => {
    if (!path) return '';
    if (path.startsWith('http')) return path;
    const baseUrl = (import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api/v1').split('/api/v1')[0];
    return `${baseUrl}/storage/${path}`;
};

export default function Navbar() {
    const [isOpen, setIsOpen] = useState(false);
    const { t } = useTranslation();

    const { data: settings } = useQuery({
        queryKey: ['public-settings'],
        queryFn: publicApi.getSettings,
        staleTime: 1000 * 60 * 5, // Cache for 5 mins
    });

    const logoUrl = getFileUrl(settings?.company_logo);

    return (
        <nav className="sticky top-0 z-50 bg-white/95 backdrop-blur border-b border-gray-100 shadow-sm">
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div className="flex items-center justify-between h-16">
                    {/* Logo */}
                    <div className="flex items-center gap-4">
                        <Link to="/" className="flex items-center gap-2">
                            {logoUrl ? (
                                <img src={logoUrl} alt="Logo" className="w-10 h-10 object-contain drop-shadow-sm" />
                            ) : (
                                <div className="w-9 h-9 rounded-full bg-gradient-to-br from-accent to-accent-dark flex items-center justify-center shadow-md">
                                    <Sun className="w-5 h-5 text-white" />
                                </div>
                            )}
                            <span className="font-display font-bold text-xl text-primary">
                                {settings?.company_name || 'AndleebSurya'}
                            </span>
                        </Link>
                        <LanguageSwitcher className="ml-2" variant="light" />
                    </div>

                    {/* Desktop Nav */}
                    <div className="hidden md:flex items-center gap-4">
                        <Link
                            to="/"
                            className="text-sm font-bold text-neutral-600 px-6 py-2.5 rounded-xl hover:bg-neutral-50 hover:text-primary transition-all duration-300"
                        >
                            {settings?.nav_home || t('nav.home')}
                        </Link>
                        <Link
                            to="/media"
                            className="text-sm font-bold text-neutral-600 px-6 py-2.5 rounded-xl hover:bg-neutral-50 hover:text-primary transition-all duration-300"
                        >
                            {settings?.nav_rewards || t('nav.benefits')}
                        </Link>
                        <Link
                            to="/solar-subsidy-calculator"
                            className="text-sm font-bold text-neutral-600 px-6 py-2.5 rounded-xl hover:bg-neutral-50 hover:text-primary transition-all duration-300"
                        >
                            {t('nav.calculator')}
                        </Link>
                        <Link
                            to="/track-status"
                            className="text-sm font-bold text-neutral-600 px-6 py-2.5 rounded-xl hover:bg-neutral-50 hover:text-primary transition-all duration-300"
                        >
                            {t('nav.track_status')}
                        </Link>
                        <Link
                            to="/user-manual"
                            className="text-sm font-bold text-neutral-600 px-6 py-2.5 rounded-xl hover:bg-neutral-50 hover:text-primary transition-all duration-300"
                        >
                            {t('nav.guide')}
                        </Link>
                    </div>

                    {/* CTAs */}
                    <div className="hidden md:flex items-center gap-3">
                        <a href="/#lead-form" className="btn-accent text-sm py-2 px-5">{settings?.nav_cta_electricity || t('home.hero_cta_primary')}</a>
                        <Link to="/login" className="btn-ghost text-sm py-2 px-5">{settings?.nav_portal_login || t('nav.login')}</Link>
                    </div>

                    {/* Hamburger */}
                    <div className="flex md:hidden items-center gap-3">
                        <LanguageSwitcher variant="light" />
                        <button onClick={() => setIsOpen(!isOpen)} className="p-2 rounded-lg text-neutral-600 hover:bg-neutral-100">
                            {isOpen ? <X className="w-6 h-6" /> : <Menu className="w-6 h-6" />}
                        </button>
                    </div>
                </div>

                {/* Mobile Nav */}
                {isOpen && (
                    <div className="md:hidden py-4 border-t border-gray-100 flex flex-col gap-2 animate-in slide-in-from-top-2">
                        <Link to="/" className="text-base font-bold text-neutral-600 hover:text-primary py-3 px-4 rounded-xl hover:bg-neutral-50 transition-all" onClick={() => setIsOpen(false)}>{settings?.nav_home || t('nav.home')}</Link>
                        <Link to="/media" className="text-base font-bold text-neutral-600 hover:text-primary py-3 px-4 rounded-xl hover:bg-neutral-50 transition-all" onClick={() => setIsOpen(false)}>{settings?.nav_rewards || t('nav.benefits')}</Link>
                        <Link to="/solar-subsidy-calculator" className="text-base font-bold text-neutral-600 hover:text-primary py-3 px-4 rounded-xl hover:bg-neutral-50 transition-all" onClick={() => setIsOpen(false)}>{t('nav.calculator')}</Link>
                        <Link to="/track-status" className="text-base font-bold text-neutral-600 hover:text-primary py-3 px-4 rounded-xl hover:bg-neutral-50 transition-all" onClick={() => setIsOpen(false)}>{t('nav.track_status')}</Link>
                        <Link to="/user-manual" className="text-base font-bold text-neutral-600 hover:text-primary py-3 px-4 rounded-xl hover:bg-neutral-50 transition-all" onClick={() => setIsOpen(false)}>{t('nav.guide')}</Link>
                        <div className="flex flex-col gap-2 pt-2">
                            <a href="/#lead-form" className="btn-accent text-sm text-center" onClick={() => setIsOpen(false)}>{settings?.nav_cta_electricity || t('home.hero_cta_primary')}</a>
                            <Link to="/login" className="btn-ghost text-sm text-center" onClick={() => setIsOpen(false)}>{settings?.nav_portal_login || t('nav.login')}</Link>
                        </div>
                    </div>
                )}
            </div>
        </nav>
    );
}
