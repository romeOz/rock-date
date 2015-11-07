Custom locales
==================

Create a class of i18n:

```php
use rock\date\locale\Locale

class Fr extends Locale
{
    protected $year = ['année', 'ans', 'ans'];
    protected $month = ['mois', 'mois', 'mois'];
    protected $week = ['semaine', 'semaines', 'semaines'];
    protected $day = ['jour', 'jours', 'jours'];
    protected $hour = ['heure', 'heures', 'heures'];
    protected $minute = ['minute', 'minutes', 'minutes'];
    protected $second = ['la seconde', 'secondes', 'secondes'];
    protected $months = [
        'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre',
        'décembre'
    ];
   ...
}
```

Profit:

```php
$config = [
    'locales' => [
        'fr' => Fr::className()
    ]
];

$datetime = new \rock\date\DateTime('1988-11-12', null, $config);
$dateTime->setLocale('fr');

$dateTime->format('j  F  Y'); // output: 12  novembre  1988
```
