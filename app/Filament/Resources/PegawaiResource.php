<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Produk;
use App\Models\Pegawai;
use App\Models\Penerbit;
use Filament\Forms\Form;
use Illuminate\View\View;
use Filament\Tables\Table;
use App\Services\PdfService;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Section;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Illuminate\Support\Facades\Response;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Actions\ActionButton;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Builder\Block;
use App\Filament\Resources\PegawaiResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\PegawaiResource\RelationManagers;
use Joaopaulolndev\FilamentPdfViewer\Infolists\Components\PdfViewerEntry;
use Spatie\Permission\Traits\HasRoles;


use HasApiTokens, HasFactory, Notifiable;






class PegawaiResource extends Resource
{
    protected static ?string $navigationGroup = 'Master';
    protected static ?string $navigationLabel = 'Pegawai';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    
    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Forms\Components\Grid::make(12) // Grid with 12 columns, gives more control over column spans
                ->schema([
                    Forms\Components\Section::make('Personal Information')
                        ->schema([
                            Forms\Components\hidden::make('user_id')
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
                        ])
                        ->columnSpan(6), // Takes up 4 out of 12 columns
    
                    Forms\Components\Section::make('Work Information Section')
                        ->schema([
                            Forms\Components\TextInput::make('pangkat')
                                ->label('Pangkat/Golongan')
                                ->required()
                                ->disabled(fn ($record) => $record !== null), // Disable input if editing a record
                            Forms\Components\TextInput::make('jabatan')
                                ->label('Jabatan')
                                ->required()
                                ->disabled(fn ($record) => $record !== null), // Disable input if editing a record
                            
                        ])
                        ->columnSpan(6),
                            Forms\Components\Section::make('Jabatan Section')
                                ->schema([
                                    Forms\Components\TextInput::make('unit')
                                    ->label('Unit')
                                    ->disabled(fn ($record) => $record !== null) // Disable input if editing a record
                                    ->required(),
                                    
                                ]),
                           
                    
                        
                        Forms\Components\Section::make('Add item Section')
                        ->schema([
                        Forms\Components\Repeater::make('penerbits')
                            ->label('Penerbit')
                            ->relationship()
                            ->schema([
                               

                                Forms\Components\Select::make('name')
                                    ->label('Produk')
                                    ->required()
                                    ->options(Produk::all()->pluck('nama_produk', 'nama_produk'))
                                    ->reactive()
                                    ->live()
                                    ->default(fn ($record) => $record ? $record->harga_beli : null)
                                    ->afterStateHydrated(function ($state, callable $set, callable $get) {
                                        // Ensure harga_beli is loaded when the form is in edit mode
                                        $produk = Produk::where('nama_produk', $get('name'))->first();
                                        if ($produk) {
                                            $set('harga_beli', $produk->harga_beli);
                                        }
                                    })
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        // Ensure harga_beli is updated when the product name changes
                                        $produk = Produk::where('nama_produk', $get('name'))->first();
                                        if ($produk) {
                                            $set('harga_beli', $produk->harga_beli);
                                        }
                                    }),
                                    Forms\Components\TextInput::make('jumlah')
                                    ->label('Jumlah')
                                    ->required()
                                    ->numeric()
                                    ->maxLength(255)
                                    ->reactive()
                                    ->live()
                                    ->default(1)
                                    ->afterStateUpdated(fn ($state, $get, $set) => static::updateTotals($get, $set)),

                                    Forms\Components\TextInput::make('harga_beli')
                                    ->label('Harga Satuan')
                                    ->required()
                                    ->numeric()
                                    
                                    ->live()
                                    ->reactive()
                                    ->afterStateUpdated(fn ($state, $get, $set) => static::updateTotals($get, $set)),

                                    Forms\Components\TextInput::make('harga')
                                    ->label('Sub Total')
                                    ->required()
                                    ->maxLength(255)
                                    ->reactive()
                                    ->live()
                                    ->afterStateUpdated(fn ($state, $get, $set) => static::updateTotals($get, $set)),

                                    
                                ])
                                
                                ->minItems(1)
                                ->maxItems(10)
                                ->createItemButtonLabel('Tambah Penerbit Baru')
                                ->columns(4)
                                ->reactive()
                                ->live()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    $pegawaiId = $get('pegawai_id'); // Assuming you have 'pegawai_id' in the form
                                    $isDuplicate = Penerbit::where('name', $state)
                                        ->where('pegawai_id', $pegawaiId)
                                        ->exists();
                            
                                    if ($isDuplicate) {
                                        $set('name', null);
                                        Notification::make()
                                            ->title('Error')
                                            ->danger()
                                            ->body('This name is already assigned to the selected employee.')
                                            ->send();
                                    } else {
                                        $produk = Produk::where('nama_produk', $state)->first();
                                        if ($produk) {
                                            $set('harga_beli', $produk->harga_beli);
                                        }
                                        self::updateTotals($get, $set);
                                    }
                                }),
                                ]),
                                
                                Forms\Components\Section::make('Total Section')
                                    ->schema([
                                        Forms\Components\TextInput::make('total')
                                        ->label('Total Harga')
                                        ->default(1)
                                        ->readonly()
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(fn ($state, $get, $set) => static::updateTotals($get, $set)),
                                
                                    ]),
                                    
                                    
                                ]),
                                Forms\Components\Section::make('Upload Pdf Section')
                                ->schema([
                                    FileUpload::make('attachment')
                                    ->label('Upload PDF')
                                    ->disk('public') // Menentukan disk penyimpanan, kamu bisa menggunakan disk 'public' atau lainnya yang kamu konfigurasi di filesystem.php
                                    ->directory('pdfs') // Folder tujuan penyimpanan
                                    ->acceptedFileTypes(['application/pdf']), // Hanya mengizinkan PDF
                                     // Opsional: untuk menjadikannya field yang wajib diisi

                                    Forms\Components\Section::make('Upload multi Pdf Section in Section')
                                    ->schema([
                                        FileUpload::make('pdf_files')
                                        ->label('Upload PDF')
                                        ->multiple()
                                        ->disk('public') // Menentukan disk penyimpanan, kamu bisa menggunakan disk 'public' atau lainnya yang kamu konfigurasi di filesystem.php
                                        ->directory('pdfs') // Folder tujuan penyimpanan
                                        ->acceptedFileTypes(['application/pdf']), // Hanya mengizinkan PDF
                                         
                                ]),
                               
                                    
                                ]),
                                Forms\Components\Section::make('Upload Image Section')
                                ->schema([
                                    FileUpload::make('image')
                                    ->label('Upload Image')
                                    ->image()
                                    ->imageResizeMode('cover')
                                    ->imageCropAspectRatio('16:9') // Crop to specific aspect ratio
                                    ->imageResizeTargetWidth(800) // Set a smaller width
                                    ->imageResizeTargetHeight(450) // Set a smaller height
                                    ->disk('public')
                                    ->directory('images')
                                    ->maxSize(1024) // Max 1MB
                                    ->hint('Image size should not exceed 1MB'),
                                  
                                    FileUpload::make('gambar')
                                    ->label('Upload Images')
                                    ->multiple() // Allows multiple file uploads
                                    ->image() // Ensures only image files can be uploaded
                                    ->imageResizeMode('cover') // Optional: Resize mode
                                    ->imageCropAspectRatio('16:9') // Optional: Crop aspect ratio
                                    ->imageResizeTargetWidth(800) // Set a smaller width
                                    ->imageResizeTargetHeight(450) // Set a smaller height
                                    ->disk('public') // Disk where files will be stored
                                    ->directory('images') // Directory within the disk
                                    ->required()
                                    ->extraAttributes(['loading' => 'lazy'])
                                    ->maxSize(2048)
                                   
                                    ->hint('Hanya 5 Foto yang diijinkan diupload.') // Optional hint text
                                    ->extraAttributes(['data-max-files' => 5]),
                                    
                                ]),
                               
        ]);
        
}

                    public static function updateTotals(callable $get, callable $set)
                    {
                        // Retrieve the list of penerbits
                        $penerbits = $get('penerbits') ?? [];
                        
                        // Initialize total to 0
                        $total = 0;
                        
                        // Iterate over the list of penerbits to calculate the total
                        foreach ($penerbits as $item) {
                            $total += $item['harga'] ?? 0   ;
                        }
                        
                        // Set the total price
                        $set('total', $total);
                        // Get the individual item price and quantity
                        $hargaBeli = $get('harga_beli');
                        $jumlah = $get('jumlah');
                        
                        // Calculate the item price
                        $harga = $hargaBeli * $jumlah;
                        
                        // Set the price for the current item
                        $set('harga', $harga);
                        
                        
                        
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
                   
                    

    public static function table(Table $table): Table
    {
        
        return $table 
    
            ->columns([
                Tables\Columns\TextColumn::make('nip')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('nama_pegawai')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('pangkat')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('jabatan')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('unit'),
                Tables\Columns\TextColumn::make('total')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('attachment')
                ->label('FIle')
                ->formatStateUsing(function ($state) {
                    // Construct a URL to download the file
                    $fileUrl = Storage::url($state);
                    // Style the link with inline CSS
                    return "<a href='{$fileUrl}' download style='color: #007bff; text-decoration: none;'>Download</a>";
                })
                ->html(),
                
                Tables\Columns\TextColumn::make('pdf_files')
                ->label('Download PDF')
                ->formatStateUsing(function ($record) {
                    $pdfFiles = $record->pdf_files;
            
                    if (!is_array($pdfFiles) || empty($pdfFiles)) {
                        return 'No PDF Available';
                    }
            
                    return collect($pdfFiles)->map(function ($pdf, $index) {
                        $fileName = 'pdf_file_' . ($index + 1) . '.pdf';
                        $fileUrl = asset('storage/' . $pdf);
                        return '<a href="' . $fileUrl . '" download="' . $fileName . '" >Download PDF ' . ($index + 1) . '</a>';
                    })->implode('<br>');
                })
                ->html(),

                Tables\Columns\ImageColumn::make('image')
                ->label('Thumbnail')
                ->width(100) // Width of the thumbnail
                ->height(60)
                ->disk('public')
                ->extraAttributes(['rel' => 'preload', 'as' => 'image']), // Use a thumbnail path to show optimized images,
                
                // Height of the thumbnail
                Tables\Columns\ImageColumn::make('gambar')
                ->label('Thumbnail Image')
                ->width(100) // Width of the thumbnail
                ->height(60),
                            
              
            ])
            ->filters([
               // Filter based on active user ID
                // Tables\Filters\Filter::make('user_id')
                // ->query(fn ($query) => $query->where('user_id', Auth::id())),
            ])
            
            ->actions([
                Tables\Actions\EditAction::make()->label('Detail Data'),
                Tables\Actions\DeleteAction::make(), // Tombol Delete Pegawai
                Tables\Actions\Action::make('view')
                    ->modalContent(function ($record): View {
                        // Eager load the related department data
                        $record->load('department');
                        return view('filament.pages.actions.detailpegawai', ['record' => $record]);
                    }),
                    Action::make('generatePdf')
                ->label('Generate PDF')
                ->action(function (Pegawai $record) {
                    // Logika untuk mengunduh PDF
                    return redirect()->route('generate-pdf', ['id' => $record->id]);
                })
                ->icon('heroicon-o-printer')
                ->color('primary'),
                    
            ])
                    ->bulkActions([
                        Tables\Actions\BulkActionGroup::make([
                            Tables\Actions\DeleteBulkAction::make(),
                        ]),
              
            ]);
    }

    // Relasi Detail Pegawai
    public static function getRelations(): array
    {
        return [
            RelationManagers\DetailPegawaiRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPegawais::route('/'),
            'create' => Pages\CreatePegawai::route('/create'),
            'edit' => Pages\EditPegawai::route('/{record}/edit'),
        ];
    }

  
}


