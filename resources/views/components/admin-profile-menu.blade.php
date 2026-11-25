@php
    $profileUser = auth()->user();
    $profileName = $profileUser->name;
    $profileInitial = mb_strtoupper(mb_substr($profileName, 0, 1));
@endphp

<details id="admin-profile-menu"
    class="group relative shrink-0 border-l border-[#DCE5DE] pl-2 sm:pl-4">

    <summary aria-label="Menu profil administrator"
        class="flex cursor-pointer list-none items-center gap-2 sm:gap-3 [&::-webkit-details-marker]:hidden">

        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#E8F1EA] text-base font-semibold text-[#174B38]">
            {{ $profileInitial }}
        </span>

        <span class="hidden sm:block">

            <span class="block max-w-[150px] truncate text-xs font-semibold text-[#173D30]">
                {{ $profileName }}
            </span>

            <span class="mt-1 block text-[10px] text-[#819387]">
                Administrator
            </span>

        </span>

        <x-admin-icon name="chevron"
            class="h-3 w-3 text-[#527A65] transition-transform group-open:rotate-180"/>

    </summary>

    <section aria-label="Pengaturan akun administrator"
        class="absolute right-0 top-full z-[60] mt-3 w-[250px] max-w-[calc(100vw-2rem)] border border-[#D8E2DA] bg-white text-[#173D30] shadow-xl">

        <div class="border-b border-[#D8E2DA] p-4">

            <p class="break-words text-xs font-semibold">
                {{ $profileName }}
            </p>

            <p class="mt-1 break-all text-[10px] text-[#74877B]">
                {{ $profileUser->email }}
            </p>

        </div>

        <div class="p-2">

            <a href="{{ route('admin.profile.show') }}"
                class="flex items-center gap-3 px-3 py-3 text-xs hover:bg-[#E9F0EB]">
                <x-admin-icon name="users" class="h-4 w-4 text-[#527A65]"/>
                Profil Saya
            </a>

            <a href="{{ route('admin.profile.edit') }}"
                class="flex items-center gap-3 px-3 py-3 text-xs hover:bg-[#E9F0EB]">
                <x-admin-icon name="file" class="h-4 w-4 text-[#527A65]"/>
                Edit Profil
            </a>

            <a href="{{ route('admin.profile.password.edit') }}"
                class="flex items-center gap-3 px-3 py-3 text-xs hover:bg-[#E9F0EB]">
                <x-admin-icon name="lock" class="h-4 w-4 text-[#527A65]"/>
                Ubah Password
            </a>

        </div>



    </section>

</details>
