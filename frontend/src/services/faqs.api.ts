import axiosInstance from "./axios";

export interface FAQ {
  id: number;
  question: string;
  answer: string;
  category: string | null;
  sort_order: number;
  is_published: boolean;
  created_at?: string;
  updated_at?: string;
}

export const faqApi = {
  getFaqs: async () => {
    const response = await axiosInstance.get('/admin/faqs');
    return response.data;
  },

  createFaq: async (data: Partial<FAQ>) => {
    const response = await axiosInstance.post('/admin/faqs', data);
    return response.data;
  },

  updateFaq: async (id: number, data: Partial<FAQ>) => {
    const response = await axiosInstance.put(`/admin/faqs/${id}`, data);
    return response.data;
  },

  deleteFaq: async (id: number) => {
    const response = await axiosInstance.delete(`/admin/faqs/${id}`);
    return response.data;
  },

  toggleStatus: async (id: number) => {
    const response = await axiosInstance.patch(`/admin/faqs/${id}/toggle-status`);
    return response.data;
  }
};
