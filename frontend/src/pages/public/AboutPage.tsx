
import Navbar from '@/components/public/Navbar';
import Footer from '@/components/public/Footer';
import SEOHead from '@/components/shared/SEOHead';
import { Shield, Target, Users } from 'lucide-react';

export default function AboutPage() {
    return (
        <div className="min-h-screen bg-neutral-50 flex flex-col">
            <SEOHead />
            <Navbar />
            <main className="flex-grow max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16 w-full">
                <div className="bg-white rounded-3xl p-8 md:p-12 shadow-sm border border-gray-100">
                    <h1 className="text-4xl font-display font-bold text-dark mb-6 text-center">About SuryaMitra</h1>
                    <p className="text-lg text-neutral-600 text-center mb-12 max-w-2xl mx-auto">
                        Empowering India with Solar Energy by acting as the bridge for the PM Surya Ghar Muft Bijli Yojana.
                    </p>

                    <div className="grid md:grid-cols-3 gap-8 mb-12">
                        <div className="text-center p-6 bg-primary/5 rounded-2xl">
                            <Target className="w-10 h-10 text-primary mx-auto mb-4" />
                            <h3 className="font-bold text-dark mb-2">Our Mission</h3>
                            <p className="text-sm text-neutral-600">Accelerate India's transition to renewable energy securely and transparently.</p>
                        </div>
                        <div className="text-center p-6 bg-accent/5 rounded-2xl">
                            <Shield className="w-10 h-10 text-accent mx-auto mb-4" />
                            <h3 className="font-bold text-dark mb-2">Secure & Reliable</h3>
                            <p className="text-sm text-neutral-600">Enterprise-grade encryption and strict KYC processes for peace of mind.</p>
                        </div>
                        <div className="text-center p-6 bg-success/5 rounded-2xl">
                            <Users className="w-10 h-10 text-success mx-auto mb-4" />
                            <h3 className="font-bold text-dark mb-2">Vetted Agents</h3>
                            <p className="text-sm text-neutral-600">A massive network of verified professionals ready to assist households.</p>
                        </div>
                    </div>

                    <div className="prose prose-blue max-w-none text-neutral-700">
                        <p>Welcome to SuryaMitra, your trusted partner in navigating the PM Surya Ghar Muft Bijli Yojana. We are a specialized platform dedicated to connecting homeowners with qualified solar agents and facilitators.</p>
                        <p>Through our secure, state-of-the-art portal, we manage a network of vetted Super Agents and Agents who assist citizens in assessing their roof capacity, estimating bill savings, and applying for government subsidies.</p>
                    </div>
                </div>
            </main>
            <Footer />
        </div>
    );
}
