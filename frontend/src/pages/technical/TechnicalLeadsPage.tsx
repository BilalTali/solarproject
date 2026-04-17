import { useState, useRef, useEffect } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { Camera, MapPin, CheckCircle, Navigation, Loader2, List, PlayCircle, UploadCloud, FileText } from 'lucide-react';
import toast from 'react-hot-toast';
import api from '@/services/axios';
import { useAuthStore } from '@/store/authStore';
import { useSettings } from '@/hooks/useSettings';
import type { Lead } from '@/types';

// Utility for fetching leads assigned to the tech
const fetchTechnicalLeads = async () => {
    const res = await api.get<{ leads: Lead[] }>('/technical/leads');
    return res.data.leads;
};

export default function TechnicalLeadsPage() {
    const { user } = useAuthStore();
    const { settings } = useSettings();
    const queryClient = useQueryClient();
    const technicianType = (user as any)?.technician_type || 'engineer';
    const termsHtml = settings.technical_team_terms || 'I certify that the information provided is accurate and captured at the site.';

    const [activeLead, setActiveLead] = useState<Lead | null>(null);
    const [actionType, setActionType] = useState<'site_survey' | 'installation_complete' | null>(null);
    const [selfie, setSelfie] = useState<File | null>(null);
    const [location, setLocation] = useState<{ lat: number, lng: number } | null>(null);
    const [agreed, setAgreed] = useState(false);
    const [locating, setLocating] = useState(false);

    const { data: leads = [], isLoading } = useQuery({
        queryKey: ['technical-assigned-leads'],
        queryFn: fetchTechnicalLeads,
    });

    const visitMutation = useMutation({
        mutationFn: async ({ ulid, formData }: { ulid: string, formData: FormData }) => {
            const res = await api.post(`/technical/leads/${ulid}/visit`, formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });
            return res.data;
        },
        onSuccess: () => {
            toast.success('Visit processed successfully');
            queryClient.invalidateQueries({ queryKey: ['technical-assigned-leads'] });
            closeModal();
        },
        onError: (err: any) => {
            toast.error(err.response?.data?.error || 'Failed to submit visit');
        }
    });

    const closeModal = () => {
        setActiveLead(null);
        setActionType(null);
        setSelfie(null);
        setLocation(null);
        setAgreed(false);
        setLocating(false);
    };

    const handleActionClick = (lead: Lead, type: 'site_survey' | 'installation_complete') => {
        setActiveLead(lead);
        setActionType(type);
    };

    const getGeoLocation = () => {
        setLocating(true);
        if ('geolocation' in navigator) {
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    setLocation({ lat: pos.coords.latitude, lng: pos.coords.longitude });
                    setLocating(false);
                    toast.success('Location acquired');
                },
                (err) => {
                    setLocating(false);
                    toast.error(`Location error: ${err.message}`);
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
            );
        } else {
            setLocating(false);
            toast.error('Geolocation is not supported by this browser');
        }
    };

    const submitVisit = () => {
        if (!location) return toast.error('Please acquire location data first');
        if (!selfie) return toast.error('Please capture a selfie');
        if (!agreed) return toast.error('You must agree to the terms');

        const fd = new FormData();
        fd.append('visit_type', actionType!);
        fd.append('latitude', String(location.lat));
        fd.append('longitude', String(location.lng));
        fd.append('selfie_image', selfie);
        fd.append('agreed_to_terms', '1');

        visitMutation.mutate({ ulid: activeLead!.ulid, formData: fd });
    };

    return (
        <div className="space-y-6">
            <div className="flex items-center gap-3">
                <div className="w-10 h-10 rounded-xl bg-orange-100 flex items-center justify-center">
                    <Navigation className="w-5 h-5 text-orange-600" />
                </div>
                <div>
                    <h1 className="text-2xl font-bold text-slate-800">Assigned Leads</h1>
                    <p className="text-sm text-slate-500">Visit sites and upload geo-tagged status updates.</p>
                </div>
            </div>

            {isLoading ? (
                <div className="flex items-center justify-center py-20">
                    <Loader2 className="w-8 h-8 animate-spin text-orange-500" />
                </div>
            ) : leads.length === 0 ? (
                <div className="bg-white rounded-2xl border border-slate-200 p-12 text-center text-slate-400">
                    <List size={48} className="mx-auto mb-4 opacity-20" />
                    <p className="font-bold text-slate-600">No leads assigned</p>
                    <p className="text-sm">You currently have no tasks assigned to you by the admin.</p>
                </div>
            ) : (
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    {leads.map((lead: Lead) => {
                        const canSurvey = lead.assigned_surveyor_id === user?.id && ['NEW', 'ON_HOLD', 'REGISTERED'].includes(lead.status);
                        const canInstall = lead.assigned_installer_id === user?.id && lead.status === 'AT_BANK';
                        const hasVisitedSurvey = (lead as any).technical_visits?.some((v: any) => v.visit_type === 'site_survey');
                        const hasVisitedInstall = (lead as any).technical_visits?.some((v: any) => v.visit_type === 'installation_complete');

                        return (
                            <div key={lead.id} className="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">
                                <div className="p-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                                    <div>
                                        <div className="text-[10px] uppercase font-black tracking-widest text-slate-400">ULID: {lead.ulid.slice(-8)}</div>
                                        <h3 className="font-bold text-slate-800">{lead.beneficiary_name}</h3>
                                    </div>
                                    <div className="text-right">
                                        <span className="inline-block px-2 py-1 bg-slate-200 text-slate-700 text-[10px] font-bold rounded uppercase tracking-wider">
                                            {lead.status.replace(/_/g, ' ')}
                                        </span>
                                    </div>
                                </div>
                                <div className="p-4 space-y-3 flex-1 text-sm bg-white">
                                    <div className="flex items-start gap-2">
                                        <MapPin className="text-slate-400 shrink-0 mt-0.5" size={14} />
                                        <span className="text-slate-600 line-clamp-2">{lead.beneficiary_address || (lead.beneficiary_district + ', ' + lead.beneficiary_state)}</span>
                                    </div>
                                </div>
                                <div className="p-4 border-t border-slate-100 flex flex-col gap-2 bg-slate-50">
                                    {lead.assigned_surveyor_id === user?.id && (
                                        canSurvey ? (
                                            <button onClick={() => handleActionClick(lead, 'site_survey')} className="flex items-center justify-center gap-2 w-full py-2 bg-indigo-600 text-white rounded-lg font-bold text-sm hover:bg-indigo-700 transition">
                                                <PlayCircle size={16} /> Execute Site Survey
                                            </button>
                                        ) : hasVisitedSurvey ? (
                                            <div className="flex items-center justify-center gap-2 w-full py-2 bg-indigo-50 text-indigo-700 rounded-lg font-bold text-sm border border-indigo-100">
                                                <CheckCircle size={16} /> Survey Completed
                                            </div>
                                        ) : null
                                    )}

                                    {lead.assigned_installer_id === user?.id && (
                                        canInstall ? (
                                            <button onClick={() => handleActionClick(lead, 'installation_complete')} className="flex items-center justify-center gap-2 w-full py-2 bg-emerald-600 text-white rounded-lg font-bold text-sm hover:bg-emerald-700 transition">
                                                <PlayCircle size={16} /> Mark Installation Complete
                                            </button>
                                        ) : hasVisitedInstall ? (
                                            <div className="flex items-center justify-center gap-2 w-full py-2 bg-emerald-50 text-emerald-700 rounded-lg font-bold text-sm border border-emerald-100">
                                                <CheckCircle size={16} /> Installation Completed
                                            </div>
                                        ) : null
                                    )}
                                </div>
                            </div>
                        );
                    })}
                </div>
            )}

            {/* Visit Modal */}
            {activeLead && actionType && (
                <div className="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
                    <div className="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden flex flex-col max-h-[90vh]">
                        <div className="p-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center shrink-0">
                            <div>
                                <h3 className="font-bold text-slate-800 text-lg">
                                    {actionType === 'site_survey' ? 'Site Survey' : 'Installation Completion'}
                                </h3>
                                <p className="text-xs text-slate-500 font-mono">Lead: {activeLead.ulid.slice(-8)}</p>
                            </div>
                            <button onClick={closeModal} className="w-8 h-8 flex items-center justify-center rounded-full hover:bg-slate-200 transition text-slate-500">×</button>
                        </div>
                        
                        <div className="p-6 overflow-y-auto space-y-6 flex-1">
                            {/* Step 1: Geolocation */}
                            <div className="space-y-3">
                                <div className="flex items-center gap-2 font-bold text-slate-700 text-sm">
                                    <span className="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center text-xs">1</span>
                                    Acquire Geo-Location
                                </div>
                                <div className="ml-8">
                                    {location ? (
                                        <div className="flex items-center gap-2 px-4 py-3 bg-emerald-50 border border-emerald-100 rounded-xl text-emerald-700 text-sm font-bold">
                                            <CheckCircle size={16} />
                                            Lat: {location.lat.toFixed(6)}, Lng: {location.lng.toFixed(6)}
                                        </div>
                                    ) : (
                                        <button 
                                            onClick={getGeoLocation}
                                            disabled={locating}
                                            className="flex items-center gap-2 px-5 py-2.5 bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-200 rounded-xl font-bold text-sm cursor-pointer disabled:opacity-50"
                                        >
                                            {locating ? <Loader2 className="w-4 h-4 animate-spin" /> : <MapPin className="w-4 h-4" />}
                                            Get Current Location
                                        </button>
                                    )}
                                </div>
                            </div>

                            {/* Step 2: Selfie */}
                            <div className="space-y-3">
                                <div className="flex items-center gap-2 font-bold text-slate-700 text-sm">
                                    <span className="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center text-xs">2</span>
                                    Capture Site Selfie
                                </div>
                                <div className="ml-8">
                                    {selfie ? (
                                        <div className="space-y-2">
                                            <div className="w-full h-48 bg-slate-100 rounded-xl overflow-hidden border border-slate-200 relative group">
                                                <img src={URL.createObjectURL(selfie)} alt="Selfie" className="w-full h-full object-cover" />
                                                <label className="absolute inset-0 bg-black/50 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 cursor-pointer font-bold transition">
                                                    Retake Photo
                                                    <input type="file" accept="image/*" capture="user" className="hidden" onChange={e => { if (e.target.files?.[0]) setSelfie(e.target.files[0]) }} />
                                                </label>
                                            </div>
                                        </div>
                                    ) : (
                                        <label className="flex items-center gap-2 px-5 py-2.5 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200 rounded-xl font-bold text-sm cursor-pointer inline-flex transition">
                                            <Camera className="w-4 h-4" />
                                            Open Camera
                                            <input type="file" accept="image/*" capture="user" className="hidden" onChange={e => { if (e.target.files?.[0]) setSelfie(e.target.files[0]) }} />
                                        </label>
                                    )}
                                </div>
                            </div>

                            {/* Step 3: T&C */}
                            <div className="space-y-3 border-t border-slate-100 pt-6">
                                <div className="flex items-center gap-2 font-bold text-slate-700 text-sm">
                                    <span className="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center text-xs">3</span>
                                    Declaration
                                </div>
                                <div className="ml-8 space-y-3">
                                    <div className="bg-orange-50 text-orange-900 border border-orange-100 p-4 rounded-xl text-xs italic">
                                        {termsHtml}
                                    </div>
                                    <label className="flex items-center gap-3 cursor-pointer">
                                        <input 
                                            type="checkbox" 
                                            checked={agreed} 
                                            onChange={e => setAgreed(e.target.checked)}
                                            className="w-5 h-5 rounded border-slate-300 text-orange-600 focus:ring-orange-500" 
                                        />
                                        <span className="text-sm font-bold text-slate-700">I agree and authorize this submission</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div className="p-4 border-t border-slate-100 bg-slate-50 flex justify-end shrink-0">
                            <button
                                onClick={submitVisit}
                                disabled={visitMutation.isPending || !location || !selfie || !agreed}
                                className="flex items-center gap-2 px-8 py-3 bg-orange-500 text-white rounded-xl font-bold hover:bg-orange-600 transition disabled:opacity-50 disabled:cursor-not-allowed shadow-md shadow-orange-200"
                            >
                                {visitMutation.isPending ? <Loader2 className="w-5 h-5 animate-spin" /> : <UploadCloud className="w-5 h-5" />}
                                Submit & Save
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
