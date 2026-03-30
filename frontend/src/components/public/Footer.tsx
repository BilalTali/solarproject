import { Link } from 'react-router-dom';
import { Mail, Phone, MapPin, Sun, ExternalLink } from 'lucide-react';
import { useQuery } from '@tanstack/react-query';
import { publicApi } from '@/services/public.api';
import WhatsAppButton from '@/components/shared/WhatsAppButton';

const getSetting = (settingsObj: any, key: string, fallback: string) => {
    return settingsObj?.[key] || fallback;
};

export default function Footer() {
    const { data: settings = {} } = useQuery({
        queryKey: ['public-settings'],
        queryFn: publicApi.getSettings,
    });

    const companyName = getSetting(settings, 'company_name', 'AndleebSurya');
    const email = getSetting(settings, 'company_email', 'info@andleebsurya.com');
    const phone = getSetting(settings, 'company_mobile', '+91 9419012345');
    const address = getSetting(settings, 'company_address', 'Srinagar, Jammu & Kashmir');

    return (
        <footer className="bg-gradient-to-b from-slate-900 to-[#020617] text-white pt-12 pb-6">
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div className="grid grid-cols-1 md:grid-cols-4 gap-8 pb-8 border-b border-white/10">
                    {/* Brand */}
                    <div>
                        <div className="flex items-center gap-2 mb-3">
                            <div className="w-9 h-9 rounded-full bg-gradient-to-br from-accent to-accent-dark flex items-center justify-center">
                                <Sun className="w-5 h-5 text-white" />
                            </div>
                            <span className="font-display font-bold text-xl">{companyName}</span>
                        </div>
                        <p className="text-sm text-neutral-600 leading-relaxed">
                            {getSetting(settings, 'footer_about_text', 'Facilitating solar rooftop installations under PM Surya Ghar Muft Bijli Yojana across Jammu & Kashmir and Ladakh.')}
                        </p>
                    </div>

                    {/* Contact */}
                    <div>
                        <h4 className="font-display font-semibold mb-3">Contact Us</h4>
                        <div className="flex flex-col gap-2">
                            <a href={`tel:${phone}`} className="flex items-center gap-2 text-sm text-neutral-600 hover:text-accent transition-colors">
                                <Phone className="w-4 h-4" /> {phone}
                            </a>
                            <a href={`mailto:${email}`} className="flex items-center gap-2 text-sm text-neutral-600 hover:text-accent transition-colors">
                                <Mail className="w-4 h-4" /> {email}
                            </a>
                            <div className="flex items-start gap-2 text-sm text-neutral-600 mt-1">
                                <MapPin className="w-4 h-4 shrink-0 mt-0.5" />
                                <span>{address}</span>
                            </div>
                        </div>
                    </div>

                    {/* Quick Links */}
                    <div>
                        <h4 className="font-display font-semibold mb-3">Quick Links</h4>
                        <ul className="flex flex-col gap-2">
                            <li><Link to="/about" className="text-sm text-neutral-600 hover:text-accent transition-colors">About Us</Link></li>
                            <li><Link to="/documents" className="text-sm text-neutral-600 hover:text-accent transition-colors">Public Documents</Link></li>
                            <li><Link to="/contact" className="text-sm text-neutral-600 hover:text-accent transition-colors">Contact Support</Link></li>
                            <li><Link to="/scheme" className="text-sm text-neutral-600 hover:text-accent transition-colors">Scheme Details</Link></li>
                            <li><Link to="/faq" className="text-sm text-neutral-600 hover:text-accent transition-colors">FAQs</Link></li>
                            <li><Link to="/solar-subsidy-calculator" className="text-sm text-neutral-600 hover:text-accent transition-colors">Subsidy Calculator</Link></li>
                            <li><Link to="/state-wise-subsidy" className="text-sm text-neutral-600 hover:text-accent transition-colors">State Subsidies</Link></li>
                        </ul>
                    </div>

                    {/* Legal */}
                    <div>
                        <h4 className="font-display font-semibold mb-3">Legal & Portals</h4>
                        <ul className="flex flex-col gap-2">
                            <li><Link to="/privacy-policy" className="text-sm text-neutral-600 hover:text-accent transition-colors">Privacy Policy</Link></li>
                            <li><Link to="/terms-conditions" className="text-sm text-neutral-600 hover:text-accent transition-colors">Terms & Conditions</Link></li>
                            <li><Link to="/refund-policy" className="text-sm text-neutral-600 hover:text-accent transition-colors">Refund Policy</Link></li>
                            <li><Link to="/agent/login" className="text-sm text-neutral-600 hover:text-accent transition-colors flex items-center gap-1 mt-2">Agent Portal <ExternalLink className="w-3 h-3" /></Link></li>
                            <li><a href="https://pmsuryaghar.gov.in" target="_blank" rel="noopener noreferrer" className="text-sm text-neutral-600 hover:text-accent transition-colors flex items-center gap-1">National Portal <ExternalLink className="w-3 h-3" /></a></li>
                        </ul>
                    </div>
                </div>

                <div className="pt-6 flex flex-col md:flex-row items-center justify-between gap-4">
                    <p className="text-xs text-neutral-600 text-center md:text-left">
                        &copy; {new Date().getFullYear()} {companyName}. All rights reserved.<br />
                        Not an official government website. We are a registered facilitator for the PM Surya Ghar scheme.
                    </p>
                </div>
            </div>
            
            {/* INJECT WHATSAPP BUTTON HERE */}
            <WhatsAppButton />
        </footer>
    );
}
