import { Helmet } from 'react-helmet-async';
import Navbar from '@/components/public/Navbar';
import Footer from '@/components/public/Footer';
import { useQuery } from '@tanstack/react-query';
import { publicApi } from '@/api/public.api';
import {
    BookOpen, CheckCircle, Info, ShieldAlert,
    MapPin, Calculator, Users, Trophy,
    LogIn, Sun, Star
} from 'lucide-react';

const getSetting = (settingsObj: any, key: string, fallback: string) => {
    return settingsObj?.[key] || fallback;
};

export default function UserManualPage() {
    const { data: settings = {} } = useQuery({
        queryKey: ['public-settings'],
        queryFn: publicApi.getSettings,
    });

    const companyName = getSetting(settings, 'company_name', 'AndleebSurya');

    return (
        <div className="min-h-screen bg-neutral-50 flex flex-col pt-16">
            <Helmet>
                <title>User Manual — {companyName} | PM Surya Ghar Facilitation</title>
                <meta name="description" content={`Complete guide for Citizens, BDEs, and BDMs using the ${companyName} facilitation portal for PM Surya Ghar.`} />
            </Helmet>
            <Navbar />

            <main className="flex-grow">
                {/* Hero section */}
                <div className="bg-[#0A3D7A] py-16 text-white overflow-hidden relative">
                    <div className="absolute top-0 right-0 w-1/2 h-full bg-[#F97316]/10 blur-[100px] rounded-full translate-x-1/2 -translate-y-1/2" />
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
                        <div className="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 border border-white/20 text-[#F97316] font-bold text-xs uppercase tracking-widest mb-6">
                            <BookOpen className="w-4 h-4" /> v1.0 • March 2026
                        </div>
                        <h1 className="text-4xl md:text-6xl font-display font-black mb-4 tracking-tight">
                            User <span className="text-[#F97316]">Manual</span>
                        </h1>
                        <p className="text-xl text-blue-100 max-w-2xl font-light leading-relaxed">
                            PM Surya Ghar Muft Bijli Yojana — Facilitation Portal Guide for Citizens, BDE (Executives) & BDM (Managers).
                        </p>
                    </div>
                </div>

                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                    <div className="flex flex-col lg:flex-row gap-12">
                        {/* Static Sidebar Navigation */}
                        <aside className="lg:w-64 shrink-0 lg:sticky lg:top-24 h-fit">
                            <nav className="space-y-1 bg-white p-4 rounded-3xl shadow-sm border border-neutral-100">
                                <p className="px-4 py-2 text-[10px] font-black uppercase tracking-widest text-neutral-400">Chapters</p>
                                <a href="#intro" className="flex items-center gap-3 px-4 py-2.5 text-sm font-bold text-neutral-600 hover:text-[#F97316] hover:bg-neutral-50 rounded-xl transition-all">
                                    <Info className="w-4 h-4" /> 1. Introduction
                                </a>
                                <a href="#citizens" className="flex items-center gap-3 px-4 py-2.5 text-sm font-bold text-neutral-600 hover:text-[#F97316] hover:bg-neutral-50 rounded-xl transition-all">
                                    <MapPin className="w-4 h-4" /> 2. For Citizens
                                </a>
                                <a href="#bde" className="flex items-center gap-3 px-4 py-2.5 text-sm font-bold text-neutral-600 hover:text-[#F97316] hover:bg-neutral-50 rounded-xl transition-all">
                                    <Users className="w-4 h-4" /> 3. Become a BDE
                                </a>
                                <a href="#bdm" className="flex items-center gap-3 px-4 py-2.5 text-sm font-bold text-neutral-600 hover:text-[#F97316] hover:bg-neutral-50 rounded-xl transition-all">
                                    <Trophy className="w-4 h-4" /> 4. BDM Guide
                                </a>
                                <a href="#dashboard" className="flex items-center gap-3 px-4 py-2.5 text-sm font-bold text-neutral-600 hover:text-[#F97316] hover:bg-neutral-50 rounded-xl transition-all">
                                    <LogIn className="w-4 h-4" /> 5. Using Portal
                                </a>
                                <a href="#commissions" className="flex items-center gap-3 px-4 py-2.5 text-sm font-bold text-neutral-600 hover:text-[#F97316] hover:bg-neutral-50 rounded-xl transition-all">
                                    <Calculator className="w-4 h-4" /> 6. Commissions
                                </a>
                                <a href="#faq" className="flex items-center gap-3 px-4 py-2.5 text-sm font-bold text-neutral-600 hover:text-[#F97316] hover:bg-neutral-50 rounded-xl transition-all">
                                    <Star className="w-4 h-4" /> 7. FAQ
                                </a>
                            </nav>
                        </aside>

                        {/* Content Area */}
                        <div className="flex-grow">
                            <div className="bg-green-50 border border-green-100 rounded-[2rem] p-8 mb-12 flex items-start gap-6 shadow-sm">
                                <div className="w-14 h-14 bg-green-600 rounded-2xl flex items-center justify-center text-white shrink-0 shadow-lg shadow-green-200">
                                    <CheckCircle size={28} />
                                </div>
                                <div>
                                    <h4 className="text-green-900 font-display font-black text-xl mb-1 italic tracking-tight">100% FREE SERVICE</h4>
                                    <p className="text-green-800 leading-relaxed m-0 text-sm">Our facilitation service is <strong>100% FREE</strong> for all beneficiaries. {companyName} agents never charge any fee at any step. Report any demands immediately.</p>
                                </div>
                            </div>

                            <article className="prose prose-blue max-w-none prose-headings:font-display prose-headings:font-black prose-headings:tracking-tight prose-p:text-neutral-600 prose-p:leading-relaxed">

                                <section id="intro">
                                    <h2 className="text-3xl md:text-4xl text-[#0A3D7A]">1. What is {companyName}?</h2>
                                    <p>
                                        {companyName} (andleebsurya.in) is a private digital facilitation platform operated by <strong>Andleeb Cluster of Services Pvt</strong>, in affiliation with <strong>Malik Solar Tech Agency</strong> — an Authorized Distributor for the PM Surya Ghar Muft Bijli Yojana in Jammu & Kashmir and Ladakh.
                                    </p>
                                    <p>
                                        We connect citizens who want free solar electricity with trained Business Development Executives (BDE) who guide them through the entire registration and documentation process — completely free of charge.
                                    </p>
                                    <div className="bg-red-50 border-l-4 border-red-500 rounded-r-3xl p-6 my-8 text-sm text-red-900 shadow-sm shadow-red-50">
                                        <div className="flex items-center gap-2 mb-2 font-black uppercase tracking-widest text-red-600">
                                            <ShieldAlert size={18} /> Important Disclaimer
                                        </div>
                                        {companyName} is a <strong>PRIVATE FACILITATION COMPANY</strong>. We are NOT a government portal. The official scheme portal is <a href="https://pmsuryaghar.gov.in" target="_blank" rel="noreferrer" className="text-red-700 font-bold underline">pmsuryaghar.gov.in</a>
                                    </div>

                                    <h3>1.1 Understanding PM Surya Ghar Yojana</h3>
                                    <ul className="grid grid-cols-1 md:grid-cols-2 gap-4 list-none p-0 mt-6">
                                        <li className="bg-white border border-neutral-100 p-5 rounded-2xl flex gap-4 shadow-sm hover:shadow-md transition-shadow">
                                            <div className="w-10 h-10 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center shrink-0"><Sun className="w-5 h-5" /></div>
                                            <div><p className="font-bold m-0 p-0 text-neutral-800">Free PV Panels</p><p className="m-0 p-0 text-xs text-neutral-500">Residential rooftop installation</p></div>
                                        </li>
                                        <li className="bg-white border border-neutral-100 p-5 rounded-2xl flex gap-4 shadow-sm hover:shadow-md transition-shadow">
                                            <div className="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center shrink-0"><Calculator className="w-5 h-5" /></div>
                                            <div><p className="font-bold m-0 p-0 text-neutral-800">₹78,000 Subsidy</p><p className="m-0 p-0 text-xs text-neutral-500">Maximum residential subsidy</p></div>
                                        </li>
                                        <li className="bg-white border border-neutral-100 p-5 rounded-2xl flex gap-4 shadow-sm hover:shadow-md transition-shadow">
                                            <div className="w-10 h-10 rounded-xl bg-green-100 text-green-600 flex items-center justify-center shrink-0"><CheckCircle className="w-5 h-5" /></div>
                                            <div><p className="font-bold m-0 p-0 text-neutral-800">300 Free Units</p><p className="m-0 p-0 text-xs text-neutral-500">Estimated monthly generation</p></div>
                                        </li>
                                        <li className="bg-white border border-neutral-100 p-5 rounded-2xl flex gap-4 shadow-sm hover:shadow-md transition-shadow">
                                            <div className="w-10 h-10 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center shrink-0"><Star className="w-5 h-5" /></div>
                                            <div><p className="font-bold m-0 p-0 text-neutral-800">Earn Credits</p><p className="m-0 p-0 text-xs text-neutral-500">Net metering for surplus energy</p></div>
                                        </li>
                                    </ul>

                                    <div className="overflow-hidden rounded-[2rem] border border-neutral-200 mt-8 shadow-inner shadow-neutral-100 bg-white">
                                        <table className="min-w-full text-sm m-0">
                                            <thead className="bg-[#0A3D7A] text-white">
                                                <tr>
                                                    <th className="px-6 py-4 text-left font-black tracking-widest uppercase text-[10px]">Capacity</th>
                                                    <th className="px-6 py-4 text-left font-black tracking-widest uppercase text-[10px]">Cost</th>
                                                    <th className="px-6 py-4 text-left font-black tracking-widest uppercase text-[10px]">Total Subsidy</th>
                                                    <th className="px-6 py-4 text-left font-black tracking-widest uppercase text-[10px]">Your Share</th>
                                                </tr>
                                            </thead>
                                            <tbody className="divide-y divide-neutral-100">
                                                <tr><td className="px-6 py-3 font-bold text-[#0A3D7A]">3 kW</td><td>₹1,59,500</td><td className="text-green-600 font-bold">₹94,800</td><td className="font-bold">₹64,700</td></tr>
                                                <tr><td className="px-6 py-3 font-bold text-[#0A3D7A]">4 kW</td><td>₹2,09,000</td><td className="text-green-600 font-bold">₹94,800</td><td className="font-bold">₹1,14,200</td></tr>
                                                <tr><td className="px-6 py-3 font-bold text-[#0A3D7A]">5 kW</td><td>₹2,59,500</td><td className="text-green-600 font-bold">₹94,800</td><td className="font-bold">₹1,63,700</td></tr>
                                                <tr className="bg-blue-50/50"><td className="px-6 py-3 font-bold text-[#0A3D7A]">10 kW</td><td>₹5,15,000</td><td className="text-green-600 font-bold">₹94,800</td><td className="font-bold">₹4,20,200</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <p className="text-[10px] text-neutral-400 mt-4 leading-relaxed italic">⚠️ Subsidy amounts (MNRE + UT Share) are indicative and set by central/state governments. Always check latest rates on the dashboard.</p>
                                </section>

                                <hr className="my-16 border-neutral-100" />

                                <section id="citizens">
                                    <h2 className="text-3xl text-[#0A3D7A] flex items-center gap-3">
                                        <div className="w-12 h-12 bg-white rounded-2xl shadow-sm border border-neutral-100 flex items-center justify-center text-[#F97316]"><MapPin size={24} /></div>
                                        2. For Citizens — Getting Started
                                    </h2>
                                    <h3>Registration Step-by-Step</h3>
                                    <div className="relative pl-12 space-y-12 before:absolute before:left-5 before:top-2 before:bottom-2 before:w-0.5 before:bg-gradient-to-b before:from-orange-400 before:to-blue-400">
                                        {[
                                            { s: 1, t: "Submit Query", d: "Visit andleebsurya.in, click 'Apply Now' and fill your basic details." },
                                            { s: 2, t: "Verified Callback", d: "A BDE (SM-XXXX) will call you within 24 hours for a consultation." },
                                            { s: 3, t: "Document Check", d: "Agent helps you prepare Aadhaar, Bill, and Bank Passbook copies." },
                                            { s: 4, t: "Portal Filing", d: "Agent submits your application on the official govt portal (pmsuryaghar.gov.in)." },
                                            { s: 5, t: "Site Survey", d: "Technical assessment of your rooftop by an empanelled vendor." },
                                            { s: 6, t: "Installation", d: "Final setup of PV panels and connection with the grid (Net Metering)." }
                                        ].map(step => (
                                            <div key={step.s} className="relative">
                                                <div className="absolute -left-[44px] top-0 w-8 h-8 rounded-full bg-white border-4 border-white shadow-md flex items-center justify-center font-black text-xs text-[#0A3D7A] z-10">{step.s}</div>
                                                <h4 className="m-0 font-display font-bold text-neutral-800">{step.t}</h4>
                                                <p className="m-0 mt-1 text-sm text-neutral-500">{step.d}</p>
                                            </div>
                                        ))}
                                    </div>

                                    <div className="bg-[#0A3D7A] text-white rounded-[2rem] p-8 mt-16 shadow-xl relative overflow-hidden group">
                                        <div className="absolute top-0 right-0 p-8 opacity-10 group-hover:scale-110 transition-transform"><Star size={120} /></div>
                                        <h4 className="text-white m-0 flex items-center gap-2 mb-4 font-black"><ShieldAlert size={20} className="text-[#F97316]" /> Verify Your Agent</h4>
                                        <p className="text-blue-100 text-sm leading-relaxed m-0">
                                            Never share sensitive documents blindly. <strong>Scan the QR code</strong> on your agent's ID card. It must load an active verification page on <code>andleebsurya.in</code>.
                                        </p>
                                    </div>
                                </section>

                                <hr className="my-16 border-neutral-100" />

                                <section id="bde">
                                    <h2 className="text-3xl text-[#0A3D7A] flex items-center gap-3">
                                        <div className="w-12 h-12 bg-white rounded-2xl shadow-sm border border-neutral-100 flex items-center justify-center text-blue-500"><Users size={24} /></div>
                                        3. Become a BDE
                                    </h2>
                                    <p>Join J&K's fastest growing solar facilitation network. As a <strong>Business Development Executive (BDE)</strong>, you help your community go solar while earning competitive commissions.</p>

                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6 my-10">
                                        <div className="p-8 bg-white rounded-[2.5rem] border border-neutral-100 shadow-sm relative group overflow-hidden">
                                            <div className="absolute top-0 right-0 w-24 h-24 bg-blue-50 rounded-full translate-x-8 -translate-y-8" />
                                            <h4 className="m-0 mb-4 font-black text-blue-900 border-b pb-2">Requirements</h4>
                                            <ul className="list-none p-0 m-0 space-y-2 text-xs font-bold text-neutral-600">
                                                <li className="flex gap-2 items-center"><CheckCircle size={14} className="text-blue-500" /> J&K or Ladakh Resident</li>
                                                <li className="flex gap-2 items-center"><CheckCircle size={14} className="text-blue-500" /> Valid Aadhaar & PAN</li>
                                                <li className="flex gap-2 items-center"><CheckCircle size={14} className="text-blue-500" /> Active Smartphone</li>
                                                <li className="flex gap-2 items-center"><CheckCircle size={14} className="text-blue-500" /> Field Research Passion</li>
                                            </ul>
                                        </div>
                                        <div className="p-8 bg-white rounded-[2.5rem] border border-neutral-100 shadow-sm relative group overflow-hidden">
                                            <div className="absolute top-0 right-0 w-24 h-24 bg-orange-50 rounded-full translate-x-8 -translate-y-8" />
                                            <h4 className="m-0 mb-4 font-black text-orange-900 border-b pb-2">BDE Perks</h4>
                                            <ul className="list-none p-0 m-0 space-y-2 text-xs font-bold text-neutral-600">
                                                <li className="flex gap-2 items-center"><CheckCircle size={14} className="text-[#F97316]" /> Paid Commission per Lead</li>
                                                <li className="flex gap-2 items-center"><CheckCircle size={14} className="text-[#F97316]" /> Official Verification QR</li>
                                                <li className="flex gap-2 items-center"><CheckCircle size={14} className="text-[#F97316]" /> Digital Joining Letter</li>
                                                <li className="flex gap-2 items-center"><CheckCircle size={14} className="text-[#F97316]" /> Performance Vouchers</li>
                                            </ul>
                                        </div>
                                    </div>
                                </section>

                                <hr className="my-16 border-neutral-100" />

                                <section id="bdm">
                                    <h2 className="text-3xl text-[#0A3D7A] flex items-center gap-3">
                                        <div className="w-12 h-12 bg-white rounded-2xl shadow-sm border border-neutral-100 flex items-center justify-center text-amber-500"><Trophy size={24} /></div>
                                        4. BDM Guide (SSM-XXXX)
                                    </h2>
                                    <p>BDMs are senior leaders who build teams. They earn direct commissions PLUS override payouts from their team member installations.</p>

                                    <div className="bg-gradient-to-br from-[#0A3D7A] to-slate-900 rounded-[2.5rem] p-8 md:p-12 text-white shadow-xl relative overflow-hidden group mb-12">
                                        <div className="relative z-10">
                                            <h3 className="text-white m-0 mb-4 font-black tracking-tight">Understanding "Absorbed Points"</h3>
                                            <p className="text-blue-100 text-sm leading-relaxed mb-8 max-w-xl">
                                                If a BDE under your team fails to reach their offer target before expiry, the installations they completed are <strong>absorbed</strong> by you. This ensures your management effort is always rewarded.
                                            </p>
                                            <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                                                <div className="p-4 rounded-2xl bg-white/5 border border-white/10 text-center"><p className="m-0 text-[10px] font-black uppercase text-accent mb-1">Transfer</p><p className="m-0 font-bold text-xs uppercase tracking-tighter">Automatic</p></div>
                                                <div className="p-4 rounded-2xl bg-white/5 border border-white/10 text-center"><p className="m-0 text-[10px] font-black uppercase text-accent mb-1">Action</p><p className="m-0 font-bold text-xs uppercase tracking-tighter">Claimable</p></div>
                                                <div className="p-4 rounded-2xl bg-white/5 border border-white/10 text-center"><p className="m-0 text-[10px] font-black uppercase text-accent mb-1">Benefit</p><p className="m-0 font-bold text-xs uppercase tracking-tighter">Full Team</p></div>
                                                <div className="p-4 rounded-2xl bg-white/5 border border-white/10 text-center"><p className="m-0 text-[10px] font-black uppercase text-accent mb-1">Expiry</p><p className="m-0 font-bold text-xs uppercase tracking-tighter">End Date</p></div>
                                            </div>
                                        </div>
                                    </div>
                                </section>

                                <hr className="my-16 border-neutral-100" />

                                <section id="dashboard" className="mb-24">
                                    <h2 className="text-3xl text-[#0A3D7A] flex items-center gap-3">
                                        <div className="w-12 h-12 bg-white rounded-2xl shadow-sm border border-neutral-100 flex items-center justify-center text-dark"><LogIn size={24} /></div>
                                        5. Using the Portal
                                    </h2>
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
                                        <div className="border-t-2 border-[#F97316] pt-6">
                                            <h4 className="m-0 font-black italic text-dark mb-2">Login & Security</h4>
                                            <p className="text-sm m-0 leading-relaxed">Use your mobile number (User ID) and the password sent by admin. Always logout from shared devices.</p>
                                        </div>
                                        <div className="border-t-2 border-blue-600 pt-6">
                                            <h4 className="m-0 font-black italic text-dark mb-2">Real-time Tracking</h4>
                                            <p className="text-sm m-0 leading-relaxed">Public users can track via <code>/track-status</code>. Agents see full internal history in their panel.</p>
                                        </div>
                                        <div className="border-t-2 border-green-600 pt-6">
                                            <h4 className="m-0 font-black italic text-dark mb-2">Offer Colors</h4>
                                            <p className="text-sm m-0 leading-relaxed">Blue cards turn <strong>Gold</strong> as you hit targets. A notification confetti will guide you to claim prizes.</p>
                                        </div>
                                        <div className="border-t-2 border-purple-600 pt-6">
                                            <h4 className="m-0 font-black italic text-dark mb-2">ID Downloads</h4>
                                            <p className="text-sm m-0 leading-relaxed">Visit Profile on your phone to download the High-Res ID Card for home verification scans.</p>
                                        </div>
                                    </div>
                                </section>

                            </article>
                        </div>
                    </div>
                </div>
            </main>

            <Footer />
        </div>
    );
}
