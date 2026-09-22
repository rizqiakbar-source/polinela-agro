<?php

namespace Tests\Support;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait as CIDatabaseTestTrait;

trait DatabaseTestTrait
{
    use CIDatabaseTestTrait;

    protected $refresh = false;
    protected $namespace = 'App';
}
