import axios from '../api/axios';
import { ApiResponse } from '../types';

export const joiningLetterApi = {
    /**
     * Get a temporary signed URL for downloading the joining letter
     */
    getDownloadUrl: async (): Promise<string> => {
        const response = await axios.get<ApiResponse<{ download_url: string }>>('/joining-letter/download-url');
        return response.data.data.download_url;
    },

    /**
     * For Admin: Get preview URL for a specific agent/super agent
     */
    getAdminPreviewUrl: (userId: number): string => {
        // Direct web route for admin joining letter preview
        return `/admin/joining-letter/${userId}/preview`;
    }
};
