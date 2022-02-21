<?php
require 'vendor/autoload.php';
require 'helper/DotEnv.php';

use helper\DotEnv;

(new DotEnv(__DIR__ . '/.env'))->load();
