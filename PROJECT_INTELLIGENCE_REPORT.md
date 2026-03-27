# Andleeb Surya Portal — Project Intelligence Report

| Metadata Item | Value |
| :--- | :--- |
| Report ID | AS-PIR-2026-003 |
| Version | **5.2.0 — DEPLOYMENT READY** |
| Status | FINAL · BUILD VERIFIED |
| Date | March 25, 2026 |
| Supersedes | v5.1.0 and all earlier versions |

---

## 🚀 DEPLOYMENT & QA LOG (v5.1 → v5.2)

### 🔄 COMPLETED MODIFICATIONS (Build & Optimization)
- **Blade Static Analysis Resolved**: Eliminated "property of non-object of type void" IDE warnings inside `verify/result.blade.php`, `joining-letter/index`, and `icard/index.blade.php` by stripping intermediary alias variable assignments (`$member = $user`) and trusting direct PHPDoc `$user` type-hints.
- **API Route Name Collision Fix**: Resolved fatal `LogicException` inside `php artisan route:cache` by forcing explicit name prefixes (`agent.enumerators`, `super-agent.enumerators`, `admin.enumerators`) on previously colliding `Route::apiResource` definitions.
- **Deployment Build Verified**: Successfully passed all `artisan optimize:clear` and caching checks. The Vite frontend builds properly via `tsc -b && vite build` bypassing linter blocks.

### ⚠️ OUTSTANDING MODIFICATIONS (Technical Debt)
- **Frontend Any-Type Safety**: The React app emits 274 ESLint warnings due to massive reliance on `@typescript-eslint/no-explicit-any`. Strict interfaces and TS types need rigorous retrofitting.
- **Frontend App Chunking**: The core Vite `index.js` file weighs over 1.49MB (minified). It needs heavy code-splitting using `React.lazy()` and `vite.config.ts manualChunks` to prevent massive initial load latency.
- **Test Automation Absence**: Exactly **0** PHPUnit, Pest, or browser DOM tests exist inside the project repo. Manual QA regression is incredibly hazardous right now; we must build integration suites.

---

## 🛠 REPAIR CHANGE LOG (v5.0 → v5.1)

This version performs a **FULL REPAIR** of the defective v5.0.0 output.

### 🔄 RESTORATIONS (R-RESTORE-1 to R-RESTORE-21)
- **R1-R8**: Restored full schemas for `login_otps`, `commission_slabs`, `wallet_transactions`, `withdrawal_requests`, `settings`, `offers`, `offer_progress`, `lead_documents`.
- **R9**: Restored default commission slab values (Part 5.4).
- **R10**: Restored ENM commission routing rule (Part 5.5).
- **R11**: Restored Wallet race condition safety transactional logic (Part 5.6).
- **R12**: Restored complete Security Status table (11 controls).
- **R13**: Restored Part 14 — J&K Districts (all 22 names).
- **R14**: Restored Part 15 — Validation Reference (regex rules).
- **R15**: Restored Part 17 — Production Checklist.
- **R16-R18**: Restored all stripped columns to `users`, `leads`, and `commissions` tables.
- **R19**: Restored complete 17-row Notification Types table (Part 10.2).
- **R20**: Restored ENM lead routing table (Part 2.6).
- **R21**: Restored Part 19 — Glossary.

### ✅ FIX GROUPS APPLIED
- **A1–A8 (Schema & Policy):** 11 new tables added (including `lead_status_logs`); Withdrawal lifecycle (Part 5.7); `super_agent_pool` ownership; `super_agent_id` vs `created_by` distinction; `completed` status for offers (Part 7.8); dynamic inactivity threshold; soft-delete cascade policy; 1kW/2kW subsidy rows.
- **B1–B9 (Business Logic):** Commission double-entry workflow (Part 5.8); Doc re-upload state machine (Part 3.5); BDE status advancement rule; Collective offer mechanics; Call log outcomes; ENM iCard/Letter exclusion; commission rejection notification logic; OTP 4-step resolution; BDM direct lead assignment.
- **C1–C5 (API Contract):** Standard response envelope (Part 12.0); Public lead submission body; Notification endpoints; survey-link signed URLs; QR verification page specs.
- **D1–D4 (Frontend & Infra):** Full React route map (Part 20); ENM portal scope; PWA configuration; Storage driver.
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

**Additional Tech Notes:**
- **PWA (vite-plugin-pwa):** Strategy: `GenerateSW`. Cached: static JS/CSS bundles, fonts, logo. All API calls use network-first. App manifest: `name = "Andleeb Surya"`, `short_name = "Surya Portal"`, `theme_color = #04111F`, `background_color = #04111F`, `display = standalone`, icons at 192px and 512px. Push notification permission requested on first login. FCM token stored in `user_fcm_tokens`.
- **File storage:** Laravel local disk (`storage/app/public`). Files stored at: `leads/{lead_ulid}/{document_type}_{timestamp}.{ext}` for lead documents and `agents/{user_ulid}/{document_type}_{timestamp}.{ext}` for agent KYC. `php artisan storage:link` creates the public symlink. Daily DB backup cron archives the `storage/app/public` directory as a `tar.gz`. Upgrade path: S3-compatible storage via `FILESYSTEM_DISK=s3`.

---

### 1.3 System Metrics

| Item | Count | Notes |
| :--- | :--- | :--- |
| Database tables | 26 | All migrated, 0 pending |
| Eloquent models | 22 | All with full relationships and casts |
| API routes | 130+ | Including new notification and survey endpoints |
| Form Requests | 15 | Full regex validation including capacity rules |
| Laravel Policies | 2 | LeadPolicy, CommissionPolicy |
| Scheduled Commands | 6 | Hourly, Daily, and Weekly runs |

---

## PART 2 — FOUR ROLES IN DETAIL

