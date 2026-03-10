import axiosInstance from './axios';
import type {
    Commission, EnterCommissionPayload, MarkCommissionPaidPayload,
    AdminCommissionSummary, SuperAgentCommissionSummary, AgentCommissionSummary,
    LeadCommissions, PaginatedResponse
} from '../types';

// ── ADMIN ──────────────────────────────────────────────────────────
export const adminCommissionsApi = {
    getAll: (params?: Record<string, unknown>) =>
        axiosInstance.get<{ data: Commission[]; meta: PaginatedResponse<Commission> }>('/admin/commissions', { params }),

    getSummary: () =>
        axiosInstance.get<{ data: AdminCommissionSummary }>('/admin/commissions/summary'),

    enterSuperAgentCommission: (leadUlid: string, payload: EnterCommissionPayload) =>
        axiosInstance.post(`/admin/leads/${leadUlid}/commission/super-agent`, payload),

    enterDirectAgentCommission: (leadUlid: string, payload: EnterCommissionPayload) =>
        axiosInstance.post(`/admin/leads/${leadUlid}/commission/agent-direct`, payload),

    updateCommission: (id: number, payload: EnterCommissionPayload) =>
        axiosInstance.put(`/admin/commissions/${id}`, payload),

    markPaid: (id: number, payload: MarkCommissionPaidPayload) =>
        axiosInstance.put(`/admin/commissions/${id}/mark-paid`, payload),

    getLeadCommissions: (leadUlid: string) =>
        axiosInstance.get<{ data: LeadCommissions }>(`/admin/leads/${leadUlid}/commissions`),
};

// ── SUPER AGENT ────────────────────────────────────────────────────
export const superAgentCommissionsApi = {
    getAll: (params?: Record<string, unknown>) =>
        axiosInstance.get<{ data: Commission[]; meta: PaginatedResponse<Commission> }>('/super-agent/commissions', { params }),

    getSummary: () =>
        axiosInstance.get<{ data: SuperAgentCommissionSummary }>('/super-agent/commissions/summary'),

    enterAgentCommission: (leadUlid: string, payload: EnterCommissionPayload) =>
        axiosInstance.post(`/super-agent/leads/${leadUlid}/commission/agent`, payload),

    updateCommission: (id: number, payload: EnterCommissionPayload) =>
        axiosInstance.put(`/super-agent/commissions/${id}`, payload),

    markPaid: (id: number, payload: MarkCommissionPaidPayload) =>
        axiosInstance.put(`/super-agent/commissions/${id}/mark-paid`, payload),

    getLeadCommissions: (leadUlid: string) =>
        axiosInstance.get<{ data: LeadCommissions }>(`/super-agent/leads/${leadUlid}/commissions`),
};

// ── AGENT (read-only) ──────────────────────────────────────────────
export const agentCommissionsApi = {
    getAll: (params?: Record<string, unknown>) =>
        axiosInstance.get<{ data: Commission[]; meta: PaginatedResponse<Commission> }>('/agent/commissions', { params }),

    getSummary: () =>
        axiosInstance.get<{ data: AgentCommissionSummary }>('/agent/commissions/summary'),
};
