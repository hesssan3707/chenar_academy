<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PermissionController extends Controller
{
    public function index(): View
    {
        $permissions = Permission::query()->orderBy('name')->orderBy('id')->paginate(40);

        return view('admin.permissions.index', [
            'title' => 'دسترسی‌ها',
            'permissions' => $permissions,
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

    public function show(int $permission): View
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
}