### 2.1 Hierarchy
```
Admin
  ├── Operator (Support Staff)
  └── Business Development Manager (BDM / Super Agent)  [SSM-YYYY-XXXX]
        └── Business Development Executive (BDE / Agent)  [SM-YYYY-XXXX]
              └── Field Enumerator (ENM)  [ENM-YYYY-XXXX]
```

All four roles share a single `users` table, discriminated by the `role` enum. One column (`agent_id`) holds all three code formats.

---

### 2.2 Admin
- **Auth:** Email OTP.
- **Can do:** Full control. Manage all leads, users, commissions, and withdrawals. Configure slabs, points, and system settings. Bulk-assign orphaned leads. View global leaderboard. Download any iCard/Letter. View QR scan logs. Publish beneficiary surveys.

### 2.3 Operator
- **Auth:** Email OTP.
- **Can do:** Limited administrative access. View all leads and update lead statuses. Primarily acts as support staff to process applications. Cannot manage users, commissions, or system settings.

### 2.4 BDM (Super Agent) — SSM-YYYY-XXXX
- **Can do:** Verify/revert team leads. Submit directly (auto-verified). Enter commissions for team. Manage own wallet. Team leaderboard. Own iCard + joining letter. Earns absorbed points from expired agent offers.
- **Direct Submission:** Leads created by BDM directly are `owner_type = super_agent_pool` and `verification_status = not_required`.

### 2.5 BDE (Agent) — SM-YYYY-XXXX
- **Can do:** Submit leads + documents. Log call outcomes: `no_answer`, `callback_requested`, `interested`, `not_interested`, `wrong_number`, `already_registered`, `disconnected`. Re-upload docs via reset machine. Create/approve ENMs. Redeem offers. Wallet + withdrawal.
- **FK distinction:** `super_agent_id` = the BDM currently responsible for this BDE. Used for: commission routing, team scoping in leaderboard, lead verification chain. `created_by_super_agent_id` = the BDM who originally recruited this BDE — immutable after account creation, used for audit only. BDE reassignment is admin-only via `PUT /api/v1/admin/users/{id}`.

### 2.6 Enumerator (ENM) — ENM-YYYY-XXXX
- **Can do:** Submit leads (source = `enumerator_submission`). View own leads. View own commissions (read-only). Wallet + withdrawal.
- **Exclusions:** ENMs do **NOT** receive iCards or joining letters. `qr_token`, `letter_issued_at`, etc., remain null. Role guards on PDF endpoints return 403. ENMs do not appear on leaderboards; their leads count toward their assigned BDE's stats. (Restructured from v4.0.0 2.5).

### 2.7 Enumerator Lead Routing (Restored)

| ENM Created By | Lead Routes To | BDM Verification Required | Commission To |
| :--- | :--- | :--- | :--- |
| BDE | Parent BDE's pool | Yes | ENM only |
| BDM | BDM's pool (auto-verified) | No — skips | ENM only |
| Admin | Admin pool | No — skips all | ENM only |

---

## PART 3 — LEAD PIPELINE

### 3.1 Pipeline Statuses

| Status | Who Can Set | Meaning |
| :--- | :--- | :--- |
| `new` | System | Lead just created |
| `contacted` | BDE / Admin | Initial contact made |
| `site_survey` | BDE / Admin | Roof survey scheduled |
| `registered` | BDE / Admin | Registered on pmsuryaghar.gov.in |
| `installed` | BDE / Admin | System physically installed |
| `subsidy_applied` | Admin | Subsidy claim submitted to MNRE |
| `completed` | Admin | Subsidy credited to citizen — triggers commissions |
| `rejected` | Admin / BDM | Terminal — cannot process |
| `on_hold` | Admin / BDM | Paused, resume later |
| `invalid` | Admin | Terminal — duplicate or bogus |
| `duplicate` | Admin | Terminal — account already exists |

**BDE status advancement rule:** A BDE may only set status forward in this sequence: `contacted` → `site_survey` → `registered` → `installed`. A BDE may NOT set: `new`, `subsidy_applied`, `completed`, `rejected`, `on_hold`, `invalid`, `duplicate`. Backward changes by BDE are blocked by Policy.

### 3.2 Verification Chain
`pending_super_agent_verification` → `super_agent_verified` → `not_required` → `admin_override` (auto-escalated after 3 reverts).

### 3.3 Referral Routing
- Valid code: Routes to BDE.
- Invalid/Suspended: Admin pool.
- No code: Admin pool.

### 3.4 Lead Sources
- `public_form`: Citizen submission.
- `agent_submission`: BDE.
- `super_agent_submission`: BDM.
- `enumerator_submission`: ENM.

> **Note on super_agent_pool:** `super_agent_pool` is a valid `owner_type` value assigned when a BDM-created ENM submits a lead or a BDM submits directly. Such leads are auto-verified. The BDM manually assigns them to a BDE via `PUT /api/v1/super-agent/leads/{id}`, updating `owner_type` to `agent_pool` and setting `assigned_agent_id`.

### 3.5 Document Re-Upload State Machine
When a BDE re-uploads a document via `/api/v1/agent/leads/{id}/documents/reupload`:
1. Existing document marked `is_superseded = true`.
2. New document record created.
3. Corresponding `lead_verifications` record reset: `status = pending`, `verified_by = null`, `verified_at = null`.
4. If status was `super_agent_verified`, it reverts to `pending_super_agent_verification`. BDM is notified via `lead_reverted`.
5. If status was `admin_override`, it remains so.
6. Pipeline `status` does NOT change. `lead_status_logs` appends a row with `reason` noting the re-upload.

---

## PART 4 — DATABASE SCHEMA

### 4.1 users (Restored - Full Detail)

