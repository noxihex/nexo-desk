<?php

namespace App\Rules;

use App\Models\Categoria;
use Illuminate\Contracts\Validation\Rule;

class CategoriaPertenceAoSetor implements Rule
{
    private $setorId;

    public function __construct($setorId)
    {
        $this->setorId = $setorId;
    }

    public function passes($attribute, $value)
    {
        if (!$this->setorId) {
            return true;
        }

        return Categoria::whereKey($value)
            ->whereHas('setores', function ($query) {
                $query->whereKey($this->setorId);
            })
            ->exists();
    }

    public function message()
    {
        return 'A categoria selecionada não pertence ao setor informado.';
    }
}
