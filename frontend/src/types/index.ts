// ============================================================
// SURYAMITRA - TypeScript Interfaces
// ============================================================

export type UserRole = 'admin' | 'super_agent' | 'agent' | 'enumerator';
export type UserStatus = 'active' | 'inactive' | 'pending';
export type OverrideStatus = 'pending' | 'approved' | 'paid';

export type LeadStatus =
    | 'new'
    | 'registered'
    | 'at_bank'
    | 'installed'
    | 'rejected'
    | 'on_hold'
    | 'completed';

export type ExcitementState = 'dormant' | 'building' | 'close' | 'ready';

export type CommissionStatus = 'pending' | 'approved' | 'paid';

export type CommissionPayeeRole = 'super_agent' | 'agent' | 'enumerator';
export type CommissionPaymentStatus = 'unpaid' | 'paid';
export type CommissionPaymentMethod = 'bank_transfer' | 'upi' | 'cash' | 'cheque';
export type LeadCommissionEntryStatus = 'none' | 'super_agent_entered' | 'agent_entered' | 'both_entered';

// ====== User / Business Development Executive ======
export interface User {
    id: number;
    name: string;
    email: string;
    mobile: string;
    role: string;
    status: string;
    agent_id?: string;
    super_agent_code?: string;
    enumerator_id?: string;
    enumerator_creator_role?: 'admin' | 'super_agent' | 'agent';
    district?: string;
    state?: string;  // SSM-YYYY-XXXX (super agents only)
    super_agent_id: number | null;          // FK → super agent (agents only)
    super_agent?: SuperAgentRef;            // populated via eager loading
    managed_states: string[] | null;        // super agents only
    area: string | null;
    whatsapp_number: string | null;
    profile_photo: string | null;
    profile_photo_url: string | null;
    last_login_at: string | null;
    created_at: string;
    updated_at: string;

    // ── Extended Profile Fields ──
    father_name: string | null;
    dob: string | null;
    blood_group: string | null;
    religion: string | null;
    gender: 'male' | 'female' | 'other' | null;
    marital_status: 'single' | 'married' | 'divorced' | 'widowed' | null;

    permanent_address: string | null;
    current_address: string | null;
    pincode: string | null;
    landmark: string | null;

    aadhaar_number: string | null; // Masked in frontend
    pan_number: string | null;    // Masked in frontend
    voter_id: string | null;

    bank_name: string | null;
    bank_account_number: string | null; // Masked in frontend
    bank_ifsc: string | null;
    bank_branch: string | null;
    upi_id: string | null;

    occupation: string | null;
    qualification: string | null;
    experience_years: number | null;
    languages_known: string[] | null;
    reference_name: string | null;
    reference_mobile: string | null;
    territory: string | null;
    target_monthly: number | null;

    // ── Joining Letter ──
    approved_at: string | null;
    approved_by: number | null;
    letter_number: string | null;
    joining_date: string | null;
    signature_image: string | null;
    profile_completion: number;
    languages_known_string?: string; // Frontend helper

    // ── QR Code & Verification (New Phase 30) ──
    qr_token: string | null;
    qr_generated_at: string | null;
    last_verified_at: string | null;
    scan_count: number;

    // Aggregates (populated on some API calls)
    agent_count?: number;
    total_leads?: number;
    created_by?: { name: string; code: string } | null;
}

/** Minimal Business Development Manager reference for nested display */
export interface SuperAgentRef {
    id: number;
    name: string;
    super_agent_code: string;
    mobile: string;
    whatsapp_number: string | null;
}

/** Team assignment audit log entry */
export interface TeamAssignment {
    id: number;
    super_agent_id: number;
    agent_id: number;
    assigned_by: number;
    assigned_at: string;
    unassigned_at: string | null;
    notes: string | null;
    agent?: User;
    super_agent?: User;
    assignedBy?: User;
}

export interface AuthState {
    token: string | null;
    user: User | null;
    role: UserRole | null;
}

// ====== Lead Pipeline Types ======
export type LeadSource =
    | 'public_form'
    | 'agent_submission'
    | 'super_agent_submission'
    | 'admin_manual';

