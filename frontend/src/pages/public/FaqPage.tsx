import Navbar from '@/components/public/Navbar';
import Footer from '@/components/public/Footer';
import SEOHead from '@/components/shared/SEOHead';

const faqs = [
    {
        q: "What is PM Surya Ghar Muft Bijli Yojana?",
        a: "PM Surya Ghar Muft Bijli Yojana is a central government initiative aimed at providing free electricity up to 300 units every month to households across India by subsidizing the installation of rooftop solar panels."
    },
    {
        q: "Who is eligible for the solar subsidy?",
        a: "Any Indian citizen owning a house with a solid roof, a valid electricity connection, and no prior solar subsidy from the government is eligible for this scheme."
    },
    {
        q: "What is the maximum subsidy amount available?",
        a: "The subsidy is calculated based on system capacity: ₹30,000 per kW for up to 2 kW, and ₹18,000 for an additional kW. The maximum subsidy is capped at ₹78,000 for systems 3 kW or higher."
    },
    {
        q: "How many free electricity units will I get?",
        a: "Homes installing appropriate capacity solar panels can generate enough power to offset their consumption, resulting in up to 300 units of free electricity every month."
    },
    {
        q: "What documents are required for application?",
        a: "You need an electricity bill (recent 6 months), Aadhaar card, PAN card, a passport-sized photograph, bank passbook copy, and a cancelled cheque."
    },
    {
        q: "Does AndleebSurya charge any fee for registration?",
        a: "No, AndleebSurya provides absolute free registration and guidance services. Our trained agents help you navigate the process without any service fees from the consumer."
    },
    {
        q: "Can I install solar panels in an apartment?",
        a: "Yes, provided you have roof rights or a no-objection certificate (NOC) from your housing society or Resident Welfare Association (RWA)."
    },
    {
        q: "How long does the installation process take?",
        a: "From registration to final grid commissioning (net metering), the typical timeline is 30 to 45 days, depending on DISCOM approvals."
    },
    {
        q: "Do I have to pay the full amount upfront?",
        a: "No. You can either self-finance the remaining amount or apply for low-interest loans specifically tailored for this scheme by major banks."
    },
    {
        q: "What is Net Metering?",
        a: "Net metering is a billing mechanism that credits solar energy system owners for the electricity they add to the grid. If you generate more than you consume, it is sent to the grid and adjusted in your bill."
    },
    {
        q: "What happens during a power cut?",
        a: "Grid-tied solar systems automatically switch off during grid failures to protect line workers (anti-islanding). You would need a hybrid system with battery storage if you want power during cuts."
    },
    {
        q: "Is there a warranty on the solar panels?",
        a: "Yes, Empaneled vendors provide a 5-year comprehensive maintenance contract (CMC) for the entire system, and panels typically have a 25-year performance warranty."
    },
    {
        q: "What happens if I move to a new house?",
        a: "Rooftop solar systems are deeply integrated but can theoretically be relocated. However, the subsidy process is tied to the consumer number and premise, making relocation complex."
    },
    {
        q: "How do I become an Agent (Business Development Executive)?",
        a: "You can click on the 'Become An Agent' button on our website, fill out the KYC details (Aadhaar, PAN, Bank Details), and our Super Agents will review and approve your application."
    },
    {
        q: "How are agents compensated?",
        a: "Agents receive attractive commissions for every successful rooftop solar installation they facilitate. Payments are transferred directly to their bank accounts after successful project commissioning."
    }
];

export default function FaqPage() {
    const faqSchema = {
        '@context': 'https://schema.org',
        '@type': 'FAQPage',
        mainEntity: faqs.map(faq => ({
            '@type': 'Question',
            name: faq.q,
            acceptedAnswer: { '@type': 'Answer', text: faq.a },
        })),
    };

    return (
        <div className="min-h-screen bg-neutral-50 flex flex-col">
            <SEOHead
                title="Frequently Asked Questions - PM Surya Ghar Yojana"
                description="Find answers to all your questions about PM Surya Ghar Muft Bijli Yojana, solar subsidies, eligibility criteria, and the installation process."
                keywords="PM Surya Ghar FAQ, solar subsidy questions, Muft Bijli Yojana eligibility, rooftop solar FAQ India"
                schemas={[faqSchema]}
                breadcrumbs={[
                    { name: 'Home', url: window.location.origin },
                    { name: 'FAQ', url: window.location.origin + '/faq' },
                ]}
            />

            <Navbar />
            
            <main className="flex-grow max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16 w-full">
                <div className="bg-white rounded-3xl p-8 md:p-12 shadow-sm border border-gray-100">
                    <h1 className="text-4xl font-display font-bold text-dark mb-10 text-center">Frequently Asked Questions</h1>

                    <div className="space-y-6">
                        {faqs.map((faq, index) => (
                            <div key={index} className="border-b border-gray-100 pb-6 last:border-0 last:pb-0">
                                <h3 className="font-display font-bold text-primary mb-2 text-lg lg:text-xl">
                                    {faq.q}
                                </h3>
                                <p className="text-neutral-600 text-sm lg:text-base leading-relaxed">
                                    {faq.a}
                                </p>
                            </div>
                        ))}
                    </div>
                </div>
            </main>
            
            <Footer />
        </div>
    );
}
