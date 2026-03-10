import { Helmet } from 'react-helmet-async';
import { ShieldCheck, AlertTriangle, Phone, Mail } from 'lucide-react';
import Navbar from '@/components/public/Navbar';
import Footer from '@/components/public/Footer';
import { useQuery } from '@tanstack/react-query';
import { publicApi } from '@/api/public.api';

const getSetting = (s: any, k: string, fb: string) => s?.[k] || fb;

export default function RefundPolicyPage() {
    const { data: settings = {} } = useQuery({
        queryKey: ['public-settings'],
        queryFn: publicApi.getSettings,
    });

    return (
        <div className="min-h-screen bg-neutral-50 flex flex-col">
            <Helmet>
                <title>Refund Policy — SuryaMitra | No-Fee Facilitation Service</title>
                <meta name="description" content="SuryaMitra does not charge beneficiaries any fee for PM Surya Ghar facilitation. Read our refund policy and learn how to report fraud." />
                <meta name="robots" content="noindex, follow" />
            </Helmet>
            <Navbar />
            <main className="flex-grow max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16 w-full">
                <div className="bg-white rounded-3xl p-8 md:p-12 shadow-sm border border-gray-100">
                    <h1 className="text-3xl font-display font-bold text-dark mb-2">Refund Policy</h1>
                    <p className="text-sm text-neutral-500 mb-8 pb-6 border-b border-gray-100">
                        Last Updated: March 10, 2026
                    </p>

                    <div className="space-y-8">

                        {/* Core statement */}
                        <div className="bg-green-50 border border-green-200 rounded-2xl p-6 flex gap-4">
                            <ShieldCheck className="w-8 h-8 text-green-600 flex-shrink-0 mt-0.5" />
                            <div>
                                <h2 className="font-bold text-green-800 text-lg mb-2">SuryaMitra is a FREE Facilitation Service</h2>
                                <p className="text-green-700 text-sm leading-relaxed">
                                    SuryaMitra <strong>does not charge any fee</strong> to beneficiaries (citizens) for facilitating PM Surya Ghar Muft Bijli Yojana applications. Therefore, <strong>no payment is accepted from beneficiaries and no refund policy applies</strong> to them.
                                </p>
                                <p className="text-green-700 text-sm mt-2">
                                    The PM Surya Ghar scheme provides free solar installation backed by Government of India subsidies — you will never be asked to pay for facilitation.
                                </p>
                            </div>
                        </div>

                        {/* Fraud warning */}
                        <div className="bg-red-50 border border-red-200 rounded-2xl p-6 flex gap-4">
                            <AlertTriangle className="w-8 h-8 text-red-600 flex-shrink-0 mt-0.5" />
                            <div>
                                <h2 className="font-bold text-red-800 text-lg mb-2">Report Fraud Immediately</h2>
                                <p className="text-red-700 text-sm leading-relaxed">
                                    If anyone claiming to represent SuryaMitra has asked you to pay any amount for:
                                </p>
                                <ul className="list-disc pl-5 text-red-700 text-sm mt-2 space-y-1">
                                    <li>Application processing or registration fees</li>
                                    <li>Document verification charges</li>
                                    <li>Survey fees or inspection charges</li>
                                    <li>Any other charges related to scheme facilitation</li>
                                </ul>
                                <p className="text-red-700 text-sm mt-3 font-semibold">
                                    This constitutes FRAUD. Contact us immediately — we will take strict action against the individual involved.
                                </p>
                            </div>
                        </div>

                        {/* Contact for fraud reports */}
                        <div className="bg-neutral-50 rounded-2xl p-6">
                            <h2 className="font-bold text-dark text-lg mb-4">Contact Us to Report Fraud</h2>
                            <div className="space-y-3">
                                <a
                                    href={`tel:${getSetting(settings, 'company_mobile', '+919876543210')}`}
                                    className="flex items-center gap-3 text-sm text-neutral-700 hover:text-primary transition-colors"
                                >
                                    <div className="w-9 h-9 rounded-full bg-primary/10 flex items-center justify-center">
                                        <Phone className="w-4 h-4 text-primary" />
                                    </div>
                                    {getSetting(settings, 'company_mobile', '+91-98765 43210')}
                                </a>
                                <a
                                    href={`mailto:${getSetting(settings, 'company_email', 'admin@suryamitra.in')}`}
                                    className="flex items-center gap-3 text-sm text-neutral-700 hover:text-primary transition-colors"
                                >
                                    <div className="w-9 h-9 rounded-full bg-primary/10 flex items-center justify-center">
                                        <Mail className="w-4 h-4 text-primary" />
                                    </div>
                                    {getSetting(settings, 'company_email', 'admin@suryamitra.in')}
                                </a>
                            </div>
                        </div>

                        {/* Agent commission section */}
                        <section>
                            <h2 className="text-xl font-bold text-dark mb-3">Commission Disputes (For Agents)</h2>
                            <p className="text-neutral-700 text-sm leading-relaxed">
                                If you are a registered Business Development Executive (SM-XXXX) or Business Development Manager (SSM-XXXX) and have a dispute regarding commission calculations or payments, please contact our admin team at{' '}
                                <a href={`mailto:${getSetting(settings, 'company_email', 'admin@suryamitra.in')}`} className="text-primary underline">
                                    {getSetting(settings, 'company_email', 'admin@suryamitra.in')}
                                </a>{' '}
                                with your agent code and relevant lead details. Commission disputes are reviewed within 5 business working days.
                            </p>
                        </section>

                    </div>
                </div>
            </main>
            <Footer />
        </div>
    );
}
