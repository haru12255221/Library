<?php

return [
    'accepted' => ':attributeを承認してください。',
    'active_url' => ':attributeは有効なURLではありません。',
    'after' => ':attributeは:date以降の日付にしてください。',
    'alpha' => ':attributeは英字のみにしてください。',
    'alpha_dash' => ':attributeは英数字、ハイフン、アンダースコアのみにしてください。',
    'alpha_num' => ':attributeは英数字のみにしてください。',
    'between' => [
        'numeric' => ':attributeは:minから:maxの間にしてください。',
        'string' => ':attributeは:min文字から:max文字の間にしてください。',
    ],
    'confirmed' => ':attributeが確認用と一致しません。',
    'email' => ':attributeは有効なメールアドレスにしてください。',
    'exists' => '選択された:attributeは正しくありません。',
    'max' => [
        'numeric' => ':attributeは:max以下にしてください。',
        'string' => ':attributeは:max文字以下にしてください。',
        'file' => ':attributeは:maxキロバイト以下にしてください。',
    ],
    'min' => [
        'numeric' => ':attributeは:min以上にしてください。',
        'string' => ':attributeは:min文字以上にしてください。',
        'file' => ':attributeは:minキロバイト以上にしてください。',
    ],
    'numeric' => ':attributeは数値にしてください。',
    'required' => ':attributeは必須です。',
    'string' => ':attributeは文字列にしてください。',
    'unique' => ':attributeはすでに使用されています。',
    'url' => ':attributeは有効なURLにしてください。',
    'password' => [
        'letters' => ':attributeは少なくとも1つの文字を含む必要があります。',
        'mixed' => ':attributeは少なくとも1つの大文字と1つの小文字を含む必要があります。',
        'numbers' => ':attributeは少なくとも1つの数字を含む必要があります。',
        'symbols' => ':attributeは少なくとも1つの記号を含む必要があります。',
        'uncompromised' => ':attributeはデータ漏洩に含まれています。別の値を選択してください。',
    ],

    'attributes' => [
        'name' => '名前',
        'email' => 'メールアドレス',
        'password' => 'パスワード',
        'password_confirmation' => 'パスワード（確認用）',
        'title' => 'タイトル',
        'author' => '著者',
        'isbn' => 'ISBN',
        'publisher' => '出版社',
        'published_date' => '出版日',
        'description' => '説明',
        'thumbnail_url' => '表紙画像URL',
    ],
];
