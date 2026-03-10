import api from './axios';
import type { User, ApiResponse } from '@/types';

export const authApi = {
    login: async (data: { identifier: string; password: string }) => {
        const res = await api.post<ApiResponse<{ token: string; user: User; requires_password_set: boolean }>>('/auth/login', data);
        return res.data;
    },

    logout: async () => {
        const res = await api.post<ApiResponse<null>>('/auth/logout');
        return res.data;
    },

    me: async () => {
        const res = await api.get<ApiResponse<User>>('/auth/me');
        return res.data;
    },

    setPassword: async (data: { password: string; password_confirmation: string }) => {
        const res = await api.post<ApiResponse<null>>('/auth/set-password', data);
        return res.data;
    },
    uploadProfilePhoto: async (file: File) => {
        const formData = new FormData();
        formData.append('photo', file);
        const res = await api.post<ApiResponse<User>>('/auth/profile-photo', formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });
        return res.data;
    },
    changePassword: async (data: { current_password?: string; password: string; password_confirmation: string }) => {
        const res = await api.put<ApiResponse<null>>('/profile/change-password', data);
        return res.data;
    },
};
