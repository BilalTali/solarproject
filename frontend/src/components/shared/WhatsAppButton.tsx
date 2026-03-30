import { useState } from 'react';
import { HelpCircle } from 'lucide-react';
import HelpCenterModal from './HelpCenterModal';

export default function WhatsAppButton() {
    const [isModalOpen, setIsModalOpen] = useState(false);

    return (
        <>
            <button 
                onClick={() => setIsModalOpen(true)}
                className="fixed bottom-6 left-6 z-50 bg-[#25D366] text-white p-4 rounded-full shadow-2xl hover:bg-[#128C7E] hover:scale-110 active:scale-95 transition-all outline-none group flex items-center justify-center animate-bounce-slow"
                aria-label="Open Help Center"
            >
                <HelpCircle className="w-8 h-8" />
                <span className="absolute left-16 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 text-xs px-3 py-1.5 rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap font-semibold pointer-events-none border border-slate-100 dark:border-slate-700">
                    Need Help? Chat with us!
                </span>
            </button>

            <HelpCenterModal 
                isOpen={isModalOpen} 
                onClose={() => setIsModalOpen(false)} 
            />
        </>
    );
}
