# MigrationHelper
MigrationHelper helps to up/down migrations from PHP script

## Installation
The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist lembadm/yii2-migration-helper "*"
```

or add

```
"lembadm/yii2-migration-helper": "*"
```

to the require section of your `composer.json` file.


## Usage
To use this extension,  simply add the following code in your application configuration:

```php
return [
    //....
    'components' => [
        'migration' => [
            'class' => 'lembadm\migration\MigrationHelper',
            'migrationTable' => '<migrationTable>',
            'idleTimeout' => '<idleTimeout>',
            'timeout' => '<timeout>',
        ],
    ],
];
```

### Apply new migrations
#### Synchronously
```php
try {
    $process = Yii::$app->migration->up();
    echo $process->getOutput();
} catch (ProcessFailedException $e) {
    echo $e->getMessage();
}
```

#### Async
```php
$process = Yii::$app->migration->upAsync();
while ($process->isRunning()) {
    // waiting for process to finish
}
```

or

```php
$process = Yii::$app->migration->upAsync();
// ... do other things
$process->wait(function ($type, $buffer) {
    echo (Process::ERR === $type)
        ? 'ERR > '.$buffer
        : 'OUT > '.$buffer;
});
```

#### Apply module migrations
All same but need to specify path to module:
`$migrationPath` Path to migrations (`--migrationPath` argument for `yii migrate/up` command).
```php
$process = Yii::$app->migration->up('<pathToModule>');
```

### Downgrades the application by reverting old migrations.

#### Synchronously
```php
try {
    $process = Yii::$app->migration->down();
    echo $process->getOutput();
} catch (ProcessFailedException $e) {
    echo $e->getMessage();
}
```

#### Async
```php
$process = Yii::$app->migration->downAsync();
while ($process->isRunning()) {
    // waiting for process to finish
}
```

or

```php
$process = Yii::$app->migration->downAsync();
// ... do other things
$process->wait(function ($type, $buffer) {
    echo (Process::ERR === $type)
        ? 'ERR > '.$buffer
        : 'OUT > '.$buffer;
});
```

#### Apply module migrations
All same but need to specify path to module:
`$migrationPath` Path to migrations (`--migrationPath` argument for `yii migrate/up` command).
```php
$process = Yii::$app->migration->downAsync('<pathToModule>');
```