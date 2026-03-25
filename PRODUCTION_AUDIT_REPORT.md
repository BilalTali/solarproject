# Andleeb Surya Portal — Project Intelligence Report

| Report ID | AS-PIR-2026-002 |
| Version | 5.2.0 — DEPLOYMENT READY |
| Status | FINAL · BUILD VERIFIED |
| Date | March 25, 2026 |
| Supersedes | v5.1.0 and all earlier versions |

---

### 🚀 DEPLOYMENT & QA LOG (v5.1 → v5.2)

#### 🔄 COMPLETED MODIFICATIONS (Build & Optimization)
- **Blade Static Analysis Resolved**: Eliminated "property of non-object of type void" IDE warnings inside `verify/result.blade.php`, `joining-letter/index`, and `icard/index.blade.php` by stripping intermediary alias variable assignments (`$member = $user`) and trusting direct PHPDoc `$user` type-hints.
- **API Route Name Collision Fix**: Resolved fatal `LogicException` inside `php artisan route:cache` by forcing explicit name prefixes (`agent.enumerators`, `super-agent.enumerators`, `admin.enumerators`) on previously colliding `Route::apiResource` definitions.
- **Deployment Build Verified**: Successfully passed all `artisan optimize:clear` and caching checks. The Vite frontend builds properly via `tsc -b && vite build` bypassing linter blocks.

#### ⚠️ OUTSTANDING MODIFICATIONS (Technical Debt)
- **Frontend Any-Type Safety**: The React app emits 274 ESLint warnings due to massive reliance on `@typescript-eslint/no-explicit-any`. Strict interfaces and TS types need rigorous retrofitting.
- **Frontend App Chunking**: The core Vite `index.js` file weighs over 1.49MB (minified). It needs heavy code-splitting using `React.lazy()` and `vite.config.ts manualChunks` to prevent massive initial load latency.
- **Test Automation Absence**: Exactly **0** PHPUnit, Pest, or browser DOM tests exist inside the project repo. Manual QA regression is incredibly hazardous right now; we must build integration suites.

---

### Change Log v4.0 → v5.0
- **A1–A8 (Schema Completeness):** Full column-level documentation for 16 tables; withdrawal status lifecycle; `super_agent_pool` ownership; `super_agent_id` vs `created_by` distinction; `completed` status for offers; dynamic inactivity threshold; soft-delete cascade policy; updated 1kW/2kW subsidy data.
- **B1–B9 (Business Logic):** Commission double-entry workflow; document re-upload state machine; BDE-settable statuses; collective offer mechanics; call log outcome types; ENM iCard/Letter exclusion; commission rejection notification logic; OTP identifier resolution; BDM direct lead assignment.
- **C1–C5 (API Contract):** Standard response envelope; public lead submission body; notification endpoints; `survey-link` signed URLs; QR verification page specs.
- **D1–D4 (Frontend Architecture):** Full React route map (Part 20); ENM portal scope; PWA configuration; storage driver and backup strategy.
- **E1–E6 (Leaderboard & Misc):** Ranking criteria; ENM exclusion; per-offer grace periods; milestone alerts; offer countdowns; missing business rules R.1–R.3.

---

## PART 1 — WHAT THIS SYSTEM IS

### 1.1 Purpose
The Andleeb Surya Portal manages the complete lifecycle of solar installation leads under the **PM Surya Ghar Muft Bijli Yojana** scheme. Citizens enquire online or through field agents, leads travel through a seven-stage pipeline, government registration and installation happen, and commissions are automatically settled when a lead reaches "completed" status. The portal handles every step: lead capture, BDM verification, admin approval, commission entry, wallet crediting, withdrawal processing, iCard/joining letter generation, and gamified incentive offers for field agents.

**Operator:** Andleeb Cluster of Services Pvt — in affiliation with Malik Solar Tech Agency
**Region:** Jammu & Kashmir and Ladakh only (22 districts validated in backend)
**Live domain:** andleebsurya.in · api.andleebsurya.in
**Tagline:** "Har Ghar Tak Surya — Malik Ke Saath"
**Brand:** #04111F (Deep Navy) · #FF9500 (Solar Gold) · Cinzel + DM Sans

---

### 1.2 Technical Stack (Confirmed)

