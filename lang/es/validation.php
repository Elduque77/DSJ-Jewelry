<?php

/**
 * Autor: Diego (Arquitecto)
 *
 * Traduccion al espanol de los mensajes de validacion. El panel y la tienda
 * estan en espanol, asi que los errores tambien deben estarlo. Solo se
 * traducen las reglas que usa el proyecto mas las de uso general; cualquier
 * regla que falte cae al ingles por APP_FALLBACK_LOCALE.
 */

return [

    'accepted' => 'El campo :attribute debe ser aceptado.',
    'active_url' => 'El campo :attribute no es una URL valida.',
    'after' => 'El campo :attribute debe ser una fecha posterior a :date.',
    'after_or_equal' => 'El campo :attribute debe ser una fecha posterior o igual a :date.',
    'alpha' => 'El campo :attribute solo debe contener letras.',
    'alpha_dash' => 'El campo :attribute solo debe contener letras, numeros, guiones y guiones bajos.',
    'alpha_num' => 'El campo :attribute solo debe contener letras y numeros.',
    'array' => 'El campo :attribute debe ser un conjunto de elementos.',
    'before' => 'El campo :attribute debe ser una fecha anterior a :date.',
    'before_or_equal' => 'El campo :attribute debe ser una fecha anterior o igual a :date.',
    'between' => [
        'array' => 'El campo :attribute debe tener entre :min y :max elementos.',
        'file' => 'El campo :attribute debe pesar entre :min y :max kilobytes.',
        'numeric' => 'El campo :attribute debe estar entre :min y :max.',
        'string' => 'El campo :attribute debe tener entre :min y :max caracteres.',
    ],
    'boolean' => 'El campo :attribute debe ser verdadero o falso.',
    'confirmed' => 'La confirmacion de :attribute no coincide.',
    'current_password' => 'La contrasena es incorrecta.',
    'date' => 'El campo :attribute no es una fecha valida.',
    'date_equals' => 'El campo :attribute debe ser una fecha igual a :date.',
    'date_format' => 'El campo :attribute no corresponde al formato :format.',
    'decimal' => 'El campo :attribute debe tener :decimal decimales.',
    'different' => 'Los campos :attribute y :other deben ser diferentes.',
    'digits' => 'El campo :attribute debe tener :digits digitos.',
    'digits_between' => 'El campo :attribute debe tener entre :min y :max digitos.',
    'email' => 'El campo :attribute debe ser un correo electronico valido.',
    'ends_with' => 'El campo :attribute debe terminar con uno de los siguientes valores: :values.',
    'exists' => 'El valor seleccionado en :attribute no existe.',
    'file' => 'El campo :attribute debe ser un archivo.',
    'filled' => 'El campo :attribute no puede estar vacio.',
    'gt' => [
        'array' => 'El campo :attribute debe tener mas de :value elementos.',
        'file' => 'El campo :attribute debe pesar mas de :value kilobytes.',
        'numeric' => 'El campo :attribute debe ser mayor que :value.',
        'string' => 'El campo :attribute debe tener mas de :value caracteres.',
    ],
    'gte' => [
        'array' => 'El campo :attribute debe tener :value elementos o mas.',
        'file' => 'El campo :attribute debe pesar :value kilobytes o mas.',
        'numeric' => 'El campo :attribute debe ser mayor o igual que :value.',
        'string' => 'El campo :attribute debe tener :value caracteres o mas.',
    ],
    'image' => 'El campo :attribute debe ser una imagen.',
    'in' => 'El valor seleccionado en :attribute no es valido.',
    'integer' => 'El campo :attribute debe ser un numero entero.',
    'lowercase' => 'El campo :attribute debe estar en minusculas.',
    'lt' => [
        'array' => 'El campo :attribute debe tener menos de :value elementos.',
        'file' => 'El campo :attribute debe pesar menos de :value kilobytes.',
        'numeric' => 'El campo :attribute debe ser menor que :value.',
        'string' => 'El campo :attribute debe tener menos de :value caracteres.',
    ],
    'lte' => [
        'array' => 'El campo :attribute debe tener :value elementos o menos.',
        'file' => 'El campo :attribute debe pesar :value kilobytes o menos.',
        'numeric' => 'El campo :attribute debe ser menor o igual que :value.',
        'string' => 'El campo :attribute debe tener :value caracteres o menos.',
    ],
    'max' => [
        'array' => 'El campo :attribute no debe tener mas de :max elementos.',
        'file' => 'El campo :attribute no debe pesar mas de :max kilobytes.',
        'numeric' => 'El campo :attribute no debe ser mayor que :max.',
        'string' => 'El campo :attribute no debe tener mas de :max caracteres.',
    ],
    'mimes' => 'El campo :attribute debe ser un archivo de tipo: :values.',
    'mimetypes' => 'El campo :attribute debe ser un archivo de tipo: :values.',
    'min' => [
        'array' => 'El campo :attribute debe tener al menos :min elementos.',
        'file' => 'El campo :attribute debe pesar al menos :min kilobytes.',
        'numeric' => 'El campo :attribute debe ser al menos :min.',
        'string' => 'El campo :attribute debe tener al menos :min caracteres.',
    ],
    'not_in' => 'El valor seleccionado en :attribute no es valido.',
    'not_regex' => 'El formato del campo :attribute no es valido.',
    'numeric' => 'El campo :attribute debe ser un numero.',
    'present' => 'El campo :attribute debe estar presente.',
    'prohibited' => 'El campo :attribute esta prohibido.',
    'regex' => 'El formato del campo :attribute no es valido.',
    'required' => 'El campo :attribute es obligatorio.',
    'required_if' => 'El campo :attribute es obligatorio cuando :other es :value.',
    'required_unless' => 'El campo :attribute es obligatorio a menos que :other este en :values.',
    'required_with' => 'El campo :attribute es obligatorio cuando :values esta presente.',
    'required_without' => 'El campo :attribute es obligatorio cuando :values no esta presente.',
    'same' => 'Los campos :attribute y :other deben coincidir.',
    'size' => [
        'array' => 'El campo :attribute debe contener :size elementos.',
        'file' => 'El campo :attribute debe pesar :size kilobytes.',
        'numeric' => 'El campo :attribute debe ser :size.',
        'string' => 'El campo :attribute debe tener :size caracteres.',
    ],
    'starts_with' => 'El campo :attribute debe comenzar con uno de los siguientes valores: :values.',
    'string' => 'El campo :attribute debe ser texto.',
    'unique' => 'El valor del campo :attribute ya esta en uso.',
    'uploaded' => 'Subir el archivo :attribute fallo.',
    'uppercase' => 'El campo :attribute debe estar en mayusculas.',
    'url' => 'El campo :attribute debe ser una URL valida.',

    /*
    |--------------------------------------------------------------------------
    | Mensajes de validacion personalizados
    |--------------------------------------------------------------------------
    */

    'custom' => [
        'contrasena' => [
            'confirmed' => 'La confirmacion de la contrasena no coincide.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Nombres de los campos
    |--------------------------------------------------------------------------
    |
    | Reemplaza el nombre tecnico de la columna por como se llama el campo en
    | la interfaz, para que el error diga "El campo categoria es obligatorio"
    | y no "El campo idCategoria es obligatorio".
    |
    */

    'attributes' => [
        'apellido' => 'apellido',
        'calificacion' => 'calificacion',
        'comentario' => 'comentario',
        'contrasena' => 'contrasena',
        'correo' => 'correo',
        'descripcion' => 'descripcion',
        'idCategoria' => 'categoria',
        'idProducto' => 'producto',
        'material' => 'material',
        'nombre' => 'nombre',
        'precio' => 'precio',
        'stock' => 'stock',
    ],

];
