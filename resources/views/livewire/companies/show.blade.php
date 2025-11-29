<div>
    <div class="flex justify-between">
        <div>
            <flux:heading>{{ $heading }}</flux:heading>
            <flux:text class="mt-0">{{ $subHeading  }}</flux:text>
        </div>
        <div class="flex items-center gap-2">
            <flux:button size="sm" variant="ghost" href="{{ route('companies.index') }}">{{ __('Back') }}</flux:button>
            @can('write')
                @if(!empty($editRoute))
                    <flux:button size="sm" :href="$editRoute" variant="primary" color="sky" icon="pencil"></flux:button>
                @endif
            @endcan
        </div>
    </div>
    <flux:separator class="mt-4 mb-1" />
    <livewire:components.show-groups :groups="$company->groups" />

    <div class="mb-4">
        <flux:heading>{{ __('Name') }}</flux:heading>
        <flux:text class="mt-1 mb-2">{{ $company->name }}</flux:text>
        <flux:heading>{{ __('Industry') }}</flux:heading>
        <flux:text class="mt-1 mb-2">{{ $company->industry }}</flux:text>
        <flux:heading>{{ __('Comments') }}</flux:heading>
        <flux:text class="mt-1 mb-2">{!! $company->comments !!}</flux:text>
        <flux:heading>{{ __('Locations') }}</flux:heading>
        <flux:text class="mt-1 mb-2">{!! $company->locations !!}</flux:text>
    </div>
        <div class="mb-4">
            <div class="flex justify-between">
                <div>
                    <flux:heading>{{ __('Contacts') }}</flux:heading>
                    <flux:text class="my-2">{{ __('The contacts at this company') }}</flux:text>
                </div>
            </div>

            <div class="flex flex-wrap gap-2 items-stretch">
                @forelse($company->companyContacts as $index => $companyContact)
                    <flux:card class="p-2 min-w-[250px] h-full">
                        <div class="space-y-2">
                            <div>
                                <h2>{{$companyContact->name}}</h2>
                                <small>@if($companyContact->role) {{ $companyContact->role }} @else &nbsp; @endif</small>
                                <div class="flex flex-row items-center">
                                    <span class="mr-2"><flux:icon name="phone" variant="micro" /></span><span class="mr-4 html-content">@if($companyContact->phone)<a href="tel:{{ $companyContact->phone }}">{{ $companyContact->phone }}</a>@else &nbsp; @endif</span>
                                </div>
                                <div class="flex flex-row items-center">
                                     <span class="mr-2"><flux:icon name="envelope" variant="micro" /></span><span class="mr-4 html-content">@if($companyContact->email)<a href="mailto:{{ $companyContact->email }}">{{ $companyContact->email }}</a> @else &nbsp; @endif</span>
                                </div>
                                <div class="flex flex-row items-center">
                                    <span class="mr-2"><flux:icon name="map-pin" variant="micro" /></span><span class="mr-4 html-content">@if($companyContact->location) {{ $companyContact->location }} @else &nbsp; @endif</span>
                                </div>
                            </div>
                        </div>
                    </flux:card>
                @empty
                    <flux:card size="sm" class="hover:bg-zinc-50 dark:hover:bg-zinc-700 relative">
                        <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
                        <flux:text class="text-gray-500">{{ __('There are no contacts added') }}</flux:text>
                    </flux:card>
                @endforelse
            </div>
        </div>
        <div class="mb-4">
            <livewire:components.workflows-panel :model="$company" :read-only="true" />
        </div>
        <div class="mb-4">
            <livewire:components.notes-panel :model="$company" :read-only="true" />
        </div>
        <flux:separator class="my-4" />
</div>
