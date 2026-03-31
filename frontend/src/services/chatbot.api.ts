import axiosInstance from './axios';

export interface WaChatbotCategory {
    id: number; 
    name: string; 
    description: string|null;
    icon_emoji: string|null; 
    sort_order: number; 
    is_active: boolean;
}

export interface WaChatbotSession {
    id: number; 
    wa_phone: string; 
    state: string; 
    last_message_at: string;
    context: any;
}

export interface WaRegistrationField {
    key: string; 
    label: string; 
    required: boolean; 
    order: number; 
    type: string;
}

export interface WaHandler {
    id: number; 
    name: string; 
    email: string;
    is_wa_lead_handler: boolean; 
    wa_lead_round_robin_counter: number;
}

export const chatbotApi = {
    // Categories
    getCategories:  () => axiosInstance.get<WaChatbotCategory[]>('/admin/chatbot/categories'),
    createCategory: (data: Partial<WaChatbotCategory>) => axiosInstance.post<WaChatbotCategory>('/admin/chatbot/categories', data),
    updateCategory: (id: number, data: Partial<WaChatbotCategory>) => axiosInstance.put<WaChatbotCategory>(`/admin/chatbot/categories/${id}`, data),
    deleteCategory: (id: number) => axiosInstance.delete(`/admin/chatbot/categories/${id}`),
    toggleCategory: (id: number) => axiosInstance.patch<WaChatbotCategory>(`/admin/chatbot/categories/${id}/toggle`),

    // Registration fields
    getRegistrationFields: () => axiosInstance.get<WaRegistrationField[]>('/admin/chatbot/registration-fields'),
    setRegistrationFields: (fields: WaRegistrationField[]) => axiosInstance.put('/admin/chatbot/registration-fields', { fields }),

    // Sessions & contacts
    getSessions: (page=1) => axiosInstance.get<{ data: WaChatbotSession[], current_page: number, last_page: number, total: number }>('/admin/chatbot/sessions', { params: { page } }),
    getContacts: () => axiosInstance.get('/admin/chatbot/contacts'),

    // WA Handlers (super-admin only)
    getWaHandlers:    () => axiosInstance.get<WaHandler[]>('/super-admin/chatbot/wa-handlers'),
    toggleWaHandler:  (id: number) => axiosInstance.post(`/super-admin/chatbot/wa-handlers/${id}/toggle`),
    resetCounters:    () => axiosInstance.post('/super-admin/chatbot/wa-handlers/reset-counters'),
};
