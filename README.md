# paste-image

This is a Laravel nova field provides an easy interface to upload delete edit an image. 

Originally package was developed on PHP 7.3.11 and Laravel 7.8, but should work on lower versions too.

## Features
- paste image
- drag and drop
- chose an image 
- preview befor submiting
- change or delete before submitting


## Installation

Via Composer

```bash
$ composer require osama/image-paste
```

## Basic Usage

in your resource fields method add the field

```php
use Osama\ImagePaste\ImagePaste;

<...>

ImagePaste::make('Image')->disk('osama'),
```

in your config/filesystem.php add this to disks

```php
 'disks' => [
    'osama' => [
                'driver' => 'local',
                'root' => public_path('images/osama'),
                'url' => env('APP_URL').'/images/osama',
                'visibility' => 'public',
            ],
  ],
```

in your route file add this route

```php
Route::get('/images/{image}/get-preview-image', function ($image){
	return new Response(Storage::disk('osama')->get($image), 200);
});
```

## Advanced Usage

``` php
use Osama\ImagePaste\ImagePaste;

<...>

ImagePaste::make('image')
    ->canSee(function () use ($user) { return $user->hasPermission('orders-fields-image-see');})
    ->hideWhenUpdating(!$user->hasPermission('orders-fields-image-edit'))
    ->disk('osama')  // you can define your own disk
    ->prunable($isNotDefault = $this->image !== 'default.png')
    ->deletable(true) // if true you will see delete icon on the bottom right corner. make sure you make column nullable in you migration
    ->preview(function () {
      return url('/orders/' . $this->id . '/image');
    })
    ->hideFromIndex()
    ->rules('nullable', 'image', 'max:2000'),

```


## Author

- [osama][link-author]


## Issues
  if you found any issues please open an issue.
  
## Screenshots
   
Create | Detail | Update | Index
------------ | ------------- | ------------- | -------------
 ![nova-responsive-detail-view](screenshots/creation.png) | ![nova-responsive-create-view](screenshots/detail.png)|  ![nova-responsive-delete-view](screenshots/update.png)|  ![nova-responsive-delete-view](screenshots/index.png)


[link-author]: https://github.com/osamaAbdullah
