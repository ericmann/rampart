@props(['status'])

@php
$colors = [
    'open' => 'bg-blue-50 text-blue-700 dark:bg-blue-900 dark:text-blue-200',
    'pending' => 'bg-amber-50 text-amber-700 dark:bg-amber-900 dark:text-amber-200',
    'resolved' => 'bg-green-50 text-green-700 dark:bg-green-900 dark:text-green-200',
    'closed' => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize '.($colors[$status] ?? $colors['closed'])]) }}>
    {{ $status }}
</span>
