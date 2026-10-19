<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\Role;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function beforeSave(): void
    {
        $record = $this->getRecord();

        if (! $record->is(auth()->user())) {
            return;
        }

        $superAdminRoleId = Role::where('name', 'super-admin')->value('id');

        if (! $superAdminRoleId) {
            return;
        }

        $hadSuperAdminRole = $record->roles()->where('roles.id', $superAdminRoleId)->exists();
        $pendingRoleIds = $this->data['roles'] ?? [];

        if ($hadSuperAdminRole && ! in_array($superAdminRoleId, $pendingRoleIds)) {
            Notification::make()
                ->danger()
                ->title('Você não pode remover sua própria role de super-admin.')
                ->send();

            $this->halt();
        }
    }
}
