@props(['for' => null, 'required' => false])

<label {{ $attributes->merge(['class' => 'block text-sm font-medium text-gray-700 mb-1']) }} 
       @if($for) for="{{ $for }}" @endif>
    {{ $slot }}
    @if($required)
        <span class="text-red-500 ml-1">*</span>
    @endif
</label>