export type LeadOwnerType = 'admin_pool' | 'super_agent_pool' | 'agent_pool';

export type LeadVerificationStatus =
    | 'not_required'
    | 'pending_agent_verification'
    | 'pending_super_agent_verification'
    | 'super_agent_verified'
    | 'reverted_to_enumerator'
    | 'reverted_to_agent'
    | 'admin_override';

export type LeadVerificationAction = 'verified' | 'reverted';

export interface LeadVerification {
    id: number;
    lead_id: number;
    action: LeadVerificationAction;
    performed_by: number;
    performed_by_name?: string;
    performedBy?: { id: number; name: string; role: string; agent_id: string | null; super_agent_code: string | null };
    performer_role: 'super_agent' | 'admin';
    reason: string | null;
    revert_count_at_time: number;
    created_at: string;
}

// ====== Lead ======
export interface Lead {
    id: number;
    ulid: string;
    source: LeadSource;
    owner_type: LeadOwnerType;
    verification_status: LeadVerificationStatus;
    revert_count: number;
    revert_reason: string | null;
    assigned_agent_id: number | null;
    assigned_super_agent_id: number | null;
    submitted_by_agent_id: number | null;
    submitted_by_enumerator_id: number | null;
    created_by_super_agent_id: number | null;
    verified_by_super_agent_id: number | null;
    verified_at: string | null;
    reverted_at: string | null;
    assigned_agent?: User;
    assigned_super_agent?: User;
    submitted_by_agent?: User;
    submitted_by_enumerator?: User;
    created_by_super_agent?: User;
    verifications?: LeadVerification[];

    beneficiary_name: string;
    beneficiary_mobile: string;
    beneficiary_email: string | null;
    beneficiary_state: string;
    beneficiary_district: string;
    beneficiary_address: string | null;
    beneficiary_pincode: string | null;

    consumer_number: string | null;
    discom_name: string | null;
    roof_size: 'less_100' | '100_200' | '200_300' | '300_plus' | null;
    system_capacity: '3kw' | '3.3kw' | '4kw' | '5kw' | '5.5kw' | '6kw' | '7kw' | '8kw' | '9kw' | '10kw' | 'above_10kw' | null;
    monthly_bill_amount: number | null;
    referral_agent_id: string | null;

    status: LeadStatus;
    query_message: string | null;
    admin_notes: string | null;
    follow_up_date: string | null;
    govt_application_number: string | null;
    rejection_reason: string | null;

    commission_entry_status: LeadCommissionEntryStatus;
    commission_prompt?: CommissionPrompt;
    commission_status?: {
        prompts: CommissionPrompt[];
    };
    formatted_commissions?: {
        super_agent_commission: Commission | null;
        agent_commission: Commission | null;
        enumerator_commission: Commission | null;
        all?: Commission[];
    };
    commissions?: Commission[];

    status_logs?: LeadStatusLog[];
    documents?: LeadDocument[];

    created_at: string;
    updated_at: string;
}

// ====== Lead Status Log ======
export interface LeadStatusLog {
    id: number;
    lead_id: number;
    changed_by: number;
    from_status: LeadStatus;
    to_status: LeadStatus;
    notes: string | null;
    changedBy?: User;
    created_at: string;
}

// ====== Lead Document ======
export interface LeadDocument {
    id: number;
    lead_id: number;
    document_type: string;
    file_path: string;
    download_url: string;
    original_filename: string;
    uploaded_by: number;
    uploadedBy?: User;
    created_at: string;
}

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

// ====== Notification ======
export interface Notification {
    id: number;
    user_id: number;
    type: string;
    title: string;
    message: string;
    data: Record<string, unknown> | null;
    read_at: string | null;
    created_at: string;
}

// ====== Settings ======
export interface Setting {
    id: number;
    key: string;
    value: string | null;
    group: string | null;
}

// ====== API Response Wrapper ======
export interface ApiResponse<T> {
    success: boolean;
    message?: string;
    data: T;
    errors?: Record<string, string[]>;
}

export interface PaginatedResponse<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

