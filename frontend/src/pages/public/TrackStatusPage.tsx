import { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { publicApi } from '@/api/public.api';
import Navbar from '@/components/public/Navbar';
import Footer from '@/components/public/Footer';
import SEOHead from '@/components/shared/SEOHead';
import { Search, Loader2, CheckCircle2, Clock, AlertCircle, MapPin, Calendar, User } from 'lucide-react';
import toast from 'react-hot-toast';

export default function TrackStatusPage() {
    const [searchId, setSearchId] = useState('');
    const [queryId, setQueryId] = useState('');

    const { data: trackingData, isLoading, isError } = useQuery({
        queryKey: ['track-application', queryId],
        queryFn: () => publicApi.trackApplication(queryId),
        enabled: !!queryId,
        retry: false,
    });

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        if (searchId.trim().length < 4) {
            toast.error('Please enter at least 4 characters');
            return;
        }
        setQueryId(searchId.trim());
    };

    const steps = [
        { label: 'New', desc: 'Application received' },
        { label: 'Assigned', desc: 'Agent assigned' },
        { label: 'In Progress', desc: 'Processing' },
        { label: 'Verified', desc: 'Manager verified' },
        { label: 'Completed', desc: 'Installed' }
    ];

    const currentStep = trackingData?.data?.step_index || 0;

    return (
        <div className="min-h-screen bg-slate-50 flex flex-col">
            <SEOHead title="Track Application Status" />
            <Navbar />

            <main className="flex-grow py-16 md:py-24">
                <div className="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                    {/* Header */}
                    <div className="text-center mb-12">
                        <h1 className="text-4xl md:text-5xl font-display font-black text-dark mb-4">
                            Track Your <span className="text-accent">Application</span>
                        </h1>
                        <p className="text-neutral-600 text-lg">
                            Enter your Mobile Number or Reference ID to check your installation progress.
                        </p>
                    </div>

                    {/* Search Form */}
                    <form onSubmit={handleSearch} className="mb-12">
                        <div className="relative group">
                            <div className="absolute inset-x-0 bottom-0 h-4 bg-primary/5 blur-xl -z-10 opacity-0 group-focus-within:opacity-100 transition-opacity" />
                            <Search className="absolute left-6 top-1/2 -translate-y-1/2 text-neutral-400 group-focus-within:text-accent transition-colors" size={24} />
                            <input
                                type="text"
                                placeholder="Enter Mobile Number or Ref ID (e.g. 01J...)"
                                className="w-full pl-16 pr-32 py-6 rounded-3xl border border-neutral-200 focus:outline-none focus:ring-8 focus:ring-accent/5 focus:border-accent bg-white shadow-card transition-all text-xl font-medium"
                                value={searchId}
                                onChange={(e) => setSearchId(e.target.value)}
                            />
                            <button
                                type="submit"
                                disabled={isLoading}
                                className="absolute right-4 top-1/2 -translate-y-1/2 bg-dark text-white hover:bg-primary px-8 py-3 rounded-2xl font-bold transition-all disabled:opacity-50"
                            >
                                {isLoading ? <Loader2 className="w-5 h-5 animate-spin" /> : 'Track'}
                            </button>
                        </div>
                    </form>

                    {/* Results */}
                    {queryId && !isLoading && trackingData?.success && (
                        <div className="animate-in fade-in slide-in-from-bottom-4 duration-500">
                            <div className="bg-white rounded-[2.5rem] p-8 md:p-12 shadow-xl border border-white relative overflow-hidden">
                                {/* Status Header */}
                                <div className="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-12 pb-12 border-b border-neutral-100">
                                    <div>
                                        <div className="text-[10px] font-black uppercase tracking-[0.2em] text-accent mb-2">Application Reference</div>
                                        <div className="font-mono text-2xl font-bold text-dark">{trackingData.data.reference}</div>
                                    </div>
                                    <div className="bg-neutral-50 px-6 py-4 rounded-2xl border border-neutral-100">
                                        <div className="text-[10px] font-black uppercase tracking-[0.2em] text-neutral-400 mb-1 text-center md:text-left">Current Status</div>
                                        <div className="text-xl font-display font-black text-primary text-center md:text-left">{trackingData.data.status}</div>
                                    </div>
                                </div>

                                {/* Main Status Content */}
                                <div className="flex items-start gap-6 bg-primary/5 p-8 rounded-3xl border border-primary/10 mb-12">
                                    <div className="w-14 h-14 bg-white rounded-2xl flex items-center justify-center text-primary shadow-sm shrink-0">
                                        <Clock size={28} />
                                    </div>
                                    <div>
                                        <h3 className="text-xl font-display font-bold text-dark mb-2">{trackingData.data.meaning}</h3>
                                        <p className="text-neutral-600 leading-relaxed mb-4">{trackingData.data.next_step}</p>
                                        <div className="flex items-center gap-4 text-xs font-bold text-neutral-400">
                                            <span className="flex items-center gap-1.5"><MapPin size={14} /> {trackingData.data.district}</span>
                                            <span className="flex items-center gap-1.5"><Calendar size={14} /> Updated {new Date(trackingData.data.updated_at).toLocaleDateString()}</span>
                                            <span className="flex items-center gap-1.5"><User size={14} /> {trackingData.data.beneficiary}</span>
                                        </div>
                                    </div>
                                </div>

                                {/* Progress Stepper */}
                                <div className="relative">
                                    <div className="hidden md:flex justify-between mb-2">
                                        {steps.map((step, idx) => {
                                            const stepNum = idx + 1;
                                            const isCompleted = currentStep > stepNum;
                                            const isActive = currentStep === stepNum;

                                            return (
                                                <div key={idx} className="flex-1 text-center">
                                                    <div className="relative mb-4 flex justify-center">
                                                        {idx > 0 && (
                                                            <div className={`absolute right-1/2 top-1/2 -translate-y-1/2 w-full h-1 -z-10 ${isCompleted || isActive ? 'bg-primary' : 'bg-neutral-100'}`} />
                                                        )}
                                                        <div className={`w-10 h-10 rounded-full flex items-center justify-center border-4 transition-all duration-500 bg-white ${isCompleted ? 'border-primary text-primary' : isActive ? 'border-accent text-accent scale-110' : 'border-neutral-100 text-neutral-300'}`}>
                                                            {isCompleted ? <CheckCircle2 size={24} fill="currentColor" className="text-white" /> : stepNum}
                                                        </div>
                                                    </div>
                                                    <div className={`text-[10px] font-black uppercase tracking-widest mb-1 ${isActive ? 'text-dark' : 'text-neutral-400'}`}>{step.label}</div>
                                                    <div className="text-[9px] text-neutral-400 font-medium px-2">{step.desc}</div>
                                                </div>
                                            );
                                        })}
                                    </div>
                                    {/* Mobile Vertical Stepper */}
                                    <div className="md:hidden flex flex-col gap-8">
                                        {steps.map((step, idx) => {
                                            const stepNum = idx + 1;
                                            const isCompleted = currentStep > stepNum;
                                            const isActive = currentStep === stepNum;

                                            return (
                                                <div key={idx} className="flex items-center gap-4">
                                                    <div className={`w-10 h-10 rounded-full flex items-center justify-center border-4 transition-all bg-white shrink-0 ${isCompleted ? 'border-primary text-primary' : isActive ? 'border-accent text-accent' : 'border-neutral-100 text-neutral-300'}`}>
                                                        {isCompleted ? <CheckCircle2 size={20} /> : stepNum}
                                                    </div>
                                                    <div>
                                                        <div className={`text-xs font-black uppercase tracking-widest ${isActive ? 'text-dark' : 'text-neutral-400'}`}>{step.label}</div>
                                                        <div className="text-[10px] text-neutral-400">{step.desc}</div>
                                                    </div>
                                                </div>
                                            );
                                        })}
                                    </div>
                                </div>
                            </div>
                        </div>
                    )}

                    {/* Error State */}
                    {queryId && !isLoading && (isError || !trackingData?.success) && (
                        <div className="bg-red-50 border-2 border-red-100 rounded-3xl p-8 text-center animate-in zoom-in-95">
                            <AlertCircle className="w-12 h-12 text-red-400 mx-auto mb-4" />
                            <h3 className="text-xl font-display font-bold text-red-900 mb-2">Application Not Found</h3>
                            <p className="text-red-700/80 mb-6">We couldn't find any application matching "{queryId}". Please double check the ID or Mobile Number.</p>
                            <button
                                onClick={() => { setSearchId(''); setQueryId(''); }}
                                className="text-red-900 font-bold underline underline-offset-4 hover:text-red-600 transition-colors"
                            >
                                Try again
                            </button>
                        </div>
                    )}

                    {/* Guidelines */}
                    {!queryId && (
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6 animate-in fade-in duration-700">
                            <div className="bg-white/50 backdrop-blur-sm p-8 rounded-3xl border border-white shadow-sm">
                                <h4 className="font-display font-bold text-dark mb-4 flex items-center gap-2">
                                    <CheckCircle2 className="text-primary w-5 h-5" /> Online Tracking
                                </h4>
                                <p className="text-neutral-600 text-sm leading-relaxed">
                                    Our real-time tracker allows you to see exactly where your solar application stands in our internal facilitation process.
                                </p>
                            </div>
                            <div className="bg-white/50 backdrop-blur-sm p-8 rounded-3xl border border-white shadow-sm">
                                <h4 className="font-display font-bold text-dark mb-4 flex items-center gap-2">
                                    <Clock className="text-accent w-5 h-5" /> 24-Hour Updates
                                </h4>
                                <p className="text-neutral-600 text-sm leading-relaxed">
                                    Status is typically updated within 24 hours of any movement in your application file. For urgent queries, contact your assigned agent.
                                </p>
                            </div>
                        </div>
                    )}
                </div>
            </main>

            <Footer />
        </div>
    );
}
