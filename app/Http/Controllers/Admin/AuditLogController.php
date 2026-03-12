<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Item;
use App\Models\Role;
use App\Models\Uom;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(
            auth()->user()->hasPermission('audit_logs.view'),
            403,
            'Anda tidak memiliki izin untuk melihat audit log.'
        );

        $query = AuditLog::with('user')
            ->when(! $request->user()->isPlatformAdmin(), fn ($builder) => $builder->where('tenant_id', $request->user()->tenant_id))
            ->orderByDesc('created_at');

        if ($request->filled('auditable_type')) {
            $query->where('auditable_type', $request->input('auditable_type'));
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $logs = $query->paginate(10)->withQueryString();

        $auditableTypes = [
            User::class,
            Role::class,
            Item::class,
            Category::class,
            Brand::class,
            Uom::class,
            Branch::class,
            Warehouse::class,
        ];

        return view('admin.audit-logs.index', compact('logs', 'auditableTypes'));
    }
}
