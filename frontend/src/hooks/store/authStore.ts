import { create } from 'zustand';
import type { User, UserRole } from '@/types';

/** Token key — stored in sessionStorage (tab-scoped, cleared on tab close). */
const TOKEN_KEY = 'sm_token';
/** Role key — stored in localStorage for cross-tab redirect logic ONLY (not sensitive). */
const ROLE_KEY = 'sm_role';

interface AuthStore {
    token: string | null;
    user: User | null;
    role: UserRole | null;
    setAuth: (token: string, user: User) => void;
    setUser: (user: User) => void;
    clearAuth: () => void;
}

export const useAuthStore = create<AuthStore>()((set) => ({
    // Rehydrate from sessionStorage on page refresh (same tab)
    token: sessionStorage.getItem(TOKEN_KEY),
    user: null,
    role: (localStorage.getItem(ROLE_KEY) as UserRole | null),

    setAuth: (token, user) => {
        // Store sensitive token in sessionStorage only (tab-scoped, cleared on tab close)
        sessionStorage.setItem(TOKEN_KEY, token);
        // Store non-sensitive role hint in localStorage for login redirect logic only
        localStorage.setItem(ROLE_KEY, user.role);
        set({ token, user, role: user.role as UserRole });
    },

    setUser: (user) => {
        set({ user });
    },

    clearAuth: () => {
        sessionStorage.removeItem(TOKEN_KEY);
        localStorage.removeItem(ROLE_KEY);
        set({ token: null, user: null, role: null });
    },
}));
