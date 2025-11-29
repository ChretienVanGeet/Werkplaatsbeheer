@props([
    'selector' => '.file-upload-validation',
    'customLimits' => [],
    'customAllowedTypes' => null,
    'includeScript' => true
])

@php
use App\Helpers\FileUploadValidationHelper;

$config = FileUploadValidationHelper::getValidationConfig($customLimits);
if ($customAllowedTypes) {
    $config['allowedTypes'] = $customAllowedTypes;
}
$config['selector'] = $selector;
@endphp

{{-- Set server limits and translations for JavaScript --}}
<script>
    window.fileUploadValidationConfig = @json($config);
</script>

@if($includeScript)
    {{-- Load the general file upload validation JavaScript --}}
    @vite('resources/js/file-upload-validation.js')
@endif