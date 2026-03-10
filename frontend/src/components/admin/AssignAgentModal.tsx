import React, { useState } from 'react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import type { Lead, User } from '@/types';
import { leadsApi } from '@/api/leads.api';
import { agentsApi } from '@/api/agents.api';

interface Props {
    lead: Lead;
    isOpen: boolean;
    onClose: () => void;
    onSuccess: () => void;
}

export const AssignAgentModal: React.FC<Props> = ({ lead, isOpen, onClose, onSuccess }) => {
    const [selectedAgentId, setSelectedAgentId] = useState<number | null>(null);
    const [search, setSearch] = useState('');
    const queryClient = useQueryClient();

    const { data: agentData } = useQuery({
        queryKey: ['admin-agents-for-assign'],
        queryFn: () => agentsApi.getAgents({ per_page: 100, status: 'active' }),
        enabled: isOpen,
    });

    const agents: User[] = agentData?.data?.data ?? [];
    const filtered = agents.filter(a =>
        a.name.toLowerCase().includes(search.toLowerCase()) ||
        (a.agent_id ?? '').toLowerCase().includes(search.toLowerCase())
    );

    const mutation = useMutation({
        mutationFn: () => leadsApi.assignLeadToAgent(lead.ulid, selectedAgentId!),
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
                <div className="px-6 py-4 bg-gradient-to-r from-teal-600 to-teal-700 text-white">
                    <h3 className="text-lg font-semibold">Assign Lead Directly to Agent</h3>
                    <p className="text-teal-200 text-sm mt-0.5">{lead.beneficiary_name} · {lead.ulid.slice(-8)}</p>
                </div>
                <div className="p-6 space-y-4">
                    <div className="flex items-start gap-2 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                        <span className="text-amber-500 mt-0.5">⚠</span>
                        <p className="text-amber-700 text-xs">This skips Super Agent assignment. Use for agents without a Super Agent or exceptional cases.</p>
                    </div>
                    <input
                        type="text"
                        placeholder="Search agents..."
                        value={search}
                        onChange={e => setSearch(e.target.value)}
                        className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
                    />
                    <div className="max-h-60 overflow-y-auto space-y-1.5">
                        {filtered.length === 0 && (
                            <p className="text-center text-gray-400 text-sm py-4">No agents found</p>
                        )}
                        {filtered.map(agent => (
                            <label
                                key={agent.id}
                                className={`flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition-all ${selectedAgentId === agent.id
                                        ? 'border-teal-500 bg-teal-50'
                                        : 'border-gray-100 hover:border-teal-200 hover:bg-teal-50/40'
                                    }`}
                            >
                                <input
                                    type="radio"
                                    name="agent"
                                    className="text-teal-600"
                                    checked={selectedAgentId === agent.id}
                                    onChange={() => setSelectedAgentId(agent.id)}
                                />
                                <div className="flex-1 min-w-0">
                                    <p className="font-medium text-gray-800 text-sm">{agent.name}</p>
                                    <p className="text-xs text-gray-500">
                                        {agent.agent_id}
                                        {agent.super_agent && <span className="ml-1 text-purple-500">· SA: {agent.super_agent.super_agent_code}</span>}
                                        {agent.district && <span className="ml-1">· {agent.district}</span>}
                                    </p>
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
                        disabled={!selectedAgentId || mutation.isPending}
                        className="px-5 py-2 bg-teal-600 text-white rounded-lg text-sm font-semibold disabled:opacity-50 hover:bg-teal-700 transition-colors"
                    >
                        {mutation.isPending ? 'Assigning…' : 'Assign Agent'}
                    </button>
                </div>
            </div>
        </div>
    );
};
