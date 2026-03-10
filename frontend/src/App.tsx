import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { Toaster } from 'react-hot-toast';

// Layouts
import AgentLayout from '@/layouts/AgentLayout';
import AdminLayout from '@/layouts/AdminLayout';
import SuperAgentLayout from '@/layouts/SuperAgentLayout';

// Route Guards
import ProtectedRoute from '@/components/shared/ProtectedRoute';

// Public Pages
import HomePage from '@/pages/public/HomePage';
import MediaPage from '@/pages/public/MediaPage';
import PublicDocumentsPage from '@/pages/public/DocumentsPage';
import VerifyAgentPage from '@/pages/public/VerifyAgentPage';
import PrivacyPolicyPage from '@/pages/public/PrivacyPolicyPage';
import TermsConditionsPage from '@/pages/public/TermsConditionsPage';
import AboutPage from '@/pages/public/AboutPage';
import ContactPage from '@/pages/public/ContactPage';
import FaqPage from '@/pages/public/FaqPage';
import SchemeInfoPage from '@/pages/public/SchemeInfoPage';
import RefundPolicyPage from '@/pages/public/RefundPolicyPage';

// Auth Pages
import AgentLoginPage from '@/pages/agent/AgentLoginPage';
import AgentRegisterPage from '@/pages/agent/AgentRegisterPage';
import AgentSetPasswordPage from '@/pages/agent/AgentSetPasswordPage';
import AdminLoginPage from '@/pages/admin/AdminLoginPage';
import SuperAgentLoginPage from '@/pages/super-agent/SuperAgentLoginPage';
import LoginPage from '@/pages/public/LoginPage';

// Business Development Executive Pages
import AgentDashboardPage from '@/pages/agent/AgentDashboardPage';
import AgentCreateLeadPage from '@/pages/agent/AgentCreateLeadPage';
import AgentLeadsPage from '@/pages/agent/AgentLeadsPage';
import AgentProfilePage from '@/pages/agent/AgentProfilePage';

import AgentCommissionsPage from '@/pages/agent/AgentCommissionsPage';
import { AgentOffersPage } from '@/pages/agent/AgentOffersPage';

// Business Development Manager Pages
import SuperAgentDashboardPage from '@/pages/super-agent/SuperAgentDashboardPage';
import SuperAgentTeamPage from '@/pages/super-agent/SuperAgentTeamPage';
import SuperAgentAgentDetailPage from '@/pages/super-agent/SuperAgentAgentDetailPage';
import SuperAgentLeadsPage from '@/pages/super-agent/SuperAgentLeadsPage';
import SuperAgentCommissionsPage from '@/pages/super-agent/SuperAgentCommissionsPage';
import SuperAgentCreateLeadPage from '@/pages/super-agent/SuperAgentCreateLeadPage';
import SuperAgentProfilePage from '@/pages/super-agent/SuperAgentProfilePage';
import { SuperAgentTeamOffersPage } from '@/pages/super-agent/SuperAgentTeamOffersPage';
import {
  SuperAgentLeadDetailPage,
  SuperAgentNotificationsPage,
} from '@/pages/super-agent/SuperAgentPlaceholders';
import DocumentsPage from '@/pages/shared/DocumentsPage';

// Admin Pages
import AdminDashboardPage from '@/pages/admin/AdminDashboardPage';
import AdminSuperAgentsPage from '@/pages/admin/AdminSuperAgentsPage';
import AdminSuperAgentDetailPage from '@/pages/admin/AdminSuperAgentDetailPage';
import AdminAgentsPage from '@/pages/admin/AdminAgentsPage';
import AdminLeadsPage from '@/pages/admin/AdminLeadsPage';
import AdminCommissionsPage from '@/pages/admin/AdminCommissionsPage';
import AdminReportsPage from '@/pages/admin/AdminReportsPage';
import AdminMediaPage from '@/pages/admin/AdminMediaPage';
import AdminDocumentsPage from '@/pages/admin/AdminDocumentsPage';
import AdminSettingsPage from '@/pages/admin/AdminSettingsPage';
import { AdminOffersPage } from '@/pages/admin/AdminOffersPage';
import { AdminRedemptionsPage } from '@/pages/admin/AdminRedemptionsPage';
import { AdminAbsorptionsPage } from '@/pages/admin/AdminAbsorptionsPage';

const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      retry: 1,
      staleTime: 1000 * 30,
    },
  },
});

