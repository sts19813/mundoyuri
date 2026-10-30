@extends('layouts.admin')
@section('title', 'Donaciones - Admin')
@section('toolbar')<div class="page-title"><h1 class="page-heading fw-bold fs-3">Donaciones</h1></div>@endsection
@section('content')
<div class="row g-5 mb-6">
    @foreach(['Intentos' => number_format($stats['attempts']), 'Pagos confirmados' => number_format($stats['successful']), 'Aportado (MXN)' => '$'.number_format($stats['gross']/100, 2), 'Reembolsado (MXN)' => '$'.number_format($stats['refunded']/100, 2), 'Comisiones disponibles (MXN)' => '$'.number_format($stats['fees']/100, 2)] as $label => $value)
        <div class="col-md"><div class="card h-100"><div class="card-body"><div class="text-muted">{{ $label }}</div><div class="fs-2 fw-bold">{{ $value }}</div></div></div></div>
    @endforeach
</div>
<p class="text-muted">Los intentos se registran al preparar el formulario de pago. Las comisiones se muestran cuando Stripe proporciona la transacción de balance; {{ $stats['missing_fees'] }} pagos aún no tienen comisión disponible. Los importes de balance pueden estar en otra moneda.</p>
<div class="card"><div class="card-body"><div class="table-responsive"><table class="table align-middle table-row-dashed"><thead><tr class="text-muted fw-bold"><th>Fecha</th><th>Usuario</th><th>Estado</th><th>Importe</th><th>Reembolso</th><th>Comisión / neto</th><th>Estrella</th><th>Mensaje personal</th><th>Referencia Stripe</th></tr></thead><tbody>
@forelse($donations as $donation)
<tr><td>{{ $donation->created_at->format('d/m/Y H:i') }}</td><td>{{ $donation->user?->displayName() ?? 'Invitada o cuenta eliminada' }}</td><td>{{ $donation->status }}</td><td>{{ strtoupper($donation->currency) }} ${{ number_format($donation->amount/100, 2) }}</td><td>${{ number_format($donation->refunded_amount/100, 2) }}</td><td>@if($donation->stripe_fee !== null){{ strtoupper($donation->balance_currency) }} ${{ number_format($donation->stripe_fee/100, 2) }} / ${{ number_format($donation->stripe_net/100, 2) }}@else Pendiente @endif</td><td>{{ $donation->show_star ? 'Sí' : 'No' }}</td><td style="white-space: pre-wrap; min-width: 180px; overflow-wrap: anywhere">{{ $donation->message ?: '—' }}</td><td>{{ $donation->stripe_payment_intent_id ?? '—' }}</td></tr>
@empty<tr><td colspan="9" class="text-muted">Todavía no hay intentos de donación.</td></tr>@endforelse
</tbody></table></div>{{ $donations->links() }}</div></div>
@endsection
