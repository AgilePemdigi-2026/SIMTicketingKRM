@php
    $notificationService = app(\App\Services\AdminNotificationService::class);
    $adminNotifications = $notificationService->latest(auth()->user());
    $adminUnreadCount = $notificationService->unreadCount(auth()->user());
@endphp

<details id="admin-notification-menu"
    class="group relative shrink-0">

    <summary
        aria-label="Notifikasi Admin, {{ $adminUnreadCount }} belum dibaca"
        class="relative flex h-10 w-10 cursor-pointer list-none items-center justify-center border border-[#DCE5DE] text-[#174B38] hover:bg-[#E9F0EB] [&::-webkit-details-marker]:hidden">

        <x-admin-icon name="bell" class="h-5 w-5"/>

        @if ($adminUnreadCount > 0)
            <span class="absolute -right-1.5 -top-1.5 flex min-h-[17px] min-w-[17px] items-center justify-center rounded-full bg-[#B84646] px-1 text-[9px] font-bold text-white">
                {{ $adminUnreadCount > 99 ? '99+' : $adminUnreadCount }}
            </span>
        @endif

    </summary>

    <section
        aria-label="Notifikasi terbaru"
        class="fixed inset-x-3 top-[120px] z-[60] border border-[#D8E2DA] bg-white text-[#173D30] shadow-xl sm:absolute sm:inset-x-auto sm:right-0 sm:top-full sm:mt-3 sm:w-[340px]">

        <div class="border-b border-[#D8E2DA] p-4">

            <div class="flex items-center justify-between gap-2">
                <h2 class="text-sm font-bold">
                    Notifikasi
                </h2>

                <span class="bg-[#E9F0EB] px-2 py-1 text-[10px] text-[#527A65]">
                    {{ $adminUnreadCount }} belum dibaca
                </span>
            </div>

            @if ($adminUnreadCount > 0)
                <form method="POST"
                    action="{{ route('admin.notifications.read-all') }}"
                    class="mt-3">

                    @csrf

                    <button type="submit"
                        class="text-[11px] font-semibold text-[#174B38] hover:underline">
                        Tandai semua telah dibaca
                    </button>

                </form>
            @endif

        </div>

        <div class="max-h-[50dvh] overflow-y-auto overscroll-contain">

            @forelse ($adminNotifications as $notification)

                <form method="POST"
                    action="{{ route('admin.notifications.read', ['auditLog' => $notification->id]) }}">

                    @csrf

                    <button type="submit"
                        class="flex w-full items-start gap-3 border-b border-[#E0E8E1] px-4 py-3 text-left hover:bg-[#E9F0EB] {{ $notification->notification_read_at === null ? 'bg-[#F0F6F0]' : 'bg-white' }}">

                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#E4EFE6] text-[#174B38]">
                            <x-admin-icon name="history" class="h-4 w-4"/>
                        </span>

                        <span class="min-w-0 flex-1">

                            <span class="block text-[11px] font-semibold">
                                {{ \App\Services\AdminNotificationService::ACTIONS[$notification->action] }}
                            </span>

                            <span class="mt-1 block break-words text-[10px] leading-4 text-[#74877B]">
                                {{ $notification->description }}
                            </span>

                            <span class="mt-1.5 block text-[9px] text-[#819187]">
                                {{ $notification->created_at?->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB
                            </span>

                        </span>

                        @if ($notification->notification_read_at === null)
                            <span aria-label="Belum dibaca" class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-[#527A65]"></span>
                        @endif

                    </button>

                </form>

            @empty

                <div class="px-4 py-8 text-center">
                    <p class="text-xs font-semibold">
                        Belum ada notifikasi
                    </p>

                    <p class="mt-2 text-[10px] text-[#74877B]">
                        Aktivitas operasional akan muncul di sini.
                    </p>
                </div>

            @endforelse

        </div>

        <a href="{{ route('admin.notifications.index') }}"
            class="block p-3 text-center text-xs font-semibold text-[#174B38] hover:bg-[#E9F0EB]">
            Lihat Semua Notifikasi
        </a>

    </section>

</details>