export default function App() {
  return (
    <QueryClientProvider client={queryClient}>
      <BrowserRouter>
        <Toaster
          position="top-right"
          toastOptions={{
            duration: 3500,
            style: {
              fontFamily: '"Noto Sans", sans-serif',
              fontSize: '14px',
              borderRadius: '12px',
              boxShadow: '0 4px 24px rgba(10,61,122,0.12)',
            },
          }}
        />
        <Routes>
          {/* Public */}
          <Route path="/" element={<HomePage />} />
          <Route path="/media" element={<MediaPage />} />
          <Route path="/documents" element={<PublicDocumentsPage />} />
          <Route path="/login" element={<LoginPage />} />
          <Route path="/verify/:token" element={<VerifyAgentPage />} />
          <Route path="/privacy-policy" element={<PrivacyPolicyPage />} />
          <Route path="/terms-conditions" element={<TermsConditionsPage />} />
          <Route path="/about" element={<AboutPage />} />
          <Route path="/contact" element={<ContactPage />} />
          <Route path="/faq" element={<FaqPage />} />
          <Route path="/scheme" element={<SchemeInfoPage />} />
          <Route path="/pm-surya-ghar" element={<SchemeInfoPage />} />
          <Route path="/refund-policy" element={<RefundPolicyPage />} />

          {/* Business Development Executive Auth */}
          <Route path="/agent/login" element={<AgentLoginPage />} />
          <Route path="/agent/register" element={<AgentRegisterPage />} />
          <Route path="/agent/set-password" element={<AgentSetPasswordPage />} />

          {/* Business Development Executive Protected Routes */}
          <Route
            path="/agent"
            element={
              <ProtectedRoute requiredRole="agent" loginPath="/agent/login">
                <AgentLayout />
              </ProtectedRoute>
            }
          >
            <Route path="dashboard" element={<AgentDashboardPage />} />
            <Route path="leads" element={<AgentLeadsPage />} />
            <Route path="leads/new" element={<AgentCreateLeadPage />} />
            <Route path="leads/:ulid" element={<div className="p-8 text-center text-slate-500">This module is currently disabled for maintenance.</div>} />
            <Route path="commissions" element={<AgentCommissionsPage />} />
            <Route path="offers" element={<AgentOffersPage />} />
            <Route path="notifications" element={<div className="p-8 text-center text-slate-500">This module is currently disabled for maintenance.</div>} />
            <Route path="documents" element={<DocumentsPage />} />
            <Route path="profile" element={<AgentProfilePage />} />
            <Route index element={<Navigate to="/agent/dashboard" replace />} />
          </Route>

          {/* Business Development Manager Auth */}
          <Route path="/super-agent/login" element={<SuperAgentLoginPage />} />
          <Route path="/super-agent/set-password" element={<AgentSetPasswordPage />} />

          {/* Business Development Manager Protected Routes */}
          <Route
            path="/super-agent"
            element={
              <ProtectedRoute requiredRole="super_agent" loginPath="/super-agent/login">
                <SuperAgentLayout />
              </ProtectedRoute>
            }
          >
            <Route path="dashboard" element={<SuperAgentDashboardPage />} />
            <Route path="team" element={<SuperAgentTeamPage />} />
            <Route path="team/:agentId" element={<SuperAgentAgentDetailPage />} />
            <Route path="leads" element={<SuperAgentLeadsPage />} />
            <Route path="leads/new" element={<SuperAgentCreateLeadPage />} />
            <Route path="leads/:ulid" element={<SuperAgentLeadDetailPage />} />
            <Route path="commissions" element={<SuperAgentCommissionsPage />} />
            <Route path="offers" element={<SuperAgentTeamOffersPage />} />
            <Route path="notifications" element={<SuperAgentNotificationsPage />} />
            <Route path="documents" element={<DocumentsPage />} />
            <Route path="profile" element={<SuperAgentProfilePage />} />
            <Route index element={<Navigate to="/super-agent/dashboard" replace />} />
          </Route>

          {/* Admin Auth */}
          <Route path="/admin/login" element={<AdminLoginPage />} />

          {/* Admin Protected Routes */}
          <Route
            path="/admin"
            element={
              <ProtectedRoute requiredRole="admin" loginPath="/admin/login">
                <AdminLayout />
              </ProtectedRoute>
            }
          >
            <Route path="dashboard" element={<AdminDashboardPage />} />
            <Route path="super-agents" element={<AdminSuperAgentsPage />} />
            <Route path="super-agents/:id" element={<AdminSuperAgentDetailPage />} />
            <Route path="leads" element={<AdminLeadsPage />} />
            <Route path="leads/:ulid" element={<AdminLeadsPage />} />
            <Route path="agents" element={<AdminAgentsPage />} />
            <Route path="agents/:id" element={<div className="p-8 text-center text-slate-500">This module is currently disabled for maintenance.</div>} />
            <Route path="commissions" element={<AdminCommissionsPage />} />
            <Route path="reports" element={<AdminReportsPage />} />
            <Route path="media" element={<AdminMediaPage />} />
            <Route path="documents" element={<AdminDocumentsPage />} />
            <Route path="offers" element={<AdminOffersPage />} />
            <Route path="redemptions" element={<AdminRedemptionsPage />} />
            <Route path="absorptions" element={<AdminAbsorptionsPage />} />
            <Route path="settings" element={<AdminSettingsPage />} />
            <Route index element={<Navigate to="/admin/dashboard" replace />} />
          </Route>

          {/* Catch-all */}
          <Route path="*" element={<Navigate to="/" replace />} />
        </Routes>
      </BrowserRouter>
    </QueryClientProvider>
  );
}
