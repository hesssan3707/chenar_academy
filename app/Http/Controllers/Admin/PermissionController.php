<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PermissionController extends Controller
{
    public function index(): View
    {
        $permissions = Permission::query()->orderBy('name')->orderBy('id')->paginate(40);

        $defaultPermissionNames = array_keys($this->defaultPermissions());
        $existingDefaultNames = Permission::query()
            ->whereIn('name', $defaultPermissionNames)
            ->pluck('name')
            ->all();
        $defaultsMissing = count($existingDefaultNames) !== count($defaultPermissionNames);

        return view('admin.permissions.index', [
            'title' => 'دسترسی‌ها',
            'permissions' => $permissions,
            'defaultsMissing' => $defaultsMissing,
        ]);
    }

    public function create(): View
    {
        return view('admin.permissions.form', [
            'title' => 'ایجاد دسترسی',
            'permission' => new Permission,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePayload($request);

        $permission = Permission::query()->create($validated);

        return redirect()->route('admin.permissions.edit', $permission->id);
    }

    public function show(int $permission): RedirectResponse
    {
        return redirect()->route('admin.permissions.edit', $permission);
    }

    public function edit(int $permission): View
    {
        $permissionModel = Permission::query()->findOrFail($permission);

        return view('admin.permissions.form', [
            'title' => 'ویرایش دسترسی',
            'permission' => $permissionModel,
        ]);
    }

    public function update(Request $request, int $permission): RedirectResponse
    {
        $permissionModel = Permission::query()->findOrFail($permission);

        $validated = $this->validatePayload($request, $permissionModel);

        $permissionModel->forceFill($validated)->save();

        return redirect()->route('admin.permissions.edit', $permissionModel->id);
    }

    public function destroy(int $permission): RedirectResponse
    {
        $permissionModel = Permission::query()->findOrFail($permission);
        $permissionModel->delete();

        return redirect()->route('admin.permissions.index');
    }

    public function bootstrap(Request $request): RedirectResponse
    {
        foreach ($this->defaultPermissions() as $name => $description) {
            Permission::query()->firstOrCreate(['name' => $name], [
                'description' => $description,
            ]);
        }

        $ownerRole = Role::query()->firstOrCreate(['name' => 'owner'], [
            'description' => 'مدیر اصلی',
        ]);

        $defaultPermissionIds = Permission::query()
            ->whereIn('name', array_keys($this->defaultPermissions()))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $ownerRole->permissions()->syncWithoutDetaching($defaultPermissionIds);

        $adminUser = $request->user();
        if ($adminUser) {
            $adminUser->roles()->syncWithoutDetaching([(int) $ownerRole->id]);
        }

        return back()->with('toast', [
            'type' => 'success',
            'title' => 'انجام شد',
            'message' => 'دسترسی‌های پیش‌فرض پنل ساخته شد.',
        ]);
    }

    private function validatePayload(Request $request, ?Permission $permission = null): array
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('permissions', 'name')->ignore($permission?->id),
            ],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        return [
            'name' => trim((string) $validated['name']),
            'description' => isset($validated['description']) && $validated['description'] !== '' ? (string) $validated['description'] : null,
        ];
    }

    private function defaultPermissions(): array
    {
        return [
            'admin.users' => 'کاربران',
            'admin.categories' => 'دسته‌بندی‌ها',
            'admin.products' => 'محصولات',
            'admin.booklets' => 'جزوه‌ها',
            'admin.videos' => 'ویدیوها',
            'admin.courses' => 'دوره‌ها',
            'admin.posts' => 'مقالات',
            'admin.tickets' => 'تیکت‌ها',
            'admin.orders' => 'سفارش‌ها',
            'admin.payments' => 'پرداخت‌ها',
            'admin.coupons' => 'کدهای تخفیف',
            'admin.discounts' => 'تخفیف گروهی',
            'admin.banners' => 'بنرها',
            'admin.media' => 'رسانه',
            'admin.reviews' => 'نظرات',
            'admin.surveys' => 'نظرسنجی‌ها',
            'admin.social_links' => 'شبکه‌های اجتماعی',
            'admin.settings' => 'تنظیمات',
            'admin.roles' => 'مدیریت نقش‌ها و دسترسی‌ها',
        ];
    }
}
