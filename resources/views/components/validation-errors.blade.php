@if ($errors->any())
    <div class="validation-errors" style="margin-bottom: 20px; padding: 16px; background-color: var(--color-danger-light, #fee2e2); border: 1px solid var(--color-danger, #dc2626); border-radius: 8px;">
        <div style="margin-bottom: 12px; font-weight: 600; color: #991b1b;">خطاهای اعتبارسنجی</div>
        <ul style="margin: 0; padding-left: 20px; color: #991b1b;">
            @foreach ($errors->all() as $error)
                <li style="margin-bottom: 8px;">{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
