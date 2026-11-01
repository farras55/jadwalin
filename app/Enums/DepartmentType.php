<?php

namespace App\Enums;

enum DepartmentType: string
{
    case OPERASIONAL = 'Operasional';
    case PRODUKSI = 'Produksi';
    case ADMINISTRASI = 'Administrasi';
    case FINANCE = 'Finance';
    case HR = 'HR';
    case IT = 'IT';
    case MARKETING = 'Marketing';
    case RD = 'R&D';
    case SALES = 'Sales';
    case FOOD_AND_BEVERAGE = 'Food & Beverage';

    /**
     * Get all enum values.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
