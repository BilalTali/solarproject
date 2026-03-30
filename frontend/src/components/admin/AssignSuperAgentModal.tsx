import React, { useState } from 'react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import type { Lead, User } from '@/types';
import { leadsApi } from '@/services/leads.api';
import { adminSuperAgentApi } from '@/services/adminSuperAgent.api';

interface Props {
    lead: Lead;
    isOpen: boolean;
    onClose: () => void;
    onSuccess: () => void;
}

export const AssignSuperAgentModal: React.FC<Props> = ({ lead, isOpen, onClose, onSuccess }) => {
    const [selectedSAId, setSelectedSAId] = useState<number | null>(null);
    const [search, setSearch] = useState('');
    const queryClient = useQueryClient();

    const { data: saData } = useQuery({
        queryKey: ['admin-super-agents-for-assign'],
        queryFn: () => adminSuperAgentApi.getSuperAgents({ per_page: 100, status: 'active' }),
        enabled: isOpen,
    });

    const superAgents: User[] = saData?.data?.data ?? [];
    const filtered = superAgents.filter(sa =>
        sa.name.toLowerCase().includes(search.toLowerCase()) ||
        (sa.super_agent_code ?? '').toLowerCase().includes(search.toLowerCase())
    );

    const mutation = useMutation({
        mutationFn: () => leadsApi.assignLeadToSuperAgent(lead.ulid, selectedSAId!),
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ['admin-leads'] });
            onSuccess();
            onClose();
        },
    });

    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
            <div className="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
                <div className="px-6 py-4 bg-gradient-to-r from-purple-600 to-purple-700 text-white">
                    <h3 className="text-lg font-semibold">Assign Lead to Super Agent</h3>
                    <p className="text-purple-200 text-sm mt-0.5">{lead.beneficiary_name} · {lead.ulid.slice(-8)}</p>
                </div>
                <div className="p-6 space-y-4">
                    <input
                        type="text"
                        placeholder="Search Super Agents..."
                        value={search}
                        onChange={e => setSearch(e.target.value)}
                        className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500"
                    />
                    <div className="max-h-60 overflow-y-auto space-y-1.5">
                        {filtered.length === 0 && (
                            <p className="text-center text-gray-400 text-sm py-4">No Super Agents found</p>
                        )}
                        {filtered.map(sa => (
                            <label
                                key={sa.id}
                                className={`flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition-all ${selectedSAId === sa.id
                                        ? 'border-purple-500 bg-purple-50'
                                        : 'border-gray-100 hover:border-purple-200 hover:bg-purple-50/40'
                                    }`}
                            >
                                <input
                                    type="radio"
                                    name="sa"
                                    className="text-purple-600"
                                    checked={selectedSAId === sa.id}
                                    onChange={() => setSelectedSAId(sa.id)}
                                />
                                <div className="flex-1 min-w-0">
                                    <p className="font-medium text-gray-800 text-sm">{sa.name}</p>
                                    <p className="text-xs text-gray-500">{sa.super_agent_code} · {sa.district}</p>
                                </div>
                            </label>
                        ))}
                    </div>
                    {mutation.isError && (
                        <p className="text-red-600 text-sm">{(mutation.error as { message: string })?.message}</p>
                    )}
                </div>
                <div className="px-6 pb-6 flex gap-3 justify-end">
                    <button onClick={onClose} className="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 font-medium">Cancel</button>
                    <button
                        onClick={() => mutation.mutate()}
                        disabled={!selectedSAId || mutation.isPending}
                        className="px-5 py-2 bg-purple-600 text-white rounded-lg text-sm font-semibold disabled:opacity-50 hover:bg-purple-700 transition-colors"
                    >
                        {mutation.isPending ? 'Assigning…' : 'Assign Super Agent'}
                    </button>
                </div>
            </div>
        </div>
    );
};
