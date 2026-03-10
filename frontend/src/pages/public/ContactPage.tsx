
import Navbar from '@/components/public/Navbar';
import Footer from '@/components/public/Footer';
import SEOHead from '@/components/shared/SEOHead';
import { Mail, Phone } from 'lucide-react';

export default function ContactPage() {
    return (
        <div className="min-h-screen bg-neutral-50 flex flex-col">
            <SEOHead />
            <Navbar />
            <main className="flex-grow max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16 w-full">
                <div className="bg-white rounded-3xl p-8 md:p-12 shadow-sm border border-gray-100">
                    <h1 className="text-4xl font-display font-bold text-dark mb-6 text-center">Get in Touch</h1>
                    <p className="text-lg text-neutral-600 text-center mb-12 max-w-2xl mx-auto">
                        Whether you are a homeowner or a professional looking to join our Agent network, our designated support team is here to help.
                    </p>

                    <div className="grid md:grid-cols-2 gap-8 mb-12">
                        <div className="bg-neutral-50 p-6 rounded-2xl border border-gray-100 flex flex-col items-center text-center">
                            <Mail className="w-8 h-8 text-primary mb-3" />
                            <h3 className="font-bold text-dark mb-1">Email Support</h3>
                            <p className="text-sm text-neutral-600 mb-2">General setup & technical queries</p>
                            <a href="mailto:support@example.com" className="text-accent font-medium mt-auto">support@example.com</a>
                        </div>
                        <div className="bg-neutral-50 p-6 rounded-2xl border border-gray-100 flex flex-col items-center text-center">
                            <Phone className="w-8 h-8 text-primary mb-3" />
                            <h3 className="font-bold text-dark mb-1">Helpline</h3>
                            <p className="text-sm text-neutral-600 mb-2">Available Mon-Sat (9 AM - 6 PM)</p>
                            <a href="tel:+919876543210" className="text-accent font-medium mt-auto">+91-98765 43210</a>
                        </div>
                    </div>

                    <div className="bg-primary/5 rounded-2xl p-8 text-center border border-primary/10">
                        <h3 className="text-xl font-bold text-dark mb-3">Agent Support</h3>
                        <p className="text-sm text-neutral-700 max-w-lg mx-auto">
                            If you are an existing agent experiencing issues with your dashboard, lead tracking, or commission payouts, please log in and utilize the internal <strong>Feedback</strong> ticketing system for prioritized support.
                        </p>
                    </div>
                </div>
            </main>
            <Footer />
        </div>
    );
}
