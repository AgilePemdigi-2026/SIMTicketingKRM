<?php

namespace App\Contracts;

interface TicketQrisGateway
{
    /**
     * Membuat transaksi QRIS melalui payment gateway.
     *
     * @return array{
     *     transaction_id: string,
     *     status: string,
     *     qr_image_url: string,
     *     qr_string: ?string
     * }
     */
    public function createQris(
        string $referenceId,
        int $amount
    ): array;
}
