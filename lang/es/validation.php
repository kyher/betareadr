<?php

return [
    'confirmed' => 'La confirmación de :attribute no coincide.',
    'current_password' => 'La contraseña es incorrecta.',
    'email' => ':attribute debe ser una dirección de correo válida.',
    'max' => ['numeric' => ':attribute no debe ser mayor que :max.', 'string' => ':attribute no debe tener más de :max caracteres.'],
    'min' => ['numeric' => ':attribute debe ser al menos :min.', 'string' => ':attribute debe tener al menos :min caracteres.'],
    'password' => [
        'letters' => ':attribute debe contener al menos una letra.',
        'mixed' => ':attribute debe contener al menos una letra mayúscula y una minúscula.',
        'numbers' => ':attribute debe contener al menos un número.',
        'symbols' => ':attribute debe contener al menos un símbolo.',
        'uncompromised' => ':attribute aparece en una filtración de datos. Elige otro.',
    ],
    'regex' => 'El formato de :attribute no es válido.',
    'required' => ':attribute es obligatorio.',
    'string' => ':attribute debe ser texto.',
    'unique' => ':attribute ya está en uso.',
    'attributes' => [
        'email' => 'El correo electrónico',
        'name' => 'El nombre',
        'password' => 'La contraseña',
        'password_confirmation' => 'La confirmación de contraseña',
    ],
];
