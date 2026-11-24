@extends('layouts.admin')

@section('title', 'Notifikasi - Kebun Raya Mangrove')

@section('content')

<div class="mx-auto max-w-[1200px]">

    <header class="mb-5 border-b border-[#B8C9BD] pb-4">

        <p class="text-[10px] font-semibold uppercase tracking-[0.12em] text-[#527A65]">
            Administrasi Kebun Raya Mangrove
        </p>

        <h1 class="mt-2 text-2xl font-medium text-[#0B372D]">
            Notifikasi
        </h1>

        <p class="mt-2 text-xs text-[#74877B]">
            Riwayat aktivitas operasional Kebun Raya Mangrove.
        </p>

    </header>

    @if (session('success'))
        <div role="status" class="mb-4 border border-[#CBD9CF] bg-[#E9F0EB] p-3 text-xs text-[#174B38]">
            {{ session('success') }}
        </div>
    @endif

    <section class="border border-[#D8E2DA] bg-white">

        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[#D8E2DA] p-4">

            <div>
                <h2 class="text-sm font-semibold text-[#173D30]">
                    Semua Notifikasi
                </h2>

                <p class="mt-1 text-xs text-[#74877B]">
                    {{ $unreadCount }} notifikasi belum dibaca
                </p>
            </div>

            @if ($unreadCount > 0)
                <form method="POST" action="{{ route('admin.notifications.read-all') }}">
                    @csrf

                    <button type="submit"
                        class="border border-[#174B38] px-3 py-2 text-xs font-semibold text-[#174B38] hover:bg-[#E9F0EB]">
                        Tandai semua dibaca
                    </button>
                </form>
            @endif

        </div>

        @forelse ($notifications as $notification)

            <form method="POST"
                action="{{ route('admin.notifications.read', ['auditLog' => $notification->id]) }}">

                @csrf

                <button type="submit"
                    class="flex w-full items-start gap-3 border-b border-[#E0E8E1] p-4 text-left hover:bg-[#F3F6F3] {{ $notification->notification_read_at === null ? 'bg-[#F0F6F0]' : 'bg-white' }}">

                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#E4EFE6] text-[#174B38]">
                        <x-admin-icon name="bell" class="h-5 w-5"/>
                    </span>

                    <span class="min-w-0 flex-1">

                        <span class="block text-sm font-semibold text-[#173D30]">
                            {{ \App\Services\AdminNotificationService::ACTIONS[$notification->action] }}
                        </span>

                        <span class="mt-1 block break-words text-xs leading-5 text-[#527A65]">
                            {{ $notification->description }}
                        </span>

                        <span class="mt-2 block text-[10px] text-[#74877B]">
                            {{ $notification->created_at?->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB
                        </span>

                        @if ($notification->notification_read_at === null)
                            <span class="mt-1 block text-[10px] font-semibold text-[#174B38]">
                                Belum dibaca
                            </span>
                        @endif

                    </span>

                    <x-admin-icon name="arrow" class="h-4 w-4 shrink-0 text-[#527A65]"/>

                </button>

            </form>

        @empty

            <div class="flex flex-col items-center py-12 text-center">
                <x-admin-icon name="bell" class="h-8 w-8 text-[#82988B]"/>

                <p class="mt-3 text-sm font-semibold text-[#174B38]">
                    Belum ada notifikasi
                </p>

                <p class="mt-2 text-xs text-[#74877B]">
                    Aktivitas operasional baru akan muncul di sini.
                </p>
            </div>

        @endforelse

    </section>

    <div class="mt-4">
        {{ $notifications->links() }}
    </div>

</div>

@endsection
