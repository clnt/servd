<?php

namespace App\Drivers\Exceptions;

class DriverNotFound extends \Exception
{
    protected $message = 'The specified driver could not be found';
}
