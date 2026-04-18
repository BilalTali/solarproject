import { useState } from 'react';
import { FileText, X, FileCheck, Ticket, UploadCloud } from 'lucide-react';
import toast from 'react-hot-toast';

interface Props {
    isOpen: boolean;
    onClose: () => void;
    onSubmit: (formData: FormData) => void;
    isPending: boolean;
    targetStatus: string;
    targetStatusLabel: string;
}

export function StatusTransitionModal({ isOpen, onClose, onSubmit, isPending, targetStatus, targetStatusLabel }: Props) {
    const [feasibilityReport, setFeasibilityReport] = useState<File | null>(null);
    const [eToken, setEToken] = useState<File | null>(null);
    const [additionalDocument, setAdditionalDocument] = useState<File | null>(null);
    const [notes, setNotes] = useState('');

    if (!isOpen) return null;

    const handleSubmit = () => {
        if (!feasibilityReport) {
            toast.error("Feasibility Report is required to register at MNRE.");
            return;
        }
        if (!eToken) {
            toast.error("E-Token is required to register at MNRE.");
            return;
        }

        const fd = new FormData();
        fd.append('status', targetStatus);
        
        if (notes) fd.append('notes', notes);
        
        fd.append('feasibility_report', feasibilityReport);
        fd.append('e_token', eToken);
        if (additionalDocument) {
            fd.append('additional_document', additionalDocument);
        }

        onSubmit(fd);
    };

    return (
        <div 
            className="fixed inset-0 z-50 flex justify-center items-center p-4 bg-black/60 backdrop-blur-sm"
            onClick={(e) => { e.stopPropagation(); onClose(); }}
        >
            <div 
                className="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden animate-in zoom-in-95 duration-200"
                onClick={e => e.stopPropagation()}
            >
                <div className="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-indigo-50">
                    <div>
                        <h3 className="font-bold text-slate-800 flex items-center gap-2">
                            <UploadCloud className="text-indigo-600" size={18} />
                            Upload Required Documents
                        </h3>
                        <p className="text-xs text-indigo-700 mt-1 font-medium">To change status to "{targetStatusLabel}"</p>
                    </div>
                    <button disabled={isPending} onClick={onClose} className="p-2 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors disabled:opacity-50">
                        <X size={20} />
                    </button>
                </div>

                <div className="p-6 space-y-6">
                    <p className="text-xs text-slate-600 bg-yellow-50 p-3 rounded-lg border border-yellow-100 italic">
                        The system requires the following MNRE documents to proceed with registration. Please upload them below.
                    </p>

                    <div className="space-y-4">
                        <div className="space-y-1.5 p-3 bg-slate-50 border border-slate-200 rounded-lg">
                            <label className="text-[11px] font-bold text-slate-700 uppercase flex items-center gap-1.5">
                                <FileCheck size={12} className="text-blue-500" /> Feasibility Report <span className="text-red-500">*</span>
                            </label>
                            <input 
                                type="file" 
                                accept=".pdf,.jpg,.jpeg,.png"
                                onChange={e => setFeasibilityReport(e.target.files?.[0] || null)}
                                className="text-[11px] text-slate-600 block w-full file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-[10px] file:font-semibold file:bg-blue-100 file:text-blue-700 hover:file:bg-blue-200 focus:outline-none"
                            />
                        </div>

                        <div className="space-y-1.5 p-3 bg-slate-50 border border-slate-200 rounded-lg">
                            <label className="text-[11px] font-bold text-slate-700 uppercase flex items-center gap-1.5">
                                <Ticket size={12} className="text-amber-500" /> E-Token <span className="text-red-500">*</span>
                            </label>
                            <input 
                                type="file" 
                                accept=".pdf,.jpg,.jpeg,.png"
                                onChange={e => setEToken(e.target.files?.[0] || null)}
                                className="text-[11px] text-slate-600 block w-full file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-[10px] file:font-semibold file:bg-amber-100 file:text-amber-700 hover:file:bg-amber-200 focus:outline-none"
                            />
                        </div>

                        <div className="space-y-1.5 p-3 bg-slate-50 border border-slate-200 rounded-lg">
                            <label className="text-[11px] font-bold text-slate-700 uppercase flex items-center gap-1.5">
                                <FileText size={12} className="text-slate-500" /> Application Acknowledgement <span className="text-[10px] font-normal text-slate-400 capitalize">(Optional)</span>
                            </label>
                            <input 
                                type="file" 
                                accept=".pdf,.jpg,.jpeg,.png"
                                onChange={e => setAdditionalDocument(e.target.files?.[0] || null)}
                                className="text-[11px] text-slate-600 block w-full file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-[10px] file:font-semibold file:bg-slate-200 file:text-slate-700 hover:file:bg-slate-300 focus:outline-none"
                            />
                        </div>

                        <div className="space-y-1.5 pt-2">
                            <label className="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Status Change Notes</label>
                            <textarea
                                rows={2}
                                value={notes}
                                onChange={e => setNotes(e.target.value)}
                                placeholder="Optional note for this status change…"
                                className="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none"
                            />
                        </div>
                    </div>
                </div>

                <div className="p-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
                    <button 
                        disabled={isPending}
                        onClick={onClose} 
                        className="px-4 py-2 border border-slate-200 text-slate-600 text-xs font-bold rounded-lg hover:bg-slate-100 transition-colors disabled:opacity-50"
                    >
                        Cancel
                    </button>
                    <button 
                        disabled={isPending || !feasibilityReport || !eToken}
                        onClick={handleSubmit} 
                        className="px-6 py-2 bg-indigo-600 text-white text-xs font-bold rounded-lg hover:bg-indigo-700 transition-colors disabled:opacity-50 shadow-sm"
                    >
                        {isPending ? 'Uploading & Saving...' : 'Upload & Change Status'}
                    </button>
                </div>
            </div>
        </div>
    );
}
