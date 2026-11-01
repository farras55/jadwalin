<?php

namespace App\Models;

/**
 * PlatformSetting model represents the platform-wide configuration settings
 * (including office coordinates and attendance geofencing radius).
 */
class PlatformSetting extends CompanySetting
{
    protected $table = 'company_settings';
}
