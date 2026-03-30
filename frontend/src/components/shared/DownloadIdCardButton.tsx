import { useState } from 'react';
import { Download, CreditCard, Loader2, Lock } from 'lucide-react';
import toast from 'react-hot-toast';
import api from '@/services/axios';
import { useAuthStore } from '@/hooks/store/authStore';
import { useSettings } from '@/hooks/useSettings';

interface Props {
    variant?: 'button' | 'card'; // 'button' = inline button, 'card' = full card widget
    className?: string;
    userId?: number;
}

export const DownloadIdCardButton = ({ variant = 'button', className, userId }: Props) => {
    const [loading, setLoading] = useState(false);
    const { user } = useAuthStore();
    const { companyName } = useSettings();

    // Is the user approved and profile complete (>= 75%)?
    const isProfileComplete = (user?.profile_completion ?? 0) >= 75;
    const isApproved = user?.role === 'admin' || (user?.status === 'active' && isProfileComplete);

    const lockTitle = !isProfileComplete
        ? "Please complete your profile (75%) to download"
        : "Waiting for Admin approval";

    const handleDownload = async () => {
        if (!isApproved) return;
        setLoading(true);
        try {
            // Get signed download URL from API
            const response = await api.get('/icard/download-url', {
                params: { userId }
            });
            const url = response.data.url;

            // Trigger download by fetching as blob to bypass PWA navigation cache interception
            const fileResponse = await fetch(url);
            
            if (!fileResponse.ok) throw new Error('Failed to download');
            
            const blob = await fileResponse.blob();
            const blobUrl = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = blobUrl;
            
            // Extract filename from Content-Disposition if available, or fallback
            const disposition = fileResponse.headers.get('content-disposition');
            let filename = 'ID_Card.pdf';
            if (disposition && disposition.indexOf('attachment') !== -1) {
                const filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                const matches = filenameRegex.exec(disposition);
                if (matches != null && matches[1]) {
                    filename = matches[1].replace(/['"]/g, '');
                }
            }
            
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(blobUrl);

            toast.success('ID Card downloaded successfully!');
        } catch (error) {
            if (import.meta.env.DEV) {
                console.error('ID Card Download Error:', error);
            }
            toast.error('Failed to generate ID card. Please try again.');
        } finally {
            setLoading(false);
        }
    };

    if (!isApproved) {
        if (variant === 'card') {
            return (
                <div className={`bg-slate-50 rounded-2xl border border-slate-200 p-6 opacity-75 ${className}`}>
                    <div className="flex items-center gap-4">
                        <div className="w-12 h-12 rounded-xl flex items-center justify-center text-2xl bg-slate-100 text-slate-400">
                            <Lock size={24} />
                        </div>
                        <div className="flex-1">
                            <h3 className="font-bold text-slate-400 text-base">ID Card Locked</h3>
                            <p className="text-sm text-slate-400 mt-0.5">
                                {lockTitle}
                            </p>
                        </div>
                    </div>
                </div>
            );
        }

        return (
            <button
                disabled
                className={`flex items-center gap-2 px-4 py-2 rounded-xl font-bold text-sm bg-slate-100 text-slate-400 border border-slate-200 cursor-not-allowed opacity-60 ${className}`}
                title={lockTitle}
            >
                <Lock className="w-4 h-4" />
                <span>Locked</span>
            </button>
        );
    }

    if (variant === 'card') {
        return (
            <div className={`bg-white rounded-2xl border border-slate-200 shadow-sm p-6 ${className}`}
                style={{ borderLeft: '4px solid #FF9500' }}>
                <div className="flex items-center gap-4">
                    <div className="w-12 h-12 rounded-xl flex items-center justify-center text-2xl bg-orange-50 text-orange-600">
                        <CreditCard size={24} />
                    </div>
                    <div className="flex-1">
                        <h3 className="font-bold text-slate-800 text-base">Your ID Card</h3>
                        <p className="text-sm text-slate-500 mt-0.5">
                            Official {companyName} identity card for field use
                        </p>
                    </div>
                    <button
                        onClick={handleDownload}
                        disabled={loading}
                        aria-label={loading ? "Generating ID Card PDF" : "Download ID Card PDF"}
                        aria-busy={loading}
                        className="flex items-center gap-2 px-4 py-2 rounded-xl font-bold text-sm transition-all shadow-sm bg-gradient-to-r from-orange-500 to-amber-500 text-white disabled:opacity-50"
                    >
                        {loading ? (
                            <Loader2 className="w-4 h-4 animate-spin" />
                        ) : (
                            <Download className="w-4 h-4" />
                        )}
                        {loading ? 'Generating...' : 'Download PDF'}
                    </button>
                </div>
            </div>
        );
    }

    // Default: inline button
    return (
        <button
            onClick={handleDownload}
            disabled={loading}
            className={`flex items-center gap-2 px-4 py-2 rounded-xl font-bold text-sm transition-all shadow-md bg-gradient-to-r from-orange-500 to-amber-500 text-white hover:shadow-lg disabled:opacity-50 ${className}`}
        >
            {loading ? (
                <Loader2 className="w-4 h-4 animate-spin" />
            ) : (
                <CreditCard className="w-4 h-4" />
            )}
            <span>{loading ? '...' : 'ID Card'}</span>
        </button>
    );
};
