import { User } from './user.types';
import { Offer } from './offer.types';
export type CommissionStatus = 'pending' | 'approved' | 'paid';

export type CommissionPayeeRole = 'super_agent' | 'agent' | 'enumerator';
export type CommissionPaymentStatus = 'unpaid' | 'paid';
export type CommissionPaymentMethod = 'bank_transfer' | 'upi' | 'cash' | 'cheque';
export type LeadCommissionEntryStatus = 'none' | 'super_agent_entered' | 'agent_entered' | 'both_entered';
export interface Commission {
    id: number;
    lead_id: number;
    payee_role: CommissionPayeeRole;
    payee_id: number;
    amount: number;
    payment_status: CommissionPaymentStatus;
    paid_at: string | null;
    paid_by: number | null;
    paid_by_name: string | null;
    payment_method: CommissionPaymentMethod | null;
    payment_reference: string | null;
    payment_notes: string | null;
    is_locked: boolean;
    is_editable: boolean;
    created_at: string;

    payee: {
        id: number;
        name: string;
        code: string;
        mobile?: string;
    };

    entered_by: number;
    entered_by_name: string;

    lead?: {
        ulid: string;
        beneficiary_name: string;
        beneficiary_district: string;
    };
}

export interface LeadCommissions {
    super_agent_commission: Commission | null;
    agent_commission: Commission | null;
}

export interface CommissionPrompt {
    should_prompt: boolean;
    payee_role?: CommissionPayeeRole;
    payee_id?: number;
    payee_name?: string;
    payee_code?: string;
    payee_type_label?: string;
    payer_id?: number;
    payer_role?: string;
    suggested_amount?: number;
    existing_commission?: Commission | null;
}

export interface EnterCommissionPayload {
    amount: number;
}

export interface MarkCommissionPaidPayload {
    payment_method: CommissionPaymentMethod;
    payment_reference: string;
    payment_notes?: string;
}

export interface AdminCommissionSummary {
    super_agent_unpaid_count: number;
    super_agent_unpaid_amount: number;
    super_agent_paid_amount: number;
    direct_agent_unpaid_count: number;
    direct_agent_unpaid_amount: number;
    all_time_disbursed: number;
}

export interface SuperAgentCommissionSummary {
    my_earnings_unpaid: number;
    my_earnings_paid: number;
    agent_payouts_unpaid_count: number;
    agent_payouts_unpaid: number;
    agent_payouts_paid: number;
}

export interface AgentCommissionSummary {
    my_earnings_total: number;
    my_earnings_unpaid: number;
    my_earnings_paid: number;
    my_earnings_this_month: number;
    enumerator_payouts_total: number;
    enumerator_payouts_unpaid: number;
    enumerator_payouts_paid: number;
}
export interface CommissionSlab {
    id: number;
    capacity: string;
    label: string;
    agent_commission: number;
    super_agent_override: number;
    enumerator_commission: number;
    description: string | null;
    is_active: boolean;
    super_agent_id: number | null;
    is_custom?: boolean;
    created_at: string;
    updated_at: string;
}

export interface WithdrawalRequest {
    id: number;
    user_id: number;
    offer_id?: number;
    points_withdrawn: number;
    amount: number | null;
    status: 'pending' | 'approved' | 'rejected' | 'paid';
    admin_notes: string | null;
    payment_method: string | null;
    payment_details: string | null;
    created_at: string;
    updated_at: string;
    user?: User;
    offer?: Offer;
}
