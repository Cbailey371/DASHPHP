<x-filament-panels::page>
    <div
        class="flex flex-col h-[calc(100vh-14rem)] bg-white dark:bg-gray-900 rounded-lg shadow border dark:border-gray-700 overflow-hidden">

        {{-- Área de Mensajes --}}
        <div class="flex-1 overflow-y-auto p-4 space-y-4" id="chat-messages">
            @foreach($messages as $msg)
                <div class="flex {{ $msg['role'] === 'user' ? 'justify-end' : 'justify-start' }}">
                    <div
                        class="max-w-[80%] rounded-lg p-3 {{ $msg['role'] === 'user' ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200' }}">
                        <p class="whitespace-pre-wrap">{{ $msg['content'] }}</p>

                        @if(!empty($msg['sql']))
                            <div class="mt-2 text-xs font-mono bg-black/10 dark:bg-black/30 p-2 rounded">
                                {{ $msg['sql'] }}
                            </div>
                            <div class="mt-2">
                                <button type="button" wire:click="createReportFromSql('{{ addslashes($msg['sql']) }}')"
                                    class="text-xs bg-white dark:bg-gray-700 text-primary-600 dark:text-primary-400 px-2 py-1 rounded shadow hover:bg-gray-50 flex items-center gap-1 font-bold">
                                    <x-heroicon-o-play class="w-3 h-3" /> Ejecutar Reporte
                                </button>
                            </div>
                        @endif

                        @if(!empty($msg['error']))
                            <div class="mt-2 text-xs text-red-500 font-bold bg-red-100 dark:bg-red-900/30 p-2 rounded">
                                {{ $msg['error'] }}
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach

            @if($isThinking)
                <div class="flex justify-start">
                    <div class="bg-gray-100 dark:bg-gray-800 rounded-lg p-3">
                        <span class="animate-pulse">Escribiendo... 🤖</span>
                    </div>
                </div>
            @endif
        </div>

        {{-- Área de Input --}}
        <div class="p-4 bg-gray-50 dark:bg-gray-800 border-t dark:border-gray-700">
            <form wire:submit="sendMessage" class="flex gap-2">
                <input type="text" wire:model="newMessage"
                    placeholder="Describe el reporte que necesitas (ej: Ventas agrupadas por mes)..."
                    class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500"
                    autofocus>
                <button type="submit"
                    class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 flex items-center gap-2">
                    <x-heroicon-o-paper-airplane class="w-5 h-5" />
                    Enviar
                </button>
            </form>
        </div>

        <script>
            // Auto-scroll al fondo cuando llegan mensajes
            document.addEventListener('livewire:initialized', () => {
                const chatContainer = document.getElementById('chat-messages');

                // Scroll inicial
                chatContainer.scrollTop = chatContainer.scrollHeight;

                Livewire.on('message-received', () => {
                    setTimeout(() => {
                        chatContainer.scrollTop = chatContainer.scrollHeight;
                    }, 100);
                });
            });
        </script>
    </div>
</x-filament-panels::page>