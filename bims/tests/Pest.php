<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Unit tests: no database needed
uses(TestCase::class)->in('Unit');

// Feature tests: full app + database refresh
uses(TestCase::class, RefreshDatabase::class)->in('Feature');
