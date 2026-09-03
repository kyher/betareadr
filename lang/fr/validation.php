<?php

return [
    'confirmed' => 'La confirmation de :attribute ne correspond pas.',
    'current_password' => 'Le mot de passe est incorrect.',
    'email' => ':attribute doit être une adresse e-mail valide.',
    'max' => ['numeric' => ':attribute ne doit pas dépasser :max.', 'string' => ':attribute ne doit pas dépasser :max caractères.'],
    'min' => ['numeric' => ':attribute doit être au moins égal à :min.', 'string' => ':attribute doit contenir au moins :min caractères.'],
    'password' => [
        'letters' => ':attribute doit contenir au moins une lettre.',
        'mixed' => ':attribute doit contenir au moins une majuscule et une minuscule.',
        'numbers' => ':attribute doit contenir au moins un chiffre.',
        'symbols' => ':attribute doit contenir au moins un symbole.',
        'uncompromised' => ':attribute figure dans une fuite de données. Choisissez-en un autre.',
    ],
    'regex' => 'Le format de :attribute n’est pas valide.',
    'required' => ':attribute est obligatoire.',
    'string' => ':attribute doit être une chaîne de caractères.',
    'unique' => ':attribute est déjà utilisé.',
    'attributes' => [
        'email' => 'L’adresse e-mail',
        'name' => 'Le nom',
        'password' => 'Le mot de passe',
        'password_confirmation' => 'La confirmation du mot de passe',
    ],
];
