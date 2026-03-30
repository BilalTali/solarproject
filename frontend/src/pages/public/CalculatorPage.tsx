import { useState } from 'react';
import Navbar from '@/components/public/Navbar';
import Footer from '@/components/public/Footer';
import SEOHead from '@/components/shared/SEOHead';
import { IndianRupee, Zap, BarChart3, Sun, BatteryFull, Shield, Info } from 'lucide-react';
import { useQuery } from '@tanstack/react-query';
import { publicApi } from '@/services/public.api';

export default function CalculatorPage() {
    const { data: settings = {} } = useQuery({
        queryKey: ['public-settings'],
        queryFn: publicApi.getSettings,
    });

    const activeSubsidyData: any[] = (() => {
        try {
            const json = (settings as any).calculator_values_json;
            if (json) {
                const parsed = JSON.parse(json);
                if (Array.isArray(parsed)) return parsed;
            }
        } catch (e) { }
        return [
            { id: '1kw', label: '1 KW', central: 30000, state: 0, savings: 600, payback: 60, cost: 65000, space: 100 },
            { id: '2kw', label: '2 KW', central: 60000, state: 0, savings: 1200, payback: 54, cost: 120000, space: 200 },
            { id: '3kw', label: '3 KW', central: 78000, state: 0, savings: 1800, payback: 48, cost: 170000, space: 300 },
            { id: '4kw', label: '4 KW', central: 78000, state: 0, savings: 2400, payback: 42, cost: 220000, space: 400 },
            { id: '5kw', label: '5 KW', central: 78000, state: 0, savings: 3000, payback: 36, cost: 270000, space: 500 },
            { id: '10kw', label: '10 KW', central: 78000, state: 0, savings: 6000, payback: 30, cost: 500000, space: 1000 },
        ];
    })();

    const [capacityOptions] = useState(activeSubsidyData);
    const [selectedCapacity, setSelectedCapacity] = useState<string>('3kw');

    const activeData = capacityOptions.find(opt => opt.id === selectedCapacity) || capacityOptions[2];

    const totalSubsidy = Number(activeData.central) + Number(activeData.state);
    const estimatedCost = activeData.cost || 0;
    const finalCost = estimatedCost > totalSubsidy ? estimatedCost - totalSubsidy : 0;
    const roofSpace = activeData.space || 300;

    return (
        <div className="min-h-screen bg-neutral-50 flex flex-col font-sans">
            <SEOHead 
                title="PM Surya Ghar Rooftop Solar Subsidy Calculator" 
                description="Calculate your PM Surya Ghar Muft Bijli Yojana subsidy, monthly savings, and ROI estimate with our easy-to-use solar capacity calculator."
                breadcrumbs={[
                    { name: 'Home', url: window.location.origin },
                    { name: 'Calculator', url: window.location.origin + '/solar-subsidy-calculator' }
                ]}
                schemas={[
                    {
                        "@context": "https://schema.org",
                        "@type": "WebApplication",
                        "name": "PM Surya Ghar Subsidy Calculator",
                        "url": window.location.href,
                        "description": "An interactive calculator to estimate government subsidy, installation cost, and payback period for rooftop solar panels in India.",
                        "applicationCategory": "CalculatorApplication",
                        "offers": {
                            "@type": "Offer",
                            "price": "0",
                            "priceCurrency": "INR"
                        }
                    }
                ]}
            />
            <Navbar />

            <main className="flex-grow w-full py-16 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
                <div className="text-center mb-12">
                    <div className="inline-flex items-center gap-2 px-4 py-2 bg-primary/10 text-primary rounded-full text-sm font-semibold mb-6">
                        <Sun className="w-5 h-5" />
                        <span>Interactive ROI Estimator</span>
                    </div>
                    <h1 className="text-4xl md:text-5xl font-display font-black text-slate-900 mb-6 tracking-tight">
                        Rooftop Solar <span className="text-transparent bg-clip-text bg-gradient-to-r from-primary to-accent">Subsidy Calculator</span>
                    </h1>
                    <p className="text-lg text-slate-600 max-w-2xl mx-auto leading-relaxed">
                        Find out exactly how much government subsidy you are entitled to, your upfront investment, and how quickly your solar panels will pay for themselves.
                    </p>
                </div>

                <div className="grid lg:grid-cols-12 gap-8 items-start">
                    
                    {/* Left Panel: Inputs */}
                    <div className="lg:col-span-4 bg-white rounded-[2rem] p-8 shadow-xl shadow-slate-200/50 border border-slate-100 flex flex-col">
                        <h2 className="text-xl font-bold text-slate-800 mb-6 flex items-center gap-2">
                            <Zap className="w-6 h-6 text-primary" />
                            System Capacity
                        </h2>
                        
                        <div className="space-y-3 mb-8">
                            {capacityOptions.map((opt) => (
                                <button 
                                    key={opt.id} 
                                    onClick={() => setSelectedCapacity(opt.id)}
                                    className={`w-full text-left px-5 py-4 rounded-xl border-2 transition-all flex justify-between items-center group
                                        ${selectedCapacity === opt.id 
                                            ? 'border-primary bg-primary/5 text-primary shadow-sm' 
                                            : 'border-slate-100 hover:border-primary/30 text-slate-600 hover:bg-slate-50'
                                    }`}
                                >
                                    <span className="font-semibold text-lg">{opt.label} System</span>
                                    {selectedCapacity === opt.id && <div className="w-3 h-3 rounded-full bg-primary shadow-sm" />}
                                </button>
                            ))}
                        </div>

                        <div className="mt-auto bg-slate-50 p-5 rounded-2xl border border-slate-100 text-sm text-slate-600 flex gap-3">
                            <Info className="w-5 h-5 text-primary shrink-0" />
                            <p>Assumes standard poly/mono-crystalline panels. Final costs vary slightly by vendor, state policies, and structure elevation.</p>
                        </div>
                    </div>

                    {/* Right Panel: Results Dashboard */}
                    <div className="lg:col-span-8 flex flex-col gap-6">
                        
                        {/* Primary Stat Hero */}
                        <div className="bg-gradient-to-br from-[#0B1B3D] to-[#1A365D] rounded-[2rem] p-8 md:p-12 shadow-2xl relative overflow-hidden text-white">
                            <div className="absolute top-0 right-0 w-64 h-64 bg-primary/20 blur-[80px] rounded-full -mr-16 -mt-16" />
                            
                            <h3 className="text-primary-light font-bold text-sm tracking-widest uppercase mb-2">Maximum Subsidy</h3>
                            <div className="flex items-end gap-2 mb-8">
                                <span className="text-5xl md:text-7xl font-black tracking-tight flex items-center">
                                    <IndianRupee className="w-10 h-10 md:w-14 md:h-14 opacity-80" />
                                    {totalSubsidy.toLocaleString()}
                                </span>
                            </div>

                            <div className="grid sm:grid-cols-2 gap-6 relative z-10">
                                <div>
                                    <div className="text-slate-400 text-sm mb-1">Estimated Installation Cost</div>
                                    <div className="text-xl font-bold flex items-center">
                                        <IndianRupee className="w-5 h-5 mr-1 opacity-70" /> {estimatedCost.toLocaleString()}
                                    </div>
                                </div>
                                <div>
                                    <div className="text-slate-400 text-sm mb-1">Your Final Payable Amount</div>
                                    <div className="text-2xl font-bold text-accent flex items-center">
                                        <IndianRupee className="w-6 h-6 mr-1" /> {finalCost.toLocaleString()}
                                    </div>
                                    <div className="text-xs text-slate-400 mt-1">Cost - Subsidy</div>
                                </div>
                            </div>
                        </div>

                        {/* Secondary Stats Grid */}
                        <div className="grid sm:grid-cols-2 gap-6">
                            
                            <div className="bg-white rounded-[2rem] p-8 shadow-lg shadow-slate-200/50 border border-slate-100 relative overflow-hidden group">
                                <div className="absolute top-0 right-0 w-32 h-32 bg-green-500/10 rounded-full blur-3xl group-hover:bg-green-500/20 transition-all" />
                                <div className="w-12 h-12 bg-green-100 text-green-600 rounded-2xl flex items-center justify-center mb-6 shadow-sm">
                                    <BarChart3 className="w-6 h-6" />
                                </div>
                                <h3 className="text-slate-500 font-medium mb-1">Monthly Electricity Savings</h3>
                                <div className="text-3xl font-bold text-slate-800 flex items-center">
                                    <IndianRupee className="w-7 h-7" /> {activeData.savings.toLocaleString()}
                                </div>
                            </div>
                            
                            <div className="bg-white rounded-[2rem] p-8 shadow-lg shadow-slate-200/50 border border-slate-100 relative overflow-hidden group">
                                <div className="absolute top-0 right-0 w-32 h-32 bg-orange-500/10 rounded-full blur-3xl group-hover:bg-orange-500/20 transition-all" />
                                <div className="w-12 h-12 bg-orange-100 text-orange-600 rounded-2xl flex items-center justify-center mb-6 shadow-sm">
                                    <BatteryFull className="w-6 h-6" />
                                </div>
                                <h3 className="text-slate-500 font-medium mb-1">Payback Period (ROI)</h3>
                                <div className="text-3xl font-bold text-slate-800">
                                    {Math.floor(activeData.payback / 12)} yrs {activeData.payback % 12 > 0 ? `${activeData.payback % 12} mo` : ''}
                                </div>
                            </div>

                        </div>

                        {/* Technical Details Banner */}
                        <div className="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                            <div className="flex items-center gap-4">
                                <div className="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center">
                                    <Shield className="w-5 h-5 text-slate-600" />
                                </div>
                                <div>
                                    <div className="text-sm font-semibold text-slate-800">Clear Roof Space Needed</div>
                                    <div className="text-xs text-slate-500">Unshaded, South-facing preferred</div>
                                </div>
                            </div>
                            <div className="font-bold text-lg text-slate-800">~{roofSpace} sq.ft.</div>
                        </div>

                    </div>
                </div>
            </main>

            <Footer />
        </div>
    );
}
