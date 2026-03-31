import { useState, useRef, useEffect } from 'react';
import { MessageCircle, X, ClipboardList, HelpCircle, UserCircle, ChevronRight, Loader2 } from 'lucide-react';
import axiosInstance from '@/services/axios';

interface SupportContact {
    id: number;
    name: string;
    whatsapp_number: string;
}

export default function WhatsAppFloatButton() {
    const [isOpen, setIsOpen] = useState(false);
    const [contacts, setContacts] = useState<SupportContact[]>([]);
    const [loading, setLoading] = useState(true);
    const popupRef = useRef<HTMLDivElement>(null);

    // Fetch real support contacts from the public API (no auth required)
    useEffect(() => {
        axiosInstance.get<SupportContact[]>('/public/support-contacts')
            .then(res => setContacts(res.data || []))
            .catch(() => {})
            .finally(() => setLoading(false));
    }, []);

    // Close popup on outside click
    useEffect(() => {
        function handleClickOutside(event: MouseEvent) {
            if (popupRef.current && !popupRef.current.contains(event.target as Node)) {
                setIsOpen(false);
            }
        }
        if (isOpen) document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, [isOpen]);

    const primaryContact = contacts[0];

    const handleRedirect = (type: 'apply' | 'faq' | 'chat') => {
        let url = '';
        if (type === 'chat' && primaryContact) {
            // Direct chat: go to the specific agent's personal WhatsApp
            const num = primaryContact.whatsapp_number.replace(/\D/g, '');
            const fullNum = num.startsWith('91') ? num : `91${num}`;
            url = `https://wa.me/${fullNum}?text=${encodeURIComponent('Hello, I need help with PM Surya Ghar.')}`;
        } else if (primaryContact) {
            // Apply / FAQ: go to the primary contact's WA with a trigger keyword for the chatbot
            const num = primaryContact.whatsapp_number.replace(/\D/g, '');
            const fullNum = num.startsWith('91') ? num : `91${num}`;
            const text = type === 'apply' ? '7' : 'menu';
            url = `https://wa.me/${fullNum}?text=${encodeURIComponent(text)}`;
        } else {
            // Fallback: generic message if no contacts configured yet
            url = `https://wa.me/?text=${encodeURIComponent('Hello, I need help with PM Surya Ghar.')}`;
        }
        window.open(url, '_blank', 'noopener,noreferrer');
        setIsOpen(false);
    };

    return (
        <div className="fixed bottom-6 right-6 z-[60]" ref={popupRef}>
            {/* Popup */}
            {isOpen && (
                <div className="absolute bottom-20 right-0 w-80 bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 overflow-hidden flex flex-col animate-in slide-in-from-bottom-5 fade-in duration-300">
                    {/* Header */}
                    <div className="bg-[#128C7E] px-4 py-4 flex items-center justify-between text-white">
                        <div className="flex items-center gap-3">
                            <div className="p-2 bg-white/20 rounded-full">
                                <MessageCircle className="w-6 h-6" />
                            </div>
                            <div>
                                <h3 className="font-bold text-sm leading-tight">
                                    {loading ? 'Loading...' : primaryContact ? primaryContact.name : 'PM Surya Ghar Support'}
                                </h3>
                                <p className="text-[11px] text-green-100">Typically replies instantly</p>
                            </div>
                        </div>
                        <button onClick={() => setIsOpen(false)} className="p-1.5 hover:bg-white/20 rounded-lg transition-colors">
                            <X className="w-5 h-5" />
                        </button>
                    </div>

                    {/* Body */}
                    <div className="p-4 bg-slate-50 dark:bg-slate-900 flex-1">
                        <div className="bg-white dark:bg-slate-800 rounded-lg p-3 shadow-sm border border-slate-100 dark:border-slate-700 text-sm text-slate-700 dark:text-slate-300 mb-4">
                            Hi there! 👋 How can we help you today? Choose an option below to continue on WhatsApp.
                        </div>

                        {loading ? (
                            <div className="flex items-center justify-center py-6 text-slate-400">
                                <Loader2 className="w-5 h-5 animate-spin mr-2" />
                                <span className="text-sm">Loading options...</span>
                            </div>
                        ) : (
                            <div className="space-y-2">
                                <button
                                    onClick={() => handleRedirect('apply')}
                                    className="w-full flex items-center justify-between p-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl hover:border-[#25D366] hover:bg-green-50 dark:hover:bg-green-900/20 transition-all group"
                                >
                                    <div className="flex items-center gap-3 text-slate-700 dark:text-slate-200">
                                        <div className="p-2 bg-green-100 dark:bg-green-900/40 text-[#128C7E] rounded-lg group-hover:bg-[#25D366] group-hover:text-white transition-colors">
                                            <ClipboardList className="w-4 h-4" />
                                        </div>
                                        <span className="font-semibold text-sm">Apply for Solar (Lead)</span>
                                    </div>
                                    <ChevronRight className="w-4 h-4 text-slate-400 group-hover:text-[#25D366]" />
                                </button>

                                <button
                                    onClick={() => handleRedirect('faq')}
                                    className="w-full flex items-center justify-between p-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl hover:border-[#25D366] hover:bg-green-50 dark:hover:bg-green-900/20 transition-all group"
                                >
                                    <div className="flex items-center gap-3 text-slate-700 dark:text-slate-200">
                                        <div className="p-2 bg-blue-100 dark:bg-blue-900/40 text-blue-600 rounded-lg group-hover:bg-[#25D366] group-hover:text-white transition-colors">
                                            <HelpCircle className="w-4 h-4" />
                                        </div>
                                        <span className="font-semibold text-sm">Frequently Asked Questions</span>
                                    </div>
                                    <ChevronRight className="w-4 h-4 text-slate-400 group-hover:text-[#25D366]" />
                                </button>

                                <button
                                    onClick={() => handleRedirect('chat')}
                                    className="w-full flex items-center justify-between p-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl hover:border-[#25D366] hover:bg-green-50 dark:hover:bg-green-900/20 transition-all group"
                                >
                                    <div className="flex items-center gap-3 text-slate-700 dark:text-slate-200">
                                        <div className="p-2 bg-purple-100 dark:bg-purple-900/40 text-purple-600 rounded-lg group-hover:bg-[#25D366] group-hover:text-white transition-colors">
                                            <UserCircle className="w-4 h-4" />
                                        </div>
                                        <div className="text-left">
                                            <div className="font-semibold text-sm">Direct Chat with Expert</div>
                                            {primaryContact && (
                                                <div className="text-[10px] text-slate-400">{primaryContact.name}</div>
                                            )}
                                        </div>
                                    </div>
                                    <ChevronRight className="w-4 h-4 text-slate-400 group-hover:text-[#25D366]" />
                                </button>
                            </div>
                        )}
                    </div>
                </div>
            )}

            {/* Floating Button */}
            {!isOpen && (
                <button
                    onClick={() => setIsOpen(true)}
                    className="bg-[#25D366] text-white p-4 rounded-full shadow-2xl hover:bg-[#128C7E] hover:scale-110 active:scale-95 transition-all outline-none group flex items-center justify-center border border-[#128C7E]"
                    aria-label="Open PM Surya Ghar Assistant"
                >
                    <MessageCircle className="w-8 h-8 fill-current" />
                    <span className="absolute right-16 top-1/2 -translate-y-1/2 bg-white text-[#128C7E] text-xs px-3 py-1.5 rounded-lg shadow-xl opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap font-bold pointer-events-none border border-green-100">
                        Chat with PM Surya Ghar <br/>
                        <span className="font-normal text-[10px] text-gray-500">Apply & FAQs ⚡️</span>
                    </span>
                    <span className="absolute -top-1 -right-1 w-4 h-4 bg-red-500 border-2 border-white rounded-full animate-pulse z-10" />
                </button>
            )}
        </div>
    );
}