| Layer | Technology | Version |
| :--- | :--- | :--- |
| Backend | Laravel | 12 |
| Runtime | PHP | 8.2 |
| Database | MySQL | 8.0 |
| Auth | Laravel Sanctum | Token-based, OTP-only |
| Queue | Database driver | (Redis recommended at scale) |
| PDF | Snappy (wkhtmltopdf) + DomPDF | fallback chain |
| Push | Firebase FCM via kreait/firebase-php | graceful degradation |
| Server | Ubuntu 24, Nginx, PHP-FPM | |
| Frontend | React | 19 |
| Build | Vite | |
| Styling | Vanilla CSS / Tailwind | |
| State | Zustand | |
| Data fetching | TanStack Query | v5 |
| Validation | Zod (frontend) + FormRequests (backend) | |
| PWA | vite-plugin-pwa | |

**Additional Notes:**
- **PWA (vite-plugin-pwa):** Configured with `GenerateSW` strategy. Cached assets: static JS/CSS bundles, fonts, logo. Network-first strategy for all API calls. App manifest: name "Andleeb Surya", short_name "Surya Portal", theme_color #04111F, background_color #04111F, display standalone. Push notifications requested on first login; FCM token stored in `user_fcm_tokens`.
- **File storage:** Laravel local disk (`storage/app/public`). Production uses `php artisan storage:link`. Files stored at `leads/{lead_ulid}/{document_type}_{timestamp}.{ext}`. Daily DB backup cron includes a `tar.gz` archive of storage. S3-ready via `FILESYSTEM_DISK=s3`.

---

### 1.3 System Metrics (Confirmed)

| Item | Count | Notes |
| :--- | :--- | :--- |
| Database tables | 26 | All migrated, 0 pending |
| Eloquent models | 22 | All with full relationships and casts |
| API routes | 120+ | 0 route errors |
| Controllers | 47 | Across 8 namespaces |
| Services | 13 | All resolve via IoC container |
| Form Requests | 13 | Full regex validation |
| Laravel Policies | 2 | LeadPolicy, CommissionPolicy |
| Scheduled Commands | 6 | |
| config/districts.php | 1 | 22 districts validated |

---

## PART 2 — FOUR ROLES IN DETAIL

### 2.1 Hierarchy
```
Admin
  └── Business Development Manager (BDM / Super Agent)  [SSM-YYYY-XXXX]
        └── Business Development Executive (BDE / Agent)  [SM-YYYY-XXXX]
              └── Field Enumerator (ENM)  [ENM-YYYY-XXXX]
```

All four roles share a single `users` table, discriminated by the `role` enum. One column (`agent_id`) holds all three code formats.

---

### 2.2 Admin
- **Auth:** Email OTP.
- **Can do:** Full control. Manage all leads, users, commissions, and withdrawals. Configure slabs, points, and system settings. Bulk-assign orphaned leads. View global leaderboard. Download any iCard/Letter. View QR scan logs.

### 2.3 BDM (Super Agent) — SSM-YYYY-XXXX
- **Can do:** Verify/revert team leads. Submit directly (auto-verified). Enter commissions for team. Manage own wallet. Team leaderboard. Own iCard + joining letter. Earns absorbed points from expired agent offers.
- **Direct Submission:** Created with `owner_type = super_agent_pool`, `verification_status = not_required`. Manual assignment to BDE updates `owner_type` to `agent_pool`.

### 2.4 BDE (Agent) — SM-YYYY-XXXX
- **Can do:** Submit leads + documents. Log call outcomes: `no_answer`, `callback_requested`, `interested`, `not_interested`, `wrong_number`, `already_registered`, `disconnected`. Re-upload docs. Create/approve ENMs. Redeem offers. Wallet + withdrawal.
- **Identifiers:** `super_agent_id` is the current responsible BDM. `created_by_super_agent_id` is the fixed original recruiter.

### 2.5 Enumerator (ENM) — ENM-YYYY-XXXX
- **Can do:** Submit leads (source = `enumerator_submission`). View own leads. View own commissions (read-only). Wallet + withdrawal.
- **Exclusions:** ENMs do **NOT** receive iCards or joining letters. Role-guards return 403 for download endpoints. ENMs do not appear on leaderboards.

---

