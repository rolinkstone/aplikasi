<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PenerbitResource\Pages;
use App\Filament\Resources\PenerbitResource\RelationManagers;
use App\Models\Penerbit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PenerbitResource extends Resource
{
    protected static ?string $model = Penerbit::class;
    protected static ?string $navigationGroup = 'Master';
    protected static ?string $navigationLabel = 'Penerbit';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                ->label('Nama Penerbit')
                ->required()
                ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('harga')->sortable()->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPenerbits::route('/'),
            'create' => Pages\CreatePenerbit::route('/create'),
            'edit' => Pages\EditPenerbit::route('/{record}/edit'),
        ];
    }
}
