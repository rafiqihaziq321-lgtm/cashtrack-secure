@foreach(['success', 'status'] as $kind)
    @if(session($kind))
        <details class="ct-feedback" open
                 x-data="{ timer: null, init() { this.start(); }, start() { this.stop(); if (!this.$el.open) return; this.timer = setTimeout(() => { if (!this.$el.contains(document.activeElement) && !this.$el.matches(':hover')) this.$el.open = false; }, 8000); }, stop() { clearTimeout(this.timer); }, destroy() { this.stop(); } }"
                 x-on:mouseenter="stop()" x-on:mouseleave="start()" x-on:focusin="stop()" x-on:focusout="start()" x-on:toggle="$el.open ? start() : stop()">
            <summary>@if($kind === 'success')<flux:icon.check />@else<flux:icon.clock />@endif<span>{{ $kind === 'success' ? 'Success' : 'Notification' }}</span><span class="ct-feedback-hint">View message</span><span class="ct-feedback-hide">Hide</span></summary>
            <p role="status" aria-live="polite" aria-atomic="true">@include('planb.message', ['message' => session($kind)])</p>
        </details>
    @endif
@endforeach