// ====== Dashboard Stats ======
export interface AdminDashboardStats {
    kpis: {
        total_leads: number;
        new_leads_today: number;
        leads_this_month: number;
        total_installations: number;
        installations_this_month: number;
        active_agents: number;
        pending_agents: number;
        total_commission_paid: number;
        pending_commission: number;
        // New super agent KPIs
        active_super_agents: number;
        unassigned_agents_count: number;
    };
    pipeline: Record<LeadStatus, number>;
    recent_leads: Lead[];
    pending_approvals: User[];
    unassigned_agents?: User[];
}

export interface AgentDashboardStats {
    total_leads: number;
    leads_installed: number;
    pending_commission: number;
    total_earned: number;
    this_month_earned: number;
    last_month_earned: number;
    recent_leads: Lead[];
}

// ====== Business Development Manager Dashboard Stats ======
export interface SuperAgentDashboardStats {
    team: {
        total_agents: number;
        active_agents: number;
        pending_agents: number;
    };
    leads: {
        total: number;
        new: number;
        in_progress: number;
        installed: number;
        completed: number;
        rejected: number;
    };
    commissions: {
        override_pending: number;
        override_paid: number;
        override_this_month: number;
        agent_pending: number;
        agent_paid: number;
    };
    trends: {
        leads_this_month: number;
        leads_last_month: number;
        installs_this_month: number;
        installs_last_month: number;
    };
}
// ====== Offers & Incentives (v2) ======
export type OfferType = 'individual' | 'collective';
export type OfferStatus = 'active' | 'paused' | 'ended';
export type RedemptionStatus = 'pending' | 'approved' | 'delivered' | 'cancelled';

export interface Offer {
    id: number;
    title: string;
    description: string | null;
    prize_label: string;
    prize_amount: number | null;
    prize_image_url: string | null;
    prize_image_path?: string | null;
    offer_from: string;
    offer_to: string;
    target_points: number;
    offer_type: OfferType;
    visible_to: 'agents' | 'super_agents' | 'both';
    status: OfferStatus;
    is_featured: boolean;
    display_order: number;
    created_at: string;
    updated_at: string;

    // v3 Fields
    grace_period_days: number;
    is_annual: boolean;
    absorption_processed_at: string | null;

    // Computed/Appended
    is_live: boolean;
    is_claimable: boolean;
    grace_period_ends_at: string | null;
    days_remaining: number;
    collective_remaining?: number;
    collective_redeemed?: boolean;
    redemptions_count?: number; // admin view
}

export interface UserOfferProgress {
    // Basic offer data
    id: number;
    title: string;
    description: string | null;
    prize_label: string;
    prize_amount: number | null;
    prize_image_url: string | null;
    offer_from: string;
    offer_to: string;
    offer_type: OfferType;
    target_points: number;
    days_remaining: number;
    is_featured: boolean;
    is_annual: boolean;
    is_claimable: boolean;
    offer_ended_zeroed_at: string | null;

    // Collective data
    current_points?: number;
    collective_remaining?: number;
    collective_redeemed?: boolean;

    // Personal data (Individual)
    my_total_points: number;
    my_redeemed_points: number;
    my_unredeemed_points: number;
    my_redemption_count: number;
    can_redeem: boolean;
    pending_redemption_count: number;

    // Cycle progress
    cycle_points: number;
    cycle_needed: number;
    cycle_percentage: number;
}

export interface SuperAgentAbsorbedPoint {
    id: number;
    super_agent_id: number;
    source_agent_id: number;
    offer_id: number;
    absorbed_points: number;
    agent_total_points: number;
    offer_target: number;
    absorption_reason: 'agent_fell_short' | 'grace_period_expired';
    absorbed_at: string;
    status: 'unclaimed' | 'claimed' | 'delivered';
    claimed_by: number | null;
    claimed_at: string | null;
    delivered_at: string | null;
    approved_by: number | null;
    admin_notes: string | null;

    // Relations
    offer: {
        id: number;
        title: string;
        offer_from: string;
        offer_to: string;
        target_points: number;
        prize_label: string;
        prize_image_path: string | null;
        visible_to: 'agents' | 'super_agents' | 'both';
    };
    source_agent: User;
    super_agent?: User;
}

