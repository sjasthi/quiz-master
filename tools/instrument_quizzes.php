<?php
/**
 * Offline batch reformatter.
 *
 * Injects the score-reporting shim (see includes/quiz_instrument.php) into
 * every quiz HTML file so heterogeneous quizzes become takeable in Quiz Master.
 * Idempotent: files already reporting a score are left alone.
 *
 * Usage (from project root):
 *   php tools/instrument_quizzes.php            # instrument quizzes/python
 *   php tools/instrument_quizzes.php --dry-run  # report only, change nothing
 *   php tools/instrument_quizzes.php path/to/dir
 */

require_once __DIR__ . '/../includes/quiz_instrument.php';

$args    = array_slice($argv, 1);
$dryRun  = in_array('--dry-run', $args, true);
$dirArg  = null;
foreach ($args as $a) {
    if ($a !== '--dry-run') { $dirArg = $a; break; }
}

$dir = $dirArg ?: (__DIR__ . '/../quizzes/python');
$dir = rtrim($dir, '/\\');

$files = glob($dir . '/*.html');
if ($files === false || count($files) === 0) {
    fwrite(STDERR, "No .html files found in: $dir\n");
    exit(1);
}

$report = ['auto-capture' => [], 'needs-review' => [], 'skipped' => []];

foreach ($files as $file) {
    $html = file_get_contents($file);
    [$new, $status, $note] = qm_instrument_html($html);

    if ($status === 'instrumented') {
        if (!$dryRun) {
            file_put_contents($file, $new);
        }
        $report[$note][] = basename($file);
    } else {
        $report['skipped'][] = basename($file);
    }
}

$total = count($files);
echo "\nQuiz instrumentation" . ($dryRun ? " (DRY RUN — no files changed)" : "") . "\n";
echo "Folder: $dir\n";
echo str_repeat('-', 60) . "\n";

printf("Auto-capture enabled : %2d\n", count($report['auto-capture']));
foreach ($report['auto-capture'] as $f) echo "    + $f\n";

printf("Needs manual review  : %2d   (instrumented, but no score element detected)\n", count($report['needs-review']));
foreach ($report['needs-review'] as $f) echo "    ? $f\n";

printf("Skipped (already OK) : %2d\n", count($report['skipped']));
foreach ($report['skipped'] as $f) echo "    = $f\n";

echo str_repeat('-', 60) . "\n";
printf("Total: %d files\n", $total);
