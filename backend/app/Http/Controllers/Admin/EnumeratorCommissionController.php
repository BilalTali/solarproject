<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EnumeratorCommissionController extends Controller
{
    public function index(Request $request)
    {
        $enumeratorId = $request->user()->id;

        $commissions = \App\Models\Commission::query()
            ->with(['lead:id,ulid,beneficiary_name'])
            ->where(fn($q) => $q->where('payee_role', 'enumerator')
                                 ->where('payee_id', $enumeratorId))
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $commissions,
        ]);
    }
}
