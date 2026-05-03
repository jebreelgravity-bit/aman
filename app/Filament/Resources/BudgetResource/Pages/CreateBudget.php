<?php
namespace App\Filament\Resources\BudgetResource\Pages;
use App\Filament\Resources\BudgetResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
class CreateBudget extends CreateRecord {
    protected static string $resource = BudgetResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array {
        $data['created_by'] = auth()->id();
        return $data;
    }
}
