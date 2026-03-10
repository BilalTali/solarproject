import { create } from 'zustand';
import { persist } from 'zustand/middleware';
import type { User, UserRole } from '@/types';

interface AuthStore {
    token: string | null;
    user: User | null;
    role: UserRole | null; // 'admin' | 'super_agent' | 'agent'
    setAuth: (token: string, user: User) => void;
    setUser: (user: User) => void;
    clearAuth: () => void;
}

export const useAuthStore = create<AuthStore>()(
    persist(
        (set) => ({
            token: null,
            user: null,
            role: null,

            setAuth: (token, user) => {
                localStorage.setItem('sm_token', token);
                localStorage.setItem('sm_role', user.role);
                set({ token, user, role: user.role });
            },

            setUser: (user) => {
                set({ user });
            },

            clearAuth: () => {
                localStorage.removeItem('sm_token');
                localStorage.removeItem('sm_role');
                localStorage.removeItem('sm_user');
                set({ token: null, user: null, role: null });
            },
        }),
        {
            name: 'sm_auth',
            partialize: (state) => ({ token: state.token, user: state.user, role: state.role }),
        }
    )
);
