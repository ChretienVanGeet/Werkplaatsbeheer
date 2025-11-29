<flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-900 dark:bg-zinc-950">
    <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

    <a href="{{ route('dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
        <x-app-logo />
    </a>

    <livewire:components.group-filter />

    <flux:navlist variant="outline">
        <flux:navlist.group :heading="__('Menu')" class="grid">
            <flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>{{ __('Dashboard') }}</flux:navlist.item>
            <flux:navlist.item icon="calendar" :href="route('activities.index')" :current="request()->routeIs('activities')" wire:navigate>{{ __('Activities') }}</flux:navlist.item>
            <flux:navlist.item icon="user" :href="route('participants.index')" :current="request()->routeIs('participants')" wire:navigate>{{ __('Participants') }}</flux:navlist.item>
            <flux:navlist.item icon="building-office-2" :href="route('companies.index')" :current="request()->routeIs('companies')" wire:navigate>{{ __('Companies') }}</flux:navlist.item>
            <flux:navlist.item icon="academic-cap" :href="route('instructors.index')" :current="request()->routeIs('instructors*')" wire:navigate>{{ __('Instructors') }}</flux:navlist.item>
            <flux:navlist.item icon="wrench-screwdriver" :href="route('resources.index')" :current="request()->routeIs('resources*')" wire:navigate>{{ __('Resources') }}</flux:navlist.item>
        </flux:navlist.group>
    </flux:navlist>


    @can('manage')
        <flux:navlist variant="outline">
            <flux:navlist.group :heading="__('Settings')" class="grid">
                <flux:navlist.item icon="numbered-list" :href="route('workflow-templates.index')" :current="request()->routeIs('workflows')" wire:navigate>{{ __('Workflow templates') }}</flux:navlist.item>
                <flux:navlist.item icon="user" :href="route('users.index')" :current="request()->routeIs('users')" wire:navigate>{{ __('Users') }}</flux:navlist.item>
                <flux:navlist.item icon="users" :href="route('groups.index')" :current="request()->routeIs('groups')" wire:navigate>{{ __('Groups') }}</flux:navlist.item>
                <flux:navlist.item icon="cog" href="/admin">{{ __('Admin') }}</flux:navlist.item>
            </flux:navlist.group>
        </flux:navlist>
    @endcan
    <flux:spacer />

    <!-- Desktop User Menu -->
    <flux:dropdown class="hidden lg:block" position="bottom" align="start">
        <flux:profile
            :name="auth()->user()->name"
            :initials="auth()->user()->initials()"
            icon:trailing="chevrons-up-down"
        />

        <flux:menu class="w-[220px]">
            <flux:menu.radio.group>
                <div class="p-0 text-sm font-normal">
                    <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                        <div class="grid flex-1 text-start text-sm leading-tight">
                            <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                            <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                        </div>
                    </div>
                </div>
            </flux:menu.radio.group>

            <flux:menu.separator />

            <flux:menu.radio.group>
                <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
            </flux:menu.radio.group>

            <flux:menu.separator />

            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                    {{ __('Log Out') }}
                </flux:menu.item>
            </form>
        </flux:menu>
    </flux:dropdown>
</flux:sidebar>

<!-- Mobile User Menu -->
<flux:header class="lg:hidden">
    <flux:sidebar.toggle class="block lg:hidden" icon="bars-2" inset="left" />

    <flux:spacer />

    <flux:dropdown position="top" align="end">
        <flux:profile
            :initials="auth()->user()->initials()"
            icon-trailing="chevron-down"
        />

        <flux:menu class="w-[calc(100vw-2rem)] max-w-[220px] min-[368px]:w-[220px]">
            <flux:menu.radio.group>
                <div class="p-0 text-sm font-normal">
                    <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                        <div class="grid flex-1 text-start text-sm leading-tight">
                            <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                            <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                        </div>
                    </div>
                </div>
            </flux:menu.radio.group>

            <flux:menu.separator />

            <flux:menu.radio.group>
                <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
            </flux:menu.radio.group>

            <flux:menu.separator />

            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                    {{ __('Log Out') }}
                </flux:menu.item>
            </form>
        </flux:menu>
    </flux:dropdown>
</flux:header>
