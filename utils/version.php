<?php
// Return release details from the changelog

$file = file_get_contents('CHANGELOG.md');
$release = preg_match("(## \[(?P<version>.*)\]\s*(?P<date>.*))", $file, $matches, PREG_OFFSET_CAPTURE);

if ($release && $matches['version'][0] == 'Unreleased') {
    echo("No version - Unreleased\n");
    exit(1);
}

if ($release && in_array('--date', $argv)) {
    echo($matches['date'][0]."\n");
}

if ($release && (in_array('--version', $argv) || $argc == 1)) {
    echo($matches['version'][0]."\n");
}

