<?php

namespace VA\Composer;

use Composer\Script\Event;
use Composer\Installer\PackageEvent;

/**
 * Special update handling for vets-website
 */
class UpdateHandler {
    public static function preUpdateCommand(Event $event) {
        $composer = $event->getComposer();
        print "Look up latest SHA of WEB here and and set it as if the user entered the SHA...";
    }

    public static function prePackageUpdate(PackageEvent $event)
    {
    }
}


