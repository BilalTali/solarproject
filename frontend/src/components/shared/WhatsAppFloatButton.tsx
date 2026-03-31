import { useState, useRef, useEffect } from 'react';
import { MessageCircle, X, ClipboardList, HelpCircle, UserCircle, ChevronRight } from 'lucide-react';

export default function WhatsAppFloatButton() {
    const [isOpen, setIsOpen] = useState(false);
    const popupRef = useRef<HTMLDivElement>(null);
    const waNumber = import.meta.env.VITE_WA_BOT_NUMBER || '911234567890';

    // Close on outside click
    useEffect(() => {
        function handleClickOutside(event: MouseEvent) {
            if (popupRef.current && !popupRef.current.contains(event.target as Node)) {
                setIsOpen(false);
            }
        }
        if (isOpen) {
            document.addEventListener('mousedown', handleClickOutside);
        }
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, [isOpen]);

    const handleRedirect = (code: string) => {
        const waLink = `https://wa.me/${waNumber}?text=${encodeURIComponent(code)}`;
        window.open(waLink, '_blank', 'noopener,noreferrer');
        setIsOpen(false);
    };

    return (
        <div className="fixed bottom-6 right-6 z-[60]" ref={popupRef}>
            {/* The Popup Window */}
            {isOpen && (
                <div className="absolute bottom-20 right-0 w-80 bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 overflow-hidden flex flex-col animate-in slide-in-from-bottom-5 fade-in duration-300">
                    <div className="bg-[#128C7E] px-4 py-4 flex items-center justify-between text-white">
                        <div className="flex items-center gap-3">
                            <div className="p-2 bg-white/20 rounded-full">
                                <MessageCircle className="w-6 h-6" />
                            </div>
                            <div>
                                <h3 className="font-bold text-sm leading-tight">Virtual Assistant</h3>
                                <p className="text-[11px] text-green-100">Typically replies instantly</p>
                            </div>
                        </div>
                        <button 
                            onClick={() => setIsOpen(false)}
                            className="p-1.5 hover:bg-white/20 rounded-lg transition-colors"
                        >
                            <X className="w-5 h-5" />
                        </button>
                    </div>

                    <div className="p-4 bg-slate-50 dark:bg-slate-900 flex-1">
                        <div className="bg-white dark:bg-slate-800 rounded-lg p-3 shadow-sm border border-slate-100 dark:border-slate-700 text-sm text-slate-700 dark:text-slate-300 mb-4 inline-block relative after:absolute after:top-0 after:-left-2 after:border-r-[10px] after:border-r-white dark:after:border-r-slate-800 after:border-t-[10px] after:border-t-transparent after:border-b-[10px] after:border-b-transparent">
                            Hi there! 👋 How can we help you today? Please choose an option below to continue on WhatsApp.
                        </div>

                        <div className="space-y-2">
                            <button 
                                onClick={() => handleRedirect('7')}
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
                                onClick={() => handleRedirect('menu')}
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
                                onClick={() => handleRedirect('8')}
                                className="w-full flex items-center justify-between p-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl hover:border-[#25D366] hover:bg-green-50 dark:hover:bg-green-900/20 transition-all group"
                            >
                                <div className="flex items-center gap-3 text-slate-700 dark:text-slate-200">
                                    <div className="p-2 bg-purple-100 dark:bg-purple-900/40 text-purple-600 rounded-lg group-hover:bg-[#25D366] group-hover:text-white transition-colors">
                                        <UserCircle className="w-4 h-4" />
                                    </div>
                                    <span className="font-semibold text-sm">Direct Chat with Expert</span>
                                </div>
                                <ChevronRight className="w-4 h-4 text-slate-400 group-hover:text-[#25D366]" />
                            </button>
                        </div>
                    </div>
                </div>
            )}

            {/* The Floating Button */}
            {!isOpen && (
                <button 
                    onClick={() => setIsOpen(true)}
                    className="bg-[#25D366] text-white p-4 rounded-full shadow-2xl hover:bg-[#128C7E] hover:scale-110 active:scale-95 transition-all outline-none group flex items-center justify-center border border-[#128C7E]"
                    aria-label="Open PM Surya Ghar Assistant"
                >
                    <MessageCircle className="w-8 h-8 fill-current" />
                    
                    {/* Tooltip on hover */}
                    <span className="absolute right-16 top-1/2 -translate-y-1/2 bg-white text-[#128C7E] text-xs px-3 py-1.5 rounded-lg shadow-xl opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap font-bold pointer-events-none border border-green-100">
                        Chat with PM Surya Ghar <br/>
                        <span className="font-normal text-[10px] text-gray-500">Apply & FAQs ⚡️</span>
                    </span>
                    
                    {/* Notification Badge Dot */}
                    <span className="absolute -top-1 -right-1 w-4 h-4 bg-red-500 border-2 border-white rounded-full animate-pulse z-10" />
                </button>
            )}
        </div>
    );
}