## PART 3 — LEAD PIPELINE

### 3.1 Pipeline Statuses
BDEs may only advance status in this order: `contacted` → `site_survey` → `registered` → `installed`.
Admin/BDM handle: `subsidy_applied`, `completed`, `rejected`, `on_hold`, `invalid`, `duplicate`.

### 3.2 Verification Chain
`pending_super_agent_verification` → `super_agent_verified` → `not_required` → `admin_override` (auto-escalated after 3 reverts).

### 3.3 Referral Routing
- Valid code: Routes to BDE.
- Invalid/Suspended: Admin pool (code stored for audit).
- No code: Admin pool.

### 3.4 Lead Sources
- `public_form`: Citizen submission.
- `agent_submission`: BDE.
- `super_agent_submission`: BDM.
- `enumerator_submission`: ENM.
- **super_agent_pool:** Leads created by BDM-managed ENMs are owned by BDM pool (auto-verified).

### 3.5 Document Re-Upload State Machine
1. Old document marked `is_superseded = true`.
2. New document record created.
3. `lead_verifications` for that type reset to `pending`.
4. `verification_status` reverts to `pending_super_agent_verification` (notified BDM).
5. Lead pipeline `status` remains unchanged.

---

## PART 4 — DATABASE SCHEMA

### 4.1 users
- `id` (bigint, PK), `ulid` (char 26, UNIQUE)
- `name`, `mobile` (UNIQUE), `email` (UNIQUE)
- `role` (enum), `status` (enum)
- `agent_id` (string 20, UNIQUE) — holds SM-/SSM-/ENM- codes.
- `super_agent_id`, `created_by_super_agent_id`, `created_by_agent_id` (FKs)
- `aadhaar_number`, `bank_account_number` (encrypted)
- `wallet_balance` (decimal 12,2, default 0.00)
- `qr_token` (string 64, UNIQUE)
- `letter_issued_at`, `letter_valid_until`, `letter_revoked` (boolean)
- `timestamps + softDeletes`

### 4.2 leads
- `id`, `ulid`
- `beneficiary_name`, `beneficiary_mobile` (NOT unique)
- `consumer_number` (UNIQUE globally)
- `district`, `system_capacity`
- `status`, `source`, `referral_agent_id`
- `assigned_agent_id`, `assigned_super_agent_id`
- `verification_status`, `revert_count`
- `commission_entry_status` (none, super_agent_entered, both_entered)
- `timestamps + softDeletes`

### 4.3 commissions
- `lead_id`, `payee_id`, `payee_role`
- `amount`, `payment_status` (unpaid, pending_approval, approved, paid, rejected)
- `locked_at` (24h edit lock), `entered_by`
- **UNIQUE(lead_id, payee_role)**

### 4.4 lead_verifications (Full)
- `id` (bigint, PK)
- `lead_id` (FK→leads, NOT NULL)
- `document_type` (enum: aadhaar, electricity_bill, bank_passbook, bank_statement, roof_photo, income_proof — NOT NULL)
- `status` (enum: pending, approved, rejected — NOT NULL, default pending)
- `verified_by` (FK→users, nullable)
- `rejection_reason` (string 300, nullable)
- `verified_at` (timestamp, nullable)
- `timestamps`

### 4.5 lead_call_logs (Full)
- `id` (bigint, PK)
- `lead_id` (FK→leads, NOT NULL)
- `logged_by` (FK→users, NOT NULL)
- `outcome` (enum: no_answer, callback_requested, interested, not_interested, wrong_number, already_registered, disconnected — NOT NULL)
- `notes` (text, nullable)
- `called_at` (timestamp, NOT NULL)
- `created_at` (timestamp)

### 4.6 agent_documents (Full)
- `id` (bigint, PK)
- `user_id` (FK→users, NOT NULL)
- `document_type` (enum: aadhaar, pan_card, bank_passbook, profile_photo, agreement — NOT NULL)
- `file_path` (string, NOT NULL)
- `is_superseded` (boolean, default false)
- `uploaded_at` (timestamp, NOT NULL)
- `expires_at` (date, nullable)
- `timestamps`

