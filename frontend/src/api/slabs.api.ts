import axios from './axios';
import { ApiResponse, CommissionSlab } from '../types';

export const slabsApi = {
    admin: {
        getAll: async () => {
            const { data } = await axios.get<ApiResponse<CommissionSlab[]>>('/admin/commission-slabs');
            return data;
        },
        create: async (payload: Partial<CommissionSlab>) => {
            const { data } = await axios.post<ApiResponse<CommissionSlab>>('/admin/commission-slabs', payload);
            return data;
        },
        update: async (id: number, payload: Partial<CommissionSlab>) => {
            const { data } = await axios.put<ApiResponse<CommissionSlab>>(`/admin/commission-slabs/${id}`, payload);
            return data;
        },
        delete: async (id: number) => {
            const { data } = await axios.delete<ApiResponse<void>>(`/admin/commission-slabs/${id}`);
            return data;
        }
    }
};
