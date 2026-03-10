import axios from './axios';
import { ApiResponse, Offer, UserOfferProgress, OfferRedemption, TeamOfferPerformance, OfferParticipant, SuperAgentAbsorbedPoint } from '../types';

export const offersApi = {
    // ==============================
    // ADMIN ENDPOINTS
    // ==============================
    admin: {
        getAll: async () => {
            const { data } = await axios.get<ApiResponse<Offer[]>>('/admin/offers');
            return data;
        },
        getOne: async (id: number) => {
            const { data } = await axios.get<ApiResponse<Offer>>(`/admin/offers/${id}`);
            return data;
        },
        create: async (payload: Partial<Offer>) => {
            const { data } = await axios.post<ApiResponse<Offer>>('/admin/offers', payload);
            return data;
        },
        update: async (id: number, payload: Partial<Offer>) => {
            const { data } = await axios.put<ApiResponse<Offer>>(`/admin/offers/${id}`, payload);
            return data;
        },
        delete: async (id: number) => {
            const { data } = await axios.delete<ApiResponse<void>>(`/admin/offers/${id}`);
            return data;
        },
        getRedemptions: async (status?: string) => {
            const { data } = await axios.get<ApiResponse<OfferRedemption[]>>('/admin/offers/redemptions', {
                params: { status }
            });
            return data;
        },
        approveRedemption: async (id: number, notes?: string) => {
            const { data } = await axios.post<ApiResponse<void>>(`/admin/offers/redemptions/${id}/approve`, { notes });
            return data;
        },
        deliverRedemption: async (id: number, notes?: string) => {
            const { data } = await axios.post<ApiResponse<void>>(`/admin/offers/redemptions/${id}/deliver`, { notes });
            return data;
        },
        getParticipants: async (id: number) => {
            const { data } = await axios.get<ApiResponse<OfferParticipant[]>>(`/admin/offers/${id}/participants`);
            return data;
        },
        getAbsorbedPoints: async () => {
            const { data } = await axios.get<ApiResponse<SuperAgentAbsorbedPoint[]>>('/admin/offers/absorbed-points');
            return data;
        },
        approveAbsorption: async (id: number, notes?: string) => {
            const { data } = await axios.post<ApiResponse<void>>(`/admin/offers/absorbed-points/${id}/approve`, { notes });
            return data;
        },
        triggerExpiry: async (id: number) => {
            const { data } = await axios.post<ApiResponse<any>>(`/admin/offers/${id}/trigger-expiry`);
            return data;
        }
    },

    // ==============================
    // AGENT ENDPOINTS
    // ==============================
    agent: {
        getOffers: async () => {
            const { data } = await axios.get<ApiResponse<UserOfferProgress[]>>('/agent/offers');
            return data;
        },
        redeem: async (id: number) => {
            const { data } = await axios.post<ApiResponse<OfferRedemption>>(`/agent/offers/${id}/redeem`);
            return data;
        },
        getMyRedemptions: async () => {
            const { data } = await axios.get<ApiResponse<OfferRedemption[]>>('/agent/offers/redemptions');
            return data;
        }
    },

    // ==============================
    // SUPER AGENT ENDPOINTS
    // ==============================
    superAgent: {
        getOffers: async () => {
            const { data } = await axios.get<ApiResponse<UserOfferProgress[]>>('/super-agent/offers');
            return data;
        },
        getTeamPerformance: async () => {
            const { data } = await axios.get<ApiResponse<TeamOfferPerformance[]>>('/super-agent/offers/team-performance');
            return data;
        },
        redeem: async (id: number) => {
            const { data } = await axios.post<ApiResponse<OfferRedemption>>(`/super-agent/offers/${id}/redeem`);
            return data;
        },
        getMyRedemptions: async () => {
            const { data } = await axios.get<ApiResponse<OfferRedemption[]>>('/super-agent/offers/redemptions');
            return data;
        },
        getAbsorbedPoints: async () => {
            const { data } = await axios.get<ApiResponse<SuperAgentAbsorbedPoint[]>>('/super-agent/offers/absorbed-points');
            return data;
        },
        claimAbsorbed: async (id: number) => {
            const { data } = await axios.post<ApiResponse<void>>(`/super-agent/offers/absorbed-points/${id}/claim`);
            return data;
        }
    }
};
