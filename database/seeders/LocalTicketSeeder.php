<?php

namespace Database\Seeders;

use App\Models\LocalDummyTicket;
use App\Models\User;
use App\Support\LocalTicketDatabase;
use Illuminate\Database\QueryException;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LocalTicketSeeder extends Seeder
{
    public function run(): void
    {
        $guard = app(LocalTicketDatabase::class);
        $guard->assertConfiguration();
        $passwords = $guard->passwords();
        $connection = $guard->connection();

        try {
            $connection->transaction(function () use ($passwords) {
                foreach ([
                    ['admin', 'admin.testing@example.com', 'Admin Demo', 'DEMO-ADMIN-001', '080000000001'],
                    ['wisatawan', 'tester.wisatawan@example.com', 'Wisatawan Demo', 'DEMO-WISATAWAN-001', '080000000002'],
                    ['petugas', 'petugas.testing@example.com', 'Petugas Demo', 'DEMO-PETUGAS-001', '080000000003'],
                ] as [$role, $email, $name, $identity, $phone]) {
                    $user = User::on('sqlite')->firstOrNew(['email' => $email]);
                    $user->fill([
                        'name' => $name,
                        'identity_number' => $identity,
                        'phone' => $phone,
                        'visitor_type' => 'domestik',
                    ]);
                    $user->role = $role;
                    if (! $user->exists || ! Hash::check($passwords[$role], $user->password)) {
                        $user->password = Hash::make($passwords[$role]);
                    }
                    $user->save();
                }

                foreach ([
                    ['dummy:masuk', '[DUMMY] Tiket Masuk', 'masuk', true, 25000, 15000, 50000, 30000],
                    ['dummy:wahana', '[DUMMY] Tiket Wahana', 'wahana', true, 20000, 10000, 40000, 20000],
                    ['dummy:nonaktif', '[DUMMY] Tiket Nonaktif', 'masuk', false, 25000, 15000, 50000, 30000],
                ] as [$code, $name, $category, $active, $adult, $child, $foreignAdult, $foreignChild]) {
                    $ticket = LocalDummyTicket::updateOrCreate(['code' => $code], [
                        'name' => $name, 'category' => $category, 'is_active' => $active,
                    ]);
                    foreach ([
                        'domestik' => [$adult, $child],
                        'mancanegara' => [$foreignAdult, $foreignChild],
                    ] as $type => [$adultPrice, $childPrice]) {
                        $ticket->rates()->updateOrCreate(['visitor_type' => $type], [
                            'adult_price' => $adultPrice, 'child_price' => $childPrice,
                        ]);
                    }
                }
            });
        } catch (QueryException) {
            // Direct db:seed must not print SQL bindings containing password hashes.
            throw new \RuntimeException('Seeding demo gagal karena konflik data/schema lokal. Perubahan akun dan katalog dibatalkan.');
        }
    }
}
