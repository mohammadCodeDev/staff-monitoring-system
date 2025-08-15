@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full px-4 py-2 text-left rtl:text-right text-sm font-medium text-white bg-indigo-600 rounded-md transition duration-150 ease-in-out'
            : 'block w-full px-4 py-2 text-left rtl:text-right text-sm font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-800 dark:hover:text-gray-200 rounded-md transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>