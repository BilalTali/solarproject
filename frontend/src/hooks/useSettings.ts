import { useQuery } from '@tanstack/react-query';
import { publicApi, type PublicSettingsData } from '@/api/public.api';

const DEFAULT_SETTINGS: PublicSettingsData = {
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
