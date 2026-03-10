import { Navigate } from 'react-router-dom';
import { useAuthStore } from '@/store/authStore';
import type { UserRole } from '@/types';

interface ProtectedRouteProps {
    children: React.ReactNode;
    requiredRole: UserRole;
    loginPath: string;
}

export default function ProtectedRoute({ children, requiredRole, loginPath }: ProtectedRouteProps) {
    const { token, role } = useAuthStore();

    if (!token || role !== requiredRole) {
        return <Navigate to={loginPath} replace />;
    }

    return <>{children}</>;
}
