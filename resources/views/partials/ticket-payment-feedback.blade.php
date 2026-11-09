@if(session('payment_updated'))
    <div class="purchase-success payment-feedback" role="status">{{ session('payment_updated') }}</div>
@endif
@if($errors->any())
    <div class="purchase-error-summary payment-feedback" role="alert" tabindex="-1"><strong>Pembayaran belum dapat diproses.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
