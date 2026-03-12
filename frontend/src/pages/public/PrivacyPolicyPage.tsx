import { Helmet } from 'react-helmet-async';
import Navbar from '@/components/public/Navbar';
import Footer from '@/components/public/Footer';
import { useQuery } from '@tanstack/react-query';
import { publicApi } from '@/api/public.api';

const getSetting = (settingsObj: any, key: string, fallback: string) => {
    return settingsObj?.[key] || fallback;
};

export default function PrivacyPolicyPage() {
    const { data: settings = {} } = useQuery({
        queryKey: ['public-settings'],
        queryFn: publicApi.getSettings,
    });

    const companyName = getSetting(settings, 'company_name', 'SuryaMitra');
    const companyEmail = getSetting(settings, 'company_email', 'admin@suryamitra.in');

    return (
        <div className="min-h-screen bg-neutral-50 flex flex-col">
            <Helmet>
                <title>Privacy Policy — {companyName} | PM Surya Ghar Facilitation</title>
                <meta name="description" content={`Read ${companyName}'s Privacy Policy to understand how we collect, use, and protect personal data for PM Surya Ghar Muft Bijli Yojana applications in J&K and Ladakh.`} />
                <meta name="robots" content="noindex, follow" />
            </Helmet>
            <Navbar />
            <main className="flex-grow max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16 w-full">
                <div className="bg-white rounded-3xl p-8 md:p-12 shadow-sm border border-gray-100">
                    <h1 className="text-3xl font-display font-bold text-dark mb-2">Privacy Policy</h1>
                    <p className="text-sm text-neutral-500 mb-8 pb-6 border-b border-gray-100">
                        Last Updated: March 10, 2026
                    </p>

                    <div className="prose prose-blue max-w-none text-neutral-700 space-y-8">

                        <div className="bg-amber-50 border border-amber-200 rounded-2xl p-4 text-sm text-amber-800">
                            <strong>Important Disclaimer:</strong> {companyName} is a <strong>PRIVATE FACILITATION COMPANY</strong>, not a government body. We assist citizens of Jammu &amp; Kashmir and Ladakh in registering for the PM Surya Ghar Muft Bijli Yojana scheme. We are not affiliated with or endorsed by the Government of India, MNRE, or any government authority.
                        </div>

                        <section>
                            <h2 className="text-xl font-bold text-dark mb-3">1. Who We Are</h2>
                            <p>{companyName} is a private company that facilitates citizen registration for the PM Surya Ghar Muft Bijli Yojana — a Government of India scheme for free solar electricity. We operate exclusively in <strong>Jammu &amp; Kashmir and Ladakh</strong>. Our registered agents (SM-XXXX) and Business Development Managers (SSM-XXXX) guide beneficiaries through the application process on the official government portal (<a href="https://pmsuryaghar.gov.in" target="_blank" rel="noopener noreferrer" className="text-primary underline">pmsuryaghar.gov.in</a>).</p>
                        </section>

                        <section>
                            <h2 className="text-xl font-bold text-dark mb-3">2. What Data We Collect</h2>
                            <p className="mb-3">We collect the following categories of personal information:</p>
                            <h3 className="font-semibold text-dark mb-2">From Beneficiaries (Citizens):</h3>
                            <ul className="list-disc pl-5 space-y-1 mb-4">
                                <li>Full name and date of birth</li>
                                <li>Mobile number and email address</li>
                                <li>District, state, and residential address</li>
                                <li>Aadhaar number (encrypted for scheme registration)</li>
                                <li>Electricity consumer number and DISCOM details</li>
                                <li>Monthly electricity bill amount and roof size</li>
                                <li>Bank account details (for subsidy disbursement via government)</li>
                                <li>Property ownership documents and photographs</li>
                            </ul>
                            <h3 className="font-semibold text-dark mb-2">From Agents / Business Development Managers:</h3>
                            <ul className="list-disc pl-5 space-y-1">
                                <li>Full name, father's name, date of birth, gender</li>
                                <li>Contact details (mobile, WhatsApp, email)</li>
                                <li>Address information (permanent and current)</li>
                                <li>Identity documents: Aadhaar number (encrypted), PAN number, Voter ID</li>
                                <li>Bank account details for commission payments (encrypted)</li>
                                <li>Profile photograph and signature image</li>
                                <li>Educational qualifications and work experience</li>
                            </ul>
                        </section>

                        <section>
                            <h2 className="text-xl font-bold text-dark mb-3">3. How We Use Your Data</h2>
                            <p className="mb-3">We use your personal data <strong>solely</strong> for the following purposes:</p>
                            <ul className="list-disc pl-5 space-y-2">
                                <li>Processing and tracking PM Surya Ghar Muft Bijli Yojana application leads</li>
                                <li>Contacting beneficiaries regarding their application status</li>
                                <li>Submitting required information to the official government portal (pmsuryaghar.gov.in) with your consent</li>
                                <li>Verifying agent identity and computing commissions for eligible registrations</li>
                                <li>Generating verified agent identity cards (iCards) for citizen trust and safety</li>
                            </ul>
                            <p className="mt-3"><strong>We do NOT:</strong></p>
                            <ul className="list-disc pl-5 space-y-1">
                                <li>Sell your data to third parties</li>
                                <li>Use your data for marketing or advertising unrelated to the scheme</li>
                                <li>Share your data with entities not involved in scheme processing</li>
                                <li>Use your Aadhaar or bank details for any purpose other than scheme registration</li>
                            </ul>
                        </section>

                        <section>
                            <h2 className="text-xl font-bold text-dark mb-3">4. Data Storage &amp; Security</h2>
                            <ul className="list-disc pl-5 space-y-2">
                                <li>Your data is stored on <strong>secure servers located in India</strong>.</li>
                                <li>Sensitive fields (Aadhaar numbers, bank account numbers) are <strong>encrypted at rest</strong> using AES-256 encryption.</li>
                                <li>All data transmission is secured via HTTPS / TLS.</li>
                                <li>Lead documents (uploaded files) are stored in private storage — not publicly accessible. Access is controlled by role-based permissions.</li>
                                <li>Passwords are hashed using bcrypt (never stored in plain text).</li>
                            </ul>
                        </section>

                        <section>
                            <h2 className="text-xl font-bold text-dark mb-3">5. Data Retention</h2>
                            <p>We retain your personal data for the duration of the scheme processing and application tracking period, plus <strong>3 years</strong> after scheme completion (as required for government compliance and audit purposes). After this period, you may request deletion of your data, subject to applicable legal requirements.</p>
                        </section>

                        <section>
                            <h2 className="text-xl font-bold text-dark mb-3">6. Your Rights</h2>
                            <p className="mb-3">You have the following rights regarding your personal data:</p>
                            <ul className="list-disc pl-5 space-y-2">
                                <li><strong>Right to Access:</strong> Request a copy of the personal data we hold about you.</li>
                                <li><strong>Right to Correction:</strong> Request correction of inaccurate or incomplete information.</li>
                                <li><strong>Right to Deletion:</strong> Request deletion of your data (subject to legal retention requirements).</li>
                                <li><strong>Right to Withdraw Consent:</strong> Withdraw your consent for data processing at any time by contacting us. This does not affect processing done before withdrawal.</li>
                                <li><strong>Right to Grievance Redressal:</strong> File a complaint with us or with the appropriate authority under applicable Indian data protection laws.</li>
                            </ul>
                        </section>

                        <section>
                            <h2 className="text-xl font-bold text-dark mb-3">7. Cookies</h2>
                            <p>Our platform uses session cookies and local storage for authentication only. We do not use tracking or advertising cookies. No third-party analytics cookies are deployed on our agent or admin portals.</p>
                        </section>

                        <section>
                            <h2 className="text-xl font-bold text-dark mb-3">8. Changes to This Policy</h2>
                            <p>We may update this Privacy Policy periodically. The "Last Updated" date at the top reflects the most recent revision. We encourage you to review this policy whenever you use our services.</p>
                        </section>

                        <section>
                            <h2 className="text-xl font-bold text-dark mb-3">9. Contact Us</h2>
                            <p className="mb-2">For any privacy-related questions, concerns, or data requests, please contact us:</p>
                            <div className="bg-neutral-50 rounded-xl p-4 text-sm space-y-1">
                                <p><strong>{companyName} — Privacy Officer</strong></p>
                                <p>Email: <a href={`mailto:${companyEmail}`} className="text-primary underline">{companyEmail}</a></p>
                                <p>Operating Region: Jammu &amp; Kashmir and Ladakh, India</p>
                                <p>Response time: Within 7 business days</p>
                            </div>
                        </section>

                    </div>
                </div>
            </main>
            <Footer />
        </div>
    );
}
