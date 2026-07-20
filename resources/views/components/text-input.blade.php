@props(['disabled' => false])

<input
    {{ $disabled ? 'disabled' : '' }}
    {!! $attributes->merge([
        'class' =>
            'bg-white text-gray-900 placeholder-gray-400 ' .
            'border border-gray-300 rounded-md shadow-sm ' .
            'focus:border-blue-500 focus:ring-blue-500 ' .
            'disabled:bg-gray-100 disabled:text-gray-500'
    ]) !!}
>
