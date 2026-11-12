<?php

namespace App\Support;

final class ValidacionesEntrada
{
    /** Reglas para texto legible en español; acepta acentos, números y puntuación común. */
    public static function texto(bool $requerido = true, int $maximo = 255): array
    {
        return [
            $requerido ? 'required' : 'nullable',
            'string',
            'max:'.$maximo,
            'regex:~^[\pL\pM\pN\s.,;:¿?¡!()#@\'"/_-]+$~u',
        ];
    }

    public static function numero(bool $requerido = true, float|int $minimo = 0, float|int|null $maximo = null): array
    {
        $rules = [$requerido ? 'required' : 'nullable', 'numeric', 'min:'.$minimo];
        if ($maximo !== null) {
            $rules[] = 'max:'.$maximo;
        }

        return $rules;
    }

    /** La validación image inspecciona el contenido real; mimes no confía en el nombre del archivo. */
    public static function imagen(bool $requerida = true, int $maximoKilobytes = 5120): array
    {
        return [
            $requerida ? 'required' : 'nullable',
            'image',
            'mimes:jpg,jpeg,png,webp',
            'max:'.$maximoKilobytes,
            'dimensions:max_width=2560,max_height=2560',
        ];
    }
}
