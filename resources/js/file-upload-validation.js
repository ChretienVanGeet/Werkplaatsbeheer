/**
 * General File Upload Validation
 *
 * Handles client-side file validation and error handling for any file upload component.
 * Prevents 413 errors by validating file sizes and types before upload.
 */

class FileUploadValidation {
    constructor(config = {}) {
        this.serverLimits = config.serverLimits || {};
        this.translations = config.translations || {};
        this.selector = config.selector || '.file-upload-validation';
        this.allowedTypes = config.allowedTypes || this.getDefaultAllowedTypes();
        this.initializeLimits();
        this.initializeEventListeners();
        // this.debugLimits();
    }

    /**
     * Get translated string with parameter replacement
     */
    translate(key, params = {}) {
        let translation = this.translations[key] || key;

        // Replace parameters in the translation
        Object.keys(params).forEach(param => {
            translation = translation.replace(`:${param}`, params[param]);
        });

        return translation;
    }

    initializeLimits() {
        // File size limits - match Laravel validation rules
        this.LARAVEL_MAX_FILE_SIZE = 5242880; // 5MB (5120 KB) as per Laravel validation rule
        this.SERVER_MAX_FILE_SIZE = this.serverLimits.upload_max_filesize || this.LARAVEL_MAX_FILE_SIZE;
        this.MAX_FILE_SIZE = Math.min(this.LARAVEL_MAX_FILE_SIZE, this.SERVER_MAX_FILE_SIZE);
        this.MAX_TOTAL_SIZE = this.serverLimits.recommended_total_size || 8388608; // Use 80% of post_max_size

        // Display values for error messages
        this.MAX_FILE_SIZE_MB = Math.round(this.MAX_FILE_SIZE / 1024 / 1024);
        this.LARAVEL_MAX_FILE_SIZE_MB = Math.round(this.LARAVEL_MAX_FILE_SIZE / 1024 / 1024);
    }

    getDefaultAllowedTypes() {
        return [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'text/plain',
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif',
            'application/zip',
            'application/x-rar-compressed'
        ];
    }

    debugLimits() {
        // Debug logging (remove after testing)
        console.log('File Upload Validation Debug:', {
            'Laravel Max (5MB)': this.LARAVEL_MAX_FILE_SIZE,
            'Server upload_max_filesize': this.SERVER_MAX_FILE_SIZE,
            'Effective MAX_FILE_SIZE': this.MAX_FILE_SIZE,
            'MAX_FILE_SIZE_MB': this.MAX_FILE_SIZE_MB,
            'serverLimits': this.serverLimits,
            'selector': this.selector
        });
    }

    validateFileSelection(event) {
        const files = Array.from(event.target.files);
        const errors = [];
        let totalSize = 0;

        // Skip validation if no files selected
        if (files.length === 0) {
            return true;
        }

        // Validate each file
        files.forEach((file) => {
            // Check file size
            if (file.size > this.MAX_FILE_SIZE) {
                const fileSizeMB = Math.round(file.size / 1024 / 1024 * 100) / 100;
                const limitMB = this.MAX_FILE_SIZE_MB;
                const errorMessage = this.translate('File exceeds :limitMB limit', { limitMB: limitMB });
                errors.push(`${file.name}: ${errorMessage} (${fileSizeMB}MB)`);
            }

            // Check file type
            if (!this.allowedTypes.includes(file.type)) {
                const errorMessage = this.translate('File type not allowed');
                errors.push(`${file.name}: ${errorMessage} (${file.type})`);
            }

            totalSize += file.size;
        });

        // Check total size to prevent 413 errors
        if (totalSize > this.MAX_TOTAL_SIZE) {
            const totalSizeMB = Math.round(totalSize / 1024 / 1024 * 100) / 100;
            const errorMessage = this.translate('Total file size exceeds server limit. Please upload fewer or smaller files.');
            errors.push(`${errorMessage} (${totalSizeMB}MB)`);
        }

        // If there are errors, show them and clear the input
        if (errors.length > 0) {
            this.showErrors(errors);
            event.target.value = '';
            return false;
        }

        return true;
    }

    showErrors(errors) {
        // Show error message using Flux toast
        if (window.Flux && window.Flux.toast) {
            window.Flux.toast(errors.join('\n'), { variant: 'danger' });
        } else {
            const title = this.translate('File Upload Errors');
            alert(`${title}:\n\n${errors.join('\n')}`);
        }
    }

    initializeEventListeners() {
        // Add event listeners to file inputs using event delegation
        document.addEventListener('change', (event) => {
            // Check if the changed element is a file input within our selector
            if (event.target.type === 'file' &&
                event.target.closest(this.selector) &&
                event.target.hasAttribute('wire:model')) {
                this.validateFileSelection(event);
            }
        }, true);

        // Handle Livewire upload errors (413, etc.)
        document.addEventListener('livewire:request', () => {
            window.fileUploadInProgress = true;
        });

        document.addEventListener('livewire:response', () => {
            window.fileUploadInProgress = false;
        });

        // Handle network errors including 413 (using arrow function to maintain 'this' context)
        const handleLivewireError = (event) => {
            const response = event.detail?.response;

            if (response && response.status === 413) {
                if (window.Flux && window.Flux.toast) {
                    const errorMessage = this.translate('Upload failed: Files are too large for server limits. Please upload smaller files or contact your administrator.');
                    window.Flux.toast(errorMessage, { variant: 'danger' });
                }
                event.preventDefault();
            } else if (response && (response.status >= 400 && response.status < 500)) {
                if (window.Flux && window.Flux.toast) {
                    const errorMessage = this.translate('Upload failed: Please check your files and try again.');
                    window.Flux.toast(errorMessage, { variant: 'danger' });
                }
                event.preventDefault();
            }
        };

        document.addEventListener('livewire:error', handleLivewireError);
    }

    /**
     * Static method to create validation instance from global config
     */
    static initializeFromGlobal(globalConfigName = 'fileUploadValidationConfig', selector = '.file-upload-validation') {
        const config = window[globalConfigName] || {};
        config.selector = selector;
        return new FileUploadValidation(config);
    }
}

// Export for use in other modules
window.FileUploadValidation = FileUploadValidation;

// Auto-initialize if global config is available
document.addEventListener('DOMContentLoaded', function() {
    if (window.fileUploadValidationConfig) {
        const validator = FileUploadValidation.initializeFromGlobal();
        // console.log('File upload validation initialized globally');
    }
});
