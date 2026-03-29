<?php

declare(strict_types=1);

if ($argc < 3) {
    fwrite(STDERR, "Usage: php tools/check-coverage.php <clover-file> <minimum-percent>\n");
    exit(1);
}

$cloverPath = $argv[1];
$minimumPercent = (float) $argv[2];

if (! file_exists($cloverPath)) {
    fwrite(STDERR, "Coverage file not found: {$cloverPath}\n");
    exit(1);
}

$xml = simplexml_load_file($cloverPath);

if ($xml === false) {
    fwrite(STDERR, "Unable to parse coverage file: {$cloverPath}\n");
    exit(1);
}

$metrics = $xml->project?->metrics;

if ($metrics === null) {
    fwrite(STDERR, "Coverage metrics are missing in: {$cloverPath}\n");
    exit(1);
}

$elements = (int) $metrics['elements'];
$coveredElements = (int) $metrics['coveredelements'];
$coverage = $elements > 0 ? ($coveredElements / $elements) * 100 : 0.0;

printf("Coverage: %.2f%%\n", $coverage);

if ($coverage < $minimumPercent) {
    fwrite(STDERR, sprintf(
        "Coverage gate failed: %.2f%% is below %.2f%%\n",
        $coverage,
        $minimumPercent
    ));
    exit(1);
}
