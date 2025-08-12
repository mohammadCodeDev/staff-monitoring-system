<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __("Update your account's profile information.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <!-- First Name -->
        <div>
            <x-input-label for="first_name" :value="__('First Name')" />
            <x-text-input id="first_name" name="first_name" type="text" class="mt-1 block w-full" :value="old('first_name', $user->first_name)" required autofocus autocomplete="given-name" />
            <x-input-error class="mt-2" :messages="$errors->get('first_name')" />
        </div>


        <!-- Last Name -->
        <div>
            <x-input-label for="last_name" :value="__('Last Name')" />
            <x-text-input id="last_name" name="last_name" type="text" class="mt-1 block w-full" :value="old('last_name', $user->last_name)" required autocomplete="family-name" />
            <x-input-error class="mt-2" :messages="$errors->get('last_name')" />
        </div>

        <!-- User Name -->
        <div>
            <x-input-label for="userName" :value="__('Username')" />
            <x-text-input id="userName" name="userName" type="text" class="mt-1 block w-full" :value="old('userName', $user->userName)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('userName')" />
        </div>

        <!-- Phone Number -->
        <div>
            <x-input-label for="phoneNumber" :value="__('Phone Number')" />
            <x-text-input id="phoneNumber" name="phoneNumber" type="text" class="mt-1 block w-full" :value="old('phoneNumber', $user->phoneNumber)" required autocomplete="tel" />
            <x-input-error class="mt-2" :messages="$errors->get('phoneNumber')" />
        </div>

        <!-- Role -->
        <div>
            <x-input-label for="role" :value="__('Role')" />
            <x-text-input id="role" name="role" type="text" class="mt-1 block w-full bg-gray-100 dark:bg-gray-900" :value="__($user->role->role_name)" disabled readonly />
        </div>


        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
            <p
                x-data="{ show: true }"
                x-show="show"
                x-transition
                x-init="setTimeout(() => show = false, 2000)"
                class="text-sm text-gray-600 dark:text-gray-400">{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>