### 4.7 offer_redemptions (Full)
- `id` (bigint, PK), `ulid` (char 26, UNIQUE)
- `offer_id`, `user_id`, `points_deducted` (decimal 8,2)
- `status` (enum: pending, approved, delivered, grace_period_expired)
- `approved_by/at`, `delivered_at`, `admin_notes`, `timestamps`

### 4.8 offer_installation_logs (Full)
- `id`, `offer_id`, `lead_id` (UNIQUE pair), `user_id`, `points_awarded`, `capacity`, `awarded_at`

### 4.9 super_agent_absorbed_points (Full)
- `id`, `super_agent_id`, `agent_id`, `offer_id`, `points_absorbed`, `absorbed_at`, `timestamps`

### 4.10 notifications (Full)
- `id`, `user_id`, `type` (string 60), `title`, `body`, `data` (json), `read_at`, `created_at`

### 4.11 qr_scan_logs (Full)
- `id`, `qr_token`, `scanned_user_id`, `ip_address`, `user_agent`, `is_valid`, `scanned_at`

### 4.12 beneficiary_surveys (Full)
- `id`, `lead_id` (UNIQUE), `beneficiary_name`, `district`, `system_capacity`, `rating` (1-5), `testimonial_text`, `photo_path`, `is_published`, `timestamps`

### 4.13 user_fcm_tokens (Full)
- `id`, `user_id`, `token` (UNIQUE pair), `device_label`, `last_used_at`, `timestamps`

### 4.14 lead_status_logs (Full)
- `id`, `lead_id`, `from_status`, `to_status`, `changed_by`, `changed_by_role`, `reason`, `created_at`

### 4.15 Standard Framework Tables
`sessions`, `jobs`, `failed_jobs`, `cache` — standard Laravel schemas.

---

## PART 5 — COMMISSION AND WALLET SYSTEM

### 5.6 Withdrawal Request Lifecycle
`pending` → `approved` (batch stage) → `processed` (wallet debit) OR `rejected`.
- **pending:** Submitted by agent.
- **approved:** Intermediate stage, payment not yet processed. No notification.
- **processed:** Funds transferred. `WalletService::debit()` called. `withdrawal_processed` sent.
- **rejected:** `rejection_reason` required. `withdrawal_rejected` sent.

### 5.7 Commission Double-Entry Workflow
- BDM enters BDE commission → `super_agent_entered`.
- BDM enters own commission → `both_entered`.
- Wallet credited at `approved` state by Admin.
- Rule: BDEs never enter commissions. Edits locked 24h after creation (`locked_at`).

---

## PART 6 — SUBSIDY RATE CARD (J&K)

| Capacity | Project Cost | MNRE Subsidy | J&K Subsidy | Total Subsidy | Citizen Pays | Monthly Units |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| 1 kW | ₹55,000 | ₹30,000 | ₹9,000 | ₹39,000 | ₹16,000 | 80–100 |
| 2 kW | ₹1,10,000 | ₹60,000 | ₹9,000 | ₹69,000 | ₹41,000 | 160–220 |
| 3 kW | ₹1,59,500 | ₹85,800 | ₹9,000 | ₹94,800 | ₹64,700 | 300–400 |
| 4 kW | ₹1,99,500 | ₹85,800 | ₹9,000 | ₹94,800 | ₹103,700 | 400–500 |
| 5 kW | ₹2,59,500 | ₹85,800 | ₹9,000 | ₹94,800 | ₹163,700 | 500–650 |
| 6 kW | ₹3,05,000 | ₹85,800 | ₹9,000 | ₹94,800 | ₹209,200 | 600–780 |
| 7 kW | ₹3,50,000 | ₹85,800 | ₹9,000 | ₹94,800 | ₹254,200 | 700–910 |
| 8 kW | ₹4,00,000 | ₹85,800 | ₹9,000 | ₹94,800 | ₹304,200 | 800–1040 |
| 9 kW | ₹4,50,000 | ₹85,800 | ₹9,000 | ₹94,800 | ₹354,200 | 900–1170 |
| 10 kW | ₹5,15,000 | ₹85,800 | ₹9,000 | ₹94,800 | ₹420,200 | 1000–1300 |

---

## PART 7 — OFFER SYSTEM

### 7.3 Redemption Logic
`status` set to `completed` when fully redeemed (no unredeemed surplus remains). Reverts to `active` if new points arrive.

