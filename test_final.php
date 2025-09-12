<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "Testing view compilation...\n";
    $view = view('furnaces.index', [
        'furnaceSets' => collect(),
        'furnaces' => collect(),
        'allCastings' => collect(),
        'furnaceCastingCounts' => [],
        'castingFurnaceSequence' => [],
        'totalCastings' => 0,
        'dailyStats' => []
    ]);
    $content = $view->render();
    echo "SUCCESS: View compiled successfully!\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
