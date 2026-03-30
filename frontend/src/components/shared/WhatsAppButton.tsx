import { MessageCircle } from 'lucide-react';
import { useQuery } from '@tanstack/react-query';
import { publicApi } from '@/services/public.api';

export default function WhatsAppButton() {
    const { data: settings = {} } = useQuery({
        queryKey: ['public-settings'],
        queryFn: publicApi.getSettings,
        staleTime: Infinity,
    });

    const mobile = (settings as any).company_mobile || '919876543210';
    // Clean non-digits from mobile
    const whatsappNum = String(mobile).replace(/\D/g, '');
    const prefillMessage = encodeURIComponent("Hello! I want to know my eligibility for PM Surya Ghar subsidy.");

    const whatsappLink = `https://wa.me/${whatsappNum}?text=${prefillMessage}`;

    return (
        <a 
            href={whatsappLink}
            target="_blank"
            rel="noopener noreferrer"
            className="fixed bottom-6 left-6 z-50 bg-[#25D366] text-white p-4 rounded-full shadow-2xl hover:bg-[#128C7E] hover:scale-110 active:scale-95 transition-all outline-none group flex items-center justify-center animate-bounce-slow"
            aria-label="Chat on WhatsApp"
        >
            <MessageCircle className="w-8 h-8" />
            <span className="absolute left-16 bg-white text-slate-800 text-xs px-3 py-1.5 rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap font-semibold pointer-events-none">
                Chat with us!
            </span>
        </a>
    );
}
