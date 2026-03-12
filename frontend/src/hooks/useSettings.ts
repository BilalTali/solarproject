import { useQuery } from '@tanstack/react-query';
import { publicApi, type PublicSettingsData } from '@/api/public.api';

export const DEFAULT_SETTINGS: PublicSettingsData = {
    company_name: 'SuryaMitra',
    company_email: null,
    company_mobile: null,
    company_whatsapp: null,
    company_address: null,
    company_slogan: null,
    company_logo: null,
    company_favicon: null,
    company_signature: null,
    company_website: 'https://suryamitra.in',
    social_facebook: null,
    social_twitter: null,
    social_instagram: null,
    social_youtube: null,
    hero_headline: 'Get 300 Units FREE Electricity Every Month',
    hero_subheadline: 'Government of India scheme — up to ₹78,000 subsidy for rooftop solar installation. We guide you end-to-end, completely free.',
    hero_video: null,
    hero_stats_json: null,
    how_it_works_json: null,
    why_choose_us_json: null,
    calculator_headline: null,
    calculator_subheadline: null,
    calculator_values_json: null,
    eligibility_headline: null,
    eligibility_subheadline: null,
    eligibility_questions_json: null,
    eligibility_success_title: null,
    eligibility_success_desc: null,
    eligibility_error_title: null,
    eligibility_error_desc: null,
    footer_about_text: null,
    footer_copyright: null,
    footer_disclaimer: null,

    nav_home: 'Home',
    nav_rewards: 'Rewards',
    nav_portal_login: 'Portal Login',
    nav_cta_electricity: 'Get Free Electricity',

    footer_section_quick_links: 'Quick Links',
    footer_section_legal: 'Legal & Support',
    footer_link_about: 'About Us',
    footer_link_scheme: 'PM Surya Ghar Scheme',
    footer_link_contact: 'Contact',
    footer_link_faq: 'FAQ',
    footer_link_privacy: 'Privacy Policy',
    footer_link_terms: 'Terms & Conditions',
    footer_link_refund: 'Refund Policy',

    label_how_it_works: 'How It Works',
    label_eligibility_checker: 'Eligibility Checker',
    label_subsidy_calculator: 'Subsidy Calculator',
    label_become_executive: 'Become a Biz Dev Executive',
    label_executive_login: 'Biz Dev Executive Login',
    label_apply_title: 'Apply for Free Solar Electricity',
    label_apply_desc: 'Fill the form below and our team will call you within 24 hours',
    label_whatsapp_text: 'WhatsApp Us',
    external_pmsuryaghar_label: 'pmsuryaghar.gov.in',
    external_pmsuryaghar_url: 'https://pmsuryaghar.gov.in',
};

export function useSettings() {
    const { data: settings = DEFAULT_SETTINGS, isLoading } = useQuery({
        queryKey: ['public-settings'],
        queryFn: publicApi.getSettings,
        staleTime: 1000 * 60 * 10, // 10 minutes
    });

    return {
        settings,
        isLoading,
        companyName: settings.company_name || 'SuryaMitra',
    };
}
