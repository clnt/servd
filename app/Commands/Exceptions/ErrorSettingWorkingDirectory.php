<?php

namespace App\Commands\Exceptions;

use Exception;

class ErrorSettingWorkingDirectory extends Exception
{
    protected $message = 'Unable to set working directory, max attempts reached.';
}
