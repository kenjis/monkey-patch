<?php

declare(strict_types=1);

namespace App\Model;

use stdClass;

class CategoryModel
{
    /**
     * @return stdClass[]
     */
    public function getCategoryList(): array
    {
        return [
            (object) ['name' => 'Book'],
            (object) ['name' => 'CD'],
        ];
    }
}
