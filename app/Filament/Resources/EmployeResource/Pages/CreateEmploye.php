<?php

namespace App\Filament\Resources\EmployeResource\Pages;

use App\Filament\Resources\EmployeResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;


use Filament\Notifications\Notification;

use App\Models\Employe;

class CreateEmploye extends CreateRecord
{
    protected static string $resource = EmployeResource::class;
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
       return Employe::where('nip', $nip)
               ->where('user_id', $userId)
               ->exists();
   }
}
