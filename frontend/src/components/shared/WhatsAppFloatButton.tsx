import { MessageCircle } from 'lucide-react';

export default function WhatsAppFloatButton() {
    // Determine the WhatsApp number to link out to.
    // Replace the fallback number with your actual provisioned number from Meta
    const waNumber = import.meta.env.VITE_WA_BOT_NUMBER || '911234567890';
    const waLink = `https://wa.me/${waNumber}?text=Hi`;

    return (
        <a 
            href={waLink}
            target="_blank"
            rel="noreferrer"
            className="fixed bottom-6 right-6 z-[60] bg-[#25D366] text-white p-4 rounded-full shadow-2xl hover:bg-[#128C7E] hover:scale-110 active:scale-95 transition-all outline-none group flex items-center justify-center cursor-pointer border border-[#128C7E]"
            aria-label="Chat with PM Surya Ghar on WhatsApp"
        >
            <MessageCircle className="w-8 h-8 fill-current" />
            
            {/* Tooltip on hover */}
            <span className="absolute right-16 top-1/2 -translate-y-1/2 bg-white text-[#128C7E] text-xs px-3 py-1.5 rounded-lg shadow-xl opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap font-bold pointer-events-none border border-green-100">
                Chat with PM Surya Ghar <br/>
                <span className="font-normal text-[10px] text-gray-500">Apply instantly ⚡️</span>
            </span>
            
            {/* Notification Badge Dot */}
            <span className="absolute -top-1 -right-1 w-4 h-4 bg-red-500 border-2 border-white rounded-full animate-pulse z-10" />
        </a>
    );
}
