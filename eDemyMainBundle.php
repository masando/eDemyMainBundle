<?php

namespace eDemy\MainBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class eDemyMainBundle extends Bundle
{
    public static function getBundleName($type = null)
    {
        if ($type == null) {
            return 'eDemyMainBundle';
        } else {
            if ($type == 'Simple') {
                return 'Main';
            } else {
                if ($type == 'simple') {
                    return 'main';
                }
            }
        }
    }

    public static function eDemyBundle() {
        return true;
    }
}
