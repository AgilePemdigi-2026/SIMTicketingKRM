@extends('layouts.app')
@section('title', 'Dashboard Petugas - SIMTicketingKRM')
@push('styles') @include('partials.ticket-payment-styles') @endpush
@section('content')
<div class="purchase-page krm-container"><div class="purchase-heading"><h1>Dashboard Petugas</h1><p>SIMTicketingKRM</p></div><section class="krm-card payment-card"><h2>Pembayaran tiket</h2><p>Periksa bukti QRIS dan konfirmasikan pembayaran cash.</p><a class="krm-btn krm-btn-primary" href="{{ route('ticket-payment.staff.index') }}">Verifikasi Pembayaran</a></section><div class="purchase-actions"><form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="krm-btn">Logout</button></form></div></div>
@endsection
