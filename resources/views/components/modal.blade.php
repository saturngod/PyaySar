@props([
    'id' => null,
    'show' => false,
    'maxWidth' => 'md',
    'closeable' => true,
])

@php
    $maxWidthClasses = match($maxWidth) {
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
        '3xl' => 'max-w-3xl',
        '4xl' => 'max-w-4xl',
        'full' => 'max-w-full mx-4',
        default => 'max-w-md',
    };
@endphp

@once
    @push('scripts')
        <script>
            function openModal(modalId) {
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                    document.body.style.overflow = 'hidden';
                }
            }

            function closeModal(modalId) {
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                    document.body.style.overflow = 'auto';
                }
            }

            // Close modal on escape key
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    const modals = document.querySelectorAll('.modal-backdrop');
                    modals.forEach(modal => {
                        if (!modal.classList.contains('hidden')) {
                            closeModal(modal.id);
                        }
                    });
                }
            });

            // Close modal on backdrop click
            document.addEventListener('click', function(event) {
                if (event.target.classList.contains('modal-backdrop')) {
                    closeModal(event.target.id);
                }
            });
        </script>
    @endpush
@endonce

<div
    id="{{ $id }}"
    class="modal-backdrop fixed inset-0 z-50 hidden items-center justify-center bg-black/70 backdrop-blur-sm"
    @if($show) style="display: flex;" @endif
>
    <div class="bg-white rounded-2xl shadow-strong {{ $maxWidthClasses }} w-full max-h-[90vh] overflow-y-auto border border-gray-200/50"
         @if($show) style="transform: scale(1); opacity: 1;" @endif>
        <!-- Header -->
        @if(isset($title) || $closeable)
            <div class="flex items-center justify-between px-6 py-5 border-b border-gray-200/50 bg-white rounded-t-2xl">
                @if(isset($title))
                    <h3 class="text-xl font-bold text-gray-900">{{ $title }}</h3>
                @endif
                @if($closeable)
                    <button
                        type="button"
                        onclick="closeModal('{{ $id }}')"
                        class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg p-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                @endif
            </div>
        @endif

        <!-- Content -->
        <div class="px-6 py-6">
            {{ $slot }}
        </div>

        <!-- Footer -->
        @if(isset($footer))
            <div class="px-6 py-5 bg-white border-t border-gray-200/50 rounded-b-2xl">
                {{ $footer }}
            </div>
        @endif
    </div>
</div>
