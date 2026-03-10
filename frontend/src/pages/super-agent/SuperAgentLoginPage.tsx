import { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { Sun, AlertCircle, Eye, EyeOff, Star } from 'lucide-react';
import { authApi } from '@/api/auth.api';
import { useAuthStore } from '@/store/authStore';
import { useSettings } from '@/hooks/useSettings';

const schema = z.object({
    mobile: z.string().length(10, 'Enter valid 10-digit mobile number').regex(/^[6-9]\d{9}$/),
    password: z.string().min(1, 'Password is required'),
});

type FormValues = z.infer<typeof schema>;

export default function SuperAgentLoginPage() {
    const navigate = useNavigate();
    const { setAuth } = useAuthStore();
    const { companyName } = useSettings();
    const [showPassword, setShowPassword] = useState(false);
    const [error, setError] = useState<string | null>(null);

    const { register, handleSubmit, formState: { errors, isSubmitting } } = useForm<FormValues>({
        resolver: zodResolver(schema),
    });

    const onSubmit = async (data: FormValues) => {
        setError(null);
        try {
            const res = await authApi.login({ identifier: data.mobile, password: data.password });
            if (!res.success) { setError(res.message || 'Login failed'); return; }
            const { token, user } = res.data;

            if (user.role !== 'super_agent') {
                setError('This portal is for Business Development Managers only. Please use the correct login portal.');
                return;
            }

            setAuth(token, user);
            navigate('/super-agent/dashboard');
        } catch (err: unknown) {
            const e = err as { response?: { data?: { message?: string } } };
            setError(e.response?.data?.message || 'Invalid credentials. Please try again.');
        }
    };

    return (
        <div className="min-h-screen bg-gradient-to-br from-slate-800 via-slate-700 to-orange-900 flex items-center justify-center px-4">
            <div className="w-full max-w-md">
                {/* Logo */}
                <div className="text-center mb-8">
                    <div className="inline-flex items-center justify-center w-16 h-16 bg-orange-500 rounded-2xl shadow-lg mb-4">
                        <Sun size={32} className="text-white" />
                    </div>
                    <h1 className="text-3xl font-bold text-white">{companyName}</h1>
                    <div className="flex items-center justify-center gap-2 mt-1">
                        <Star size={14} className="text-orange-400 fill-orange-400" />
                        <p className="text-orange-300 font-medium">Business Development Manager Portal</p>
                        <Star size={14} className="text-orange-400 fill-orange-400" />
                    </div>
                    <p className="text-slate-400 text-sm mt-1">PM Surya Ghar Muft Bijli Yojana</p>
                </div>

                {/* Card */}
                <div className="bg-white rounded-2xl shadow-2xl p-8">
                    <h2 className="text-xl font-bold text-slate-800 mb-1">Business Development Manager Login</h2>
                    <p className="text-xs text-slate-500 mb-6">Sign in with your registered mobile number</p>

                    {error && (
                        <div className="flex items-start gap-2 bg-red-50 border border-red-200 rounded-lg px-4 py-3 mb-5 text-red-700 text-sm">
                            <AlertCircle size={16} className="mt-0.5 flex-shrink-0" />
                            {error}
                        </div>
                    )}

                    <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
                        <div>
                            <label className="block text-sm font-medium text-slate-700 mb-1">Mobile Number</label>
                            <input
                                {...register('mobile')}
                                type="tel"
                                maxLength={10}
                                className="input"
                                placeholder="10-digit mobile"
                            />
                            {errors.mobile && <p className="text-red-500 text-xs mt-1">{errors.mobile.message}</p>}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700 mb-1">Password</label>
                            <div className="relative">
                                <input
                                    {...register('password')}
                                    type={showPassword ? 'text' : 'password'}
                                    className="input pr-10"
                                    placeholder="••••••••"
                                />
                                <button
                                    type="button"
                                    onClick={() => setShowPassword(!showPassword)}
                                    className="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400"
                                >
                                    {showPassword ? <EyeOff size={16} /> : <Eye size={16} />}
                                </button>
                            </div>
                            {errors.password && <p className="text-red-500 text-xs mt-1">{errors.password.message}</p>}
                        </div>

                        <button
                            type="submit"
                            disabled={isSubmitting}
                            className="w-full btn-primary py-3 mt-2"
                        >
                            {isSubmitting ? 'Signing in...' : 'Sign In'}
                        </button>
                    </form>

                    <div className="mt-6 pt-4 border-t border-slate-100 flex justify-between text-xs text-slate-500">
                        <Link to="/agent/login" className="hover:text-orange-600 transition-colors">Business Development Executive Login →</Link>
                        <Link to="/admin/login" className="hover:text-orange-600 transition-colors">Admin Login →</Link>
                    </div>
                </div>
            </div>
        </div>
    );
}