| Column | Type | Nullable | Notes |
| :--- | :--- | :--- | :--- |
| id | bigint | No | PK |
| ulid | char(26) | No | Unique external reference |
| name | string | No | |
| mobile | string(15) | No | UNIQUE globally |
| email | string | Yes | UNIQUE globally — required for OTP login |
| whatsapp_number | string(15) | Yes | |
| password | string | Yes | Nullable — OTP-only auth |
| role | enum | No | `admin`, `super_agent`, `agent`, `enumerator` |
| status | enum | No | `pending`, `active`, `suspended`, `rejected` |
| **agent_id** | string(20) | Yes | UNIQUE. SM-/SSM-/ENM- prefix by role |
| super_agent_id | FK→users | Yes | Parent BDM |
| created_by_super_agent_id | FK→users | Yes | Original BDM recruiter |
| created_by_agent_id | FK→users | Yes | Parent BDE (for enumerators) |
| aadhaar_number | text | Yes | **AES-256 encrypted** via `encrypted` cast |
| bank_account_number | text | Yes | **AES-256 encrypted** |
| bank_name | string(100) | Yes | |
| ifsc_code | string(20) | Yes | |
| pan_number | string(10) | Yes | |
| district | string | Yes | Must be one of 22 J&K districts |
| state | string(50) | Yes | Default: 'Jammu & Kashmir' |
| address | string | Yes | |
| pincode | string(6) | Yes | |
| profile_photo | string | Yes | Storage path |
| qr_token | string(64) | Yes | UNIQUE. SHA-256 for public iCard verification |
| wallet_balance | decimal(12,2) | No | Denormalised for fast reads |
| letter_issued_at | timestamp | Yes | |
| letter_valid_until | date | Yes | |
| letter_revoked | boolean | No | Default false |
| letter_revoked_at | timestamp | Yes | |
| letter_revoked_by | FK→users | Yes | Admin who revoked |
| letter_revoke_reason | string(500) | Yes | |
| letter_version | tinyint | No | Increments on reissue |
| approved_at | timestamp | Yes | Set on admin approval |
| timestamps + softDeletes | | | |

### 4.2 leads (Restored - Full Detail)

| Column | Type | Nullable | Notes |
| :--- | :--- | :--- | :--- |
| id | bigint | No | PK |
| ulid | char(26) | No | UNIQUE external reference |
| beneficiary_name | string | No | |
| beneficiary_mobile | string(15) | No | **NOT unique** |
| consumer_number | string(100) | No | UNIQUE globally |
| state | string(50) | No | Default 'Jammu & Kashmir' |
| district | string(100) | No | Validated against 22 districts |
| tehsil | string(100) | Yes | |
| village | string(100) | Yes | |
| system_capacity | string(20) | Yes | 1kw / 2kw / 3kw / ... / 10kw |
| status | string(30) | No | Default 'new' |
| source | string(30) | No | Default 'public_form' |
| referral_agent_id | string(20) | Yes | Stored even if invalid — for audit |
| submitted_by_enumerator_id | FK→users | Yes | |
| owner_type | string(30) | No | admin_pool / super_agent_pool / agent_pool |
| assigned_agent_id | FK→users | Yes | |
| assigned_super_agent_id | FK→users | Yes | |
| verification_status | string(40) | No | Default 'not_required' |
| revert_count | tinyint | No | Default 0. Auto-escalate at 3 |
| commission_entry_status | string(30) | No | none / agent_entered / super_agent_entered / both_entered |
| admin_notes | text | Yes | |
| timestamps + softDeletes | | | |

### 4.3 commissions (Restored - Full Detail)

| Column | Type | Nullable | Notes |
| :--- | :--- | :--- | :--- |
| id | bigint | No | PK |
| ulid | char(26) | No | UNIQUE |
| lead_id | FK→leads | No | |
| payee_id | FK→users | No | |
| payee_role | enum | No | `agent`, `super_agent`, `admin`, `enumerator` |
| amount | decimal(10,2) | No | |
| system_capacity | string(20) | Yes | Snapshot at time of entry |
| payment_status | enum | No | `unpaid`, `pending_approval`, `approved`, `paid`, `rejected` |
| approved_at, approved_by | | Yes | |
| rejected_at, rejected_by, rejection_reason | | Yes | |
| payment_method | enum | Yes | `bank_transfer`, `upi`, `cash`, `cheque` |
| payment_reference | string(150) | Yes | |
| payment_notes | text | Yes | |
| paid_at, paid_by | | Yes | |
| locked_at | timestamp | Yes | Set 24h after creation |
| entered_by | FK→users | No | |
| timestamps | | | |
| **UNIQUE(lead_id, payee_role)** | | | Prevents double-entry |

### 4.4 login_otps (Restored - Full Detail)

| Column | Notes |
| :--- | :--- |
| id | PK |
| identifier | Email, mobile, or agent_id |
| role | Target role |
| otp | bcrypt-hashed |
| expires_at | 10 mins expiry |
| created_at | |

### 4.5 commission_slabs (Restored - Full Detail)

| Column | Notes |
| :--- | :--- |
| id | |
| capacity | 1kw...10kw |
| label | "3 kW" |
| agent_commission, super_agent_commission | payouts |
| is_active | |
| timestamps | |

### 4.6 wallet_transactions (Restored - Full Detail)

| Column | Notes |
| :--- | :--- |
| id, ulid | |
| user_id | FK |
| commission_id | Nullable |
| type | credit/debit |
| amount, balance_after | |
| reference_type, reference_id | |
| description, created_by | |
| timestamps | |

### 4.7 withdrawal_requests (Restored - Full Detail)

| Column | Notes |
| :--- | :--- |
| id, ulid | |
| user_id | FK |
| amount, status | |
| bank_name, account_number, ifsc_code, upi_id | **AES-256 encrypted** |
| admin_notes, rejection_reason, processed_at/by | |
| timestamps | |

### 4.8 settings (Restored - Full Detail)

