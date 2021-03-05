<?php

declare(strict_types=1);

namespace App;

use App\Library\Input;
use App\Model\AuthyModel;
use App\Model\CategoryModel;

class PatchingMethod
{
    public function index(): string
    {
        $categoryModel = new CategoryModel();

        $list = $categoryModel->getCategoryList();

        $output = '';
        foreach ($list as $category) {
            $output .= $category->name . "\n";
        }

        return $output;
    }

    public function auth(): string
    {
        $authModel = new AuthyModel();
        $input = new Input();

        $id = $input->post('id');
        $password = $input->post('password');

        $login = $authModel->login($id, $password);

        if (! $login) {
            return 'Error!';
        }

        return 'Okay!';
    }
}
