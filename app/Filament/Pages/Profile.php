<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class Profile extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = null;
    protected static bool $shouldRegisterNavigation = false; // non in sidebar
    protected static ?string $title = 'Profilo';
    protected static string $view = 'filament.pages.profile';

    public ?array $data = [];

    public function mount(): void
{
    $user = auth()->user();

    // ✅ Banner se deve cambiare password
    if ($user?->must_change_password) {
        \Filament\Notifications\Notification::make()
            ->title('Cambio password obbligatorio')
            ->body('Per motivi di sicurezza devi cambiare la password al primo accesso, poi potrai usare il portale normalmente.')
            ->warning()
            ->persistent()
            ->send();
    }

    $this->form->fill([
        'first_name' => $user->first_name,
        'last_name'  => $user->last_name,
        'email'      => $user->email,
        'phone'      => $user->phone,
        'address'    => $user->address,
    ]);
}


    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Forms\Components\Section::make('Dati profilo')
                    ->schema([
                        Forms\Components\TextInput::make('first_name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('last_name')
                            ->label('Cognome')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(
                                table: 'users',
                                column: 'email',
                                ignorable: fn () => auth()->user()
                            ),

                        Forms\Components\TextInput::make('phone')
                            ->label('Telefono')
                            ->tel()
                            ->maxLength(50)
                            ->nullable(),

                        Forms\Components\TextInput::make('address')
                            ->label('Indirizzo')
                            ->maxLength(255)
                            ->nullable(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Password')
                    ->schema([
                        Forms\Components\TextInput::make('password')
                            ->label('Nuova password')
                            ->password()
                            ->revealable()
                            ->nullable()
                            ->rule(Password::min(8)->mixedCase()->numbers()->symbols())
->validationMessages([
    'min' => 'Scegli una password di almeno :min caratteri.',
    'mixed_case' => 'Inserisci almeno una lettera maiuscola e una minuscola.',
    'numbers' => 'Inserisci almeno un numero.',
    'symbols' => 'Inserisci almeno un simbolo (es. ! ? @ #).',
]),


                        Forms\Components\TextInput::make('password_confirmation')
                            ->label('Conferma password')
                            ->password()
                            ->revealable()
                            ->nullable()
                            ->same('password')
->validationMessages([
    'same' => 'Le due password non coincidono.',
]),
                    ])
                    ->columns(2)
                    ->description('Compila solo se vuoi cambiarla.'),
            ]);
    }

    public function save(): void
    {
        $user = auth()->user();

        $data = $this->form->getState();

        $user->first_name = $data['first_name'];
        $user->last_name  = $data['last_name'];
        $user->email      = $data['email'];
        $user->phone      = $data['phone'] ?? null;
        $user->address    = $data['address'] ?? null;

        // Manteniamo anche name aggiornato (compatibilità)
        $user->name = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));

        // Password: solo se compilata
        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
            $user->must_change_password = false;
        }

        $user->save();

        Notification::make()
            ->title('Profilo aggiornato')
            ->success()
            ->send();

        // pulizia campi password
        $this->form->fill([
            'first_name' => $user->first_name,
            'last_name'  => $user->last_name,
            'email'      => $user->email,
            'phone'      => $user->phone,
            'address'    => $user->address,
        ]);
    }
}
