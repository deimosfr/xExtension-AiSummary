<?php

declare(strict_types=1);

define('AI_SUMMARY_TESTING', true);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/stubs/FreshRSS.php';
require_once __DIR__ . '/../extension.php';
require_once __DIR__ . '/../Controllers/AiSummaryController.php';

FreshRSS_Context::init();
