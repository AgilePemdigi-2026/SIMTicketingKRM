<?php

namespace App\Services;

use App\Contracts\TicketCatalogProvider;
use App\Models\DaftarTiket;
use App\Support\TicketPurchaseDatabase;

class MasterTicketCatalogProvider implements TicketCatalogProvider
{
    public function __construct(private TicketPurchaseDatabase $database) {}

    public function activeTickets(): array
    {
        $this->database->connection();

        return DaftarTiket::with(['wahana', 'daftarWahana'])->where('status_aktif', 1)
            ->orderBy('id_tiket')->get()->map(function (DaftarTiket $ticket) {
                $type = $this->visitorType($ticket->kategori_wisatawan);

                return [
                    'reference' => 'master:'.$ticket->id_tiket,
                    'name' => $ticket->nama_tiket,
                    'category' => $ticket->kategori_tiket,
                    'rates' => $type === null ? [] : [[
                        'visitor_type' => $type, 'adult_price' => $ticket->harga_dewasa,
                        'child_price' => $ticket->harga_anak,
                    ]],
                    'wahana' => $ticket->daftarWahana->when($ticket->wahana !== null,
                        fn ($items) => $items->push($ticket->wahana))
                        ->unique('id_wahana')->map(fn ($wahana) => [
                            'id' => $wahana->id_wahana, 'name' => $wahana->nama_wahana,
                        ])->values()->all(),
                ];
            })->all();
    }

    public function find(string $reference, string $visitorType, string $visitDate): ?array
    {
        $this->database->connection();
        if (! preg_match('/^master:([1-9][0-9]{0,18})$/D', $reference, $matches)
            || (strlen($matches[1]) === 19 && strcmp($matches[1], (string) PHP_INT_MAX) > 0)) {
            return null;
        }
        $ticket = DaftarTiket::find($matches[1]);
        if ($ticket === null || $this->visitorType($ticket->kategori_wisatawan) !== $visitorType) {
            return null;
        }

        return [
            'reference' => 'master:'.$ticket->id_tiket,
            'name' => $ticket->nama_tiket, 'category' => $ticket->kategori_tiket,
            'is_active' => (int) $ticket->status_aktif === 1,
            'adult_price' => $ticket->harga_dewasa, 'child_price' => $ticket->harga_anak,
        ];
    }

    private function visitorType(string $label): ?string
    {
        return match (mb_strtolower(trim($label))) {
            'domestik', 'domestik (wni)' => 'domestik',
            'mancanegara (wna)' => 'mancanegara',
            default => null,
        };
    }
}
