@props([
    'id' => uniqid(),
    'height' => 8,
    'width' => 8
])

<svg {{ $attributes }} fill="none">
    <defs>
        <pattern id="pattern-{{ $id }}" x="0" y="0" width="{{ $width }}" height="{{ $height }}" patternUnits="userSpaceOnUse">
            <path d="M-1 5L5 -1M3 9L8.5 3.5" stroke-width="0.5"></path>
        </pattern>
    </defs>
    <rect stroke="none" fill="url(#pattern-{{ $id }})" width="100%" height="100%"></rect>
</svg>
