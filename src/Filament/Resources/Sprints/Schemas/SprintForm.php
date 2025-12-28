<?php

namespace Adultdate\Schedule\Filament\Resources\Sprints\Schemas;

use Adultdate\Schedule\Enums\Priority;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SprintForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Sprint details')
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->autofocus(),

                        RichEditor::make('description')
                            ->label('Goals')
                            ->columnSpanFull(),

                        Select::make('priority')
                            ->options(self::priorityOptions())
                            ->default(Priority::Medium->value)
                            ->required(),

                        DatePicker::make('starts_at')
                            ->native(false)
                            ->required(),

                        DatePicker::make('ends_at')
                            ->native(false)
                            ->required()
                            ->rule('after_or_equal:starts_at'),
                    ])
                    ->columns(2),
            ]);
    }

    /**
     * @return array<string, string>
     */
    protected static function priorityOptions(): array
    {
        return collect(Priority::cases())
            ->mapWithKeys(fn (Priority $priority) => [$priority->value => $priority->getLabel()])
            ->all();
    }
}
