<?php

declare(strict_types=1);

require_once __DIR__.'/env.php';

echo shell_exec(PHP_EXECUTABLE.PRODUCTION_PATH.' bin/console app:ffta:archer-update'.GET_COMMAND_ERROR);