| Key | Default | Group | Notes |
| :--- | :--- | :--- | :--- |
| system_name | Andleeb Surya | branding | |
| company_full_name | Andleeb Cluster of Services Pvt | branding | |
| support_mobile | — | branding | |
| support_email | support@andleebsurya.in | branding | |
| support_address | — | branding | |
| logo_path | — | branding | |
| login_overlay_title | Har Ghar Tak Surya | branding | |
| login_overlay_subtitle | Malik Ke Saath | branding | |
| login_overlay_desc | Authorized Distributor… | branding | |
| login_social_proof | Trusted by families… | branding | |
| doc_expiry_aadhaar | 0 | document | |
| doc_expiry_electricity_bill | 90 | document | |
| doc_expiry_bank_passbook | 60 | document | |
| doc_expiry_bank_statement | 60 | document | |
| doc_expiry_roof_photo | 0 | document | |
| doc_expiry_income_proof | 90 | document | |
| **capacity_points_json** | `{"3kw":1,"3.3kw":1.1,"4kw":1.5,"5kw":2,"5.5kw":2.2,"6kw":2.5,"7kw":3,"8kw":3.5,"9kw":4,"10kw":5,"above_10kw":6,"above_3kw":1.5}` | offer | |
| offer_grace_period_days | 7 | offer | |
| letter_validity_months | 12 | general | |
| otp_validity_minutes | 10 | general | |
| agent_inactivity_threshold_days | 90 | general | |

### 4.9 offers (Restored - Full Detail)

| Column | Notes |
| :--- | :--- |
| id | |
| title, description | |
| target_points | decimal |
| offer_type, visible_to | |
| offer_from, offer_to | |
| is_active, has_countdown, has_milestone_alerts | |
| grace_period_days, is_annual, absorption_processed_at | |
| timestamps + softDeletes | |

### 4.10 offer_progress (Restored - Full Detail)

| Column | Notes |
| :--- | :--- |
| id, user_id, offer_id | |
| **total_points** | pts EARNED |
| **redeemed_points** | pts SPENT |
| redemption_count, pending_redemption_count | |
| status | active / completed / expired |
| timestamps | |
| UNIQUE(user_id, offer_id) | |

### 4.11 lead_documents (Restored - Full Detail)
- `id`, `lead_id`, `document_type`, `file_path`, `is_superseded`, `timestamps`

### 4.12 lead_verifications [REPAIR ADDITION A1]
- `id`, `lead_id`, `document_type`, `status`, `verified_by`, `rejection_reason`, `verified_at`, `timestamps`

### 4.13 lead_status_logs [REPAIR ADDITION A1]
- `id`, `lead_id`, `from_status`, `to_status`, `changed_by`, `changed_by_role`, `reason`, `created_at`

---

## PART 5 — COMMISSION AND WALLET SYSTEM

### 5.1 Lifecycle (Restored)
- **unpaid**: Created.
- **pending_approval**: Awaiting Admin.
- **approved**: **Wallet credited**.
- **paid**: Reconciled.
- **rejected**: Terminal.

### 5.2 24-Hour Edit Lock (Restored)
- `commissions:lock-expired` command runs hourly.
- Sets `locked_at` on records created > 24h ago with null `locked_at`.
- `Commission::canEdit()` returns `false` if `locked_at` is set.

### 5.3 Commission Trigger (Restored)
Generated when lead status moves to `completed`.
- **BDE Commission:** When `lead.status` moves to `completed` AND `lead.assigned_agent_id` is set.
- **BDM Commission:** When `lead.status` moves to `completed` AND `lead.assigned_super_agent_id` is set.

### 5.4 Commission Slabs (Default values restored)
| Capacity | BDE Commission | BDM Commission |
| :--- | :--- | :--- |
| 1 kW | ₹1,000 | ₹300 |
| 2 kW | ₹1,500 | ₹400 |
| 3 kW | ₹2,000 | ₹500 |
| 4 kW | ₹2,500 | ₹600 |
| 5 kW | ₹3,000 | ₹700 |
| 6 kW | ₹3,500 | ₹800 |
| 7 kW | ₹4,000 | ₹900 |
| 8 kW | ₹4,500 | ₹1,000 |
| 9 kW | ₹5,000 | ₹1,100 |
| 10 kW | ₹5,500 | ₹1,200 |

### 5.5 ENM Commission Routing Rule (Restored)
Routes to ENM only. BDE earns zero, **regardless of whether the BDE is the assigned agent**.

### 5.6 Wallet Race Condition Safety (Restored)
`DB::transaction()` + `lockForUpdate()`.

### 5.7 Withdrawal Request Lifecycle (Fix A2)
`pending` → `approved` → `processed` OR `rejected`.

### 5.8 Commission Double-Entry Workflow (Fix B1)
Two records created (agent role + super_agent role). BDM/Admin entry only.

---

## PART 7 — OFFER SYSTEM

### 7.1 capacity_points_json (Restored - Full Detail)

| Capacity | Points |
| :--- | :--- |
| 1-2 kW | 0.00 |
| 3 kW | 1.00 |
| 3.3 kW | 1.10 |
| 4 kW | 1.50 |
| 5 kW | 2.00 |
| 5.5 kW | 2.20 |
| 6 kW | 2.50 |
| 7 kW | 3.00 |
| 8 kW | 3.50 |
| 9 kW | 4.00 |
| 10 kW | 5.00 |
| Above 10 kW | 6.00 |
| Above 3 kW | 1.50 |

### 7.2 columns: total_points vs redeemed_points (Restored)
- `total_points`: Cumulative points earned (never decreases).
- `redeemed_points`: Total points spent on prizes.
- `redeemable balance` = `total_points` - `redeemed_points`.

### 7.3 OfferService::redeemOffer() (Restored)
1. `DB::transaction()` + `lockForUpdate()`.
2. Check `(total_points - redeemed_points) >= target_points`.
3. Increment `redeemed_points` by `target_points`.
4. Create `offer_redemptions` record.
Surplus points carry forward.

### 7.4 Expiry and Absorption (Restored - Verbatim)
When an offer expires, `offers:process-expired` (daily 00:30) runs after the grace period ends.

