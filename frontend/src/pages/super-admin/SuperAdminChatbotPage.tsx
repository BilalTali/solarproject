import { useState, useEffect } from 'react';
import { useAuthStore } from '@/hooks/store/authStore';
import { 
    chatbotApi, WaChatbotCategory, WaRegistrationField, WaChatbotSession, WaHandler 
} from '@/services/chatbot.api';
import { faqApi, FAQ } from '@/services/faqs.api';
import { 
    MessageSquare, List, Phone, Users, Plus, Save, Trash2, Edit2, ShieldAlert,
    ExternalLink, CheckCircle, Activity, RotateCcw
} from 'lucide-react';
import { Link } from 'react-router-dom';

export default function SuperAdminChatbotPage() {
    const { user } = useAuthStore();
    const isSuperAdmin = user?.role === 'super_admin';
    const [activeTab, setActiveTab] = useState('categories');
    
    return (
        <div className="space-y-6 animate-fade-in">
            <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 className="text-2xl font-bold text-gray-900 tracking-tight flex items-center gap-2">
                        <MessageSquare className="w-8 h-8 text-green-600 p-1.5 bg-green-100 rounded-lg" />
                        WhatsApp Chatbot Configuration
                    </h1>
                    <p className="text-gray-500 mt-1">Manage interactive WhatsApp flows, Q&A, and registration steps.</p>
                </div>
            </div>

            <div className="bg-white/80 backdrop-blur-md border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
                <div className="flex overflow-x-auto border-b border-gray-100">
                    {[
                        { id: 'categories', label: 'Menu Categories', icon: List },
                        { id: 'registration', label: 'Registration Form', icon: Plus },
                        { id: 'contacts', label: 'Support Contacts', icon: Phone },
                        { id: 'sessions', label: 'Session Logs', icon: Activity },
                        ...(isSuperAdmin ? [{ id: 'handlers', label: 'Lead Handlers (Super Admin)', icon: ShieldAlert }] : []),
                    ].map(tab => (
                        <button
                            key={tab.id}
                            onClick={() => setActiveTab(tab.id)}
                            className={`flex items-center gap-2 px-6 py-4 font-medium text-sm whitespace-nowrap transition-colors border-b-2 origin-left ${
                                activeTab === tab.id 
                                ? 'border-green-600 text-green-600 bg-green-50/50' 
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50'
                            }`}
                        >
                            <tab.icon className="w-4 h-4" />
                            {tab.label}
                        </button>
                    ))}
                </div>

                <div className="p-6">
                    {activeTab === 'categories' && <CategoriesTab />}
                    {activeTab === 'registration' && <RegistrationTab />}
                    {activeTab === 'contacts' && <ContactsTab />}
                    {activeTab === 'sessions' && <SessionsTab />}
                    {activeTab === 'handlers' && isSuperAdmin && <HandlersTab />}
                </div>
            </div>
        </div>
    );
}

