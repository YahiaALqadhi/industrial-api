<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

class Settings extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Settings';

    protected static ?string $title = 'Settings';

    protected static ?int $navigationSort = 999;

    protected static string $view = 'filament.pages.settings';

    public ?array $data = [];

    public function mount(): void
    {
        $setting = Setting::first();

        if ($setting) {
            $this->form->fill($setting->toArray());
            return;
        }

        $this->form->fill([
            'site_name' => 'NINGBO PASAFEITE',
            'currency' => 'USD',
            'auto_reply_message' => 'Thank you for contacting NINGBO PASAFEITE. We have received your message and our team will respond as soon as possible.',
            'bank_payment_instructions' => 'Please transfer the exact order total and upload the payment receipt from the app.',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('General Settings')
                    ->schema([
                        Forms\Components\TextInput::make('site_name')
                            ->label('Company Name')
                            ->maxLength(255),

                        Forms\Components\Placeholder::make('current_logo')
                            ->label('Current Logo')
                            ->content(function () {
                                $logo = $this->data['logo'] ?? null;

                                if (is_array($logo)) {
                                    $logo = count($logo) ? reset($logo) : null;
                                }

                                if (! $logo) {
                                    return new HtmlString('<div style="color:#6b7280;">No logo uploaded.</div>');
                                }

                                return new HtmlString(
                                    '<img src="' . asset('storage/' . $logo) . '" alt="Logo" style="max-height:80px; max-width:220px; object-fit:contain; border:1px solid #e5e7eb; padding:8px; border-radius:12px; background:white;">'
                                );
                            }),

                        Forms\Components\FileUpload::make('logo')
                            ->label('Upload New Logo')
                            ->image()
                            ->disk('public')
                            ->directory('settings')
                            ->visibility('public')
                            ->nullable()
                            ->maxSize(2048)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'])
                            ->downloadable()
                            ->openable()
                            ->previewable(false)
                            ->deleteUploadedFileUsing(function (string $file) {
                                Storage::disk('public')->delete($file);
                            })
                            ->helperText('You can upload a new logo to replace the current one, or remove it.'),

                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('phone')
                            ->label('Phone / WhatsApp')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('address')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('currency')
                            ->default('USD')
                            ->maxLength(10),

                        Forms\Components\Textarea::make('auto_reply_message')
                            ->label('Auto Reply Message')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Bank Transfer Settings')
                    ->description('These details will be shown to the customer when the order is confirmed and ready for bank transfer payment.')
                    ->schema([
                        Forms\Components\TextInput::make('bank_name')
                            ->label('Bank Name')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('bank_account_name')
                            ->label('Account Name')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('bank_account_number')
                            ->label('Account Number')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('bank_iban')
                            ->label('IBAN')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('bank_swift_code')
                            ->label('SWIFT Code')
                            ->maxLength(255),

                        Forms\Components\Textarea::make('bank_address')
                            ->label('Bank Address')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('bank_payment_instructions')
                            ->label('Payment Instructions')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        if (isset($data['logo']) && is_array($data['logo'])) {
            $data['logo'] = count($data['logo']) ? reset($data['logo']) : null;
        }

        Setting::updateOrCreate(
            ['id' => 1],
            $data
        );

        Notification::make()
            ->title('Settings saved successfully.')
            ->success()
            ->send();

        $this->redirect(static::getUrl());
    }
}