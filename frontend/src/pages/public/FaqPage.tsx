
import Navbar from '@/components/public/Navbar';
import Footer from '@/components/public/Footer';
import SEOHead from '@/components/shared/SEOHead';

export default function FaqPage() {
    return (
        <div className="min-h-screen bg-neutral-50 flex flex-col">
            <SEOHead />
            <Navbar />
            <main className="flex-grow max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16 w-full">
                <div className="bg-white rounded-3xl p-8 md:p-12 shadow-sm border border-gray-100">
                    <h1 className="text-4xl font-display font-bold text-dark mb-10 text-center">Frequently Asked Questions</h1>

                    <div className="space-y-10">
                        <section>
                            <h2 className="text-2xl font-bold text-primary mb-6 border-b pb-2">For Homeowners (Beneficiaries)</h2>
                            <div className="space-y-6">
                                <div>
                                    <h3 className="font-bold text-dark mb-2 text-lg">What is the PM Surya Ghar Yojana?</h3>
                                    <p className="text-neutral-600 text-sm">It is a government initiative providing subsidies to households for installing rooftop solar panels, aiming to provide up to 300 units of free electricity every month.</p>
                                </div>
                                <div>
                                    <h3 className="font-bold text-dark mb-2 text-lg">Is my data safe if I fill out the application form?</h3>
                                    <p className="text-neutral-600 text-sm">Absolutely. Your data is routed directly into our secure, encrypted database. It is only shared with verified agents assigned to assist with your specific geographic area.</p>
                                </div>
                            </div>
                        </section>

                        <section>
                            <h2 className="text-2xl font-bold text-primary mb-6 border-b pb-2">For Agents & Super Agents</h2>
                            <div className="space-y-6">
                                <div>
                                    <h3 className="font-bold text-dark mb-2 text-lg">How do I get paid for my leads?</h3>
                                    <p className="text-neutral-600 text-sm">Commissions are automatically calculated by our system when a lead progresses to the "Installed" and "Completed" stages. Payments are processed securely to the encrypted bank details you provided during KYC.</p>
                                </div>
                                <div>
                                    <h3 className="font-bold text-dark mb-2 text-lg">Why do I need to provide my Aadhaar and PAN?</h3>
                                    <p className="text-neutral-600 text-sm">Due to financial compliance and to ensure the integrity of the network, we require government-issued identification to verify all agents handling citizen data and receiving financial payouts.</p>
                                </div>
                                <div>
                                    <h3 className="font-bold text-dark mb-2 text-lg">Can I access my account on a mobile device?</h3>
                                    <p className="text-neutral-600 text-sm">Yes, our modern portal is fully responsive and designed to work seamlessly on smartphones and tablets, allowing you to update lead statuses on the go.</p>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </main>
            <Footer />
        </div>
    );
}
