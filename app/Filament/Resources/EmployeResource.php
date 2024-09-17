<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Employe;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Tables\Enums\FiltersLayout;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Components\Section;
use App\Filament\Resources\EmployeResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\EmployeResource\RelationManagers;

class EmployeResource extends Resource
{
    protected static ?string $model = Employe::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('user_id')
                ->default(auth()->id()),

                Forms\Components\TextInput::make('nip')
                ->label('NIP')
                ->required()
                ->disabled(fn ($record) => $record !== null) // Disable input if editing a record
                ->maxLength(255),
                Forms\Components\TextInput::make('nama_pegawai')
                ->label('Nama Pegawai')
                ->required()
                ->disabled(fn ($record) => $record !== null) // Disable input if editing a record
                ->maxLength(255),
                Forms\Components\TextInput::make('pangkat')
                ->label('Pangkat/Golongan')
                ->required()
                ->disabled(fn ($record) => $record !== null), // Disable input if editing a record
                Forms\Components\TextInput::make('jabatan')
                ->label('Jabatan')
                ->required()
                ->disabled(fn ($record) => $record !== null),
                // Select Box Bertingkat (parent)
                Forms\Components\Select::make('kategori')
                ->options([
                    'keterampilan' => 'Keterampilan',
                    'keahlian' => 'Keahlian',
                    
                ])
                ->searchable()
                ->required()
                ->live()
                ->disabled(fn ($record) => $record !== null),
                //(child)
                Forms\Components\Select::make('jenjang')
                ->options(fn (Get $get): array => match ($get('kategori')) {
                    'keterampilan' => [
                    'terampil' => 'Terampil',
                    'Mahir' => 'Mahir',
                    'Penyelia' => 'Penyelia',
                    ],
                    'keahlian' => [
                    'ahli_pertama' => 'Ahli Pertama',
                    'ahli_muda' => 'Ahli Muda',
                    'ahli_madya' => 'Ahli Madya',
                    'ahli_utama' => 'Ahli Utama',
                    ],
                    
                    default => [],
            })
            ->required()
            ->disabled(fn ($record) => $record !== null),
                Forms\Components\TextInput::make('unit')
                ->label('Unit')
                ->disabled(fn ($record) => $record !== null) // Disable input if editing a record
                ->required(),
                Forms\Components\Section::make('Informasi Tambahan')
                ->columnSpan(4),
                Forms\Components\Section::make('Jabatan Section'),
                

            ]);
    }


     
    

    public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('nip')->sortable()->searchable(),
            Tables\Columns\TextColumn::make('nama_pegawai')->sortable()->searchable(),
            Tables\Columns\TextColumn::make('pangkat')->sortable()->searchable(),
            Tables\Columns\TextColumn::make('jabatan')->sortable()->searchable(),
            Tables\Columns\TextColumn::make('unit'),
        ])
        ->filters([
            Tables\Filters\Filter::make('nip_nama_pegawai')
                ->form([
                    Forms\Components\Grid::make(20)
                        ->schema([
                            Forms\Components\Select::make('jenjang')
                                ->label('Select Jenjang')
                                ->options(Employe::all()->pluck('jenjang', 'jenjang'))
                                ->placeholder('Select Jenjang')
                                ->columnSpan(10)
                                ->searchable()
                                ->reactive(),  
                             Forms\Components\Select::make('nama_pegawai')
                                ->label('Select Nama Pegawai')
                                ->options(Employe::all()->pluck('nama_pegawai', 'nama_pegawai'))
                                ->placeholder('Select Nama Pegawai')
                                ->columnSpan(10)
                                ->searchable()
                                ->reactive(),  
                        ]),
                ])
                ->query(function (Builder $query, array $data) {
                    // Return no data if filter not applied
                   // if (empty($data['nama_pegawai'])) {
                       // return $query->whereNull('id'); // Query that returns no results
                    //}

                    return $query->when($data['nama_pegawai'], function (Builder $query, $nama_pegawai) {
                        $query->where('nama_pegawai', $nama_pegawai);
                    })
                    ->when($data['jenjang'], function (Builder $query, $jenjang) {
                        $query->where('jenjang', $jenjang);
                    });
                })
                ->indicateUsing(function (array $data): ?string {
                    $indicators = [];

                    if ($data['nama_pegawai']) {
                        $indicators[] = 'Filter Nama Pegawai: ' . $data['nama_pegawai'];
                    }

                    return implode(', ', $indicators) ?: null;
                }),
        ], layout: FiltersLayout::AboveContent)
        ->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ]),
            
        ]);
}

   // Show all data for super_admin role dan apabila user tampilkan data sesuai user id
   public static function getEloquentQuery(): Builder
   {
       $query = parent::getEloquentQuery();
   
       if (Auth::user()->hasRole('super_admin')) {
           // Show all data for super_admin role
           return $query;
       }
   
       // Filter based on user ID for other roles
       return $query->where('user_id', Auth::id());
   }
   
    public static function getRelations(): array
    {
        return [
            RelationManagers\KompetensiPegawaiRelationManager::class,
            RelationManagers\KompetensiPegawaiTeknisRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployes::route('/'),
            'create' => Pages\CreateEmploye::route('/create'),
            'edit' => Pages\EditEmploye::route('/{record}/edit'),
        ];
    }
}
