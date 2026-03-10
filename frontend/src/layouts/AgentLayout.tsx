import { useState } from 'react';
import { Outlet } from 'react-router-dom';
import { Menu } from 'lucide-react';
import AgentSidebar from '@/components/agent/AgentSidebar';
import { useSettings } from '@/hooks/useSettings';

export default function AgentLayout() {
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const { companyName } = useSettings();

    return (
        <div className="flex h-screen bg-neutral-100 overflow-hidden">
            {/* Skip to content link */}
            <a
                href="#main-content"
                className="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-[100] focus:px-4 focus:py-2 focus:bg-primary focus:text-white focus:rounded-lg focus:font-bold focus:shadow-lg"
            >
                Skip to main content
            </a>

            {/* Desktop Sidebar */}
            <div className="hidden lg:block shrink-0">
                <AgentSidebar />
            </div>

            {/* Mobile Drawer */}
            {sidebarOpen && (
                <div className="fixed inset-0 z-50 flex lg:hidden">
                    <div className="fixed inset-0 bg-black/50" onClick={() => setSidebarOpen(false)} />
                    <div className="relative z-10">
                        <AgentSidebar onClose={() => setSidebarOpen(false)} />
                    </div>
                </div>
            )}

            {/* Main */}
            <div className="flex-1 flex flex-col min-w-0 overflow-hidden">
                {/* Top Bar (mobile) */}
                <header className="lg:hidden flex items-center gap-3 px-4 py-3 bg-white shadow-sm">
                    <button
                        onClick={() => setSidebarOpen(true)}
                        className="p-2 text-neutral-600 hover:bg-neutral-100 rounded-lg"
                        aria-label="Open sidebar"
                    >
                        <Menu className="w-5 h-5" />
                    </button>
                    <span className="font-display font-bold text-primary">{companyName} Business Development Executive</span>
                </header>

                {/* Content */}
                <main id="main-content" className="flex-1 overflow-y-auto p-4 md:p-6" tabIndex={-1}>
                    <Outlet />
                </main>
            </div>
        </div>
    );
}
