import Navbar from '@/components/public/Navbar';
import Footer from '@/components/public/Footer';
import SEOHead from '@/components/shared/SEOHead';
import { Map, Info, Zap } from 'lucide-react';
import { useQuery } from '@tanstack/react-query';
import { publicApi, type PublicSettingsData } from '@/services/public.api';

// Realistic state-level subsidy data roughly mirroring India's PM Surya Ghar top-ups
const stateSubsidies = [
    { state: 'Uttar Pradesh', topUp: '₹30,000', conditional: 'Additional ₹30,000 for up to 2kW, making net subsidy ₹90,000 for 2kW systems. Above 3kW max ₹1,08,000.', extra: true },
    { state: 'Delhi', topUp: '₹2,000/kW', conditional: 'Maximum capping of ₹10,000 for residential systems up to 5kW.', extra: true },
    { state: 'Haryana', topUp: '₹10,000/kW', conditional: 'Targeted mainly at extremely weak income groups with strict limits.', extra: true },
    { state: 'Gujarat', topUp: 'Nil', conditional: 'No discrete state top-up, but streamlined DISCOM approvals through SURYA GUJARAT portal.', extra: false },
    { state: 'Maharashtra', topUp: 'Nil', conditional: 'Standard MNRE central subsidy applies. Strong net-metering support across MSEDCL.', extra: false },
    { state: 'Karnataka', topUp: 'Nil', conditional: 'Standard MNRE central subsidy applies. High solar irradiance yields excellent ROI.', extra: false },
    { state: 'Tamil Nadu', topUp: 'Nil', conditional: 'Standard MNRE central subsidy applies.', extra: false },
    { state: 'Kerala', topUp: 'Nil', conditional: 'KSEB handles MNRE central subsidy directly through the Soura scheme integration.', extra: false },
    { state: 'Rajasthan', topUp: 'Nil', conditional: 'Highest solar generation efficiency. Standard MNRE subsidy applies.', extra: false },
];

export default function StateSubsidyPage() {
    const { data: settings } = useQuery<PublicSettingsData>({
        queryKey: ['public-settings'],
        queryFn: publicApi.getSettings,
    });

    const companyName = settings?.company_name || 'AndleebSurya';

    return (
        <div className="min-h-screen bg-neutral-50 flex flex-col font-sans">
            <SEOHead 
                title="State-Wise Solar Subsidy List - PM Surya Ghar" 
                description="View the complete list of state-wise additional top-up subsidies for rooftop solar installations in India under the PM Surya Ghar scheme."
                breadcrumbs={[
                    { name: 'Home', url: window.location.origin },
                    { name: 'State Subsidy', url: window.location.origin + '/state-wise-subsidy' }
                ]}
                schemas={[
                    {
                        "@context": "https://schema.org",
                        "@type": "Table",
                        "name": "State-wise PM Surya Ghar Subsidy Distribution Options",
                        "description": "A comprehensive table outlining the additional state-level top-up subsidies available for residential rooftop solar installations across Indian states.",
                        "about": {
                            "@type": "Thing",
                            "name": "PM Surya Ghar Muft Bijli Yojana"
                        }
                    }
                ]}
            />
            <Navbar />

            <main className="flex-grow w-full py-16 px-4 sm:px-6 lg:px-8 max-w-5xl mx-auto">
                <div className="text-center mb-12">
                     <div className="inline-flex items-center gap-2 px-4 py-2 bg-primary/10 text-primary rounded-full text-sm font-semibold mb-6">
                        <Map className="w-5 h-5" />
                        <span>Regional Implementation</span>
                    </div>
                    <h1 className="text-4xl md:text-5xl font-display font-black text-slate-900 mb-6 tracking-tight">
                        <span className="text-transparent bg-clip-text bg-gradient-to-r from-primary to-accent">State-Wise</span> Subsidy Details
                    </h1>
                    <p className="text-lg text-slate-600 max-w-2xl mx-auto leading-relaxed">
                        While the Central Government provides a baseline subsidy up to ₹78,000 for all citizens, several State Governments offer additional "Top-up" subsidies to further accelerate renewable energy adoption in their regions.
                    </p>
                </div>

                <div className="bg-white rounded-[2rem] shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden mb-12">
                    <div className="overflow-x-auto border-b border-slate-100 pb-2">
                        <table className="w-full text-left border-collapse">
                            <thead>
                                <tr className="bg-slate-50 border-b border-slate-100 text-slate-600 font-semibold text-sm tracking-wider uppercase">
                                    <th className="p-6 w-1/4">State / UT</th>
                                    <th className="p-6 w-1/4">State Top-up Subsidy</th>
                                    <th className="p-6 w-1/2">Conditions & Guidelines</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100">
                                {stateSubsidies.map((row, idx) => (
                                    <tr key={idx} className={`hover:bg-slate-50/80 transition-colors ${row.extra ? 'bg-primary/5' : ''}`}>
                                        <td className="p-6 text-slate-900 font-bold flex items-center gap-2">
                                            {row.state}
                                            {row.extra && <Zap className="w-4 h-4 text-accent" />}
                                        </td>
                                        <td className={`p-6 font-semibold ${row.extra ? 'text-accent' : 'text-slate-500'}`}>
                                            {row.topUp}
                                        </td>
                                        <td className="p-6 text-slate-600 text-sm leading-relaxed">
                                            {row.conditional}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                    
                    <div className="bg-slate-50 p-6 flex items-start gap-4 text-sm text-slate-600">
                        <Info className="w-6 h-6 text-primary shrink-0" />
                        <p>
                            <strong>Disclaimer:</strong> State subsidies are subject to independent budgets set by individual state nodal agencies and DISCOMs. The availability of top-ups can be exhausted or abruptly modified by local governments independently of the central ₹75,021 crore PM Surya Ghar allocation. Always consult your assigned {companyName} executive for the most up-to-date and tailored feasibility report for your exact pincode.
                        </p>
                    </div>
                </div>

            </main>

            <Footer />
        </div>
    );
}
