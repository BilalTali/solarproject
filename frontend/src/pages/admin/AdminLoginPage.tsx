import { useNavigate } from 'react-router-dom';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { useMutation } from '@tanstack/react-query';
import toast from 'react-hot-toast';
import { Sun, Shield, Mail, Lock } from 'lucide-react';
import { authApi } from '@/api/auth.api';
import { useAuthStore } from '@/store/authStore';
import { useSettings } from '@/hooks/useSettings';

const schema = z.object({
    identifier: z.string().email('Enter a valid email'),
    password: z.string().min(1, 'Password is required'),
});
type FormData = z.infer<typeof schema>;

export default function AdminLoginPage() {
    const navigate = useNavigate();
    const { setAuth } = useAuthStore();
    const { companyName } = useSettings();

    const { register, handleSubmit, formState: { errors } } = useForm<FormData>({
        resolver: zodResolver(schema),
    });

    const loginMutation = useMutation({
        mutationFn: authApi.login,
        onSuccess: (res) => {
            if (!res.success) { toast.error(res.message || 'Login failed'); return; }
            const { token, user } = res.data;
            if (user.role !== 'admin') { toast.error('This login is for admins only.'); return; }
            setAuth(token, user);
            toast.success(`Welcome, ${user.name}!`);
            navigate('/admin/dashboard');
        },
        onError: (err: unknown) => {
            const msg = (err as { response?: { data?: { message?: string } } })?.response?.data?.message || 'Login failed.';
            toast.error(msg);
        },
    });

    return (
        <div className="min-h-screen bg-dark flex items-center justify-center p-4">
            <div className="w-full max-w-md">
                <div className="text-center mb-8">
                    <div className="inline-flex items-center gap-2 mb-2">
                        <div className="w-10 h-10 rounded-full bg-gradient-to-br from-accent to-accent-dark flex items-center justify-center shadow-md">
                            <Sun className="w-6 h-6 text-white" />
                        </div>
                        <span className="font-display font-bold text-2xl text-white">{companyName}</span>
                    </div>
                    <div className="flex items-center justify-center gap-2 mt-4">
                        <Shield className="w-5 h-5 text-accent" />
                        <h1 className="font-display font-bold text-xl text-white">Admin Portal</h1>
                    </div>
                    <p className="text-neutral-600 text-sm mt-1">Authorized access only</p>
                </div>

                <div className="bg-white/5 backdrop-blur border border-white/10 rounded-card p-8 shadow-2xl">
                    <form onSubmit={handleSubmit((d) => loginMutation.mutate(d))} className="flex flex-col gap-5">
                        <div>
                            <label className="block text-sm font-semibold text-white/70 mb-1.5">Admin Email</label>
                            <div className="relative">
                                <Mail className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-white/30" />
                                <input
                                    className={`w-full bg-white/5 border border-white/10 text-white rounded-lg pl-10 pr-4 py-2.5 focus:outline-none focus:border-accent transition-all ${errors.identifier ? 'border-danger' : ''}`}
                                    placeholder="admin@suryamitra.in"
                                    {...register('identifier')}
                                />
                            </div>
                            {errors.identifier && <p className="text-danger text-xs mt-1">{errors.identifier.message}</p>}
                        </div>
                        <div>
                            <label className="block text-sm font-semibold text-white/70 mb-1.5">Password</label>
                            <div className="relative">
                                <Lock className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-white/30" />
                                <input
                                    type="password"
                                    className={`w-full bg-white/5 border border-white/10 text-white rounded-lg pl-10 pr-4 py-2.5 focus:outline-none focus:border-accent transition-all ${errors.password ? 'border-danger' : ''}`}
                                    placeholder="Admin password"
                                    {...register('password')}
                                />
                            </div>
                            {errors.password && <p className="text-danger text-xs mt-1">{errors.password.message}</p>}
                        </div>
                        <button type="submit" className="btn-accent w-full py-3 flex items-center justify-center gap-2 mt-2" disabled={loginMutation.isPending}>
                            {loginMutation.isPending ? 'Signing in...' : '🔐 Sign In Securely'}
                        </button>
                    </form>

                    {import.meta.env.DEV && (
                        <div className="mt-5 pt-4 border-t border-white/10">
                            <p className="text-xs text-white/40 text-center">Dev default: admin@suryamitra.in / Admin@123456</p>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}
