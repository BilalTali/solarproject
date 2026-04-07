import { useQuery } from '@tanstack/react-query';
import { settingsApi } from '@/services/settings.api';
import { useAuthStore } from '@/hooks/store/authStore';

export function useAdminSettings() {
    const { user } = useAuthStore();
    
    const { data: response, isLoading } = useQuery({
        queryKey: ['admin-settings', user?.id],
        queryFn: settingsApi.getSettings,
        enabled: !!user,
        staleTime: 1000 * 60 * 5, // 5 minutes
    });

    // Flatten settings for easy access (similar to PublicSettingsData)
    const settings: Record<string, string | null> = {};
    if (response?.data) {
        Object.values(response.data).forEach(group => {
            group.forEach(item => {
                settings[item.key] = item.value;
            });
        });
    }

    return {
        settings,
        isLoading,
        companyName: settings.company_name || 'SuryaMitra',
        logo: settings.company_logo,
    };
}