export interface OfferParticipant {
    user_id: number;
    name: string;
    agent_id: string; // The code
    role: 'agent' | 'super_agent';
    total_points: number;
    unredeemed: number;
    redemptions: number;
    last_activity: string | null;
}

export interface OfferRedemption {
    id: number;
    offer_id: number;
    user_id: number;
    redemption_number: number;
    points_used: number;
    status: RedemptionStatus;
    notes: string | null;
    approved_by: number | null;
    approved_at: string | null;
    delivered_at: string | null;
    claimed_at: string;
    created_at: string;

    // Relationships
    offer?: Offer;
    user?: User;
    approved_by_user?: User;
}

export interface TeamOfferPerformance {
    offer_id: number;
    offer_title: string;
    prize_label: string;
    prize_image_url: string | null;
    target_points: number;
    offer_to: string;
    days_remaining: number;
    is_featured: boolean;
    agents: TeamAgentOfferRow[];
    team_totals: {
        total_points: number;
        redeemed_points: number;
        unredeemed_points: number;
        redemption_count: number;
        agents_who_can_redeem: number;
    };
}

export interface TeamAgentOfferRow {
    agent_id: number;
    agent_name: string;
    agent_code: string | null;
    total_points: number;
    redeemed_points: number;
    unredeemed_points: number;
    redemption_count: number;
    can_redeem: boolean;
    cycle_points: number;
    cycle_needed: number;
    last_installation_at: string | null;
}

// ====== Offer Excitement Helpers ======

/**
 * Maps the UserOfferProgress to the simplified OfferProgress used by excitement components.
 * This handles the 'my_' prefix mapping.
 */
export interface OfferProgress {
    total_points: number;
    unredeemed_points: number;
    redemption_count: number;
    can_redeem: boolean;
}

export interface OfferWithProgress extends Offer {
    progress?: UserOfferProgress;
    own_progress?: UserOfferProgress;
}

/**
 * Computes the excitement state for a single offer+progress pair.
 * Uses CYCLE progress (unredeemed % target), not lifetime total.
 */
export function getExcitementState(
    progress: OfferProgress | UserOfferProgress | null | undefined,
    targetPoints: number
): ExcitementState {
    if (!progress) return 'dormant';

    // Support both OfferProgress and UserOfferProgress field naming
    const total = (progress as UserOfferProgress).my_total_points ?? (progress as OfferProgress).total_points;
    if (total === 0) return 'dormant';

    if (progress.can_redeem) return 'ready';

    const unredeemed = (progress as UserOfferProgress).my_unredeemed_points ?? (progress as OfferProgress).unredeemed_points;
    const cyclePoints = unredeemed % targetPoints;
    const pct = targetPoints > 0
        ? (cyclePoints / targetPoints) * 100
        : 0;

    if (pct >= 90) return 'ready';
    if (pct >= 60) return 'close';
    if (pct >= 30) return 'building';
    return 'dormant';
}

/**
 * Urgency score 0–100 for determining whether to show the banner.
 */
export function computeUrgencyScore(
    offer: Offer,
    progress: OfferProgress | UserOfferProgress | null | undefined
): number {
    if (!progress) return 0;
    if (progress.can_redeem) return 100;

    const target = offer.target_points;
    const unredeemed = (progress as UserOfferProgress).my_unredeemed_points ?? (progress as OfferProgress).unredeemed_points;
    const cyclePoints = unredeemed % target;
    const pct = target > 0 ? (cyclePoints / target) * 100 : 0;

    const daysBonus =
        offer.days_remaining <= 2 ? 35 :
            offer.days_remaining <= 5 ? 20 :
                offer.days_remaining <= 7 ? 12 :
                    offer.days_remaining <= 14 ? 5 : 0;

    return Math.min(100, Math.round(pct + daysBonus));
}

export interface CommissionSlab {
    id: number;
    capacity: string;
    label: string;
    agent_commission: number;
    super_agent_override: number;
    description: string | null;
    is_active: boolean;
    super_agent_id: number | null;
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