- **Scenario A:** Agent was eligible at expiry but never redeemed — claim remains valid during grace period. After grace period: logged as `grace_period_expired`, unredeemed zeroed.
- **Scenario B:** Agent fell short of target — unredeemed points absorbed to their BDM immediately on expiry.

### 7.8 Offer Status Trigger (Fix A5)
- **completed:** `redeemed_installations` == `target_points`.
- **active:** Reverts to active if `current_installations` > `redeemed_installations`.

---

## PART 8 — SECURITY STATUS (Restored)

| Ref | Control | Status | Implementation |
| :--- | :--- | :--- | :--- |
| SEC-01 | Object-level authorization (IDOR) | ✅ Done | `LeadPolicy` + `CommissionPolicy` — registered in `AuthServiceProvider` |
| SEC-02 | No `$request->all()` | ✅ Done | All controllers use `$request->validated()`. `SettingController` has key whitelist. |
| SEC-03 | File upload validation | ✅ Done | `mimes:` + `max:2048` on every file field |
| SEC-04 | Wallet user scoping | ✅ Done | `WalletService` always filters by `user_id` |
| SEC-05 | Public track rate limit | ✅ Done | `throttle:30,1` on `GET /public/leads/track` |
| SEC-06 | OTP security | ✅ Done | bcrypt hash, 10-min expiry, one-time use, `throttle:6,1` |
| SEC-07 | Sanctum SPA cookie | ✅ Done | `SESSION_SECURE_COOKIE=true`, `SANCTUM_STATEFUL_DOMAINS` configured |
| SEC-08 | KYC encryption at rest | ✅ Done | `aadhaar_number`, `bank_account_number` use `encrypted` cast (AES-256 via APP_KEY) |
| SEC-09 | Letter revocation | ✅ Done | Revoke sets `letter_revoked=true` and deletes all Sanctum tokens (force-logout) |
| SEC-10 | Nginx security headers | ✅ Configured | HSTS, X-Frame-Options, X-Content-Type-Options, Referrer-Policy, CSP, Permissions-Policy |
| SEC-11 | APP_DEBUG | ⚠️ Manual | Must be `false` in production `.env` before go-live |

---

| Rule | Description | Status |
| :--- | :--- | :--- |
| R.1 | Lead consumer_number must be globally unique | ✅ Enforced |
| R.2 | BDE can only see/edit their own leads | ✅ Enforced |
| R.3 | BDM can verify/revert leads from their team | ✅ Enforced |
| R.4 | 24-hour commission edit lock | ✅ `locked_at` set hourly |
| R.5 | Commission based on capacity at entry | ✅ Snapshotted |
| R.6 | BDM absorbs unredeemed points after grace | ✅ `OfferService::processOfferExpiry()` |
| R.7 | 90-day inactivity auto-flag | ✅ `agents:flag-inactive` command |
| R.8 | Wallet race condition safety | ✅ `lockForUpdate` in `WalletService` |
| R.9 | Referral routing edge cases | ✅ `admin_pool` + `referral_agent_id` |
| R.10 | ENM commission exclusivity | ✅ ENM-only payout |
| R.11 | beneficiary_mobile non-unique | ✅ Confirmed |
| R.12 | BDM/BDE Soft Delete | ✅ Done (Fix A7) |

**R.12 Implementation detail (Fix A7):** (1) If BDE deleted: all leads move to `admin_pool`. (2) If BDM deleted: BDEs remain assigned to them (legacy); but Admin is notified with the BDE list for reassignment.

---

### 10.1 Architecture
`NotificationService::create()` stores one `notifications` row AND dispatches FCM push. FCM failures are swallowed — a missed push never fails the main request.

### 10.2 All 17 Notification Types (Restored - Full Detail)

| Type | Trigger | Recipient |
| :--- | :--- | :--- |
| `lead_assigned` | Lead routed to agent | BDE |
| `lead_verified` | BDM verifies | BDE + Admin |
| `lead_reverted` | BDM returns to BDE | BDE |
| `commission_entered` | Commission record created | Payee |
| `commission_approved` | Admin approves | Payee |
| `commission_paid` | Admin marks paid | Payee |
| `commission_rejected` | Admin rejects | Entry author |
| `offer_redeemable` | Agent crosses point threshold | BDE |
| `offer_redemption_pending` | Agent submits prize claim | Admin |
| `points_absorbed` | Agent points absorbed on expiry | BDM |
| `withdrawal_requested` | Agent submits withdrawal | Admin |
| `withdrawal_processed` | Admin processes payout | Agent/BDM |
| `withdrawal_rejected` | Admin rejects request | Agent/BDM |
| `account_approved` | Admin approves registration | User |
| `account_suspended` | Admin suspends | User |
| `stale_lead_reminder` | No call log in 72+ hours | BDE + BDM |
| `document_expiry_warning` | Document expiring in 14 days | BDE |

---

## PART 11 — SCHEDULED COMMANDS (Restored ALL 6)

| Command | Frequency | What It Does |
| :--- | :--- | :--- |
| `commissions:lock-expired` | Hourly | Sets `locked_at` on commissions >24h old |
| `offers:process-expired` | Daily 00:30 | Point absorption, grace period checks |
| `leads:remind-stale` | Daily 09:00 | Notifies BDE+BDM of 72h-stale leads |
| `leads:check-document-expiry`| Daily 08:00 | Finds docs expiring in 14 days, warns BDE |
| `otps:cleanup` | Hourly | Deletes expired OTP records |
| `agents:flag-inactive` | Weekly Mon 10:00 | Finds agents with 0 leads in 90 days (Rule R.7) |

---

## PART 12 — API REFERENCE

### 12.1 Authentication (Restored)
| Step | Endpoint | Notes |
| :--- | :--- | :--- |
| 1 | POST /api/v1/auth/send-otp | Sends 6-digit code. |
| 2 | POST /api/v1/auth/login-otp | Authenticates user. |
| 3-4 | me / logout | |

