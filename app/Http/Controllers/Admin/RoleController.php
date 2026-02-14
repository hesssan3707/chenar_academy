<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(): View
    {
        $roles = Role::query()->orderBy('name')->orderBy('id')->paginate(40);

        return view('admin.roles.index', [
            'title' => 'دسترسی‌ها',
            'roles' => $roles,
        ]);
    }

    public function create(): View
    {
        return view('admin.roles.form', [
            'title' => 'ایجاد نقش',
            'role' => new Role,
            'permissions' => Permission::query()->orderBy('name')->orderBy('id')->get(),
            'selectedPermissionIds' => [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePayload($request);

        $role = Role::query()->create($validated['payload']);
        $role->permissions()->sync($validated['permissionIds']);

        return redirect()->route('admin.roles.edit', $role->id);
    }

    public function show(int $role): RedirectResponse
    {
        return redirect()->route('admin.roles.edit', $role);
    }

    public function edit(int $role): View
    {
        $roleModel = Role::query()->with('permissions')->findOrFail($role);

        return view('admin.roles.form', [
            'title' => 'ویرایش نقش',
            'role' => $roleModel,
            'permissions' => Permission::query()->orderBy('name')->orderBy('id')->get(),
            'selectedPermissionIds' => $roleModel->permissions->pluck('id')->map(fn ($id) => (int) $id)->all(),
        ]);
    }

    public function update(Request $request, int $role): RedirectResponse
    {
        $roleModel = Role::query()->with('permissions')->findOrFail($role);

        $validated = $this->validatePayload($request, $roleModel);

        $roleModel->forceFill($validated['payload'])->save();
        $roleModel->permissions()->sync($validated['permissionIds']);

        return redirect()->route('admin.roles.edit', $roleModel->id);
    }

    public function destroy(int $role): RedirectResponse
    {
        $roleModel = Role::query()->findOrFail($role);
        $roleModel->delete();

        return redirect()->route('admin.roles.index');
    }

    private function validatePayload(Request $request, ?Role $role = null): array
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('roles', 'name')->ignore($role?->id),
            ],
            'description' => ['nullable', 'string', 'max:255'],
            'permission_ids' => ['nullable', 'array'],
            'permission_ids.*' => ['integer', Rule::exists('permissions', 'id')],
        ]);

        $permissionIds = $validated['permission_ids'] ?? [];
        $permissionIds = is_array($permissionIds) ? $permissionIds : [];
        $permissionIds = array_values(array_unique(array_map(fn ($id) => (int) $id, $permissionIds)));
        $permissionIds = array_values(array_filter($permissionIds, fn ($id) => $id > 0));

        return [
            'payload' => [
                'name' => trim((string) $validated['name']),
                'description' => isset($validated['description']) && $validated['description'] !== '' ? (string) $validated['description'] : null,
            ],
            'permissionIds' => $permissionIds,
        ];
    }
}
