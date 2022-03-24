<?php

namespace App\Drivers\Exceptions;

use Exception;

class NoServicesEnabled extends Exception
{
    protected $message = 'No services have been enabled, unable to continue';
}
