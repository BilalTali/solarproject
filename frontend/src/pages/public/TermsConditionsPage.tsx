import { Helmet } from 'react-helmet-async';
import Navbar from '@/components/public/Navbar';
import Footer from '@/components/public/Footer';
import { useQuery } from '@tanstack/react-query';
import { publicApi } from '@/services/public.api';

const getSetting = (settingsObj: any, key: string, fallback: string) => {
    return settingsObj?.[key] || fallback;
};

export default function TermsConditionsPage() {
    const { data: settings = {} } = useQuery({
        queryKey: ['public-settings'],
        queryFn: publicApi.getSettings,
    });

    const companyName = getSetting(settings, 'company_name', 'SuryaMitra');
    const companyEmail = getSetting(settings, 'company_email', 'admin@suryamitra.in');

    return (
        <div className="min-h-screen bg-neutral-50 flex flex-col">
            <Helmet>
                <title>Terms & Conditions — {companyName} | PM Surya Ghar Facilitation</title>
                <meta name="description" content={`Read ${companyName}'s Terms & Conditions. ${companyName} is a private facilitation company helping J&K and Ladakh residents register for PM Surya Ghar Muft Bijli Yojana.`} />
                <meta name="robots" content="noindex, follow" />
            </Helmet>
            <Navbar />
            <main className="flex-grow max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16 w-full">
                <div className="bg-white rounded-3xl p-8 md:p-12 shadow-sm border border-gray-100">
                    <h1 className="text-3xl font-display font-bold text-dark mb-2">Terms &amp; Conditions</h1>
                    <p className="text-sm text-neutral-500 mb-8 pb-6 border-b border-gray-100">
                        Last Updated: March 10, 2026
                    </p>

                    <div className="prose prose-blue max-w-none text-neutral-700 space-y-8">

                        <div className="bg-amber-50 border border-amber-200 rounded-2xl p-4 text-sm text-amber-800">
                            <strong>Important:</strong> {companyName} is a <strong>PRIVATE FACILITATION SERVICE</strong>. We are NOT affiliated with or endorsed by the Government of India, MNRE (Ministry of New and Renewable Energy), or any government body. The PM Surya Ghar Muft Bijli Yojana is a Government of India scheme — {companyName} merely facilitates the application process.
                        </div>

                        <section>
                            <h2 className="text-xl font-bold text-dark mb-3">1. Service Description</h2>
                            <p>{companyName} provides a <strong>free facilitation service</strong> to help residents of Jammu &amp; Kashmir and Ladakh register for the PM Surya Ghar Muft Bijli Yojana — a Government of India scheme providing free solar electricity and subsidies for rooftop solar installations. Our agents (Business Development Executives: SM-XXXX) and Business Development Managers (SSM-XXXX) guide beneficiaries through the application process on the official government portal (<a href="https://pmsuryaghar.gov.in" target="_blank" rel="noopener noreferrer" className="text-primary underline">pmsuryaghar.gov.in</a>).</p>
                        </section>

                        <section>
                            <h2 className="text-xl font-bold text-dark mb-3">2. No Fee Policy — CRITICAL</h2>
                            <div className="bg-green-50 border border-green-200 rounded-xl p-4 mb-3">
                                <p className="font-bold text-green-800">{companyName} does NOT charge beneficiaries (citizens) any fee whatsoever for facilitation services.</p>
                            </div>
                            <p>The PM Surya Ghar Muft Bijli Yojana provides free solar installation — <strong>no payment is required from you as a beneficiary</strong>. If anyone claiming to represent {companyName} demands money from you, please report it immediately to <a href={`mailto:${companyEmail}`} className="text-primary underline">{companyEmail}</a> or call our support line. This constitutes fraud and will be reported to authorities.</p>
                        </section>

                        <section>
                            <h2 className="text-xl font-bold text-dark mb-3">3. Eligibility &amp; Service Area</h2>
                            <p>{companyName} operates exclusively in <strong>Jammu &amp; Kashmir and Ladakh</strong>. Our facilitation services are available to:</p>
                            <ul className="list-disc pl-5 space-y-1">
                                <li>Residential electricity consumers with an active connection</li>
                                <li>Individuals who own the property (or have owner's consent)</li>
                                <li>Citizens of Jammu &amp; Kashmir or Ladakh Union Territories</li>
                            </ul>
                        </section>

                        <section>
                            <h2 className="text-xl font-bold text-dark mb-3">4. Agent Code of Conduct</h2>
                            <p className="mb-3">Our agents (SM-XXXX) and Business Development Managers (SSM-XXXX) operate under a strict code of conduct. They will <strong>never</strong>:</p>
                            <ul className="list-disc pl-5 space-y-1">
                                <li>Demand any payment from beneficiaries for facilitation services</li>
                                <li>Promise guaranteed installation timelines (these depend on government processing and vendor availability)</li>
                                <li>Claim to be government officials or employees</li>
                                <li>Misrepresent the subsidy amounts or scheme terms</li>
                                <li>Collect original documents from beneficiaries (copies only)</li>
                            </ul>
                            <p className="mt-3">You can verify any {companyName} agent by scanning their QR code or visiting our verification page. Report misconduct immediately.</p>
                        </section>

                        <section>
                            <h2 className="text-xl font-bold text-dark mb-3">5. Subsidy Disclaimer</h2>
                            <p>Subsidy amounts under PM Surya Ghar Muft Bijli Yojana are <strong>determined by the Government of India and MNRE</strong> and are subject to change without notice. Currently indicative rates are:</p>
                            <ul className="list-disc pl-5 space-y-1 mt-2">
                                {(() => {
                                    try {
                                        const json = (settings as any).calculator_values_json;
                                        if (json) {
                                            const parsed = JSON.parse(json);
                                            if (Array.isArray(parsed)) {
                                                return parsed.map((item: any) => (
                                                    <li key={item.id}>
                                                        {item.label}: ₹{((Number(item.central) || 0) + (Number(item.state) || 0)).toLocaleString()} subsidy {item.id.includes('above') ? '(maximum)' : ''}
                                                    </li>
                                                ));
                                            }
                                        }
                                    } catch (e) { }
                                    return (
                                        <>
                                            <li>3 kW & Above: ₹94,800 subsidy (maximum for residential)</li>
                                        </>
                                    );
                                })()}
                            </ul>
                            <p className="mt-3">{companyName} does <strong>not guarantee</strong> specific subsidy amounts. Always verify current rates at <a href="https://pmsuryaghar.gov.in" target="_blank" rel="noopener noreferrer" className="text-primary underline">pmsuryaghar.gov.in</a>.</p>
                        </section>

                        <section>
                            <h2 className="text-xl font-bold text-dark mb-3">6. Data Accuracy</h2>
                            <p>You are responsible for providing accurate and truthful information when submitting a lead or registering as an agent. Incorrect information may result in rejection of your application by the government portal. {companyName} is not liable for rejections caused by inaccurate data provided by the user.</p>
                        </section>

                        <section>
                            <h2 className="text-xl font-bold text-dark mb-3">7. Limitation of Liability</h2>
                            <p>{companyName} facilitates the application process only. We are <strong>not responsible</strong> for:</p>
                            <ul className="list-disc pl-5 space-y-1">
                                <li>Government processing delays or rejection of applications by official portals</li>
                                <li>Changes in scheme eligibility criteria, subsidy amounts, or policy by MNRE</li>
                                <li>Vendor delays in solar panel installation after government approval</li>
                                <li>Technical outages on the official pmsuryaghar.gov.in portal</li>
                            </ul>
                        </section>

                        <section>
                            <h2 className="text-xl font-bold text-dark mb-3">8. Agent Registration Terms</h2>
                            <p>Individuals registering as Business Development Executives or Managers acknowledge that:</p>
                            <ul className="list-disc pl-5 space-y-1">
                                <li>They are independent contractors, not employees of {companyName}</li>
                                <li>KYC documents submitted must be authentic; fraudulent documents will result in immediate termination and legal action</li>
                                <li>Commission payments are subject to verification and successful lead conversion</li>
                                {companyName} reserves the right to suspend or terminate accounts that violate platform guidelines
                            </ul>
                        </section>

                        <section>
                            <h2 className="text-xl font-bold text-dark mb-3">9. Intellectual Property</h2>
                            <p>All content on {companyName}'s platform — including the portal, iCards, and agent verification system — is the proprietary property of {companyName}. Unauthorized reproduction, distribution, or misuse of our brand, agent codes, or verification system constitutes infringement.</p>
                        </section>

                        <section>
                            <h2 className="text-xl font-bold text-dark mb-3">10. Governing Law</h2>
                            <p>These Terms are governed by the laws of India. Any disputes arising from these Terms shall be subject to the jurisdiction of courts in Jammu, Jammu &amp; Kashmir, India.</p>
                        </section>

                        <section>
                            <h2 className="text-xl font-bold text-dark mb-3">11. Contact</h2>
                            <div className="bg-neutral-50 rounded-xl p-4 text-sm space-y-1">
                                <p><strong>{companyName}</strong></p>
                                <p>Email: <a href={`mailto:${companyEmail}`} className="text-primary underline">{companyEmail}</a></p>
                                <p>For reporting fraud or agent misconduct, contact us immediately.</p>
                            </div>
                        </section>

                    </div>
                </div>
            </main>
            <Footer />
        </div>
    );
}
