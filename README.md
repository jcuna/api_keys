# API Keys package.

**To Use it with Lumen, please do the following:**
* Register repo with composer.json by adding it to the `respositories` array, i.e.
~~~~~~~~~~
    "repositories": [
        {
            "type": "vcs",
            "url": "ssh://git@github.com:jcuna/api_keys.git"
        }
~~~~~~~~~~
* Add `"jcuna/api_keys": "*"` package to require object in composer.json i.e `"require": {"jcuna/api_keys": "*"}`
* Additionally, add `"extra": {"laravel": {"providers": ["Jcuna\\Api\\ServiceProvider"]}}`
* Register the provider with the lumen app on `bootstrap/app.php` add `$app->register(Jcuna\ApiKeys\ServiceProvider::class);`
* Enable eloquent on `bootstrap/app.php` by ensuring that the file has `$app->withEloquent();`
* Configure Lumen's exception handler to return proper message and error on api keys authorization errors. i.e

`file: app/Exceptions/Handler.php`
~~~~~~~~~~~~~~~

/**
 * Render an exception into an HTTP response.
 *
 * @param  \Illuminate\Http\Request  $request
 * @param  \Exception  $exception
 * @return \Illuminate\Http\Response
 */
public function render($request, Exception $exception)
{
    if ($exception instanceof ApiKeysException) {
        return response()->json([
            'message' => $exception->getMessage(),
        ], 
        $exception->getCode(), ['Access-Control-Allow-Origin' => '*']);
    }

    return parent::render($request, $exception);
}

~~~~~~~~~~~~~~~

**If you setup a fresh database, which might be the case for local dev, please make sure you run migrations**
* `php artisan migrate`

**The following commands are available after this package is installed properly:**
* `php artisan api:client new` Creates a new client, follow the prompts
* `php artisan api:client ls` Lists clients that are not expired. Use `php artisan api:client ls --all` to list all clients
* `php artisan api:client expire` Expires a client, follow the prompt

##Adding the auth to your routes
** routes/web.php **
```
$router->group(
    ['middleware' => 'api.keys'],
    function() use ($router) {
        $router->get('/test-api', 'TestController@apiKey');
    }
);
```