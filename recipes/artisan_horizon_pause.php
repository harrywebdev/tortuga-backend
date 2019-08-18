<?php

namespace Deployer;

desc('Pause Laravel Horizon');
task('artisan:horizon:pause', artisan('horizon:pause', ['runInCurrent', 'showOutput']));
