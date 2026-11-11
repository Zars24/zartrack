@props(['status' => 'pending', 'label' => null])
<span {{ $attributes->merge(['class' => "badge-status st-$status"]) }}>
    <span class="dot"></span>{{ $label ?? config('zartrack.status_labels.'.$status, ucfirst($status)) }}
</span>
