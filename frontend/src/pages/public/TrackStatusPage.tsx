import { useState, useMemo } from 'react';
import { useQuery } from '@tanstack/react-query';
import { publicApi } from '@/api/public.api';
import Navbar from '@/components/public/Navbar';
import Footer from '@/components/public/Footer';
import SEOHead from '@/components/shared/SEOHead';
import { 
    Search, Loader2, CheckCircle2, User, 
    ClipboardList, FileCheck, Map, Building2, Sun, Zap, FileSignature, Banknote,
    ChevronRight, ArrowLeft, ShieldCheck, 
    Activity, Database
} from 'lucide-react';
import toast from 'react-hot-toast';

export default function TrackStatusPage() {
    const [searchId, setSearchId] = useState('');
    const [queryId, setQueryId] = useState('');

    const { data: trackingData, isLoading } = useQuery({
        queryKey: ['track-application', queryId],
        queryFn: () => publicApi.trackApplication(queryId),
        enabled: !!queryId,
        retry: false,
    });

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        const cleanId = searchId.trim();
        if (cleanId.length < 4) {
            toast.error('Query too brief for global search');
            return;
        }
        setQueryId(cleanId);
    };

    const steps = useMemo(() => [
        { label: 'Received', desc: 'Query Entry', icon: ClipboardList },
        { label: 'Portal Reg', desc: 'MNRE Registered', icon: FileCheck },
        { label: 'Site Survey', desc: 'Tech Inspection', icon: Map },
        { label: 'Financing', desc: 'Bank Clearance', icon: Building2 },
        { label: 'Installation', desc: 'Solar Mounted', icon: Sun },
        { label: 'Commissioned', desc: 'System Live', icon: Zap },
        { label: 'Applied', desc: 'Subsidy Claim', icon: FileSignature },
        { label: 'Processing', desc: 'Verification', icon: Loader2 },
        { label: 'Disbursed', desc: 'Payment Paid', icon: Banknote }
    ], []);

    const currentStep = trackingData?.data?.step_index || 0;

    return (
        <div className="min-h-screen bg-[#FDFDFD] text-slate-900 selection:bg-accent selection:text-white font-sans overflow-x-hidden flex flex-col">
            <SEOHead title="Solar Dashboard | Track Your Application" />
            <Navbar />

            {/* Light Immersive Background Elements */}
            <div className="fixed inset-0 pointer-events-none overflow-hidden flex justify-center z-0">
                <div className="absolute top-[-10%] left-[-10%] w-[50vw] h-[50vw] bg-accent/5 rounded-full blur-[120px]" />
                <div className="absolute top-[20%] right-[-10%] w-[40vw] h-[40vw] bg-primary/5 rounded-full blur-[100px]" />
                <div className="absolute bottom-[-20%] left-[20%] w-[60vw] h-[60vw] bg-yellow-400/5 rounded-full blur-[150px]" />
                <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full h-full bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-[0.01]" />
            </div>

            <main className="relative z-10 py-16 md:py-24 px-4 w-full flex-grow flex flex-col items-center">
                <div className="w-full max-w-6xl mx-auto flex flex-col items-center gap-12">
                    
                    {/* Header */}
                    <div className="text-center space-y-6 pt-10 animate-in fade-in slide-in-from-top-12 duration-1000">
                        <div className="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white border border-slate-200 shadow-sm text-slate-600 text-[10px] font-black uppercase tracking-[0.2em] relative group">
                            <div className="absolute -inset-1 bg-accent/20 rounded-full blur opacity-0 group-hover:opacity-100 transition-opacity" />
                            <Activity size={12} className="text-accent animate-pulse relative z-10" />
                            <span className="relative z-10">Global Tracking Enabled</span>
                        </div>

                        <h1 className="text-5xl md:text-7xl lg:text-[5.5rem] font-display font-black text-slate-900 leading-[1.05] tracking-tight">
                            Track Your Solar <br />
                            <span className="text-accent italic drop-shadow-sm inline-block mt-3">Application</span>
                        </h1>
                        <p className="text-slate-500 font-medium text-lg md:text-xl max-w-xl mx-auto">
                            Check the real-time status of your PM Surya Ghar journey using your Mobile Number or Reference ID.
                        </p>
                    </div>

                    {/* Glowing Command Search */}
                    <div className="w-full max-w-2xl relative z-20">
                        <form onSubmit={handleSearch} className="group relative">
                            {/* Hover glow */}
                            <div className="absolute -inset-3 bg-gradient-to-r from-accent/10 to-primary/10 rounded-full blur-2xl opacity-40 group-hover:opacity-100 transition-opacity duration-700" />
                            
                            <div className="relative bg-white shadow-[0_8px_40px_rgba(0,0,0,0.06)] p-2.5 rounded-[3rem] flex items-center transition-all border border-slate-100">
                                <Search className="text-slate-400 ml-5 mr-3 shrink-0" size={24} />
                                <input
                                    type="text"
                                    placeholder="8899056335"
                                    className="flex-grow bg-transparent border-none focus:outline-none focus:ring-0 text-xl font-bold text-slate-800 placeholder:text-slate-300 min-w-0"
                                    value={searchId}
                                    onChange={(e) => setSearchId(e.target.value)}
                                />
                                <button
                                    type="submit"
                                    disabled={isLoading}
                                    className="bg-[#0f172a] hover:bg-slate-800 text-white px-8 py-5 md:px-10 md:py-6 rounded-[3rem] font-black uppercase tracking-widest text-[11px] flex items-center gap-3 shadow-lg shadow-slate-900/20 active:scale-95 transition-all shrink-0 group/btn"
                                >
                                    {isLoading ? <Loader2 className="w-5 h-5 animate-spin" /> : 'TRACK NOW'}
                                    {!isLoading && <ChevronRight size={16} className="text-white/70 group-hover/btn:translate-x-1 transition-transform" />}
                                </button>
                            </div>
                        </form>
                    </div>

                    {/* Result Interface */}
                    {queryId && !isLoading && trackingData?.success && (
                        <div className="w-full max-w-5xl animate-in slide-in-from-bottom-10 fade-in duration-700 ease-out space-y-10 mt-8">
                            
                            {/* Top row: Two cards matching user screenshot */}
                            <div className="grid grid-cols-1 md:grid-cols-[1.2fr_1fr] lg:grid-cols-[1.4fr_1fr] gap-8 items-stretch">
                                
                                {/* Status Card */}
                                <div className="bg-white rounded-[2.5rem] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100 p-10 md:p-12 relative overflow-hidden flex flex-col items-start min-h-[360px] group/card hover:shadow-[0_12px_40px_rgb(0,0,0,0.06)] transition-all duration-500">
                                    <div className="absolute top-0 right-0 w-64 h-64 bg-accent/5 blur-3xl -z-10 rounded-full group-hover/card:scale-125 transition-transform duration-1000" />
                                    
                                    <div className="flex flex-col gap-8 w-full relative z-10 h-full">
                                        <div>
                                            <span className="inline-flex bg-orange-50 text-accent text-[9px] font-black uppercase tracking-[0.25em] px-3 py-1.5 rounded-md border border-orange-100/50">
                                                Current Status Level
                                            </span>
                                        </div>
                                        
                                        <div className="flex flex-col sm:flex-row items-start sm:items-center gap-6 flex-grow">
                                            <div className="w-24 h-24 bg-[#0A1A3A] text-white rounded-[1.75rem] flex items-center justify-center shadow-xl shrink-0 group-hover/card:shadow-primary/20 transition-all">
                                                {(() => {
                                                    const StepIcon = steps[currentStep - 1]?.icon || Sun;
                                                    return <StepIcon size={44} className="opacity-90" />;
                                                })()}
                                            </div>
                                            <div className="space-y-4">
                                                <h2 className="text-4xl lg:text-[2.75rem] font-display font-black text-slate-900 leading-[1.1] tracking-tight">
                                                    {trackingData.data.status}
                                                </h2>
                                                <p className="text-slate-500 font-medium text-lg leading-relaxed max-w-sm">
                                                    {trackingData.data.meaning || "Your solar energy journey has reached this successful milestone."}
                                                </p>
                                            </div>
                                        </div>

                                        <div className="flex items-center gap-4 mt-auto pt-6 w-full flex-wrap border-t border-slate-50">
                                            <div className="flex items-center gap-2 bg-slate-50 border border-slate-100 rounded-full px-4 py-2 text-xs font-bold text-slate-600">
                                                <div className="w-2 h-2 rounded-full bg-success"></div>
                                                {trackingData.data.reference}
                                            </div>
                                            <div className="text-[10px] font-black uppercase text-slate-400 tracking-wider">
                                                Updated {new Date(trackingData.data.updated_at).toLocaleDateString('en-GB', {day: '2-digit', month: 'short'})}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {/* User Info Card */}
                                <div className="bg-white rounded-[2.5rem] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100 p-10 flex flex-col items-center justify-between min-h-[360px] relative hover:shadow-[0_12px_40px_rgb(0,0,0,0.06)] transition-all duration-500 group/user">
                                    <div className="flex flex-col items-center gap-5 w-full mt-2">
                                        <div className="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center text-slate-800 border border-slate-100 shadow-sm relative group-hover/user:scale-105 transition-transform duration-500">
                                            <User size={40} strokeWidth={1.5} />
                                        </div>
                                        <div className="space-y-1.5 text-center">
                                            <div className="text-[9px] font-black text-slate-400 uppercase tracking-[0.25em]">Beneficiary Name</div>
                                            <div className="text-2xl font-display font-black text-slate-900 tracking-tight uppercase">
                                                {trackingData.data.beneficiary}
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div className="flex items-center gap-4 w-full mt-auto pt-8">
                                        <div className="flex-1 bg-slate-50 rounded-[1.25rem] p-5 flex flex-col items-center justify-center border border-slate-100/60">
                                            <div className="text-[8px] font-black text-slate-400 uppercase tracking-[0.25em] mb-1.5 line-clamp-1 text-center">State Level</div>
                                            <div className="text-sm font-bold text-slate-700 capitalize text-center">{trackingData.data.district || 'Verified'}</div>
                                        </div>
                                        <div className="flex-1 bg-slate-50 rounded-[1.25rem] p-5 flex flex-col items-center justify-center border border-slate-100/60">
                                            <div className="text-[8px] font-black text-slate-400 uppercase tracking-[0.25em] mb-1.5 line-clamp-1 text-center">System Verification</div>
                                            <div className="text-sm font-bold text-success flex items-center justify-center gap-1.5">
                                                <ShieldCheck size={16} /> Secure
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            {/* Updated Timeline / Energy Flow */}
                            <div className="bg-white rounded-[2.5rem] p-10 md:p-14 border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] mt-4 relative overflow-hidden group/timeline hover:shadow-[0_12px_40px_rgb(0,0,0,0.06)] transition-all duration-500">
                                <div className="text-center mb-16 space-y-2">
                                    <div className="text-[10px] font-black text-accent uppercase tracking-[0.3em]">Progress Tracker</div>
                                    <h3 className="text-3xl font-display font-black text-slate-900 tracking-tight">Project Milestones</h3>
                                </div>

                                <div className="relative">
                                    {/* Connector lines (Desktop) */}
                                    <div className="absolute top-9 left-[5%] right-[5%] h-1 bg-slate-100 rounded-full overflow-hidden hidden lg:block">
                                        <div 
                                            className="h-full bg-gradient-to-r from-accent to-yellow-400 transition-all duration-1000 ease-in-out" 
                                            style={{ 
                                                width: `${Math.min(((currentStep - 1) / (steps.length - 1)) * 100, 100)}%`,
                                            }}
                                        />
                                    </div>

                                    {/* Milestone Points */}
                                    <div className="grid grid-cols-2 lg:grid-cols-9 gap-12 lg:gap-4 lg:px-4 relative">
                                        {steps.map((step, idx) => {
                                            const stepNum = idx + 1;
                                            const isCompleted = currentStep > stepNum;
                                            const isActive = currentStep === stepNum;
                                            const StepIcon = step.icon;

                                            return (
                                                <div key={idx} className="flex flex-col items-center group/step">
                                                    <div className={`relative w-16 h-16 md:w-[4.5rem] md:h-[4.5rem] rounded-[1.25rem] flex items-center justify-center transition-all duration-500 z-10 ${
                                                        isCompleted ? 'bg-accent text-white scale-90 shadow-[0_4px_20px_rgba(255,149,0,0.3)]' : 
                                                        isActive ? 'bg-[#0A1A3A] text-white scale-110 shadow-xl ring-4 ring-white' : 
                                                        'bg-white border-2 border-slate-100 text-slate-400'
                                                    }`}>
                                                        {isCompleted ? <CheckCircle2 size={26} /> : (
                                                            <StepIcon size={24} className={isActive ? 'animate-pulse' : ''} />
                                                        )}
                                                    </div>
                                                    <div className="mt-6 text-center space-y-1">
                                                        <div className={`text-[10px] font-black uppercase tracking-[0.1em] leading-tight ${isActive ? 'text-slate-900' : 'text-slate-500'}`}>
                                                            {step.label}
                                                        </div>
                                                        <div className={`text-[8px] font-bold uppercase tracking-[0.15em] ${isActive ? 'text-accent' : 'text-slate-400'} max-w-[80px] mx-auto`}>
                                                            {step.desc}
                                                        </div>
                                                    </div>
                                                </div>
                                            );
                                        })}
                                    </div>
                                </div>
                            </div>

                        </div>
                    )}

                    {/* Enhanced Error Screen (Light Mode) */}
                    {queryId && !isLoading && !trackingData?.success && (
                        <div className="bg-white rounded-[3rem] p-16 md:p-24 text-center border border-red-50 shadow-[0_8px_30px_rgb(239,68,68,0.06)] w-full max-w-4xl relative overflow-hidden group animate-in zoom-in-95 duration-700">
                            <div className="absolute top-0 left-0 w-64 h-64 bg-red-100/50 blur-[80px] -z-10 rounded-full -ml-20 -mt-20 pointer-events-none" />
                            <div className="w-24 h-24 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-8 border border-red-100 text-red-500 shadow-sm">
                                <Activity className="w-10 h-10" />
                            </div>
                            <h2 className="text-4xl md:text-5xl lg:text-6xl font-display font-black text-slate-900 mb-6 tracking-tight">ID Not Found</h2>
                            <p className="text-slate-500 text-lg md:text-xl max-w-xl mx-auto mb-12 font-medium">
                                The tracking protocol <span className="text-slate-900 font-black">"{queryId}"</span> has not been initialized in our verification database.
                            </p>
                            <div className="flex flex-col sm:flex-row items-center justify-center gap-4">
                                <button
                                    onClick={() => { setSearchId(''); setQueryId(''); }}
                                    className="bg-slate-900 text-white px-10 py-5 rounded-full font-black text-[11px] uppercase tracking-widest hover:bg-slate-800 transition-all shadow-lg flex items-center justify-center gap-3 w-full sm:w-auto"
                                >
                                    <ArrowLeft size={16} />
                                    Try Another
                                </button>
                                <a href="/contact" className="px-10 py-5 font-black text-[11px] uppercase tracking-widest text-slate-500 hover:text-slate-900 transition-all w-full sm:w-auto">
                                    Contact Support
                                </a>
                            </div>
                        </div>
                    )}

                    {/* Initial Features Screen (Light Mode) */}
                    {!queryId && !isLoading && (
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-6 w-full max-w-5xl animate-in fade-in duration-1000 delay-300 mt-10">
                            {[
                                { icon: <Database className="text-accent" />, title: 'Real-time Sync', text: 'Live API bridge to MNRE portal.' },
                                { icon: <ShieldCheck className="text-primary" />, title: 'Secure Access', text: 'End-to-end encrypted tracking.' },
                                { icon: <Zap className="text-yellow-400" />, title: 'Energy Node', text: 'Instant updates on milestones.' }
                            ].map((item, i) => (
                                <div key={i} className="bg-white p-8 md:p-10 rounded-[2.5rem] border border-slate-100 shadow-[0_4px_20px_rgba(0,0,0,0.03)] flex flex-col items-center text-center space-y-6 hover:shadow-[0_12px_30px_rgba(0,0,0,0.06)] hover:-translate-y-1 transition-all duration-300 group">
                                    <div className="w-16 h-16 bg-slate-50 rounded-[1.25rem] flex items-center justify-center border border-slate-100 group-hover:scale-110 group-hover:bg-white group-hover:shadow-sm transition-all duration-500">
                                        {item.icon}
                                    </div>
                                    <div className="space-y-3">
                                        <h4 className="text-xl font-display font-black text-slate-900 tracking-tight">{item.title}</h4>
                                        <p className="text-slate-500 text-[11px] font-bold uppercase tracking-wider leading-relaxed">
                                            {item.text}
                                        </p>
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                </div>
            </main>

            <Footer />
        </div>
    );
}
