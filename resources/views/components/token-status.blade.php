@php
    use App\Helpers\TokenHelper;
    $tokenInfo = TokenHelper::getTokenInfo();
@endphp

@if($tokenInfo)
<div class="text-xs text-gray-500 p-2 bg-gray-50 rounded border border-gray-200 mb-4">
    @php
        $timeRemaining = $tokenInfo['timeRemaining'];
        $minutes = floor($timeRemaining / 60);
        $seconds = $timeRemaining % 60;
    @endphp
    
    <div class="flex justify-between items-center">
        <span>Status: <strong class="text-green-600">{{ $tokenInfo['isExpired'] ? 'Refreshing...' : 'Active' }}</strong></span>
        <span>Sisa waktu: <strong>{{ $minutes }}m {{ $seconds }}s</strong></span>
    </div>
</div>
@endif
