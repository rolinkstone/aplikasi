<?php

namespace App\Filament\Resources\PegawaiResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
use App\Models\Penerbit;
use App\Models\User;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Radio;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Http;
use Filament\Forms\Get;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Illuminate\Support\Facades\Cache;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Hidden;



class DetailPegawaiRelationManager extends RelationManager
{
    protected static string $relationship = 'detail_pegawai';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\hidden::make('pegawai_id')
                ->default(auth()->id()),

                Forms\Components\TextArea::make('alamat')
                    ->label('Alamat')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('kode_pos')
                    ->label('Kode Pos')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('jk')
                ->label('Jenis Kelamin')
                ->options
                    ([
                    'laki-laki' => 'Laki-laki',
                    'perempuan' => 'Perempuan',
                    ])
                    ->required()
                    ->native(false),
                
                Forms\Components\Select::make('penerbit')
                    ->label('Penerbit')
                    ->options(Penerbit::all()->pluck('name', 'name'))
                    ->searchable()
                    ->live()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->reactive()
                            ->live()
                            ->maxLength(255),
                    ])
                    ->createOptionUsing(function ($data) {
                        // Handle creation logic
                        $penerbit = Penerbit::create($data);
                        
                        // Return the newly created option's key and value
                        return $penerbit->name;
                    })
                    ->afterStateUpdated(function ($state, $set, $get) {
                        // Add the new option to the list dynamically
                        $options = $get('options');
                        $options[$state] = $state;
                        $set('options', $options);
                    }),
                    
                Forms\Components\CheckboxList::make('technologies')
                ->options([
                    'tailwind' => 'Tailwind CSS',
                    'alpine' => 'Alpine.js',
                    'laravel' => 'Laravel',
                    'livewire' => 'Laravel Livewire',
                ])
                
                    ->columns(2)
                    ->gridDirection('row')
                    ->required(),

                Forms\Components\Radio::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'scheduled' => 'Scheduled',
                        'published' => 'Published'
                    ])
                    ->descriptions([
                        'draft' => 'Is not visible.',
                        'scheduled' => 'Will be visible.',
                        'published' => 'Is visible.'
                    ])
               
                    ->inline()
                    ->required()
                    ->inlineLabel(false),
            Forms\Components\Select::make('wilayah')
            ->label('Wilayah API')
            ->options(function () {
                // Fetch the departments data from the API
                $response = Http::get('https://wilayah.id/api/provinces.json');
                $wilayah = $response->json();
        
                // Map data to the format needed by Select
                return collect($wilayah['data'])->mapWithKeys(function ($item) {
                    // Ensure 'code' and 'name' are present and use them as needed
                    return [$item['name'] => $item['name']];
                });
            })
            ->searchable()
      
            ->placeholder('Select a Provinsi'),

            // Select Box Bertingkat (parent)
            Forms\Components\Select::make('keahlian')
             ->options([
                 'web' => 'Web development',
                 'mobile' => 'Mobile development',
                 'design' => 'Design',
             ])
             ->searchable()
             ->required()
             ->live(),
             //(child)
            Forms\Components\Select::make('pekerjaan')
             ->options(fn (Get $get): array => match ($get('keahlian')) {
                    'web' => [
                    'frontend_web' => 'Frontend development',
                    'backend_web' => 'Backend development',
                 ],
                 'mobile' => [
                 'ios_mobile' => 'iOS development',
                 'android_mobile' => 'Android development',
                ],
                 'design' => [
                     'app_design' => 'Panel design',
                     'marketing_website_design' => 'Marketing website design',
                 ],
                 default => [],
            })
            ->required(),
            Forms\Components\Placeholder::make('city_label')
            ->label('')
            ->content(''),

            Forms\Components\Select::make('provinsi')
                ->label('Provinsi')
                ->required()
                ->options(function () {
                    // Fetch the provinces data from the API
                    $response = Http::get('https://emsifa.github.io/api-wilayah-indonesia/api/provinces.json');
                    $wilayah = $response->json();
                
                    // Map data to the format needed by Select
                  
                    return collect($wilayah)->mapWithKeys(function ($item) {
                        // Ensure 'code' and 'name' are present and use them as needed
                        return [$item['id'] => $item['name']];
                        
                    });
                })
                ->searchable()
                ->live()
                ->placeholder('Select a Provinsi')
                ->reactive()
                ->required()
                ->afterStateUpdated(function ($state, callable $set) {
                    // Set 'kabupaten' to null when 'provinsi' is updated
                    $set('kabupaten', null);
                }),
            Forms\Components\Select::make('kabupaten')
                ->label('Kabupaten')
                ->options(function (callable $get) {
                    $provinsiCode = $get('provinsi');
                
                    if (!$provinsiCode) {
                        return []; // Return an empty array if no province is selected
                    }
                
                    // Fetch the regencies data from the API based on the selected province
                    $response = Http::get("https://emsifa.github.io/api-wilayah-indonesia/api/regencies/{$provinsiCode}.json");
                    $wilayah = $response->json();
                
                    // Map data to the format needed by Select
                    return collect($wilayah)->mapWithKeys(function ($item) {
                        return [$item['id'] => $item['name']];
                    });
                })
                ->searchable()
                ->live()
                ->placeholder('Select a Kabupaten')
                ->reactive()
                ->required()
                ->afterStateUpdated(function ($state, callable $set) {
                    // Set 'kabupaten' to null when 'provinsi' is updated
                    $set('kecamatan', null);
                }),
            
               
            Forms\Components\Select::make('kecamatan')
                ->label('Kecamatan')
                ->options(function (callable $get) {
                    $kabupatenCode = $get('kabupaten');
                
                    if (!$kabupatenCode) {
                        return []; // Return an empty array if no kabupaten is selected
                    }
                
                    // Fetch the districts data from the API based on the selected kabupaten
                    $response = Http::get("https://emsifa.github.io/api-wilayah-indonesia/api/districts/{$kabupatenCode}.json");
                    $wilayah = $response->json();
                
                    // Map data to the format needed by Select
                    return collect($wilayah)->mapWithKeys(function ($item) {
                        return [$item['id'] => $item['name']];
                    });
                })
                ->searchable()
                ->live()
                ->placeholder('Select a Kecamatan')
                ->reactive()
                ->required()
                ->afterStateUpdated(function ($state, callable $set) {
                    // Set 'kabupaten' to null when 'provinsi' is updated
                    $set('kelurahan', null);
                }),
            Forms\Components\Select::make('kelurahan')
                ->label('Kelurahan')
                ->options(function (callable $get) {
                    $kecamatanCode = $get('kecamatan');
                
                    if (!$kecamatanCode) {
                        return []; // Return an empty array if no kecamatan is selected
                    }
                
                    // Fetch the villages data from the API based on the selected kecamatan
                    $response = Http::get("https://emsifa.github.io/api-wilayah-indonesia/api/villages/{$kecamatanCode}.json");
                    $wilayah = $response->json();
                
                    // Map data to the format needed by Select
                    return collect($wilayah)->mapWithKeys(function ($item) {
                        return [$item['id'] => $item['name']];
                    });
                })
                ->searchable()
                ->live()
                ->placeholder('Select a Kelurahan')
                ->required()
                ->reactive(),
                
            // Forms\Components\Select::make('type')
            // ->options([
                // 'employee' => 'Employee',
                // 'freelancer' => 'Freelancer',
            // ])
             //->live()
            // ->afterStateUpdated(fn (Select $component) => $component
                // ->getContainer()
                // ->getComponent('dynamicTypeFields')
                // ->getChildComponentContainer()
                // ->fill())
                // ->searchable(),
    
                // Forms\Components\Grid::make(2)
                // ->schema(fn (Get $get): array => match ($get('type')) {
                    // 'employee' => [
                      //   TextInput::make('employee_number')
                           //  ->required(),
                      //   FileUpload::make('badge')
                            // ->image()
                           
                            // ->required(),
                    // ],
                   // 'freelancer' => [
                        // TextInput::make('hourly_rate')
                            // ->numeric()
                            // ->required()
                            // ->prefix('€'),
                        // FileUpload::make('contract')
                             //->required(),
                    // ],
                     //default => [],
                // })
                 //->key('dynamicTypeFields')



            ]);
            
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('provinsi')
            ->columns([
              
                Tables\Columns\TextColumn::make('alamat')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('kode_pos')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('jk')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('penerbit')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('technologies')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('status')->sortable()->searchable(),  
                Tables\Columns\TextColumn::make('wilayah')->sortable()->searchable(),  
                Tables\Columns\TextColumn::make('full_keahlian')->label('Keahlian & Pekerjaan')->getStateUsing(function ($record) {
                // Misalkan Anda ingin menggabungkan 'keahlian' dan 'pekerjaan'
                return $record->keahlian . ' - ' . $record->pekerjaan;})->sortable()->searchable(), 
                Tables\Columns\TextColumn::make('full_provinsi')
                ->label('Provinsi, Kabupaten/Kota, Kecamatan, Kelurahan')
                ->getStateUsing(function ($record) {
                    // Cache all province data for an hour
                    $provinces = Cache::remember('provinces', 60 * 60, function () {
                        return Http::get('https://www.emsifa.com/api-wilayah-indonesia/api/provinces.json')->json();
                    });

                    // Cache regency, district, and village data for each unique ID
                    $cacheKeyRegency = 'regency_' . $record->kabupaten;
                    $cacheKeyDistrict = 'district_' . $record->kecamatan;
                    $cacheKeyVillage = 'village_' . $record->kelurahan;

                    $regency = Cache::remember($cacheKeyRegency, 60 * 60, function () use ($record) {
                        $response = Http::get("https://emsifa.github.io/api-wilayah-indonesia/api/regency/{$record->kabupaten}.json");
                        return $response->successful() ? $response->json() : null;
                    });

                    $district = Cache::remember($cacheKeyDistrict, 60 * 60, function () use ($record) {
                        $response = Http::get("https://emsifa.github.io/api-wilayah-indonesia/api/district/{$record->kecamatan}.json");
                        return $response->successful() ? $response->json() : null;
                    });

                    $village = Cache::remember($cacheKeyVillage, 60 * 60, function () use ($record) {
                        $response = Http::get("https://emsifa.github.io/api-wilayah-indonesia/api/village/{$record->kelurahan}.json");
                        return $response->successful() ? $response->json() : null;
                    });

                    // Find names from cached data
                    $provinceName = collect($provinces)->firstWhere('id', $record->provinsi)['name'] ?? 'Unknown';
                    $regencyName = $regency['name'] ?? 'Unknown';
                    $districtName = $district['name'] ?? 'Unknown';
                    $villageName = $village['name'] ?? 'Unknown';

                    return $provinceName . ' - ' . $regencyName . ' - ' . $districtName . ' - ' . $villageName;
                })
                ->sortable()
                ->searchable(),
                  
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ViewAction::make(),
                
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
