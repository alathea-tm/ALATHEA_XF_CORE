<?php

namespace Alathea\Core\Repository;

use XF\Mvc\Entity\Repository;

class Dc extends Repository
{
    /**
     * Точки входа центра загрузок Alathea.
     *
     * @return string[]
     */
    public function getEndpoints(): array
    {
        return [
            'https://dc.alathea.ru', // Основная точка входа.
            'https://test.dc.alathea.ru:4431' // Точка входа разработки.
        ];
    }
}