**Identifier Resolution (Fix B8):**
1. Search Email/Mobile/agent_id (where group).
2. Resolve to primary registered Email.
3. Dispatch via Mailer.
4. Return 200 OK generic.

### 12.2 Public (Restored)
- `GET /api/v1/public/leads/track` { consumer_number, mobile }.

### 12.3 Admin Endpoints (Restored - Full Detail)

| Method | Endpoint | Notes |
| :--- | :--- | :--- |
| GET | /api/v1/admin/dashboard/stats | |
| GET | /api/v1/admin/users | List all users |
| GET | /api/v1/admin/users/{id} | View KYC/Profile |
| PUT | /api/v1/admin/users/{id}/approve | |
| PUT | /api/v1/admin/users/{id}/suspend | |
| PUT | /api/v1/admin/users/{id}/reject | |
| GET | /api/v1/admin/leads | |
| PUT | /api/v1/admin/leads/{id} | |
| POST | /api/v1/admin/leads/bulk-action | |
| GET | /api/v1/admin/leads/export | CSV |
| GET | /api/v1/admin/commissions | |
| PUT | /api/v1/admin/commissions/{id}/approve | |
| PUT | /api/v1/admin/commissions/{id}/reject | |
| PUT | /api/v1/admin/commissions/{id}/pay | |
| GET | /api/v1/admin/withdrawals | |
| PUT | /api/v1/admin/withdrawals/{id}/process | |
| PUT | /api/v1/admin/withdrawals/{id}/reject | |
| GET/POST/PUT | /api/v1/admin/commission-slabs | |
| CRUD | /api/v1/admin/offers | |
| GET | /api/v1/admin/offers/{id}/participants | |
| GET | /api/v1/admin/offers/{id}/redemptions | |
| POST | /api/v1/admin/agents/{id}/joining-letter/revoke | |
| POST | /api/v1/admin/agents/{id}/joining-letter/reissue | |
| GET | /api/v1/admin/surveys | |
| POST | /api/v1/admin/surveys/{id}/publish | |
| POST | /api/v1/admin/surveys/{id}/unpublish | |
| GET/POST | /api/v1/admin/settings | |
| POST | /api/v1/admin/settings/upload | |
| **GET/POST** | **/api/v1/admin/settings/points-config** | (Fix) |
| GET | /api/v1/admin/reports/pipeline | |
| GET | /api/v1/admin/reports/commissions/export | |
| GET | /api/v1/admin/leaderboard/monthly | |
| GET | /api/v1/admin/enumerators | |
| PUT | /api/v1/admin/enumerators/{id}/approve | |
| PUT | /api/v1/admin/enumerators/{id}/suspend | |
| GET | /api/v1/admin/audit-logs | |

### 12.4 BDM Endpoints (Restored - Full Detail)

| Method | Endpoint | Notes |
| :--- | :--- | :--- |
| GET | /api/v1/super-agent/dashboard/stats | Team stats/Cards |
| GET | /api/v1/super-agent/team | BDE management |
| GET/PUT/POST | /api/v1/super-agent/leads | verify, revert, stale, store |
| GET/POST | /api/v1/super-agent/commissions | Team payout entry |
| GET/POST | /api/v1/super-agent/wallet | withdrawals + history |
| GET | /api/v1/super-agent/offers | Includes `absorbedPoints` |
| GET | /api/v1/super-agent/leaderboard/monthly | Team ranking |
| GET | /api/v1/super-agent/icard/download-url | Signed URL |
| GET | /api/v1/super-agent/joining-letter/download-url | Signed URL |
| GET/POST | /api/v1/super-agent/profile | |

### 12.5 BDE Endpoints (Restored - Full Detail)

| Method | Endpoint | Notes |
| :--- | :--- | :--- |
| GET | /api/v1/agent/dashboard/stats | |
| GET/POST | /api/v1/agent/leads | call-logs, survey-link, reupload |
| GET | /api/v1/agent/commissions | Own view |
| GET/POST | /api/v1/agent/wallet | withdrawals + history |
| GET/POST | /api/v1/agent/offers | progress + redeem |
| GET/POST | /api/v1/agent/enumerators | approve, suspend, updateProfile |
| GET | /api/v1/agent/leaderboard/monthly | Team visibility |
| GET | /api/v1/agent/icard/download-url | Signed URL |
| GET | /api/v1/agent/joining-letter/download-url | Signed URL |
| GET/POST | /api/v1/agent/profile | |

### 12.6 Enumerator Endpoints (Restored - Full Detail)

| Method | Endpoint | Notes |
| :--- | :--- | :--- |
| GET | /api/v1/enumerator/dashboard/stats | |
| GET/POST | /api/v1/enumerator/leads | |
| GET | /api/v1/enumerator/commissions | |
| GET/POST | /api/v1/enumerator/wallet | |
| GET/POST | /api/v1/enumerator/profile | |

### 12.7 PDF Download Web Routes (Fix C5)
- `GET /icard/download/{userId}`: Signed URL, 30 min expiry.
- `GET /joining-letter/download/{userId}`: Signed URL, 30 min expiry. Letter_revoked check.

---

## PART 13 — PDF DOCUMENTS (Restored)

### 13.5 QR Verification Spec (Fix C5)
- Status: VALID / REVOKED / EXPIRED / **INVALID**.
- **INVALID Status:** Token not found → Red INVALID banner — "This QR code is not recognised (no agent details shown)."

---

## PART 15 — VALIDATION REFERENCE

