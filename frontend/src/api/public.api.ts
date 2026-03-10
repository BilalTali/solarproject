import api from './axios';
import type { ApiResponse } from '@/types';

export interface PublicSettingsData {
    company_name: string | null;
    company_email: string | null;
    company_mobile: string | null;
    company_whatsapp: string | null;
    company_address: string | null;
    company_slogan: string | null;
    company_logo: string | null;
    company_favicon: string | null;
    company_signature: string | null;
    company_website: string | null;
    social_facebook: string | null;
    social_twitter: string | null;
    social_instagram: string | null;
    social_youtube: string | null;
    hero_headline: string | null;
    hero_subheadline: string | null;

    hero_video: string | null;
    hero_stats_json: string | null;
    how_it_works_json: string | null;
    why_choose_us_json: string | null;
    calculator_headline: string | null;
    calculator_subheadline: string | null;
    calculator_values_json: string | null;
    eligibility_headline: string | null;
    eligibility_subheadline: string | null;
    eligibility_questions_json: string | null;
    eligibility_success_title: string | null;
    eligibility_success_desc: string | null;
    eligibility_error_title: string | null;
    eligibility_error_desc: string | null;
    footer_about_text: string | null;
    footer_copyright: string | null;
    footer_disclaimer: string | null;
}

export interface PublicAchievement {
    id: number;
    title: string;
    description: string | null;
    image_url: string | null;
    date: string | null;
}

export interface PublicFeedback {
    id: number;
    name: string;
    message: string;
    rating: number;
    admin_reply: string | null;
    date: string;
}

export interface PublicMedia {
    id: number;
    title: string;
    description: string | null;
    image_url: string | null;
    date: string | null;
}

export const publicApi = {
    getSettings: async (): Promise<PublicSettingsData> => {
        const res = await api.get<ApiResponse<PublicSettingsData>>('/public/settings');
        return res.data.data as PublicSettingsData;
    },
    getAchievements: async (): Promise<PublicAchievement[]> => {
        const res = await api.get<ApiResponse<PublicAchievement[]>>('/public/achievements');
        return res.data.data ?? [];
    },
    getFeedbacks: async (): Promise<PublicFeedback[]> => {
        const res = await api.get<ApiResponse<PublicFeedback[]>>('/public/feedbacks');
        return res.data.data ?? [];
    },
    getMedia: async (): Promise<PublicMedia[]> => {
        const res = await api.get<ApiResponse<PublicMedia[]>>('/public/media');
        return res.data.data ?? [];
    },
    submitFeedback: async (data: { name: string; email?: string; phone?: string; message: string; rating: number }) => {
        const res = await api.post<ApiResponse<{ id: number }>>('/public/feedback', data);
        return res.data;
    },
};
