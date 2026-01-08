<?php

namespace App\Filament\Pages;

use App\Models\AppSetting;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class AiConfiguration extends Page
{
    public static function canAccess(): bool
    {
        return auth()->user()->can('manage_ai_configuration');
    }

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'Sistema';
    protected static ?string $title = 'Configuración IA';
    protected static ?string $slug = 'ai-configuration';

    protected static string $view = 'filament.pages.ai-configuration';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'openai_api_key' => AppSetting::where('key', 'openai_api_key')->first()?->value,
            'openai_model' => AppSetting::where('key', 'openai_model')->first()?->value ?? 'gpt-4o',
            'openai_api_base' => AppSetting::where('key', 'openai_api_base')->first()?->value ?? 'https://api.openai.com/v1',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('OpenAI Settings')
                    ->description('Configura las credenciales para el asistente de reportes.')
                    ->schema([
                        TextInput::make('openai_api_key')
                            ->label('API Key')
                            ->password()
                            ->revealable()
                            ->helperText('Obtén tu clave en https://platform.openai.com/api-keys')
                            ->required(),
                        TextInput::make('openai_model')
                            ->label('Modelo')
                            ->helperText('Ejemplo: gpt-4o, gpt-3.5-turbo, o cualquier modelo compatible.')
                            ->placeholder('gpt-4o')
                            ->required(),
                        TextInput::make('openai_api_base')
                            ->label('Base API URL')
                            ->helperText('Opcional. Por defecto es https://api.openai.com/v1. Útil para Groq, Ollama o Proxies.')
                            ->placeholder('https://api.openai.com/v1'),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        AppSetting::updateOrCreate(['key' => 'openai_api_key'], ['value' => $data['openai_api_key']]);
        AppSetting::updateOrCreate(['key' => 'openai_model'], ['value' => $data['openai_model']]);
        AppSetting::updateOrCreate(['key' => 'openai_api_base'], ['value' => $data['openai_api_base'] ?? 'https://api.openai.com/v1']);

        Notification::make()
            ->title('Configuración guardada correctamente')
            ->success()
            ->send();
    }
}
