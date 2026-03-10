import axios from 'axios';

const api = axios.create({
    baseURL: import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api/v1',
    headers: {
        Accept: 'application/json',
    },
});

// Request interceptor — attach token
api.interceptors.request.use((config) => {
    const token = localStorage.getItem('sm_token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

// Response interceptor — redirect on 401
api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            localStorage.removeItem('sm_token');
            localStorage.removeItem('sm_user');
            // Determine redirect based on stored role
            const role = localStorage.getItem('sm_role');
            let loginPath = '/agent/login';
            if (role === 'admin') loginPath = '/admin/login';
            if (role === 'super_agent') loginPath = '/super-agent/login';

            if (!window.location.pathname.includes('/login')) {
                window.location.href = loginPath;
            }
        }
        return Promise.reject(error);
    }
);

export default api;
