@props([
    'href',
    'label',
    'icon' => 'plus',
])

<flux:button
    size="sm"
    href="{{ $href }}"
    icon="{{ $icon }}"
    variant="primary"
    aria-label="{{ $label }}"
    tooltip="{{ $label }}"
    class="justify-center gap-0 sm:gap-2 [&>span]:hidden sm:[&>span]:inline"
>
    {{ $label }}
</flux:button>
