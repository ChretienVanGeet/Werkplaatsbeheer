<div>
    @if($model->id)
        <div class="space-y-4">
            <flux:heading class="mb-2">{{ __('Notes') }}</flux:heading>

            {{-- Notes list --}}
            <div class="space-y-2">
                @forelse ($notes as $note)

                    <flux:card size="sm" class="hover:bg-zinc-50 dark:hover:bg-zinc-700">
                        <flux:heading class="flex justify-between items-start">
                            <div class="text-xs text-zinc-700 dark:text-zinc-200">
                                {{__('Updated')}}: {{ $note->updated_at->format('d M Y H:i') }} {{ __('by') }}
                                @if($note->updater)
                                    <flux:tooltip toggleable>
                                        <flux:button class="cursor-pointer pl-1 pr-1" size="xs" variant="ghost" >{{ $note->updater->name }}</flux:button>
                                        <flux:tooltip.content class="max-w-[20rem] space-y-2">
                                            <flux:button href="{{ route('users.edit', $note->updater->id) }}" target="_blank" size="xs" icon="user" class="w-full">{{ $note->updater->name }}</flux:button>
                                            <br />
                                            <flux:button href="mailto:{{ $note->updater->email }}" size="xs" icon="envelope" class="w-full">{{ $note->updater->email }}</flux:button>
                                            <br />
                                            <flux:button href="tel:{{ $note->updater->mobile }}" size="xs" icon="phone" class="w-full">{{ $note->updater->mobile }}</flux:button>
                                        </flux:tooltip.content>
                                    </flux:tooltip>
                                @endif
                                <br />
                                {{__('Created')}}: {{ $note->created_at->format('d M Y H:i') }} {{ __('by') }}
                                @if($note->creator)
                                    <flux:tooltip toggleable>
                                        <flux:button class="cursor-pointer pl-1 pr-1" size="xs" variant="ghost" >{{ $note->creator->name }}</flux:button>
                                        <flux:tooltip.content class="max-w-[20rem] space-y-2">
                                            <flux:button href="{{ route('users.edit', $note->creator->id) }}" target="_blank" size="xs" icon="user" class="w-full">{{ $note->creator->name }}</flux:button>
                                            <br />
                                            <flux:button href="mailto:{{ $note->creator->email }}" size="xs" icon="envelope" class="w-full">{{ $note->creator->email }}</flux:button>
                                            <br />
                                            <flux:button href="tel:{{ $note->creator->mobile }}" size="xs" icon="phone" class="w-full">{{ $note->creator->mobile }}</flux:button>
                                        </flux:tooltip.content>
                                    </flux:tooltip>
                                @endif
                            </div>
                            <div class="flex gap-1 items-center">
                                @if(!$readOnly)
                                    <flux:button
                                        icon="pencil"
                                        size="sm"
                                        variant="primary"
                                        color="blue"
                                        :wire:click="'edit(' . $note->id . ')'"
                                    />
                                    <flux:button
                                        icon="trash"
                                        size="sm"
                                        variant="danger"
                                        color="red"
                                        :wire:click="'confirmDeleteNote(' . $note->id . ')'"
                                    />
                                @endif
                            </div>
                        </flux:heading>
                        <flux:heading size="lg">{{ $note->subject }}</flux:heading>
                        <flux:text class="mt-2">{!! $note->content !!}</flux:text>
                        <div class="flex flex-wrap items-start gap-2 mt-2">
                            @foreach ($note->attachments as $attachment)
                                <div class="flex items-center justify-between p-2 rounded-md bg-gray-100">
                                    <div class="flex items-center gap-2 text-sm text-gray-700">
                                        <flux:icon name="paper-clip" class="text-gray-500" />{{ $attachment->display_name ?? $attachment->original_name }}
                                    </div>

                                    <div class="flex items-center gap-1 ml-2">

                                        <flux:button
                                            icon="arrow-down-tray"
                                            size="xs"
                                            variant="outline"
                                            color="blue"
                                            wire:target="_blank"
                                            href="{{ Storage::disk('public')->url($attachment->file_path) }}"
                                        />

                                        @if(!$readOnly)

                                            <flux:button
                                                icon="trash"
                                                size="xs"
                                                variant="outline"
                                                color="red"
                                                wire:click="confirmDeleteNoteAttachment({{ $attachment->id }})"
                                            />

                                       @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </flux:card>
                @empty
                    <flux:card size="sm" class="hover:bg-zinc-50 dark:hover:bg-zinc-700 relative">
                        <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
                        <flux:text class="text-gray-500">{{ __('No notes yet.') }}</flux:text>
                    </flux:card>
                @endforelse
            </div>

            @if(!$readOnly)
                {{-- Note input (no form) --}}
                <div wire:submit.prevent="save">
                    <flux:card size="sm" class="hover:bg-zinc-50 dark:hover:bg-zinc-700 relative">
                        <flux:heading class="flex justify-between items-start">{{ $editingNote ? __('Edit existing note') : __('Add new note') }}</flux:heading>
                    <div class="flex flex-col gap-2 mt-2">
                        <flux:input wire:model.defer="subject" placeholder="{{ __('Subject...') }}"/>
                        <flux:editor wire:model.defer="content" rows="2" placeholder="{{ __('Note content...') }}"/>

                        @if($editingNote)
                        <div class="space-y-2">
                            @foreach($editingNote->attachments as $file)
                                <div class="flex flex-col sm:flex-row sm:items-center gap-2 p-3 rounded-lg border border-zinc-200 dark:border-zinc-700">
                                    <div class="flex items-center gap-2 min-w-0 sm:flex-1">
                                        <flux:icon name="document" class="w-5 h-5 text-zinc-500 shrink-0" />
                                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100 truncate">{{ $file->original_name }}</span>
                                    </div>
                                    <flux:input size="sm" type="text" placeholder="{{ __('Display name (optional)') }}" wire:model.defer="editingNotesAttachmentNames.{{ $file->id }}" class="sm:max-w-xs custom-placeholder" />
                                    <flux:button
                                        icon="x-mark"
                                        size="sm"
                                        variant="ghost"
                                        wire:click="confirmDeleteNoteAttachment({{ $file->id }})"
                                        class="shrink-0"
                                    />
                                </div>
                            @endforeach
                        </div>
                        @endif

                        @if(count($files) > 0)
                        <div class="space-y-2">
                            @foreach($files as $i => $file)
                                <div class="flex flex-col sm:flex-row sm:items-center gap-2 p-3 rounded-lg border border-zinc-200 dark:border-zinc-700">
                                    <div class="flex items-center gap-2 min-w-0 sm:flex-1">
                                        <flux:icon name="document" class="w-5 h-5 text-zinc-500 shrink-0" />
                                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100 truncate">{{ $file->getClientOriginalName() }}</span>
                                    </div>
                                    <flux:input size="sm" type="text" placeholder="{{ __('Display name (optional)') }}" wire:model.defer="fileDisplayNames.{{ $i }}" class="sm:max-w-xs custom-placeholder" />
                                    <flux:button
                                        icon="x-mark"
                                        size="sm"
                                        variant="ghost"
                                        wire:click="removeFile({{ $i }})"
                                        class="shrink-0"
                                    />
                                </div>
                            @endforeach
                        </div>
                        @endif

                        <div wire:key="file-wrapper-{{ $editingNote?->id ?? 'new' }}" class="file-upload-validation">
                            <label class="flex flex-col items-center justify-center w-full p-6 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                <div class="flex flex-col items-center justify-center gap-2 text-center">
                                    <flux:icon name="cloud-arrow-up" class="w-8 h-8 text-gray-400" />
                                    <div>
                                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Drop files here or click to browse') }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('Allowed files: PDF, Word, Excel, PowerPoint, images, text, archives. Max size: 5MB per file.') }}</p>
                                    </div>
                                </div>
                                <input
                                    type="file"
                                    wire:model="newFiles"
                                    multiple
                                    accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.jpg,.jpeg,.pjpeg,.png,.gif,.webp,.zip,.rar"
                                    class="hidden"
                                />
                            </label>
                        </div>

                        <div class="flex flex-row-reverse gap-1">
                            <flux:button
                                wire:click="save"
                                size="sm"
                                variant="primary"
                                type="button"
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-50"
                            >
                                <span wire:loading.remove wire:target="save">{{ $editingNote ? __('Update note') : __('Add note') }}</span>
                                <span wire:loading wire:target="save">{{ __('Saving...') }}</span>
                            </flux:button>
                            @if ($editingNote)
                                <flux:button wire:click="cancelEdit" size="sm" variant="primary" type="button" >{{ __('Cancel') }}</flux:button>
                            @endif
                        </div>
                    </div>
                    </flux:card>
                </div>

                {{-- Error messages --}}
                @error('subject')
                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                @enderror

                @error('content')
                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                @enderror

                @if ($errors->has('files.*'))
                    <div class="bg-red-50 border border-red-200 rounded-md p-3 mt-2">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">{{ __('File Upload Errors') }}</h3>
                                <div class="mt-2 text-sm text-red-700">
                                    <ul class="list-disc pl-5 space-y-1">
                                        @foreach ($errors->get('files.*') as $error)
                                            @if(is_array($error))
                                                @foreach($error as $subError)
                                                    <li>{{ $subError }}</li>
                                                @endforeach
                                            @else
                                                <li>{{ $error }}</li>
                                            @endif
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- File upload progress indicator --}}
                <div wire:loading wire:target="newFiles" class="bg-blue-50 border border-blue-200 rounded-md p-3 mt-2">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="animate-spin h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">{{ __('Processing files...') }}</p>
                        </div>
                    </div>
                </div>

                <x-modals.confirm-delete name="confirm-note-attachment-delete" submitAction="deleteNoteAttachment" actionType="button" :modelName="__('Note attachment')" />
                <x-modals.confirm-delete name="confirm-note-delete" submitAction="deleteNote" actionType="button" :modelName="__('Note')" />
            @endif
        </div>
    @else
{{--        Parent not saved, so cannot add notes--}}
    @endif

    {{-- Include file upload validation --}}
    <x-file-upload-validation selector=".file-upload-validation" />
</div>
