import { useState, useRef, useEffect } from 'react';
import { MessageCircle, X, ClipboardList, HelpCircle, UserCircle, ChevronRight, Loader2 } from 'lucide-react';
import axiosInstance from '@/services/axios';
import { useSettings } from '@/hooks/useSettings';

interface SupportContact {
    id: number;
    name: string;
    whatsapp_number: string;
}

export default function WhatsAppFloatButton() {
    const [isOpen, setIsOpen] = useState(false);
    const [contacts, setContacts] = useState<SupportContact[]>([]);
    const [loading, setLoading] = useState(true);
    const [companyWa, setCompanyWa] = useState('+918899055335');
    const popupRef = useRef<HTMLDivElement>(null);
    const { settings } = useSettings();

    // Fetch real support contacts from the public API (no auth required)
    useEffect(() => {
        axiosInstance.get<{ contacts: SupportContact[], company_whatsapp?: string }>('/public/support-contacts')
            .then(res => {
                setContacts(res.data.contacts || []);
                if (res.data.company_whatsapp) {
                    setCompanyWa(res.data.company_whatsapp);
                }
            })
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
        
        const targetNumber = primaryContact ? primaryContact.whatsapp_number : (settings?.company_whatsapp || companyWa || '');
        const num = targetNumber.replace(/\D/g, '');
        const fullNum = num ? (num.startsWith('91') ? num : `91${num}`) : '';

        if (type === 'chat') {
            url = fullNum ? `https://wa.me/${fullNum}?text=${encodeURIComponent('SUPPORT')}` : `https://wa.me/?text=${encodeURIComponent('SUPPORT')}`;
        } else {
            const text = type === 'apply' ? 'APPLY' : 'FAQ';
            url = fullNum ? `https://wa.me/${fullNum}?text=${encodeURIComponent(text)}` : `https://wa.me/?text=${encodeURIComponent(text)}`;
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
                                    className={`w-full flex items-center justify-between p-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl transition-all group hover:border-[#25D366] hover:bg-green-50 dark:hover:bg-green-900/20`}
                                >
                                    <div className="flex items-center gap-3 text-slate-700 dark:text-slate-200">
                                        <div className={`p-2 rounded-lg transition-colors bg-green-100 dark:bg-green-900/40 text-[#128C7E] group-hover:bg-[#25D366] group-hover:text-white`}>
                                            <ClipboardList className="w-4 h-4" />
                                        </div>
                                        <div className="text-left">
                                            <span className="font-semibold text-sm block">Apply for Solar (Lead)</span>
                                        </div>
                                    </div>
                                    <ChevronRight className="w-4 h-4 text-slate-400 group-hover:text-[#25D366]" />
                                </button>

                                <button
                                    onClick={() => handleRedirect('faq')}
                                    className={`w-full flex items-center justify-between p-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl transition-all group hover:border-[#25D366] hover:bg-green-50 dark:hover:bg-green-900/20`}
                                >
                                    <div className="flex items-center gap-3 text-slate-700 dark:text-slate-200">
                                        <div className={`p-2 rounded-lg transition-colors bg-blue-100 dark:bg-blue-900/40 text-blue-600 group-hover:bg-[#25D366] group-hover:text-white`}>
                                            <HelpCircle className="w-4 h-4" />
                                        </div>
                                        <div className="text-left">
                                            <span className="font-semibold text-sm block">Frequently Asked Questions</span>
                                        </div>
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
