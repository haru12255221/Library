@props(['messages' => [], 'field' => null])

@if($messages && count($messages) > 0)
    <div class="mt-1" role="alert">
        @foreach($messages as $message)
            <p class="text-sm text-danger flex items-center gap-1">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                {{ $message }}
            </p>
        @endforeach
    </div>
@endif