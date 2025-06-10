<x-filament-panels::page>
    <div class="space-y-6">
        <form wire:submit.prevent="submit">
            {{ $this->form }}

            <button
                type="submit"
                class="fi-btn fi-btn-size-md fi-btn-color-primary inline-flex items-center justify-center gap-2 px-3 py-2 text-sm font-medium transition-colors duration-75 rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-70 dark:focus:ring-offset-0 fi-color-primary bg-primary-600 hover:bg-primary-500 text-white focus:ring-primary-500 dark:bg-primary-500 dark:hover:bg-primary-400 dark:focus:ring-primary-500"
            >
                Importar
            </button>
        </form>
    </div>
</x-filament-panels::page>