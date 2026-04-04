import { Sun, CheckCircle, FileText, ExternalLink, AlertTriangle, Zap, IndianRupee } from 'lucide-react';
import Navbar from '@/components/public/Navbar';
import Footer from '@/components/public/Footer';
import SEOHead from '@/components/shared/SEOHead';
import { useQuery } from '@tanstack/react-query';
import { publicApi, type PublicSettingsData } from '@/services/public.api';

 
const subsidyData = [
    { capacity: '3 kW & Above', subsidy: '₹94,800', monthly: 'Up to 300 units free', tag: 'Maximum' },
];

const documents = [
    'Aadhaar Card (Resident of J&K or Ladakh)',
    'Latest Electricity Bill (with consumer number)',
    'Property ownership proof or landlord\'s NOC',
    'Passport-size photograph',
    'Bank passbook / cancelled cheque (for subsidy credit)',
    'PAN Card (for subsidy amounts above ₹50,000)',
];

export default function SchemeInfoPage() {
    const { data: settings } = useQuery<PublicSettingsData>({
        queryKey: ['public-settings'],
        queryFn: publicApi.getSettings,
    });

    const companyName = settings?.company_name || 'AndleebSurya';

    const steps = [
        { step: '01', title: 'Submit Your Query', desc: 'Fill the form on our website with your name, mobile, and district. Zero cost, takes 2 minutes.' },
        { step: '02', title: 'Agent Calls You', desc: `A verified ${companyName} agent (SM-XXXX) contacts you within 24 hours to understand your requirement.` },
        { step: '03', title: 'Document Collection', desc: 'Agent helps you compile required documents and checks your eligibility for the scheme.' },
        { step: '04', title: 'Government Portal Registration', desc: 'Your application is submitted on the official pmsuryaghar.gov.in portal.' },
        { step: '05', title: 'Site Survey', desc: 'An empanelled installation vendor surveys your rooftop and provides capacity recommendation.' },
        { step: '06', title: 'Solar Installation', desc: 'Solar panels are installed by the government-empanelled vendor at no cost to you.' },
        { step: '07', title: 'Net Meter & Subsidy', desc: 'Net meter is installed. Government subsidy of up to ₹94,800 is credited directly to your bank account.' },
    ];

    return (
        <div className="min-h-screen bg-neutral-50 flex flex-col">
            <SEOHead 
                title={`PM Surya Ghar Muft Bijli Yojana — Free Solar Scheme Info`} 
                description={`Everything about PM Surya Ghar Muft Bijli Yojana — free solar panels, ₹94,800 government subsidy, eligibility for J&K and Ladakh residents. Documents, process, benefits explained via ${companyName}.`}
                keywords="PM Surya Ghar, Muft Bijli Yojana, free solar panels J&K, solar subsidy Jammu Kashmir, MNRE scheme, rooftop solar Ladakh"
                breadcrumbs={[
                    { name: 'Home', url: window.location.origin },
                    { name: 'Scheme Info', url: window.location.origin + '/scheme' }
                ]}
                schemas={[
                    {
                        "@context": "https://schema.org",
                        "@type": "Article",
                        "headline": "PM Surya Ghar Muft Bijli Yojana — Free Solar Scheme Info",
                        "description": `Get free solar panels with up to ₹94,800 government subsidy under PM Surya Ghar Muft Bijli Yojana. ${companyName} helps J&K and Ladakh residents register.`,
                        "author": {
                            "@type": "Organization",
                            "name": companyName
                        }
                    }
                ]}
            />
            <Navbar />

            <main className="flex-grow">
                {/* Hero */}
                <div className="bg-gradient-to-br from-primary to-primary-dark text-white py-16 px-4">
                    <div className="max-w-4xl mx-auto text-center">
                        <div className="inline-flex items-center gap-2 bg-white/10 rounded-full px-4 py-1.5 text-sm font-medium mb-4">
                            <Sun className="w-4 h-4 text-accent" />
                            Government of India Scheme
                        </div>
                        <h1 className="text-3xl md:text-4xl font-display font-bold mb-4">
                            PM Surya Ghar Muft Bijli Yojana
                        </h1>
                        <p className="text-lg text-white/80 max-w-2xl mx-auto mb-2">
                            Free rooftop solar installation with up to <strong className="text-accent">₹94,800 subsidy</strong> for eligible households in Jammu &amp; Kashmir and Ladakh.
                        </p>
                        <p className="text-sm text-white/60">
                            {companyName} facilitates registration — we are not a government body
                        </p>
                    </div>
                </div>

                <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-12">

                    {/* Disclaimer */}
                    <div className="bg-amber-50 border border-amber-200 rounded-2xl p-4 flex gap-3">
                        <AlertTriangle className="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" />
                        <div className="text-sm text-amber-800">
                            <strong>{companyName} is a private facilitation company, not a government portal.</strong> We help residents of J&amp;K and Ladakh navigate the official PM Surya Ghar registration process. The actual scheme and subsidies are provided by the Government of India. Official portal: {' '}
                            <a href="https://pmsuryaghar.gov.in" target="_blank" rel="noopener noreferrer" className="underline font-medium">pmsuryaghar.gov.in</a>
                        </div>
                    </div>

                    {/* What is the scheme */}
                    <section className="bg-white rounded-3xl p-8 shadow-sm border border-gray-100">
                        <h2 className="text-2xl font-display font-bold text-dark mb-4">What is PM Surya Ghar Muft Bijli Yojana?</h2>
                        <p className="text-neutral-700 leading-relaxed mb-4">
                            PM Surya Ghar Muft Bijli Yojana is a flagship Government of India scheme launched in February 2024 under which residential households receive free rooftop solar panel installations along with a direct government subsidy of up to ₹94,800. The scheme aims to provide up to 300 units of free electricity per month to 1 crore households across India.
                        </p>
                        <div className="grid sm:grid-cols-3 gap-4 mt-6">
                            <div className="bg-primary/5 rounded-2xl p-4 text-center">
                                <Zap className="w-8 h-8 text-primary mx-auto mb-2" />
                                <p className="font-bold text-dark">300 Units/Month</p>
                                <p className="text-xs text-neutral-500 mt-1">Free electricity for eligible households</p>
                            </div>
                            <div className="bg-accent/5 rounded-2xl p-4 text-center">
                                <IndianRupee className="w-8 h-8 text-accent mx-auto mb-2" />
                                <p className="font-bold text-dark">Up to ₹94,800</p>
                                <p className="text-xs text-neutral-500 mt-1">Direct government subsidy</p>
                            </div>
                            <div className="bg-success/5 rounded-2xl p-4 text-center">
                                <Sun className="w-8 h-8 text-success mx-auto mb-2" />
                                <p className="font-bold text-dark">Zero Cost</p>
                                <p className="text-xs text-neutral-500 mt-1">No fee for facilitation service</p>
                            </div>
                        </div>
                    </section>

                    {/* Subsidy structure */}
                    <section className="bg-white rounded-3xl p-8 shadow-sm border border-gray-100">
                        <h2 className="text-2xl font-display font-bold text-dark mb-2">Subsidy Structure</h2>
                        <p className="text-sm text-neutral-500 mb-6">Current MNRE rates as of 2024–25. Subject to Government revision — verify at pmsuryaghar.gov.in.</p>
                        <div className="grid sm:grid-cols-3 gap-4">
                            {subsidyData.map((row) => (
                                <div key={row.capacity} className="border border-gray-200 rounded-2xl p-5 relative">
                                    <span className="absolute top-3 right-3 text-xs bg-accent/10 text-accent font-semibold px-2 py-0.5 rounded-full">
                                        {row.tag}
                                    </span>
                                    <p className="text-sm text-neutral-500 mb-1">{row.capacity}</p>
                                    <p className="text-2xl font-bold text-dark mb-1">{row.subsidy}</p>
                                    <p className="text-xs text-neutral-500">{row.monthly}</p>
                                </div>
                            ))}
                        </div>
                    </section>

                    {/* Eligibility */}
                    <section className="bg-white rounded-3xl p-8 shadow-sm border border-gray-100">
                        <h2 className="text-2xl font-display font-bold text-dark mb-4">Who is Eligible?</h2>
                        <ul className="space-y-3">
                            {[
                                'Residential electricity consumers with an active connection from JPDCL / LPDCL',
                                'Must own the property or have the owner\'s written consent',
                                'Resident of Jammu & Kashmir or Ladakh Union Territory',
                                'Must not have previously availed rooftop solar subsidy from the government',
                                'No arrears on electricity bills',
                            ].map((item) => (
                                <li key={item} className="flex items-start gap-3">
                                    <CheckCircle className="w-5 h-5 text-success flex-shrink-0 mt-0.5" />
                                    <span className="text-neutral-700 text-sm">{item}</span>
                                </li>
                            ))}
                        </ul>
                    </section>

                    {/* How it works */}
                    <section className="bg-white rounded-3xl p-8 shadow-sm border border-gray-100">
                        <h2 className="text-2xl font-display font-bold text-dark mb-6">How the Process Works</h2>
                        <div className="space-y-4">
                            {steps.map((s) => (
                                <div key={s.step} className="flex gap-4 items-start">
                                    <div className="w-10 h-10 rounded-full bg-primary text-white font-bold text-sm flex items-center justify-center flex-shrink-0">
                                        {s.step}
                                    </div>
                                    <div>
                                        <p className="font-semibold text-dark">{s.title}</p>
                                        <p className="text-sm text-neutral-500 mt-0.5">{s.desc}</p>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </section>

                    {/* Documents */}
                    <section className="bg-white rounded-3xl p-8 shadow-sm border border-gray-100">
                        <h2 className="text-2xl font-display font-bold text-dark mb-4">Documents Required</h2>
                        <ul className="space-y-3">
                            {documents.map((doc) => (
                                <li key={doc} className="flex items-start gap-3">
                                    <FileText className="w-5 h-5 text-primary flex-shrink-0 mt-0.5" />
                                    <span className="text-neutral-700 text-sm">{doc}</span>
                                </li>
                            ))}
                        </ul>
                    </section>

                    {/* CTA */}
                    <section className="bg-gradient-to-br from-primary to-primary-dark rounded-3xl p-8 text-white text-center">
                        <h2 className="text-2xl font-display font-bold mb-3">Ready to Apply?</h2>
                        <p className="text-white/80 mb-6 max-w-md mx-auto text-sm">
                            {companyName} helps you complete the process — at zero cost. Our agent will call you within 24 hours of submitting your query.
                        </p>
                        <div className="flex flex-col sm:flex-row gap-3 justify-center">
                            <a href="/#apply" className="bg-accent hover:bg-accent-dark text-white font-semibold px-6 py-3 rounded-xl transition-colors text-sm">
                                Apply Now — Free
                            </a>
                            <a href="https://pmsuryaghar.gov.in" target="_blank" rel="noopener noreferrer" className="bg-white/10 hover:bg-white/20 text-white font-medium px-6 py-3 rounded-xl transition-colors text-sm flex items-center justify-center gap-2">
                                <ExternalLink className="w-4 h-4" /> Official Portal
                            </a>
                        </div>
                    </section>

                </div>
            </main>
            <Footer />
        </div>
    );
}