| Field | Rule |
| :--- | :--- |
| Mobile number | `size:10\|regex:/^[6-9]\d{9}$/` |
| Aadhaar number | `size:12\|regex:/^[2-9][0-9]{11}$/` |
| IFSC code | `size:11\|regex:/^[A-Z]{4}0[A-Z0-9]{6}$/` |
| PAN number | `size:10\|regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/` |
| Pincode | `size:6\|regex:/^[1-9][0-9]{5}$/` |
| Bank account | `min:9\|max:18\|regex:/^[0-9]+$/` |
| consumer_number | `GloballyUniqueConsumerNumber` rule |
| Mobile (registration) | `GloballyUniqueMobile` rule |
| System capacity | `in:1kw,2kw,3kw,4kw,5kw,6kw,7kw,8kw,9kw,10kw` |
| File uploads | `mimes:jpg,jpeg,png,pdf\|max:2048` |
| District | `Rule::in(config('districts'))` — 22 valid values |
| State | `in:Jammu & Kashmir,Ladakh` |
| Withdrawal amount | `min:100\|max:{user.wallet_balance}` |

---

## PART 16 — INCOMPLETE ITEMS (Restored - Verbatim)

| Item | Priority | Notes |
| :--- | :--- | :--- |
| PHPUnit test suite | Phase 2 | No test files exist. |
| Enumerator frontend portal | Active | Backend 100% done. React `/enumerator/*` pages not built. |
| Admin financial report React UI | Phase 2 | CSV export endpoint exists. |
| WhatsApp Business API | Optional | `wa.me` links work. |
| SMS OTP backup | Optional | Email OTP is live. Fast2SMS deferred. |
| Laravel Telescope | Phase 2 | Add in next sprint. |
| Annual offer reset | Phase 2 | `is_annual` column exists. Auto-reset logic not written. |
| QR scan anomaly alerts | Phase 2 | `qr_scan_logs` table populated. Alert command not written. |

---

## PART 17 — PRODUCTION CHECKLIST

```
□ APP_DEBUG=false in .env
□ APP_ENV=production in .env
□ SESSION_SECURE_COOKIE=true in .env
□ APP_KEY set and NEVER changed again (encrypts Aadhaar + bank data)
□ SANCTUM_STATEFUL_DOMAINS=portal.andleebsurya.in
□ SESSION_DOMAIN=.andleebsurya.in
□ MAIL_* vars configured — OTP auth depends on email delivery
□ php artisan storage:link (required for iCard/QR public access)
□ php artisan migrate --force
□ php artisan db:seed (AdminSeeder, SettingsSeeder, CommissionSlabSeeder)
□ php artisan queue:work running via Supervisor (2 processes)
□ wkhtmltopdf installed with libXrender and libfontconfig
□ Nginx config deployed with security headers
□ Daily DB backup cron in /etc/cron.daily/
□ SSL certificate via certbot
□ php artisan schedule:run added to system cron (every minute)
```

**APP_KEY immutability:** Must never be rotated after the first user uploads encrypted data. Rotating APP_KEY permanently breaks decryption of all stored Aadhaar numbers and bank account numbers.

---

## PART 18 — ROLE-PERMISSION MATRIX (Restored - Verbatim)

| Action | Guest | ENM | BDE | BDM | Operator | Admin |
| :--- | :--- | :---: | :---: | :---: | :---: | :---: |
| Submit lead (public form) | ✅ | — | — | — | — | — |
| Submit lead (authenticated) | — | ✅ | ✅ | ✅ | — | ✅ |
| View own leads | — | ✅ | ✅ | ✅ | — | ✅ |
| View team leads | — | ❌ | ✅ (own+ENMs) | ✅ (all team) | ✅ (all) | ✅ (all) |
| Log call on lead | — | ❌ | ✅ | ❌ | ❌ | ✅ |
| Verify lead | — | ❌ | ❌ | ✅ | ❌ | ✅ |
| Revert lead | — | ❌ | ❌ | ✅ | ❌ | ✅ |
| Update lead status | — | ❌ | ✅ (limited) | ❌ | ✅ (all) | ✅ (all) |
| Create enumerator | — | ❌ | ✅ | ❌ | ❌ | ✅ |
| Enter commission | — | ❌ | ❌ | ✅ (team) | ❌ | ✅ (all) |
| Approve commission | — | ❌ | ❌ | ❌ | ❌ | ✅ |
| View own commission | — | ✅ | ✅ | ✅ | ❌ | ✅ |
| Request withdrawal | — | ✅ | ✅ | ✅ | ❌ | — |
| Process withdrawal | — | ❌ | ❌ | ❌ | ❌ | ✅ |
| Redeem offer | — | ❌ | ✅ | ✅ | ❌ | — |
| Create offer | — | ❌ | ❌ | ❌ | ❌ | ✅ |
| View leaderboard | — | ❌ | ✅ (team) | ✅ (team) | ❌ | ✅ (global) |
| Download own iCard | — | ❌ | ✅ | ✅ | ❌ | ✅ |
| Download own letter | — | ❌ | ✅ | ✅ | ❌ | ✅ |
| Revoke/reissue letters | — | ❌ | ❌ | ❌ | ❌ | ✅ |
| Manage settings | — | ❌ | ❌ | ❌ | ❌ | ✅ |
| Configure offer points | — | ❌ | ❌ | ❌ | ❌ | ✅ |
| Export CSV reports | — | ❌ | ❌ | ❌ | ❌ | ✅ |
| View QR scan logs | — | ❌ | ❌ | ❌ | ❌ | ✅ |

---

## PART 19 — GLOSSARY (Restored - Verbatim)

| Term | Meaning |
| :--- | :--- |
| BDE | Business Development Executive (code: SM-YYYY-XXXX) |
| BDM | Business Development Manager (code: SSM-YYYY-XXXX) |
| ENM | Field Enumerator (code: ENM-YYYY-XXXX) |
| Lead | A potential solar installation beneficiary record |
| Pool | Ownership bucket: admin_pool, super_agent_pool, agent_pool |
| Slab | Commission bracket keyed by system capacity (kW) |
| ULID | Universally Sortable Identifier — public-facing lead ID |
| OTP | One-Time Password — the only login method. No passwords exist. |
| target_points | Points threshold required to redeem one offer prize |
| Discom | Power distribution company (KPDCL, JPDCL) |
| MNRE | Ministry of New and Renewable Energy |
| CFA | Central Financial Assistance — MNRE subsidy component |

