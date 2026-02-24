<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductAccess;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $scopedUserId = $request->attributes->get('adminScopedUserId');
        $q = trim((string) $request->query('q', ''));
        $kind = trim((string) $request->query('kind', 'users'));
        $kind = in_array($kind, ['users', 'admins'], true) ? $kind : 'users';

        $query = User::query()->orderByDesc('id');
        if (is_int($scopedUserId) && $scopedUserId > 0) {
            $query->where('id', $scopedUserId);
        } else {
            if ($kind === 'admins') {
                $query->whereHas('roles', fn ($roleQuery) => $roleQuery->whereIn('name', ['admin', 'super_admin']));
            } else {
                $query->whereDoesntHave('roles', fn ($roleQuery) => $roleQuery->whereIn('name', ['admin', 'super_admin']));
            }

            if ($q !== '') {
                $tokens = preg_split('/\s+/', $q) ?: [];
                $tokens = array_values(array_filter($tokens, fn ($token) => is_string($token) && trim($token) !== ''));

                foreach ($tokens as $token) {
                    $token = (string) $token;
                    $query->where(function ($sub) use ($token) {
                        $sub->where('phone', 'like', '%'.$token.'%')
                            ->orWhere('name', 'like', '%'.$token.'%');
                    });
                }
            }
        }

        $users = $query->paginate(40)->withQueryString();

        return view('admin.users.index', [
            'title' => 'کاربران',
            'users' => $users,
            'activeKind' => $kind,
        ]);
    }

    public function scopeStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'min:1', 'exists:users,id'],
        ]);

        $request->session()->put('admin_scoped_user_id', (int) $validated['user_id']);

        return back()->with('toast', [
            'type' => 'success',
            'title' => 'فیلتر فعال شد',
            'message' => 'پنل بر اساس کاربر انتخاب‌شده فیلتر شد.',
        ]);
    }

    public function scopeClear(Request $request): RedirectResponse
    {
        $request->session()->forget('admin_scoped_user_id');

        return back()->with('toast', [
            'type' => 'success',
            'title' => 'فیلتر حذف شد',
            'message' => 'فیلتر کاربر حذف شد.',
        ]);
    }

    public function create(): View
    {
        return view('admin.users.form', [
            'title' => 'ایجاد کاربر',
            'user' => new User([
                'is_active' => true,
            ]),
            'isAdmin' => false,
            'roles' => Role::query()->orderBy('name')->orderBy('id')->get(),
            'selectedRoleIds' => [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'phone' => ['required', 'string', 'max:20', Rule::unique('users', 'phone')],
            'password' => $this->passwordPolicyRules(false),
            'is_active' => ['nullable'],
            'is_admin' => ['nullable'],
            'role_ids' => ['nullable', 'array'],
            'role_ids.*' => ['integer', 'min:1', Rule::exists('roles', 'id')],
        ]);

        $user = User::query()->create([
            'name' => (string) $validated['name'],
            'phone' => (string) $validated['phone'],
            'password' => Hash::make((string) $validated['password']),
            'email' => null,
            'phone_verified_at' => now(),
            'is_active' => $request->boolean('is_active'),
        ]);

        if ($request->boolean('is_admin')) {
            $adminRoleId = $this->getAdminRoleId();
            if ($adminRoleId !== null) {
                $user->roles()->syncWithoutDetaching([$adminRoleId]);
            }
        }

        $this->syncUserRoles($request, $user, $validated);

        return redirect()->route('admin.users.edit', $user->id);
    }

    public function show(int $user): RedirectResponse
    {
        return redirect()->route('admin.users.edit', $user);
    }

    public function edit(int $user): View
    {
        $scopedUserId = request()->attributes->get('adminScopedUserId');
        if (is_int($scopedUserId) && $scopedUserId > 0 && $user !== $scopedUserId) {
            abort(404);
        }

        $userModel = User::query()->findOrFail($user);

        return view('admin.users.form', [
            'title' => 'ویرایش کاربر',
            'user' => $userModel,
            'isAdmin' => $userModel->hasRole('admin'),
            'roles' => Role::query()->orderBy('name')->orderBy('id')->get(),
            'selectedRoleIds' => $userModel->roles()->pluck('roles.id')->map(fn ($id) => (int) $id)->all(),
        ]);
    }

    public function products(Request $request, int $user): View
    {
        $scopedUserId = $request->attributes->get('adminScopedUserId');
        if (is_int($scopedUserId) && $scopedUserId > 0 && $user !== $scopedUserId) {
            abort(404);
        }

        $userModel = User::query()->findOrFail($user);

        $productQ = trim((string) $request->query('product_q', ''));
        $productQuery = Product::query()
            ->whereIn('type', ['course', 'video', 'note'])
            ->orderByDesc('id');

        if ($productQ !== '') {
            $tokens = preg_split('/\s+/', $productQ) ?: [];
            $tokens = array_values(array_filter($tokens, fn ($token) => is_string($token) && trim($token) !== ''));

            foreach ($tokens as $token) {
                $token = (string) $token;
                $productQuery->where(function ($sub) use ($token) {
                    $sub->where('title', 'like', '%'.$token.'%')
                        ->orWhere('slug', 'like', '%'.$token.'%');
                });
            }
        }

        $accesses = $userModel->productAccesses()
            ->with('product')
            ->orderByDesc('granted_at')
            ->orderByDesc('id')
            ->get();

        return view('admin.users.products', [
            'title' => 'محصولات کاربر',
            'user' => $userModel,
            'accesses' => $accesses,
            'products' => $productQuery->limit(50)->get(['id', 'type', 'title', 'slug']),
            'productQ' => $productQ,
        ]);
    }

    public function update(Request $request, int $user): RedirectResponse
    {
        $scopedUserId = $request->attributes->get('adminScopedUserId');
        if (is_int($scopedUserId) && $scopedUserId > 0 && $user !== $scopedUserId) {
            abort(404);
        }

        $userModel = User::query()->findOrFail($user);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'phone' => ['required', 'string', 'max:20', Rule::unique('users', 'phone')->ignore($userModel->id)],
            'password' => ['nullable', 'string', 'min:6', 'max:120', 'regex:'.$this->passwordPolicyRegex()],
            'is_active' => ['nullable'],
            'is_admin' => ['nullable'],
            'role_ids' => ['nullable', 'array'],
            'role_ids.*' => ['integer', 'min:1', Rule::exists('roles', 'id')],
        ]);

        $payload = [
            'name' => (string) $validated['name'],
            'phone' => (string) $validated['phone'],
            'is_active' => $request->boolean('is_active'),
        ];

        if (($validated['password'] ?? null) !== null && (string) $validated['password'] !== '') {
            $payload['password'] = Hash::make((string) $validated['password']);
        }

        $userModel->forceFill($payload)->save();

        $adminRoleId = $this->getAdminRoleId();
        if ($adminRoleId !== null) {
            if ($request->boolean('is_admin')) {
                $userModel->roles()->syncWithoutDetaching([$adminRoleId]);
            } else {
                $userModel->roles()->detach($adminRoleId);
            }
        }

        $this->syncUserRoles($request, $userModel, $validated);

        return redirect()->route('admin.users.edit', $userModel->id);
    }

    public function destroy(int $user): RedirectResponse
    {
        $scopedUserId = request()->attributes->get('adminScopedUserId');
        if (is_int($scopedUserId) && $scopedUserId > 0 && $user !== $scopedUserId) {
            abort(404);
        }

        $userModel = User::query()->findOrFail($user);
        $userModel->delete();

        return redirect()->route('admin.users.index');
    }

    public function accessStore(Request $request, int $user): RedirectResponse
    {
        $scopedUserId = $request->attributes->get('adminScopedUserId');
        if (is_int($scopedUserId) && $scopedUserId > 0 && $user !== $scopedUserId) {
            abort(404);
        }

        $userModel = User::query()->findOrFail($user);

        $validated = $request->validate([
            'product_id' => [
                'required',
                'integer',
                'min:1',
                Rule::exists('products', 'id')->whereIn('type', ['course', 'video', 'note']),
            ],
            'expires_days' => ['nullable', 'integer', 'min:1', 'max:3650'],
        ]);

        $expiresAt = null;
        if (isset($validated['expires_days']) && is_numeric($validated['expires_days'])) {
            $expiresAt = Carbon::now()->addDays((int) $validated['expires_days'])->endOfDay();
        }

        ProductAccess::query()->updateOrCreate([
            'user_id' => $userModel->id,
            'product_id' => (int) $validated['product_id'],
        ], [
            'order_item_id' => null,
            'granted_at' => now(),
            'expires_at' => $expiresAt,
            'meta' => [
                'source' => 'admin',
                'admin_user_id' => (int) ($request->user()?->id ?? 0),
            ],
        ]);

        return back()->with('toast', [
            'type' => 'success',
            'title' => 'دسترسی ثبت شد',
            'message' => 'دسترسی محصول برای کاربر ثبت شد.',
        ]);
    }

    public function accessDestroy(Request $request, int $user, int $access): RedirectResponse
    {
        $scopedUserId = $request->attributes->get('adminScopedUserId');
        if (is_int($scopedUserId) && $scopedUserId > 0 && $user !== $scopedUserId) {
            abort(404);
        }

        User::query()->findOrFail($user);

        ProductAccess::query()
            ->where('id', $access)
            ->where('user_id', $user)
            ->delete();

        return back()->with('toast', [
            'type' => 'success',
            'title' => 'دسترسی حذف شد',
            'message' => 'دسترسی محصول حذف شد.',
        ]);
    }

    private function getAdminRoleId(): ?int
    {
        $role = Role::query()->firstOrCreate(['name' => 'admin'], [
            'description' => 'Admin',
        ]);

        return $role?->id ? (int) $role->id : null;
    }

    private function syncUserRoles(Request $request, User $user, array $validated): void
    {
        $roleIds = $validated['role_ids'] ?? [];
        $roleIds = is_array($roleIds) ? $roleIds : [];
        $roleIds = array_values(array_unique(array_map(fn ($id) => (int) $id, $roleIds)));
        $roleIds = array_values(array_filter($roleIds, fn ($id) => $id > 0));

        $adminRoleId = $this->getAdminRoleId();
        if ($adminRoleId !== null) {
            if ($request->boolean('is_admin')) {
                $roleIds[] = (int) $adminRoleId;
            } else {
                $roleIds = array_values(array_filter($roleIds, fn ($id) => (int) $id !== (int) $adminRoleId));
            }
        }

        $superAdminRoleId = Role::query()->where('name', 'super_admin')->value('id');
        $superAdminRoleId = is_numeric($superAdminRoleId) ? (int) $superAdminRoleId : null;
        if (is_int($superAdminRoleId) && $superAdminRoleId > 0) {
            if ($user->hasRole('super_admin')) {
                $roleIds[] = (int) $superAdminRoleId;
            } else {
                $roleIds = array_values(array_filter($roleIds, fn ($id) => (int) $id !== (int) $superAdminRoleId));
            }
        }

        $roleIds = array_values(array_unique($roleIds));
        $user->roles()->sync($roleIds);
    }
}
