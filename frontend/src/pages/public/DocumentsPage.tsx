import { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { FileText, Download, Search, FolderOpen, ExternalLink } from 'lucide-react';
import SEOHead from '@/components/shared/SEOHead';
import { publicApi, type PublicDocument } from '@/services/public.api';

async function fetchPublicDocuments(): Promise<PublicDocument[]> {
    return publicApi.getDocuments();
}

export default function DocumentsPage() {
    const [search, setSearch] = useState('');

    const { data: documents = [], isLoading } = useQuery({
        queryKey: ['public-documents'],
        queryFn: fetchPublicDocuments,
    });

    const filtered = documents.filter(d =>
        d.title.toLowerCase().includes(search.toLowerCase()) ||
        (d.category ?? '').toLowerCase().includes(search.toLowerCase()) ||
        (d.description ?? '').toLowerCase().includes(search.toLowerCase())
    );

    // Group by category
    const grouped = filtered.reduce<Record<string, PublicDocument[]>>((acc, doc) => {
        const key = doc.category ?? 'General';
        if (!acc[key]) acc[key] = [];
        acc[key].push(doc);
        return acc;
    }, {});

    const categories = Object.keys(grouped).sort();

    return (
        <div className="min-h-screen" style={{ background: 'linear-gradient(135deg, #fff8f0 0%, #ffffff 60%, #f0f4ff 100%)' }}>
            <SEOHead
                title="Document Download Centre — PM Surya Ghar"
                description="Download official forms, guidelines and templates for PM Surya Ghar Muft Bijli Yojana."
                breadcrumbs={[
                    { name: 'Home', url: window.location.origin },
                    { name: 'Documents', url: window.location.origin + '/documents' }
                ]}
            />

            {/* Hero */}
            <div className="bg-gradient-to-r from-orange-500 to-amber-500 text-white pt-16 pb-24 px-4">
                <div className="max-w-4xl mx-auto text-center">
                    <div className="inline-flex items-center justify-center w-16 h-16 bg-white/20 rounded-2xl mb-4 backdrop-blur-sm ring-2 ring-white/30">
                        <FileText className="w-8 h-8" />
                    </div>
                    <h1 className="text-3xl md:text-4xl font-display font-black tracking-tight mb-3">
                        Document Download Centre
                    </h1>
                    <p className="text-orange-100 text-base max-w-xl mx-auto">
                        Official forms, guidelines and templates for PM Surya Ghar Muft Bijli Yojana
                    </p>

                    {/* Search */}
                    <div className="mt-8 max-w-lg mx-auto relative">
                        <Search className="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-orange-300" />
                        <input
                            type="text"
                            placeholder="Search documents..."
                            value={search}
                            onChange={e => setSearch(e.target.value)}
                            className="w-full pl-12 pr-5 py-3.5 rounded-2xl text-slate-800 text-sm font-medium
                                       bg-white/95 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-white/60
                                       placeholder:text-slate-400 shadow-xl"
                        />
                    </div>
                </div>
            </div>

            {/* Content */}
            <div className="max-w-5xl mx-auto px-4 -mt-10 pb-20">
                {isLoading ? (
                    <div className="flex flex-col items-center gap-3 py-24 text-slate-400">
                        <div className="w-10 h-10 border-4 border-orange-100 border-t-orange-500 rounded-full animate-spin" />
                        <p className="text-sm font-medium">Loading documents...</p>
                    </div>
                ) : categories.length === 0 ? (
                    <div className="text-center py-24 bg-white rounded-3xl shadow-sm border border-slate-100">
                        <FolderOpen className="w-12 h-12 text-slate-200 mx-auto mb-3" />
                        <p className="font-bold text-slate-600">No documents found</p>
                        <p className="text-sm text-slate-400">
                            {search ? 'Try a different search term' : 'Documents will appear here once published'}
                        </p>
                    </div>
                ) : (
                    <div className="space-y-8">
                        {categories.map(cat => (
                            <section key={cat}>
                                <h2 className="flex items-center gap-2 text-sm font-black text-slate-500 uppercase tracking-widest mb-3">
                                    <FolderOpen className="w-4 h-4 text-orange-400" />
                                    {cat}
                                    <span className="text-[10px] bg-orange-100 text-orange-600 px-2 py-0.5 rounded-full font-bold">
                                        {grouped[cat].length}
                                    </span>
                                </h2>

                                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                    {grouped[cat].map(doc => (
                                        <div
                                            key={doc.id}
                                            className="group bg-white rounded-2xl border border-slate-100 shadow-sm p-5
                                                       hover:border-orange-200 hover:shadow-md transition-all duration-200 flex flex-col gap-3"
                                        >
                                            {/* Icon + Title */}
                                            <div className="flex items-start gap-3">
                                                <div className="w-10 h-10 rounded-xl bg-orange-50 border border-orange-100 flex items-center justify-center shrink-0 group-hover:bg-orange-100 transition-colors">
                                                    {doc.thumbnail_url
                                                        ? <img src={doc.thumbnail_url} alt="" className="w-8 h-8 object-cover rounded-lg" />
                                                        : <FileText className="w-5 h-5 text-orange-500" />
                                                    }
                                                </div>
                                                <div className="flex-1 min-w-0">
                                                    <h3 className="font-bold text-slate-800 leading-tight text-sm truncate">{doc.title}</h3>
                                                    {doc.description && (
                                                        <p className="text-xs text-slate-500 line-clamp-2 mt-0.5">{doc.description}</p>
                                                    )}
                                                </div>
                                            </div>

                                            {/* Download button */}
                                            {doc.file_url ? (
                                                <a
                                                    href={doc.file_url}
                                                    target="_blank"
                                                    rel="noreferrer noopener"
                                                    className="mt-auto flex items-center justify-center gap-2 px-4 py-2 rounded-xl
                                                               bg-orange-50 text-orange-600 font-bold text-xs
                                                               border border-orange-100 hover:bg-orange-500 hover:text-white hover:border-orange-500
                                                               transition-all duration-200 group/btn"
                                                >
                                                    <Download className="w-3.5 h-3.5 group-hover/btn:scale-110 transition-transform" />
                                                    Download
                                                    <ExternalLink className="w-3 h-3 opacity-50" />
                                                </a>
                                            ) : (
                                                <span className="mt-auto flex items-center justify-center gap-2 px-4 py-2 rounded-xl
                                                               bg-slate-50 text-slate-400 font-bold text-xs border border-slate-100 cursor-not-allowed">
                                                    Not available
                                                </span>
                                            )}
                                        </div>
                                    ))}
                                </div>
                            </section>
                        ))}
                    </div>
                )}
            </div>
        </div>
    );
}
