import { useQuery } from '@tanstack/react-query';
import { publicApi, type PublicSettingsData } from '@/api/public.api';
import { ArrowRight, Play, Sun, CheckCircle2 } from 'lucide-react';

const DEFAULT: PublicSettingsData = {
    company_name: 'SuryaMitra',
    company_email: null, company_mobile: null, company_whatsapp: null,
    company_address: null, company_slogan: null, company_logo: null,
    company_favicon: null, company_signature: null,
    company_website: 'https://suryamitra.in',
    social_facebook: null, social_twitter: null, social_instagram: null, social_youtube: null,
    hero_headline: 'Get 300 Units FREE Electricity Every Month',
    hero_subheadline: 'Government of India scheme — up to ₹78,000 subsidy for rooftop solar installation. We guide you end-to-end, completely free.',

    hero_video: null,
    hero_stats_json: null,
    how_it_works_json: null,
    why_choose_us_json: null,
    calculator_headline: null, calculator_subheadline: null, calculator_values_json: null,
    eligibility_headline: null, eligibility_subheadline: null, eligibility_questions_json: null,
    eligibility_success_title: null, eligibility_success_desc: null,
    eligibility_error_title: null, eligibility_error_desc: null,
    footer_about_text: null, footer_copyright: null, footer_disclaimer: null,
};

export default function HeroSection() {
    const { data: settings = DEFAULT } = useQuery({
        queryKey: ['public-settings'],
        queryFn: publicApi.getSettings,
    });

    const hasVideo = !!settings.hero_video;

    return (
        <section className="relative overflow-hidden min-h-[600px] md:min-h-[800px] flex items-center pt-20">
            {/* Background Layer */}
            <div className="absolute inset-0 z-0">
                {hasVideo ? (
                    <video
                        autoPlay loop muted playsInline
                        className="w-full h-full object-cover scale-105"
                        src={settings.hero_video!}
                    />
                ) : (
                    <div className="w-full h-full grad-primary" />
                )}
                <div className={`absolute inset-0 ${hasVideo ? 'bg-primary/60' : 'bg-primary/20'} backdrop-blur-[2px]`} />
            </div>

            {/* Decorative Floating Blobs */}
            <div className="absolute top-1/4 -left-20 w-80 h-80 bg-accent/20 blur-[100px] rounded-full animate-float" />
            <div className="absolute bottom-1/4 -right-20 w-96 h-96 bg-primary-light/30 blur-[120px] rounded-full animate-float" style={{ animationDelay: '2s' }} />

            {/* Content Container */}
            <div className="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
                <div className="grid lg:grid-cols-2 gap-16 items-center">
                    <div className="animate-slide-up">
                        <div className="inline-flex items-center gap-2 bg-white/10 backdrop-blur-md text-white text-[10px] font-black tracking-[0.2em] uppercase px-5 py-2.5 rounded-pill border border-white/20 mb-8 shadow-xl">
                            <span className="flex h-2 w-2 relative">
                                <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-accent opacity-75"></span>
                                <span className="relative inline-flex rounded-full h-2 w-2 bg-accent"></span>
                            </span>
                            PM Surya Ghar Yojana 2024
                        </div>

                        <h1 className="font-display font-black text-4xl sm:text-5xl md:text-7xl text-white leading-[1.1] mb-8">
                            {((settings?.hero_headline ?? DEFAULT.hero_headline) || '').split(' ').map((word, i) => (
                                <span key={i} className={word.toLowerCase() === 'free' || word.toLowerCase() === 'units' ? 'text-accent text-glow' : ''}>
                                    {word}{' '}
                                </span>
                            ))}
                        </h1>

                        <p className="text-white/70 text-lg md:text-xl mb-10 max-w-xl leading-relaxed font-body">
                            {settings.hero_subheadline}
                        </p>

                        <div className="flex flex-col sm:flex-row gap-5">
                            <a href="#lead-form" className="btn-accent group relative overflow-hidden">
                                <span className="relative z-10 flex items-center justify-center gap-3">
                                    Get Free Electricity <ArrowRight className="w-5 h-5 group-hover:translate-x-1 transition-transform" />
                                </span>
                            </a>
                            <a href="/agent/register" className="glass-card px-8 py-3 flex items-center justify-center gap-3 text-white font-bold hover:bg-white/20 transition-all border-white/10">
                                <Play className="w-5 h-5 text-accent" /> Become a Partner
                            </a>
                        </div>
                    </div>

                    {/* Visual Element / Glass Card Widget */}
                    <div className="hidden lg:block animate-float" style={{ animationDuration: '8s' }}>
                        <div className="glass-card p-1 items-center justify-center overflow-hidden">
                            <div className="bg-dark/20 rounded-[22px] p-8 backdrop-blur-3xl border border-white/5 relative overflow-hidden">
                                {/* Inner decorative light */}
                                <div className="absolute -top-24 -right-24 w-48 h-48 bg-accent/20 blur-[60px] rounded-full" />

                                <div className="relative z-10 space-y-8">
                                    <div className="flex items-center justify-between border-b border-white/10 pb-6">
                                        <div>
                                            <div className="text-white/40 text-[10px] font-black uppercase tracking-widest mb-1">Max Subsidy</div>
                                            <div className="text-4xl font-display font-black text-white">₹78,000</div>
                                        </div>
                                        <div className="h-14 w-14 rounded-2xl grad-accent flex items-center justify-center shadow-glow">
                                            <Sun className="text-white w-8 h-8" />
                                        </div>
                                    </div>

                                    <div className="grid grid-cols-2 gap-8">
                                        <div>
                                            <div className="text-white/40 text-[10px] font-black uppercase tracking-widest mb-1">System Life</div>
                                            <div className="text-2xl font-display font-bold text-white">25 Years</div>
                                        </div>
                                        <div>
                                            <div className="text-white/40 text-[10px] font-black uppercase tracking-widest mb-1">Payback</div>
                                            <div className="text-2xl font-display font-bold text-white">48 Months</div>
                                        </div>
                                    </div>

                                    <div className="bg-white/5 rounded-2xl p-4 border border-white/10">
                                        <div className="flex items-center gap-3 text-sm text-white/60">
                                            <CheckCircle2 className="text-success w-5 h-5" />
                                            <span>Fully Government Funded</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {/* Bottom Wave / Transition */}
            <div className="absolute bottom-0 left-0 right-0 h-32 bg-gradient-to-t from-white to-transparent" />
        </section>
    );
}