// ── Categories Tab ────────────────────────────────────────────────────────
function CategoriesTab() {
    const [categories, setCategories] = useState<WaChatbotCategory[]>([]);
    const [faqs, setFaqs] = useState<FAQ[]>([]);
    const [loading, setLoading] = useState(true);
    const [editingCat, setEditingCat] = useState<Partial<WaChatbotCategory>>({});
    const [isSaving, setIsSaving] = useState(false);

    useEffect(() => {
        loadData();
    }, []);

    const loadData = async () => {
        try {
            const [catsRes, faqsRes] = await Promise.all([
                chatbotApi.getCategories(),
                faqApi.getFaqs()
            ]);
            setCategories(catsRes.data);
            setFaqs(faqsRes.data);
        } catch (e) {
            console.error(e);
        } finally {
            setLoading(false);
        }
    };

    const handleSave = async () => {
        if (!editingCat.name) return;
        setIsSaving(true);
        try {
            if (editingCat.id) {
                await chatbotApi.updateCategory(editingCat.id, editingCat);
            } else {
                await chatbotApi.createCategory(editingCat);
            }
            setEditingCat({});
            loadData();
        } catch (e) {
            alert('Failed to save category');
        } finally {
            setIsSaving(false);
        }
    };

    const handleDelete = async (id: number) => {
        if (!confirm('Are you sure you want to delete this category?')) return;
        try {
            await chatbotApi.deleteCategory(id);
            loadData();
        } catch (e) {
            alert('Failed to delete category');
        }
    };

    const toggleActive = async (id: number) => {
        try {
            await chatbotApi.toggleCategory(id);
            loadData();
        } catch (e) {
            console.error(e);
        }
    };

    if (loading) return <div>Loading...</div>;

    return (
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div className="lg:col-span-1">
                <div className="bg-gray-50 border border-gray-200 rounded-xl p-5 sticky top-6">
                    <h3 className="font-semibold text-lg mb-4">{editingCat.id ? 'Edit Category' : 'Add New Category'}</h3>
                    <div className="space-y-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Name (Internal link to FAQs) *</label>
                            <input 
                                type="text" 
                                value={editingCat.name || ''} 
                                onChange={e => setEditingCat({...editingCat, name: e.target.value})}
                                className="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                                placeholder="e.g. Subsidy"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Description (Shown on WA)</label>
                            <input 
                                type="text" 
                                value={editingCat.description || ''} 
                                onChange={e => setEditingCat({...editingCat, description: e.target.value})}
                                className="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                                placeholder="e.g. Amounts and details"
                            />
                        </div>
                        <div className="flex gap-4">
                            <div className="flex-1">
                                <label className="block text-sm font-medium text-gray-700 mb-1">Emoji</label>
                                <input 
                                    type="text" 
                                    value={editingCat.icon_emoji || ''} 
                                    onChange={e => setEditingCat({...editingCat, icon_emoji: e.target.value})}
                                    className="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                                    placeholder="💰"
                                />
                            </div>
                            <div className="flex-[2]">
                                <label className="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                                <input 
                                    type="number" 
                                    value={editingCat.sort_order || 0} 
                                    onChange={e => setEditingCat({...editingCat, sort_order: parseInt(e.target.value)})}
                                    className="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                                />
                            </div>
                        </div>
                        {!editingCat.id && (
                            <div className="flex items-center gap-2">
                                <input type="checkbox" id="active" checked={editingCat.is_active !== false} onChange={e => setEditingCat({...editingCat, is_active: e.target.checked})} className="rounded text-green-600 focus:ring-green-500" />
                                <label htmlFor="active" className="text-sm font-medium text-gray-700">Active</label>
                            </div>
                        )}
                        <div className="flex gap-2 pt-2">
                            <button 
                                onClick={handleSave} 
                                disabled={isSaving || !editingCat.name}
                                className="flex-1 bg-green-600 text-white py-2 rounded-lg font-medium hover:bg-green-700 disabled:opacity-50 transition-colors"
                            >
                                {isSaving ? 'Saving...' : 'Save Category'}
                            </button>
                            {editingCat.id && (
                                <button 
                                    onClick={() => setEditingCat({})}
                                    className="px-4 bg-gray-200 text-gray-700 py-2 rounded-lg font-medium hover:bg-gray-300 transition-colors"
                                >
                                    Cancel
                                </button>
                            )}
                        </div>
                    </div>
                </div>
            </div>

            <div className="lg:col-span-2 space-y-4">
                <div className="flex justify-between items-center bg-blue-50 text-blue-800 p-4 rounded-xl border border-blue-100">
                    <p className="text-sm">Categories map to FAQ topics. Users select a category to view matched questions.</p>
                    <Link to="/super-admin/help-center" className="text-sm font-medium bg-white px-3 py-1.5 rounded-lg border border-blue-200 shadow-sm hover:bg-blue-50 transition-colors flex items-center gap-1">
                        Manage Q&A <ExternalLink className="w-3 h-3" />
                    </Link>
                </div>

                {categories.length === 0 ? (
                    <div className="text-center py-12 text-gray-500">No categories added yet.</div>
                ) : (
                    <div className="grid gap-4">
                        {categories.map(cat => {
                            const catFaqs = faqs.filter(f => f.category === cat.name);
                            return (
                                <div key={cat.id} className="border border-gray-200 rounded-xl overflow-hidden bg-white shadow-sm hover:shadow-md transition-shadow">
                                    <div className="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                                        <div className="flex items-center gap-3">
                                            <span className="text-2xl">{cat.icon_emoji}</span>
                                            <div>
                                                <h4 className="font-bold text-gray-900">{cat.name}</h4>
                                                <p className="text-sm text-gray-500">{cat.description}</p>
                                            </div>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <button 
                                                onClick={() => toggleActive(cat.id)}
                                                className={`px-3 py-1 text-xs font-medium rounded-full ${cat.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600'}`}
                                            >
                                                {cat.is_active ? 'Active' : 'Hidden'}
                                            </button>
                                            <button onClick={() => setEditingCat(cat)} className="p-2 text-gray-400 hover:text-blue-600 transition-colors">
                                                <Edit2 className="w-4 h-4" />
                                            </button>
                                            <button onClick={() => handleDelete(cat.id)} className="p-2 text-gray-400 hover:text-red-600 transition-colors">
                                                <Trash2 className="w-4 h-4" />
                                            </button>
                                        </div>
                                    </div>
                                    <div className="p-4 bg-gray-50 text-sm">
                                        <div className="font-medium text-gray-600 mb-2">{catFaqs.length} Related FAQs Linked:</div>
                                        {catFaqs.length > 0 ? (
                                            <ul className="list-disc pl-5 space-y-1 text-gray-600 text-xs">
                                                {catFaqs.slice(0, 3).map(f => (
                                                    <li key={f.id}>{f.question}</li>
                                                ))}
                                                {catFaqs.length > 3 && <li className="text-blue-600 italic">+ {catFaqs.length - 3} more questions...</li>}
                                            </ul>
                                        ) : (
                                            <div className="text-orange-600 text-xs flex items-center gap-1">
                                                <ShieldAlert className="w-3 h-3" /> No FAQs found matching this category name.
                                            </div>
                                        )}
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                )}
            </div>
        </div>
    );
}

// ── Registration Tab ───────────────────────────────────────────────────────
function RegistrationTab() {
    const [fields, setFields] = useState<WaRegistrationField[]>([]);
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);

    useEffect(() => {
        chatbotApi.getRegistrationFields().then(res => {
            setFields(res.data);
            setLoading(false);
        });
    }, []);

    const handleSave = async () => {
        setSaving(true);
        try {
            await chatbotApi.setRegistrationFields(fields);
            alert('Saved successfully!');
        } catch (e) {
            alert('Error saving fields');
        } finally {
            setSaving(false);
        }
    };

    const addField = () => {
        setFields([
            ...fields, 
            { key: `field_${Date.now()}`, label: 'New Field', required: false, order: fields.length + 1, type: 'text' }
        ]);
    };

    const updateField = (index: number, updates: Partial<WaRegistrationField>) => {
        const newFields = [...fields];
        newFields[index] = { ...newFields[index], ...updates };
        setFields(newFields);
    };

    if (loading) return <div>Loading...</div>;

    return (
        <div className="max-w-3xl">
            <div className="flex justify-between items-center mb-6">
                <p className="text-gray-600 text-sm">Configure the exact questions asked when a user registers on WhatsApp.</p>
                <button 
                    onClick={handleSave}
                    disabled={saving}
                    className="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-lg font-medium flex items-center gap-2 transition-colors"
                >
                    <Save className="w-4 h-4" /> {saving ? 'Saving...' : 'Save Configuration'}
                </button>
            </div>

            <div className="space-y-3">
                {fields.sort((a,b)=>a.order - b.order).map((field, index) => (
                    <div key={field.key} className="flex gap-4 items-center bg-gray-50 border border-gray-200 p-4 rounded-xl group">
                        <div className="w-12 text-center text-gray-400 font-medium">#{field.order}</div>
                        <div className="flex-1">
                            <input 
                                type="text" 
                                value={field.label}
                                onChange={e => updateField(index, { label: e.target.value })}
                                className="w-full bg-white border border-gray-300 rounded-md shadow-sm px-3 py-1.5 focus:ring-green-500 focus:border-green-500"
                            />
                        </div>
                        <div className="w-32">
                            <span className="text-xs font-mono bg-gray-200 px-2 py-1 rounded text-gray-600">{field.key}</span>
                        </div>
                        <div className="w-24 flex items-center justify-center">
                            <label className="flex items-center gap-2 cursor-pointer">
                                <input 
                                    type="checkbox" 
                                    checked={field.required}
                                    onChange={e => updateField(index, { required: e.target.checked })}
                                    className="rounded text-green-600 focus:ring-green-500" 
                                />
                                <span className="text-sm text-gray-600">Req.</span>
                            </label>
                        </div>
                        <button 
                            onClick={() => setFields(fields.filter((_, i) => i !== index))}
                            className="text-gray-400 hover:text-red-600 opacity-0 group-hover:opacity-100 transition-opacity p-2"
                        >
                            <Trash2 className="w-4 h-4" />
                        </button>
                    </div>
                ))}
            </div>

            <button 
                onClick={addField}
                className="mt-4 flex items-center justify-center gap-2 w-full border-2 border-dashed border-gray-300 py-3 rounded-xl text-gray-500 hover:bg-gray-50 hover:text-green-600 transition-colors font-medium text-sm"
            >
                <Plus className="w-4 h-4" /> Add Custom Field
            </button>

            <div className="mt-8 pt-6 border-t border-gray-200">
                <h4 className="font-semibold text-gray-800 mb-3">Fixed System Steps (Always appended)</h4>
                <div className="space-y-2 opacity-70">
                    <div className="flex gap-4 items-center bg-gray-50 border border-gray-200 p-3 rounded-lg">
                        <div className="w-12 text-center text-gray-400 font-medium">Doc</div>
                        <div className="flex-1 text-sm font-medium">Request Electricity Bill Photo</div>
                    </div>
                    <div className="flex gap-4 items-center bg-gray-50 border border-gray-200 p-3 rounded-lg">
                        <div className="w-12 text-center text-gray-400 font-medium">Doc</div>
                        <div className="flex-1 text-sm font-medium">Request Aadhaar Card Photo</div>
                    </div>
                    <div className="flex gap-4 items-center bg-gray-50 border border-gray-200 p-3 rounded-lg">
                        <div className="w-12 text-center text-gray-400 font-medium">System</div>
                        <div className="flex-1 text-sm font-medium">Ask for Agent Referral Code</div>
                    </div>
                </div>
            </div>
        </div>
    );
}

// ── Contacts Tab ────────────────────────────────────────────────────────
function ContactsTab() {
    const [allUsers, setAllUsers] = useState<any[]>([]);
    const [loading, setLoading] = useState(true);
    const [toggling, setToggling] = useState<number | null>(null);
    const [search, setSearch] = useState('');

    const load = async () => {
        try {
            const res = await chatbotApi.getAllContacts();
            setAllUsers(res.data);
        } catch (e) {
            console.error(e);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => { load(); }, []);

    const handleToggle = async (id: number) => {
        setToggling(id);
        try {
            const res = await chatbotApi.toggleContact(id);
            setAllUsers(prev =>
                prev.map(u => u.id === id ? { ...u, is_public_contact: res.data.is_public_contact } : u)
                    .sort((a, b) => (b.is_public_contact ? 1 : 0) - (a.is_public_contact ? 1 : 0))
            );
        } catch (e) {
            console.error(e);
        } finally {
            setToggling(null);
        }
    };

    const roleBadge = (role: string) => {
        const map: Record<string, string> = {
            super_admin: 'bg-purple-100 text-purple-700',
            admin: 'bg-blue-100 text-blue-700',
            super_agent: 'bg-orange-100 text-orange-700',
            agent: 'bg-green-100 text-green-700',
            enumerator: 'bg-gray-100 text-gray-700',
        };
        return map[role] || 'bg-gray-100 text-gray-600';
    };

    const roleLabel = (role: string) => role.replace('_', ' ').replace(/\b\w/g, c => c.toUpperCase());

    const filtered = allUsers.filter(u =>
        u.name.toLowerCase().includes(search.toLowerCase()) ||
        u.role.toLowerCase().includes(search.toLowerCase()) ||
        (u.district || '').toLowerCase().includes(search.toLowerCase())
    );

    const active = filtered.filter(u => u.is_public_contact);
    const inactive = filtered.filter(u => !u.is_public_contact);

    if (loading) return <div className="py-8 text-center text-gray-500">Loading users...</div>;

    return (
        <div className="max-w-4xl space-y-5">
            {/* Info Banner */}
            <div className="bg-green-50 text-green-800 p-4 border border-green-100 rounded-xl flex items-start gap-3">
                <CheckCircle className="w-5 h-5 flex-shrink-0 mt-0.5 text-green-600" />
                <div>
                    <p className="font-semibold text-sm mb-1">How does this work?</p>
                    <p className="text-sm opacity-90">
                        Users toggled <strong>ON</strong> here will appear as support contacts on the public WhatsApp float button.
                        The first enabled contact's WhatsApp number is used for the <em>Apply</em> and <em>FAQ</em> bot flows.
                        Only users with a WhatsApp number are shown.
                    </p>
                </div>
            </div>

            {/* Search */}
            <div className="relative">
                <input
                    type="text"
                    placeholder="Search by name, role or district..."
                    value={search}
                    onChange={e => setSearch(e.target.value)}
                    className="w-full pl-4 pr-4 py-2.5 rounded-xl border border-gray-200 bg-white shadow-sm text-sm focus:outline-none focus:ring-2 focus:ring-green-500/30 focus:border-green-500"
                />
            </div>

            {/* Active contacts */}
            {active.length > 0 && (
                <div>
                    <h3 className="text-sm font-bold text-gray-500 uppercase tracking-widest mb-3 flex items-center gap-2">
                        <CheckCircle className="w-4 h-4 text-green-600" /> Active Contacts ({active.length})
                    </h3>
                    <div className="space-y-2">
                        {active.map(u => (
                            <div key={u.id} className="flex items-center justify-between p-4 bg-green-50 border border-green-100 rounded-xl">
                                <div className="flex items-center gap-3">
                                    <div className="w-10 h-10 rounded-full bg-green-200 flex items-center justify-center text-green-800 font-bold text-sm">
                                        {u.name.charAt(0).toUpperCase()}
                                    </div>
                                    <div>
                                        <div className="font-semibold text-gray-900">{u.name}</div>
                                        <div className="flex items-center gap-2 mt-0.5 flex-wrap">
                                            <span className={`text-[10px] font-bold px-2 py-0.5 rounded-full ${roleBadge(u.role)}`}>{roleLabel(u.role)}</span>
                                            {u.district && <span className="text-xs text-gray-400">{u.district}{u.state ? `, ${u.state}` : ''}</span>}
                                            <a href={`https://wa.me/91${u.whatsapp_number}`} target="_blank" rel="noreferrer" className="text-xs text-green-600 hover:underline flex items-center gap-1">
                                                <ExternalLink className="w-3 h-3" /> +91{u.whatsapp_number}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <button
                                    onClick={() => handleToggle(u.id)}
                                    disabled={toggling === u.id}
                                    className="px-4 py-1.5 bg-red-50 text-red-600 hover:bg-red-100 rounded-lg text-xs font-bold border border-red-100 transition-colors disabled:opacity-50"
                                >
                                    {toggling === u.id ? '...' : 'Remove'}
                                </button>
                            </div>
                        ))}
                    </div>
                </div>
            )}

            {/* Available users */}
            {inactive.length > 0 && (
                <div>
                    <h3 className="text-sm font-bold text-gray-500 uppercase tracking-widest mb-3 flex items-center gap-2">
                        <Users className="w-4 h-4 text-gray-400" /> Available Users ({inactive.length})
                    </h3>
                    <div className="space-y-2">
                        {inactive.map(u => (
                            <div key={u.id} className="flex items-center justify-between p-4 bg-white border border-gray-200 rounded-xl hover:border-green-200 hover:bg-green-50/30 transition-colors">
                                <div className="flex items-center gap-3">
                                    <div className="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 font-bold text-sm">
                                        {u.name.charAt(0).toUpperCase()}
                                    </div>
                                    <div>
                                        <div className="font-semibold text-gray-700">{u.name}</div>
                                        <div className="flex items-center gap-2 mt-0.5 flex-wrap">
                                            <span className={`text-[10px] font-bold px-2 py-0.5 rounded-full ${roleBadge(u.role)}`}>{roleLabel(u.role)}</span>
                                            {u.district && <span className="text-xs text-gray-400">{u.district}{u.state ? `, ${u.state}` : ''}</span>}
                                            <span className="text-xs text-gray-300">+91{u.whatsapp_number}</span>
                                        </div>
                                    </div>
                                </div>
                                <button
                                    onClick={() => handleToggle(u.id)}
                                    disabled={toggling === u.id}
                                    className="px-4 py-1.5 bg-green-50 text-green-700 hover:bg-green-100 rounded-lg text-xs font-bold border border-green-200 transition-colors disabled:opacity-50"
                                >
                                    {toggling === u.id ? '...' : '+ Add'}
                                </button>
                            </div>
                        ))}
                    </div>
                </div>
            )}

            {filtered.length === 0 && (
                <div className="text-center py-10 text-gray-400 text-sm">
                    No matching users found. Make sure they have a WhatsApp number set in their profile.
                </div>
            )}
        </div>
    );
}

// ── Sessions Tab ────────────────────────────────────────────────────────
function SessionsTab() {
    const [sessions, setSessions] = useState<WaChatbotSession[]>([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        chatbotApi.getSessions(1).then(res => {
            setSessions(res.data.data);
            setLoading(false);
        });
    }, []);

    if (loading) return <div>Loading logs...</div>;

    return (
        <div>
            <div className="bg-white border text-sm border-gray-200 rounded-xl overflow-hidden shadow-sm">
                <table className="w-full text-left">
                    <thead className="bg-gray-50 text-gray-600 font-medium">
                        <tr>
                            <th className="px-5 py-3 border-b border-gray-200">Phone</th>
                            <th className="px-5 py-3 border-b border-gray-200">State</th>
                            <th className="px-5 py-3 border-b border-gray-200">Last Active</th>
                            <th className="px-5 py-3 border-b border-gray-200">Context</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100">
                        {sessions.map(s => (
                            <tr key={s.id} className="hover:bg-gray-50">
                                <td className="px-5 py-3 font-mono text-gray-700">+{s.wa_phone.substring(0, 4)}••••{s.wa_phone.substring(8)}</td>
                                <td className="px-5 py-3">
                                    <span className={`px-2 py-1 rounded-md text-xs font-semibold ${
                                        s.state === 'done' ? 'bg-green-100 text-green-700' : 
                                        s.state === 'register' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700'
                                    }`}>
                                        {s.state.toUpperCase()}
                                    </span>
                                </td>
                                <td className="px-5 py-3 text-gray-500 text-xs">{new Date(s.last_message_at).toLocaleString()}</td>
                                <td className="px-5 py-3 text-xs text-gray-400 font-mono break-all max-w-[200px]">
                                    {s.context ? JSON.stringify(s.context).substring(0, 50) + '...' : 'none'}
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </div>
    );
}

// ── Handlers Tab ───────────────────────────────────────────────────────
function HandlersTab() {
    const [handlers, setHandlers] = useState<WaHandler[]>([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        const fetchHandlers = async () => {
            try {
                const res = await chatbotApi.getWaHandlers();
                setHandlers(res.data);
            } catch (e) {
                console.error(e);
            } finally {
                setLoading(false);
            }
        };
        fetchHandlers();
    }, []);

    const toggleHandler = async (id: number) => {
        try {
            await chatbotApi.toggleWaHandler(id);
            const res = await chatbotApi.getWaHandlers();
            setHandlers(res.data);
        } catch (e) {
            console.error(e);
        }
    };

    const resetCache = async () => {
        if (!confirm('Reset round robin counters to 0?')) return;
        try {
            await chatbotApi.resetCounters();
            const res = await chatbotApi.getWaHandlers();
            setHandlers(res.data);
        } catch (e) {
            console.error(e);
        }
    };

    if (loading) return <div>Loading handlers...</div>;

    const activeHandlers = handlers.filter(h => h.is_wa_lead_handler);
    const availableAdmins = handlers.filter(h => !h.is_wa_lead_handler);

    return (
        <div className="max-w-4xl space-y-8">
            <div className="flex justify-between items-center bg-gray-900 text-white p-6 rounded-2xl shadow-md">
                <div>
                    <h3 className="text-xl font-bold mb-1">WhatsApp Lead Handlers</h3>
                    <p className="text-gray-400 text-sm">Designate which Admins receive orphaned WhatsApp Leads automatically via Round-Robin.</p>
                </div>
                <button onClick={resetCache} className="bg-gray-800 hover:bg-gray-700 border border-gray-700 text-white px-4 py-2 rounded-lg font-medium text-sm flex items-center gap-2 transition-colors">
                    <RotateCcw className="w-4 h-4 text-green-400" /> Reset Counters
                </button>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div className="bg-white border border-gray-200 rounded-xl overflow-hidden">
                    <div className="bg-green-50 border-b border-green-100 p-4 font-semibold text-green-800 flex items-center gap-2">
                        <CheckCircle className="w-5 h-5 text-green-600" /> Active Handlers ({activeHandlers.length})
                    </div>
                    <div className="divide-y divide-gray-100">
                        {activeHandlers.map(h => (
                            <div key={h.id} className="p-4 flex items-center justify-between hover:bg-gray-50">
                                <div>
                                    <div className="font-semibold text-gray-900">{h.name}</div>
                                    <div className="text-xs text-gray-500 mt-1">Leads handled: <span className="font-bold text-gray-900">{h.wa_lead_round_robin_counter}</span></div>
                                </div>
                                <button onClick={() => toggleHandler(h.id)} className="text-red-500 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg text-xs font-semibold">
                                    Remove
                                </button>
                            </div>
                        ))}
                    </div>
                </div>

                <div className="bg-white border border-gray-200 rounded-xl overflow-hidden">
                    <div className="bg-gray-50 border-b border-gray-200 p-4 font-semibold text-gray-800 flex items-center gap-2">
                        <Users className="w-5 h-5 text-gray-500" /> Available Admins ({availableAdmins.length})
                    </div>
                    <div className="divide-y divide-gray-100 max-h-96 overflow-y-auto">
                        {availableAdmins.map(h => (
                            <div key={h.id} className="p-4 flex items-center justify-between hover:bg-gray-50">
                                <div>
                                    <div className="font-semibold text-gray-700">{h.name}</div>
                                </div>
                                <button onClick={() => toggleHandler(h.id)} className="text-green-600 bg-green-50 hover:bg-green-100 px-3 py-1.5 rounded-lg text-xs font-semibold">
                                    + Add
                                </button>
                            </div>
                        ))}
                    </div>
                </div>
            </div>
        </div>
    );
}
