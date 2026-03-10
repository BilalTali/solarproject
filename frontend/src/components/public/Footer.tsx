import { Sun, Phone, MessageCircle, ExternalLink } from 'lucide-react';
import { Link } from 'react-router-dom';
import { useQuery } from '@tanstack/react-query';
import { publicApi } from '@/api/public.api';

const getSetting = (settingsObj: any, key: string, fallback: string) => {
    return settingsObj?.[key] || fallback;
};

export default function Footer() {
    const { data: settings = {} } = useQuery({
        queryKey: ['public-settings'],
        queryFn: publicApi.getSettings,
    });

    return (
        <footer className="bg-dark text-white pt-12 pb-6">
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div className="grid grid-cols-1 md:grid-cols-4 gap-8 pb-8 border-b border-white/10">
                    {/* Brand */}
                    <div>
                        <div className="flex items-center gap-2 mb-3">
                            <div className="w-9 h-9 rounded-full bg-gradient-to-br from-accent to-accent-dark flex items-center justify-center">
                                <Sun className="w-5 h-5 text-white" />
                            </div>
                            <span className="font-display font-bold text-xl">{getSetting(settings, 'company_name', 'SuryaMitra')}</span>
                        </div>
                        <p className="text-sm text-neutral-600 leading-relaxed">
                            {getSetting(settings, 'footer_about_text', 'Facilitating solar rooftop installations under PM Surya Ghar Muft Bijli Yojana across Jammu & Kashmir and Ladakh.')}
                        </p>
                    </div>

                    {/* Contact */}
                    <div>
                        <h4 className="font-display font-semibold mb-3">Contact Us</h4>
                        <div className="flex flex-col gap-2">
                            <a href={`tel:${getSetting(settings, 'company_mobile', '+919876543210')}`} className="flex items-center gap-2 text-sm text-neutral-600 hover:text-accent transition-colors">
                                <Phone className="w-4 h-4" /> {getSetting(settings, 'company_mobile', '+91-98765 43210')}
                            </a>
                            <a href={`https://wa.me/${getSetting(settings, 'company_whatsapp', '919876543210').replace(/[^0-9]/g, '')}`} target="_blank" rel="noreferrer" className="flex items-center gap-2 text-sm text-neutral-600 hover:text-accent transition-colors">
                                <MessageCircle className="w-4 h-4" /> WhatsApp Us
                            </a>
                            <a href="https://pmsuryaghar.gov.in" target="_blank" rel="noreferrer" className="flex items-center gap-2 text-sm text-neutral-600 hover:text-accent transition-colors">
                                <ExternalLink className="w-4 h-4" /> pmsuryaghar.gov.in
                            </a>
                        </div>
                    </div>

                    {/* Links */}
                    <div>
                        <h4 className="font-display font-semibold mb-3">Quick Links</h4>
                        <div className="flex flex-col gap-2">
                            <Link to="/#how-it-works" className="text-sm text-neutral-600 hover:text-accent transition-colors">How It Works</Link>
                            <Link to="/#eligibility" className="text-sm text-neutral-600 hover:text-accent transition-colors">Eligibility Checker</Link>
                            <Link to="/#calculator" className="text-sm text-neutral-600 hover:text-accent transition-colors">Subsidy Calculator</Link>
                            <Link to="/agent/register" className="text-sm text-neutral-600 hover:text-accent transition-colors">Become a Biz Dev Executive</Link>
                            <Link to="/agent/login" className="text-sm text-neutral-600 hover:text-accent transition-colors">Biz Dev Executive Login</Link>
                        </div>
                    </div>

                    {/* Legal & Support */}
                    <div>
                        <h4 className="font-display font-semibold mb-3">Legal & Support</h4>
                        <div className="flex flex-col gap-2">
                            <Link to="/about" className="text-sm text-neutral-600 hover:text-accent transition-colors">About Us</Link>
                            <Link to="/scheme" className="text-sm text-neutral-600 hover:text-accent transition-colors">PM Surya Ghar Scheme</Link>
                            <Link to="/contact" className="text-sm text-neutral-600 hover:text-accent transition-colors">Contact</Link>
                            <Link to="/faq" className="text-sm text-neutral-600 hover:text-accent transition-colors">FAQ</Link>
                            <Link to="/privacy-policy" className="text-sm text-neutral-600 hover:text-accent transition-colors">Privacy Policy</Link>
                            <Link to="/terms-conditions" className="text-sm text-neutral-600 hover:text-accent transition-colors">Terms &amp; Conditions</Link>
                            <Link to="/refund-policy" className="text-sm text-neutral-600 hover:text-accent transition-colors">Refund Policy</Link>
                        </div>
                    </div>
                </div>

                <div className="pt-6 flex flex-col md:flex-row gap-2 justify-between items-start">
                    <p className="text-xs text-neutral-600">
                        {getSetting(settings, 'footer_copyright', '© 2026 SuryaMitra. All rights reserved.')}
                    </p>
                    <p className="text-xs text-neutral-600 max-w-md">
                        {getSetting(settings, 'footer_disclaimer', '⚠️ SuryaMitra is an independent facilitation agency. Not a government body. PM Surya Ghar Muft Bijli Yojana is a Government of India scheme.')}
                    </p>
                </div>
            </div>
        </footer>
    );
}
