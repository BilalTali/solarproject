import { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { useMutation } from '@tanstack/react-query';
import toast from 'react-hot-toast';
import { Sun, Lock, Phone, User } from 'lucide-react';
import { authApi } from '@/api/auth.api';
import { useAuthStore } from '@/store/authStore';
import { useSettings } from '@/hooks/useSettings';

const schema = z.object({
    identifier: z.string().min(1, 'Mobile or Business Development Executive ID is required'),
    password: z.string().min(1, 'Password is required'),
});
type FormData = z.infer<typeof schema>;

export default function AgentLoginPage() {
    const navigate = useNavigate();
    const { setAuth } = useAuthStore();
    const { companyName } = useSettings();
    const [useAgentId, setUseAgentId] = useState(false);

    const { register, handleSubmit, formState: { errors } } = useForm<FormData>({
        resolver: zodResolver(schema),
    });

    const loginMutation = useMutation({
        mutationFn: authApi.login,
        onSuccess: (res) => {
            if (!res.success) { toast.error(res.message || 'Login failed'); return; }
            const { token, user, requires_password_set } = res.data;
            if (user.role !== 'agent') { toast.error('This login is for agents only.'); return; }
            setAuth(token, user);
            toast.success(`Welcome back, ${user.name}!`);
            if (requires_password_set) navigate('/agent/set-password');
            else navigate('/agent/dashboard');
        },
        onError: (err: unknown) => {
            const msg = (err as { response?: { data?: { message?: string } } })?.response?.data?.message || 'Login failed.';
            toast.error(msg);
        },
    });

    return (
        <div className="min-h-screen bg-gradient-to-br from-primary/5 to-neutral-100 flex items-center justify-center p-4">
            <div className="w-full max-w-md">
                {/* Logo */}
                <div className="text-center mb-8">
                    <div className="inline-flex items-center gap-2 mb-2">
                        <div className="w-10 h-10 rounded-full bg-gradient-to-br from-accent to-accent-dark flex items-center justify-center shadow-md">
                            <Sun className="w-6 h-6 text-white" />
                        </div>
                        <span className="font-display font-bold text-2xl text-primary">{companyName}</span>
                    </div>
                    <h1 className="font-display font-bold text-2xl text-dark mt-3">Business Development Executive Login</h1>
                    <p className="text-neutral-600 text-sm mt-1">Sign in to your agent dashboard</p>
                </div>

                <div className="card shadow-xl">
                    {/* Toggle */}
                    <div className="flex rounded-xl bg-neutral-100 p-1 mb-6">
                        <button
                            onClick={() => setUseAgentId(false)}
                            className={`flex-1 flex items-center justify-center gap-2 py-2 rounded-lg text-sm font-medium transition-all ${!useAgentId ? 'bg-white text-primary shadow-sm' : 'text-neutral-600'}`}
                        >
                            <Phone className="w-4 h-4" /> Mobile Number
                        </button>
                        <button
                            onClick={() => setUseAgentId(true)}
                            className={`flex-1 flex items-center justify-center gap-2 py-2 rounded-lg text-sm font-medium transition-all ${useAgentId ? 'bg-white text-primary shadow-sm' : 'text-neutral-600'}`}
                        >
                            <User className="w-4 h-4" /> Business Development Executive ID
                        </button>
                    </div>

                    <form onSubmit={handleSubmit((d) => loginMutation.mutate(d))} className="flex flex-col gap-4">
                        <div>
                            <label className="label">{useAgentId ? 'Business Development Executive ID (SM-YYYY-XXXX)' : 'Mobile Number'}</label>
                            <input
                                className={`input ${errors.identifier ? 'input-error' : ''}`}
                                placeholder={useAgentId ? 'SM-2026-1001' : '10-digit mobile number'}
                                {...register('identifier')}
                            />
                            {errors.identifier && <p className="text-danger text-xs mt-1">{errors.identifier.message}</p>}
                        </div>
                        <div>
                            <label className="label">Password</label>
                            <input
                                type="password"
                                className={`input ${errors.password ? 'input-error' : ''}`}
                                placeholder="Your password"
                                {...register('password')}
                            />
                            {errors.password && <p className="text-danger text-xs mt-1">{errors.password.message}</p>}
                        </div>
                        <button type="submit" className="btn-primary w-full py-3 flex items-center justify-center gap-2 mt-2" disabled={loginMutation.isPending}>
                            <Lock className="w-4 h-4" />
                            {loginMutation.isPending ? 'Signing in...' : 'Sign In'}
                        </button>
                    </form>

                    <div className="mt-6 pt-5 border-t border-gray-100 text-center">
                        <p className="text-sm text-neutral-600">
                            Not yet an agent?{' '}
                            <Link to="/agent/register" className="text-primary font-semibold hover:underline">Register here</Link>
                        </p>
                    </div>
                </div>

                <p className="text-center mt-4">
                    <Link to="/" className="text-sm text-neutral-600 hover:text-primary">← Back to Home</Link>
                </p>
            </div>
        </div>
    );
}
