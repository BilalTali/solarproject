import { Link } from 'react-router-dom';
import { Sun, ShieldCheck, Users, User, ArrowRight, Home } from 'lucide-react';
import Navbar from '@/components/public/Navbar';
import Footer from '@/components/public/Footer';
import SEOHead from '@/components/shared/SEOHead';
import { useSettings } from '@/hooks/useSettings';

export default function LoginPage() {
    const { companyName } = useSettings();
    const roles = [
        {
            id: 'admin',
            title: 'Administrator',
            description: 'Full access to system settings, reports, and team management.',
            icon: <ShieldCheck className="w-8 h-8 text-blue-600" />,
            bg: 'bg-blue-50',
            border: 'border-blue-100',
            link: '/admin/login',
            cta: 'Admin Login'
        },
        {
            id: 'super_agent',
            title: 'Business Development Manager',
            description: 'Manage your team of agents, track leads, and view override earnings.',
            icon: <Users className="w-8 h-8 text-orange-600" />,
            bg: 'bg-orange-50',
            border: 'border-orange-100',
            link: '/super-agent/login',
            cta: 'Business Development Manager Login'
        },
        {
            id: 'agent',
            title: 'Business Development Executive',
            description: 'Submit leads, track your installations, and manage your profile.',
            icon: <User className="w-8 h-8 text-emerald-600" />,
            bg: 'bg-emerald-50',
            border: 'border-emerald-100',
            link: '/agent/login',
            cta: 'Business Development Executive Login'
        }
    ];

    return (
        <div className="min-h-screen bg-neutral-50 flex flex-col">
            <SEOHead title="Portal Access" description="Access the SuryaMitra management dashboard for Admins, Managers, and Executives." />
            <Navbar />

            <main className="flex-1 flex flex-col items-center justify-center p-6 py-12">
                <div className="max-w-4xl w-full">
                    <div className="text-center mb-12">
                        <div className="inline-flex items-center gap-2 mb-4">
                            <div className="w-12 h-12 rounded-full bg-gradient-to-br from-accent to-accent-dark flex items-center justify-center shadow-lg">
                                <Sun className="w-7 h-7 text-white" />
                            </div>
                            <span className="font-display font-bold text-3xl text-primary tracking-tight">{companyName}</span>
                        </div>
                        <h1 className="text-3xl md:text-4xl font-display font-extrabold text-dark mb-3">Portal Access</h1>
                        <p className="text-neutral-600 text-lg max-w-xl mx-auto">Select your role to access your dedicated management dashboard</p>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                        {roles.map((role) => (
                            <Link
                                key={role.id}
                                to={role.link}
                                className={`group relative bg-white rounded-3xl p-8 border-2 ${role.border} hover:border-primary/30 transition-all duration-300 hover:shadow-2xl hover:-translate-y-2 flex flex-col h-full`}
                            >
                                <div className={`w-16 h-16 ${role.bg} rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform`}>
                                    {role.icon}
                                </div>

                                <h3 className="text-xl font-bold text-dark mb-3 tracking-tight">{role.title}</h3>
                                <p className="text-neutral-500 text-sm leading-relaxed mb-8 flex-1">
                                    {role.description}
                                </p>

                                <div className="flex items-center gap-2 font-bold text-sm text-primary group-hover:gap-3 transition-all">
                                    <span>{role.cta}</span>
                                    <ArrowRight className="w-4 h-4" />
                                </div>

                                {/* Subtle background glow on hover */}
                                <div className={`absolute inset-0 ${role.bg} opacity-0 group-hover:opacity-5 rounded-3xl transition-opacity pointer-events-none`} />
                            </Link>
                        ))}
                    </div>

                    <div className="mt-12 text-center">
                        <Link to="/" className="inline-flex items-center gap-2 text-sm font-medium text-neutral-500 hover:text-primary transition-colors">
                            <Home className="w-4 h-4" />
                            <span>Return to Public Homepage</span>
                        </Link>
                    </div>
                </div>
            </main>

            <Footer />
        </div>
    );
}
