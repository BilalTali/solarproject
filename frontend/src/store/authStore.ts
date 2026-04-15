import { create } from 'zustand';
import type { User, UserRole } from '@/types';

/** Token key — stored in sessionStorage (tab-scoped, cleared on tab close). */
const TOKEN_KEY = 'sm_token';
/** Role key — stored in localStorage for cross-tab redirect logic ONLY (not sensitive). */
const ROLE_KEY = 'sm_role';
/** User key — stored in sessionStorage to survive refresh but stay tab-scoped. */
const USER_KEY = 'sm_user';

interface AuthStore {
    token: string | null;
    user: User | null;
    role: UserRole | null;
    setAuth: (token: string, user: User) => void;
    setUser: (user: User) => void;
    clearAuth: () => void;
}

const getStoredUser = (): User | null => {
    try {
        const val = sessionStorage.getItem(USER_KEY);
        return val ? JSON.parse(val) : null;
    } catch {
        return null;
    }
};

export const useAuthStore = create<AuthStore>()((set) => ({
    // Rehydrate from sessionStorage on page refresh (same tab)
    token: sessionStorage.getItem(TOKEN_KEY),
    user: getStoredUser(),
    role: (localStorage.getItem(ROLE_KEY) as UserRole | null),

    setAuth: (token, user) => {
        // Store sensitive token in sessionStorage only (tab-scoped, cleared on tab close)
        sessionStorage.setItem(TOKEN_KEY, token);
        // Store non-sensitive role hint in localStorage for login redirect logic only
        localStorage.setItem(ROLE_KEY, user.role);
        // Persist user object
        sessionStorage.setItem(USER_KEY, JSON.stringify(user));
        set({ token, user, role: user.role as UserRole });
    },

    setUser: (user) => {
        sessionStorage.setItem(USER_KEY, JSON.stringify(user));
        set({ user });
    },

    clearAuth: () => {
        sessionStorage.removeItem(TOKEN_KEY);
        localStorage.removeItem(ROLE_KEY);
        sessionStorage.removeItem(USER_KEY);
        set({ token: null, user: null, role: null });
    },
}));
