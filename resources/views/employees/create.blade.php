{{-- This view can extend a master layout if you have one --}}
{{-- For example: @extends('layouts.app') --}}
{{-- @section('content') --}}

<!DOCTYPE html>
{{-- We set the language and direction based on the current app locale --}}
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'fa' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Add New Employee/Professor') }}</title>
    {{-- Basic styling --}}
    <style>
        body { font-family: sans-serif; padding: 2rem; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.5rem; }
        input, select { width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px; }
        .btn { padding: 0.75rem 1.5rem; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .error { color: red; font-size: 0.875em; margin-top: 0.25rem; }
        html[dir="rtl"] .error { text-align: right; }
    </style>
</head>
<body>

    <h1>{{ __('Add New Employee/Professor') }}</h1>

    <form action="{{ route('employees.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="first_name">{{ __('First Name') }}</label>
            <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}" required>
            @error('first_name')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="last_name">{{ __('Last Name') }}</label>
            <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}" required>
            @error('last_name')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="department_id">{{ __('Department') }}</label>
            <select id="department_id" name="department_id" required>
                <option value="">{{ __('Select a Department') }}</option>
                @foreach ($departments as $department)
                    <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                        {{ $department->name }}
                    </option>
                @endforeach
            </select>
            @error('department_id')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn">{{ __('Save Employee') }}</button>
    </form>

</body>
</html>

{{-- @endsection --}}