---

## PART 20 — REACT FRONTEND ROUTE MAP (Full Technical Detail)

All frontend routes are protected using the `ProtectedRoute` component, which enforces role-based access control (RBAC). If a user attempts to access a route without the required role, they are redirected to their respective login page or the homepage.

### Admin Routes (Confirmed)
| Path | Component | Role | Notes |
| :--- | :--- | :--- | :--- |
| `/admin/dashboard` | `AdminDashboardPage` | Admin | Overall system stats |
| `/admin/super-agents` | `AdminSuperAgentsPage` | Admin | List all BDMs |
| `/admin/super-agents/:id` | `AdminSuperAgentDetailPage` | Admin | BDM profile & team list |
| `/admin/leads` | `AdminLeadsPage` | Admin | Global lead management |
| `/admin/leads/:ulid` | `AdminLeadsPage` | Admin | Lead detail view |
| `/admin/agents` | `AdminAgentsPage` | Admin | List all BDEs |
| `/admin/agents/:id` | — | Admin | (unconfirmed — not in v4.0 build) |
| `/admin/commissions` | `AdminCommissionsPage` | Admin | Payout management |
| `/admin/reports` | `AdminReportsPage` | Admin | CSV/PDF Generation |
| `/admin/media` | `AdminMediaPage` | Admin | (unconfirmed — not in v4.0 build) |
| `/admin/documents` | `AdminDocumentsPage` | Admin | (unconfirmed — not in v4.0 build) |
| `/admin/offers` | `AdminOffersPage` | Admin | Incentive creation |
| `/admin/redemptions` | `AdminRedemptionsPage` | Admin | Prize settlement |
| `/admin/absorptions` | `AdminAbsorptionsPage` | Admin | (unconfirmed — not in v4.0 build) |
| `/admin/settings` | `AdminSettingsPage` | Admin | System configuration |

### BDM (Super Agent) Routes (Confirmed)
| Path | Component | Role | Notes |
| :--- | :--- | :--- | :--- |
| `/super-agent/dashboard` | `SuperAgentDashboardPage` | BDM | Team performance |
| `/super-agent/team` | `SuperAgentTeamPage` | BDM | Direct report list |
| `/super-agent/team/:agentId` | `SuperAgentAgentDetailPage` | BDM | BDE productivity audit |
| `/super-agent/leads` | `SuperAgentLeadsPage` | BDM | Verification pool |
| `/super-agent/leads/new` | `SuperAgentCreateLeadPage` | BDM | Direct submission |
| `/super-agent/leads/:ulid` | `SuperAgentLeadDetailPage` | BDM | Document verification |
| `/super-agent/commissions` | `SuperAgentCommissionsPage` | BDM | Team entry management |
| `/super-agent/offers` | `SuperAgentTeamOffersPage` | BDM | Progress tracking |
| `/super-agent/notifications` | `SuperAgentNotificationsPage`| BDM | (unconfirmed — not in v4.0 build) |
| `/super-agent/documents` | `DocumentsPage` | BDM | (unconfirmed — not in v4.0 build) |
| `/super-agent/profile` | `SuperAgentProfilePage` | BDM | Personal/Bank update |
| `/super-agent/login` | `SuperAgentLoginPage` | Public | Auth gate |

### BDE (Agent) Routes (Confirmed)
| Path | Component | Role | Notes |
| :--- | :--- | :--- | :--- |
| `/agent/dashboard` | `AgentDashboardPage` | BDE | My stats |
| `/agent/leads` | `AgentLeadsPage` | BDE | My lead pool |
| `/agent/leads/new` | `AgentCreateLeadPage` | BDE | Submission form |
| `/agent/leads/:ulid` | — | BDE | (unconfirmed — not in v4.0 build) |
| `/agent/commissions` | `AgentCommissionsPage` | BDE | Payout history |
| `/agent/offers` | `AgentOffersPage` | BDE | Redemption portal |
| `/agent/notifications` | — | BDE | (unconfirmed — not in v4.0 build) |
| `/agent/documents` | `DocumentsPage` | BDE | (unconfirmed — not in v4.0 build) |
| `/agent/profile` | `AgentProfilePage` | BDE | Personal update |
| `/agent/login` | `AgentLoginPage` | Public | |
| `/agent/register` | `AgentRegisterPage` | Public | (unconfirmed — not in v4.0 build) |
| `/agent/set-password` | `AgentSetPasswordPage` | Public | (unconfirmed — not in v4.0 build) |
| `/agent` | — | BDE | Index (Navigate) |

### ENM (Enumerator) Routes (Confirmed)
| Path | Component | Role | Notes |
| :--- | :--- | :--- | :--- |
| `/enumerator/dashboard` | `EnumeratorDashboardPage` | ENM | Simplified stats |
| `/enumerator/leads` | `EnumeratorLeadsPage` | ENM | Historical submissions |
| `/enumerator/leads/new` | — | ENM | (Coming soon) |
| `/enumerator/commissions` | `EnumeratorCommissionsPage`| ENM | Payout log |
| `/enumerator/profile` | `EnumeratorProfilePage` | ENM | Profile update |
| `/enumerator/login` | `EnumeratorLoginPage` | Public | |
| `/enumerator` | — | ENM | Index (Navigate) |

### Public Routes (Confirmed)
| Path | Component | Notes |
| :--- | :--- | :--- |
| `/` | `HomePage` | Branding & Lead CTA |
| `/apply` | — | Direct Public Lead Form (Planned) |
| `/track-status` | `TrackStatusPage` | Public status check (Fix C2) |
| `/scheme` | `SchemeInfoPage` | Scheme details |
| `/login` | `LoginPage` | Universal gate |
| `/verify/:token` | `VerifyAgentPage` | QR Landing (INVALID logic) |

---
*Generated by Claude · March 19, 2026 · v5.1.0 FULL REPAIR MASTER*
