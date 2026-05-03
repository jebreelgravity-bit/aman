<?php
$resources = [
    'ActivityLogs' => 'ActivityLogResource',
    'CouponUsages' => 'CouponUsageResource',
    'DriverRewards' => 'DriverRewardResource',
    'PopupInteractions' => 'PopupInteractionResource',
    'Popups' => 'PopupResource',
    'Ratings' => 'RatingResource',
    'SubscriptionPlans' => 'SubscriptionPlanResource',
    'Transactions' => 'TransactionResource',
    'UserSubscriptions' => 'UserSubscriptionResource',
];

$basePath = __DIR__ . '/app/Filament/Resources';

function copy_dir($src, $dst) {
    if (!is_dir($dst)) {
        mkdir($dst, 0777, true);
    }
    $dir = opendir($src);
    while (false !== ( $file = readdir($dir)) ) {
        if (( $file != '.' ) && ( $file != '..' )) {
            if ( is_dir($src . '/' . $file) ) {
                copy_dir($src . '/' . $file, $dst . '/' . $file);
            } else {
                copy($src . '/' . $file, $dst . '/' . $file);
            }
        }
    }
    closedir($dir);
}

function remove_dir($dir) {
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (is_dir($dir. DIRECTORY_SEPARATOR .$object) && !is_link($dir."/".$object))
                    remove_dir($dir. DIRECTORY_SEPARATOR .$object);
                else
                    @unlink($dir. DIRECTORY_SEPARATOR .$object);
            }
        }
        @rmdir($dir);
    }
}

foreach ($resources as $oldFolder => $newName) {
    $oldFolderPath = $basePath . '/' . $oldFolder;
    $newFolderPath = $basePath . '/' . $newName;
    $mainFileOld = $oldFolderPath . '/' . $newName . '.php';
    $mainFileNew = $basePath . '/' . $newName . '.php';

    echo "Processing $oldFolder...\n";

    if (is_dir($oldFolderPath)) {
        if (file_exists($mainFileOld)) {
            copy($mainFileOld, $mainFileNew);
            @unlink($mainFileOld);
            echo "- Moved main file out.\n";
        }

        copy_dir($oldFolderPath, $newFolderPath);
        echo "- Copied directory structure to $newName.\n";

        remove_dir($oldFolderPath);
        echo "- Removed old directory.\n";
    }

    if (file_exists($mainFileNew)) {
        $content = file_get_contents($mainFileNew);
        $content = str_replace('namespace App\Filament\Resources\\' . $oldFolder . ';', 'namespace App\Filament\Resources;', $content);
        $content = str_replace('App\Filament\Resources\\' . $oldFolder, 'App\Filament\Resources\\' . $newName, $content);
        file_put_contents($mainFileNew, $content);
        echo "- Updated main file namespace.\n";
    }

    if (is_dir($newFolderPath)) {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($newFolderPath));
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $content = file_get_contents($file->getPathname());
                $content = str_replace('App\Filament\Resources\\' . $oldFolder, 'App\Filament\Resources\\' . $newName, $content);
                file_put_contents($file->getPathname(), $content);
            }
        }
        echo "- Updated namespaces in sub-files.\n";
    }

    echo "Successfully migrated $oldFolder to $newName.\n\n";
}
