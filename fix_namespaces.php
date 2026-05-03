<?php
$basePath = __DIR__ . '/app/Filament/Resources';

$resources = [
    'ActivityLogResource',
    'CouponUsageResource',
    'DriverRewardResource',
    'PopupInteractionResource',
    'PopupResource',
    'RatingResource',
    'SubscriptionPlanResource',
    'TransactionResource',
    'UserSubscriptionResource',
];

foreach ($resources as $resource) {
    $folderPath = $basePath . '/' . $resource;
    if (is_dir($folderPath)) {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($folderPath));
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $content = file_get_contents($file->getPathname());
                // Fix the duplicated class import
                $search = 'use App\Filament\Resources\\' . $resource . '\\' . $resource . ';';
                $replace = 'use App\Filament\Resources\\' . $resource . ';';
                if (str_contains($content, $search)) {
                    $content = str_replace($search, $replace, $content);
                    file_put_contents($file->getPathname(), $content);
                    echo "Fixed: " . $file->getPathname() . "\n";
                }
            }
        }
    }
}
echo "Done fixing namespaces.\n";