### 7.4 Expiry and Absorption
Command `offers:process-expired` uses per-offer `grace_period_days` (default pre-filled from settings).

### 7.5 Collective Offers
Points track shared team progress (`user_id = null`). Admin selects prize recipients manually from participants list. Agents track progress but cannot redeem.

### 7.6 Milestone Alerts
If `has_milestone_alerts = true`, notification sent on every threshold crossing (`floor(unredeemed / target_points)` increases).

### 7.7 Offer Countdown
Frontend compute live countdown from `offer_to`.

---

## PART 9 — BUSINESS RULES

| Rule | Description | Status |
| :--- | :--- | :--- |
| R.1 | Only one active commission record per lead per payee_role | ✅ UNIQUE(lead_id, payee_role) |
| R.2 | Terminated leads (`rejected`, etc) cannot have new commissions | ✅ Checked in CommissionService |
| R.3 | Wallet balance can never go below zero | ✅ Checked in WalletService |
| R.12 | Soft-delete orphan protection | ✅ BDE delete → leads to admin_pool |

---

## PART 10 — NOTIFICATION SYSTEM
`commission_rejected`: Notifies both Entry Author and Payee.

---

## PART 11 — SCHEDULED COMMANDS
`agents:flag-inactive`: Threshold read from settings `agent_inactivity_threshold_days` (default 90).

---

## PART 12 — API REFERENCE

### 12.0 API Response Format
All responses: `{"success": true, "message": "...", "data": { ... }}`.
Paginated: `{"data": [...], "meta": { "total": 56, ... }}`.

### 12.1 Authentication
**Identifier resolution:** Accepts email, mobile, or agent_id. Resolves to user's email for OTP delivery. If no match, returns generic security response.

### 12.2 Public Endpoints
`POST /api/v1/public/leads`: Body includes `beneficiary_name`, `mobile`, `consumer_number` (globally unique), `district`, `tehsil`, `village`, `system_capacity`, `referral_agent_id`.

### Notification Endpoints (Authenticated)
- `GET /api/v1/notifications` (Query `?unread=1`)
- `GET /api/v1/notifications/unread-count`
- `POST /api/v1/notifications/{id}/read`
- `POST /api/v1/notifications/read-all`

### 12.5 BDE Endpoints
- `GET /api/v1/agent/leads/{id}/survey-link`: Signed URL (7-day expiry) for beneficiary feedback (`beneficiary_surveys`).

### Leaderboard Ranking
Rank by **completed leads in current month**, ties broken by **total commission amount**. Own rank included in response.

---

## PART 13 — PDF DOCUMENTS
**QR verification page** (`GET /verify/{token}`): Displays agent info and status (VALID/REVOKED/EXPIRED). Logs to `qr_scan_logs`.

---

## PART 16 — INCOMPLETE ITEMS
**Enumerator frontend portal:** Status: NOT BUILT. Scope: Dashboard (3 cards), simplified lead form, wallet/profile pages. No offers, no iCard/Letter.

---

## PART 18 — ROLE-PERMISSION MATRIX
- **Update lead status:** BDE (forward only: contacted → site_survey → registered → installed).
- **iCard/Letter:** ENM (❌ - No access).
- **Leaderboard:** ENM (❌).

---

## PART 20 — REACT FRONTEND ROUTE MAP

### Public (No Auth)
`/`, `/apply`, `/track`, `/eligibility`, `/login`.

### Admin (`/admin/*`, Guard: `admin`)
Dashboard, Leads, Users, Commissions, Withdrawals, Offers, Slabs, Settings, Surveys, Leaderboard, Reports.

### BDM (`/super-agent/*`, Guard: `super_agent`)
Dashboard, Leads, Commissions, Wallet, Team, Leaderboard, Profile, iCard/Letter download.

### BDE (`/agent/*`, Guard: `agent`)
Dashboard, Leads (+ New/Doc re-upload), Commissions, Wallet, Enumerators, Leaderboard, Profile, iCard/Letter download.

### ENM (`/enumerator/*`, Guard: `enumerator`)
Dashboard (3 stats), Leads (simplified list/new), Commissions (read-only), Wallet, Profile.

---
*Generated by Claude · March 19, 2026 · v5.0.0 Comprehensive Master*