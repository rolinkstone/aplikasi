<?php

namespace App\Filament\Resources\PegawaiResource\Pages;

use App\Filament\Resources\PegawaiResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Pegawai;


class CreatePegawai extends CreateRecord
{
    protected static string $resource = PegawaiResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function beforeCreate(): void
    {
        // Get the NIP and user_id from the form data
        $nip = $this->data['nip'] ?? null;
        $userId = $this->data['user_id'] ?? null;

        // Check if the NIP and user_id combination already exists
        if ($this->nipExistsForUser($nip, $userId)) {
            // Throw an exception to prevent the record from being created
            Notification::make()
                ->title('Duplicate Entry')
                ->body('NIP dan User ID sudah terdaftar. Silakan gunakan NIP atau User ID yang berbeda.')
                ->danger()
                ->send();

            $this->halt(); // Stop the creation process
        }
    }

   // Define the nipExistsForUser method
   protected function nipExistsForUser($nip, $userId): bool
   {
       return Pegawai::where('nip', $nip)
               ->where('user_id', $userId)
               ->exists();
   }
}


